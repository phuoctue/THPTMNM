-- =====================================================
-- TẠO BẢNG USERS CHO HỆ THỐNG AUTHENTICATION
-- =====================================================

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

-- =====================================================
-- CẤU TRÚC BẢNG ORDERS (để tham khảo)
-- =====================================================
-- ALTER TABLE orders ADD COLUMN user_id INT;
-- ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id);

-- =====================================================
-- CHÈ LỎNG MỘT NGƯỜI DÙNG MẫU (TUỲ CHỌN)
-- =====================================================
-- INSERT INTO users (full_name, email, password, phone, address, role)
-- VALUES ('Admin Store', 'admin@mystore.com', '$2y$10$...', '0909123456', '123 Đường ABC, TP.HCM', 'admin');
