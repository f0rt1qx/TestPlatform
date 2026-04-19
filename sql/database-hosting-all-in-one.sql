-- ============================================================
-- Test Platform: ALL-IN-ONE schema for InfinityFree
-- Database: if0_41654195_testplatformdbb
-- Import this single file in phpMyAdmin
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `if0_41654195_testplatformdbb`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `if0_41654195_testplatformdbb`;

-- Clean rebuild (safe for re-import)
DROP TABLE IF EXISTS `otp_codes`;
DROP TABLE IF EXISTS `recordings`;
DROP TABLE IF EXISTS `screenshots`;
DROP TABLE IF EXISTS `logs`;
DROP TABLE IF EXISTS `results`;
DROP TABLE IF EXISTS `attempts`;
DROP TABLE IF EXISTS `answers`;
DROP TABLE IF EXISTS `questions`;
DROP TABLE IF EXISTS `tests`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `email_verifications`;
DROP TABLE IF EXISTS `users`;

-- USERS
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student','admin') NOT NULL DEFAULT 'student',
  `first_name` VARCHAR(80) DEFAULT NULL,
  `last_name` VARCHAR(80) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `website` VARCHAR(255) DEFAULT NULL,
  `social_vk` VARCHAR(255) DEFAULT NULL,
  `social_tg` VARCHAR(255) DEFAULT NULL,
  `birth_date` DATE DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_visit_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `settings_json` JSON DEFAULT NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`),
  INDEX `idx_users_city` (`city`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- EMAIL VERIFICATIONS
CREATE TABLE `email_verifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASSWORD RESETS
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `expires_at` TIMESTAMP NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TESTS
CREATE TABLE `tests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `time_limit` INT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Minutes',
  `max_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `pass_score` TINYINT UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Percent',
  `shuffle_questions` TINYINT(1) NOT NULL DEFAULT 1,
  `shuffle_answers` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- QUESTIONS
CREATE TABLE `questions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `test_id` INT UNSIGNED NOT NULL,
  `question_text` TEXT NOT NULL,
  `question_type` ENUM('single','multiple','text') NOT NULL DEFAULT 'single',
  `points` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `order_num` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE,
  INDEX `idx_test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ANSWERS
CREATE TABLE `answers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT UNSIGNED NOT NULL,
  `answer_text` TEXT NOT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `order_num` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
  INDEX `idx_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ATTEMPTS
CREATE TABLE `attempts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `test_id` INT UNSIGNED NOT NULL,
  `attempt_number` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `finished_at` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('in_progress','completed','abandoned','flagged') NOT NULL DEFAULT 'in_progress',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(500) DEFAULT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_test` (`user_id`, `test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RESULTS
CREATE TABLE `results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT UNSIGNED NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `test_id` INT UNSIGNED NOT NULL,
  `score` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `max_score` SMALLINT UNSIGNED NOT NULL,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `passed` TINYINT(1) NOT NULL DEFAULT 0,
  `answers_json` LONGTEXT NOT NULL,
  `time_spent` INT UNSIGNED NOT NULL DEFAULT 0,
  `cheat_score` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LOGS
CREATE TABLE `logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `event_type` ENUM(
    'tab_switch',
    'window_blur',
    'copy_attempt',
    'right_click',
    'devtools_open',
    'rapid_answer',
    'idle_too_long',
    'page_reload',
    'focus_lost',
    'fullscreen_exit',
    'admin_action',
    'recording_uploaded',
    'recording_started',
    'recording_stopped',
    'recording_error'
  ) NOT NULL,
  `event_data` JSON DEFAULT NULL,
  `severity` ENUM('low','medium','high') NOT NULL DEFAULT 'low',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_attempt` (`attempt_id`),
  INDEX `idx_user_events` (`user_id`, `event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SCREENSHOTS (left for backward compatibility; can stay unused)
CREATE TABLE `screenshots` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `type` ENUM('manual','auto') NOT NULL DEFAULT 'manual',
  `reason` VARCHAR(255) DEFAULT NULL,
  `event_data` JSON DEFAULT NULL,
  `capture_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_screenshots_attempt` (`attempt_id`),
  INDEX `idx_screenshots_user` (`user_id`),
  INDEX `idx_screenshots_created_at` (`created_at`),
  INDEX `idx_screenshots_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- RECORDINGS
CREATE TABLE `recordings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `chunk_index` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_final` TINYINT(1) NOT NULL DEFAULT 0,
  `duration` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_recordings_attempt` (`attempt_id`),
  INDEX `idx_recordings_user` (`user_id`),
  INDEX `idx_recordings_final` (`is_final`),
  INDEX `idx_recordings_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OTP CODES
CREATE TABLE `otp_codes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'login',
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '',
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `used_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_otp_email` (`email`),
  INDEX `idx_otp_user_id` (`user_id`),
  INDEX `idx_otp_expires` (`expires_at`),
  INDEX `idx_otp_used` (`used`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional default admin (change password after first login)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `email_verified`, `is_active`)
VALUES (
  'admin',
  'admin@testplatform.local',
  '$2y$10$fmPAsTojh6jhtegyHdQeQuKDjqzs2.v8J0V.Yr2fpk52qSy4mnNlC',
  'admin',
  'Admin',
  'User',
  1,
  1
);

-- Normalize bad event values if any legacy data is merged later
UPDATE `logs`
SET `event_type` = 'admin_action'
WHERE `event_type` = '';

SET FOREIGN_KEY_CHECKS = 1;

