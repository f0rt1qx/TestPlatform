<?php

require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$userModel = new UserModel();

match (true) {
    $action === 'register' && $method === 'POST' => (function () use ($input, $userModel): void {
        !empty($input['csrf_token']) && !validateCsrfToken($input['csrf_token']) && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $username  = trim($input['username'] ?? '');
        $email     = trim($input['email'] ?? '');
        $password  = $input['password'] ?? '';
        $firstName = trim($input['first_name'] ?? '');
        $lastName  = trim($input['last_name'] ?? '');

        error_log('[REGISTER] Получены данные: username=' . $username . ', email=' . $email);

        strlen($username) < 3 || strlen($username) > 50 && jsonResponse(['success' => false, 'errors' => ['Имя пользователя: от 3 до 50 символов']], 422);
        !filter_var($email, FILTER_VALIDATE_EMAIL) && jsonResponse(['success' => false, 'errors' => ['Некорректный email']], 422);
        strlen($password) < 8 && jsonResponse(['success' => false, 'errors' => ['Пароль: минимум 8 символов']], 422);
        !preg_match('/^[a-zA-Z0-9_]+$/', $username) && jsonResponse(['success' => false, 'errors' => ['Имя пользователя: только латиница, цифры, _']], 422);

        $userModel->findByEmail($email) && jsonResponse(['success' => false, 'message' => 'Email уже зарегистрирован'], 409);
        $userModel->findByUsername($username) && jsonResponse(['success' => false, 'message' => 'Имя пользователя занято'], 409);

        try {
            $userId = $userModel->create([
                'username'   => $username,
                'email'      => $email,
                'password'   => $password,
                'first_name' => $firstName,
                'last_name'  => $lastName,
            ]);

            MAIL_ENABLED && $userModel->createEmailVerification($userId);

            $token = JWT::encode(['sub' => $userId, 'username' => $username, 'role' => 'student']);

            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            setcookie('auth_token', $token, [
                'expires'  => time() + JWT_EXPIRE,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Strict',
            ]);

            jsonResponse([
                'success'    => true,
                'message'    => 'Регистрация успешна',
                'token'      => $token,
                'user'       => ['id' => $userId, 'username' => $username, 'role' => 'student'],
                'csrf_token' => generateCsrfToken(),
            ]);
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'message' => 'Ошибка регистрации: ' . $e->getMessage()], 500);
        }
    })(),

    $action === 'login' && $method === 'POST' => (function () use ($input, $userModel): void {
        !validateCsrfToken($input['csrf_token'] ?? '') && jsonResponse(['success' => false, 'message' => 'CSRF token invalid'], 403);

        $login    = trim($input['login'] ?? '');
        $password = $input['password'] ?? '';

        (!$login || !$password) && jsonResponse(['success' => false, 'message' => 'Введите логин и пароль'], 400);

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateKey   = $ipAddress . ':' . $login;
        !checkRateLimit($rateKey, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME) && jsonResponse(['success' => false, 'message' => 'Слишком много попыток входа. Попробуйте позже.'], 429);

        $user = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? $userModel->findByEmail($login)
            : $userModel->findByUsername($login);

        !$user && usleep(random_int(100000, 300000));
        (!$user || !$userModel->verifyPassword($password, $user['password_hash'])) && jsonResponse(['success' => false, 'message' => 'Неверный логин или пароль'], 401);
        $user['is_blocked'] && jsonResponse(['success' => false, 'message' => 'Аккаунт заблокирован'], 403);
        !$user['is_active'] && jsonResponse(['success' => false, 'message' => 'Аккаунт деактивирован'], 403);

        unset($_SESSION['rate_limit:' . $rateKey]);

        $token = JWT::encode([
            'sub'      => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);

        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        setcookie('auth_token', $token, [
            'expires'  => time() + JWT_EXPIRE,
            'path'     => '/',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        jsonResponse([
            'success'    => true,
            'token'      => $token,
            'csrf_token' => generateCsrfToken(),
            'user'       => [
                'id'         => $user['id'],
                'username'   => $user['username'],
                'email'      => $user['email'],
                'role'       => $user['role'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
            ],
        ]);
    })(),

    $action === 'logout' && $method === 'POST' => (function (): void {
        setcookie('auth_token', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        jsonResponse(['success' => true, 'message' => 'Вы вышли из системы']);
    })(),

    $action === 'me' && $method === 'GET' => (function () use ($userModel): void {
        $payload = AuthMiddleware::require();
        $user = $userModel->findById($payload['sub']);
        !$user && jsonResponse(['success' => false, 'message' => 'Пользователь не найден'], 404);
        unset($user['password_hash']);
        jsonResponse(['success' => true, 'user' => $user, 'csrf_token' => generateCsrfToken()]);
    })(),

    $action === 'csrf_token' && $method === 'GET' => (fn() =>
        jsonResponse(['success' => true, 'csrf_token' => generateCsrfToken()])
    )(),

    default => jsonResponse(['success' => false, 'message' => 'Unknown action'], 404),
};
