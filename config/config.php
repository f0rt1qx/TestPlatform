<?php
/**
 * config.php — основной конфигурационный файл
 * XAMPP: C:\xampp\htdocs\test-platform\config\config.php
 */

// ── База данных ───────────────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'test_platform');
define('DB_USER',    'root');
define('DB_PASS',    '');           // XAMPP default — пустой пароль
define('DB_CHARSET', 'utf8mb4');

// ── URL приложения (авто-определение) ────────────────────────────────────────
if (!defined('APP_URL')) {
    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot  = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\');
    $projRoot = rtrim(dirname(dirname(__FILE__)), '/\\');

    $subPath  = '';
    if ($docRoot && strpos($projRoot, $docRoot) === 0) {
        $subPath = str_replace('\\', '/', substr($projRoot, strlen($docRoot)));
    }

    define('APP_URL', $scheme . '://' . $host . rtrim($subPath, '/'));
}

// ── Приложение ────────────────────────────────────────────────────────────────
define('APP_NAME',  'TestPlatform');
define('APP_ENV',   'development');
define('APP_DEBUG', true);

// ── JWT ───────────────────────────────────────────────────────────────────────
// Генерируем уникальный секрет если файл secret.php не существует
$secretFile = __DIR__ . '/secret.php';
if (file_exists($secretFile)) {
    require_once $secretFile;
}
if (!defined('JWT_SECRET')) {
    // Генерируем новый секрет и сохраняем в файл
    $generatedSecret = bin2hex(random_bytes(32));
    define('JWT_SECRET', $generatedSecret);
    // Сохраняем в файл для постоянства
    file_put_contents($secretFile, "<?php\n// AUTO-GENERATED JWT SECRET - DO NOT SHARE\n// Сгенерировано: " . date('Y-m-d H:i:s') . "\ndefine('JWT_SECRET', '$generatedSecret');\n");
    chmod($secretFile, 0600);
}
define('JWT_EXPIRE',         3600 * 24);        // 24 часа
define('JWT_REFRESH_EXPIRE', 3600 * 24 * 7);   // 7 дней

// ── Email ─────────────────────────────────────────────────────────────────────
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USER',      'flaymov09@gmail.com');
define('MAIL_PASS',      'rjcedeuzbzpjvrut');
define('MAIL_FROM',      'flaymov09@gmail.com');
define('MAIL_FROM_NAME', APP_NAME);
define('MAIL_ENABLED',   true);     // true = отправка через SMTP, false = показывать код на странице

// ── reCAPTCHA ─────────────────────────────────────────────────────────────────
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET',   '');
define('RECAPTCHA_ENABLED',  false);

// ── Безопасность ─────────────────────────────────────────────────────────────
define('BCRYPT_COST',       12);
define('SESSION_LIFETIME',  3600 * 2);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

// ── Анти-читинг ───────────────────────────────────────────────────────────────
define('ANTICHEAT_TAB_SWITCH_WARN',  2);
define('ANTICHEAT_TAB_SWITCH_MAX',   5);
define('ANTICHEAT_RAPID_ANSWER_SEC', 3);
define('ANTICHEAT_CHEAT_THRESHOLD',  40);

// ── Пути ──────────────────────────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('SRC_PATH',    ROOT_PATH . '/src');
define('VIEW_PATH',   ROOT_PATH . '/views');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PUBLIC_PATH', ROOT_PATH . '/public');
