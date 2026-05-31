<?php
class ViewHelper
{
    public static function consumeFlash(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $flash = [
            'errors' => $_SESSION['errors'] ?? [],
            'success' => $_SESSION['success'] ?? '',
            'old_data' => $_SESSION['old_data'] ?? [],
        ];

        unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old_data']);

        return $flash;
    }

    public static function old(array $oldData, string $key, $default = '')
    {
        return $oldData[$key] ?? $default;
    }

    public static function hasError(array $errors, array $messages): bool
    {
        return !empty(array_intersect($errors, $messages));
    }

    public static function firstError(array $errors, array $messages): string
    {
        $matched = array_values(array_intersect($errors, $messages));
        return $matched[0] ?? '';
    }
}
