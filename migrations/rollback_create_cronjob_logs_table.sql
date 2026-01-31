-- Rollback: Drop cronjob_logs table
-- Run: mysql -u root -p your_database < migrations/rollback_create_cronjob_logs_table.sql

DROP TABLE IF EXISTS `cronjob_logs`;

DELETE FROM `modules` WHERE `name` = 'cronjob_log';
