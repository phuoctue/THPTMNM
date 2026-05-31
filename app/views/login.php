<?php
require_once 'app/libs/ViewHelper.php';
$flash = ViewHelper::consumeFlash();
$old_data = $flash['old_data'];
$errors = $flash['errors'];
$success = $flash['success'];

$emailErrors = ['Vui lòng nhập email', 'Email không hợp lệ'];
$loginErrors = ['Email hoặc mật khẩu không chính xác'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - My Store</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #4f46e5; 
            --primary-d: #3730a3; 
            --accent: #f59e0b; 
            --dark: #1e1b4b; 
            --light-bg: #f5f5ff; 
        }
        
        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .auth-header {
            background: linear-gradient(180deg, #5c53f0 0%, #4f46e5 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .auth-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 10px 0;
        }

        .auth-header p {
            font-size: 0.95rem;
            margin: 0;
            opacity: 0.9;
        }

        .auth-body {
            padding: 40px 30px;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            font-weight: 500;
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-danger {
            background-color: #fee;
            color: #c33;
        }

        .alert-success {
            background-color: #efe;
            color: #3c3;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(180deg, #5c53f0 0%, #4f46e5 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-login:hover {
            background: linear-gradient(180deg, #4338ca 0%, #3730a3 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        .auth-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
            margin-top: 20px;
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .remember-check {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-check input[type="checkbox"] {
            cursor: pointer;
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
            padding: 0;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e5e7eb;
        }

        .divider span {
            background: white;
            padding: 0 10px;
            position: relative;
            color: #999;
            font-size: 0.9rem;
        }

        .btn-register-link {
            width: 100%;
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            border: 2px solid var(--primary);
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-register-link:hover {
            background: var(--primary);
            color: white;
        }

        @media (max-width: 576px) {
            .auth-container {
                box-shadow: none;
            }

            .auth-header {
                padding: 30px 20px;
            }

            .auth-header h1 {
                font-size: 1.5rem;
            }

            .auth-body {
                padding: 30px 20px;
            }

            .auth-footer {
                flex-direction: column;
                gap: 10px;
            }

            .auth-footer .remember-check {
                order: 2;
            }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <!-- HEADER -->
    <div class="auth-header">
        <h1><i class="fas fa-sign-in-alt"></i> Đăng Nhập</h1>
        <p>Chào mừng bạn quay lại cửa hàng</p>
    </div>

    <!-- BODY -->
    <div class="auth-body">
        <?php require 'app/views/shares/flash.php'; ?>

        <!-- FORM ĐĂNG NHẬP -->
        <form method="POST" action="/auth/login" novalidate>
            
            <!-- EMAIL -->
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input 
                    type="email" 
                    class="form-control <?php echo array_intersect($errors, $emailErrors) ? 'is-invalid' : ''; ?>" 
                    id="email" 
                    name="email" 
                    placeholder="example@gmail.com" 
                    value="<?php echo htmlspecialchars($old_data['email'] ?? ''); ?>"
                    required
                >
                <?php if (array_intersect($errors, $emailErrors)): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars(array_values(array_intersect($errors, $emailErrors))[0]); ?></div>
                <?php elseif (array_intersect($errors, $loginErrors)): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars(array_values(array_intersect($errors, $loginErrors))[0]); ?></div>
                <?php endif; ?>
            </div>

            <!-- MẬT KHẨU -->
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Mật khẩu
                </label>
                <div class="password-field">
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Nhập mật khẩu" 
                        required
                    >
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- NÚT ĐĂNG NHẬP -->
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Đăng Nhập
            </button>
        </form>

        <!-- NHỚ MẬT KHẨU & QUÊN MẬT KHẨU -->
        <div class="auth-footer">
            <label class="remember-check" style="margin: 0;">
                <input type="checkbox" name="remember_me">
                <span>Nhớ mật khẩu</span>
            </label>
            <a href="#forgot-password" title="Chức năng sắp có">
                <i class="fas fa-redo"></i> Quên mật khẩu?
            </a>
        </div>

        <!-- HOẶC ĐĂNG KÝ -->
        <div class="divider">
            <span>hoặc</span>
        </div>

        <!-- NÚT ĐĂNG KÝ -->
        <a href="/auth/register" class="btn-register-link">
            <i class="fas fa-user-plus"></i> Tạo tài khoản mới
        </a>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
    // HÀM CHUYỂN ĐỔI HIỂN THỊ/ẨN MẬT KHẨU
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const isPassword = field.type === 'password';
        field.type = isPassword ? 'text' : 'password';
    }

    // LƯU GHI NHỚ MẬT KHẨU TRONG LOCAL STORAGE (TUỲ CHỌN)
    document.querySelector('form').addEventListener('submit', function(e) {
        const rememberMe = document.querySelector('input[name="remember_me"]').checked;
        const email = document.getElementById('email').value;

        if (rememberMe && email) {
            localStorage.setItem('remembered_email', email);
        }
    });

    // LÀM TRÀN ĐẶT LẠI EMAIL NẾU CÓ GHI NHỚ
    window.addEventListener('load', function() {
        const remembered = localStorage.getItem('remembered_email');
        if (remembered) {
            document.getElementById('email').value = remembered;
            document.querySelector('input[name="remember_me"]').checked = true;
        }
    });
</script>

</body>
</html>
