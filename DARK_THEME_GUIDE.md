# 🌙 Тёмная тема — Руководство по проверке

## ✅ Что было исправлено

### 1. CSS переменные для тёмной темы
Добавлены все необходимые переменные:
- `--bg-light`, `--bg-gray`, `--white` — фоны
- `--text-dark`, `--text-gray`, `--text-light` — текст
- `--border-light` — границы
- `--shadow-*` — тени

### 2. Стили для всех компонентов
- ✅ Navbar (тёмный фон)
- ✅ Карточки (feature-card, test-card, stat-box)
- ✅ Таблицы
- ✅ Формы (input, label, hint)
- ✅ Модальные окна
- ✅ Toast уведомления
- ✅ Footer
- ✅ Sidebar (в dashboard)
- ✅ Auth страницы (login, register)
- ✅ Profile страницы
- ✅ Hero секция (тёмный градиент)
- ✅ Достижения

### 3. JavaScript
- Добавлено логирование для отладки
- Исправлено переключение темы
- Сохранение темы в localStorage

---

## 🧪 Как проверить

### Способ 1: Через главную страницу
1. Откройте `http://localhost/test-platform/index.php`
2. Нажмите на иконку ☀️ в правом верхнем углу
3. Тема должна переключиться на тёмную 🌙
4. Нажмите ещё раз для возврата к светлой

### Способ 2: Через тестовую страницу
1. Откройте `http://localhost/test-platform/test-theme.html`
2. Вы увидите текущую тему и все CSS переменные
3. Переключите тему кнопкой ☀️/🌙
4. Проверьте что цвета изменились

### Способ 3: Через консоль браузера
1. Откройте консоль (F12)
2. Выполните: `Theme.toggle()`
3. Проверьте логи: `[THEME] Переключение с light на dark`
4. Или выполните: `document.documentElement.setAttribute('data-theme', 'dark')`

---

## 🎨 Ожидаемые цвета

### Светлая тема (light)
```
bg-gray:    #f8fafc
bg-light:   #f5f7f8
white:      #ffffff
text-dark:  #333333
text-gray:  #6b7280
border:     #e5e7eb
```

### Тёмная тема (dark)
```
bg-gray:    #1e293b
bg-light:   #0f172a
white:      #1e293b
text-dark:  #f1f5f9
text-gray:  #94a3b8
border:     #334155
```

---

## 🔍 Диагностика

### Тема не переключается?

1. **Проверьте localStorage:**
   ```javascript
   console.log(localStorage.getItem('theme'));
   ```
   Должно быть `'light'` или `'dark'`

2. **Проверьте атрибут:**
   ```javascript
   console.log(document.documentElement.getAttribute('data-theme'));
   ```
   Должно быть `'light'` или `'dark'`

3. **Очистите кэш:**
   - Ctrl + Shift + Del
   - Или выполните: `localStorage.clear()`

4. **Проверьте консоль:**
   - Откройте F12 → Console
   - Должны быть логи: `[THEME] Инициализация темы: dark`

### Цвета неправильные?

1. **Проверьте CSS переменные:**
   ```javascript
   const styles = getComputedStyle(document.documentElement);
   console.log('--bg-gray:', styles.getPropertyValue('--bg-gray'));
   console.log('--text-dark:', styles.getPropertyValue('--text-dark'));
   ```

2. **Проверьте что modern.css подключён:**
   - F12 → Network
   - Должен быть `modern.css` со статусом 200

---

## 📝 Примечания

- Тёмная тема сохраняется между сессиями
- При первом посещении используется светлая тема
- Hero секция в тёмной теме имеет более тёмный зелёный градиент
- Все интерактивные элементы сохраняют зелёные акценты

---

## 🐛 Если что-то не работает

1. Проверьте что файл `public/css/modern.css` существует
2. Проверьте что файл `public/js/app.js` существует
3. Очистите кэш браузера
4. Проверьте консоль на ошибки

### Сообщить о проблеме
Откройте `test-theme.html` и сделайте скриншот диагностики
