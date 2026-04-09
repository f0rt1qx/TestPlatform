# 👁️ Eye-Tracking — Полное Руководство

## 📦 Что реализовано

### 1. Клиентская часть (JavaScript)

**Файл:** `public/js/eye-tracker.js`

- ✅ Автоматическая инициализация WebGazer.js
- ✅ 9-точечная калибровка с визуальным интерфейсом
- ✅ Отслеживание точек взгляда в реальном времени
- ✅ Определение фиксаций (минимум 100ms)
- ✅ Периодическое логирование (каждые 5 секунд)
- ✅ Визуальный индикатор статуса
- ✅ Graceful degradation (если камера недоступна)

### 2. Интеграция в тест

**Файл:** `test.php`

- ✅ Подключение eye-tracker.js
- ✅ Автоматическая инициализация при загрузке теста
- ✅ Логирование статуса в монитор активности
- ✅ Корректная остановка при завершении/дисквалификации

### 3. Серверная часть (API)

**Файлы:**
- `api/test.php` — добавлен тип события `eye_fixations`
- `api/admin.php` — endpoint для получения данных eye-tracking
- `src/models/ResultModel.php` — метод `getEyeTrackingLogs()`

### 4. База данных

**Файл:** `sql/migrations/002_add_eye_tracking_support.sql`

- ✅ Добавлен `eye_fixations` в ENUM event_type
- ✅ Добавлен индекс для оптимизации запросов

### 5. Админ-панель

**Файл:** `admin.php`

- ✅ Новая вкладка "👁️ Eye-tracking"
- ✅ Статистика (всего фиксаций, средняя длительность, уникальные пользователи)
- ✅ Таблица с детализацией по каждой записи
- ✅ Фильтры по тестам и попыткам
- ✅ Визуализация тепловых карт на Canvas
- ✅ Кнопка просмотра деталей фиксации

---

## 🚀 Установка и запуск

### Шаг 1: Применение миграции БД

```bash
# Через командную строку MySQL
mysql -u root -p < sql/migrations/002_add_eye_tracking_support.sql

# ИЛИ через phpMyAdmin:
# 1. Откройте phpMyAdmin
# 2. Выберите базу test_platform
# 3. Перейдите во вкладку "Импорт"
# 4. Загрузите sql/migrations/002_add_eye_tracking_support.sql
```

### Шаг 2: Проверка файлов

Убедитесь что созданы следующие файлы:
- ✅ `public/js/eye-tracker.js`
- ✅ `sql/migrations/002_add_eye_tracking_support.sql`
- ✅ `docs/EYE_TRACKING.md`

Изменены файлы:
- ✅ `test.php` — добавлена интеграция eye-tracking
- ✅ `api/test.php` — добавлен event_type `eye_fixations`
- ✅ `api/admin.php` — добавлен endpoint `eye_tracking`
- ✅ `admin.php` — добавлена вкладка Eye-tracking
- ✅ `public/js/app.js` — добавлен метод `adminEyeTracking()`
- ✅ `src/models/ResultModel.php` — добавлен метод `getEyeTrackingLogs()`

### Шаг 3: Запуск

1. Откройте браузер и перейдите на `http://localhost/test-platform/`
2. Войдите как студент
3. Начните прохождение любого теста
4. Разрешите доступ к веб-камере
5. Пройдите калибровку (нажмите 5 раз на каждую из 9 точек)
6. Пройдите тест
7. Откройте админ-панель → вкладка Eye-tracking

---

## 📊 Как это работает

### Процесс калибровки

```
1. Пользователь начинает тест
   ↓
2. Запрашивается доступ к камере
   ↓
3. Загружается WebGazer.js с CDN
   ↓
4. Показывается экран калибровки
   - 9 красных точек (3x3 сетка)
   - Нужно нажать 5 раз на каждую
   - Глядя на точку, пользователь калибрует систему
   ↓
5. Калибровка завершена
   - Показывается индикатор "Eye-tracking активен"
   - Начинается отслеживание взгляда
```

### Процесс отслеживания

```
1. WebGazer сэмплирует координаты взгляда (~10 раз/сек)
   ↓
2. Определяются фиксации (взгляд в одной области 100ms+)
   ↓
3. Фиксации накапливаются в буфере
   ↓
4. Каждые 5 секунд данные отправляются на сервер:
   {
     "event_type": "eye_fixations",
     "data": {
       "fixations": [...],
       "count": 10
     }
   }
   ↓
5. Данные сохраняются в таблицу logs
```

### Структура данных фиксации

```json
{
  "startX": 450.2,
  "startY": 320.5,
  "startTime": 1712239200000,
  "endX": 455.1,
  "endY": 318.9,
  "endTime": 1712239200250,
  "duration": 250,
  "points": 5
}
```

**Описание полей:**
- `startX/startY` — координаты начала фиксации
- `endX/endY` — координаты конца фиксации
- `startTime/endTime` — timestamp (ms)
- `duration` — длительность в миллисекундах
- `points` — количество сэмплов взгляда

---

## 🎨 Визуализация в админ-панели

### Статистика

При открытии вкладки Eye-tracking отображаются 4 карточки:

1. **Всего фиксаций** — общее количество зафиксированных точек
2. **Средняя длительность** — среднее время фиксации (ms)
3. **Уникальных пользователей** — сколько разных пользователей прошло тест
4. **Записей в логе** — количество записей в таблице logs

### Тепловая карта

Canvas элемент отображает все точки фиксаций:
- **Радиус градиента** зависит от длительности фиксации
- **Прозрачность** зависит от длительности (чем дольше — тем ярче)
- **Точки**标记руют центры фиксаций

### Таблица данных

Отображает:
- Время записи
- Пользователь (имя + ID)
- Тест
- Количество фиксаций
- Средняя длительность
- Кнопка "Детали"

