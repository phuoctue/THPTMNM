<?php
/**
 * SETUP ADMIN ACCOUNT
 * Mở trình duyệt và truy cập: http://localhost/setup_admin.php
 */

require_once 'app/config/database.php';

$message = '';
$error = '';

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự";
    } else {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Kết nối database
            $db = new Database();
            $conn = $db->getConnection();

            // Kiểm tra email đã tồn tại
            $check = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $check->bindParam(':email', $email);
            $check->execute();

            if ($check->rowCount() > 0) {
                $error = "Email này đã tồn tại!";
            } else {
                // Insert admin account
                $sql = "INSERT INTO users (full_name, email, password, role) 
                        VALUES (:full_name, :email, :password, 'admin')";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':full_name', $full_name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);

                if ($stmt->execute()) {
                    $message = "✅ Tạo tài khoản admin thành công!<br>
                                <strong>Email:</strong> " . htmlspecialchars($email) . "<br>
                                <strong>Password:</strong> " . htmlspecialchars($password) . "<br>
                                <strong>Hash:</strong> " . htmlspecialchars($hashed_password) . "<br><br>
                                Bạn có thể đăng nhập tại: <a href='/auth/login' target='_blank'>/auth/login</a>";
                } else {
                    $error = "Lỗi khi tạo tài khoản!";
                }
            }
        } catch (Exception $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Account</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-header h1 {
            color: #333;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .setup-header p {
            color: #666;
            font-size: 0.95rem;
        }
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
        }
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        .btn-submit {
            width: 100%;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-submit:hover {
            background: linear-gradient(180deg, #5568d3 0%, #6a3d91 100%);
            color: white;
        }
        .success-info {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="setup-container">
    <div class="setup-header">
        <h1>⚙️ Setup Admin Account</h1>
        <p>Tạo tài khoản admin cho hệ thống</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <strong>❌ Lỗi!</strong> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($message)): ?>
    <form method="POST">
        <div class="form-group">
            <label for="full_name">👤 Họ và Tên</label>
            <input 
                type="text" 
                class="form-control" 
                id="full_name" 
                name="full_name" 
                placeholder="Admin"
                value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">📧 Email</label>
            <input 
                type="email" 
                class="form-control" 
                id="email" 
                name="email" 
                placeholder="admin@mystore.com"
                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">🔐 Mật khẩu</label>
            <input 
                type="password" 
                class="form-control" 
                id="password" 
                name="password" 
                placeholder="Tối thiểu 6 ký tự"
                required
            >
        </div>

        <button type="submit" class="btn-submit">
            Tạo Admin Account
        </button>
    </form>

    <div class="success-info">
        <strong>ℹ️ Lưu ý:</strong><br>
        • Email phải là duy nhất<br>
        • Mật khẩu sẽ được hash (mã hóa)<br>
        • Sau khi tạo, hãy xóa file này đi (hoặc đặt .htaccess bảo vệ)<br>
        • Đăng nhập tại: <code>/auth/login</code>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
