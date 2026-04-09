<?php


class AuthMiddleware {

    public static function check(): ?array {
        $authHeaderVal = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $authHeaderVal, $matches)) {
            return self::validateToken($matches[1]);
        }

        if (!empty($_COOKIE['auth_token'])) {
            return self::validateToken($_COOKIE['auth_token']);
        }

        return null;
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
            if (!$accountRecord || $accountRecord['is_blocked'] || !$accountRecord['is_active']) {
                return null;
            }
            return $tokenBody;
        } catch (RuntimeException $e) {
            return null;
        }
    }
}
