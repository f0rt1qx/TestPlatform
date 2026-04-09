-- ============================================================
-- Обновление таблицы users для расширенного профиля
-- ============================================================

ALTER TABLE `users` 
ADD COLUMN `avatar` VARCHAR(255) DEFAULT NULL COMMENT 'Путь к аватарке' AFTER `email`,
ADD COLUMN `bio` TEXT DEFAULT NULL COMMENT 'О себе' AFTER `avatar`,
ADD COLUMN `phone` VARCHAR(20) DEFAULT NULL COMMENT 'Телефон' AFTER `bio`,
ADD COLUMN `city` VARCHAR(100) DEFAULT NULL COMMENT 'Город' AFTER `phone`,
ADD COLUMN `website` VARCHAR(255) DEFAULT NULL COMMENT 'Веб-сайт' AFTER `city`,
ADD COLUMN `social_vk` VARCHAR(255) DEFAULT NULL COMMENT 'VK профиль' AFTER `website`,
ADD COLUMN `social_tg` VARCHAR(255) DEFAULT NULL COMMENT 'Telegram профиль' AFTER `social_vk`,
ADD COLUMN `birth_date` DATE DEFAULT NULL COMMENT 'Дата рождения' AFTER `social_tg`,
ADD COLUMN `last_visit_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Последний визит' AFTER `created_at`,
ADD COLUMN `settings_json` JSON DEFAULT NULL COMMENT 'Настройки пользователя' AFTER `last_visit_at`;

-- Индекс для поиска по городу
CREATE INDEX `idx_users_city` ON `users` (`city`);

-- Обновляем last_visit_at при каждом входе
-- (это будет делать PHP код)
