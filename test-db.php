<?php
/**
 * test-db.php — проверка подключения к БД
 */
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config/config.php';

echo "<h1>Проверка подключения к БД</h1>";

// Проверка подключения
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );
    
    echo "<p><strong>DSN:</strong> $dsn</p>";
    echo "<p><strong>User:</strong> " . DB_USER . "</p>";
    echo "<p><strong>Pass:</strong> " . (DB_PASS ?: '(пустой)') . "</p>";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<p style='color: green;'>✅ Подключение успешно!</p>";
    
    // Проверяем таблицу users
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Таблица users существует!</p>";
        
        // Проверяем структуру
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll();
        echo "<h3>Структура таблицы users:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
        }
        echo "</table>";
        
        // Считаем пользователей
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "<p>Всего пользователей: <strong>$count</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ Таблица users НЕ существует!</p>";
        echo "<p>Импортируйте <code>sql/database.sql</code> через phpMyAdmin</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка подключения: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<ul>";
    echo "<li>Проверьте что MySQL запущен в XAMPP</li>";
    echo "<li>Проверьте что база данных <code>" . DB_NAME . "</code> существует</li>";
    echo "<li>Проверьте учётные данные в config.php</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<h3>Полезные команды:</h3>";
echo "<pre>";
echo "1. Создать базу данных:\n";
echo "   CREATE DATABASE IF NOT EXISTS `test_platform` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n\n";
echo "2. Импортировать дамп:\n";
echo "   mysql -u root -p test_platform < sql/database.sql\n\n";
echo "3. Или откройте phpMyAdmin и импортируйте sql/database.sql\n";
echo "</pre>";

echo "<hr>";
echo "<a href='register.php'>← Вернуться к регистрации</a>";
