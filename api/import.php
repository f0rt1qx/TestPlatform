<?php
/**
 * api/import.php — импорт тестов из CSV
 * 
 * Формат CSV:
 * test_title,test_description,time_limit,max_attempts,pass_score,question_text,question_type,points,answer_text,is_correct
 * "Математика 5 класс","Тест по математике",30,2,70,"Сколько будет 2+2?","single",1,"4",1
 * "Математика 5 класс","Тест по математике",30,2,70,"Сколько будет 2+2?","single",1,"5",0
 * "Математика 5 класс","Тест по математике",30,2,70,"Какие числа четные?","multiple",2,"2",1
 * "Математика 5 класс","Тест по математике",30,2,70,"Какие числа четные?","multiple",2,"4",1
 * "Математика 5 класс","Тест по математике",30,2,70,"Какие числа четные?","multiple",2,"3",0
 */

require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ─── REQUIRE ADMIN ───────────────────────────────────────────────────────────
$payload = AuthMiddleware::require();
if (!isset($payload['role']) || $payload['role'] !== 'admin') {
    jsonResponse(['success' => false, 'message' => 'Доступ запрещён'], 403);
}

// ─── IMPORT CSV ──────────────────────────────────────────────────────────────
if ($action === 'csv' && $method === 'POST') {
    // Проверка CSRF - для FormData берём из $_POST, не из php://input
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    // Проверка файла
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
    
    // Проверка расширения
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        jsonResponse(['success' => false, 'message' => 'Разрешены только CSV файлы'], 400);
    }

    // Проверка MIME типа
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
    if (!in_array($mimeType, $allowedMimes)) {
        jsonResponse(['success' => false, 'message' => 'Неверный формат файла'], 400);
    }

    try {
        $result = importFromCSV($file['tmp_name'], $payload['sub']);
        jsonResponse([
            'success' => true,
            'message' => 'Тест импортирован успешно',
            'data' => $result
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

// ─── GET TEMPLATE ────────────────────────────────────────────────────────────
if ($action === 'template' && $method === 'GET') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="test_template.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM для UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Заголовки
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
        'is_correct'
    ]);
    
    // Пример 1: вопрос с одним правильным ответом
    fputcsv($output, [
        'Пример теста',
        'Описание теста',
        '30',
        '2',
        '60',
        'Сколько будет 2 + 2?',
        'single',
        '1',
        '4',
        '1'
    ]);
    fputcsv($output, [
        'Пример теста',
        'Описание теста',
        '30',
        '2',
        '60',
        'Сколько будет 2 + 2?',
        'single',
        '1',
        '5',
        '0'
    ]);
    fputcsv($output, [
        'Пример теста',
        'Описание теста',
        '30',
        '2',
        '60',
        'Сколько будет 2 + 2?',
        'single',
        '1',
        '6',
        '0'
    ]);
    
    // Пример 2: вопрос с несколькими правильными ответами
    fputcsv($output, [
        'Пример теста',
        'Описание теста',
        '30',
        '2',
        '60',
        'Какие числа чётные?',
        'multiple',
        '2',
        '2',
        '1'
    ]);
    fputcsv($output, [
        'Пример теста',
        'Описание теста',
        '30',
        '2',
        '60',
        'Какие числа чётные?',
        'multiple',
        '2',
        '4',
        '1'
    ]);
    fputcsv($output, [
        'Пример теста',
        'Описание теста',
        '30',
        '2',
        '60',
        'Какие числа чётные?',
        'multiple',
        '2',
        '3',
        '0'
    ]);
    fputcsv($output, [
        'Пример теста',
        'Описание теста',
        '30',
        '2',
        '60',
        'Какие числа чётные?',
        'multiple',
        '2',
        '5',
        '0'
    ]);
    
    fclose($output);
    exit;
}

// ─── FUNCTIONS ───────────────────────────────────────────────────────────────

/**
 * Импорт из CSV файла
 */
