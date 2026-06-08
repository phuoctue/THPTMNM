<?php

require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthHelper.php';
require_once 'app/models/UserModel.php';

class UserApiController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        $this->requireAdmin();
        ApiResponse::success('Users retrieved successfully', $this->userModel->getAll(false));
    }

    public function show($id): void
    {
        $this->requireAdmin();
        $user = $this->userModel->findById((int) $id, true);
        if (!$user) {
            ApiResponse::error('User not found', null, 404);
        }

        ApiResponse::success('User retrieved successfully', $user);
    }

    public function update($id): void
    {
        $this->requireAdmin();
        $id = (int) $id;
        $current = $this->userModel->findById($id, true);
        if (!$current) {
            ApiResponse::error('User not found', null, 404);
        }

        $data = $this->getJsonInput();
        $errors = $this->validatePayload($data, $id);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $status = $data['status'] ?? ($current['status'] ?? 'active');
        if ($id === (int) AuthHelper::getUserId() && $status === 'locked') {
            ApiResponse::error('Bạn không thể khóa chính tài khoản admin của mình', null, 422);
        }

        $updated = $this->userModel->updateAdmin($id, [
            'full_name' => trim((string) ($data['full_name'] ?? $current['full_name'] ?? '')),
            'email' => trim((string) ($data['email'] ?? $current['email'] ?? '')),
            'phone' => trim((string) ($data['phone'] ?? $current['phone'] ?? '')),
            'address' => trim((string) ($data['address'] ?? $current['address'] ?? '')),
            'role' => $data['role'] ?? ($current['role'] ?? 'customer'),
            'status' => $status,
        ]);

        if (!$updated) {
            ApiResponse::error('User update failed', null, 400);
        }

        if ($id === (int) AuthHelper::getUserId()) {
            $_SESSION['user_name'] = trim((string) ($data['full_name'] ?? $current['full_name'] ?? ''));
            $_SESSION['user_email'] = trim((string) ($data['email'] ?? $current['email'] ?? ''));
            $_SESSION['user_role'] = $data['role'] ?? ($current['role'] ?? 'customer');
            $_SESSION['user_status'] = $status;
        }

        ApiResponse::success('User updated successfully');
    }

    public function destroy($id): void
    {
        $this->requireAdmin();
        $id = (int) $id;

        if ($id === (int) AuthHelper::getUserId()) {
            ApiResponse::error('Bạn không thể xóa chính tài khoản của mình', null, 422);
        }

        $deleted = $this->userModel->hardDelete($id);
        if (!$deleted) {
            ApiResponse::error('User deletion failed', null, 400);
        }

        ApiResponse::success('User deleted successfully');
    }

    private function requireAdmin(): void
    {
        if (!AuthHelper::isAdmin()) {
            ApiResponse::error('Forbidden', null, 403);
        }
    }

    private function validatePayload(array $data, int $ignoreId): array
    {
        $errors = [];
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $role = $data['role'] ?? '';
        $status = $data['status'] ?? '';

        if ($fullName === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        } elseif ($this->userModel->emailExists($email, $ignoreId)) {
            $errors['email'] = 'Email đã tồn tại ở người dùng khác';
        }
        if ($phone !== '') {
            $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
            if (!preg_match('/^(?:\+?[0-9]{7,15}|0[0-9]{8,10})$/', $normalizedPhone)) {
                $errors['phone'] = 'Số điện thoại không hợp lệ';
            }
        }
        if ($role !== '' && !in_array($role, ['customer', 'admin'], true)) {
            $errors['role'] = 'Vai trò không hợp lệ';
        }
        if ($status !== '' && !in_array($status, ['active', 'locked'], true)) {
            $errors['status'] = 'Trạng thái không hợp lệ';
        }

        return $errors;
    }

    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }
}
