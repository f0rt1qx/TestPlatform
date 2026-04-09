<?php

require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$httpMethod = $_SERVER['REQUEST_METHOD'];
$importOp = $_GET['action'] ?? '';

$adminSession = AuthMiddleware::require();
if (!isset($adminSession['role']) || $adminSession['role'] !== 'admin') {
    jsonResponse(['success' => false, 'message' => 'Доступ запрещён'], 403);
}

if ($importOp === 'csv' && $httpMethod === 'POST') {

    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Файл слишком большой',
            UPLOAD_ERR_FORM_SIZE => 'Файл слишком большой',
            UPLOAD_ERR_PARTIAL => 'Файл загружен частично',
            UPLOAD_ERR_NO_FILE => 'Файл не выбран',
            UPLOAD_ERR_NO_TMP_DIR => 'Нет временной папки',
            UPLOAD_ERR_CANT_WRITE => 'Ошибка записи',
            UPLOAD_ERR_EXTENSION => 'Загрузка прервана',
        ];
        $errorMsg = $errorMessages[$_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE] ?? 'Ошибка загрузки';
        jsonResponse(['success' => false, 'message' => $errorMsg], 400);
    }

    $file = $_FILES['file'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') jsonResponse(['success' => false, 'message' => 'Разрешены только CSV файлы'], 400);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
    if (!in_array($mimeType, $allowedMimes)) jsonResponse(['success' => false, 'message' => 'Неверный формат файла'], 400);

    // Generator-based import — yields results row by row with accumulated errors
    $importResult = importFromCSVGenerator($file['tmp_name'], $adminSession['sub']);
    jsonResponse([
        'success' => true,
        'message' => 'Тест импортирован успешно',
        'data' => $importResult,
    ]);
}

if ($importOp === 'template' && $httpMethod === 'GET') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="test_template.csv"');

    $output = fopen('php://output', 'w');

    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($output, [
        'test_title',
        'test_description',
        'time_limit',
        'max_attempts',
        'pass_score',
        'question_text',
        'question_type',
        'points',
        'answer_text',
        'is_correct',
    ]);

    fputcsv($output, [
        'Пример теста', 'Описание теста', '30', '2', '60',
        'Сколько будет 2 + 2?', 'single', '1', '4', '1',
    ]);
    fputcsv($output, [
        'Пример теста', 'Описание теста', '30', '2', '60',
        'Сколько будет 2 + 2?', 'single', '1', '5', '0',
    ]);
    fputcsv($output, [
        'Пример теста', 'Описание теста', '30', '2', '60',
        'Сколько будет 2 + 2?', 'single', '1', '6', '0',
    ]);

    fputcsv($output, [
        'Пример теста', 'Описание теста', '30', '2', '60',
        'Какие числа чётные?', 'multiple', '2', '2', '1',
    ]);
    fputcsv($output, [
        'Пример теста', 'Описание теста', '30', '2', '60',
        'Какие числа чётные?', 'multiple', '2', '4', '1',
    ]);
    fputcsv($output, [
        'Пример теста', 'Описание теста', '30', '2', '60',
        'Какие числа чётные?', 'multiple', '2', '3', '0',
    ]);
    fputcsv($output, [
        'Пример теста', 'Описание теста', '30', '2', '60',
        'Какие числа чётные?', 'multiple', '2', '5', '0',
    ]);

    fclose($output);
    exit;
}

/**
 * Generator-based CSV import — processes rows one at a time via yield.
 * Instead of throwing exceptions, yields ['error' => ...] for problems
 * and ['row' => ...] for successful processing.
 * Returns accumulated stats at the end.
 */
