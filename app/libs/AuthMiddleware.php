<?php

require_once __DIR__ . '/JwtHelper.php';
require_once __DIR__ . '/ApiResponse.php';

class AuthMiddleware
{
    public static function authenticate(): array
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        if (empty($authHeader) || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            ApiResponse::error('Unauthorized: Missing or invalid token', null, 401);
        }

        $token = $matches[1];
        $payload = JwtHelper::decode($token);

        if (!$payload) {
            ApiResponse::error('Unauthorized: Invalid or expired token', null, 401);
        }

        return $payload;
    }
}
