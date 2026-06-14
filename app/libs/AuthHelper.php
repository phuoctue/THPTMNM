<?php
/**
 * AuthHelper.php
 * Helper dùng chung cho xác thực session và JWT.
 */

require_once __DIR__ . '/JwtHelper.php';

class AuthHelper
{
    private static ?array $resolvedApiUser = null;

    public static function bootstrapSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function setUserSession(array $user): void
    {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_name'] = $user['full_name'] ?? ($user['name'] ?? '');
        $_SESSION['user_role'] = $user['role'] ?? 'customer';
        $_SESSION['user_status'] = $user['status'] ?? 'active';
        $_SESSION['user_avatar'] = $user['avatar'] ?? null;
        $_SESSION['user_email_verified_at'] = $user['email_verified_at'] ?? null;
    }

    public static function clearUserSession(): void
    {
        self::$resolvedApiUser = null;
        unset(
            $_SESSION['user_id'],
            $_SESSION['user_email'],
            $_SESSION['user_name'],
            $_SESSION['user_role'],
            $_SESSION['user_status'],
            $_SESSION['user_avatar'],
            $_SESSION['user_email_verified_at']
        );
    }

    public static function isLoggedIn(): bool
    {
        $user = self::getCurrentUser();
        return !empty($user['id']) && ($user['status'] ?? 'active') === 'active';
    }

    public static function isAdmin(): bool
    {
        $user = self::getCurrentUser();
        return !empty($user) && ($user['role'] ?? '') === 'admin' && ($user['status'] ?? 'active') === 'active';
    }

    public static function isEmailVerified(): bool
    {
        $user = self::getCurrentUser();
        return !empty($user) && !empty($user['email_verified_at']);
    }

    public static function getCurrentUser(): ?array
    {
        if (self::$resolvedApiUser !== null) {
            return self::$resolvedApiUser;
        }

        if (!empty($_SESSION['user_id']) && ($_SESSION['user_status'] ?? 'active') === 'active') {
            self::syncCurrentUserFromDatabase();

            return [
                'id' => (int) $_SESSION['user_id'],
                'email' => $_SESSION['user_email'] ?? '',
                'name' => $_SESSION['user_name'] ?? '',
                'role' => $_SESSION['user_role'] ?? 'customer',
                'status' => $_SESSION['user_status'] ?? 'active',
                'avatar' => $_SESSION['user_avatar'] ?? null,
                'email_verified_at' => $_SESSION['user_email_verified_at'] ?? null,
            ];
        }

        $token = self::extractBearerToken();
        if ($token === '') {
            return null;
        }

        $payload = JwtHelper::decode($token);
        if (!is_array($payload)) {
            return null;
        }

        $userId = (int) ($payload['sub'] ?? 0);
        if ($userId <= 0) {
            return null;
        }

        require_once 'app/models/UserModel.php';
        $userModel = new UserModel();
        $user = $userModel->findById($userId);
        if (!$user || ($user['status'] ?? 'active') !== 'active' || !empty($user['deleted_at'])) {
            return null;
        }

        self::$resolvedApiUser = [
            'id' => $userId,
            'email' => (string) ($user['email'] ?? ($payload['email'] ?? '')),
            'name' => (string) ($user['full_name'] ?? ($payload['name'] ?? '')),
            'role' => (string) ($user['role'] ?? ($payload['role'] ?? 'customer')),
            'status' => (string) ($user['status'] ?? ($payload['status'] ?? 'active')),
            'avatar' => $user['avatar'] ?? ($payload['avatar'] ?? null),
            'email_verified_at' => $user['email_verified_at'] ?? ($payload['email_verified_at'] ?? null),
        ];

        return self::$resolvedApiUser;
    }

    public static function getUserName(): ?string
    {
        $user = self::getCurrentUser();
        return $user['name'] ?? null;
    }

    public static function getUserEmail(): ?string
    {
        $user = self::getCurrentUser();
        return $user['email'] ?? null;
    }

    public static function getUserId(): ?int
    {
        $user = self::getCurrentUser();
        return isset($user['id']) ? (int) $user['id'] : null;
    }

    public static function getUserRole(): ?string
    {
        $user = self::getCurrentUser();
        return $user['role'] ?? null;
    }

