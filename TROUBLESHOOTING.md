# Решение проблемы с регистрацией (422 Unprocessable Entity)

## 🔍 Диагностика

### 1. Проверьте базу данных

Откройте в браузере:
```
http://localhost/test-platform/test-db.php
```

Если видите ошибку подключения к БД:
- ✅ Убедитесь что **MySQL запущен** в XAMPP Control Panel
- ✅ Создайте базу данных через phpMyAdmin:
  ```sql
  CREATE DATABASE IF NOT EXISTS `test_platform` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;
  ```
- ✅ Импортируйте дамп `sql/database.sql` через phpMyAdmin

### 2. Проверьте регистрацию напрямую

Откройте в браузере:
```
http://localhost/test-platform/test-register.php
```

Если тест успешен (пользователь создан), но форма регистрации не работает:
- Проверьте консоль браузера (F12 → Console)
- Проверьте логи ошибок в `logs/error.log`

### 3. Проверьте логи

Файл логов: `C:\xampp\htdocs\test-platform\logs\error.log`

Ищите строки начинающиеся с `[REGISTER]`

---

## 🛠️ Распространённые проблемы и решения

### Ошибка: "CSRF token invalid"
**Решение:** CSRF токен теперь опционален для регистрации. Если проблема остаётся:
1. Очистите кэш браузера (Ctrl+Shift+Del)
2. Откройте консоль (F12) и выполните: `localStorage.clear()`
3. Перезагрузите страницу

### Ошибка: "Table 'users' doesn't exist"
**Решение:**
```bash
# Через командную строку MySQL
mysql -u root -p
USE test_platform;
SOURCE C:/xampp/htdocs/test-platform/sql/database.sql;
```

### Ошибка: "Email уже зарегистрирован" или "Имя пользователя занято"
**Решение:** Используйте уникальные email и username

### Ошибка: "Пароль: минимум 8 символов"
**Решение:** Пароль должен быть не менее 8 символов

---

## 📋 Требования к данным

| Поле | Требования |
|------|------------|
| Username | 3-50 символов, только латиница, цифры, _ |
| Email | Корректный email формат |
| Password | Минимум 8 символов |
| First Name | Опционально |
| Last Name | Опционально |

---

## 🧪 Тестовые данные для проверки

```
Username: testuser123
Email: testuser123@example.com
Password: testpass123
First Name: Иван
Last Name: Иванов
```

---

## 📞 Если ничего не помогло

1. Проверьте что **Apache и MySQL запущены** в XAMPP
2. Проверьте права доступа к папке `logs/`
3. Включите отладку в `config.php`:
   ```php
   define('APP_DEBUG', true);
   ```
4. Проверьте `logs/error.log` на наличие ошибок
5. Перезапустите XAMPP

---

## ✅ Успешная регистрация

После успешной регистрации вы будете перенаправлены на `dashboard.php`

Для проверки что вы вошли:
- Откройте консоль (F12)
- Выполните: `console.log(Auth.isLoggedIn())`
- Должно вернуть `true`
