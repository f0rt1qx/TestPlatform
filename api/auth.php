<?php

require_once __DIR__ . '/../src/bootstrap.php';

setCORSHeaders();
setSecurityHeaders();
header('Content-Type: application/json; charset=utf-8');

/**
 * Custom exception hierarchy — each type maps to a different HTTP status code.
 */
class ValidationException extends InvalidArgumentException {
    public function getHttpCode(): int { return 422; }
}

class AuthException extends RuntimeException {
    protected int $httpCode = 401;
    public function getHttpCode(): int { return $this->httpCode; }
}

class ConflictException extends RuntimeException {
    public function getHttpCode(): int { return 409; }
}

class RateLimitException extends RuntimeException {
    public function getHttpCode(): int { return 429; }
}

class CSRFException extends RuntimeException {
    public function getHttpCode(): int { return 403; }
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input  = json_decode(file_get_contents('php://input'), true) ?? [];

$userModel = new UserModel();

try {
    match (true) {
        $action === 'register' && $method === 'POST' => (function () use ($input, $userModel): void {
            !empty($input['csrf_token']) && !validateCsrfToken($input['csrf_token']) && throw new CSRFException('CSRF token invalid');

            $username  = trim($input['username'] ?? '');
            $email     = trim($input['email'] ?? '');
            $password  = $input['password'] ?? '';
            $firstName = trim($input['first_name'] ?? '');
            $lastName  = trim($input['last_name'] ?? '');

            error_log('[REGISTER] Получены данные: username=' . $username . ', email=' . $email);

            if (strlen($username) < 3 || strlen($username) > 50) {
                throw new ValidationException('Имя пользователя: от 3 до 50 символов');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ValidationException('Некорректный email');
            }
            if (strlen($password) < 8) {
                throw new ValidationException('Пароль: минимум 8 символов');
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                throw new ValidationException('Имя пользователя: только латиница, цифры, _');
            }

            $userModel->findByEmail($email) && throw new ConflictException('Email уже зарегистрирован');
            $userModel->findByUsername($username) && throw new ConflictException('Имя пользователя занято');

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
            // CSRF validation is intentionally skipped for login
            // since it's an unauthenticated endpoint (no session yet)

            $login    = trim($input['login'] ?? '');
            $password = $input['password'] ?? '';

            if (!$login || !$password) {
                throw new AuthException('Введите логин и пароль');
            }

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $rateKey   = $ipAddress . ':' . $login;
            if (!checkRateLimit($rateKey, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME)) {
                throw new RateLimitException('Слишком много попыток входа. Попробуйте позже.');
            }

            $user = filter_var($login, FILTER_VALIDATE_EMAIL)
                ? $userModel->findByEmail($login)
                : $userModel->findByUsername($login);

            if (!$user) {
                usleep(random_int(100000, 300000));
                throw new AuthException('Неверный логин или пароль');
            }
            if (!$userModel->verifyPassword($password, $user['password_hash'])) {
                throw new AuthException('Неверный логин или пароль');
            }
            if ($user['is_blocked']) {
                $ex = new AuthException('Аккаунт заблокирован');
                $ex->httpCode = 403;
                throw $ex;
            }
            if (!$user['is_active']) {
                $ex = new AuthException('Аккаунт деактивирован');
                $ex->httpCode = 403;
                throw $ex;
            }

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
            if (!$user) {
                throw new AuthException('Пользователь не найден');
            }
            unset($user['password_hash']);
            jsonResponse(['success' => true, 'user' => $user, 'csrf_token' => generateCsrfToken()]);
        })(),

        $action === 'csrf_token' && $method === 'GET' => (fn() =>
            jsonResponse(['success' => true, 'csrf_token' => generateCsrfToken()])
        )(),

        default => jsonResponse(['success' => false, 'message' => 'Unknown action'], 404),
    };
} catch (UnauthenticatedException $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], (int)$e->getCode());
} catch (ValidationException $e) {
    jsonResponse(['success' => false, 'errors' => [$e->getMessage()]], $e->getHttpCode());
} catch (AuthException $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], $e->getHttpCode());
} catch (ConflictException $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], $e->getHttpCode());
} catch (RateLimitException $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], $e->getHttpCode());
} catch (CSRFException $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], $e->getHttpCode());
}
