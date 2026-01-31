-- Create cronjob_logs table for tracking cronjob execution history
-- Run: mysql -u root -p your_database < migrations/create_cronjob_logs_table.sql

CREATE TABLE IF NOT EXISTS `cronjob_logs` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `job_name` VARCHAR(100) NOT NULL,
    `job_type` VARCHAR(50) NOT NULL COMMENT 'sync, generate, update, cleanup',
    `status` ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    `trigger_source` ENUM('scheduled', 'manual', 'webhook') DEFAULT 'scheduled',
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `duration_seconds` DECIMAL(10,3) NULL,
    `total_items` INT(11) UNSIGNED DEFAULT 0,
    `processed_items` INT(11) UNSIGNED DEFAULT 0,
    `failed_items` INT(11) UNSIGNED DEFAULT 0,
    `skipped_items` INT(11) UNSIGNED DEFAULT 0,
    `error_message` TEXT NULL,
    `details` JSON NULL,
    `triggered_by` INT(11) NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_job_name` (`job_name`),
    INDEX `idx_status` (`status`),
    INDEX `idx_started_at` (`started_at`),
    INDEX `idx_job_type` (`job_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add module permission for cronjob_log
INSERT INTO `modules` (`name`, `display_name`, `controller`, `icon`, `sort_order`, `is_active`, `created_at`)
VALUES ('cronjob_log', 'Cronjob Logs', 'cronjob_log', 'bi bi-clock-history', 999, 1, NOW())
ON DUPLICATE KEY UPDATE `display_name` = VALUES(`display_name`);
