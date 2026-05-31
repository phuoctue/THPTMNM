<?php
/**
 * AuthHelper.php - Hỗ trợ kiểm tra trạng thái xác thực
 * Được sử dụng trong views và controllers để kiểm tra quyền người dùng
 */

class AuthHelper {
    /**
     * KIỂM TRA NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP CHƯA
     * 
     * @return bool - True nếu đã đăng nhập
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * KIỂM TRA NGƯỜI DÙNG CÓ QUYỀN ADMIN KHÔNG
     * 
     * @return bool - True nếu là admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * LẤY THÔNG TIN NGƯỜI DÙNG HIỆN TẠI
     * 
     * @return array|null - Mảng thông tin người dùng hoặc null
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }

    /**
     * LẤY TÊN NGƯỜI DÙNG HIỆN TẠI
     * 
     * @return string|null - Tên người dùng hoặc null
     */
    public static function getUserName() {
        return self::isLoggedIn() ? $_SESSION['user_name'] : null;
    }

    /**
     * LẤY EMAIL NGƯỜI DÙNG HIỆN TẠI
     * 
     * @return string|null - Email người dùng hoặc null
     */
    public static function getUserEmail() {
        return self::isLoggedIn() ? $_SESSION['user_email'] : null;
    }

    /**
     * LẤY ID NGƯỜI DÙNG HIỆN TẠI
     * 
     * @return int|null - ID người dùng hoặc null
     */
    public static function getUserId() {
        return self::isLoggedIn() ? $_SESSION['user_id'] : null;
    }

    /**
     * YÊUDUNG PHẢI ĐĂNG NHẬP (REDIRECT NẾU CHƯA)
     * 
     * Sử dụng trong các trang cần xác thực:
     * AuthHelper::requireLogin();
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /auth/login');
            exit;
        }
    }

    /**
     * YÊU DỤC PHẢI LÀ ADMIN (REDIRECT NẾU KHÔNG PHẢI)
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header('Location: /');
            exit;
        }
    }

    /**
     * CHUYỂN HƯỚNG ĐẾN TRANG ĐÃ YÊU CẦU TRƯỚC ĐĂNG NHẬP
     */
    public static function redirectAfterLogin() {
        if (isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        }
    }
}
?>
