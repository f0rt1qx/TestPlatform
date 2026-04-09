<?php

class JWT {

    public static function encode(array $tokenBody, ?string $signingKey = null, int $ttl = 0): string {
        $signingKey ??= JWT_SECRET;
        $ttl = $ttl > 0 ? $ttl : JWT_EXPIRE;

        $headerSegment = self::base64urlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

        $tokenBody['iat'] = time();
        $tokenBody['exp'] = time() + $ttl;
        $tokenBody['jti'] = bin2hex(random_bytes(8));

        $payloadSegment = self::base64urlEncode(json_encode($tokenBody));

        $sigSegment = self::computeSignature($headerSegment . '.' . $payloadSegment, $signingKey);

        return $headerSegment . '.' . $payloadSegment . '.' . $sigSegment;
    }

    public static function decode(string $encodedToken, ?string $signingKey = null): array {
        $signingKey ??= JWT_SECRET;

        $segments = explode('.', $encodedToken);
        count($segments) !== 3 && throw new RuntimeException('Invalid token structure');

        [$hdrB64, $bodyB64, $sigB64] = $segments;

        $expectedSig = self::computeSignature($hdrB64 . '.' . $bodyB64, $signingKey);
        !hash_equals($expectedSig, $sigB64) && throw new RuntimeException('Invalid token signature');

        $decodedBody = json_decode(self::base64urlDecode($bodyB64), true);
        !$decodedBody && throw new RuntimeException('Invalid token payload');

        $exp = $decodedBody['exp'] ?? null;
        isset($exp) && $exp < time() && throw new RuntimeException('Token expired');

        return $decodedBody;
    }

    public static function getPayload(string $encodedToken): ?array {
        $segments = explode('.', $encodedToken);
        if (count($segments) !== 3) return null;
        return json_decode(self::base64urlDecode($segments[1]), true) ?: null;
    }

    private static function computeSignature(string $rawData, string $signingKey): string {
        return self::base64urlEncode(hash_hmac('sha256', $rawData, $signingKey, true));
    }

    private static function base64urlEncode(string $raw): string {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $encoded): string {
        return base64_decode(strtr($encoded, '-_', '+/') . str_repeat('=', (4 - strlen($encoded) % 4) % 4));
    }
}
