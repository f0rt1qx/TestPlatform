<?php

class Database {
    private static ?PDO $singletonInstance = null;

    public static function getInstance(): PDO {
        if (self::$singletonInstance === null) {
            $connectionString = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );

            $pdoOptions = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                PDO::ATTR_PERSISTENT         => true,
            ];

            try {
                self::$singletonInstance = new PDO(
                    dsn: $connectionString,
                    username: DB_USER,
                    password: DB_PASS,
                    options: $pdoOptions,
                );
            } catch (PDOException $dbException) {
                $fallbackMsg = 'Database connection failed. Please try again later.';

                $errorLogPath = __DIR__ . '/../../logs/error.log';
                is_writable(dirname($errorLogPath)) && error_log(
                    date('Y-m-d H:i:s') . ' - DB Connection Error: ' . $dbException->getMessage() . PHP_EOL,
                    3,
                    $errorLogPath,
                );

                if (APP_DEBUG) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    die(json_encode([
                        'error'   => 'DB Connection failed',
                        'details' => $dbException->getMessage(),
                        'code'    => $dbException->getCode(),
                    ], JSON_UNESCAPED_UNICODE));
                }

                http_response_code(500);
                header('Content-Type: application/json');
                die(json_encode(['error' => $fallbackMsg], JSON_UNESCAPED_UNICODE));
            }
        }
        return self::$singletonInstance;
    }

    private function __construct() {}
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
