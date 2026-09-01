CREATE DATABASE IF NOT EXISTS `keys` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `keys`;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sku` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(255) NOT NULL,
  `type` VARCHAR(20) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'RUB',
  `image` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(40) NOT NULL UNIQUE,
  `product_sku` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `currency` CHAR(3) NOT NULL,
  `final_amount` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'created',
  `promocode_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_orders_status` (`status`),
  INDEX `idx_orders_sku` (`product_sku`)
);

CREATE TABLE IF NOT EXISTS `keys` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sku` VARCHAR(50) NOT NULL,
  `code` VARCHAR(100) NOT NULL UNIQUE,
  `order_id` INT NULL,
  `status` ENUM('available','reserved','issued') NOT NULL DEFAULT 'available',
  `reserved_at` TIMESTAMP NULL DEFAULT NULL,
  `issued_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_keys_sku_status_order` (`sku`, `status`, `order_id`)
);

CREATE TABLE IF NOT EXISTS `gateway_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` VARCHAR(100) NOT NULL UNIQUE,
  `order_reference` VARCHAR(100) NOT NULL,
  `payload` JSON NOT NULL,
  `processed_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `supplier_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `request_id` VARCHAR(100) NOT NULL UNIQUE,
  `order_id` INT NOT NULL,
  `sku` VARCHAR(50) NOT NULL,
  `code` VARCHAR(100) NULL,
  `status` ENUM('pending','success','error','out_of_stock') NOT NULL DEFAULT 'pending',
  `attempts` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `promocodes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('percent','amount') NOT NULL,
  `value` DECIMAL(10,2) NOT NULL,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'RUB',
  `max_uses` INT NOT NULL,
  `used_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `promocode_usages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `promocode_id` INT NOT NULL,
  `order_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_promo_order` (`promocode_id`, `order_id`)
);
