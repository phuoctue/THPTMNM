<?php
/**
 * ProfileController.php - Controller quản lý hồ sơ người dùng
 * Xử lý: Xem profile, Sửa profile, Đổi mật khẩu, Đơn hàng
 */

require_once 'app/libs/AuthHelper.php';
require_once 'app/models/UserModel.php';

class ProfileController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // ================================================================
    // XEM PROFILE - TRANG CỬ NHÂN
    // ================================================================
    /**
     * Hiển thị trang hồ sơ cá nhân
     */
    public function index() {
        // ✅ YÊU CẦU PHẢI ĐĂNG NHẬP
        AuthHelper::requireLogin();

        $user = AuthHelper::getCurrentUser();
        $userDetails = $this->userModel->findById($user['id']);

        include 'app/views/profile/index.php';
    }

    // ================================================================
    // CHỈNH SỬA PROFILE
    // ================================================================
    /**
     * Hiển thị form chỉnh sửa profile và xử lý submit
     */
    public function edit() {
        // ✅ YÊU CẦU PHẢI ĐĂNG NHẬP
        AuthHelper::requireLogin();

        $user = AuthHelper::getCurrentUser();
        $userDetails = $this->userModel->findById($user['id']);
        $oldData = $_SESSION['old_data'] ?? [];
        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['old_data']);
        unset($_SESSION['errors']);
        unset($_SESSION['success']);

        // Xử lý form khi submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditProfile($user['id']);
        }

        include 'app/views/profile/edit.php';
    }

    /**
     * XỬ LÝ LOGIC CẬP NHẬT PROFILE
     */
    private function handleEditProfile($userId) {
        $full_name = $_POST['full_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $errors = [];

        // VALIDATION

        // 1. Kiểm tra họ tên không để trống
        if (empty($full_name)) {
            $errors[] = "Vui lòng nhập họ và tên";
        }

        // 2. Kiểm tra độ dài họ tên
        if (!empty($full_name) && strlen($full_name) > 100) {
            $errors[] = "Họ và tên không được vượt quá 100 ký tự";
        }

        // 3. Kiểm tra số điện thoại (nếu có)
        if (!empty($phone) && !preg_match('/^[0-9\s\-\+\(\)]{7,20}$/', $phone)) {
            $errors[] = "Số điện thoại không hợp lệ";
        }

        // Nếu có lỗi
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = [
                'full_name' => $full_name,
                'phone' => $phone,
                'address' => $address,
            ];
            header('Location: /profile/edit');
            exit;
        }

        // CẬP NHẬT DATABASE
        $data = [
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address
        ];

        if ($this->userModel->update($userId, $data)) {
            // Cập nhật session với tên mới
            $_SESSION['user_name'] = $full_name;

            $_SESSION['success'] = "✅ Cập nhật hồ sơ thành công!";
            header('Location: /profile');
            exit;
        } else {
            $_SESSION['errors'] = ["Cập nhật thất bại. Vui lòng thử lại."];
            header('Location: /profile/edit');
            exit;
        }
    }

    // ================================================================
    // ĐỔI MẬT KHẨU
    // ================================================================
    /**
     * Hiển thị form đổi mật khẩu và xử lý submit
     */
    public function changePassword() {
        // ✅ YÊU CẦU PHẢI ĐĂNG NHẬP
        AuthHelper::requireLogin();

        $user = AuthHelper::getCurrentUser();
        $errors = $_SESSION['errors'] ?? [];
        $success = $_SESSION['success'] ?? '';
        unset($_SESSION['errors']);
        unset($_SESSION['success']);

        // Xử lý form khi submit
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleChangePassword($user['id']);
        }

        include 'app/views/profile/change-password.php';
    }

    /**
     * XỬ LÝ LOGIC ĐỔI MẬT KHẨU
     */
    private function handleChangePassword($userId) {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];

        // VALIDATION

        // 1. Kiểm tra không để trống
        if (empty($oldPassword)) {
            $errors[] = "Vui lòng nhập mật khẩu cũ";
        }
        if (empty($newPassword)) {
            $errors[] = "Vui lòng nhập mật khẩu mới";
        }
        if (empty($confirmPassword)) {
            $errors[] = "Vui lòng xác nhận mật khẩu mới";
        }

        // 2. Kiểm tra độ dài mật khẩu mới
        if (!empty($newPassword) && strlen($newPassword) < 6) {
            $errors[] = "Mật khẩu mới phải có ít nhất 6 ký tự";
        }

        // 3. Kiểm tra mật khẩu mới trùng khớp
        if (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $errors[] = "Mật khẩu xác nhận không trùng khớp";
        }

        // 4. Kiểm tra mật khẩu mới không giống mật khẩu cũ
        if (!empty($oldPassword) && !empty($newPassword) && $oldPassword === $newPassword) {
            $errors[] = "Mật khẩu mới phải khác mật khẩu cũ";
        }

        // Nếu có lỗi validation
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /profile/changePassword');
            exit;
        }

        // ĐỔI MẬT KHẨU
        if ($this->userModel->changePassword($userId, $oldPassword, $newPassword)) {
            // Đổi thành công
            $successMessage = "✅ Đổi mật khẩu thành công! Vui lòng đăng nhập lại.";

            // Xóa dữ liệu đăng nhập cũ, giữ lại flash message cho màn hình login
            $_SESSION = [];
            $_SESSION['success'] = $successMessage;
            session_regenerate_id(true);

            // Chuyển sang trang đăng nhập để user login lại
            header('Location: /auth/login');
            exit;
        } else {
            // Đổi thất bại - có thể password cũ sai
            $_SESSION['errors'] = ["Mật khẩu cũ không chính xác hoặc có lỗi hệ thống"];
            header('Location: /profile/changePassword');
            exit;
        }
    }

    // ================================================================
    // ĐƠN HÀNG
    // ================================================================
    /**
     * Xem danh sách đơn hàng của người dùng
     */
    public function orders() {
        // ✅ YÊU CẦU PHẢI ĐĂNG NHẬP
        AuthHelper::requireLogin();

        $user = AuthHelper::getCurrentUser();
        
        // TODO: Lấy danh sách đơn hàng từ OrderModel
        // $orderModel = new OrderModel();
        // $orders = $orderModel->findByUserId($user['id']);

        include 'app/views/profile/orders.php';
    }

    // ================================================================
    // QUẢN LÝ USERS (CHỈ ADMIN)
    // ================================================================
    /**
     * Xem danh sách tất cả users (admin only)
     */
    public function allUsers() {
        // ✅ YÊU CẦU PHẢI LÀ ADMIN
        AuthHelper::requireAdmin();

        $users = $this->userModel->getAll();

        include 'app/views/admin/users.php';
    }

    /**
     * Xóa user (admin only)
     */
    public function deleteUser($id) {
        // ✅ YÊU CẦU PHẢI LÀ ADMIN
        AuthHelper::requireAdmin();

        // Không cho admin xóa chính mình
        if ($id == AuthHelper::getUserId()) {
            $_SESSION['errors'] = ["Không thể xóa chính mình"];
            header('Location: /profile/allUsers');
            exit;
        }

        if ($this->userModel->delete($id)) {
            $_SESSION['success'] = "Đã xóa người dùng";
        } else {
            $_SESSION['errors'] = ["Xóa thất bại"];
        }

        header('Location: /profile/allUsers');
        exit;
    }
}
?>

