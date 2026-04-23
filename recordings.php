<?php
require_once __DIR__ . '/src/bootstrap.php';

// Only admins can access this page (supports session and JWT cookie auth)
AuthMiddleware::requirePage('admin');
$autoRecordingId = isset($_GET['recording_id']) ? (int)$_GET['recording_id'] : 0;

if (!function_exists('esc_html')) {
    function esc_html($value): string {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

// Get filter parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get recordings with user and test info
$db = Database::getInstance();
$stmt = $db->prepare(
    "SELECT r.*, u.username, u.email, a.test_id, a.status as attempt_status, t.title as test_title
     FROM recordings r
     LEFT JOIN attempts a ON r.attempt_id = a.id
     LEFT JOIN users u ON r.user_id = u.id
     LEFT JOIN tests t ON a.test_id = t.id
     WHERE r.is_final = 1
     ORDER BY r.created_at DESC
     LIMIT ? OFFSET ?"
);
$stmt->execute([$perPage, $offset]);
$recordings = $stmt->fetchAll();

// Get total count
$totalStmt = $db->query("SELECT COUNT(*) as cnt FROM recordings WHERE is_final = 1");
$total = (int)$totalStmt->fetch()['cnt'];
$totalPages = ceil($total / $perPage);

// Get stats
$recordingModel = new RecordingModel();
$stats = $recordingModel->getStats();

// Disable auto-open when URL points to a deleted/non-final recording.
if ($autoRecordingId > 0) {
    $checkStmt = $db->prepare("SELECT id FROM recordings WHERE id = ? AND is_final = 1 LIMIT 1");
    $checkStmt->execute([$autoRecordingId]);
    if (!$checkStmt->fetchColumn()) {
        $autoRecordingId = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title>Записи экрана — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="public/css/modern.css?v=4">
  <style>
    .recordings-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 32px;
    }
    .recordings-header h1 {
      font-size: 2rem;
      font-weight: 800;
      color: var(--text-dark);
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 32px;
    }
    .stat-card {
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 20px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-light);
    }
    .stat-card .value {
      font-size: 2rem;
      font-weight: 800;
      color: var(--gradient-start);
      margin-bottom: 4px;
    }
    .stat-card .label {
      font-size: 0.85rem;
      color: var(--text-gray);
      font-weight: 600;
    }
    .recordings-table {
      width: 100%;
      background: var(--white);
      border-radius: var(--radius-lg);
      overflow: hidden;
      box-shadow: var(--shadow-md);
    }
    .recordings-table table {
      width: 100%;
      border-collapse: collapse;
    }
    .recordings-table th {
      background: var(--bg-light);
      padding: 14px 16px;
      text-align: left;
      font-weight: 700;
      font-size: 0.85rem;
      color: var(--text-gray);
      border-bottom: 2px solid var(--border-light);
    }
    .recordings-table td {
      padding: 14px 16px;
      border-bottom: 1px solid var(--border-light);
      font-size: 0.9rem;
    }
    .recordings-table tr:hover {
      background: var(--bg-light);
    }
    .recordings-table tr:last-child td {
      border-bottom: none;
    }
    .btn-play {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      background: var(--gradient-primary);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
    }
    .btn-play:hover {
      opacity: 0.9;
      transform: translateY(-1px);
    }
    .btn-delete {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      background: #fee2e2;
      color: #dc2626;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-delete:hover {
      background: #fecaca;
    }
    .file-size {
      color: var(--text-gray);
      font-size: 0.85rem;
    }
    .duration {
      color: var(--text-gray);
      font-size: 0.85rem;
      font-variant-numeric: tabular-nums;
    }
    .pagination {
      display: flex;
      justify-content: center;
      gap: 8px;
      margin-top: 24px;
    }
    .pagination button {
      padding: 10px 16px;
      background: var(--white);
      border: 1px solid var(--border-light);
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .pagination button:hover:not(:disabled) {
      background: var(--gradient-start);
      color: #fff;
      border-color: var(--gradient-start);
    }
    .pagination button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    .pagination button.active {
      background: var(--gradient-start);
      color: #fff;
      border-color: var(--gradient-start);
    }
    .empty-state {
      text-align: center;
      padding: 80px 20px;
      color: var(--text-gray);
    }
    .empty-state i {
      font-size: 4rem;
      margin-bottom: 16px;
      opacity: 0.3;
    }
    .empty-state p {
      font-size: 1.1rem;
      font-weight: 600;
    }

    /* Modal */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background:
        radial-gradient(circle at top, rgba(105, 240, 174, 0.14), transparent 28%),
        rgba(15, 23, 42, 0.72);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal-content {
      background:
        radial-gradient(circle at top right, rgba(105, 240, 174, 0.14), transparent 28%),
        linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 252, 250, 0.98));
      border-radius: 30px;
      max-width: 1200px;
      width: 100%;
      max-height: 90vh;
      overflow: hidden;
      border: 1px solid rgba(0, 200, 83, 0.12);
      box-shadow: 0 30px 80px rgba(0, 200, 83, 0.18);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 22px 24px 18px;
      border-bottom: 1px solid rgba(0, 200, 83, 0.1);
    }
    .modal-header h2 {
      font-size: 1.32rem;
      font-weight: 800;
      letter-spacing: -0.02em;
    }
    .modal-close {
      width: 42px;
      height: 42px;
      border: none;
      background: rgba(15, 23, 42, 0.05);
      border-radius: 16px;
      cursor: pointer;
      font-size: 1.28rem;
      color: var(--text-gray);
      transition: all 0.25s ease;
    }
    .modal-close:hover {
      background: rgba(239, 68, 68, 0.12);
      color: #dc2626;
      transform: rotate(90deg);
    }
    .modal-body {
      padding: 18px 24px 24px;
      overflow-y: auto;
      max-height: calc(90vh - 80px);
    }
    .video-container {
      position: relative;
      width: 100%;
      background: radial-gradient(circle at top, rgba(0, 200, 83, 0.12), rgba(15, 23, 42, 0.94) 48%);
      border-radius: 28px;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,0.08);
      box-shadow: 0 28px 70px rgba(0, 0, 0, 0.34), inset 0 1px 0 rgba(255,255,255,0.05);
    }
    .video-container video {
      width: 100%;
      max-height: 70vh;
      display: block;
      background: #000;
      outline: none;
    }
    .custom-player {
      position: relative;
    }
    .player-topbar {
      position: absolute;
      inset: 16px 16px auto 16px;
      z-index: 3;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      pointer-events: none;
    }
    .player-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-radius: 999px;
      background: rgba(15, 23, 42, 0.68);
      color: #fff;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: 0.01em;
    }
    .player-badge i {
      color: #34d399;
    }
    .player-status {
      color: #d1fae5;
    }
    .player-center-play {
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 86px;
      height: 86px;
      border: none;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.92), rgba(105, 240, 174, 0.92));
      color: #fff;
      font-size: 1.65rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      z-index: 4;
      box-shadow: 0 20px 50px rgba(0, 200, 83, 0.38);
      transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .player-center-play:hover {
      transform: translate(-50%, -50%) scale(1.04);
    }
    .player-center-play.hidden {
      opacity: 0;
      pointer-events: none;
    }
    .player-gradient {
      position: absolute;
      inset: auto 0 0 0;
      height: 180px;
      background: linear-gradient(180deg, rgba(2, 6, 23, 0) 0%, rgba(2, 6, 23, 0.82) 72%, rgba(2, 6, 23, 0.96) 100%);
      pointer-events: none;
      z-index: 1;
      transition: opacity 0.22s ease;
    }
    .player-controls {
      position: absolute;
      left: 16px;
      right: 16px;
      bottom: 16px;
      z-index: 5;
      display: flex;
      flex-direction: column;
      gap: 12px;
      padding: 14px 14px 12px;
      border-radius: 24px;
      background: rgba(15, 23, 42, 0.68);
      border: 1px solid rgba(255,255,255,0.08);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
      box-shadow: 0 18px 44px rgba(0, 0, 0, 0.28);
      transition: opacity 0.22s ease, transform 0.22s ease;
    }
    .player-progress-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
      color: rgba(255,255,255,0.88);
      font-weight: 700;
      font-variant-numeric: tabular-nums;
      font-size: 0.88rem;
    }
    .player-progress {
      -webkit-appearance: none;
      appearance: none;
      flex: 1;
      height: 6px;
      border-radius: 999px;
      background: rgba(255,255,255,0.14);
      outline: none;
      cursor: pointer;
      overflow: hidden;
    }
    .player-progress::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 14px;
      height: 14px;
      border-radius: 50%;
      background: #fff;
      box-shadow: -400px 0 0 400px #34d399;
      border: 2px solid rgba(255,255,255,0.5);
    }
    .player-progress::-moz-range-track {
      height: 6px;
      border-radius: 999px;
      background: rgba(255,255,255,0.14);
    }
    .player-progress::-moz-range-progress {
      height: 6px;
      border-radius: 999px;
      background: #34d399;
    }
    .player-progress::-moz-range-thumb {
      width: 14px;
      height: 14px;
      border: 2px solid rgba(255,255,255,0.5);
      border-radius: 50%;
      background: #fff;
    }
    .player-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .player-actions,
    .player-side {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }
    .player-btn {
      width: 42px;
      height: 42px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.08);
      background: rgba(255,255,255,0.08);
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
      font-size: 0.95rem;
    }
    .player-btn:hover {
      background: rgba(52, 211, 153, 0.16);
      border-color: rgba(52, 211, 153, 0.28);
      transform: translateY(-1px);
    }
    .player-volume {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      padding: 0 6px 0 2px;
    }
    .player-volume input[type="range"] {
      -webkit-appearance: none;
      appearance: none;
      width: 88px;
      height: 4px;
      border-radius: 999px;
      background: rgba(255,255,255,0.2);
      cursor: pointer;
    }
    .player-volume input[type="range"]::-webkit-slider-thumb {
      -webkit-appearance: none;
      appearance: none;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #fff;
      border: none;
    }
    .player-volume input[type="range"]::-moz-range-thumb {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #fff;
      border: none;
    }
    .player-speed {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 52px;
      padding: 0 12px;
      height: 42px;
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,0.08);
      background: rgba(255,255,255,0.08);
      color: #fff;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }
    .player-speed:hover {
      background: rgba(52, 211, 153, 0.16);
      border-color: rgba(52, 211, 153, 0.28);
      transform: translateY(-1px);
    }
    .player-time {
      color: rgba(255,255,255,0.92);
      font-weight: 700;
      font-variant-numeric: tabular-nums;
      letter-spacing: 0.01em;
    }
    .custom-player.is-idle .player-controls,
    .custom-player.is-idle .player-topbar,
    .custom-player.is-idle .player-gradient {
      opacity: 0;
      pointer-events: none;
      transform: translateY(6px);
    }
    .custom-player.is-paused .player-controls,
    .custom-player.is-paused .player-topbar,
    .custom-player.is-paused .player-gradient {
      opacity: 1;
      pointer-events: auto;
      transform: none;
    }
    .custom-player.is-paused .player-center-play {
      opacity: 1;
      pointer-events: auto;
    }
    .player-empty {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: rgba(255,255,255,0.75);
      font-weight: 700;
      letter-spacing: 0.01em;
      pointer-events: none;
      z-index: 2;
    }
    .player-empty.hidden {
      display: none;
    }
    @media (max-width: 768px) {
      .modal-body {
        padding: 16px;
      }
      .player-controls {
        left: 10px;
        right: 10px;
        bottom: 10px;
        border-radius: 18px;
        padding: 12px;
      }
      .player-topbar {
        inset: 10px 10px auto 10px;
      }
      .player-bottom {
        gap: 12px;
      }
      .player-actions,
      .player-side {
        width: 100%;
        justify-content: space-between;
      }
      .player-volume input[type="range"] {
        width: 72px;
      }
    }
    .video-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 12px;
      margin-top: 16px;
    }
    .video-info-item {
      padding: 12px;
      background: var(--bg-light);
      border-radius: 8px;
    }
    .video-info-item .label {
      font-size: 0.75rem;
      color: var(--text-gray);
      font-weight: 600;
      margin-bottom: 4px;
    }
    .video-info-item .value {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--text-dark);
    }
  </style>
