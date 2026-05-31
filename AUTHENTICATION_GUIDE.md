# 🔐 HƯỚNG DẪN CHỨC NĂNG AUTHENTICATION (Xác thực người dùng)

## 📋 Mục lục
1. [Cài đặt & Cấu hình](#cài-đặt--cấu-hình)
2. [Các file được tạo](#các-file-được-tạo)
3. [Cách hoạt động](#cách-hoạt-động)
4. [Sử dụng trong Controllers](#sử-dụng-trong-controllers)
5. [Sử dụng trong Views](#sử-dụng-trong-views)
6. [Troubleshooting](#troubleshooting)

---

## ✅ Cài đặt & Cấu hình

### 1. Tạo bảng Users trong Database

Chạy câu lệnh SQL sau trong phpMyAdmin hoặc command line MySQL:

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. Kiểm tra cấu hình Database

Đảm bảo file `app/config/database.php` có kết nối đúng:

```php
$this->db_name = "my_store";     // Tên database
$this->username = "root";         // Username MySQL
$this->password = "";             // Password MySQL
```

### 3. Kiểm tra .htaccess

File `.htaccess` đã được cấu hình sẵn để route URL. Không cần thay đổi.

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

---

## 📂 Các file được tạo

### Model
- **app/models/UserModel.php** - Quản lý dữ liệu người dùng

### Controller
- **app/controllers/AuthController.php** - Xử lý đăng ký, đăng nhập, đăng xuất

### Helper
- **app/libs/AuthHelper.php** - Hỗ trợ kiểm tra trạng thái đăng nhập

### Views
- **app/views/register.php** - Form đăng ký
- **app/views/login.php** - Form đăng nhập

### Updated Files
- **app/views/shares/header.php** - Cập nhật để hiển thị nút đăng nhập/đăng xuất

---

## 🔄 Cách hoạt động

### Luồng Đăng Ký (Register)
```
1. Người dùng truy cập: /auth/register
2. AuthController->register() hiển thị form đăng ký
3. User nhập: full_name, email, password, phone, address
4. Form submit POST đến: /auth/register
5. AuthController->handleRegister() xử lý:
   - Validate dữ liệu
   - Kiểm tra email không trùng
   - Hash password (bcrypt)
   - Lưu vào database via UserModel
6. Nếu thành công: Redirect đến /auth/login
7. Nếu lỗi: Quay lại form với thông báo lỗi
```

### Luồng Đăng Nhập (Login)
```
1. Người dùng truy cập: /auth/login
2. AuthController->login() hiển thị form đăng nhập
3. User nhập: email, password
4. Form submit POST đến: /auth/login
5. AuthController->handleLogin() xử lý:
   - Validate dữ liệu
   - Tìm người dùng bằng email (UserModel->findByEmail)
   - Verify password (password_verify)
6. Nếu đúng: Tạo session và redirect về /
7. Nếu sai: Quay lại form với thông báo lỗi
```

### Luồng Đăng Xuất (Logout)
```
1. Người dùng click nút "Đăng xuất" trong navbar
2. Truy cập: /auth/logout
3. AuthController->logout() xử lý:
   - Xóa tất cả $_SESSION
   - Destroy session
   - Redirect về /
```

---

## 💻 Sử dụng trong Controllers

### Kiểm tra người dùng đã đăng nhập

```php
<?php
require_once 'app/libs/AuthHelper.php';

class MyController {
    public function myAction() {
        // Kiểm tra đã đăng nhập chưa
        if (!AuthHelper::isLoggedIn()) {
            header('Location: /auth/login');
            exit;
        }

        // Lấy thông tin người dùng
        $user = AuthHelper::getCurrentUser();
        echo "Xin chào " . $user['name'];
    }

    public function adminOnly() {
        // Chỉ admin mới có quyền truy cập
        AuthHelper::requireAdmin();
        // Code tiếp tục chỉ khi là admin
    }
}
?>
```

### Các method của AuthHelper

```php
// Kiểm tra đã đăng nhập
$isLogged = AuthHelper::isLoggedIn();  // true/false

// Kiểm tra là admin
$isAdmin = AuthHelper::isAdmin();  // true/false

// Lấy thông tin người dùng
$user = AuthHelper::getCurrentUser();  // array
// Kết quả: ['id' => 1, 'email' => '...', 'name' => '...', 'role' => 'customer']

// Lấy tên người dùng
$name = AuthHelper::getUserName();  // string

// Lấy email
$email = AuthHelper::getUserEmail();  // string

// Lấy ID
$id = AuthHelper::getUserId();  // int

// Yêu cầu phải đăng nhập (redirect nếu chưa)
AuthHelper::requireLogin();

// Yêu cầu phải là admin (redirect nếu không phải)
AuthHelper::requireAdmin();

// Chuyển hướng đến trang đã yêu cầu trước đăng nhập
AuthHelper::redirectAfterLogin();
```

---

## 🎨 Sử dụng trong Views

### Kiểm tra trạng thái trong View

```php
<?php
require_once 'app/libs/AuthHelper.php';

if (AuthHelper::isLoggedIn()) {
    $user = AuthHelper::getCurrentUser();
    echo "Chào " . htmlspecialchars($user['name']);
    echo '<a href="/auth/logout">Đăng xuất</a>';
} else {
    echo '<a href="/auth/login">Đăng nhập</a>';
    echo '<a href="/auth/register">Đăng ký</a>';
}
?>
```

### Ví dụ: Trang chỉ dành cho người đã đăng nhập

```php
<?php
require_once 'app/libs/AuthHelper.php';

// Yêu cầu phải đăng nhập
AuthHelper::requireLogin();

// Code dưới đây chỉ chạy khi đã đăng nhập
$user = AuthHelper::getCurrentUser();
?>

<h1>Tài khoản của <?php echo htmlspecialchars($user['name']); ?></h1>
<p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
```

---

## 🔑 Bảo mật

### Mật khẩu
- ✅ Được hash bằng **BCRYPT** (password_hash)
- ✅ Được xác minh bằng **password_verify**
- ❌ Không lưu mật khẩu plain text
- ✅ Yêu cầu tối thiểu 6 ký tự

### SQL Injection
- ✅ Sử dụng **Prepared Statements** (PDO::prepare)
- ✅ Tất cả dữ liệu được bind thông qua bindParam
- ✅ Không concatenate SQL string

### Validation
- ✅ Email hợp lệ (filter_var)
- ✅ Kiểm tra trường không trống
- ✅ Trim và sanitize dữ liệu
- ✅ htmlspecialchars khi hiển thị

### Session
- ✅ Sử dụng $_SESSION để quản lý trạng thái
- ✅ Xóa session khi đăng xuất
- ✅ Flash message để hiển thị thông báo tạm thời

---

## 📝 Ví dụ sử dụng

### Ví dụ 1: Trang chủ với kiểm tra đăng nhập

```php
<?php
// File: app/controllers/HomeController.php
require_once 'app/libs/AuthHelper.php';

class HomeController {
    public function index() {
        $isLoggedIn = AuthHelper::isLoggedIn();
        $user = $isLoggedIn ? AuthHelper::getCurrentUser() : null;

        include 'app/views/home.php';
    }
}
?>
```

### Ví dụ 2: Trang cá nhân (yêu cầu đăng nhập)

```php
<?php
// File: app/controllers/ProfileController.php
require_once 'app/libs/AuthHelper.php';

class ProfileController {
    public function index() {
        // Yêu cầu phải đăng nhập
        AuthHelper::requireLogin();

        $user = AuthHelper::getCurrentUser();
        // Lấy thông tin chi tiết người dùng từ database...
        
        include 'app/views/profile.php';
    }

    public function edit() {
        AuthHelper::requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Xử lý cập nhật profile
        }
        
        include 'app/views/profile-edit.php';
    }
}
?>
```

### Ví dụ 3: Trang admin (yêu cầu là admin)

```php
<?php
// File: app/controllers/DashboardController.php
require_once 'app/libs/AuthHelper.php';

class DashboardController {
    public function index() {
        // Yêu cầu phải là admin
        AuthHelper::requireAdmin();

        // Code admin chỉ chạy nếu là admin
        include 'app/views/dashboard.php';
    }
}
?>
```

---

## 🐛 Troubleshooting

### Vấn đề: Không thể đăng nhập
**Nguyên nhân:**
- Database chưa có bảng users
- Database connection sai
- Email không tồn tại
- Password sai

**Giải pháp:**
1. Kiểm tra bảng users tồn tại: `SHOW TABLES;`
2. Kiểm tra database.php có đúng host/user/password
3. Kiểm tra lại email/password

### Vấn đề: Session không hoạt động
**Nguyên nhân:**
- session_start() chưa được gọi
- SESSION_NONE check failed

**Giải pháp:**
- Đảm bảo file index.php gọi `session_start()` ở đầu

### Vấn đề: Email đã tồn tại nhưng vẫn register được
**Nguyên nhân:**
- Kiểm tra UNIQUE constraint chưa được áp dụng

**Giải pháp:**
```sql
ALTER TABLE users ADD UNIQUE KEY unique_email (email);
```

### Vấn đề: Mật khẩu hash không khớp
**Nguyên nhân:**
- Sử dụng function hash sai
- Password bị thay đổi

**Giải pháp:**
- Sử dụng password_hash() và password_verify()
- Không sửa mật khẩu trực tiếp trong database

---

## 🚀 Tính năng tiếp theo (có thể thêm)

- [ ] Quên mật khẩu / Reset password
- [ ] Xác minh email (Email verification)
- [ ] Đăng nhập bằng Google/Facebook
- [ ] Two-factor authentication (2FA)
- [ ] Remember me chức năng thực sự
- [ ] User profile management
- [ ] Change password
- [ ] Activity log

---

## 📞 Hỗ trợ

Nếu gặp vấn đề, hãy kiểm tra:
1. ✅ Database connection
2. ✅ Bảng users tồn tại
3. ✅ File permissions (app/libs, app/models, app/controllers)
4. ✅ Session hoạt động bình thường
5. ✅ .htaccess được kích hoạt (mod_rewrite)

---

**Tạo bởi**: PHP MVC Authentication System  
**Ngày tạo**: 2024  
**Phiên bản**: 1.0
