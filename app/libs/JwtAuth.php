<?php

require_once __DIR__ . '/EnvHelper.php';

class JwtAuth
{
    private const ALG = 'HS256';

    public static function secret(): string
    {
        $secret = (string) EnvHelper::get('JWT_SECRET', '');
        if ($secret === '') {
            $secret = (string) EnvHelper::get('APP_KEY', '');
        }

        return $secret !== '' ? $secret : 'change-this-jwt-secret';
    }

    public static function encode(array $claims, int $ttlSeconds = 3600): string
    {
        $now = time();
        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + $ttlSeconds,
        ]);

        $header = ['typ' => 'JWT', 'alg' => self::ALG];
        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_UNICODE)),
            self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), self::secret(), true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $token): array|false
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $header = json_decode(self::base64UrlDecode($encodedHeader), true);
        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);

        if (!is_array($header) || !is_array($payload)) {
            return false;
        }

        if (($header['alg'] ?? '') !== self::ALG) {
            return false;
        }

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, self::secret(), true)
        );

        if (!hash_equals($expectedSignature, $encodedSignature)) {
            return false;
        }

        if (!empty($payload['nbf']) && time() < (int) $payload['nbf']) {
            return false;
        }

        if (!empty($payload['exp']) && time() >= (int) $payload['exp']) {
            return false;
        }

        return $payload;
    }

    public static function issueUserToken(array $user, int $ttlSeconds = 86400): array
    {
        $token = self::encode([
            'sub' => (int) $user['id'],
            'uid' => (int) $user['id'],
            'email' => strtolower((string) ($user['email'] ?? '')),
            'role' => (string) ($user['role'] ?? 'customer'),
            'name' => (string) ($user['full_name'] ?? ''),
        ], $ttlSeconds);

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttlSeconds,
            'expires_at' => gmdate('c', time() + $ttlSeconds),
        ];
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
