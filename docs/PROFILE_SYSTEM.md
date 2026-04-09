# 📋 Система профиля пользователя

## 🎯 Возможности

### Личная информация
- ✏️ Имя и фамилия
- 📝 О себе (биография)
- 📱 Телефон
- 🏙️ Город
- 🌐 Веб-сайт
- 📅 Дата рождения
- 🔗 Социальные сети (VK, Telegram)

### Безопасность
- 🔑 Смена пароля
- 📧 Изменение email
- ✏️ Смена имени пользователя

### Аватарка
- 🖼️ Загрузка изображений (JPG, PNG, GIF, WebP)
- 📏 Максимальный размер: 5MB
- 🗑️ Удаление аватарки
- ♻️ Автоматическое удаление старой аватарки

### Достижения 🏆
- 🎯 **Первый шаг** — Пройти первый тест
- 🏆 **Опытный пользователь** — Пройти 10 тестов
- 💎 **Перфекционист** — Средний балл 100%
- 👑 **Мастер тестов** — Пройти 50 тестов успешно
- ✅ **Честный игрок** — Низкий уровень подозрений
- ⏱️ **Тайм-менеджер** — Проведено в тестах более X часов

### Активность 📊
- 📈 Тепловая карта активности (30 дней)
- 📋 Последние результаты (5 тестов)
- 📊 Полная статистика

## 📁 Структура файлов

```
test-platform/
├── profile.php                 # Страница профиля
├── api/profile.php             # API для профиля
├── src/models/ProfileModel.php # Модель данных
├── uploads/avatars/            # Загруженные аватарки
└── public/img/default-avatar.svg # Аватарка по умолчанию
```

## 🔌 API Endpoints

### GET `/api/profile.php?action=get`
Получить полный профиль пользователя
```json
{
  "success": true,
  "profile": { ... },
  "statistics": { ... },
  "achievements": [ ... ],
  "recent_results": [ ... ],
  "csrf_token": "..."
}
```

### POST `/api/profile.php?action=update`
Обновить личную информацию
```json
{
  "bio": "О себе",
  "phone": "+7 (999) 000-00-00",
  "city": "Москва",
  "website": "https://...",
  "birth_date": "2000-01-01",
  "social_vk": "...",
  "social_tg": "...",
  "first_name": "Иван",
  "last_name": "Иванов",
  "csrf_token": "..."
}
```

### POST `/api/profile.php?action=change_email`
Изменить email
```json
{
  "email": "new@email.com",
  "csrf_token": "..."
}
```

### POST `/api/profile.php?action=change_username`
Изменить имя пользователя
```json
{
  "username": "new_username",
  "csrf_token": "..."
}
```

### POST `/api/profile.php?action=change_password`
Изменить пароль
```json
{
  "current_password": "...",
  "new_password": "...",
  "confirm_password": "...",
  "csrf_token": "..."
}
```

### POST `/api/profile.php?action=upload_avatar`
Загрузить аватарку (multipart/form-data)
```
avatar: (file)
csrf_token: "..."
```

### POST `/api/profile.php?action=remove_avatar`
Удалить аватарку
```json
{
  "csrf_token": "..."
}
```

### GET `/api/profile.php?action=activity&days=30`
Получить активность по дням
```json
{
  "success": true,
  "activity": {
    "2024-03-25": 3,
    "2024-03-26": 1
  },
  "days": 30
}
```

## 🗄️ База данных

### Таблица `users` (новые поля)

| Поле | Тип | Описание |
|------|-----|----------|
| `avatar` | VARCHAR(255) | Путь к аватарке |
| `bio` | TEXT | О себе |
| `phone` | VARCHAR(20) | Телефон |
| `city` | VARCHAR(100) | Город |
| `website` | VARCHAR(255) | Веб-сайт |
| `social_vk` | VARCHAR(255) | VK профиль |
| `social_tg` | VARCHAR(255) | Telegram профиль |
| `birth_date` | DATE | Дата рождения |
| `last_visit_at` | TIMESTAMP | Последний визит |
| `settings_json` | JSON | Настройки пользователя |

## 🎨 Стилизация

Профиль использует современный дизайн с:
- Градиентным заголовком
- Карточками статистики
- Адаптивной вёрсткой
- Тёмной темой
- Плавными анимациями

## 🔒 Безопасность

- ✅ CSRF защита для всех форм
- ✅ Валидация входных данных
- ✅ Проверка прав доступа (требуется авторизация)
- ✅ Безопасная загрузка файлов (проверка MIME-типа)
- ✅ Автоматическое удаление старых файлов
- ✅ Хеширование паролей (bcrypt)

## 📝 Примечания

- Email и username должны быть уникальными
- При смене email требуется повторная верификация
- Аватарки хранятся в `uploads/avatars/`
- Достижения рассчитываются автоматически на основе статистики
