<?php

require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/AuthMiddleware.php';
require_once 'app/libs/JwtHelper.php';
require_once 'app/libs/MailHelper.php';
require_once 'app/models/UserModel.php';

class AuthApiController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function register(): void
    {
        $data = $this->requestData();
        $errors = $this->validateRegisterPayload($data);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        if ($this->userModel->emailExists((string) $data['email'])) {
            ApiResponse::error('Validation failed', ['email' => 'Email đã được sử dụng'], 422);
        }

        $userId = $this->userModel->register([
            'full_name' => trim((string) $data['full_name']),
            'email' => trim((string) $data['email']),
            'password' => (string) $data['password'],
            'phone' => trim((string) ($data['phone'] ?? '')),
            'address' => trim((string) ($data['address'] ?? '')),
            'role' => 'customer',
        ]);

        if (!$userId) {
            ApiResponse::error('User creation failed', null, 400);
        }

        [$selector, $validator, $tokenHash, $expiresAt] = $this->generateTokenPair(24, '+24 hours');
        $this->userModel->createEmailVerificationToken($userId, (string) $data['email'], $selector, $tokenHash, $expiresAt);

        $verifyLink = MailHelper::baseUrl() . '/api/auth/verifyEmail?token=' . $selector . '.' . $validator;
        $mailSent = MailHelper::send(
            (string) $data['email'],
            'Xác thực tài khoản My Store',
            MailHelper::verificationEmail((string) $data['full_name'], $verifyLink)
        );

        ApiResponse::success(
            $mailSent
                ? 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.'
                : 'Đăng ký thành công, nhưng hệ thống chưa gửi được email xác thực.',
            [
                'user_id' => $userId,
                'email_verification_sent' => (bool) $mailSent,
            ],
            201
        );
    }

    public function login(): void
    {
        $data = $this->requestData();
        $errors = $this->validateLoginPayload($data);

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        $email = trim((string) $data['email']);
        $password = (string) $data['password'];
        $rememberMe = !empty($data['remember_me']);
        $user = $this->userModel->findByEmail($email);

        if (
            !$user
            || !password_verify($password, $user['password'])
            || ($user['status'] ?? 'active') !== 'active'
            || !empty($user['deleted_at'])
        ) {
            ApiResponse::error('Email hoặc mật khẩu không chính xác', null, 401);
        }

        session_regenerate_id(true);
        AuthHelper::setUserSession($user);

        if ($rememberMe) {
            $this->issueRememberMeCookie((int) $user['id']);
        }

        $token = JwtHelper::encode([
            'sub' => (int) $user['id'],
            'email' => $user['email'] ?? '',
            'name' => $user['full_name'] ?? '',
            'role' => $user['role'] ?? 'customer',
            'status' => $user['status'] ?? 'active',
            'avatar' => $user['avatar'] ?? null,
            'email_verified_at' => $user['email_verified_at'] ?? null,
        ], 7200);

        ApiResponse::success('Đăng nhập thành công', [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 7200,
            'user' => $this->makeUserPayload($user),
        ]);
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

        ApiResponse::success('Đã đăng xuất thành công');
    }

    public function me(): void
    {
        $payload = AuthMiddleware::authenticate();
        $user = $this->userModel->findById((int) $payload['sub']);
        if (!$user) {
            ApiResponse::error('User not found', null, 404);
        }

        ApiResponse::success('User retrieved successfully', $this->makeUserPayload($user));
    }

    public function verifyEmail(): void
    {
        $data = $this->requestData();
        $token = trim((string) ($data['token'] ?? ($_GET['token'] ?? '')));
        $parts = explode('.', $token, 2);

        if (count($parts) !== 2) {
            ApiResponse::error('Liên kết xác thực không hợp lệ', null, 422);
        }

        [$selector, $validator] = $parts;
        $record = $this->userModel->findEmailVerificationBySelector($selector);

        if (
            !$record
            || !empty($record['verified_at'])
            || strtotime($record['expires_at']) < time()
            || !hash_equals($record['token_hash'], hash('sha256', $validator))
        ) {
            ApiResponse::error('Liên kết xác thực đã hết hạn hoặc không hợp lệ', null, 422);
        }

        $this->userModel->verifyEmail((int) $record['user_id']);
        $this->userModel->markEmailVerificationUsed((int) $record['id']);

        ApiResponse::success('Xác thực email thành công');
    }

    public function resendVerification(): void
    {
        if (!AuthHelper::isLoggedIn()) {
            ApiResponse::error('Unauthorized', null, 401);
        }

        $user = $this->userModel->findById(AuthHelper::getUserId() ?? 0);
        if (!$user) {
            ApiResponse::error('User not found', null, 404);
        }

        if (!empty($user['email_verified_at'])) {
            ApiResponse::error('Tài khoản đã được xác thực email', null, 422);
        }

        [$selector, $validator, $tokenHash, $expiresAt] = $this->generateTokenPair(24, '+24 hours');
        $this->userModel->createEmailVerificationToken((int) $user['id'], $user['email'], $selector, $tokenHash, $expiresAt);

        $verifyLink = MailHelper::baseUrl() . '/api/auth/verifyEmail?token=' . $selector . '.' . $validator;
        MailHelper::send(
            $user['email'],
            'Xác thực tài khoản My Store',
            MailHelper::verificationEmail($user['full_name'], $verifyLink)
        );

        ApiResponse::success('Đã gửi lại email xác thực');
    }

    public function forgotPassword(): void
    {
        $data = $this->requestData();
        $email = trim((string) ($data['email'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            ApiResponse::error('Validation failed', ['email' => 'Vui lòng nhập email hợp lệ'], 422);
        }

        $user = $this->userModel->findByEmail($email);
        if ($user && ($user['status'] ?? 'active') === 'active' && empty($user['deleted_at'])) {
            [$selector, $validator, $tokenHash, $expiresAt] = $this->generateTokenPair(24, '+1 hour');
            $this->userModel->createPasswordResetToken((int) $user['id'], $email, $selector, $tokenHash, $expiresAt);

            $resetLink = MailHelper::baseUrl() . '/api/auth/resetPassword?token=' . $selector . '.' . $validator;
            MailHelper::send(
                $user['email'],
                'Đặt lại mật khẩu My Store',
                MailHelper::resetPasswordEmail($user['full_name'], $resetLink)
            );
        }

        ApiResponse::success('Nếu email tồn tại trong hệ thống, chúng tôi đã gửi liên kết đặt lại mật khẩu');
    }

    public function resetPassword(): void
    {
        $data = $this->requestData();
        $token = trim((string) ($data['token'] ?? ($_GET['token'] ?? '')));
        $password = (string) ($data['password'] ?? '');
        $confirmPassword = (string) ($data['confirm_password'] ?? '');
        $errors = [];

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            $errors['token'] = 'Liên kết đặt lại mật khẩu không hợp lệ';
        }
        if ($password === '' || strlen($password) < 8) {
            $errors['password'] = 'Mật khẩu mới phải có ít nhất 8 ký tự';
        }
        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Vui lòng xác nhận mật khẩu mới';
        }
        if ($password !== '' && $confirmPassword !== '' && $password !== $confirmPassword) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không trùng khớp';
        }

        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        [$selector, $validator] = $parts;
        $record = $this->userModel->findPasswordResetBySelector($selector);

        if (
            !$record
            || !empty($record['used_at'])
            || strtotime($record['expires_at']) < time()
            || !hash_equals($record['token_hash'], hash('sha256', $validator))
        ) {
            ApiResponse::error('Liên kết đặt lại mật khẩu đã hết hạn hoặc không hợp lệ', null, 422);
        }

        $this->userModel->updatePassword((int) $record['user_id'], $password);
        $this->userModel->markPasswordResetUsed((int) $record['id']);
        $this->userModel->revokeRememberTokenByUserId((int) $record['user_id']);

        ApiResponse::success('Đặt lại mật khẩu thành công');
    }

    private function requestData(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        return is_array($json) ? $json : [];
    }

    private function validateRegisterPayload(array $data): array
    {
        $errors = [];
        $fullName = trim((string) ($data['full_name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $confirmPassword = (string) ($data['confirm_password'] ?? '');
        $phone = trim((string) ($data['phone'] ?? ''));

        if ($fullName === '') {
            $errors['full_name'] = 'Vui lòng nhập họ và tên';
        }
        if ($email === '') {
            $errors['email'] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }
        if ($password === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 8 ký tự';
        }
        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Vui lòng xác nhận mật khẩu';
        } elseif ($password !== '' && $password !== $confirmPassword) {
            $errors['confirm_password'] = 'Mật khẩu không trùng khớp';
        }
        if ($phone !== '') {
            $normalizedPhone = preg_replace('/[\s\-\(\)]/', '', $phone);
            if (!preg_match('/^(?:\+?[0-9]{7,15}|0[0-9]{8,10})$/', $normalizedPhone)) {
                $errors['phone'] = 'Số điện thoại không hợp lệ';
            }
        }

        return $errors;
    }

    private function validateLoginPayload(array $data): array
    {
        $errors = [];
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '') {
            $errors['email'] = 'Vui lòng nhập email';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không hợp lệ';
        }
        if ($password === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu';
        }

        return $errors;
    }

    private function generateTokenPair(int $bytes, string $expiresModifier): array
    {
        $selector = bin2hex(random_bytes(8));
        $validator = bin2hex(random_bytes($bytes));
        $tokenHash = hash('sha256', $validator);
        $expiresAt = (new DateTimeImmutable())->modify($expiresModifier)->format('Y-m-d H:i:s');

        return [$selector, $validator, $tokenHash, $expiresAt];
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