</head>
<body>

<div style="min-height: 100vh; background: var(--bg-gray); padding: 40px 20px;">
  <div style="max-width: 1400px; margin: 0 auto;">

    <!-- Header -->
    <div class="recordings-header">
      <div>
        <h1><i class="fas fa-video"></i> Записи экрана</h1>
        <p style="color: var(--text-gray); margin-top: 8px;">Просмотр записей тестов студентов</p>
      </div>
      <a href="admin.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: var(--bg-light); color: var(--text-dark); border-radius: 10px; text-decoration: none; font-weight: 600;">
        <i class="fas fa-arrow-left"></i> Назад в админ-панель
      </a>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="value"><?= $stats['final_recordings'] ?></div>
        <div class="label">Всего записей</div>
      </div>
      <div class="stat-card">
        <div class="value"><?= $total > 0 ? round($stats['total_size'] / 1024 / 1024) : 0 ?> MB</div>
        <div class="label">Общий размер</div>
      </div>
      <div class="stat-card">
        <div class="value"><?= $total > 0 && $stats['avg_duration'] > 0 ? round($stats['avg_duration'] / 1000) . 'с' : '—' ?></div>
        <div class="label">Средняя длительность</div>
      </div>
      <div class="stat-card">
        <div class="value"><?= $total ?></div>
        <div class="label">Показано</div>
      </div>
    </div>

    <!-- Recordings Table -->
    <?php if (empty($recordings)): ?>
      <div class="empty-state">
        <i class="fas fa-video-slash"></i>
        <p>Записей пока нет</p>
        <p style="font-size: 0.9rem; margin-top: 8px;">Записи появятся здесь, когда студенты начнут проходить тесты</p>
      </div>
    <?php else: ?>
      <div class="recordings-table">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Студент</th>
              <th>Тест</th>
              <th>Размер</th>
              <th>Длительность</th>
              <th>Дата</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recordings as $rec): ?>
              <tr>
                <td>#<?= $rec['id'] ?></td>
                <td>
                  <div style="font-weight: 600;"><?= esc_html($rec['username'] ?? 'Неизвестно') ?></div>
                  <div style="font-size: 0.8rem; color: var(--text-gray);"><?= esc_html($rec['email'] ?? '') ?></div>
                </td>
                <td>
                  <div style="font-weight: 600;"><?= esc_html($rec['test_title'] ?? 'Неизвестно') ?></div>
                  <div style="font-size: 0.8rem; color: var(--text-gray);">Попытка #<?= $rec['attempt_id'] ?></div>
                </td>
                <td class="file-size"><?= number_format($rec['file_size'] / 1024 / 1024, 2) ?> MB</td>
                <td class="duration"><?= $rec['duration'] > 0 ? round($rec['duration'] / 1000) . 'с' : '—' ?></td>
                <td><?= date('d.m.Y H:i', strtotime($rec['created_at'])) ?></td>
                <td>
                  <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn-play" onclick="playRecording(<?= $rec['id'] ?>)">
                      <i class="fas fa-play"></i> Смотреть
                    </button>
                    <button type="button" class="btn-delete" onclick="deleteRecording(<?= $rec['id'] ?>)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <button <?= $page <= 1 ? 'disabled' : '' ?> onclick="location.href='?page=<?= $page - 1 ?>'">
            <i class="fas fa-chevron-left"></i> Назад
          </button>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
              <button class="active"><?= $i ?></button>
            <?php elseif ($i <= 3 || $i > $totalPages - 2 || abs($i - $page) <= 1): ?>
              <button onclick="location.href='?page=<?= $i ?>'"><?= $i ?></button>
            <?php elseif ($i == 4 || $i == $totalPages - 2): ?>
              <button disabled>...</button>
            <?php endif; ?>
          <?php endfor; ?>
          <button <?= $page >= $totalPages ? 'disabled' : '' ?> onclick="location.href='?page=<?= $page + 1 ?>'">
            Далее <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  </div>
