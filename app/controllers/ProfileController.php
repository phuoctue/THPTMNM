<?php
/**
 * ProfileController.php
 * Quản lý hồ sơ cá nhân, thay đổi mật khẩu và các thao tác của người dùng đã đăng nhập.
 */

require_once 'app/libs/AuthHelper.php';
require_once 'app/config/database.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/CartModel.php';

class ProfileController
{
    private UserModel $userModel;
    private CartModel $cartModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $db = (new Database())->getConnection();
        $this->cartModel = new CartModel($db);
    }

    public function index(): void
    {
        AuthHelper::requireLogin();

        $user = AuthHelper::getCurrentUser();
        $userDetails = $this->userModel->findById((int) $user['id']);
        $flash = $this->consumeFlash();

        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/profile.php';
    }

    public function edit(): void
    {
        AuthHelper::requireLogin();
        AuthHelper::requireVerifiedEmail();

        $user = AuthHelper::getCurrentUser();
        $userDetails = $this->userModel->findById((int) $user['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit((int) $user['id'], $userDetails);
        }

        $flash = $this->consumeFlash();
        $oldData = $flash['old_data'];
        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/profile/edit.php';
    }

    private function handleEdit(int $userId, array $userDetails): void
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $errors = [];

        if ($fullName === '') {
            $errors[] = 'Vui lòng nhập họ và tên';
        }
        $nameLength = function_exists('mb_strlen') ? mb_strlen($fullName) : strlen($fullName);
        if ($fullName !== '' && $nameLength > 100) {
            $errors[] = 'Họ và tên không được vượt quá 100 ký tự';
        }
        if ($phone !== '') {
            $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
            if (!preg_match('/^(?:\+?[0-9]{7,15}|0[0-9]{8,10})$/', $normalizedPhone)) {
                $errors[] = 'Số điện thoại không hợp lệ';
            }
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = [
                'full_name' => $fullName,
                'phone' => $phone,
                'address' => $address,
            ];
            header('Location: /profile/edit');
            exit;
        }

        $updatedAvatar = $userDetails['avatar'] ?? null;

        if (!$this->userModel->updateProfile($userId, [
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
        ])) {
            $_SESSION['errors'] = ['Cập nhật hồ sơ thất bại, vui lòng thử lại'];
            header('Location: /profile/edit');
            exit;
        }

        if (!empty($_FILES['avatar']['name'])) {
            $uploadResult = $this->handleAvatarUpload($userId, $userDetails);
            if ($uploadResult !== true) {
                $_SESSION['errors'] = [$uploadResult];
                header('Location: /profile/edit');
                exit;
            }
            if (is_array($uploadResult) && !empty($uploadResult['avatar'])) {
                $updatedAvatar = $uploadResult['avatar'];
            }
        }

        AuthHelper::setUserSession(array_merge($userDetails, [
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
            'avatar' => $updatedAvatar,
        ]));

        $_SESSION['success'] = 'Cập nhật hồ sơ thành công.';
        header('Location: /profile');
        exit;
    }

    public function changePassword(): void
    {
        AuthHelper::requireLogin();
        AuthHelper::requireVerifiedEmail();

        $user = AuthHelper::getCurrentUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleChangePassword((int) $user['id']);
        }

        $flash = $this->consumeFlash();
        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/change_password.php';
    }

    private function handleChangePassword(int $userId): void
    {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $errors = [];

        if ($oldPassword === '') {
            $errors[] = 'Vui lòng nhập mật khẩu cũ';
        }
        if ($newPassword === '') {
            $errors[] = 'Vui lòng nhập mật khẩu mới';
        }
        if ($confirmPassword === '') {
            $errors[] = 'Vui lòng xác nhận mật khẩu mới';
        }
        if ($newPassword !== '' && strlen($newPassword) < 8) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 8 ký tự';
        }
        if ($newPassword !== '' && $newPassword !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không trùng khớp';
        }
        if ($oldPassword !== '' && $newPassword !== '' && $oldPassword === $newPassword) {
            $errors[] = 'Mật khẩu mới phải khác mật khẩu cũ';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            header('Location: /profile/changePassword');
            exit;
        }

        if (!$this->userModel->changePassword($userId, $oldPassword, $newPassword)) {
            $_SESSION['errors'] = ['Mật khẩu cũ không chính xác hoặc hệ thống đang gặp lỗi'];
            header('Location: /profile/changePassword');
            exit;
        }

        $this->userModel->revokeRememberTokenByUserId($userId);
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['success'] = 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.';
        header('Location: /auth/login');
        exit;
    }

    public function orders(): void
    {
        AuthHelper::requireLogin();
        AuthHelper::requireVerifiedEmail();

        $user = AuthHelper::getCurrentUser();
        $orders = $this->cartModel->getOrdersByCustomerEmail((string) ($user['email'] ?? ''));
        $flash = $this->consumeFlash();
        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/profile/orders.php';
    }

    private function handleAvatarUpload(int $userId, array $userDetails)
    {
        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }

        $file = $_FILES['avatar'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'Tải ảnh đại diện thất bại';
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            return 'Ảnh đại diện không được vượt quá 2MB';
        }

        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!isset($allowedMime[$mimeType])) {
            return 'Chỉ chấp nhận file ảnh JPG, PNG hoặc WEBP';
        }

        $uploadDir = __DIR__ . '/../../uploads/avatars';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            return 'Không thể tạo thư mục upload';
        }

        $ext = $allowedMime[$mimeType];
        $filename = 'avatar_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'Không thể lưu ảnh đại diện';
        }

        $relativePath = 'uploads/avatars/' . $filename;
        if (!$this->userModel->updateAvatar($userId, $relativePath)) {
            @unlink($targetPath);
            return 'Không thể cập nhật avatar trong cơ sở dữ liệu';
        }

        $oldAvatar = $userDetails['avatar'] ?? null;
        if (!empty($oldAvatar)) {
            $oldFile = __DIR__ . '/../../' . ltrim($oldAvatar, '/');
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        AuthHelper::bootstrapSession();
        $_SESSION['user_avatar'] = $relativePath;

        return [
            'avatar' => $relativePath,
        ];
    }

    private function consumeFlash(): array
    {
        $flash = [
            'errors' => $_SESSION['errors'] ?? [],
            'success' => $_SESSION['success'] ?? '',
            'old_data' => $_SESSION['old_data'] ?? [],
        ];

        unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old_data']);

        return $flash;
    }
}
