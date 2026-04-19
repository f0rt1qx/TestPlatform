<?php

require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

/**
 * Centralized error handler — catches all uncaught exceptions
 * and converts them to JSON responses with appropriate HTTP codes.
 */
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (error_reporting() & $errno) {
        jsonResponse(['success' => false, 'message' => $errstr], 500);
    }
    return true;
});

set_exception_handler(function (Throwable $e): void {
    $httpCode = match (true) {
        $e instanceof AdminException => 403,
        $e instanceof UnauthenticatedException => 401,
        $e instanceof DomainException => 400,
        $e instanceof InvalidArgumentException => 400,
        $e instanceof UnexpectedValueException => 422,
        $e instanceof LogicException => 409,
        default => 500,
    };
    jsonResponse(['success' => false, 'message' => $e->getMessage()], $httpCode);
});

$payload = AuthMiddleware::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$userModel   = new UserModel();
$testModel   = new TestModel();
$resultModel = new ResultModel();

match (true) {
    $action === 'users' && $method === 'GET' => (fn() =>
        jsonResponse(['success' => true, 'users' => $userModel->getAll(100, 0), 'csrf_token' => generateCsrfToken()])
    )(),

    $action === 'block_user' && $method === 'POST' => (function () use ($input, $payload, $userModel, $resultModel): void {
        validateCsrfToken($input['csrf_token'] ?? '') || throw new InvalidArgumentException('CSRF token invalid');
        $userId = (int)($input['user_id'] ?? 0);
        $userId > 0 || throw new DomainException('user_id required');
        $block  = (bool)($input['block'] ?? true);
        $userModel->toggleBlock($userId, $block);
        $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
            'action' => $block ? 'block_user' : 'unblock_user',
            'target_user_id' => $userId,
        ], 'medium');
        jsonResponse(['success' => true, 'message' => $block ? 'Пользователь заблокирован' : 'Разблокирован']);
    })(),

    $action === 'tests' && $method === 'GET' => (fn() =>
        jsonResponse(['success' => true, 'tests' => $testModel->getAll(false), 'csrf_token' => generateCsrfToken()])
    )(),

    $action === 'create_test' && $method === 'POST' => (function () use ($input, $payload, $testModel, $resultModel): void {
        validateCsrfToken($input['csrf_token'] ?? '') || throw new InvalidArgumentException('CSRF token invalid');
        $input['created_by'] = $payload['sub'];
        $testId = $testModel->create($input);
        $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
            'action' => 'create_test',
            'test_id' => $testId,
        ], 'low');
        jsonResponse(['success' => true, 'test_id' => $testId, 'message' => 'Тест создан']);
    })(),

    $action === 'delete_test' && $method === 'DELETE' => (function () use ($testModel, $resultModel, $payload): void {
        $testId = (int)($_GET['test_id'] ?? 0);
        $testId > 0 || throw new DomainException('test_id required');
        $testModel->deleteTest($testId);
        $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
            'action' => 'delete_test',
            'test_id' => $testId,
        ], 'medium');
        jsonResponse(['success' => true, 'message' => 'Тест удалён']);
    })(),

    $action === 'toggle_test' && $method === 'POST' => (function () use ($input, $testModel): void {
        validateCsrfToken($input['csrf_token'] ?? '') || throw new InvalidArgumentException('CSRF token invalid');
        $testId = (int)($input['test_id'] ?? 0);
        $active = (bool)($input['active'] ?? true);
        $testModel->toggleActive($testId, $active);
        jsonResponse(['success' => true]);
    })(),

    $action === 'add_question' && $method === 'POST' => (function () use ($input, $testModel): void {
        validateCsrfToken($input['csrf_token'] ?? '') || throw new InvalidArgumentException('CSRF token invalid');
        $testId = (int)($input['test_id'] ?? 0);
        $testId > 0 || throw new DomainException('test_id required');
        $questionId = $testModel->addQuestion($testId, $input);
        !empty($input['answers']) && is_array($input['answers']) && array_walk(
            $input['answers'],
            fn($ans, $i) => $testModel->addAnswer($questionId, [...$ans, 'order_num' => $i])
        );
        jsonResponse(['success' => true, 'question_id' => $questionId]);
    })(),

    $action === 'import_csv' && $method === 'POST' => (function () use ($input, $payload, $testModel, $resultModel): void {
        validateCsrfToken($input['csrf_token'] ?? '') || throw new InvalidArgumentException('CSRF token invalid');
        $testId = (int)($input['test_id'] ?? 0);
        $testId > 0 || throw new DomainException('test_id required');
        (isset($_FILES['csv']) && $_FILES['csv']['error'] === UPLOAD_ERR_OK) || throw new DomainException('CSV файл не загружен');
        $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
        in_array($_FILES['csv']['type'], $allowedTypes, true) || throw new UnexpectedValueException('Недопустимый тип файла. Только CSV.');
        $_FILES['csv']['size'] <= 5 * 1024 * 1024 || throw new UnexpectedValueException('Файл слишком большой. Максимум 5MB.');

        $handle   = fopen($_FILES['csv']['tmp_name'], 'r');
        $imported = 0;
        $errors   = [];
        $line     = 0;

        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            ++$line;
            if ($line === 1) continue;
            if (count($row) < 5) {
                $errors[] = "Строка $line: недостаточно столбцов";
                continue;
            }

            $questionText = sanitize($row[0]);
            $type         = $row[1];
            $points       = (int)$row[2];
            $rest         = array_slice($row, 3);

            $qId = $testModel->addQuestion($testId, [
                'question_text' => $questionText,
                'question_type' => in_array($type, ['single','multiple','text'], true) ? $type : 'single',
                'points'        => max(1, $points),
                'order_num'     => $line,
            ]);

            for ($i = 0; $i < count($rest) - 1; $i += 2) {
                if (empty($rest[$i])) continue;
                $testModel->addAnswer($qId, [
                    'answer_text' => sanitize($rest[$i]),
                    'is_correct'  => trim($rest[$i + 1]) === '1' ? 1 : 0,
                    'order_num'   => (int)($i / 2),
                ]);
            }
            ++$imported;
        }
        fclose($handle);

        $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
            'action' => 'import_csv',
            'test_id' => $testId,
            'imported' => $imported,
        ], 'low');

        jsonResponse(['success' => true, 'imported' => $imported, 'errors' => $errors]);
    })(),

    $action === 'logs' && $method === 'GET' => (fn() =>
        jsonResponse(['success' => true, 'logs' => $resultModel->getAllLogs(500), 'csrf_token' => generateCsrfToken()])
    )(),

    $action === 'results' && $method === 'GET' => (fn() =>
        jsonResponse(['success' => true, 'results' => $resultModel->getAllResults(500), 'csrf_token' => generateCsrfToken()])
    )(),

    $action === 'export_csv' && $method === 'GET' => (function () use ($resultModel): never {
        $results = $resultModel->getAllResults(10000);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="results_' . date('Y-m-d') . '.csv"');
        header('X-Content-Type-Options: nosniff');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['ID', 'Пользователь', 'Email', 'Тест', 'Попытка', 'Балл', 'Макс', '%', 'Сдан', 'Читинг', 'Время(сек)', 'Дата']);
        array_walk($results, fn($r) => fputcsv($out, [
            $r['id'], $r['username'], $r['email'], $r['test_title'],
            $r['attempt_number'], $r['score'], $r['max_score'],
            $r['percentage'], $r['passed'] ? 'Да' : 'Нет',
            $r['cheat_score'], $r['time_spent'], $r['created_at'],
        ]));
        fclose($out);
        exit;
    })(),

    $action === 'export_pdf' && $method === 'GET' => (function () use ($resultModel, $payload): never {
        require_once __DIR__ . '/../src/helpers/PDFExporter.php';
        $results = $resultModel->getAllResults(10000);
        $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
            'action' => 'export_pdf',
            'records_count' => count($results),
        ], 'low');
        PDFExporter::exportResults($results);
    })(),

    default => throw new LogicException('Unknown action'),
};