</div>

<!-- Video Modal -->
<div class="modal-overlay<?= $autoRecordingId > 0 ? ' active' : '' ?>" id="videoModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2><i class="fas fa-play-circle"></i> Просмотр записи</h2>
      <button class="modal-close" onclick="closeModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <div class="video-container custom-player is-paused" id="customPlayer">
        <div class="player-topbar">
          <div class="player-badge"><i class="fas fa-shield-heart"></i> <span class="player-status" id="playerStatus">Запись готова к просмотру</span></div>
        </div>
        <div class="player-empty" id="playerEmptyState">Загрузите запись для просмотра</div>
        <video id="videoPlayer" playsinline preload="metadata">
          Ваш браузер не поддерживает воспроизведение видео.
        </video>
        <div class="player-gradient"></div>
        <button type="button" class="player-center-play" id="playerCenterPlay" aria-label="Воспроизвести">
          <i class="fas fa-play"></i>
        </button>
        <div class="player-controls" id="playerControls">
          <div class="player-progress-wrap">
            <span id="playerCurrentTime">00:00</span>
            <input type="range" id="playerProgress" class="player-progress" min="0" max="100" value="0" step="0.1">
            <span id="playerDuration">00:00</span>
          </div>
          <div class="player-bottom">
            <div class="player-actions">
              <button type="button" class="player-btn" id="playerPlayPause" aria-label="Пауза / воспроизведение">
                <i class="fas fa-play"></i>
              </button>
              <div class="player-volume">
                <button type="button" class="player-btn" id="playerMute" aria-label="Звук">
                  <i class="fas fa-volume-up"></i>
                </button>
                <input type="range" id="playerVolume" min="0" max="1" step="0.01" value="1" aria-label="Громкость">
              </div>
              <button type="button" class="player-speed" id="playerSpeed" aria-label="Скорость">1x</button>
            </div>
            <div class="player-side">
              <div class="player-time" id="playerTimelineText">00:00 / 00:00</div>
              <button type="button" class="player-btn" id="playerFullscreen" aria-label="Во весь экран">
                <i class="fas fa-expand"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="video-info" id="videoInfo">
        <!-- Will be populated by JS -->
      </div>
      <?php if ($autoRecordingId > 0): ?>
        <div style="margin-top:12px;">
          <a href="api/view-recording.php?id=<?= $autoRecordingId ?>" target="_blank" style="color:var(--gradient-start);font-weight:600;">
            Открыть видео в новой вкладке
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
let currentVideoBlobUrl = null;
let isLoadingVideo = false;
const playerState = {
  hideTimer: null,
  speeds: [1, 1.25, 1.5, 1.75, 2],
  speedIndex: 0
};

