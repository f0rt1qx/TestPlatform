<?php


ob_start(); 

require_once __DIR__ . '/../src/bootstrap.php';


ob_clean();

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$httpMethod = $_SERVER['REQUEST_METHOD'];
$otpOp = $_GET['action'] ?? '';
$otpPayload  = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $codeManager = new OTPAuthModel();
    $accountsDb = new UserModel();

    
    if ($action === 'request' && $method === 'POST') {
        $email = sanitize($input['email'] ?? '');
        $method_type = $input['method'] ?? 'email';
        $type = $input['type'] ?? 'login';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Неверный формат email'], 400);
        }

        $cooldown = $otpModel->checkResendCooldown($email);
        if (!$cooldown['can_resend']) {
            jsonResponse([
                'success' => false,
                'message' => $cooldown['message'],
                'cooldown_remaining' => $cooldown['remaining_seconds']
            ], 429);
        }

        $rateLimitId = 'otp_request_' . ($_SERVER['REMOTE_ADDR'] ?? '');
        if (!checkRateLimit($rateLimitId, 5, 300)) {
            jsonResponse(['success' => false, 'message' => 'Слишком много запросов. Попробуйте позже'], 429);
        }

        $result = $otpModel->createOTP($email, $type);
        if (!$result['success']) jsonResponse($result, 404);

        $plainCode = $result['code'] ?? '';
        $sendResult = $otpModel->sendCode($email, $plainCode, $method_type);

        jsonResponse([
            'success' => true,
            'message' => $sendResult['message'],
            'expires_in' => $result['expires_in'],
            'development_code' => $sendResult['development_code'] ?? $plainCode,
            'development_info' => $sendResult['development_info'] ?? null,
            'csrf_token' => generateCsrfToken()
        ]);
    }

    
    if ($action === 'verify' && $method === 'POST') {
        if (!validateCsrfToken($input['csrf_token'] ?? '')) {
            jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
        }

        $email = sanitize($input['email'] ?? '');
        $code = sanitize($input['code'] ?? '');
        if (empty($email) || empty($code)) jsonResponse(['success' => false, 'message' => 'Email и код обязательны'], 400);

        $rateLimitId = 'otp_verify_' . ($_SERVER['REMOTE_ADDR'] ?? '');
        if (!checkRateLimit($rateLimitId, 10, 300)) {
            jsonResponse(['success' => false, 'message' => 'Слишком много попыток. Попробуйте позже'], 429);
        }

        $result = $otpModel->verifyCode($email, $code);
        if (!$result['success']) {
            jsonResponse([
                'success' => false,
                'message' => $result['message'],
                'remaining_attempts' => $result['remaining_attempts'] ?? null
            ], 401);
        }
        if ($result['is_blocked']) jsonResponse(['success' => false, 'message' => 'Аккаунт заблокирован. Обратитесь в поддержку'], 403);

        $userModel->updateLastLogin($result['user_id']);

        $token = JWT::encode([
            'sub'   => $result['user_id'],
            'email' => $email,
            'role'  => $result['role'],
            'otp'   => true,
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Вход выполнен успешно',
            'token' => $token,
            'user' => [
                'id' => $result['user_id'],
                'username' => $result['username'],
                'role' => $result['role'],
            ],
            'csrf_token' => generateCsrfToken()
        ]);
    }

    
    if ($action === 'resend' && $method === 'POST') {
        $email = sanitize($input['email'] ?? '');
        $method_type = $input['method'] ?? 'email';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Неверный формат email'], 400);
        }

        $cooldown = $otpModel->checkResendCooldown($email);
        if (!$cooldown['can_resend']) {
            jsonResponse([
                'success' => false,
                'message' => $cooldown['message'],
                'cooldown_remaining' => $cooldown['remaining_seconds']
            ], 429);
        }

        $rateLimitId = 'otp_resend_' . ($_SERVER['REMOTE_ADDR'] ?? '');
        if (!checkRateLimit($rateLimitId, 3, 300)) {
            jsonResponse(['success' => false, 'message' => 'Слишком много запросов'], 429);
        }

        $result = $otpModel->createOTP($email);
        if (!$result['success']) jsonResponse($result, 404);

        $plainCode = $result['code'] ?? '';
        $sendResult = $otpModel->sendCode($email, $plainCode, $method_type);

        jsonResponse([
            'success' => true,
            'message' => 'Новый код отправлен',
            'expires_in' => $result['expires_in'],
            'development_code' => $sendResult['development_code'] ?? $plainCode,
            'csrf_token' => generateCsrfToken()
        ]);
    }

    
    if ($action === 'check_user' && $method === 'POST') {
        $email = sanitize($input['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Неверный формат email'], 400);
        }

        $user = $userModel->findByEmail($email);
        if ($user) {
            jsonResponse([
                'success' => true,
                'exists' => true,
                'username' => $user['username'],
                'has_phone' => !empty($user['phone']),
            ]);
        }

        jsonResponse(['success' => true, 'exists' => false, 'message' => 'Пользователь не найден']);
    }

    jsonResponse(['success' => false, 'message' => 'Unknown action'], 404);

} catch (Exception $e) {
    
    ob_clean();
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
