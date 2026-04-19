<?php
/**
 * Проверка состояния базы данных для системы записи экрана
 * Запустите этот файл через браузер: http://localhost/test-platform/check-recordings-db.php
 */

require_once __DIR__ . '/src/bootstrap.php';

header('Content-Type: text/html; charset=utf-8');

$db = Database::getInstance();

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
  <meta charset='UTF-8'>
  <title>Проверка БД - Записи экрана</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 40px; color: #1a1a1a; }
    .container { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; margin-bottom: 30px; font-size: 2rem; }
    .status { padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; }
    .status.success { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
    .status.error { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }
    .status.warning { background: #fff3cd; color: #856404; border: 2px solid #ffeaa7; }
    h2 { margin-top: 30px; color: #34495e; font-size: 1.3rem; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 700; border-bottom: 2px solid #dee2e6; }
    td { padding: 12px; border-bottom: 1px solid #dee2e6; }
    .code { background: #f8f9fa; padding: 2px 6px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 0.9em; }
    .btn { display: inline-block; padding: 12px 24px; background: #4361ee; color: #fff; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 20px; }
    .btn:hover { background: #3a56d4; }
    .info-box { background: #e7f3ff; border-left: 4px solid #4361ee; padding: 16px; margin: 20px 0; }
    .action-required { background: #fff3cd; border-left: 4px solid #ffc107; padding: 16px; margin: 20px 0; }
  </style>
</head>
<body>
<div class='container'>
  <h1>🔍 Проверка базы данных</h1>
  <p style='color: #6c757d; margin-bottom: 30px;'>Проверка состояния таблицы <code>recordings</code> для системы записи экрана</p>";

// 1. Проверка существования таблицы
try {
    $stmt = $db->query("SHOW TABLES LIKE 'recordings'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "<div class='status success'>✅ Таблица <code>recordings</code> существует</div>";
    } else {
        echo "<div class='status error'>❌ Таблица <code>recordings</code> НЕ существует!</div>";
        echo "<div class='action-required'>
          <strong>⚠️ Требуется действие:</strong> Необходимо создать таблицу для системы записи экрана.<br><br>
          <strong>Вариант 1:</strong> Выполните миграцию:<br>
          <div class='code'>mysql -u root -p test_platform < database/migrations/002_create_recordings.sql</div><br>
          <strong>Вариант 2:</strong> Импортируйте через phpMyAdmin:<br>
          - Откройте phpMyAdmin<br>
          - Выберите базу данных <code>test_platform</code><br>
          - Перейдите во вкладку 'Импорт'<br>
          - Загрузите файл <code>database/migrations/002_create_recordings.sql</code><br>
          - Нажмите 'Вперед'
        </div>";
        echo "</div></body></html>";
        exit;
    }

    // 2. Проверка структуры таблицы
    $stmt = $db->query("SHOW COLUMNS FROM recordings");
    $columns = $stmt->fetchAll();

    echo "<h2>📋 Структура таблицы</h2>";
    echo "<table>
      <thead>
        <tr>
          <th>Поле</th>
          <th>Тип</th>
          <th>NULL</th>
          <th>По умолчанию</th>
          <th>Статус</th>
        </tr>
      </thead>
      <tbody>";

    $requiredColumns = [
        'id' => 'INT UNSIGNED',
        'attempt_id' => 'INT UNSIGNED',
        'user_id' => 'INT UNSIGNED',
        'file_path' => 'VARCHAR',
        'file_size' => 'BIGINT',
        'chunk_index' => 'INT',
        'is_final' => 'TINYINT',
        'duration' => 'BIGINT',
        'created_at' => 'TIMESTAMP'
    ];

    $allColumnsExist = true;
    foreach ($requiredColumns as $colName => $colType) {
        $found = false;
        foreach ($columns as $col) {
            if ($col['Field'] === $colName) {
                $found = true;
                echo "<tr>
                  <td><code>{$col['Field']}</code></td>
                  <td>{$col['Type']}</td>
                  <td>{$col['Null']}</td>
                  <td>" . ($col['Default'] !== null ? $col['Default'] : 'NULL') . "</td>
                  <td style='color: #28a745; font-weight: 600;'>✅ OK</td>
                </tr>";
                break;
            }
        }
        if (!$found) {
            $allColumnsExist = false;
            echo "<tr>
              <td><code>$colName</code></td>
              <td><code>$colType</code></td>
              <td>-</td>
              <td>-</td>
              <td style='color: #dc3545; font-weight: 600;'>❌ ОТСУТСТВУЕТ</td>
            </tr>";
        }
    }

    echo "</tbody></table>";

    if ($allColumnsExist) {
        echo "<div class='status success'>✅ Все необходимые поля существуют в таблице</div>";
    } else {
        echo "<div class='status error'>❌ Некоторые поля отсутствуют! Требуется обновление таблицы.</div>";
        echo "<div class='action-required'>
          <strong>⚠️ Требуется действие:</strong> Некоторые поля отсутствуют в таблице.<br><br>
          Выполните SQL запрос в phpMyAdmin:<br>
          <div class='code'>
            ALTER TABLE recordings<br>
            &nbsp;&nbsp;ADD COLUMN IF NOT EXISTS missing_column_name TYPE;<br>
          </div>
          Или пересоздайте таблицу из файла миграции.
        </div>";
    }

    // 3. Проверка индексов
    $stmt = $db->query("SHOW INDEX FROM recordings");
    $indexes = $stmt->fetchAll();

    $indexNames = array_unique(array_column($indexes, 'Key_name'));

    echo "<h2>📑 Индексы</h2>";
    echo "<table>
      <thead>
        <tr>
          <th>Имя индекса</th>
          <th>Поля</th>
          <th>Статус</th>
        </tr>
      </thead>
      <tbody>";

    $requiredIndexes = [
        'PRIMARY' => ['id'],
        'idx_attempt' => ['attempt_id'],
        'idx_user' => ['user_id'],
        'idx_final' => ['is_final'],
        'idx_created_at' => ['created_at']
    ];

    foreach ($requiredIndexes as $idxName => $fields) {
        $found = in_array($idxName, $indexNames);
        $status = $found ? 
            "<td style='color: #28a745; font-weight: 600;'>✅ OK</td>" :
            "<td style='color: #dc3545; font-weight: 600;'>❌ ОТСУТСТВУЕТ</td>";
        
        echo "<tr>
          <td><code>$idxName</code></td>
          <td><code>" . implode(', ', $fields) . "</code></td>
          $status
        </tr>";
    }

    echo "</tbody></table>";

    // 4. Проверка внешних ключей
    $stmt = $db->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'recordings'
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $foreignKeys = $stmt->fetchAll();

    echo "<h2>🔗 Внешние ключи</h2>";
    if (count($foreignKeys) > 0) {
        echo "<table>
          <thead>
            <tr>
              <th>Ограничение</th>
              <th>Поле</th>
              <th>Ссылка</th>
              <th>Статус</th>
            </tr>
          </thead>
          <tbody>";

        foreach ($foreignKeys as $fk) {
            echo "<tr>
              <td><code>{$fk['CONSTRAINT_NAME']}</code></td>
              <td><code>{$fk['COLUMN_NAME']}</code></td>
              <td><code>{$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}</code></td>
              <td style='color: #28a745; font-weight: 600;'>✅ OK</td>
            </tr>";
        }

        echo "</tbody></table>";
        echo "<div class='status success'>✅ Внешние ключи настроены корректно</div>";
    } else {
        echo "<div class='status warning'>⚠️ Внешние ключи не настроены (опционально, но рекомендуется)</div>";
    }

    // 5. Проверка директории для записей
    $uploadDir = __DIR__ . '/uploads/recordings';
    if (is_dir($uploadDir)) {
        $isWritable = is_writable($uploadDir);
        if ($isWritable) {
            echo "<div class='status success'>✅ Директория <code>uploads/recordings/</code> существует и доступна для записи</div>";
        } else {
            echo "<div class='status error'>❌ Директория <code>uploads/recordings/</code> не доступна для записи!</div>";
            echo "<div class='action-required'>
              <strong>⚠️ Требуется действие:</strong> Установите права на директорию:<br>
              <div class='code'>chmod 755 uploads/recordings/</div>
            </div>";
        }
    } else {
        echo "<div class='status error'>❌ Директория <code>uploads/recordings/</code> не существует!</div>";
        echo "<div class='action-required'>
          <strong>⚠️ Требуется действие:</strong> Создайте директиву:<br>
          <div class='code'>mkdir uploads/recordings/ && chmod 755 uploads/recordings/</div>
        </div>";
    }

    // 6. Проверка количества записей
    $stmt = $db->query("SELECT COUNT(*) as cnt FROM recordings");
    $count = $stmt->fetch()['cnt'];

    echo "<div class='info-box'>
      <strong>📊 Текущее состояние:</strong><br>
      Записей в таблице: <strong>$count</strong>
    </div>";

    // Итоговый статус
    echo "<h2>🎯 Итоговый статус</h2>";
    if ($allColumnsExist && $isWritable) {
        echo "<div class='status success'>
          ✅ <strong>Все проверки пройдены!</strong><br>
          База данных полностью готова для работы системы записи экрана.<br>
          Никаких обновлений не требуется.
        </div>";
    } else {
        echo "<div class='status error'>
          ❌ <strong>Обнаружены проблемы!</strong><br>
          Пожалуйста, выполните действия, описанные выше.
        </div>";
    }

    echo "<a href='admin.php' class='btn'>Перейти в админ-панель</a>";
    echo "<a href='recordings.php' class='btn' style='background: #059669; margin-left: 10px;'>Просмотр записей</a>";

} catch (PDOException $e) {
    echo "<div class='status error'>
      ❌ <strong>Ошибка базы данных:</strong><br>
      " . htmlspecialchars($e->getMessage()) . "
    </div>";
}

echo "</div>
</body>
</html>";