const customPlayer = document.getElementById('customPlayer');
const videoElement = document.getElementById('videoPlayer');
const playerControls = document.getElementById('playerControls');
const playerCenterPlay = document.getElementById('playerCenterPlay');
const playerPlayPause = document.getElementById('playerPlayPause');
const playerProgress = document.getElementById('playerProgress');
const playerCurrentTime = document.getElementById('playerCurrentTime');
const playerDuration = document.getElementById('playerDuration');
const playerTimelineText = document.getElementById('playerTimelineText');
const playerMute = document.getElementById('playerMute');
const playerVolume = document.getElementById('playerVolume');
const playerSpeed = document.getElementById('playerSpeed');
const playerFullscreen = document.getElementById('playerFullscreen');
const playerStatus = document.getElementById('playerStatus');
const playerEmptyState = document.getElementById('playerEmptyState');

function formatPlayerTime(seconds) {
  if (!Number.isFinite(seconds) || seconds < 0) {
    return '00:00';
  }

  const totalSeconds = Math.floor(seconds);
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const secs = totalSeconds % 60;

  if (hours > 0) {
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
  }

  return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
}

function showPlayerUi() {
  customPlayer.classList.remove('is-idle');
  clearTimeout(playerState.hideTimer);

  if (!videoElement.paused) {
    playerState.hideTimer = setTimeout(() => {
      customPlayer.classList.add('is-idle');
    }, 2200);
  }
}