function importFromCSVGenerator(string $filePath, int $adminId): array {
    $db = Database::getInstance();
    $testModel = new TestModel();

    $handle = fopen($filePath, 'r');
    if (!$handle) {
        return ['tests_created' => 0, 'questions_created' => 0, 'answers_created' => 0, 'errors' => ['Не удалось открыть файл']];
    }

    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        return ['tests_created' => 0, 'questions_created' => 0, 'answers_created' => 0, 'errors' => ['Пустой файл CSV']];
    }

    $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);

    $requiredColumns = ['test_title', 'question_text', 'answer_text', 'is_correct'];
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $headers)) {
            fclose($handle);
            return ['tests_created' => 0, 'questions_created' => 0, 'answers_created' => 0, 'errors' => ["Отсутствует колонка: $col"]];
        }
    }

    $stats = [
        'tests_created' => 0,
        'questions_created' => 0,
        'answers_created' => 0,
        'errors' => [],
    ];

    // Generator: read and validate rows one at a time
    $rowGenerator = (function () use ($handle, $headers): iterable {
        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count(array_filter($row, 'trim')) === 0) continue;
            yield $rowNum => array_combine($headers, $row);
        }
    })();

    // Accumulate grouped data from generator
    $grouped = [];
    foreach ($rowGenerator as $rowNum => $data) {
        $testTitle = trim($data['test_title'] ?? '');
        $questionText = trim($data['question_text'] ?? '');
        $answerText = trim($data['answer_text'] ?? '');

        // Yield errors instead of throwing
        if (!$testTitle) {
            yield ['error' => "Строка {$rowNum}: Пустое название теста"];
            continue;
        }
        if (!$questionText) {
            yield ['error' => "Строка {$rowNum}: Пустой текст вопроса"];
            continue;
        }
        if (!$answerText) {
            yield ['error' => "Строка {$rowNum}: Пустой текст ответа"];
            continue;
        }

        $key = $testTitle . '|||' . $questionText;

        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'test_title' => $testTitle,
                'test_description' => trim($data['test_description'] ?? ''),
                'time_limit' => (int)($data['time_limit'] ?? 30),
                'max_attempts' => (int)($data['max_attempts'] ?? 1),
                'pass_score' => (int)($data['pass_score'] ?? 60),
                'question_type' => trim($data['question_type'] ?? 'single'),
                'points' => (int)($data['points'] ?? 1),
                'question_text' => $questionText,
                'answers' => [],
            ];
        }

        $grouped[$key]['answers'][] = [
            'answer_text' => $answerText,
            'is_correct' => (int)($data['is_correct'] ?? 0),
        ];
    }
    fclose($handle);

    if (empty($grouped)) {
        return ['tests_created' => 0, 'questions_created' => 0, 'answers_created' => 0, 'errors' => ['Нет данных для импорта']];
    }

    // Generator: process grouped data one entry at a time
    $currentTest = null;
    $currentTestId = null;
    $questionOrder = 0;

    $processGenerator = (function () use ($grouped, $db, $testModel, $adminId, &$stats, &$currentTest, &$currentTestId, &$questionOrder): iterable {
        foreach ($grouped as ['test_title' => $testTitle, 'test_description' => $description, 'time_limit' => $timeLimit, 'max_attempts' => $maxAttempts, 'pass_score' => $passScore, 'question_type' => $questionType, 'points' => $points, 'question_text' => $questionText, 'answers' => $answers]) {

            if ($currentTest !== $testTitle) {
                try {
                    $stmt = $db->prepare('SELECT id FROM tests WHERE title = ? LIMIT 1');
                    $stmt->execute([$testTitle]);
                    $existing = $stmt->fetch();

                    if ($existing) {
                        $currentTestId = (int)$existing['id'];
                    } else {
                        $testData = [
                            'title' => $testTitle,
                            'description' => $description,
                            'time_limit' => $timeLimit,
                            'max_attempts' => $maxAttempts,
                            'pass_score' => $passScore,
                            'shuffle_questions' => 1,
                            'shuffle_answers' => 1,
                            'created_by' => $adminId,
                        ];
                        $currentTestId = $testModel->create($testData);
                        $stats['tests_created']++;
                        yield ['info' => "Создан тест: {$testTitle}"];
                    }

                    $currentTest = $testTitle;
                    $questionOrder = 0;
                } catch (Exception $e) {
                    yield ['error' => "Тест '{$testTitle}': " . $e->getMessage()];
                    continue;
                }
            }

            if (empty($answers)) {
                yield ['error' => "Вопрос '{$testTitle}|||{$questionText}': нет вариантов ответа"];
                continue;
            }

            try {
                $questionData = [
                    'question_text' => $questionText,
                    'question_type' => $questionType,
                    'points' => $points,
                    'order_num' => $questionOrder++,
                ];

                $questionId = $testModel->addQuestion($currentTestId, $questionData);
                $stats['questions_created']++;
                yield ['info' => "Добавлен вопрос: {$questionText}"];

                foreach ($answers as $idx => ['answer_text' => $answerText, 'is_correct' => $isCorrect]) {
                    $testModel->addAnswer($questionId, [
                        'answer_text' => $answerText,
                        'is_correct' => $isCorrect,
                        'order_num' => $idx,
                    ]);
                    $stats['answers_created']++;
                }
            } catch (Exception $e) {
                yield ['error' => "Вопрос '{$testTitle}|||{$questionText}': " . $e->getMessage()];
            }
        }
    })();

    // Consume the process generator, collecting errors
    foreach ($processGenerator as $yielded) {
        if (isset($yielded['error'])) {
            $stats['errors'][] = $yielded['error'];
        }
    }

    return $stats;
}

jsonResponse(['success' => false, 'message' => 'Unknown action'], 404);
