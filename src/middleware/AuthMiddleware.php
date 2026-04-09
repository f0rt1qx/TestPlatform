<?php
/**
 * AuthMiddleware.php — Проверка JWT + сессии
 */

class AuthMiddleware {

    /**
     * Проверить авторизацию. Вернуть payload или null.
     */
    public static function check(): ?array {
        // 1. Из заголовка Authorization: Bearer <token>
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.+)/i', $authHeader, $m)) {
            return self::validateToken($m[1]);
        }

        // 2. Из куки (для SSR страниц)
        if (!empty($_COOKIE['auth_token'])) {
            return self::validateToken($_COOKIE['auth_token']);
        }

        return null;
    }

    /**
     * Требовать авторизацию, иначе вернуть 401 JSON
     */
    public static function require(): array {
        $payload = self::check();
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
            exit;
        }
        return $payload;
    }

    /**
     * Требовать роль admin
     */
    public static function requireAdmin(): array {
        $payload = self::require();
        if ($payload['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied. Admin only.']);
            exit;
        }
        return $payload;
    }

    /**
     * Для страниц (не API) — редирект вместо JSON
     */
    public static function requirePage(string $role = 'student'): array {
        $payload = self::check();
        if (!$payload) {
            header('Location: ' . APP_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        if ($role === 'admin' && $payload['role'] !== 'admin') {
            header('Location: ' . APP_URL . '/dashboard.php');
            exit;
        }
        return $payload;
    }

    private static function validateToken(string $token): ?array {
        try {
            $payload = JWT::decode($token);
            // Дополнительно проверяем, что пользователь не заблокирован
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT is_blocked, is_active FROM users WHERE id = ?');
            $stmt->execute([$payload['sub']]);
            $user = $stmt->fetch();
            if (!$user || $user['is_blocked'] || !$user['is_active']) {
                return null;
            }
            return $payload;
        } catch (RuntimeException $e) {
            return null;
        }
    }
}
