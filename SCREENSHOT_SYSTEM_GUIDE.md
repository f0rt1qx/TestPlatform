# 📸 Система скриншотов для тестирования

## Обзор

Добавлена система автоматического и ручного захвата скриншотов во время тестирования. Эта функция позволяет:

- **Автоматический захват** - скриншоты делаются через заданные интервалы времени
- **Ручной захват** - возможность сделать скриншот по требованию
- **Событийный захват** - автоматические скриншоты при переключении вкладок/возврате фокуса
- **Хранение метаданных** - каждый скриншот сопровождается информацией о времени, разрешении, URL и т.д.
- **Экспорт** - возможность скачать отдельные скриншоты или все сразу в ZIP-архиве

## Файлы

### 1. `public/js/screenshot-capture.js`
Основной модуль системы скриншотов. Предоставляет класс `ScreenshotCapture` со следующим API:

#### Конструктор
```javascript
var screenshotCapture = new ScreenshotCapture({
  attemptId: 'test_123',              // ID попытки тестирования
  captureFrequency: 30000,            // Интервал авто-захвата (мс)
  maxScreenshots: 50,                 // Максимальное количество хранимых скриншотов
  quality: 0.8,                       // Качество JPEG (0.0 - 1.0)
  includeMetadata: true,              // Включать ли метаданные
  onScreenshotCaptured: function(data) {
    // Callback при создании скриншота
  }
});
```

#### Методы

| Метод | Описание |
|-------|----------|
| `start()` | Запуск системы захвата |
| `stop()` | Остановка системы захвата |
| `capture(trigger, metadata)` | Сделать скриншот вручную |
| `enableAutoCapture(frequencyMs)` | Включить автоматический захват |
| `disableAutoCapture()` | Выключить автоматический захват |
| `getScreenshots()` | Получить массив всех скриншотов |
| `getScreenshotById(id)` | Получить скриншот по ID |
| `downloadScreenshot(id)` | Скачать отдельный скриншот |
| `downloadAllScreenshots()` | Скачать все скриншоты в ZIP |
| `clearScreenshots()` | Очистить все сохранённые скриншоты |
| `getStats()` | Получить статистику |

#### Структура данных скриншота
```javascript
{
  id: 'screenshot_1234567890_abc123',
  attemptId: 'test_123',
  trigger: 'manual' | 'auto' | 'visibility_change' | 'window_focus',
  timestamp: 1234567890,
  datetime: '2024-01-01T12:00:00.000Z',
  blob: Blob,                          // Binary large object
  url: 'blob:...',                     // Object URL для отображения
  width: 1920,                         // Ширина в пикселях
  height: 1080,                        // Высота в пикселях
  size: 245678,                        // Размер в байтах
  metadata: {                          // Метаданные
    userAgent: '...',
    language: 'ru-RU',
    platform: 'Win32',
    screenResolution: '1920x1080',
    viewportSize: { width: 1920, height: 1080 },
    scrollPosition: { x: 0, y: 0 },
    url: 'https://example.com',
    title: 'Page Title',
    activeElement: 'INPUT',
    timestamp: 1234567890
  }
}
```

### 2. `test-screenshot-capture.html`
Демонстрационная страница для тестирования системы скриншотов. Откройте в браузере:
```
http://localhost/test-screenshot-capture.html
```

## Зависимости

Для полной функциональности рекомендуются следующие библиотеки:

### html2canvas (обязательно для качественных скриншотов)
```html
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
```

### JSZip (для экспорта в ZIP)
```html
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
```

Без `html2canvas` система будет работать в режиме fallback с ограниченными возможностями.

## Интеграция в существующий проект

### 1. Подключение скрипта

Добавьте в ваши тестовые страницы (например, `test.php`, `dashboard.php`):

```html
<!-- Опционально: библиотеки для улучшенной функциональности -->
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- Модуль скриншотов -->
<script src="public/js/screenshot-capture.js"></script>
```

### 2. Инициализация

В JavaScript коде вашей тестовой страницы:

```javascript
// Инициализация при начале теста
var screenshotCapture = new ScreenshotCapture({
  attemptId: currentAttemptId,  // ID текущей попытки тестирования
  captureFrequency: 30000,      // Каждые 30 секунд
  maxScreenshots: 50,
  quality: 0.8,
  onScreenshotCaptured: function(data) {
    console.log('Screenshot captured:', data.id);
    
    // Опционально: отправка на сервер
    // uploadScreenshot(data);
  }
});

// Запуск при начале теста
function startTest() {
  screenshotCapture.start();
  screenshotCapture.enableAutoCapture(30000);
}

// Остановка при завершении теста
function endTest() {
  screenshotCapture.stop();
}
```

### 3. Интеграция с EyeTracker

Система скриншотов может работать совместно с existing EyeTracker:

```javascript
// Совместная работа с eye-tracker.js
var eyeTracker = new EyeTracker({
  attemptId: attemptId,
  onGazeData: function(data) {
    // Обработка данных взгляда
  }
});

var screenshotCapture = new ScreenshotCapture({
  attemptId: attemptId,
  onScreenshotCaptured: function(data) {
    // Привязка скриншотов к данным взгляда
    data.gazeData = eyeTracker.getStats();
  }
});

// Запуск обоих модулей
Promise.all([
  eyeTracker.start(),
  screenshotCapture.start()
]).then(function() {
  screenshotCapture.enableAutoCapture(30000);
});
```

