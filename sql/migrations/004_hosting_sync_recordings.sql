-- ============================================================
-- Migration: Hosting sync for production (InfinityFree)
-- Database: if0_41654195_testplatformdb
-- Purpose:
-- 1) Allow admin logs without attempt_id (ON DELETE SET NULL)
-- 2) Ensure recordings table exists
-- 3) Align logs.event_type enum with current backend events
-- 4) Normalize previously broken enum values ('')
-- ============================================================

-- 1) logs.attempt_id should allow NULL
ALTER TABLE `logs`
  MODIFY COLUMN `attempt_id` INT UNSIGNED NULL;

-- 1.1) Recreate FK logs(attempt_id) => attempts(id) with ON DELETE SET NULL safely
SET @fk_name := (
  SELECT `CONSTRAINT_NAME`
  FROM `information_schema`.`KEY_COLUMN_USAGE`
  WHERE `TABLE_SCHEMA` = DATABASE()
    AND `TABLE_NAME` = 'logs'
    AND `COLUMN_NAME` = 'attempt_id'
    AND `REFERENCED_TABLE_NAME` = 'attempts'
  LIMIT 1
);

SET @drop_fk_sql := IF(
  @fk_name IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `logs` DROP FOREIGN KEY `', @fk_name, '`')
);
PREPARE stmt_drop_fk FROM @drop_fk_sql;
EXECUTE stmt_drop_fk;
DEALLOCATE PREPARE stmt_drop_fk;

ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1`
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE SET NULL;

-- 2) recordings table
CREATE TABLE IF NOT EXISTS `recordings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Relative path to recording file',
  `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'File size in bytes',
  `chunk_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Chunk sequence number',
  `is_final` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Is this the final merged recording',
  `duration` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Recording duration in milliseconds',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_attempt` (`attempt_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_final` (`is_final`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Test session screen recordings for anti-cheat monitoring';

-- 3) logs.event_type enum update
ALTER TABLE `logs`
  MODIFY COLUMN `event_type` ENUM(
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
    'eye_fixations',
    'recording_uploaded',
    'recording_started',
    'recording_stopped',
    'recording_error'
  ) NOT NULL;

-- 4) If old enum mismatch produced empty event_type, normalize it
UPDATE `logs`
SET `event_type` = 'admin_action'
WHERE `event_type` = '';

