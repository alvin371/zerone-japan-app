-- Migration: Create sync_jobs table for tracking product sync progress
-- Run this SQL in your MySQL database

CREATE TABLE IF NOT EXISTS `sync_jobs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `job_type` VARCHAR(50) NOT NULL DEFAULT 'product_sync',
  `marketplace` VARCHAR(50) NOT NULL,
  `shop_id` VARCHAR(100) NOT NULL,
  `shop_name` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
  `total_products` INT(11) DEFAULT 0,
  `processed_products` INT(11) DEFAULT 0,
  `current_page` INT(11) DEFAULT 0,
  `next_page_token` TEXT DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_marketplace_shop` (`marketplace`, `shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
