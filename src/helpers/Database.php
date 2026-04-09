<?php

/**
 * Custom exception that wraps PDO exceptions with a cause chain.
 * Preserves the original PDOException as the previous exception.
 */
class DbException extends RuntimeException {
    private ?string $sqlState;
    private array $context;

    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
        ?string $sqlState = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->sqlState = $sqlState;
        $this->context  = $context;
    }

    public function getSqlState(): ?string {
        return $this->sqlState;
    }

    public function getContext(): array {
        return $this->context;
    }

    public function getChain(): array {
        $chain = [];
        $exc = $this;
        while ($exc !== null) {
            $chain[] = [
                'class'   => get_class($exc),
                'message' => $exc->getMessage(),
                'code'    => $exc->getCode(),
                'file'    => $exc->getFile(),
                'line'    => $exc->getLine(),
            ];
            $exc = $exc->getPrevious();
        }
        return $chain;
    }
}

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

            $tempConnection = null;
            try {
                $tempConnection = new PDO(
                    dsn: $connectionString,
                    username: DB_USER,
                    password: DB_PASS,
                    options: $pdoOptions,
                );

                // Successfully created — assign to singleton
                self::$singletonInstance = $tempConnection;
            } catch (PDOException $dbException) {
                // Build context info for debugging
                $context = [
                    'host' => DB_HOST,
                    'port' => DB_PORT,
                    'dbname' => DB_NAME,
                    'user' => DB_USER,
                ];

                // Wrap in DbException with cause chain
                $wrapped = new DbException(
                    'Database connection failed',
                    $dbException->getCode(),
                    $dbException,  // <-- cause chain: DbException -> PDOException
                    $dbException->errorInfo[0] ?? null,
                    $context
                );

                $errorLogPath = __DIR__ . '/../../logs/error.log';
                $logEntry = date('Y-m-d H:i:s') . ' - DB Connection Error: ' . $wrapped->getMessage()
                    . ' | Chain: ' . json_encode($wrapped->getChain(), JSON_UNESCAPED_UNICODE) . PHP_EOL;

                try {
                    if (is_writable(dirname($errorLogPath))) {
                        error_log($logEntry, 3, $errorLogPath);
                    }
                } finally {
                    // Cleanup: ensure no dangling connection
                    $tempConnection = null;
                }

                if (APP_DEBUG) {
                    http_response_code(500);
                    header('Content-Type: application/json');
                    die(json_encode([
                        'error'   => 'DB Connection failed',
                        'details' => $wrapped->getMessage(),
                        'code'    => $wrapped->getCode(),
                        'chain'   => $wrapped->getChain(),
                        'context' => $wrapped->getContext(),
                    ], JSON_UNESCAPED_UNICODE));
                }

                http_response_code(500);
                header('Content-Type: application/json');
                die(json_encode(['error' => 'Database connection failed. Please try again later.'], JSON_UNESCAPED_UNICODE));
            } finally {
                // Additional cleanup: clear temp reference if something went wrong
                if (self::$singletonInstance === null && $tempConnection !== null) {
                    $tempConnection = null;
                }
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
