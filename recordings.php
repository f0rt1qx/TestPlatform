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
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal-content {
      background: var(--white);
      border-radius: var(--radius-xl);
      max-width: 1200px;
      width: 100%;
      max-height: 90vh;
      overflow: hidden;
      box-shadow: var(--shadow-xl);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 24px;
      border-bottom: 1px solid var(--border-light);
    }
    .modal-header h2 {
      font-size: 1.3rem;
      font-weight: 700;
    }
    .modal-close {
      width: 32px;
      height: 32px;
      border: none;
      background: var(--bg-light);
      border-radius: 8px;
      cursor: pointer;
      font-size: 1.2rem;
      color: var(--text-gray);
      transition: all 0.2s;
    }
    .modal-close:hover {
      background: var(--border-light);
      color: var(--text-dark);
    }
    .modal-body {
      padding: 24px;
      overflow-y: auto;
      max-height: calc(90vh - 80px);
    }
    .video-container {
      position: relative;
      width: 100%;
      background: #000;
      border-radius: var(--radius-lg);
      overflow: hidden;
    }
    .video-container video {
      width: 100%;
      max-height: 70vh;
      display: block;
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
      <div class="video-container">
        <video id="videoPlayer" controls>
          Ваш браузер не поддерживает воспроизведение видео.
        </video>
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

async function playRecording(id, options = {}) {
  const silent = !!options.silent;
  const modal = document.getElementById('videoModal');
  const video = document.getElementById('videoPlayer');
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
    video.src = currentVideoBlobUrl;
    video.load();

    // Show modal
    modal.classList.add('active');

    // Auto-play
    video.play().catch(e => console.log('Autoplay prevented:', e));
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
  const video = document.getElementById('videoPlayer');

  // Stop video
  video.pause();
  video.src = '';
  if (currentVideoBlobUrl) {
    URL.revokeObjectURL(currentVideoBlobUrl);
    currentVideoBlobUrl = null;
  }

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
