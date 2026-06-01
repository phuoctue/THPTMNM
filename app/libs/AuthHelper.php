<?php
/**
 * AuthHelper.php
 * Bộ helper dùng chung cho xác thực, phân quyền và session của website.
 */

class AuthHelper
{
    /**
     * Khởi tạo session cookie an toàn hơn cho toàn hệ thống.
     */
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

    /**
     * Lưu thông tin người dùng vào session sau khi đăng nhập.
     */
    public static function setUserSession(array $user): void
    {
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_name'] = $user['full_name'] ?? '';
        $_SESSION['user_role'] = $user['role'] ?? 'customer';
        $_SESSION['user_status'] = $user['status'] ?? 'active';
        $_SESSION['user_avatar'] = $user['avatar'] ?? null;
        $_SESSION['user_email_verified_at'] = $user['email_verified_at'] ?? null;
    }

    /**
     * Xóa toàn bộ session người dùng hiện tại.
     */
    public static function clearUserSession(): void
    {
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

    /**
     * Kiểm tra người dùng đã đăng nhập chưa.
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']) && ($_SESSION['user_status'] ?? 'active') === 'active';
    }

    /**
     * Kiểm tra có phải admin hay không.
     */
    public static function isAdmin(): bool
    {
        return self::isLoggedIn() && ($_SESSION['user_role'] ?? '') === 'admin';
    }

    /**
     * Kiểm tra email đã xác thực chưa.
     */
    public static function isEmailVerified(): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        self::syncCurrentUserFromDatabase();

        return !empty($_SESSION['user_email_verified_at']);
    }

    /**
     * Trả về thông tin người dùng hiện tại từ session.
     */
    public static function getCurrentUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }

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

    public static function getUserName(): ?string
    {
        return self::isLoggedIn() ? (string) ($_SESSION['user_name'] ?? '') : null;
    }

    public static function getUserEmail(): ?string
    {
        return self::isLoggedIn() ? (string) ($_SESSION['user_email'] ?? '') : null;
    }

    public static function getUserId(): ?int
    {
        return self::isLoggedIn() ? (int) $_SESSION['user_id'] : null;
    }

    public static function getUserRole(): ?string
    {
        return self::isLoggedIn() ? (string) ($_SESSION['user_role'] ?? 'customer') : null;
    }

    public static function getUserAvatar(): ?string
    {
        return self::isLoggedIn() ? ($_SESSION['user_avatar'] ?? null) : null;
    }

    /**
     * Yêu cầu đăng nhập. Nếu chưa đăng nhập sẽ chuyển đến trang login.
     */
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

    /**
     * Yêu cầu quyền admin.
     */
    public static function requireAdmin(): void
    {
        if (self::isAdmin()) {
            return;
        }

        $_SESSION['errors'] = ['Bạn không có quyền truy cập trang này'];
        header('Location: /');
        exit;
    }

    /**
     * Yêu cầu người dùng đã xác thực email.
     */
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

    /**
     * Đồng bộ lại thông tin người dùng từ database để tránh session bị stale.
     * Dùng khi trạng thái email verify / role / status có thể thay đổi sau khi đăng nhập.
     */
    private static function syncCurrentUserFromDatabase(): void
    {
        if (!self::isLoggedIn()) {
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

    /**
     * Chuyển hướng tới trang ban đầu sau khi đăng nhập.
     */
    public static function redirectAfterLogin(): void
    {
        if (!empty($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        }
    }

    /**
     * Auto login từ cookie remember me.
     */
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

    private static function clearInvalidRememberCookie(): void
    {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie('remember_me', '', time() - 3600, '/', '', $secure, true);
    }
}
