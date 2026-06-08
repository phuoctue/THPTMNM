<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthHelper.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/CartModel.php';

class ProfileApiController
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
        $user = $this->requireUser();
        $userDetails = $this->userModel->findById((int) $user['id']);

        if (!$userDetails) {
            ApiResponse::error('User not found', null, 404);
        }

        ApiResponse::success('Profile retrieved successfully', $this->makeUserPayload($userDetails));
    }

    public function update(): void
    {
        $user = $this->requireVerifiedUser();
        $userDetails = $this->userModel->findById((int) $user['id']);

        if (!$userDetails) {
            ApiResponse::error('User not found', null, 404);
        }

        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $address = trim((string) ($_POST['address'] ?? ''));

        $errors = $this->validateProfilePayload($fullName, $phone);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        if (!$this->userModel->updateProfile((int) $user['id'], [
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
        ])) {
            ApiResponse::error('Profile update failed', null, 400);
        }

        if (!empty($_FILES['avatar']['name'])) {
            $uploadResult = $this->handleAvatarUpload((int) $user['id'], $userDetails);
            if ($uploadResult !== true) {
                ApiResponse::error('Avatar upload failed', ['avatar' => $uploadResult], 422);
            }
        }

        $fresh = $this->userModel->findById((int) $user['id']);
        AuthHelper::setUserSession($fresh ?: array_merge($userDetails, [
            'full_name' => $fullName,
            'phone' => $phone,
            'address' => $address,
        ]));

        ApiResponse::success('Profile updated successfully');
    }

    public function changePassword(): void
    {
        $user = $this->requireVerifiedUser();
        $userId = (int) $user['id'];

        $oldPassword = (string) ($_POST['old_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        $errors = $this->validatePasswordPayload($oldPassword, $newPassword, $confirmPassword);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        if (!$this->userModel->changePassword($userId, $oldPassword, $newPassword)) {
            ApiResponse::error('Mật khẩu cũ không chính xác hoặc hệ thống đang gặp lỗi', null, 400);
        }

        $this->userModel->revokeRememberTokenByUserId($userId);
        $_SESSION = [];
        session_regenerate_id(true);

        ApiResponse::success('Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
    }

    public function orders(): void
    {
        $user = $this->requireVerifiedUser();
        $orders = $this->cartModel->getOrdersByCustomerEmail((string) ($user['email'] ?? ''));

        ApiResponse::success('Orders retrieved successfully', $orders);
    }

    private function requireUser(): array
    {
        if (!AuthHelper::isLoggedIn()) {
            ApiResponse::error('Unauthenticated', null, 401);
        }

        $user = AuthHelper::getCurrentUser();
        if (!$user) {
            ApiResponse::error('Unauthenticated', null, 401);
        }

        return $user;
    }

    private function requireVerifiedUser(): array
    {
        $user = $this->requireUser();
        if (empty($user['email_verified_at'])) {
            ApiResponse::error('Bạn cần xác thực email trước khi sử dụng chức năng này', null, 403);
        }

        return $user;
    }

    private function validateProfilePayload(string $fullName, string $phone): array
    {
        $errors = [];

        if ($fullName === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên';
        }
        $nameLength = function_exists('mb_strlen') ? mb_strlen($fullName) : strlen($fullName);
        if ($fullName !== '' && $nameLength > 100) {
            $errors['full_name'] = 'Họ và tên không được vượt quá 100 ký tự';
        }
        if ($phone !== '') {
            $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
            if (!preg_match('/^(?:\+?[0-9]{7,15}|0[0-9]{8,10})$/', $normalizedPhone)) {
                $errors['phone'] = 'Số điện thoại không hợp lệ';
            }
        }

        return $errors;
    }

    private function validatePasswordPayload(string $oldPassword, string $newPassword, string $confirmPassword): array
    {
        $errors = [];

        if ($oldPassword === '') {
            $errors['old_password'] = 'Vui lòng nhập mật khẩu cũ';
        }
        if ($newPassword === '') {
            $errors['new_password'] = 'Vui lòng nhập mật khẩu mới';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'Mật khẩu mới phải có ít nhất 8 ký tự';
        }
        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Vui lòng xác nhận mật khẩu mới';
        } elseif ($newPassword !== '' && $confirmPassword !== $newPassword) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không trùng khớp';
        }
        if ($oldPassword !== '' && $newPassword !== '' && $oldPassword === $newPassword) {
            $errors['new_password'] = 'Mật khẩu mới phải khác mật khẩu cũ';
        }

        return $errors;
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

        return true;
    }

    private function makeUserPayload(array $user): array
    {
        return [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
            'address' => $user['address'] ?? '',
            'role' => $user['role'] ?? 'customer',
            'status' => $user['status'] ?? 'active',
            'avatar' => $user['avatar'] ?? null,
            'email_verified_at' => $user['email_verified_at'] ?? null,
        ];
    }
}
