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

/**
 * Fatal error shutdown handler — catches E_ERROR, E_PARSE, etc.
 * that normal try/catch cannot intercept.
 */
register_shutdown_function(function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error: ' . $error['message'],
            'file'    => APP_DEBUG ? $error['file'] : null,
            'line'    => APP_DEBUG ? $error['line'] : null,
        ], JSON_UNESCAPED_UNICODE);
    }
});

/**
 * Match-based error code selector — maps exception class to HTTP status.
 */
$httpCodeFor = fn(Throwable $e): int => match (true) {
    $e instanceof UnauthenticatedException => 401,
    $e instanceof AdminException           => 403,
    $e instanceof DomainException          => 400,
    $e instanceof InvalidArgumentException => 400,
    $e instanceof LengthException          => 400,
    $e instanceof OutOfBoundsException     => 404,
    $e instanceof OverflowException        => 409,
    $e instanceof RuntimeException         => 500,
    default                                => 500,
};

try {
    match (true) {
        $action === 'list' && $method === 'GET' => (function () use ($testModel): void {
            $tests = $testModel->getAll(true);
            echo json_encode(['success' => true, 'tests' => $tests], JSON_UNESCAPED_UNICODE);
            exit;
        })(),

        $action === 'start' && $method === 'POST' => (function () use ($input, $testModel, $resultModel): void {
            validateCsrfToken($input['csrf_token'] ?? '') || throw new InvalidArgumentException('CSRF token invalid');

            $payload = AuthMiddleware::require();
            $userId  = $payload['sub'];
            $testId  = (int)($input['test_id'] ?? 0);

            if ($testId <= 0) {
                throw new DomainException('test_id required');
            }

            // Targeted try/catch around DB operation
            try {
                $test = $testModel->findById($testId);
            } catch (PDOException $dbEx) {
                throw new RuntimeException('Failed to load test: ' . $dbEx->getMessage(), 0, $dbEx);
            }

            if (!$test || !$test['is_active']) {
                throw new OutOfBoundsException('Тест не найден или не активен');
            }

            $disqualified = $resultModel->hasDisqualifiedAttempt($userId, $testId);
            if ($disqualified) {
                jsonResponse([
                    'success'      => false,
                    'message'      => 'Вы были дисквалифицированы за нарушения. Повторное прохождение этого теста заблокировано.',
                    'disqualified' => true,
                ], 403);
            }

            $attemptsUsed = $testModel->getUserAttemptCount($userId, $testId);
            if ($attemptsUsed >= $test['max_attempts']) {
                throw new OverflowException('Исчерпан лимит попыток (' . $test['max_attempts'] . ')');
            }

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

            // Targeted try/catch around attempt creation
            try {
                $attemptId = $resultModel->createAttempt($userId, $testId);
                $testData  = $testModel->loadForAttempt($testId);
            } catch (PDOException $dbEx) {
                throw new RuntimeException('Failed to create attempt: ' . $dbEx->getMessage(), 0, $dbEx);
            }

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
            validateCsrfToken($input['csrf_token'] ?? '') || throw new InvalidArgumentException('CSRF token invalid');

            $payload   = AuthMiddleware::require();
            $userId    = $payload['sub'];
            $attemptId = (int)($input['attempt_id'] ?? 0);
            $answers   = $input['answers'] ?? [];
            $timeSpent = (int)($input['time_spent'] ?? 0);
            $disqualified = !empty($input['disqualified']);

            if ($attemptId <= 0) {
                throw new DomainException('attempt_id required');
            }

            $attempt = $resultModel->findAttempt($attemptId);
            if (!$attempt || $attempt['user_id'] != $userId) {
                throw new OutOfBoundsException('Попытка не найдена');
            }
            if ($attempt['status'] !== 'in_progress') {
                throw new OverflowException('Попытка уже завершена');
            }

            // Targeted try/catch around question loading
            try {
                $questions = $testModel->getQuestionsWithAnswers($attempt['test_id']);
            } catch (PDOException $dbEx) {
                throw new RuntimeException('Failed to load questions: ' . $dbEx->getMessage(), 0, $dbEx);
            }

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

            // Targeted try/catch around calculation
            try {
                $result = $resultModel->calculateAndSave(
                    $attemptId, $userId, $attempt['test_id'],
                    $answers, $questions, $timeSpent
                );
            } catch (PDOException $dbEx) {
                throw new RuntimeException('Failed to save result: ' . $dbEx->getMessage(), 0, $dbEx);
            }

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
            if (!in_array($eventType, $allowed, true)) {
                throw new InvalidArgumentException('Invalid event_type');
            }

            $severity = match (true) {
                in_array($eventType, ['tab_switch', 'devtools_open'], true) => 'high',
                in_array($eventType, ['window_blur', 'page_reload'], true) => 'medium',
                default => 'low',
            };

            $attempt = $resultModel->findAttempt($attemptId);
            if (!$attempt || $attempt['user_id'] != $userId) {
                throw new DomainException('Invalid attempt');
            }

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

            // Targeted try/catch around DB reads
            try {
                $result  = $resultModel->findResult($attemptId);
                $attempt = $resultModel->findAttempt($attemptId);
            } catch (PDOException $dbEx) {
                throw new RuntimeException('Failed to load result details: ' . $dbEx->getMessage(), 0, $dbEx);
            }

            if (!$result || $attempt['user_id'] != $payload['sub']) {
                throw new OutOfBoundsException('Not found');
            }

            $result['answers_json'] = json_decode($result['answers_json'], true);
            jsonResponse(['success' => true, 'result' => $result]);
        })(),

        $action === 'export_pdf' && $method === 'GET' => (function () use ($resultModel, $testModel): void {
            require_once __DIR__ . '/../src/helpers/PDFExporter.php';

            $payload   = AuthMiddleware::require();
            $attemptId = (int)($_GET['attempt_id'] ?? 0);

            if ($attemptId <= 0) {
                throw new DomainException('attempt_id required');
            }

            // Targeted try/catch around data loading
            try {
                $result  = $resultModel->findResultWithDetails($attemptId);
                $attempt = $resultModel->findAttempt($attemptId);
            } catch (PDOException $dbEx) {
                throw new RuntimeException('Failed to load result: ' . $dbEx->getMessage(), 0, $dbEx);
            }

            if (!$result || $attempt['user_id'] != $payload['sub']) {
                throw new OutOfBoundsException('Not found');
            }

            $questions = $testModel->getQuestionsWithAnswers($result['test_id']);
            PDFExporter::exportSingleResult($result, $questions);
        })(),

        default => jsonResponse(['success' => false, 'message' => 'Unknown action'], 404),
    };
} catch (Throwable $e) {
    // Match-based handler selects the right HTTP code
    $httpCode = $httpCodeFor($e);
    jsonResponse(['success' => false, 'message' => $e->getMessage()], $httpCode);
}
