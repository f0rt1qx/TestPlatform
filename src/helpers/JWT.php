<?php
/**
 * JWT.php — Простая реализация JWT без внешних библиотек
 * Алгоритм: HMAC-SHA256 (HS256)
 */

class JWT {

    /**
     * Создать JWT токен
     */
    public static function encode(array $payload, ?string $secret = null, int $expire = 0): string {
        $secret = $secret ?? JWT_SECRET;
        $expire = $expire > 0 ? $expire : JWT_EXPIRE;

        $header = self::base64url(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

        $payload['iat'] = time();
        $payload['exp'] = time() + $expire;
        $payload['jti'] = bin2hex(random_bytes(8));

        $payloadEncoded = self::base64url(json_encode($payload));

        $signature = self::sign($header . '.' . $payloadEncoded, $secret);

        return $header . '.' . $payloadEncoded . '.' . $signature;
    }

    /**
     * Декодировать и проверить JWT токен
     * @throws RuntimeException при невалидном токене
     */
    public static function decode(string $token, ?string $secret = null): array {
        $secret = $secret ?? JWT_SECRET;

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token structure');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // Проверяем подпись
        $expected = self::sign($headerB64 . '.' . $payloadB64, $secret);
        if (!hash_equals($expected, $signatureB64)) {
            throw new RuntimeException('Invalid token signature');
        }

        $payload = json_decode(self::base64urlDecode($payloadB64), true);
        if (!$payload) {
            throw new RuntimeException('Invalid token payload');
        }

        // Проверяем срок действия
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new RuntimeException('Token expired');
        }

        return $payload;
    }

    /**
     * Получить payload без проверки (для refresh логики)
     */
    public static function getPayload(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        $decoded = json_decode(self::base64urlDecode($parts[1]), true);
        return $decoded ?: null;
    }

    // ─── Приватные методы ─────────────────────────────────────────────────────

    private static function sign(string $data, string $secret): string {
        return self::base64url(hash_hmac('sha256', $data, $secret, true));
    }

    private static function base64url(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
