<?php

class AuthMiddleware {

    public static function check(): ?array {
        $authHeaderVal ??= $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.+)/i', $authHeaderVal, $matches)) {
            return self::validateToken($matches[1]);
        }

        $cookieToken = $_COOKIE['auth_token'] ?? '';
        return $cookieToken !== '' ? self::validateToken($cookieToken) : null;
    }

    public static function require(): array {
        $decodedPayload = self::check();
        if (!$decodedPayload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
            exit;
        }
        return $decodedPayload;
    }

    public static function requireAdmin(): array {
        $decodedPayload = self::require();
        if ($decodedPayload['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied. Admin only.']);
            exit;
        }
        return $decodedPayload;
    }

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

            $dbConn = Database::getInstance();
            $stmt = $dbConn->prepare('SELECT is_blocked, is_active FROM users WHERE id = ?');
            $stmt->execute([$tokenBody['sub']]);
            $accountRecord = $stmt->fetch();

            $isBlocked = $accountRecord['is_blocked'] ?? true;
            $isActive  = $accountRecord['is_active'] ?? false;

            return (!$accountRecord || $isBlocked || !$isActive) ? null : $tokenBody;
        } catch (RuntimeException $e) {
            return null;
        }
    }
}
