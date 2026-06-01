-- My Store deployment SQL
-- Import this file after selecting the target database in phpMyAdmin/shared hosting.
-- No CREATE DATABASE / USE statements are included on purpose.

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `email_verification_tokens`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `product`;
DROP TABLE IF EXISTS `category`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','locked') NOT NULL DEFAULT 'active',
  `avatar` varchar(255) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: category
-- --------------------------------------------------------
CREATE TABLE `category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: product
-- --------------------------------------------------------
CREATE TABLE `product` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,0) NOT NULL DEFAULT 0,
  `category_id` int unsigned DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_product_category` (`category_id`),
  CONSTRAINT `fk_product_category`
    FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(120) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(120) DEFAULT NULL,
  `customer_address` text NOT NULL,
  `note` text DEFAULT NULL,
  `total_price` decimal(15,0) NOT NULL DEFAULT 0,
  `payment_method` enum('cod','banking') NOT NULL DEFAULT 'cod',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `status` enum('pending','confirmed','shipping','done','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: order_items
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(15,0) NOT NULL,
  `quantity` int NOT NULL DEFAULT 1,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order` (`order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `product` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: password_resets
-- --------------------------------------------------------
CREATE TABLE `password_resets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_resets_selector` (`selector`),
  KEY `idx_password_resets_email` (`email`),
  KEY `idx_password_resets_user_id` (`user_id`),
  CONSTRAINT `fk_password_resets_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: email_verification_tokens
-- --------------------------------------------------------
CREATE TABLE `email_verification_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `email` varchar(100) NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email_verification_selector` (`selector`),
  KEY `idx_email_verification_user_id` (`user_id`),
  CONSTRAINT `fk_email_verification_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: remember_tokens
-- --------------------------------------------------------
CREATE TABLE `remember_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_remember_tokens_selector` (`selector`),
  KEY `idx_remember_tokens_user_id` (`user_id`),
  CONSTRAINT `fk_remember_tokens_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: app_settings
-- --------------------------------------------------------
CREATE TABLE `app_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_app_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed data: category
-- --------------------------------------------------------
INSERT INTO `category` (`id`, `name`, `description`, `created_at`) VALUES
  (1, 'Điện thoại', 'Điện thoại di động các loại', '2026-05-18 03:23:53'),
  (2, 'Laptop', 'Máy tính xách tay', '2026-05-18 03:23:53'),
  (3, 'Phụ kiện', 'Tai nghe, sạc, cáp...', '2026-05-18 03:23:53'),
  (4, 'Máy tính bảng', 'Tablet các loại', '2026-05-18 03:23:53');

-- --------------------------------------------------------
-- Seed data: product
-- --------------------------------------------------------
INSERT INTO `product` (`id`, `name`, `description`, `price`, `category_id`, `image`, `created_at`) VALUES
  (1, 'iPhone 15 Pro', 'Chip A17 Pro, khung titanium', 29990000, 1, 'uploads/img_6a0a870e7d7cc5.26004569.jpg', '2026-05-18 03:23:53'),
  (2, 'Samsung Galaxy S24', 'Màn AMOLED 120Hz, AI Camera', 19990000, 1, 'uploads/img_6a0a86be8b3c84.27898318.jpg', '2026-05-18 03:23:53'),
  (3, 'MacBook Air M3', 'Apple Silicon M3, pin 18 giờ', 32990000, 2, 'uploads/img_6a0a868eb9f089.48289987.jpg', '2026-05-18 03:23:53'),
  (4, 'Tai nghe Sony WH-1000XM5', 'Chống ồn ANC cao cấp', 8490000, 3, 'uploads/img_6a0a8674df0387.36616151.jpg', '2026-05-18 03:23:53'),
  (5, 'OPPO Find X7', 'Sạc nhanh 100W, camera chân dung đẹp', 18990000, 1, 'uploads/img_6a0a888639ede6.37564493.webp', '2026-05-18 03:33:26'),
  (6, 'Vivo X100 Pro', 'Chip Dimensity cao cấp, chụp đêm tốt', 21990000, 1, 'uploads/img_6a0a88ec5712a6.98272298.webp', '2026-05-18 03:35:08'),
  (7, 'iPad Air 6', 'Chip Apple M2 mạnh mẽ', 19990000, 4, 'uploads/img_6a0a8b005f1151.20332591.webp', '2026-05-18 03:44:00');

-- --------------------------------------------------------
-- Seed data: orders
-- --------------------------------------------------------
INSERT INTO `orders` (`id`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `note`, `total_price`, `payment_method`, `payment_status`, `status`, `created_at`) VALUES
  (1, 'Nguyễn Văn A', '0909123456', 'vana@gmail.com', 'TP.HCM', 'Giao giờ hành chính', 49980000, 'cod', 'unpaid', 'pending', '2026-05-18 03:23:53');

-- --------------------------------------------------------
-- Seed data: order_items
-- --------------------------------------------------------
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `name`, `price`, `quantity`, `image`, `created_at`) VALUES
  (1, 1, 1, 'iPhone 15 Pro', 29990000, 1, 'uploads/img_6a0a870e7d7cc5.26004569.jpg', '2026-05-18 03:23:53'),
  (2, 1, 2, 'Samsung Galaxy S24', 19990000, 1, 'uploads/img_6a0a86be8b3c84.27898318.jpg', '2026-05-18 03:23:53');

-- --------------------------------------------------------
-- Optional admin seed
-- Replace the hash below with a real password_hash() output before using.
-- --------------------------------------------------------
INSERT IGNORE INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `role`, `status`, `email_verified_at`, `created_at`, `updated_at`)
VALUES
  (1, 'Admin Store', 'admin@mystore.com', '$2y$10$bjJi7WpHmcwsfrs9HJsyMu8WrfIL7ww/JJr87P1DjTAd8R.0r3d5W', '0909123456', '123 Đường ABC, TP.HCM', 'admin', 'active', NOW(), NOW(), NOW());