### 4. Отправка на сервер

Пример функции для отправки скриншотов на сервер:

```javascript
function uploadScreenshot(screenshotData) {
  var formData = new FormData();
  formData.append('attempt_id', screenshotData.attemptId);
  formData.append('screenshot_id', screenshotData.id);
  formData.append('trigger', screenshotData.trigger);
  formData.append('timestamp', screenshotData.timestamp);
  formData.append('metadata', JSON.stringify(screenshotData.metadata));
  formData.append('image', screenshotData.blob, screenshotData.id + '.jpg');

  fetch('/api/upload-screenshot', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => console.log('Upload success:', data))
  .catch(err => console.error('Upload failed:', err));
}
```

## Примеры использования

### Базовое использование
```javascript
var sc = new ScreenshotCapture({ attemptId: 'test_1' });
sc.start();

// Сделать скриншот
sc.capture('manual').then(function(data) {
  console.log('Captured:', data.url);
});

// Остановить
sc.stop();
```

### Автоматический захват
```javascript
var sc = new ScreenshotCapture({
  attemptId: 'test_1',
  captureFrequency: 15000  // Каждые 15 секунд
});

sc.start();
sc.enableAutoCapture();  // Использует captureFrequency из конструктора
// или
sc.enableAutoCapture(60000);  // Переопределить интервал (60 секунд)
```

### Работа с галереей
```javascript
// Получить все скриншоты
var all = sc.getScreenshots();

// Получить последний
var last = all[all.length - 1];

// Скачать конкретный
sc.downloadScreenshot(last.id);

// Скачать все в ZIP
sc.downloadAllScreenshots();

// Очистить память
sc.clearScreenshots();
```

### Статистика
```javascript
var stats = sc.getStats();
console.log(stats);
// {
//   totalScreenshots: 10,
//   isActive: true,
//   autoCaptureEnabled: true,
//   captureFrequency: 30000,
//   maxScreenshots: 50,
//   recentCount: 2,
//   lastCaptureTime: 1234567890
// }
```

## События для автоматического захвата

Система автоматически делает скриншоты при:

1. **visibilitychange** - когда пользователь возвращается на вкладку
2. **window focus** - когда окно получает фокус
3. **auto** - по таймеру (если включён авто-захват)
4. **manual** - ручной вызов метода `capture()`

Вы можете добавить свои триггеры:

```javascript
// Скриншот при клике на важную кнопку
document.getElementById('submit-test').addEventListener('click', function() {
  screenshotCapture.capture('form_submit', {
    customData: { buttonId: 'submit-test' }
  });
});

// Скриншот при ошибке
window.addEventListener('error', function(e) {
  screenshotCapture.capture('error_event', {
    errorMessage: e.message,
    errorSource: e.filename
  });
});
```

## Настройки производительности

### Оптимизация для длинных тестов
```javascript
var sc = new ScreenshotCapture({
  attemptId: 'long_test',
  captureFrequency: 60000,    // Раз в минуту вместо 30 секунд
  maxScreenshots: 100,        // Увеличить лимит
  quality: 0.7                // Немного снизить качество для экономии памяти
});
```

### Минималистичный режим
```javascript
var sc = new ScreenshotCapture({
  attemptId: 'minimal',
  includeMetadata: false,     // Не собирать метаданные
  quality: 0.6                // Ниже качество
});
```

## Безопасность и конфиденциальность

⚠️ **Важные замечания:**

1. Скриншоты могут содержать конфиденциальную информацию
2. Данные хранятся в памяти браузера до очистки или закрытия вкладки
3. При использовании `URL.createObjectURL()` не забывайте освобождать память
4. Рекомендуется очищать скриншоты после отправки на сервер

```javascript
// После успешной отправки
uploadScreenshot(data).then(function() {
  URL.revokeObjectURL(data.url);  // Освободить память
});
```

## Тестирование

1. Откройте `test-screenshot-capture.html` в браузере
2. Нажмите "Запустить захват скриншотов"
3. Попробуйте:
   - Сделать ручной скриншот
   - Включить авто-захват
   - Переключиться на другую вкладку и вернуться
   - Скачать скриншоты
4. Проверьте консоль браузера на наличие логов

## Будущие улучшения

- [ ] Прямая загрузка на сервер через configured endpoint
- [ ] Сжатие изображений перед отправкой
- [ ] Интеграция с системой античитинга
- [ ] Привязка скриншотов к конкретным вопросам теста
- [ ] Поддержка видео-записи сессии
- [ ] Облачное хранение скриншотов

## Поддержка

При возникновении проблем:
1. Проверьте консоль браузера на ошибки
2. Убедитесь, что html2canvas загружен (если требуется)
3. Проверьте права доступа к буферу обмена и файловой системе
4. Убедитесь, что страница открыта по HTTPS (требуется для некоторых функций)
