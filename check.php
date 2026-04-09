<?php
/**
 * check.php — диагностика установки
 * Откройте: http://localhost/test-platform/check.php
 */
header('Content-Type: text/html; charset=utf-8');

$checks = [];

// PHP версия
$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks[] = ['PHP версия', PHP_VERSION, $phpOk ? 'ok' : 'error', $phpOk ? '' : 'Требуется PHP 8.0+'];

// Расширения
foreach (['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'] as $ext) {
    $ok = extension_loaded($ext);
    $checks[] = ["Расширение $ext", $ok ? 'Загружено' : 'НЕ ЗАГРУЖЕНО', $ok ? 'ok' : 'error', ''];
}

// config.php
$configPath = __DIR__ . '/config/config.php';
$configOk = file_exists($configPath);
$checks[] = ['config.php', $configOk ? 'Найден' : 'НЕ НАЙДЕН', $configOk ? 'ok' : 'error', ''];

// Подключение к БД
$dbOk = false;
$dbMsg = '';
if ($configOk) {
    require_once $configPath;
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $dbOk = true;
        $dbMsg = 'Подключено к ' . DB_NAME . '@' . DB_HOST;
        
        // Проверяем таблицы
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $required = ['users','tests','questions','answers','results','logs','attempts'];
        $missing = array_diff($required, $tables);
        if ($missing) {
            $checks[] = ['Таблицы БД', 'Отсутствуют: ' . implode(', ', $missing), 'error', 
                         'Импортируйте sql/database.sql в phpMyAdmin'];
        } else {
            $checks[] = ['Таблицы БД', count($tables) . ' таблиц найдено', 'ok', ''];
        }
        
        // Проверяем пользователей
        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $checks[] = ['Пользователи в БД', $userCount . ' шт.', $userCount > 0 ? 'ok' : 'warning', 
                     $userCount == 0 ? 'Импортируйте тестовые данные из database.sql' : ''];
                     
    } catch (PDOException $e) {
        $dbMsg = $e->getMessage();
    }
}
$checks[] = ['Подключение к MySQL', $dbOk ? $dbMsg : '❌ ' . $dbMsg, $dbOk ? 'ok' : 'error', 
             $dbOk ? '' : 'Проверьте DB_HOST, DB_USER, DB_PASS в config.php и что MySQL запущен'];

// APP_URL
$detectedUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$detectedUrl .= rtrim($scriptDir, '/');
if ($configOk) {
    $urlMatch = defined('APP_URL') && APP_URL === $detectedUrl;
    $checks[] = ['APP_URL в config.php', defined('APP_URL') ? APP_URL : 'не задан', 
                 $urlMatch ? 'ok' : 'warning', 
                 $urlMatch ? '' : "Рекомендуемое значение: $detectedUrl"];
}

// Папки
foreach (['api', 'src', 'public/css', 'public/js', 'sql'] as $dir) {
    $ok = is_dir(__DIR__ . '/' . $dir);
    $checks[] = ["Папка /$dir", $ok ? 'Существует' : 'ОТСУТСТВУЕТ', $ok ? 'ok' : 'error', ''];
}

// JS файлы
foreach (['public/js/config.js', 'public/js/app.js', 'public/js/anticheat.js', 'public/css/main.css'] as $f) {
    $ok = file_exists(__DIR__ . '/' . $f);
    $checks[] = [$f, $ok ? '✓' : '✗ ОТСУТСТВУЕТ', $ok ? 'ok' : 'error', ''];
}

?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Диагностика — TestPlatform</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: system-ui, sans-serif; background: #f8fafc; color: #1e293b; padding: 30px 20px; }
.container { max-width: 750px; margin: 0 auto; }
h1 { font-size: 1.6rem; margin-bottom: 6px; color: #4f46e5; }
.subtitle { color: #64748b; margin-bottom: 28px; }
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
th, td { padding: 13px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; }
th { background: #f1f5f9; font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; color: #64748b; }
tr:last-child td { border-bottom: none; }
.ok      { color: #10b981; font-weight: 700; }
.error   { color: #ef4444; font-weight: 700; }
.warning { color: #f59e0b; font-weight: 700; }
.hint { font-size: .8rem; color: #ef4444; margin-top: 3px; }
.url-box { margin-top: 24px; background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
.url-box h3 { margin-bottom: 12px; }
code { background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-family: monospace; }
.big-ok { background: #dcfce7; color: #15803d; padding: 16px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; }
.big-err { background: #fee2e2; color: #dc2626; padding: 16px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 700; }
a { color: #4f46e5; }
</style>
</head>
<body>
<div class="container">
  <h1>🔍 Диагностика TestPlatform</h1>
  <p class="subtitle">Автоматическая проверка установки</p>
  
  <?php
  $hasError = array_filter($checks, function($c) { return $c[2] === 'error'; });
  if (empty($hasError)):
  ?>
  <div class="big-ok">✅ Все проверки пройдены! Проект готов к работе.</div>
  <?php else: ?>
  <div class="big-err">❌ Найдено ошибок: <?= count($hasError) ?>. Исправьте их для работы платформы.</div>
  <?php endif; ?>
  
  <table>
    <thead><tr><th>Проверка</th><th>Статус</th><th class="ok" style="color:#10b981;">✓/✗</th></tr></thead>
    <tbody>
    <?php foreach ($checks as $c): ?>
    <tr>
      <td><?= htmlspecialchars($c[0]) ?></td>
      <td>
        <?= htmlspecialchars($c[1]) ?>
        <?php if ($c[3]): ?><div class="hint">💡 <?= htmlspecialchars($c[3]) ?></div><?php endif; ?>
      </td>
      <td class="<?= $c[2] ?>"><?= $c[2] === 'ok' ? '✓' : ($c[2] === 'warning' ? '!' : '✗') ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="url-box">
    <h3>📍 Текущий URL</h3>
    <p>Вы открыли диагностику по адресу: <code><?= htmlspecialchars($detectedUrl . '/check.php') ?></code></p>
    <p style="margin-top:8px;">В <code>config/config.php</code> установите:<br>
    <code>define('APP_URL', '<?= htmlspecialchars($detectedUrl) ?>');</code></p>
  </div>

  <div class="url-box" style="margin-top:16px;">
    <h3>🔗 Быстрые ссылки</h3>
    <ul style="list-style:none;display:flex;flex-wrap:wrap;gap:12px;margin-top:10px;">
      <li><a href="index.php">🏠 Главная</a></li>
      <li><a href="login.php">🔑 Вход (admin / password)</a></li>
      <li><a href="register.php">📝 Регистрация</a></li>
      <li><a href="admin.php">⚙️ Админ-панель</a></li>
      <li><a href="api/auth.php?action=me">🧪 API тест</a></li>
    </ul>
  </div>

  <p style="margin-top:20px;color:#94a3b8;font-size:.85rem;">
    ⚠️ Удалите <code>check.php</code> перед выходом в production.
  </p>
</div>
</body>
</html>