function updatePlayerIcons() {
  const isPaused = videoElement.paused;
  customPlayer.classList.toggle('is-paused', isPaused);
  playerCenterPlay.classList.toggle('hidden', !isPaused);
  playerPlayPause.innerHTML = isPaused
    ? '<i class="fas fa-play"></i>'
    : '<i class="fas fa-pause"></i>';
  playerCenterPlay.innerHTML = isPaused
    ? '<i class="fas fa-play"></i>'
    : '<i class="fas fa-pause"></i>';

  let volumeIcon = 'fa-volume-up';
  if (videoElement.muted || videoElement.volume === 0) {
    volumeIcon = 'fa-volume-mute';
  } else if (videoElement.volume < 0.5) {
    volumeIcon = 'fa-volume-down';
  }
  playerMute.innerHTML = `<i class="fas ${volumeIcon}"></i>`;
}

function updatePlayerProgress() {
  const duration = Number.isFinite(videoElement.duration) ? videoElement.duration : 0;
  const current = Number.isFinite(videoElement.currentTime) ? videoElement.currentTime : 0;
  const progressValue = duration > 0 ? (current / duration) * 100 : 0;

  playerProgress.value = progressValue;
  playerCurrentTime.textContent = formatPlayerTime(current);
  playerDuration.textContent = formatPlayerTime(duration);
  playerTimelineText.textContent = `${formatPlayerTime(current)} / ${formatPlayerTime(duration)}`;
}

