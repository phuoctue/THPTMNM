<?php

require_once __DIR__ . '/EnvHelper.php';

class ApiRequest
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function isJson(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        return stripos($contentType, 'application/json') !== false;
    }

    public static function json(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public static function body(): array
    {
        if (self::isJson()) {
            return self::json();
        }

        if (!empty($_POST)) {
            return $_POST;
        }

        return self::json();
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        $body = self::body();
        if (array_key_exists($key, $body)) {
            return $body[$key];
        }

        if (array_key_exists($key, $_GET)) {
            return $_GET[$key];
        }

        return $default;
    }

    public static function all(): array
    {
        return array_merge($_GET, self::body());
    }

    public static function files(): array
    {
        return $_FILES;
    }

    public static function bearerToken(): ?string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authorization = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['Authorization']
            ?? ($headers['Authorization'] ?? $headers['authorization'] ?? null);

        if (!$authorization || !preg_match('/Bearer\s+(\S+)/i', $authorization, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }
}
