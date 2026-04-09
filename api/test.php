<?php


require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$testModel   = new TestModel();
$resultModel = new ResultModel();

$response = static function (array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
};


if ($action === 'list' && $method === 'GET') {
    $tests = $testModel->getAll(true);
    echo json_encode(['success' => true, 'tests' => $tests], JSON_UNESCAPED_UNICODE);
    exit;
}


if ($action === 'start' && $method === 'POST') {
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $payload = AuthMiddleware::require();
    $userId  = $payload['sub'];
    $testId  = (int)($input['test_id'] ?? 0);

    if (!$testId) jsonResponse(['success' => false, 'message' => 'test_id required'], 400);

    $test = $testModel->findById($testId);
    if (!$test || !$test['is_active']) jsonResponse(['success' => false, 'message' => 'Тест не найден или не активен'], 404);

    $disqualified = $resultModel->hasDisqualifiedAttempt($userId, $testId);
    if ($disqualified) {
        jsonResponse([
            'success' => false,
            'message' => 'Вы были дисквалифицированы за нарушения. Повторное прохождение этого теста заблокировано.',
            'disqualified' => true
        ], 403);
    }

    $attemptsUsed = $testModel->getUserAttemptCount($userId, $testId);
    if ($attemptsUsed >= $test['max_attempts']) jsonResponse(['success' => false, 'message' => 'Исчерпан лимит попыток (' . $test['max_attempts'] . ')'], 403);

    $active = $resultModel->getActiveAttempt($userId, $testId);
    if ($active) {
        $testData = $testModel->loadForAttempt($testId);
        $response([
            'success'    => true,
            'attempt_id' => $active['id'],
            'test'       => $testData,
            'resumed'    => true,
            'started_at' => $active['started_at'],
        ]);
    }

    $attemptId = $resultModel->createAttempt($userId, $testId);
    $testData  = $testModel->loadForAttempt($testId);

    jsonResponse([
        'success'    => true,
        'attempt_id' => $attemptId,
        'test'       => $testData,
        'resumed'    => false,
        'started_at' => date('Y-m-d H:i:s'),
        'csrf_token' => generateCsrfToken(),
    ]);
}


if ($action === 'submit' && $method === 'POST') {
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $payload      = AuthMiddleware::require();
    $userId       = $payload['sub'];
    $attemptId    = (int)($input['attempt_id'] ?? 0);
    $answers      = $input['answers'] ?? [];
    $timeSpent    = (int)($input['time_spent'] ?? 0);
    $disqualified = !empty($input['disqualified']);

    if (!$attemptId) jsonResponse(['success' => false, 'message' => 'attempt_id required'], 400);

    $attempt = $resultModel->findAttempt($attemptId);
    if (!$attempt || $attempt['user_id'] != $userId) jsonResponse(['success' => false, 'message' => 'Попытка не найдена'], 404);
    if ($attempt['status'] !== 'in_progress') jsonResponse(['success' => false, 'message' => 'Попытка уже завершена'], 409);

    $questions = $testModel->getQuestionsWithAnswers($attempt['test_id']);

    if ($disqualified) {
        $resultModel->updateAttemptStatus($attemptId, 'abandoned');
        $resultModel->markDisqualified($attemptId, $userId, $attempt['test_id'], $timeSpent);
        echo json_encode([
            'success'      => true,
            'disqualified' => true,
            'message'      => 'Тест завершён из-за нарушений правил.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = $resultModel->calculateAndSave(
        $attemptId, $userId, $attempt['test_id'],
        $answers, $questions, $timeSpent
    );

    jsonResponse(['success' => true, 'result' => $result]);
}


if ($action === 'log_event' && $method === 'POST') {
    $payload   = AuthMiddleware::require();
    $userId    = $payload['sub'];
    $attemptId = (int)($input['attempt_id'] ?? 0);
    $eventType = $input['event_type'] ?? '';
    $eventData = $input['data'] ?? [];

    $allowed = ['tab_switch','window_blur','copy_attempt','right_click',
                'devtools_open','rapid_answer','idle_too_long','page_reload',
                'focus_lost','fullscreen_exit','eye_fixations'];
    if (!in_array($eventType, $allowed)) jsonResponse(['success' => false, 'message' => 'Invalid event_type'], 400);

    $severity = match (true) {
        in_array($eventType, ['tab_switch', 'devtools_open']) => 'high',
        in_array($eventType, ['window_blur', 'page_reload']) => 'medium',
        default => 'low',
    };

    $attempt = $resultModel->findAttempt($attemptId);
    if (!$attempt || $attempt['user_id'] != $userId) jsonResponse(['success' => false, 'message' => 'Invalid attempt'], 403);

    $resultModel->logEvent($attemptId, $userId, $eventType, $eventData, $severity);

    $response(['success' => true]);
}


if ($action === 'my_results' && $method === 'GET') {
    $payload = AuthMiddleware::require();
    $results = $resultModel->getUserResults($payload['sub']);
    echo json_encode(['success' => true, 'results' => $results, 'csrf_token' => generateCsrfToken()], JSON_UNESCAPED_UNICODE);
    exit;
}


if ($action === 'result_detail' && $method === 'GET') {
    $payload   = AuthMiddleware::require();
    $attemptId = (int)($_GET['attempt_id'] ?? 0);
    $result    = $resultModel->findResult($attemptId);
    $attempt   = $resultModel->findAttempt($attemptId);

    if (!$result || $attempt['user_id'] != $payload['sub']) jsonResponse(['success' => false, 'message' => 'Not found'], 404);

    $result['answers_json'] = json_decode($result['answers_json'], true);
    $response(['success' => true, 'result' => $result]);
}


if ($action === 'export_pdf' && $method === 'GET') {
    require_once __DIR__ . '/../src/helpers/PDFExporter.php';

    $payload   = AuthMiddleware::require();
    $attemptId = (int)($_GET['attempt_id'] ?? 0);

    if (!$attemptId) jsonResponse(['success' => false, 'message' => 'attempt_id required'], 400);

    $result  = $resultModel->findResultWithDetails($attemptId);
    $attempt = $resultModel->findAttempt($attemptId);

    if (!$result || $attempt['user_id'] != $payload['sub']) jsonResponse(['success' => false, 'message' => 'Not found'], 404);

    $questions = $testModel->getQuestionsWithAnswers($result['test_id']);

    PDFExporter::exportSingleResult($result, $questions);
}

jsonResponse(['success' => false, 'message' => 'Unknown action'], 404);
