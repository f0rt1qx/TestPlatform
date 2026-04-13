<?php



define('DB_HOST',    getenv('DB_HOST')    ?: 'sql100.infinityfree.com');
define('DB_PORT',    getenv('DB_PORT')    ?: '3306');
define('DB_NAME',    getenv('DB_NAME')    ?: 'if0_41654195_testplatformdb');
define('DB_USER',    getenv('DB_USER')    ?: 'if0_41654195');
define('DB_PASS',    getenv('DB_PASS')    ?: 'gAW3XYQbaw');
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
define('APP_ENV',   'development');
define('APP_DEBUG', true);



$secretFile = __DIR__ . '/secret.php';
if (file_exists($secretFile)) {
    require_once $secretFile;
}
if (!defined('JWT_SECRET')) {
    $generatedSecret = bin2hex(random_bytes(32));
    define('JWT_SECRET', $generatedSecret);
    @file_put_contents($secretFile, "<?php\n// AUTO-GENERATED JWT SECRET - DO NOT SHARE\n// Сгенерировано: " . date('Y-m-d H:i:s') . "\ndefine('JWT_SECRET', '$generatedSecret');\n");
    @chmod($secretFile, 0600);
}
define('JWT_EXPIRE',         3600 * 24);        
define('JWT_REFRESH_EXPIRE', 3600 * 24 * 7);   


define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',      587);
define('MAIL_USER',      'flaymov09@gmail.com');
define('MAIL_PASS',      'rjcedeuzbzpjvrut');
define('MAIL_FROM',      'flaymov09@gmail.com');
define('MAIL_FROM_NAME', APP_NAME);
define('MAIL_ENABLED',   true);     


define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET',   '');
define('RECAPTCHA_ENABLED',  false);


define('BCRYPT_COST',       12);
define('SESSION_LIFETIME',  3600 * 2);
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