function resetPlayerUi() {
  customPlayer.classList.add('is-paused');
  customPlayer.classList.remove('is-idle');
  playerProgress.value = 0;
  playerCurrentTime.textContent = '00:00';
  playerDuration.textContent = '00:00';
  playerTimelineText.textContent = '00:00 / 00:00';
  playerStatus.textContent = 'Запись готова к просмотру';
  playerVolume.value = 1;
  videoElement.volume = 1;
  videoElement.muted = false;
  videoElement.playbackRate = 1;
  playerState.speedIndex = 0;
  playerSpeed.textContent = '1x';
  playerEmptyState.classList.remove('hidden');
  updatePlayerIcons();
}

function togglePlayState() {
  if (!videoElement.src) {
    return;
  }

  if (videoElement.paused) {
    videoElement.play().catch(e => console.log('Play prevented:', e));
  } else {
    videoElement.pause();
  }
}

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    customPlayer.requestFullscreen?.();
  } else {
    document.exitFullscreen?.();
  }
}

async function playRecording(id, options = {}) {
  const silent = !!options.silent;
  const modal = document.getElementById('videoModal');
  const info = document.getElementById('videoInfo');
  isLoadingVideo = true;

  try {
    const response = await fetch('api/view-recording.php?id=' + id, {
      method: 'GET',
      credentials: 'include',
      cache: 'no-store'
    });

    const contentType = (response.headers.get('content-type') || '').toLowerCase();
    const isLikelyVideo = contentType.startsWith('video/') || contentType.includes('application/octet-stream');
    if (!response.ok || contentType.includes('application/json') || !isLikelyVideo) {
      let message = 'Не удалось загрузить видео';
      try {
        const errorData = await response.json();
        if (errorData && errorData.message) {
          message = errorData.message;
        }
      } catch (e) {
        if (!response.ok) {
          message = 'HTTP ' + response.status;
        } else {
          message = 'Получен неожиданный ответ сервера';
        }
      }
      if (!silent) {
        alert('Ошибка просмотра: ' + message);
      }
      return;
    }

    const videoBlob = await response.blob();
    if (!videoBlob || videoBlob.size === 0) {
      if (!silent) {
        alert('Ошибка просмотра: пустой видео-файл');
      }
      return;
    }

    if (currentVideoBlobUrl) {
      URL.revokeObjectURL(currentVideoBlobUrl);
      currentVideoBlobUrl = null;
    }

    currentVideoBlobUrl = URL.createObjectURL(videoBlob);
    videoElement.src = currentVideoBlobUrl;
    videoElement.load();
    playerEmptyState.classList.add('hidden');
    playerStatus.textContent = 'Видео загружено';
    showPlayerUi();

    // Show modal
    modal.classList.add('active');

    // Auto-play
    videoElement.play().catch(e => console.log('Autoplay prevented:', e));
    isLoadingVideo = false;
  } catch (error) {
    isLoadingVideo = false;
    if (!silent) {
      alert('Ошибка просмотра: ' + error.message);
    }
  }
}

