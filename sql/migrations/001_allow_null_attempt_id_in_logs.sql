-- Миграция: Разрешить NULL для attempt_id в таблице logs
-- Причина: Действия администратора не связаны с попытками прохождения тестов
-- Дата: 2026-04-03

ALTER TABLE `logs` 
  MODIFY COLUMN `attempt_id` INT UNSIGNED NULL,
  DROP FOREIGN KEY `logs_ibfk_1`,
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`attempt_id`) REFERENCES `attempts`(`id`) ON DELETE SET NULL;
