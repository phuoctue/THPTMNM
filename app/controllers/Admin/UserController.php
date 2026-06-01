<?php
namespace Admin;

/**
 * UserController.php
 * Trang quản lý người dùng cho admin.
 */

require_once 'app/libs/AuthHelper.php';
require_once 'app/models/UserModel.php';

class UserController
{
    private \UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new \UserModel();
    }

    public function index(): void
    {
        \AuthHelper::requireAdmin();

        // Mặc định chỉ hiển thị người dùng chưa bị xóa mềm
        $users = $this->userModel->getAll(false);
        $flash = $this->consumeFlash();

        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/admin/users/index.php';
    }

    public function edit($id = null): void
    {
        \AuthHelper::requireAdmin();

        $id = (int) $id;
        $user = $this->userModel->findById($id, true);
        if (!$user) {
            $_SESSION['errors'] = ['Người dùng không tồn tại'];
            header('Location: /admin/users');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdate($id, $user);
        }

        $flash = $this->consumeFlash();
        $errors = $flash['errors'];
        $success = $flash['success'];
        $oldData = $flash['old_data'];

        include 'app/views/admin/users/edit.php';
    }

    private function handleUpdate(int $id, array $currentUser): void
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $role = $_POST['role'] ?? 'customer';
        $status = $_POST['status'] ?? 'active';
        $errors = [];

        if ($fullName === '') {
            $errors[] = 'Vui lòng nhập họ và tên';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        if ($this->userModel->emailExists($email, $id)) {
            $errors[] = 'Email đã tồn tại ở người dùng khác';
        }
        if (!in_array($role, ['customer', 'admin'], true)) {
            $errors[] = 'Vai trò không hợp lệ';
        }
        if (!in_array($status, ['active', 'locked'], true)) {
            $errors[] = 'Trạng thái không hợp lệ';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'role' => $role,
                'status' => $status,
            ];
            header('Location: /admin/users/edit/' . $id);
            exit;
        }

        if (
            $id === (int) \AuthHelper::getUserId()
            && $status === 'locked'
        ) {
            $_SESSION['errors'] = ['Bạn không thể khóa chính tài khoản admin của mình'];
            header('Location: /admin/users/edit/' . $id);
            exit;
        }

        if (!$this->userModel->updateAdmin($id, [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'role' => $role,
            'status' => $status,
        ])) {
            $_SESSION['errors'] = ['Cập nhật người dùng thất bại'];
            header('Location: /admin/users/edit/' . $id);
            exit;
        }

        if ($id === (int) \AuthHelper::getUserId()) {
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['user_status'] = $status;
        }

        $_SESSION['success'] = 'Cập nhật người dùng thành công';
        header('Location: /admin/users/edit/' . $id);
        exit;
    }

    public function toggleStatus($id = null): void
    {
        \AuthHelper::requireAdmin();

        $id = (int) $id;
        if ($id === (int) \AuthHelper::getUserId()) {
            $_SESSION['errors'] = ['Bạn không thể khóa/mở khóa chính tài khoản của mình'];
            header('Location: /admin/users');
            exit;
        }

        $user = $this->userModel->findById($id, true);
        if (!$user) {
            $_SESSION['errors'] = ['Người dùng không tồn tại'];
            header('Location: /admin/users');
            exit;
        }

        $newStatus = ($user['status'] ?? 'active') === 'active' ? 'locked' : 'active';
        $this->userModel->setStatus($id, $newStatus);

        $_SESSION['success'] = 'Đã cập nhật trạng thái tài khoản';
        header('Location: /admin/users');
        exit;
    }

    public function delete($id = null): void
    {
        \AuthHelper::requireAdmin();

        $id = (int) $id;
        if ($id === (int) \AuthHelper::getUserId()) {
            $_SESSION['errors'] = ['Bạn không thể xóa chính tài khoản của mình'];
            header('Location: /admin/users');
            exit;
        }

        // Trang quản lý user chỉ dùng xóa vĩnh viễn để tránh hiểu nhầm giữa xóa mềm và xóa thật
        $success = $this->userModel->hardDelete($id);

        $_SESSION['success'] = $success ? 'Đã xóa vĩnh viễn người dùng' : 'Xóa người dùng thất bại';
        if (!$success) {
            $_SESSION['errors'] = ['Xóa người dùng thất bại'];
        }

        header('Location: /admin/users');
        exit;
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