function closeModal() {
  const modal = document.getElementById('videoModal');

  // Stop video
  videoElement.pause();
  videoElement.removeAttribute('src');
  videoElement.load();
  if (currentVideoBlobUrl) {
    URL.revokeObjectURL(currentVideoBlobUrl);
    currentVideoBlobUrl = null;
  }
  resetPlayerUi();

  // Hide modal
  modal.classList.remove('active');
}

document.getElementById('videoPlayer').addEventListener('error', function() {
  if (isLoadingVideo) {
    return;
  }

  if (!this.currentSrc || this.networkState === this.NETWORK_NO_SOURCE) {
    return;
  }

  alert('Ошибка воспроизведения: браузер не смог декодировать видеофайл.');
});

videoElement.addEventListener('loadedmetadata', () => {
  playerStatus.textContent = 'Запись готова к воспроизведению';
  updatePlayerProgress();
  updatePlayerIcons();
});

videoElement.addEventListener('timeupdate', updatePlayerProgress);
videoElement.addEventListener('play', () => {
  playerStatus.textContent = 'Воспроизведение';
  updatePlayerIcons();
  showPlayerUi();
});
videoElement.addEventListener('pause', () => {
  playerStatus.textContent = 'Пауза';
  clearTimeout(playerState.hideTimer);
  customPlayer.classList.remove('is-idle');
  updatePlayerIcons();
});
videoElement.addEventListener('ended', () => {
  playerStatus.textContent = 'Просмотр завершён';
  customPlayer.classList.remove('is-idle');
  customPlayer.classList.add('is-paused');
  updatePlayerIcons();
});

playerCenterPlay.addEventListener('click', togglePlayState);
playerPlayPause.addEventListener('click', togglePlayState);

playerProgress.addEventListener('input', () => {
  const duration = Number.isFinite(videoElement.duration) ? videoElement.duration : 0;
  if (duration > 0) {
    videoElement.currentTime = (Number(playerProgress.value) / 100) * duration;
  }
  updatePlayerProgress();
});

playerMute.addEventListener('click', () => {
  videoElement.muted = !videoElement.muted;
  updatePlayerIcons();
});

playerVolume.addEventListener('input', () => {
  videoElement.volume = Number(playerVolume.value);
  videoElement.muted = videoElement.volume === 0;
  updatePlayerIcons();
});

playerSpeed.addEventListener('click', () => {
  playerState.speedIndex = (playerState.speedIndex + 1) % playerState.speeds.length;
  const nextSpeed = playerState.speeds[playerState.speedIndex];
  videoElement.playbackRate = nextSpeed;
  playerSpeed.textContent = `${nextSpeed}x`;
});

playerFullscreen.addEventListener('click', toggleFullscreen);

customPlayer.addEventListener('mousemove', showPlayerUi);
customPlayer.addEventListener('mouseenter', showPlayerUi);
customPlayer.addEventListener('mouseleave', () => {
  if (!videoElement.paused) {
    playerState.hideTimer = setTimeout(() => {
      customPlayer.classList.add('is-idle');
    }, 700);
  }
});

videoElement.addEventListener('click', togglePlayState);
document.addEventListener('fullscreenchange', () => {
  playerFullscreen.innerHTML = document.fullscreenElement
    ? '<i class="fas fa-compress"></i>'
    : '<i class="fas fa-expand"></i>';
});

resetPlayerUi();

async function deleteRecording(id) {
  if (!confirm('Вы уверены, что хотите удалить эту запись?')) {
    return;
  }

  try {
    const response = await fetch('api/recording.php', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ recording_id: id })
    });

    const result = await response.json();

    if (result.success) {
      alert('Запись удалена успешно');
      window.location.href = 'recordings.php';
    } else {
      alert('Ошибка: ' + result.message);
    }
  } catch (error) {
    alert('Ошибка при удалении: ' + error.message);
  }
}

// Close modal on overlay click
document.getElementById('videoModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeModal();
  }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeModal();
  }
});

function escHtml(str) {
  const div = document.createElement('div');
  div.textContent = str || '';
  return div.innerHTML;
}

<?php if ($autoRecordingId > 0): ?>
setTimeout(() => {
  playRecording(<?= $autoRecordingId ?>, { silent: true });
}, 50);
<?php endif; ?>
</script>

</body>
</html>
