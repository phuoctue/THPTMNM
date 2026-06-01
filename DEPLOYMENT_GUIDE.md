# Hướng dẫn deploy My Store lên hosting thật

## 1. Chuẩn bị source code

1. Commit hoặc zip toàn bộ project.
2. Đảm bảo có các file:
   - `index.php`
   - `app/`
   - `vendor/` nếu hosting không cho chạy `composer install`
   - `WebBanHang.sql`
   - `.env` hoặc dùng trang cấu hình SMTP trong admin sau khi deploy

## 2. Tạo database trên hosting

1. Vào cPanel hoặc phpMyAdmin của hosting.
2. Tạo database mới, ví dụ `my_store`.
3. Tạo user MySQL mới và cấp toàn quyền cho database đó.
4. Ghi lại:
   - DB host
   - DB name
   - DB user
   - DB password

## 3. Import dữ liệu

1. Mở phpMyAdmin.
2. Chọn database vừa tạo.
3. Import file `WebBanHang.sql`.
4. Nếu import lỗi, kiểm tra:
   - MySQL version
   - quyền foreign key
   - đã chọn đúng database chưa

## 4. Cấu hình file `.env`

### Local

File `.env` mẫu:

```env
APP_URL=http://localhost:8080
DB_HOST=localhost
DB_NAME=my_store
DB_USER=root
DB_PASS=
MAIL_MAILER=sendmail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@mystore.local
MAIL_FROM_NAME="My Store"
```

### Production

Chỉnh lại:

```env
APP_URL=https://yourdomain.com
DB_HOST=...
DB_NAME=...
DB_USER=...
DB_PASS=...
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-mail-provider.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@yourdomain.com
MAIL_FROM_NAME="My Store"
```

## 5. Upload source lên hosting

1. Upload toàn bộ mã nguồn vào thư mục public của domain:
   - `public_html/`
   - hoặc thư mục document root mà hosting yêu cầu
2. Nếu hosting yêu cầu, đảm bảo `index.php` là file vào mặc định.
3. Nếu web nằm trong thư mục con, sửa `.htaccess` hoặc cấu hình domain cho đúng path.

## 6. Kiểm tra autoload của Composer

### Nếu hosting hỗ trợ SSH

Chạy:

```bash
composer install --no-dev
```

### Nếu không có SSH

1. Chạy `composer install` trên máy local.
2. Upload luôn thư mục `vendor/` lên hosting.

## 7. Cấu hình SMTP trong admin

1. Đăng nhập admin.
2. Vào menu `Cấu hình SMTP`.
3. Nhập:
   - `APP_URL`
   - `MAIL_MAILER`
   - `MAIL_HOST`
   - `MAIL_PORT`
   - `MAIL_USERNAME`
   - `MAIL_PASSWORD`
   - `MAIL_ENCRYPTION`
   - `MAIL_FROM_ADDRESS`
   - `MAIL_FROM_NAME`
4. Lưu lại.
5. Gửi thử email xác thực hoặc quên mật khẩu.

## 8. Tạo admin mặc định

Sau khi import SQL, hệ thống đã có admin mặc định:

- Email: `admin@mystore.com`
- Password: `Admin@123456`

Nên đổi mật khẩu ngay sau khi đăng nhập lần đầu.

## 9. Kiểm tra email xác thực

1. Đăng ký tài khoản mới.
2. Mở email xác thực.
3. Bấm link xác thực.
4. Đăng nhập lại để dùng đầy đủ chức năng.

## 10. Checklist khi deploy lỗi

1. Không đăng nhập được:
   - kiểm tra DB host/user/pass
   - kiểm tra bảng `users`
2. Không gửi được email:
   - kiểm tra `MAIL_HOST`
   - kiểm tra `MAIL_PORT`
   - kiểm tra `MAIL_USERNAME`
   - kiểm tra `MAIL_PASSWORD`
   - kiểm tra `MAIL_ENCRYPTION`
3. Link email sai domain:
   - kiểm tra `APP_URL`
4. Link xác thực báo hết hạn:
   - token cũ đã bị thay bằng token mới
   - mở email mới nhất

## 11. Khuyến nghị bảo mật

1. Không commit `.env` lên git.
2. Không để lộ mật khẩu SMTP.
3. Dùng App Password thay vì mật khẩu thường.
4. Bật HTTPS cho domain.
5. Nếu có thể, thêm SPF/DKIM/DMARC cho domain gửi mail.
