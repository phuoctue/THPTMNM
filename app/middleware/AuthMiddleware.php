<?php

require_once __DIR__ . '/../libs/ApiResponse.php';
require_once __DIR__ . '/../libs/ApiRequest.php';
require_once __DIR__ . '/../libs/JwtAuth.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthMiddleware
{
    public static function user(bool $adminOnly = false): array
    {
        $token = ApiRequest::bearerToken();
        if ($token === null) {
            ApiResponse::error('Unauthorized', null, 401);
        }

        $payload = JwtAuth::decode($token);
        if ($payload === false) {
            ApiResponse::error('Unauthorized', null, 401);
        }

        $userId = (int) ($payload['sub'] ?? $payload['uid'] ?? 0);
        if ($userId <= 0) {
            ApiResponse::error('Unauthorized', null, 401);
        }

        $userModel = new UserModel();
        $user = $userModel->findById($userId);
        if (!$user || ($user['status'] ?? 'active') !== 'active' || !empty($user['deleted_at'])) {
            ApiResponse::error('Unauthorized', null, 401);
        }

        if ($adminOnly && ($user['role'] ?? '') !== 'admin') {
            ApiResponse::error('Forbidden', null, 403);
        }

        return $user;
    }

    public static function admin(): array
    {
        return self::user(true);
    }
}
