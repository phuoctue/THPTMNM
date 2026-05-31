<?php
/**
 * AuthController.php - Controller xử lý authentication
 * Quản lý: Đăng ký, Đăng nhập, Đăng xuất
 */

require_once 'app/models/UserModel.php';

class AuthController {
    private $userModel;
    private $errors = [];
    private $success = '';

    public function __construct() {
        // Khởi tạo UserModel
        $this->userModel = new UserModel();
    }

    // ================================================================
    // HIỂN THỊ & XỬ LÝ FORM ĐĂNG KÝ
    // ================================================================
    public function register() {
        // Nếu đã đăng nhập, chuyển hướng về trang chủ
        if ($this->isLoggedIn()) {
            header('Location: /');
            exit;
        }

        // Xử lý form khi submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
        }

        // Lấy thông báo lỗi/success từ session
        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['errors']);
        unset($_SESSION['success']);

        // Hiển thị view đăng ký
        include 'app/views/register.php';
    }

    /**
     * XỬ LÝ LOGIC ĐĂNG KÝ
     */
    private function handleRegister() {
        $this->errors = [];

        // Lấy dữ liệu từ form
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        // VALIDATION - KIỂM TRA DỮ LIỆU

        // 1. Kiểm tra các trường bắt buộc
        if (empty($full_name)) {
            $this->errors[] = "Vui lòng nhập họ và tên";
        }
        if (empty($email)) {
            $this->errors[] = "Vui lòng nhập email";
        }
        if (empty($password)) {
            $this->errors[] = "Vui lòng nhập mật khẩu";
        }
        if (empty($confirm_password)) {
            $this->errors[] = "Vui lòng xác nhận mật khẩu";
        }

        // 2. Kiểm tra định dạng email
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Email không hợp lệ";
        }

        // 3. Kiểm tra độ dài mật khẩu (tối thiểu 6 ký tự)
        if (!empty($password) && strlen($password) < 6) {
            $this->errors[] = "Mật khẩu phải có ít nhất 6 ký tự";
        }

        // 4. Kiểm tra mật khẩu trùng khớp
        if (!empty($password) && $password !== $confirm_password) {
            $this->errors[] = "Mật khẩu không trùng khớp";
        }

        // 5. Kiểm tra họ tên hợp lệ (không quá 100 ký tự)
        if (!empty($full_name) && strlen($full_name) > 100) {
            $this->errors[] = "Họ và tên không được vượt quá 100 ký tự";
        }

        // 6. Kiểm tra số điện thoại nếu người dùng có nhập
        if (!empty($phone)) {
            $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
            if (!preg_match('/^(?:\+?[0-9]{7,15}|0[0-9]{8,10})$/', $normalizedPhone)) {
                $this->errors[] = "Số điện thoại không hợp lệ";
            }
        }

        // Nếu có lỗi, lưu vào session và quay lại form
        if (!empty($this->errors)) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['old_data'] = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ];
            header('Location: /auth/register');
            exit;
        }

        // ĐĂNG KÝ - TẠO TÀI KHOẢN MỚI
        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'address' => $address
        ];

        if ($this->userModel->register($data)) {
            // Đăng ký thành công
            $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
            header('Location: /auth/login');
            exit;
        } else {
            // Đăng ký thất bại (có thể email đã tồn tại)
            $_SESSION['errors'] = ["Email đã được sử dụng hoặc có lỗi hệ thống"];
            $_SESSION['old_data'] = [
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ];
            header('Location: /auth/register');
            exit;
        }
    }

    // ================================================================
    // HIỂN THỊ & XỬ LÝ FORM ĐĂNG NHẬP
    // ================================================================
    public function login() {
        // Nếu đã đăng nhập, chuyển hướng về trang chủ
        if ($this->isLoggedIn()) {
            header('Location: /');
            exit;
        }

        // Xử lý form khi submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        }

        // Lấy thông báo lỗi/success từ session
        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['errors']);
        unset($_SESSION['success']);

        // Hiển thị view đăng nhập
        include 'app/views/login.php';
    }

    /**
     * XỬ LÝ LOGIC ĐĂNG NHẬP
     */
    private function handleLogin() {
        $this->errors = [];

        // Lấy dữ liệu từ form
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // VALIDATION - KIỂM TRA DỮ LIỆU

        // 1. Kiểm tra các trường bắt buộc
        if (empty($email)) {
            $this->errors[] = "Vui lòng nhập email";
        }
        if (empty($password)) {
            $this->errors[] = "Vui lòng nhập mật khẩu";
        }

        // 2. Kiểm tra định dạng email
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Email không hợp lệ";
        }

        // Nếu có lỗi validation
        if (!empty($this->errors)) {
            $_SESSION['errors'] = $this->errors;
            $_SESSION['old_data'] = [
                'email' => $email,
            ];
            header('Location: /auth/login');
            exit;
        }

        // TÌM NGƯỜI DÙNG
        $user = $this->userModel->findByEmail($email);

        // Kiểm tra người dùng tồn tại và password đúng
        if ($user && password_verify($password, $user['password'])) {
            // ĐĂNG NHẬP THÀNH CÔNG - TẠO SESSION
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['success'] = "Đăng nhập thành công!";

            // Chuyển hướng về trang chủ
            header('Location: /');
            exit;
        } else {
            // ĐĂNG NHẬP THẤT BẠI
            $_SESSION['errors'] = ["Email hoặc mật khẩu không chính xác"];
            $_SESSION['old_data'] = [
                'email' => $email,
            ];
            header('Location: /auth/login');
            exit;
        }
    }

    // ================================================================
    // ĐĂNG XUẤT
    // ================================================================
    public function logout() {
        // Hủy tất cả session
        $_SESSION = [];
        session_destroy();

        // Chuyển hướng về trang chủ
        header('Location: /');
        exit;
    }

    // ================================================================
    // HỆ THỐNG - KIỂM TRA ĐĂNG NHẬP
    // ================================================================

    /**
     * Kiểm tra người dùng đã đăng nhập chưa
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Kiểm tra người dùng là admin
     */
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Lấy thông tin người dùng hiện tại
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
}
?>
