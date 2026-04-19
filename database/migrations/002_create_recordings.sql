-- ============================================================
-- Migration: Create recordings table
-- Purpose: Store screen recordings during test attempts
-- Date: 2026-04-14
-- ============================================================

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
