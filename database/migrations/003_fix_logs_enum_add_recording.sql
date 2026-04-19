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
    'recording_uploaded',
    'recording_started',
    'recording_stopped',
    'recording_error'
  ) NOT NULL;
