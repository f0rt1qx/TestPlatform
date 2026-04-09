<?php
/**
 * Database.php — PDO singleton-соединение с MySQL
 */

class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT         => true, // Persistent connections для производительности
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                $errorMsg = 'Database connection failed. Please try again later.';
                
                // Логирование ошибки
                $logFile = __DIR__ . '/../../logs/error.log';
                if (is_writable(dirname($logFile))) {
                    error_log(date('Y-m-d H:i:s') . ' - DB Connection Error: ' . $e->getMessage() . PHP_EOL, 3, $logFile);
                }
                
                if (APP_DEBUG) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    die(json_encode([
                        'error' => 'DB Connection failed',
                        'details' => $e->getMessage(),
                        'code' => $e->getCode()
                    ]));
                }
                
                http_response_code(500);
                header('Content-Type: application/json');
                die(json_encode(['error' => $errorMsg]));
            }
        }
        return self::$instance;
    }

    // Запрещаем клонирование
    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
