<?php


require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$payload = AuthMiddleware::requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$userModel   = new UserModel();
$testModel   = new TestModel();
$resultModel = new ResultModel();


if ($action === 'users' && $method === 'GET') {
    $users = $userModel->getAll(100, 0);
    jsonResponse(['success' => true, 'users' => $users, 'csrf_token' => generateCsrfToken()]);
}

if ($action === 'block_user' && $method === 'POST') {
    !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

    $userId = (int)($input['user_id'] ?? 0);
    $block  = (bool)($input['block'] ?? true);
    if (!$userId) jsonResponse(['success' => false, 'message' => 'user_id required'], 400);
    $userModel->toggleBlock($userId, $block);

    $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
        'action' => $block ? 'block_user' : 'unblock_user',
        'target_user_id' => $userId,
    ], 'medium');

    jsonResponse(['success' => true, 'message' => $block ? 'Пользователь заблокирован' : 'Разблокирован']);
}


if ($action === 'tests' && $method === 'GET') {
    $tests = $testModel->getAll(false);
    jsonResponse(['success' => true, 'tests' => $tests, 'csrf_token' => generateCsrfToken()]);
}

if ($action === 'create_test' && $method === 'POST') {
    !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

    $input['created_by'] = $payload['sub'];
    $testId = $testModel->create($input);

    $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
        'action' => 'create_test',
        'test_id' => $testId,
    ], 'low');

    jsonResponse(['success' => true, 'test_id' => $testId, 'message' => 'Тест создан']);
}

if ($action === 'delete_test' && $method === 'DELETE') {
    $testId = (int)($_GET['test_id'] ?? 0);
    if (!$testId) jsonResponse(['success' => false, 'message' => 'test_id required'], 400);

    $testModel->deleteTest($testId);

    $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
        'action' => 'delete_test',
        'test_id' => $testId,
    ], 'medium');

    jsonResponse(['success' => true, 'message' => 'Тест удалён']);
}

if ($action === 'toggle_test' && $method === 'POST') {
    !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    $testId = (int)($input['test_id'] ?? 0);
    $active = (bool)($input['active'] ?? true);
    $testModel->toggleActive($testId, $active);
    jsonResponse(['success' => true]);
}


if ($action === 'add_question' && $method === 'POST') {
    !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    $testId = (int)($input['test_id'] ?? 0);
    if (!$testId) jsonResponse(['success' => false, 'message' => 'test_id required'], 400);

    $questionId = $testModel->addQuestion($testId, $input);

    if (!empty($input['answers']) && is_array($input['answers'])) {
        foreach ($input['answers'] as $i => $ans) {
            $testModel->addAnswer($questionId, array_merge($ans, ['order_num' => $i]));
        }
    }

    jsonResponse(['success' => true, 'question_id' => $questionId]);
}


if ($action === 'import_csv' && $method === 'POST') {
    !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    $testId = (int)($input['test_id'] ?? 0);
    if (!$testId) jsonResponse(['success' => false, 'message' => 'test_id required'], 400);
    if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) jsonResponse(['success' => false, 'message' => 'CSV файл не загружен'], 400);

    $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'text/plain'];
    !in_array($_FILES['csv']['type'], $allowedTypes) && jsonResponse(['success' => false, 'message' => 'Недопустимый тип файла. Только CSV.'], 400);
    $_FILES['csv']['size'] > 5 * 1024 * 1024 && jsonResponse(['success' => false, 'message' => 'Файл слишком большой. Максимум 5MB.'], 400);

    $handle = fopen($_FILES['csv']['tmp_name'], 'r');
    $imported = 0;
    $errors = [];
    $line = 0;

    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
        $line++;
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
            'question_type' => in_array($type, ['single','multiple','text']) ? $type : 'single',
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
        $imported++;
    }
    fclose($handle);

    
    $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
        'action' => 'import_csv',
        'test_id' => $testId,
        'imported' => $imported,
    ], 'low');

    jsonResponse(['success' => true, 'imported' => $imported, 'errors' => $errors]);
}


if ($action === 'logs' && $method === 'GET') {
    $logs = $resultModel->getAllLogs(500);
    jsonResponse(['success' => true, 'logs' => $logs, 'csrf_token' => generateCsrfToken()]);
}


if ($action === 'eye_tracking' && $method === 'GET') {
    $testId = isset($_GET['test_id']) ? (int)$_GET['test_id'] : null;
    $attemptId = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : null;
    $eyeData = $resultModel->getEyeTrackingLogs(500, $testId, $attemptId);
    $tests = $testModel->getAll(false);
    jsonResponse(['success' => true, 'data' => $eyeData, 'tests' => $tests, 'csrf_token' => generateCsrfToken()]);
}


if ($action === 'results' && $method === 'GET') {
    $results = $resultModel->getAllResults(500);
    jsonResponse(['success' => true, 'results' => $results, 'csrf_token' => generateCsrfToken()]);
}


if ($action === 'export_csv' && $method === 'GET') {
    $results = $resultModel->getAllResults(10000);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="results_' . date('Y-m-d') . '.csv"');
    header('X-Content-Type-Options: nosniff');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($out, ['ID', 'Пользователь', 'Email', 'Тест', 'Попытка', 'Балл', 'Макс', '%', 'Сдан', 'Читинг', 'Время(сек)', 'Дата']);
    foreach ($results as $r) {
        fputcsv($out, [
            $r['id'], $r['username'], $r['email'], $r['test_title'],
            $r['attempt_number'], $r['score'], $r['max_score'],
            $r['percentage'], $r['passed'] ? 'Да' : 'Нет',
            $r['cheat_score'], $r['time_spent'], $r['created_at'],
        ]);
    }
    fclose($out);
    exit;
}


if ($action === 'export_pdf' && $method === 'GET') {
    require_once __DIR__ . '/../src/helpers/PDFExporter.php';
    
    $results = $resultModel->getAllResults(10000);
    
    
    $resultModel->logEvent(null, $payload['sub'], 'admin_action', [
        'action' => 'export_pdf',
        'records_count' => count($results),
    ], 'low');
    
    PDFExporter::exportResults($results);
}

jsonResponse(['success' => false, 'message' => 'Unknown action'], 404);
