<?php
namespace Wisp;

require_once __DIR__ . '/JwtMiddleware.php';

class Jwt {
    public static function encode(array $payload, ?string $secret = null, ?int $ttl = null): string {
        $secret = $secret ?? ($_ENV['JWT_SECRET'] ?? '');
        $ttl = $ttl ?? (int)($_ENV['JWT_TTL'] ?? 3600);

        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $now = time();
        $payload['iat'] = $payload['iat'] ?? $now;
        if ($ttl > 0) {
            $payload['exp'] = $payload['exp'] ?? ($now + $ttl);
        }

        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload)),
        ];
        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $token, ?string $secret = null): ?array {
        $secret = $secret ?? ($_ENV['JWT_SECRET'] ?? '');
        if ($secret === '') {
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = json_decode(self::base64UrlDecode($headerB64), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256') {
            return null;
        }

        $expected = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $secret, true);
        $actual = self::base64UrlDecode($signatureB64);
        if (!hash_equals($expected, $actual)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            return null;
        }

        if (isset($payload['exp']) && time() >= $payload['exp']) {
            return null;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// Extend Request: expose authenticated JWT claims via $req->user()
Request::macro('user', function () {
    return $this->getAttribute('user');
});
