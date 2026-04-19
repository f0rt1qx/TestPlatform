<?php
/**
 * 🔍 Отладка ошибки Unauthorized в записях
 * Откройте эту страницу когда авторизованы как администратор
 */

require_once __DIR__ . '/src/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>🔍 Отладка записей</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f7fa; padding: 30px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        h1 { color: #2c3e50; margin-top: 0; }
        .status { padding: 16px; border-radius: 8px; margin: 15px 0; font-weight: 600; }
        .ok { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .info { background: #e7f3ff; color: #0c5460; border-left: 4px solid #17a2b8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 13px; }
        .test-btn { display: inline-block; padding: 12px 24px; background: #4361ee; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; text-decoration: none; margin-top: 20px; }
        .test-btn:hover { background: #3a56d4; }
        .debug-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
        .debug-box { background: #f8f9fa; padding: 15px; border-radius: 8px; }
        .debug-box h3 { margin-top: 0; font-size: 1rem; color: #495057; }
    </style>
</head>
<body>
<div class="container">

<h1>🔍 Отладка ошибки Unauthorized в записях</h1>

<?php

echo "<div class='status info'>📌 Текущее состояние сессии:</div>";

echo "<div class='debug-grid'>";

echo "<div class='debug-box'>";
echo "<h3>🔐 Сессия:</h3>";
echo "session_status(): " . session_status() . " (1=NONE, 2=ACTIVE)<br>";
echo "session_id(): " . session_id() . "<br>";
echo "SESSION USER_ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '❌ НЕТ') . "<br>";
echo "SESSION ROLE: " . (isset($_SESSION['role']) ? $_SESSION['role'] : '❌ НЕТ') . "<br>";
echo "</div>";

echo "<div class='debug-box'>";
echo "<h3>🍪 Куки:</h3>";
echo "PHPSESSID существует: " . (isset($_COOKIE['PHPSESSID']) ? '✅ ДА' : '❌ НЕТ') . "<br>";
if (isset($_COOKIE['PHPSESSID'])) {
    echo "PHPSESSID значение: " . substr($_COOKIE['PHPSESSID'], 0, 16) . "...<br>";
}
echo "</div>";

echo "</div>";

echo "<div class='status ".(isset($_SESSION['user_id']) ? 'ok' : 'error')."'>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Вы авторизованы, пользователь ID: {$_SESSION['user_id']}, роль: {$_SESSION['role']}";
} else {
    echo "❌ Вы НЕ авторизованы! Сессия не работает.";
}
echo "</div>";

// Проверка соединения с базой
try {
    $db = Database::getInstance();
    echo "<div class='status ok'>✅ Подключение к базе данных: ОК</div>";
} catch (Exception $e) {
    echo "<div class='status error'>❌ Ошибка базы данных: ".$e->getMessage()."</div>";
}

// Тест прямого вызова
echo "<h3>🧪 Тестовый запрос на видео:</h3>";
echo "<p>Найдены записи в базе данных:</p>";

$testUrl = '';
try {
    $recordingModel = new RecordingModel();
    $recordings = $recordingModel->findAll(10);
    
    if (count($recordings) > 0) {
        echo "<div style='margin: 10px 0; padding: 10px; background: #f0f8ff; border-radius: 6px;'>";
        echo "<strong>✅ Найдено " . count($recordings) . " записей в базе:</strong><br><br>";
        
        foreach ($recordings as $rec) {
            $url = APP_URL . "/api/view-recording.php?id=" . $rec['id'];
            echo "<a href='$url' target='_blank' style='display: block; padding: 6px; margin: 4px 0; background: white; border-radius: 4px; text-decoration: none; color: #4361ee;'>📹 Запись ID: {$rec['id']} | Пользователь: {$rec['user_id']} | {$rec['created_at']}</a>";
        }
        
        echo "</div>";
    } else {
        echo "<div class='status info'>ℹ️ В базе данных пока нет записей экрана</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='status error'>❌ Ошибка при получении записей: ".$e->getMessage()."</div>";
}

echo "<br><p><strong>Текущий APP_URL:</strong> <code>" . APP_URL . "</code></p>";

echo "<hr><h3>📋 Все данные сессии:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

?>

</div>
</body>
</html>