<?php

require_once __DIR__ . '/EnvHelper.php';

class JwtHelper
{
    public static function secret(): string
    {
        return (string) EnvHelper::get('JWT_SECRET', 'change-me-in-production');
    }

    public static function issuer(): string
    {
        return (string) EnvHelper::get('APP_URL', 'http://localhost');
    }

    public static function encode(array $payload, int $ttlSeconds = 7200): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $now = time();

        $claims = array_merge($payload, [
            'iss' => self::issuer(),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + max(60, $ttlSeconds),
        ]);

        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE)),
            self::base64UrlEncode(json_encode($claims, JSON_UNESCAPED_UNICODE)),
        ];

        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, self::secret(), true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $jwt): array|false
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }

        [$head64, $payload64, $sig64] = $parts;
        $header = json_decode(self::base64UrlDecode($head64), true);
        $payload = json_decode(self::base64UrlDecode($payload64), true);

        if (!is_array($header) || !is_array($payload)) {
            return false;
        }

        if (($header['alg'] ?? '') !== 'HS256') {
            return false;
        }

        $expected = self::base64UrlEncode(hash_hmac('sha256', $head64 . '.' . $payload64, self::secret(), true));
        if (!hash_equals($expected, $sig64)) {
            return false;
        }

        $now = time();
        if (!empty($payload['nbf']) && $now < (int) $payload['nbf']) {
            return false;
        }
        if (!empty($payload['exp']) && $now >= (int) $payload['exp']) {
            return false;
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/')) ?: '';
    }
}
