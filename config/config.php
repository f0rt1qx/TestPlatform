<?php

// DB defaults:
// - localhost/XAMPP -> local MySQL
// - InfinityFree hosting -> hosting MySQL
// Any DB_* environment variable overrides these defaults.
$httpHost = strtolower($_SERVER['HTTP_HOST'] ?? '');
$isCli = PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
$isLocalHost = $isCli
    || $httpHost === ''
    || in_array($httpHost, ['localhost', '127.0.0.1', '::1'], true)
    || str_starts_with($httpHost, 'localhost:');

$defaultDbHost = 'localhost';
$defaultDbPort = '3306';
$defaultDbName = 'test_platform';
$defaultDbUser = 'root';
$defaultDbPass = '';

// On any non-local host use hosting DB by default.
if (!$isLocalHost) {
    $defaultDbHost = 'sql100.infinityfree.com';
    $defaultDbPort = '3306';
    $defaultDbName = 'if0_41654195_testplatformdbb';
    $defaultDbUser = 'if0_41654195';
    $defaultDbPass = 'gAW3XYQbaw';
}

define('DB_HOST',    getenv('DB_HOST')    ?: $defaultDbHost);
define('DB_PORT',    getenv('DB_PORT')    ?: $defaultDbPort);
define('DB_NAME',    getenv('DB_NAME')    ?: $defaultDbName);
define('DB_USER',    getenv('DB_USER')    ?: $defaultDbUser);
define('DB_PASS',    getenv('DB_PASS')    ?: $defaultDbPass);
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

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

define('APP_NAME',  'Sapienta');

$appEnv = getenv('APP_ENV') ?: 'development';
$appDebugEnv = getenv('APP_DEBUG');
$appDebug = $appDebugEnv !== false
    ? (filter_var($appDebugEnv, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false)
    : false;

define('APP_ENV',   $appEnv);
define('APP_DEBUG', $appDebug);

$secretFile = __DIR__ . '/secret.php';
$jwtSecretFromEnv = getenv('JWT_SECRET') ?: '';
if ($jwtSecretFromEnv !== '') {
    define('JWT_SECRET', $jwtSecretFromEnv);
} else {
    if (file_exists($secretFile)) {
        require_once $secretFile;
    }
    if (!defined('JWT_SECRET')) {
        // Stable fallback for environments where secret.php cannot be written.
        // Override with env JWT_SECRET on production for best security.
        $stableFallback = hash('sha256', DB_HOST . '|' . DB_NAME . '|' . DB_USER . '|sapienta-jwt-v1');
        define('JWT_SECRET', $stableFallback);

        $secretPhp = "<?php\n// JWT SECRET (generated fallback)\ndefine('JWT_SECRET', '$stableFallback');\n";
        @file_put_contents($secretFile, $secretPhp);
        @chmod($secretFile, 0600);
    }
}
define('JWT_EXPIRE',         3600 * 24);
define('JWT_REFRESH_EXPIRE', 3600 * 24 * 7);

$mailHost = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
$mailPort = (int)(getenv('MAIL_PORT') ?: 587);
$mailUser = getenv('MAIL_USER') ?: '';
$mailPass = getenv('MAIL_PASS') ?: '';
$mailFrom = getenv('MAIL_FROM') ?: ($mailUser ?: 'no-reply@localhost');
$mailFromName = getenv('MAIL_FROM_NAME') ?: APP_NAME;

$mailEnabledEnv = getenv('MAIL_ENABLED');
$mailEnabled = $mailEnabledEnv !== false
    ? (filter_var($mailEnabledEnv, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false)
    : false;

// Never enable SMTP if credentials are missing.
if ($mailEnabled && ($mailUser === '' || $mailPass === '')) {
    $mailEnabled = false;
}

define('MAIL_HOST',      $mailHost);
define('MAIL_PORT',      $mailPort);
define('MAIL_USER',      $mailUser);
define('MAIL_PASS',      $mailPass);
define('MAIL_FROM',      $mailFrom);
define('MAIL_FROM_NAME', $mailFromName);
define('MAIL_ENABLED',   $mailEnabled);

define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET',   '');
define('RECAPTCHA_ENABLED',  false);

define('BCRYPT_COST',        12);
define('SESSION_LIFETIME',   3600 * 2);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

define('ANTICHEAT_TAB_SWITCH_WARN',  2);
define('ANTICHEAT_TAB_SWITCH_MAX',   5);
define('ANTICHEAT_RAPID_ANSWER_SEC', 3);
define('ANTICHEAT_CHEAT_THRESHOLD',  40);

define('ROOT_PATH',   dirname(__DIR__));
define('SRC_PATH',    ROOT_PATH . '/src');
define('VIEW_PATH',   ROOT_PATH . '/views');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('PUBLIC_PATH', ROOT_PATH . '/public');
