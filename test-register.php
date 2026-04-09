<?php

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/bootstrap.php';

echo "<h1>Тест регистрации</h1>";


$testUser = [
    'username' => 'testuser_' . time(),
    'email' => 'test_' . time() . '@example.com',
    'password' => 'testpass123',
    'first_name' => 'Тест',
    'last_name' => 'Тестов',
];

echo "<h2>Тестовые данные:</h2>";
echo "<pre>" . print_r($testUser, true) . "</pre>";

try {
    $userModel = new UserModel();
    
    echo "<h2>Проверка подключения к БД...</h2>";
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Подключение успешно!</p>";
    
    echo "<h2>Попытка создания пользователя...</h2>";
    $userId = $userModel->create($testUser);
    
    if ($userId > 0) {
        echo "<p style='color: green;'>✅ Пользователь успешно создан с ID: $userId</p>";
        
        
        $user = $userModel->findById($userId);
        echo "<h3>Созданный пользователь:</h3>";
        echo "<pre>" . print_r($user, true) . "</pre>";
        
        
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
        echo "<p style='color: orange;'>⚠️ Тестовый пользователь удалён</p>";
    } else {
        echo "<p style='color: red;'>❌ Не удалось создать пользователя</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>Возможные проблемы и решения:</h3>";
echo "<ul>";
echo "<li><strong>Ошибка подключения к БД:</strong> Убедитесь что MySQL запущен в XAMPP</li>";
echo "<li><strong>Table doesn't exist:</strong> Импортируйте sql/database.sql</li>";
echo "<li><strong>CSRF token invalid:</strong> Токен опционален для регистрации</li>";
echo "<li><strong>422 Unprocessable Entity:</strong> Проверьте валидацию данных</li>";
echo "</ul>";

echo "<hr>";
echo "<a href='register.php'>← Вернуться к регистрации</a> | ";
echo "<a href='test-db.php'>Проверка БД →</a>";
