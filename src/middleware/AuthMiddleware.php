<?php

/**
 * Thrown when user is not authenticated at all.
 */
class UnauthenticatedException extends RuntimeException {
    public function __construct(string $message = 'Unauthorized. Please login.') {
        parent::__construct($message, 401);
    }
}

/**
 * Thrown specifically when user lacks admin role.
 */
class AdminException extends RuntimeException {
    public function __construct(string $message = 'Access denied. Admin only.') {
        parent::__construct($message, 403);
    }
}

class AuthMiddleware {

    /**
     * check() — silent: returns null on any failure.
     * Never throws, never outputs. Just payload or null.
     */
    public static function check(): ?array {
        $authHeaderVal = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.+)/i', $authHeaderVal, $matches)) {
            $headerPayload = self::validateToken($matches[1]);
            if ($headerPayload) {
                return $headerPayload;
            }
        }

        // Prefer server-side session for page/API consistency.
        $sessionPayload = self::validateSessionUser();
        if ($sessionPayload) {
            return $sessionPayload;
        }

        $cookieToken = $_COOKIE['auth_token'] ?? '';
        if ($cookieToken !== '') {
            $cookiePayload = self::validateToken($cookieToken);
            if ($cookiePayload) {
                return $cookiePayload;
            }
        }

        return null;
    }

    /**
     * require() — throws UnauthenticatedException on failure.
     * Different from check(): it enforces authentication with an exception.
     */
    public static function require(): array {
        $decodedPayload = self::check();
        if (!$decodedPayload) {
            throw new UnauthenticatedException('Unauthorized. Please login.');
        }
        return $decodedPayload;
    }

    /**
     * requireAdmin() — throws AdminException (a dedicated exception class)
     * if the authenticated user is not an admin.
     */
    public static function requireAdmin(): array {
        $decodedPayload = self::require();  // may throw UnauthenticatedException
        if ($decodedPayload['role'] !== 'admin') {
            throw new AdminException('Access denied. Admin only.');
        }
        return $decodedPayload;
    }

    /**
     * requirePage() — for PHP page redirects (not API).
     * Redirects instead of throwing.
     */
    public static function requirePage(string $requiredRole = 'student'): array {
        $decodedPayload = self::check();
        if (!$decodedPayload) {
            header('Location: ' . APP_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        if ($requiredRole === 'admin' && $decodedPayload['role'] !== 'admin') {
            header('Location: ' . APP_URL . '/dashboard.php');
            exit;
        }
        return $decodedPayload;
    }

    private static function validateToken(string $rawToken): ?array {
        try {
            $tokenBody = JWT::decode($rawToken);
        } catch (RuntimeException $e) {
            return null;
        }

        $dbConn = Database::getInstance();
        $stmt = $dbConn->prepare('SELECT is_blocked, is_active FROM users WHERE id = ?');
        $stmt->execute([$tokenBody['sub']]);
        $accountRecord = $stmt->fetch();

        $isBlocked = $accountRecord['is_blocked'] ?? true;
        $isActive  = $accountRecord['is_active'] ?? false;

        return (!$accountRecord || $isBlocked || !$isActive) ? null : $tokenBody;
    }

    private static function validateSessionUser(): ?array {
        $sessionUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($sessionUserId <= 0) {
            return null;
        }

        $dbConn = Database::getInstance();
        $stmt = $dbConn->prepare('SELECT id, username, role, is_blocked, is_active FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$sessionUserId]);
        $user = $stmt->fetch();

        if (!$user || (int)$user['is_blocked'] === 1 || (int)$user['is_active'] !== 1) {
            return null;
        }

        return [
            'sub' => (int)$user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
    }
}