---

## 🔧 Настройка

### Изменение параметров калибровки

В `eye-tracker.js`:

```javascript
// Количество точек калибровки (positions array)
var positions = [
  { x: '15%', y: '15%' }, { x: '50%', y: '15%' }, { x: '85%', y: '15%' },
  { x: '15%', y: '50%' }, { x: '50%', y: '50%' }, { x: '85%', y: '50%' },
  { x: '15%', y: '85%' }, { x: '50%', y: '85%' }, { x: '85%', y: '85%' }
];

// Количество кликов на точку
var requiredClicks = 5; // измените на 3 для более быстрой калибровки
```

### Изменение параметров фиксации

```javascript
this.FIXATION_DURATION = 100; // минимальная длительность (ms)
this.SAMPLE_RATE       = 100; // частота сэмплов (ms)
```

### Изменение частоты логирования

В `test.php` найдите `_startLogFlush`:

```javascript
EyeTracker.prototype._startLogFlush = function() {
  var self = this;
  this._logInterval = setInterval(function() {
    self._flushData();
  }, 5000); // измените на нужное значение (ms)
};
```

### Отключение eye-tracking для всех тестов

Закомментируйте в `test.php`:

```javascript
// initEyeTracker();
```

---

## 🧪 Тестирование

### Проверка работы

1. **Откройте консоль браузера** (F12)
2. **Начните тест** — должны появиться логи:
   ```
   [EyeTracker] Initialization started
   [EyeTracker] Calibration complete
   [EyeTracker] Flushed: X points, Y fixations
   ```

3. **Проверьте базу данных**:
   ```sql
   SELECT * FROM logs WHERE event_type = 'eye_fixations' ORDER BY created_at DESC LIMIT 5;
   ```

### Возможные проблемы

**"Не удалось активировать отслеживание взгляда"**
- Проверьте что браузер имеет доступ к камере
- Убедитесь что используется HTTP (localhost) или HTTPS
- Разрешите доступ к камере в настройках браузера

**"WebGazer не загружается"**
- Проверьте интернет-соединение (CDN)
- Скачайте webgazer.js локально и измените путь

**"Нет данных в админ-панели"**
- Убедитесь что миграция применена
- Проверьте что пользователь прошёл тест с камерой

---

## 📈 Примеры использования данных

### 1. Обнаружение списывания

```sql
-- Пользователи с аномально большим количеством фиксаций
SELECT 
  u.username,
  COUNT(*) as log_count,
  SUM(JSON_EXTRACT(l.event_data, '$.count')) as total_fixations
FROM logs l
JOIN users u ON l.user_id = u.id
WHERE l.event_type = 'eye_fixations'
GROUP BY u.id
HAVING total_fixations > 1000
ORDER BY total_fixations DESC;
```

### 2. Анализ поведения

```sql
-- Среднее время фиксации по тестам
SELECT 
  t.title,
  AVG(fix.duration) as avg_fixation_duration
FROM logs l
JOIN attempts a ON l.attempt_id = a.id
JOIN tests t ON a.test_id = t.id,
JSON_TABLE(l.event_data, '$.fixations[*]' COLUMNS (
  duration INT PATH '$.duration'
)) as fix
WHERE l.event_type = 'eye_fixations'
GROUP BY t.id;
```

### 3. Python анализ

```python
import pandas as pd
import matplotlib.pyplot as plt

# Загрузка данных из БД
data = pd.read_sql("""
    SELECT l.*, u.username, t.title as test_title
    FROM logs l
    JOIN users u ON l.user_id = u.id
    JOIN attempts a ON l.attempt_id = a.id
    JOIN tests t ON a.test_id = t.id
    WHERE l.event_type = 'eye_fixations'
""", connection)

# Анализ
fixations = []
for _, row in data.iterrows():
    event_data = json.loads(row['event_data'])
    for fix in event_data['fixations']:
        fixations.append({
            'username': row['username'],
            'test': row['test_title'],
            'x': fix['startX'],
            'y': fix['startY'],
            'duration': fix['duration']
        })

df = pd.DataFrame(fixations)

# Тепловая карта
plt.figure(figsize=(12, 8))
plt.hexbin(df['x'], df['y'], gridsize=50, cmap='YlOrRd')
plt.colorbar(label='Плотность фиксаций')
plt.xlabel('X (px)')
plt.ylabel('Y (px)')
plt.title('Eye-Tracking Heatmap')
plt.show()
```

---

## 🔒 Безопасность и конфиденциальность

### Что собирается
- ✅ Координаты взгляда (x, y)
- ✅ Временные метки
- ✅ Длительность фиксаций

### Что НЕ собирается
- ❌ Видео с камеры (не сохраняется)
- ❌ Изображения пользователя
- ❌ Биометрические данные

### Защита данных
- Данные привязаны только к `attempt_id`
- Требуется явное разрешение на камеру
- Пользователь видит индикатор работы
- Данные доступны только администраторам

---

## 📚 Дополнительные ресурсы

- [WebGazer.js Official](https://webgazer.cs.brown.edu/)
- [Документация платформы](docs/EYE_TRACKING.md)
- [Примеры анализа eye-tracking](https://github.com/brownhci/WebGazer)

---

## ✅ Чек-лист перед продакшеном

- [ ] Применена миграция БД
- [ ] Протестировано в Chrome/Firefox/Edge
- [ ] Проверена работа на HTTPS
- [ ] Администраторы имеют доступ к вкладке
- [ ] Пользователи предупреждены о сборе данных
- [ ] Логи сохраняются корректно
- [ ] Визуализация работает с большим объемом данных

---

**Готово!** 🎉 Eye-tracking полностью интегрирован в платформу.
