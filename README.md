# 🎓 TestPlatform — Платформа для тестирования

> **Современная система онлайн-тестирования с защитой от списывания**

[![PHP](https://img.shields.io/badge/PHP-8.0+-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## ✨ Особенности

- 🔐 **Безопасность**: CSRF защита, rate limiting, JWT авторизация
- 🚀 **Производительность**: Оптимизированные SQL запросы, кэширование
- 🎨 **UI/UX**: Современный дизайн, светлая/тёмная тема, адаптивность
- 🛡️ **Анти-читинг**: Отслеживание переключений вкладок, времени ответа
- 📊 **Аналитика**: Детальные результаты, логи событий

---

## 📋 Требования

- XAMPP с Apache 2.4+ и PHP 8.0+
- MySQL 5.7+ (через phpMyAdmin)
- Windows 10/11 или Linux

---

## 🚀 Быстрый старт (5 минут)

### Шаг 1: Установите XAMPP

Скачайте и установите: https://www.apachefriends.org/

### Шаг 2: Скопируйте проект

```
C:\xampp\htdocs\test-platform\
```

### Шаг 3: Запустите XAMPP

1. Откройте **XAMPP Control Panel**
2. Нажмите **Start** рядом с **Apache**
3. Нажмите **Start** рядом с **MySQL**

### Шаг 4: Создайте базу данных

1. Откройте: **http://localhost/phpmyadmin**
2. Нажмите **«Новая»**
3. Имя: `test_platform`
4. Кодировка: `utf8mb4_unicode_ci`

### Шаг 5: Импортируйте SQL

1. Выберите БД `test_platform`
2. Вкладка **«Импорт»**
3. Файл: `test-platform/sql/database.sql`
4. Нажмите **«Вперёд»**

### Шаг 6: Откройте сайт

```
http://localhost/test-platform/
```

> ⚠️ **JWT_SECRET будет сгенерирован автоматически при первом запуске!**

---

## 👤 Тестовые аккаунты

| Роль | Логин | Email | Пароль |
|------|-------|-------|--------|
| Администратор | `admin` | admin@testplatform.local | `password` |
| Студент | `student1` | student@testplatform.local | `password` |

> ⚠️ **Смените пароли в production!**

---

## 📁 Структура файлов

```
test-platform/
│
├── 📄 index.php              — Главная страница
├── 📄 login.php              — Вход
├── 📄 register.php           — Регистрация
├── 📄 dashboard.php          — Личный кабинет
├── 📄 test.php               — Прохождение теста
├── 📄 admin.php              — Админ-панель
├── 📄 forgot-password.php    — Восстановление пароля
├── 📄 .htaccess              — Настройки Apache
├── 📄 .gitignore             — Git ignore файл
├── 📄 OPTIMIZATION_REPORT.md — Отчёт об оптимизациях
│
├── 📁 config/
│   ├── config.php            — Конфигурация
│   └── secret.php            — Авто-генерируемый JWT_SECRET ⚠️
│
├── 📁 src/
│   ├── bootstrap.php         — Автозагрузчик, хелперы (CSRF, rate limit)
│   ├── helpers/
│   │   ├── Database.php      — PDO singleton с логированием
│   │   └── JWT.php           — JWT без библиотек
│   ├── middleware/
│   │   └── AuthMiddleware.php — Проверка авторизации
│   └── models/
│       ├── UserModel.php     — Пользователи
│       ├── TestModel.php     — Тесты (оптимизировано N+1)
│       └── ResultModel.php   — Результаты + анти-чит
│
├── 📁 api/
│   ├── auth.php              — Auth API с CSRF + rate limit
│   ├── test.php              — Test API с CSRF
│   └── admin.php             — Admin API с логированием
│
├── 📁 public/
│   ├── css/main.css          — Стили (светлая/тёмная тема)
│   └── js/
│       ├── app.js            — API клиент с CSRF
│       └── anticheat.js      — Анти-читинг
│
├── 📁 logs/                  — Логи ошибок ⚠️
│   └── error.log
│
└── 📁 sql/
    └── database.sql          — Схема БД
```

---

## 🛡️ Безопасность

### Реализованные механизмы

| Механизм | Статус | Описание |
|----------|--------|----------|
| CSRF токены | ✅ | Защита от подделки запросов |
| Rate Limiting | ✅ | 5 попыток входа за 15 минут |
| JWT с авто-секретом | ✅ | Уникальный ключ для каждого экземпляра |
| Secure Cookies | ✅ | Secure + SameSite + HttpOnly |
| Заголовки безопасности | ✅ | X-Frame-Options, CSP, X-XSS-Protection |
| Валидация паролей | ✅ | Минимум 8 символов, заглавные + цифры |
| Логирование админа | ✅ | Все действия администратора |
| SQL injection защита | ✅ | Prepared statements + whitelist |

---

## 🛡️ Анти-читинг система

### Отслеживаемые события

| Событие | Важность | Описание |
|---------|----------|----------|
| `tab_switch` | 🔴 HIGH | Переключение вкладок |
| `devtools_open` | 🔴 HIGH | Открытие DevTools |
| `window_blur` | 🟡 MEDIUM | Потеря фокуса |
| `page_reload` | 🟡 MEDIUM | Перезагрузка |
| `rapid_answer` | 🟡 MEDIUM | Быстрый ответ (<3 сек) |
| `copy_attempt` | 🟢 LOW | Попытка копирования |
| `right_click` | 🟢 LOW | Правая кнопка мыши |

### Пороги (config.php):
```php
ANTICHEAT_TAB_SWITCH_MAX = 5;    // Дисквалификация
ANTICHEAT_CHEAT_THRESHOLD = 40;  // Флаг нарушения
```

---

## 📊 API Endpoints

### Auth API (`/api/auth.php`)
```
POST /api/auth.php?action=register       — Регистрация
POST /api/auth.php?action=login          — Вход (с rate limit)
POST /api/auth.php?action=logout         — Выход
GET  /api/auth.php?action=me             — Текущий пользователь
```

### Test API (`/api/test.php`)
```
GET  /api/test.php?action=list           — Список тестов
POST /api/test.php?action=start          — Начать тест
POST /api/test.php?action=submit         — Отправить ответы
POST /api/test.php?action=log_event      — Лог события
GET  /api/test.php?action=my_results     — Результаты
```

### Admin API (`/api/admin.php`) — только admin
```
GET  /api/admin.php?action=users         — Пользователи
POST /api/admin.php?action=block_user    — Блокировка
GET  /api/admin.php?action=tests         — Тесты
POST /api/admin.php?action=create_test   — Создать тест
POST /api/admin.php?action=add_question  — Добавить вопрос
POST /api/admin.php?action=import_csv    — CSV импорт
GET  /api/admin.php?action=logs          — Логи
GET  /api/admin.php?action=export_csv    — Экспорт CSV
```

---

## 📥 CSV формат вопросов

```csv
question_text,type,points,answer1,correct1,answer2,correct2
"Что такое PHP?",single,1,"Язык программирования",1,"База данных",0
"Выберите фреймворки:",multiple,2,"Laravel",1,"Symfony",1,"WordPress",0
```

---

## 🔧 Настройка production

### 1. Включите HTTPS
```php
// Автоматически определяется в config.php
```

### 2. Настройте почту
```php
MAIL_ENABLED = true
MAIL_HOST = 'smtp.example.com'
MAIL_PORT = 587
MAIL_USER = 'user@example.com'
MAIL_PASS = '***'
```

### 3. Установите APP_DEBUG = false
```php
define('APP_DEBUG', false);
```

### 4. Права доступа
```bash
chmod 755 /path/to/test-platform
chmod 600 config/secret.php
chmod 777 logs/
```

---

## 📈 Производительность

| Метрика | Значение |
|---------|----------|
| Запросов к БД за тест | 1-2 (было 20-50) |
| Время загрузки теста | ~50ms (было 500ms) |
| Persistent DB соединения | ✅ |
| Оптимизация N+1 | ✅ |

---

## ❓ Частые проблемы

### База данных не подключается
- Проверьте что MySQL запущен
- Проверьте `DB_PASS` в config.php

### JWT ошибки
- Удалите `config/secret.php` — он пересоздастся
- Очистите localStorage (F12 → Application)

### CSRF token invalid
- Обновите страницу для получения нового токена
- Проверите что сессии работают

---

## 📚 Документация

- [OPTIMIZATION_REPORT.md](OPTIMIZATION_REPORT.md) — Полный отчёт об оптимизациях
- [sql/database.sql](sql/database.sql) — Схема БД

---

## 📄 Лицензия

MIT License

---

**Разработано с ❤️ для современного образования**
