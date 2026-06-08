<?php

class ApiResponse
{
    public static function send(int $statusCode, bool $success, string $message, mixed $data = null, mixed $errors = null, array $meta = []): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $payload = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ];

        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success(string $message, mixed $data = null, int $statusCode = 200, array $meta = []): void
    {
        self::send($statusCode, true, $message, $data, null, $meta);
    }

    public static function error(string $message, mixed $errors = null, int $statusCode = 400): void
    {
        self::send($statusCode, false, $message, null, $errors);
    }
}
