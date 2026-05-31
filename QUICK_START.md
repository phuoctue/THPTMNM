# 🚀 QUICK START - Hệ thống Xác thực (Authentication)

## ⚡ Bước nhanh (5 phút)

### 1️⃣ Tạo bảng Users

Chạy SQL này trong **phpMyAdmin** (database: `my_store`):

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

### 2️⃣ Các URL chính

| Chức năng | URL | Phương thức |
|-----------|-----|-----------|
| 📝 Đăng ký | `/auth/register` | GET / POST |
| 🔑 Đăng nhập | `/auth/login` | GET / POST |
| 🚪 Đăng xuất | `/auth/logout` | GET |

### 3️⃣ Test ngay

**Đăng ký:**
- Truy cập: `http://localhost/auth/register`
- Nhập: full_name, email, password (6+ ký tự), phone, address
- Nhấn: Đăng ký

**Đăng nhập:**
- Truy cập: `http://localhost/auth/login`
- Nhập: email + password vừa đăng ký
- Nhấn: Đăng nhập

**Đăng xuất:**
- Nhấn nút "Đăng xuất" ở navbar (góc trên phải)

### 4️⃣ Sử dụng trong Controller của bạn

```php
<?php
require_once 'app/libs/AuthHelper.php';

class MyController {
    public function myAction() {
        // Kiểm tra đã đăng nhập
        if (!AuthHelper::isLoggedIn()) {
            header('Location: /auth/login');
            exit;
        }

        // Lấy thông tin người dùng
        $user = AuthHelper::getCurrentUser();
        // $user['id'], $user['name'], $user['email'], $user['role']
    }

    public function adminAction() {
        // Chỉ admin mới vào được
        AuthHelper::requireAdmin();
        // Các code tiếp tục...
    }
}
?>
```

### 5️⃣ Sử dụng trong View

```php
<?php
require_once 'app/libs/AuthHelper.php';

if (AuthHelper::isLoggedIn()) {
    $user = AuthHelper::getCurrentUser();
    echo "Xin chào " . htmlspecialchars($user['name']);
}
?>
```

---

## 📁 File được tạo

```
app/
├── libs/
│   └── AuthHelper.php              ✨ Helper kiểm tra đăng nhập
├── models/
│   └── UserModel.php               ✨ Model quản lý users
├── controllers/
│   └── AuthController.php           ✨ Controller xác thực
└── views/
    ├── register.php                 ✨ Form đăng ký
    ├── login.php                    ✨ Form đăng nhập
    └── shares/header.php            🔄 (Cập nhật navbar)
```

---

## 🔒 Bảo mật

✅ Password hash: `password_hash()` + BCRYPT  
✅ SQL Injection: Prepared Statements (PDO)  
✅ Validation: Email, trống, độ dài  
✅ Session: Tự động tạo/xóa  
✅ XSS: htmlspecialchars()  

---

## 📖 Chi tiết

Xem file `AUTHENTICATION_GUIDE.md` để tài liệu đầy đủ.

---

## 🆘 Lỗi phổ biến

| Lỗi | Giải pháp |
|-----|----------|
| "Email đã được sử dụng" | Kiểm tra email trong database |
| "Email hoặc mật khẩu không chính xác" | Kiểm tra email/password |
| Session không lưu | Đảm bảo `session_start()` ở index.php |
| 404 /auth/register | Kiểm tra .htaccess được bật |

---

**Bạn đã sẵn sàng! 🎉**

Hãy đăng ký tài khoản test ngay: `/auth/register`
