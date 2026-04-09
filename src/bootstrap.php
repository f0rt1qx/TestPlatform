<?php
/**
 * bootstrap.php — загрузка конфигурации и всех классов
 */

// Подключаем конфиг
require_once __DIR__ . '/../config/config.php';

// Устанавливаем часовой пояс
date_default_timezone_set('Europe/Moscow');

// Обработка ошибок
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
}

// Автозагрузчик классов
spl_autoload_register(function (string $class): void {
    $paths = [
        SRC_PATH . '/helpers/' . $class . '.php',
        SRC_PATH . '/models/' . $class . '.php',
        SRC_PATH . '/controllers/' . $class . '.php',
        SRC_PATH . '/middleware/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// Заголовки безопасности
function setSecurityHeaders(): void {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.gstatic.com; font-src https://fonts.gstatic.com; connect-src 'self' https://api.vk.com;");
}

// CORS для API
function setCORSHeaders(): void {
    $allowedOrigin = APP_URL;
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if ($origin === $allowedOrigin) {
        header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Хелпер: JSON-ответ
function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Хелпер: sanity-check входных данных
function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// Rate limiting
function checkRateLimit(string $identifier, int $maxAttempts, int $lockTime): bool {
    $key = 'rate_limit:' . $identifier;
    $attempts = (int)($_SESSION[$key]['count'] ?? 0);
    $resetTime = $_SESSION[$key]['reset'] ?? 0;
    
    if (time() > $resetTime) {
        $_SESSION[$key] = ['count' => 1, 'reset' => time() + $lockTime];
        return true;
    }
    
    if ($attempts >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$key]['count'] = $attempts + 1;
    return true;
}

// CSRF токен
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
