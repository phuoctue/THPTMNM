<?php
/**
 * AuthController.php
 * Xử lý đăng ký, đăng nhập, xác thực email, quên mật khẩu và đặt lại mật khẩu.
 */

require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/MailHelper.php';
require_once 'app/models/UserModel.php';

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function register(): void
    {
        if (AuthHelper::isLoggedIn()) {
            header('Location: /');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
        }

        $flash = $this->consumeFlash();
        $old_data = $flash['old_data'];
        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/register.php';
    }

    private function handleRegister(): void
    {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        $errors = [];

        if ($fullName === '') {
            $errors[] = 'Vui lòng nhập họ và tên';
        }
        if ($email === '') {
            $errors[] = 'Vui lòng nhập email';
        }
        if ($password === '') {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }
        if ($confirmPassword === '') {
            $errors[] = 'Vui lòng xác nhận mật khẩu';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }
        $nameLength = function_exists('mb_strlen') ? mb_strlen($fullName) : strlen($fullName);
        if ($fullName !== '' && $nameLength > 100) {
            $errors[] = 'Họ và tên không được vượt quá 100 ký tự';
        }
        if ($password !== '' && strlen($password) < 8) {
            $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
        }
        if ($password !== '' && $password !== $confirmPassword) {
            $errors[] = 'Mật khẩu không trùng khớp';
        }
        if ($phone !== '') {
            $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
            if (!preg_match('/^(?:\+?[0-9]{7,15}|0[0-9]{8,10})$/', $normalizedPhone)) {
                $errors[] = 'Số điện thoại không hợp lệ';
            }
        }

        if ($this->userModel->emailExists($email)) {
            $errors[] = 'Email đã được sử dụng';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ];
            header('Location: /auth/register');
            exit;
        }

        $userId = $this->userModel->register([
            'full_name' => $fullName,
            'email' => $email,
            'password' => $password,
            'phone' => $phone,
            'address' => $address,
            'role' => 'customer',
        ]);

        if (!$userId) {
            $_SESSION['errors'] = ['Không thể tạo tài khoản. Vui lòng thử lại.'];
            $_SESSION['old_data'] = [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ];
            header('Location: /auth/register');
            exit;
        }

        [$selector, $validator, $tokenHash, $expiresAt] = $this->generateTokenPair(24, '+24 hours');
        $this->userModel->createEmailVerificationToken($userId, $email, $selector, $tokenHash, $expiresAt);

        $verifyLink = MailHelper::baseUrl() . '/auth/verifyEmail/' . $selector . '.' . $validator;
        $mailSent = MailHelper::send(
            $email,
            'Xác thực tài khoản My Store',
            MailHelper::verificationEmail($fullName, $verifyLink)
        );

        $_SESSION['success'] = $mailSent
            ? 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.'
            : 'Đăng ký thành công, nhưng hệ thống chưa gửi được email xác thực. Vui lòng kiểm tra cấu hình mail.';

        header('Location: /auth/login');
        exit;
    }

    public function login(): void
    {
        if (AuthHelper::isLoggedIn()) {
            header('Location: /');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        }

        $flash = $this->consumeFlash();
        $old_data = $flash['old_data'];
        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/login.php';
    }

    private function handleLogin(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = !empty($_POST['remember_me']);
        $errors = [];

        if ($email === '') {
            $errors[] = 'Vui lòng nhập email';
        }
        if ($password === '') {
            $errors[] = 'Vui lòng nhập mật khẩu';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = ['email' => $email];
            header('Location: /auth/login');
            exit;
        }

        $user = $this->userModel->findByEmail($email);

        if (
            !$user
            || !password_verify($password, $user['password'])
            || ($user['status'] ?? 'active') !== 'active'
            || !empty($user['deleted_at'])
        ) {
            $_SESSION['errors'] = ['Email hoặc mật khẩu không chính xác'];
            $_SESSION['old_data'] = ['email' => $email];
            header('Location: /auth/login');
            exit;
        }

        session_regenerate_id(true);
        AuthHelper::setUserSession($user);

        if ($rememberMe) {
            $this->issueRememberMeCookie((int) $user['id']);
        }

        $_SESSION['success'] = !empty($user['email_verified_at'])
            ? 'Đăng nhập thành công!'
            : 'Đăng nhập thành công, nhưng tài khoản của bạn chưa được xác thực email. Vui lòng kiểm tra hộp thư hoặc gửi lại email xác thực.';
        AuthHelper::redirectAfterLogin();
        header('Location: /');
        exit;
    }

    public function logout(): void
    {
        if (!empty($_COOKIE['remember_me'])) {
            $parts = explode(':', $_COOKIE['remember_me'], 2);
            if (count($parts) === 2) {
                $token = $this->userModel->findRememberTokenBySelector($parts[0]);
                if ($token) {
                    $this->userModel->revokeRememberToken((int) $token['id']);
                }
            }

            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            setcookie('remember_me', '', time() - 3600, '/', '', $secure, true);
        }

        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['success'] = 'Đã đăng xuất thành công.';
        header('Location: /');
        exit;
    }

    /**
     * Xác thực email bằng link có dạng selector.token.
     */
    public function verifyEmail($token = null): void
    {
        $token = $token ?? ($_GET['token'] ?? '');
        $parts = explode('.', (string) $token, 2);

        if (count($parts) !== 2) {
            $_SESSION['errors'] = ['Liên kết xác thực không hợp lệ'];
            header('Location: /auth/login');
            exit;
        }

        [$selector, $validator] = $parts;
        $record = $this->userModel->findEmailVerificationBySelector($selector);

        if (
            !$record
            || !empty($record['verified_at'])
            || strtotime($record['expires_at']) < time()
            || !hash_equals($record['token_hash'], hash('sha256', $validator))
        ) {
            $_SESSION['errors'] = ['Liên kết xác thực đã hết hạn hoặc không hợp lệ'];
            header('Location: /auth/login');
            exit;
        }

        $this->userModel->verifyEmail((int) $record['user_id']);
        $this->userModel->markEmailVerificationUsed((int) $record['id']);

        $_SESSION['success'] = 'Xác thực email thành công. Bạn có thể đăng nhập ngay bây giờ.';
        header('Location: /auth/login');
        exit;
    }

    /**
     * Gửi lại email xác thực cho người dùng đang đăng nhập nhưng chưa verify.
     */
    public function resendVerification(): void
    {
        AuthHelper::requireLogin();

        $user = $this->userModel->findById(AuthHelper::getUserId());
        if (!$user || !empty($user['email_verified_at'])) {
            $_SESSION['errors'] = ['Tài khoản của bạn đã được xác thực email'];
            header('Location: /profile');
            exit;
        }

        [$selector, $validator, $tokenHash, $expiresAt] = $this->generateTokenPair(24, '+24 hours');
        $this->userModel->createEmailVerificationToken((int) $user['id'], $user['email'], $selector, $tokenHash, $expiresAt);

        $verifyLink = MailHelper::baseUrl() . '/auth/verifyEmail/' . $selector . '.' . $validator;
        MailHelper::send(
            $user['email'],
            'Xác thực tài khoản My Store',
            MailHelper::verificationEmail($user['full_name'], $verifyLink)
        );

        $_SESSION['success'] = 'Đã gửi lại email xác thực. Vui lòng kiểm tra hộp thư.';
        header('Location: /profile');
        exit;
    }

    /**
     * Trang xác thực email riêng để test local rõ ràng hơn.
     */
    public function emailVerification(): void
    {
        AuthHelper::requireLogin();

        $user = $this->userModel->findById(AuthHelper::getUserId());
        if (!$user) {
            $_SESSION['errors'] = ['Không tìm thấy thông tin tài khoản'];
            header('Location: /auth/login');
            exit;
        }

        if (!empty($user['email_verified_at'])) {
            $_SESSION['success'] = 'Tài khoản của bạn đã được xác thực email.';
            header('Location: /profile');
            exit;
        }

        $flash = $this->consumeFlash();
        $errors = $flash['errors'];
        $success = $flash['success'];

        include 'app/views/email_verification.php';
    }

    public function forgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleForgotPassword();
        }

        $flash = $this->consumeFlash();
        $errors = $flash['errors'];
        $success = $flash['success'];
        $old_data = $flash['old_data'];

        include 'app/views/forgot_password.php';
    }

    private function handleForgotPassword(): void
    {
        $email = trim($_POST['email'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['Vui lòng nhập email hợp lệ'];
            $_SESSION['old_data'] = ['email' => $email];
            header('Location: /auth/forgotPassword');
            exit;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user && ($user['status'] ?? 'active') === 'active' && empty($user['deleted_at'])) {
            [$selector, $validator, $tokenHash, $expiresAt] = $this->generateTokenPair(24, '+1 hour');
            $this->userModel->createPasswordResetToken((int) $user['id'], $email, $selector, $tokenHash, $expiresAt);

            $resetLink = MailHelper::baseUrl() . '/auth/resetPassword/' . $selector . '.' . $validator;
            MailHelper::send(
                $user['email'],
                'Đặt lại mật khẩu My Store',
                MailHelper::resetPasswordEmail($user['full_name'], $resetLink)
            );
        }

        $_SESSION['success'] = 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu.';
        header('Location: /auth/forgotPassword');
        exit;
    }

    public function resetPassword($token = null): void
    {
        $token = $token ?? ($_GET['token'] ?? '');
        $parts = explode('.', (string) $token, 2);
        $record = null;

        if (count($parts) === 2) {
            $record = $this->userModel->findPasswordResetBySelector($parts[0]);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleResetPassword($record);
        }

        $flash = $this->consumeFlash();
        $errors = $flash['errors'];
        $success = $flash['success'];
        $old_data = $flash['old_data'];
        $token = $token;

        include 'app/views/reset_password.php';
    }

    private function handleResetPassword($record): void
    {
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $parts = explode('.', $token, 2);
        $errors = [];

        if (count($parts) !== 2) {
            $errors[] = 'Liên kết đặt lại mật khẩu không hợp lệ';
        }
        if ($password === '') {
            $errors[] = 'Vui lòng nhập mật khẩu mới';
        }
        if ($confirmPassword === '') {
            $errors[] = 'Vui lòng xác nhận mật khẩu mới';
        }
        if ($password !== '' && strlen($password) < 8) {
            $errors[] = 'Mật khẩu mới phải có ít nhất 8 ký tự';
        }
        if ($password !== '' && $password !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không trùng khớp';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = ['token' => $token];
            header('Location: /auth/resetPassword/' . urlencode($token));
            exit;
        }

        [$selector, $validator] = $parts;
        $record = $record ?: $this->userModel->findPasswordResetBySelector($selector);

        if (
            !$record
            || !empty($record['used_at'])
            || strtotime($record['expires_at']) < time()
            || !hash_equals($record['token_hash'], hash('sha256', $validator))
        ) {
            $_SESSION['errors'] = ['Liên kết đặt lại mật khẩu đã hết hạn hoặc không hợp lệ'];
            header('Location: /auth/forgotPassword');
            exit;
        }

        $this->userModel->updatePassword((int) $record['user_id'], $password);
        $this->userModel->markPasswordResetUsed((int) $record['id']);
        $this->userModel->revokeRememberTokenByUserId((int) $record['user_id']);

        $_SESSION['success'] = 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập lại.';
        header('Location: /auth/login');
        exit;
    }

    private function issueRememberMeCookie(int $userId): void
    {
        $this->userModel->revokeRememberTokenByUserId($userId);

        [$selector, $validator, $tokenHash, $expiresAt] = $this->generateTokenPair(32, '+30 days');
        $this->userModel->createRememberToken($userId, $selector, $tokenHash, $expiresAt);

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        setcookie(
            'remember_me',
            $selector . ':' . $validator,
            [
                'expires' => strtotime($expiresAt),
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private function generateTokenPair(int $bytes, string $expiresModifier): array
    {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes($bytes));
        $tokenHash = hash('sha256', $validator);
        $expiresAt = (new DateTimeImmutable())->modify($expiresModifier)->format('Y-m-d H:i:s');

        return [$selector, $validator, $tokenHash, $expiresAt];
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
