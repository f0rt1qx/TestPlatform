<?php
/**
 * api/auth.php — регистрация, вход, выход
 */

require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$userModel = new UserModel();

// ─── REGISTER ─────────────────────────────────────────────────────────────────
if ($action === 'register' && $method === 'POST') {
    // CSRF проверка (опциональная для регистрации)
    if (!empty($input['csrf_token']) && !validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $username  = trim($input['username'] ?? '');
    $email     = trim($input['email'] ?? '');
    $password  = $input['password'] ?? '';
    $firstName = trim($input['first_name'] ?? '');
    $lastName  = trim($input['last_name'] ?? '');

    // Логирование для отладки
    error_log('[REGISTER] Получены данные: username=' . $username . ', email=' . $email);

    // Упрощенная валидация
    $errors = [];
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Имя пользователя: от 3 до 50 символов';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Пароль: минимум 8 символов';
    }
    if (!empty($username) && !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Имя пользователя: только латиница, цифры, _';
    }

    if ($errors) {
        error_log('[REGISTER] Ошибки валидации: ' . json_encode($errors));
        jsonResponse(['success' => false, 'errors' => $errors], 422);
    }

    if ($userModel->findByEmail($email)) {
        jsonResponse(['success' => false, 'message' => 'Email уже зарегистрирован'], 409);
    }
    if ($userModel->findByUsername($username)) {
        jsonResponse(['success' => false, 'message' => 'Имя пользователя занято'], 409);
    }

    try {
        $userId = $userModel->create([
            'username'   => $username,
            'email'      => $email,
            'password'   => $password,
            'first_name' => $firstName,
            'last_name'  => $lastName,
        ]);

        if (MAIL_ENABLED) {
            $token = $userModel->createEmailVerification($userId);
            // TODO: отправить email
        }

        $token = JWT::encode(['sub' => $userId, 'username' => $username, 'role' => 'student']);
        
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('auth_token', $token, [
            'expires' => time() + JWT_EXPIRE,
            'path' => '/',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        jsonResponse([
            'success' => true,
            'message' => 'Регистрация успешна',
            'token'   => $token,
            'user'    => ['id' => $userId, 'username' => $username, 'role' => 'student'],
            'csrf_token' => generateCsrfToken(),
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Ошибка регистрации: ' . $e->getMessage()], 500);
    }
}

// ─── LOGIN ────────────────────────────────────────────────────────────────────
if ($action === 'login' && $method === 'POST') {
    // CSRF проверка
    if (!validateCsrfToken($input['csrf_token'] ?? '')) {
        jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);
    }

    $login    = trim($input['login'] ?? '');
    $password = $input['password'] ?? '';

    if (!$login || !$password) {
        jsonResponse(['success' => false, 'message' => 'Введите логин и пароль'], 400);
    }

    // Rate limiting
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateKey = $ipAddress . ':' . $login;
    if (!checkRateLimit($rateKey, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
        jsonResponse(['success' => false, 'message' => 'Слишком много попыток входа. Попробуйте позже.'], 429);
    }

    // Ищем по email или username
    $user = filter_var($login, FILTER_VALIDATE_EMAIL)
        ? $userModel->findByEmail($login)
        : $userModel->findByUsername($login);

    // Добавляем задержку для защиты от timing attack
    if (!$user) {
        usleep(random_int(100000, 300000)); // 100-300ms
    }

    if (!$user || !$userModel->verifyPassword($password, $user['password_hash'])) {
        jsonResponse(['success' => false, 'message' => 'Неверный логин или пароль'], 401);
    }

    if ($user['is_blocked']) {
        jsonResponse(['success' => false, 'message' => 'Аккаунт заблокирован'], 403);
    }

    if (!$user['is_active']) {
        jsonResponse(['success' => false, 'message' => 'Аккаунт деактивирован'], 403);
    }

    // Сбрасываем rate limit после успешного входа
    unset($_SESSION['rate_limit:' . $rateKey]);

    $token = JWT::encode([
        'sub'      => $user['id'],
        'username' => $user['username'],
        'role'     => $user['role'],
    ]);

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    setcookie('auth_token', $token, [
        'expires' => time() + JWT_EXPIRE,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    jsonResponse([
        'success' => true,
        'token'   => $token,
        'csrf_token' => generateCsrfToken(),
        'user'    => [
            'id'         => $user['id'],
            'username'   => $user['username'],
            'email'      => $user['email'],
            'role'       => $user['role'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
        ],
    ]);
}

// ─── LOGOUT ───────────────────────────────────────────────────────────────────
if ($action === 'logout' && $method === 'POST') {
    setcookie('auth_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    jsonResponse(['success' => true, 'message' => 'Вы вышли из системы']);
}

// ─── ME (текущий пользователь) ───────────────────────────────────────────────
if ($action === 'me' && $method === 'GET') {
    $payload = AuthMiddleware::require();
    $user = $userModel->findById($payload['sub']);
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Пользователь не найден'], 404);
    }

    unset($user['password_hash']);
    jsonResponse(['success' => true, 'user' => $user, 'csrf_token' => generateCsrfToken()]);
}

// ─── GET CSRF TOKEN ──────────────────────────────────────────────────────────
if ($action === 'csrf_token' && $method === 'GET') {
    jsonResponse(['success' => true, 'csrf_token' => generateCsrfToken()]);
}

jsonResponse(['success' => false, 'message' => 'Unknown action'], 404);
