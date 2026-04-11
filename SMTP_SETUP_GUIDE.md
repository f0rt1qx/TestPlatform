# 📧 Настройка SMTP для отправки Email

## Gmail SMTP (рекомендуется)

### Шаг 1: Включите 2-факторную аутентификацию

1. Откройте https://myaccount.google.com/security
2. Включите **Двухэтапную аутентификацию**

### Шаг 2: Создайте пароль приложения

1. Откройте https://myaccount.google.com/apppasswords
2. Выберите **Приложение** → **Почта**
3. Выберите **Устройство** → **Другое** → введите `Sapienta`
4. Нажмите **Создать**
5. **Скопируйте 16-значный пароль** (он показывается один раз!)

### Шаг 3: Настройте config.php

Откройте `config/config.php` и измените:

```php
// Email
define('MAIL_HOST',       'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USER',       'your-email@gmail.com');     // Ваш Gmail
define('MAIL_PASS',       'abcd efgh ijkl mnop');      // Пароль приложения (без пробелов: abcdefghijklmnop)
define('MAIL_FROM',       'your-email@gmail.com');
define('MAIL_FROM_NAME',  'Sapienta');
define('MAIL_ENABLED',    true);  // ← Включить!
```

**Важно:** В `MAIL_PASS` вставьте пароль приложения **без пробелов**!

### Шаг 4: Протестируйте

1. Откройте `http://localhost/test-platform/test-email.php`
2. Введите ваш email
3. Нажмите "📤 Отправить тестовое письмо"
4. Проверьте почту (включая папку "Спам")

---

## Yandex SMTP

### Шаг 1: Создайте пароль приложения

1. Откройте https://passport.yandex.ru/profile
2. **Безопасность** → **Пароли для внешних приложений**
3. Создайте новый пароль

### Шаг 2: Настройка

```php
define('MAIL_HOST',       'smtp.yandex.ru');
define('MAIL_PORT',       587);
define('MAIL_USER',       'your-email@yandex.ru');
define('MAIL_PASS',       'ваш-пароль-приложения');
define('MAIL_FROM',       'your-email@yandex.ru');
define('MAIL_FROM_NAME',  'Sapienta');
define('MAIL_ENABLED',    true);
```

---

## Mail.ru SMTP

```php
define('MAIL_HOST',       'smtp.mail.ru');
define('MAIL_PORT',       587);
define('MAIL_USER',       'your-email@mail.ru');
define('MAIL_PASS',       'ваш-пароль-приложения');
define('MAIL_FROM',       'your-email@mail.ru');
define('MAIL_FROM_NAME',  'Sapienta');
define('MAIL_ENABLED',    true);
```

---

## Другие SMTP провайдеры

### Outlook/Hotmail
```php
define('MAIL_HOST', 'smtp-mail.outlook.com');
define('MAIL_PORT', 587);
```

### Zoho Mail
```php
define('MAIL_HOST', 'smtp.zoho.com');
define('MAIL_PORT', 587);
```

### Custom SMTP
```php
define('MAIL_HOST', 'smtp.yourdomain.com');
define('MAIL_PORT', 587);  // или 465 для SSL
define('MAIL_USER', 'noreply@yourdomain.com');
define('MAIL_PASS', 'your-password');
define('MAIL_FROM', 'noreply@yourdomain.com');
define('MAIL_FROM_NAME', 'Your App');
define('MAIL_ENABLED', true);
```

---

## 🔧 Troubleshooting

### Ошибка: "Connection failed"

**Причина:** Неправильный host/port или фаервол

**Решение:**
1. Проверьте что порт не заблокирован фаерволом
2. Попробуйте `telnet smtp.gmail.com 587`
3. Проверьте что XAMPP имеет доступ к интернету

### Ошибка: "Authentication failed"

**Причина:** Неправильный логин/пароль

**Решение:**
1. Убедитесь что используете **пароль приложения**, а не обычный пароль от аккаунта
2. Проверьте что 2FA включен (для Gmail)
3. Уберите пробелы из пароля приложения

### Ошибка: "TLS required"

**Причина:** Сервер требует шифрование

**Решение:**
- Для порта 587: TLS включается автоматически через STARTTLS
- Для порта 465: Измените порт на 587 или настройте SSL

### Письмо не приходит

**Причина:** Попало в спам или задержка

**Решение:**
1. Проверьте папку **Спам**
2. Подождите 1-2 минуты
3. Проверьте логи SMTP сервера

---

## Тестирование без реального SMTP (Development)

Пока не настроили SMTP, система работает в **development режиме**:
- Код OTP показывается на странице в желтом блоке
- Можно тестировать вход без реальной отправки email

Когда будете готовы — включите `MAIL_ENABLED = true` и настройте SMTP.

---

## Проверка работы

1. Откройте `http://localhost/test-platform/test-email.php`
2. Нажмите "🔌 Тест подключения"
   - Должно показать: ✅ Подключение успешно
3. Введите email и нажмите "📤 Отправить тестовое письмо"
4. Проверьте почту

---

## Production Checklist

Перед деплоем:

- [ ] Настроен SMTP сервер
- [ ] Протестирована отправка email
- [ ] `MAIL_ENABLED = true`
- [ ] `APP_DEBUG = false` (отключить test-email.php)
- [ ] Удален файл `test-email.php` или закрыт доступ
- [ ] Настроен логирование ошибок отправки

---

## Готово! 🎉

После настройки SMTP:
1. Пользователи будут получать реальные email с кодами
2. Желтый блок с кодом больше не появится
3. Вход через OTP станет полностью безопасным
