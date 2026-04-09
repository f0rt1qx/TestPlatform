<?php

ob_start();
require_once __DIR__ . '/../src/bootstrap.php';
ob_clean();

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$otpModel  = new OTPAuthModel();
$userModel = new UserModel();

/**
 * Rate limit guard — reusable guard pattern object.
 * Returns ['ok' => true] or ['ok' => false, 'response' => [...]]
 */
class RateLimitGuard {
    public static function check(string $key, int $maxAttempts, int $windowSeconds): array {
        if (checkRateLimit($key, $maxAttempts, $windowSeconds)) {
            return ['ok' => true];
        }
        return [
            'ok'       => false,
            'response' => ['success' => false, 'message' => 'Слишком много запросов. Попробуйте позже'],
            'code'     => 429,
        ];
    }
}

/**
 * Assert wrapper with custom JSON error messages for validation failures.
 * Uses assert_options(ASSERT_EXCEPTION, 1) to throw on failure.
 */
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_CALLBACK, function (string $file, int $line, string $code, ?string $desc): void {
    $message = $desc ?: 'Validation failed';
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
});

match (true) {
    $action === 'request' && $method === 'POST' => (function () use ($input, $otpModel): void {
        $email       = sanitize($input['email'] ?? '');
        $method_type = $input['method'] ?? 'email';
        $type        = $input['type'] ?? 'login';

        // assert() for input validation
        assert('filter_var($email, FILTER_VALIDATE_EMAIL) !== false', 'Неверный формат email');

        // Guard pattern for cooldown check
        $cooldown = $otpModel->checkResendCooldown($email);
        if (!$cooldown['can_resend']) {
            jsonResponse([
                'success'            => false,
                'message'            => $cooldown['message'],
                'cooldown_remaining' => $cooldown['remaining_seconds'],
            ], 429);
        }

        // Rate limit guard
        $rateLimitId = 'otp_request_' . ($_SERVER['REMOTE_ADDR'] ?? '');
        $guard = RateLimitGuard::check($rateLimitId, 5, 300);
        if (!$guard['ok']) {
            jsonResponse($guard['response'], $guard['code']);
        }

        // try/catch ONLY for the external sendCode call
        $result = $otpModel->createOTP($email, $type);
        if (!$result['success']) {
            jsonResponse($result, 404);
        }

        $plainCode  = $result['code'] ?? '';
        try {
            $sendResult = $otpModel->sendCode($email, $plainCode, $method_type);
        } catch (Exception $e) {
            // External service (email) failure — log and continue in dev mode
            error_log('OTP send failed: ' . $e->getMessage());
            $sendResult = [
                'message'          => 'Код создан, но не отправлен',
                'development_code' => $plainCode,
                'development_info' => 'SMTP error: ' . $e->getMessage(),
            ];
        }

        jsonResponse([
            'success'          => true,
            'message'          => $sendResult['message'],
            'expires_in'       => $result['expires_in'],
            'development_code' => $sendResult['development_code'] ?? $plainCode,
            'development_info' => $sendResult['development_info'] ?? null,
            'csrf_token'       => generateCsrfToken(),
        ]);
    })(),

    $action === 'verify' && $method === 'POST' => (function () use ($input, $otpModel, $userModel): void {
        // assert() for required fields
        assert('!empty($input["csrf_token"])', 'CSRF token требуется');
        validateCsrfToken($input['csrf_token'] ?? '') || jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $email = sanitize($input['email'] ?? '');
        $code  = sanitize($input['code'] ?? '');
        assert('!empty($email) && !empty($code)', 'Email и код обязательны');

        // Rate limit guard
        $rateLimitId = 'otp_verify_' . ($_SERVER['REMOTE_ADDR'] ?? '');
        $guard = RateLimitGuard::check($rateLimitId, 10, 300);
        if (!$guard['ok']) {
            jsonResponse($guard['response'], $guard['code']);
        }

        $result = $otpModel->verifyCode($email, $code);
        if (!$result['success']) {
            jsonResponse([
                'success'          => false,
                'message'          => $result['message'],
                'remaining_attempts' => $result['remaining_attempts'] ?? null,
            ], 401);
        }
        if (!empty($result['is_blocked'])) {
            jsonResponse(['success' => false, 'message' => 'Аккаунт заблокирован. Обратитесь в поддержку'], 403);
        }

        $userModel->updateLastLogin($result['user_id']);

        $token = JWT::encode([
            'sub'   => $result['user_id'],
            'email' => $email,
            'role'  => $result['role'],
            'otp'   => true,
        ]);

        jsonResponse([
            'success'    => true,
            'message'    => 'Вход выполнен успешно',
            'token'      => $token,
            'user'       => [
                'id'       => $result['user_id'],
                'username' => $result['username'],
                'role'     => $result['role'],
            ],
            'csrf_token' => generateCsrfToken(),
        ]);
    })(),

    $action === 'resend' && $method === 'POST' => (function () use ($input, $otpModel): void {
        $email       = sanitize($input['email'] ?? '');
        $method_type = $input['method'] ?? 'email';

        // assert() for email format
        assert('filter_var($email, FILTER_VALIDATE_EMAIL) !== false', 'Неверный формат email');

        // Guard for cooldown
        $cooldown = $otpModel->checkResendCooldown($email);
        if (!$cooldown['can_resend']) {
            jsonResponse([
                'success'            => false,
                'message'            => $cooldown['message'],
                'cooldown_remaining' => $cooldown['remaining_seconds'],
            ], 429);
        }

        // Rate limit guard
        $rateLimitId = 'otp_resend_' . ($_SERVER['REMOTE_ADDR'] ?? '');
        $guard = RateLimitGuard::check($rateLimitId, 3, 300);
        if (!$guard['ok']) {
            jsonResponse($guard['response'], $guard['code']);
        }

        $result = $otpModel->createOTP($email);
        if (!$result['success']) {
            jsonResponse($result, 404);
        }

        $plainCode  = $result['code'] ?? '';
        // try/catch ONLY for the external sendCode call
        try {
            $sendResult = $otpModel->sendCode($email, $plainCode, $method_type);
        } catch (Exception $e) {
            error_log('OTP resend send failed: ' . $e->getMessage());
            $sendResult = [
                'message'          => 'Код создан, но не отправлен',
                'development_code' => $plainCode,
            ];
        }

        jsonResponse([
            'success'          => true,
            'message'          => 'Новый код отправлен',
            'expires_in'       => $result['expires_in'],
            'development_code' => $sendResult['development_code'] ?? $plainCode,
            'csrf_token'       => generateCsrfToken(),
        ]);
    })(),

    $action === 'check_user' && $method === 'POST' => (function () use ($input, $userModel): void {
        $email = sanitize($input['email'] ?? '');
        // assert() for email validation
        assert('filter_var($email, FILTER_VALIDATE_EMAIL) !== false', 'Неверный формат email');

        $user = $userModel->findByEmail($email);
        if ($user) {
            jsonResponse([
                'success'    => true,
                'exists'     => true,
                'username'   => $user['username'],
                'has_phone'  => !empty($user['phone']),
            ]);
        }

        jsonResponse(['success' => true, 'exists' => false, 'message' => 'Пользователь не найден']);
    })(),

    default => jsonResponse(['success' => false, 'message' => 'Unknown action'], 404),
};