    public static function getUserAvatar(): ?string
    {
        $user = self::getCurrentUser();
        return $user['avatar'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (self::isLoggedIn()) {
            return;
        }

        if (!empty($_SESSION['user_id']) && ($_SESSION['user_status'] ?? 'active') !== 'active') {
            self::clearUserSession();
            $_SESSION['errors'] = ['Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'];
            header('Location: /auth/login');
            exit;
        }

        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
        $_SESSION['errors'] = ['Vui lòng đăng nhập để tiếp tục'];
        header('Location: /auth/login');
        exit;
    }

    public static function requireAdmin(): void
    {
        if (self::isAdmin()) {
            return;
        }

        $_SESSION['errors'] = ['Bạn không có quyền truy cập trang này'];
        header('Location: /');
        exit;
    }

    public static function requireVerifiedEmail(): void
    {
        self::requireLogin();

        if (self::isEmailVerified()) {
            return;
        }

        $_SESSION['errors'] = ['Bạn cần xác thực email trước khi sử dụng chức năng này'];
        header('Location: /profile');
        exit;
    }

    private static function syncCurrentUserFromDatabase(): void
    {
        if (empty($_SESSION['user_id'])) {
            return;
        }

        require_once 'app/models/UserModel.php';

        $userModel = new UserModel();
        $user = $userModel->findById((int) $_SESSION['user_id']);

        if (!$user) {
            self::clearUserSession();
            $_SESSION['errors'] = ['Phiên đăng nhập không còn hợp lệ, vui lòng đăng nhập lại'];
            header('Location: /auth/login');
            exit;
        }

        $_SESSION['user_email'] = $user['email'] ?? $_SESSION['user_email'];
        $_SESSION['user_name'] = $user['full_name'] ?? $_SESSION['user_name'];
        $_SESSION['user_role'] = $user['role'] ?? $_SESSION['user_role'];
        $_SESSION['user_status'] = $user['status'] ?? $_SESSION['user_status'];
        $_SESSION['user_avatar'] = $user['avatar'] ?? $_SESSION['user_avatar'];
        $_SESSION['user_email_verified_at'] = $user['email_verified_at'] ?? null;

        if (($user['status'] ?? 'active') !== 'active' || !empty($user['deleted_at'])) {
            self::clearUserSession();
            $_SESSION['errors'] = ['Tài khoản của bạn đã bị khóa hoặc không còn tồn tại'];
            header('Location: /auth/login');
            exit;
        }
    }

    public static function redirectAfterLogin(): void
    {
        if (!empty($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        }
    }

    public static function restoreRememberMe(): void
    {
        if (self::isLoggedIn() || empty($_COOKIE['remember_me'])) {
            return;
        }

        require_once 'app/models/UserModel.php';

        $parts = explode(':', $_COOKIE['remember_me'], 2);
        if (count($parts) !== 2) {
            self::clearInvalidRememberCookie();
            return;
        }

        [$selector, $validator] = $parts;
        $selector = trim($selector);
        $validator = trim($validator);

        if ($selector === '' || $validator === '') {
            self::clearInvalidRememberCookie();
            return;
        }

        $userModel = new UserModel();
        $token = $userModel->findRememberTokenBySelector($selector);

        if (
            !$token
            || !empty($token['revoked_at'])
            || strtotime($token['expires_at']) < time()
            || !hash_equals($token['token_hash'], hash('sha256', $validator))
        ) {
            if ($token && !empty($token['id'])) {
                $userModel->revokeRememberToken((int) $token['id']);
            }
            self::clearInvalidRememberCookie();
            return;
        }

        $user = $userModel->findById((int) $token['user_id']);
        if (!$user || ($user['status'] ?? 'active') !== 'active' || !empty($user['deleted_at'])) {
            self::clearInvalidRememberCookie();
            return;
        }

        session_regenerate_id(true);
        self::setUserSession($user);
    }

    public static function extractBearerToken(): string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');

        if (!is_string($authorization) || $authorization === '') {
            return '';
        }

        if (preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    private static function clearInvalidRememberCookie(): void
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('remember_me', '', time() - 3600, '/', '', $secure, true);
    }
}
