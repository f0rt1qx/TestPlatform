-- ============================================================
-- Test Platform Database
-- Import via phpMyAdmin or: mysql -u root -p < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS `test_platform` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `test_platform`;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student','admin') NOT NULL DEFAULT 'student',
  `first_name` VARCHAR(80) DEFAULT NULL,
  `last_name` VARCHAR(80) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_blocked` TINYINT(1) NOT NULL DEFAULT 0,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- EMAIL VERIFICATIONS
-- ============================================================
CREATE TABLE `email_verifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PASSWORD RESETS
-- ============================================================
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `expires_at` TIMESTAMP NOT NULL,
  `used` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TESTS
-- ============================================================
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
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- QUESTIONS
-- ============================================================
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

-- ============================================================
-- ANSWERS
-- ============================================================
CREATE TABLE `answers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT UNSIGNED NOT NULL,
  `answer_text` TEXT NOT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `order_num` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE,
  INDEX `idx_question` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ATTEMPTS
-- ============================================================
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- RESULTS
-- ============================================================
CREATE TABLE `results` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT UNSIGNED NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `test_id` INT UNSIGNED NOT NULL,
  `score` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `max_score` SMALLINT UNSIGNED NOT NULL,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `passed` TINYINT(1) NOT NULL DEFAULT 0,
  `answers_json` LONGTEXT NOT NULL COMMENT 'JSON snapshot of answers',
  `time_spent` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Seconds',
  `cheat_score` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0-100 suspicion level',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`test_id`) REFERENCES `tests`(`id`) ON DELETE CASCADE,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_test` (`test_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ANTI-CHEAT LOGS
-- ============================================================
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
    'admin_action'
  ) NOT NULL,
  `event_data` JSON DEFAULT NULL,
  `severity` ENUM('low','medium','high') NOT NULL DEFAULT 'low',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_attempt` (`attempt_id`),
  INDEX `idx_user_events` (`user_id`, `event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin user (password: Admin123!)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `email_verified`) VALUES
('admin', 'admin@sapienta.local', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'admin', 'Администратор', 'Системы', 1);

-- Student user (password: Student123!)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`, `first_name`, `last_name`, `email_verified`) VALUES
('student1', 'student@sapienta.local', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', 'student', 'Иван', 'Иванов', 1);

-- Sample test
INSERT INTO `tests` (`title`, `description`, `time_limit`, `max_attempts`, `pass_score`, `created_by`) VALUES
('Основы информатики', 'Тест проверяет базовые знания по информатике', 20, 2, 60, 1),
('HTML и CSS', 'Вводный тест по веб-разработке', 15, 3, 70, 1);

-- Questions for test 1
INSERT INTO `questions` (`test_id`, `question_text`, `question_type`, `points`, `order_num`) VALUES
(1, 'Что такое алгоритм?', 'single', 1, 1),
(1, 'Какой из языков программирования является интерпретируемым?', 'single', 1, 2),
(1, 'Выберите все типы данных в программировании:', 'multiple', 2, 3),
(1, 'Сколько бит в одном байте?', 'single', 1, 4),
(1, 'Что такое переменная?', 'single', 1, 5);

-- Answers for Q1
INSERT INTO `answers` (`question_id`, `answer_text`, `is_correct`, `order_num`) VALUES
(1, 'Набор инструкций для решения задачи', 1, 1),
(1, 'Программа для работы с данными', 0, 2),
(1, 'Язык программирования', 0, 3),
(1, 'База данных', 0, 4);

-- Answers for Q2
INSERT INTO `answers` (`question_id`, `answer_text`, `is_correct`, `order_num`) VALUES
(2, 'Python', 1, 1),
(2, 'C++', 0, 2),
(2, 'Go', 0, 3),
(2, 'Rust', 0, 4);

-- Answers for Q3
INSERT INTO `answers` (`question_id`, `answer_text`, `is_correct`, `order_num`) VALUES
(3, 'Целое число (Integer)', 1, 1),
(3, 'Строка (String)', 1, 2),
(3, 'Алгоритм', 0, 3),
(3, 'Булево (Boolean)', 1, 4);

-- Answers for Q4
INSERT INTO `answers` (`question_id`, `answer_text`, `is_correct`, `order_num`) VALUES
(4, '8', 1, 1),
(4, '4', 0, 2),
(4, '16', 0, 3),
(4, '2', 0, 4);

-- Answers for Q5
INSERT INTO `answers` (`question_id`, `answer_text`, `is_correct`, `order_num`) VALUES
(5, 'Именованная ячейка памяти для хранения данных', 1, 1),
(5, 'Функция программы', 0, 2),
(5, 'Тип данных', 0, 3),
(5, 'Цикл', 0, 4);

-- Questions for test 2
INSERT INTO `questions` (`test_id`, `question_text`, `question_type`, `points`, `order_num`) VALUES
(2, 'Для чего используется тег <h1>?', 'single', 1, 1),
(2, 'Какой атрибут задаёт стиль элемента inline?', 'single', 1, 2),
(2, 'CSS — это:', 'single', 1, 3);

INSERT INTO `answers` (`question_id`, `answer_text`, `is_correct`, `order_num`) VALUES
(6, 'Заголовок первого уровня', 1, 1),
(6, 'Горизонтальная линия', 0, 2),
(6, 'Абзац текста', 0, 3),
(7, 'style', 1, 1),
(7, 'class', 0, 2),
(7, 'id', 0, 3),
(8, 'Язык стилей', 1, 1),
(8, 'Язык программирования', 0, 2),
(8, 'База данных', 0, 3);
