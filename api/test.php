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

match (true) {
    $action === 'list' && $method === 'GET' => (function () use ($testModel): void {
        $tests = $testModel->getAll(true);
        echo json_encode(['success' => true, 'tests' => $tests], JSON_UNESCAPED_UNICODE);
        exit;
    })(),

    $action === 'start' && $method === 'POST' => (function () use ($input, $testModel, $resultModel): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $payload = AuthMiddleware::require();
        $userId  = $payload['sub'];
        $testId  = (int)($input['test_id'] ?? 0);

        !$testId && jsonResponse(['success' => false, 'message' => 'test_id required'], 400);

        $test = $testModel->findById($testId);
        (!$test || !$test['is_active']) && jsonResponse(['success' => false, 'message' => 'Тест не найден или не активен'], 404);

        $disqualified = $resultModel->hasDisqualifiedAttempt($userId, $testId);
        if ($disqualified) {
            jsonResponse([
                'success'      => false,
                'message'      => 'Вы были дисквалифицированы за нарушения. Повторное прохождение этого теста заблокировано.',
                'disqualified' => true,
            ], 403);
        }

        $attemptsUsed = $testModel->getUserAttemptCount($userId, $testId);
        $attemptsUsed >= $test['max_attempts'] && jsonResponse(['success' => false, 'message' => 'Исчерпан лимит попыток (' . $test['max_attempts'] . ')'], 403);

        $active = $resultModel->getActiveAttempt($userId, $testId);
        if ($active) {
            $testData = $testModel->loadForAttempt($testId);
            jsonResponse([
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
    })(),

    $action === 'submit' && $method === 'POST' => (function () use ($input, $testModel, $resultModel): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $payload   = AuthMiddleware::require();
        $userId    = $payload['sub'];
        $attemptId = (int)($input['attempt_id'] ?? 0);
        $answers   = $input['answers'] ?? [];
        $timeSpent = (int)($input['time_spent'] ?? 0);
        $disqualified = !empty($input['disqualified']);

        !$attemptId && jsonResponse(['success' => false, 'message' => 'attempt_id required'], 400);

        $attempt = $resultModel->findAttempt($attemptId);
        (!$attempt || $attempt['user_id'] != $userId) && jsonResponse(['success' => false, 'message' => 'Попытка не найдена'], 404);
        $attempt['status'] !== 'in_progress' && jsonResponse(['success' => false, 'message' => 'Попытка уже завершена'], 409);

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
    })(),

    $action === 'log_event' && $method === 'POST' => (function () use ($input, $resultModel): void {
        $payload   = AuthMiddleware::require();
        $userId    = $payload['sub'];
        $attemptId = (int)($input['attempt_id'] ?? 0);
        $eventType = $input['event_type'] ?? '';
        $eventData = $input['data'] ?? [];

        $allowed = ['tab_switch','window_blur','copy_attempt','right_click',
                    'devtools_open','rapid_answer','idle_too_long','page_reload',
                    'focus_lost','fullscreen_exit','eye_fixations'];
        !in_array($eventType, $allowed, true) && jsonResponse(['success' => false, 'message' => 'Invalid event_type'], 400);

        $severity = match (true) {
            in_array($eventType, ['tab_switch', 'devtools_open'], true) => 'high',
            in_array($eventType, ['window_blur', 'page_reload'], true) => 'medium',
            default => 'low',
        };

        $attempt = $resultModel->findAttempt($attemptId);
        (!$attempt || $attempt['user_id'] != $userId) && jsonResponse(['success' => false, 'message' => 'Invalid attempt'], 403);

        $resultModel->logEvent($attemptId, $userId, $eventType, $eventData, $severity);
        jsonResponse(['success' => true]);
    })(),

    $action === 'my_results' && $method === 'GET' => (function () use ($resultModel): void {
        $payload = AuthMiddleware::require();
        $results = $resultModel->getUserResults($payload['sub']);
        echo json_encode(['success' => true, 'results' => $results, 'csrf_token' => generateCsrfToken()], JSON_UNESCAPED_UNICODE);
        exit;
    })(),

    $action === 'result_detail' && $method === 'GET' => (function () use ($resultModel): void {
        $payload   = AuthMiddleware::require();
        $attemptId = (int)($_GET['attempt_id'] ?? 0);
        $result    = $resultModel->findResult($attemptId);
        $attempt   = $resultModel->findAttempt($attemptId);

        (!$result || $attempt['user_id'] != $payload['sub']) && jsonResponse(['success' => false, 'message' => 'Not found'], 404);

        $result['answers_json'] = json_decode($result['answers_json'], true);
        jsonResponse(['success' => true, 'result' => $result]);
    })(),

    $action === 'export_pdf' && $method === 'GET' => (function () use ($resultModel, $testModel): void {
        require_once __DIR__ . '/../src/helpers/PDFExporter.php';

        $payload   = AuthMiddleware::require();
        $attemptId = (int)($_GET['attempt_id'] ?? 0);

        !$attemptId && jsonResponse(['success' => false, 'message' => 'attempt_id required'], 400);

        $result  = $resultModel->findResultWithDetails($attemptId);
        $attempt = $resultModel->findAttempt($attemptId);

        (!$result || $attempt['user_id'] != $payload['sub']) && jsonResponse(['success' => false, 'message' => 'Not found'], 404);

        $questions = $testModel->getQuestionsWithAnswers($result['test_id']);
        PDFExporter::exportSingleResult($result, $questions);
    })(),

    default => jsonResponse(['success' => false, 'message' => 'Unknown action'], 404),
};
