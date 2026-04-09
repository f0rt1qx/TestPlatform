<?php

/**
 * JWT helper with differentiated exception types:
 * - InvalidArgumentException for structural/payload issues
 * - RuntimeException for signature mismatches and expiration
 *
 * Also provides validate() that returns bool instead of throwing.
 */
class JWT {

    public static function encode(array $tokenBody, ?string $signingKey = null, int $ttl = 0): string {
        if (empty($tokenBody)) {
            throw new InvalidArgumentException('Token body must not be empty');
        }
        if (isset($tokenBody['sub']) && !is_string($tokenBody['sub']) && !is_int($tokenBody['sub'])) {
            throw new InvalidArgumentException('Token subject (sub) must be string or int');
        }

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

        // InvalidArgumentException for structural problems
        $segments = explode('.', $encodedToken);
        if (count($segments) !== 3) {
            throw new InvalidArgumentException('Invalid token structure: expected 3 segments, got ' . count($segments));
        }

        [$hdrB64, $bodyB64, $sigB64] = $segments;

        // RuntimeException for signature mismatch
        $expectedSig = self::computeSignature($hdrB64 . '.' . $bodyB64, $signingKey);
        if (!hash_equals($expectedSig, $sigB64)) {
            throw new RuntimeException('Invalid token signature');
        }

        // InvalidArgumentException for malformed payload
        $decodedBody = json_decode(self::base64urlDecode($bodyB64), true);
        if (!$decodedBody) {
            throw new InvalidArgumentException('Invalid token payload: cannot decode');
        }

        // RuntimeException for expiration
        $exp = $decodedBody['exp'] ?? null;
        if (isset($exp) && $exp < time()) {
            throw new RuntimeException('Token expired');
        }

        return $decodedBody;
    }

    /**
     * Validate without throwing — returns bool.
     * Useful for silent checks where you don't need to know WHY it failed.
     */
    public static function validate(string $encodedToken, ?string $signingKey = null): bool {
        $signingKey ??= JWT_SECRET;

        $segments = explode('.', $encodedToken);
        if (count($segments) !== 3) return false;

        [$hdrB64, $bodyB64, $sigB64] = $segments;

        $expectedSig = self::computeSignature($hdrB64 . '.' . $bodyB64, $signingKey);
        if (!hash_equals($expectedSig, $sigB64)) return false;

        $decodedBody = json_decode(self::base64urlDecode($bodyB64), true);
        if (!$decodedBody) return false;

        $exp = $decodedBody['exp'] ?? null;
        if (isset($exp) && $exp < time()) return false;

        return true;
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
