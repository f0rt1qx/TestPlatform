-- ============================================================
-- Migration: Add eye-tracking support
-- Date: 2026-04-04
-- Description: Add eye_fixations event type to logs table
-- ============================================================

USE `test_platform`;

-- Add eye_fixations to the event_type ENUM
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
    'eye_fixations'
  ) NOT NULL;

-- Add index for filtering eye-tracking events
ALTER TABLE `logs`
  ADD INDEX `idx_event_type` (`event_type`);

-- Add comment to document the eye_fixations data structure
-- eye_fixations event_data JSON structure:
-- {
--   "fixations": [
--     {
--       "startX": 450.2,
--       "startY": 320.5,
--       "startTime": 1712239200000,
--       "endX": 455.1,
--       "endY": 318.9,
--       "endTime": 1712239200250,
--       "duration": 250,
--       "points": 5
--     }
--   ],
--   "count": 10
-- }