function importFromCSV(string $filePath, int $adminId): array {
    $db = Database::getInstance();
    $testModel = new TestModel();

    $handle = fopen($filePath, 'r');
    if (!$handle) {
        throw new Exception('Не удалось открыть файл');
    }

    // Читаем заголовки
    $headers = fgetcsv($handle);
    if (!$headers) {
        fclose($handle);
        throw new Exception('Пустой файл CSV');
    }

    // Удаляем BOM из первого заголовка
    $headers[0] = preg_replace('/^\x{FEFF}/u', '', $headers[0]);

    // Проверяем обязательные колонки
    $requiredColumns = ['test_title', 'question_text', 'answer_text', 'is_correct'];
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $headers)) {
            fclose($handle);
            throw new Exception("Отсутствует колонка: $col");
        }
    }

    $stats = [
        'tests_created' => 0,
        'questions_created' => 0,
        'answers_created' => 0,
        'errors' => []
    ];

    // Собираем все данные из CSV
    $rows = [];
    $rowNum = 1;
    while (($row = fgetcsv($handle)) !== false) {
        $rowNum++;
        if (count(array_filter($row, 'trim')) === 0) continue;
        $rows[] = array_combine($headers, $row);
    }
    fclose($handle);

    if (empty($rows)) {
        throw new Exception('Нет данных для импорта');
    }

    // Группируем по тестам и вопросам
    $grouped = [];
    foreach ($rows as $idx => $data) {
        $testTitle = trim($data['test_title'] ?? '');
        $questionText = trim($data['question_text'] ?? '');
        $answerText = trim($data['answer_text'] ?? '');

        if (!$testTitle) {
            $stats['errors'][] = "Строка " . ($idx + 2) . ": Пустое название теста";
            continue;
        }
        if (!$questionText) {
            $stats['errors'][] = "Строка " . ($idx + 2) . ": Пустой текст вопроса";
            continue;
        }
        if (!$answerText) {
            $stats['errors'][] = "Строка " . ($idx + 2) . ": Пустой текст ответа";
            continue;
        }

        // Ключ для группировки: тест + вопрос
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
                'question_text' => $questionText, // Сохраняем текст вопроса
                'answers' => []
            ];
        }

        $grouped[$key]['answers'][] = [
            'answer_text' => $answerText,
            'is_correct' => (int)($data['is_correct'] ?? 0),
        ];
    }

    // Создаём тесты, вопросы и ответы
    $currentTest = null;
    $currentTestId = null;
    $questionOrder = 0;

    foreach ($grouped as $key => $item) {
        try {
            // Проверяем/создаём тест
            if ($currentTest !== $item['test_title']) {
                $stmt = $db->prepare('SELECT id FROM tests WHERE title = ? LIMIT 1');
                $stmt->execute([$item['test_title']]);
                $existing = $stmt->fetch();

                if ($existing) {
                    $currentTestId = (int)$existing['id'];
                } else {
                    $testData = [
                        'title' => $item['test_title'],
                        'description' => $item['test_description'],
                        'time_limit' => $item['time_limit'],
                        'max_attempts' => $item['max_attempts'],
                        'pass_score' => $item['pass_score'],
                        'shuffle_questions' => 1,
                        'shuffle_answers' => 1,
                        'created_by' => $adminId,
                    ];
                    $currentTestId = $testModel->create($testData);
                    $stats['tests_created']++;
                }

                $currentTest = $item['test_title'];
                $questionOrder = 0;
            }

            // Создаём вопрос
            if (empty($item['answers'])) {
                $stats['errors'][] = "Вопрос '{$key}': нет вариантов ответа";
                continue;
            }

            $questionData = [
                'question_text' => $item['question_text'],
                'question_type' => $item['question_type'],
                'points' => $item['points'],
                'order_num' => $questionOrder++,
            ];

            $questionId = $testModel->addQuestion($currentTestId, $questionData);
            $stats['questions_created']++;

            // Создаём ответы
            foreach ($item['answers'] as $idx => $answer) {
                $answerData = [
                    'answer_text' => $answer['answer_text'],
                    'is_correct' => $answer['is_correct'],
                    'order_num' => $idx,
                ];
                $testModel->addAnswer($questionId, $answerData);
                $stats['answers_created']++;
            }

        } catch (Exception $e) {
            $stats['errors'][] = "Вопрос '{$key}': " . $e->getMessage();
        }
    }

    if ($stats['tests_created'] === 0 && $stats['questions_created'] === 0) {
        throw new Exception('Не удалось импортировать ни одной записи');
    }

    return $stats;
}

jsonResponse(['success' => false, 'message' => 'Unknown action'], 404);
