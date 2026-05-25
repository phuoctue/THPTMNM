-- --------------------------------------------------------
-- Hosting deploy SQL for my_store
-- Safe to re-import on shared hosting/phpMyAdmin
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- NOTE:
-- 1) Do NOT include CREATE DATABASE / USE for shared hosting.
-- 2) Select your target database in phpMyAdmin before importing.

-- Reset old tables (for clean re-import)
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `product`;
DROP TABLE IF EXISTS `category`;

-- Table: category
CREATE TABLE `category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table: product
CREATE TABLE `product` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(15,0) NOT NULL DEFAULT '0',
  `category_id` int unsigned DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_product_category` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table: orders
CREATE TABLE `orders` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(120) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(120) DEFAULT NULL,
  `customer_address` text NOT NULL,
  `note` text,
  `total_price` decimal(15,0) NOT NULL DEFAULT '0',
  `payment_method` enum('cod','banking') NOT NULL DEFAULT 'cod',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `status` enum('pending','confirmed','shipping','done','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table: order_items
CREATE TABLE `order_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int unsigned NOT NULL,
  `product_id` int unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(15,0) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `image` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_order` (`order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Seed: category
INSERT INTO `category` (`id`, `name`, `description`, `created_at`) VALUES
  (1, 'Điện thoại', 'Điện thoại di động các loại', '2026-05-18 03:23:53'),
  (2, 'Laptop', 'Máy tính xách tay', '2026-05-18 03:23:53'),
  (3, 'Phụ kiện', 'Tai nghe, sạc, cáp...', '2026-05-18 03:23:53'),
  (4, 'Máy tính bảng', 'Tablet các loại', '2026-05-18 03:23:53');

-- Seed: product
INSERT INTO `product` (`id`, `name`, `description`, `price`, `category_id`, `image`, `created_at`) VALUES
  (1, 'iPhone 15 Pro', 'Chip A17 Pro, khung titanium', 29990000, 1, 'uploads/img_6a0a870e7d7cc5.26004569.jpg', '2026-05-18 03:23:53'),
  (2, 'Samsung Galaxy S24', 'Màn AMOLED 120Hz, AI Camera', 19990000, 1, 'uploads/img_6a0a86be8b3c84.27898318.jpg', '2026-05-18 03:23:53'),
  (3, 'MacBook Air M3', 'Apple Silicon M3, pin 18 giờ', 32990000, 2, 'uploads/img_6a0a868eb9f089.48289987.jpg', '2026-05-18 03:23:53'),
  (4, 'Tai nghe Sony WH-1000XM5', 'Chống ồn ANC cao cấp', 8490000, 3, 'uploads/img_6a0a8674df0387.36616151.jpg', '2026-05-18 03:23:53'),
  (5, 'OPPO Find X7', 'Sạc nhanh 100W, camera chân dung đẹp', 18990000, 1, 'uploads/img_6a0a888639ede6.37564493.webp', '2026-05-18 03:33:26'),
  (6, 'Vivo X100 Pro', 'Chip Dimensity cao cấp, chụp đêm tốt', 21990000, 1, 'uploads/img_6a0a88ec5712a6.98272298.webp', '2026-05-18 03:35:08'),
  (7, 'iPad Air 6', 'Chip Apple M2 mạnh mẽ', 19990000, 4, 'uploads/img_6a0a8b005f1151.20332591.webp', '2026-05-18 03:44:00');

-- Seed: orders
INSERT INTO `orders` (`id`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `note`, `total_price`, `payment_method`, `payment_status`, `status`, `created_at`) VALUES
  (1, 'Nguyễn Văn A', '0909123456', 'vana@gmail.com', 'TP.HCM', 'Giao giờ hành chính', 49980000, 'cod', 'unpaid', 'pending', '2026-05-18 03:23:53');

-- Seed: order_items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `name`, `price`, `quantity`, `image`, `created_at`) VALUES
  (1, 1, 1, 'iPhone 15 Pro', 29990000, 1, 'uploads/iphone15pro.jpg', '2026-05-18 03:23:53'),
  (2, 1, 2, 'Samsung Galaxy S24', 19990000, 1, 'uploads/s24.jpg', '2026-05-18 03:23:53');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
