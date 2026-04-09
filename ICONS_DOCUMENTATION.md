# 🎨 FontAwesome Icons — Документация

## ✅ Иконки добавлены на все страницы

### Подключение
FontAwesome 6.5.1 подключён через CDN (cdnjs):
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
```

---

## 📝 Использованные иконки

### Navbar
| Иконка | Класс | Использование |
|--------|-------|---------------|
| 🎓 | `fas fa-graduation-cap` | Логотип |
| 🏠 | `fas fa-home` | Главная |
| ⭐ | `fas fa-star` | Возможности |
| ⚙️ | `fas fa-cog` | Как это работает |
| 📋 | `fas fa-clipboard-list` | Тесты |
| 🔐 | `fas fa-sign-in-alt` | Войти |
| 👤 | `fas fa-user-plus` | Регистрация |
| 📊 | `fas fa-th-large` | Кабинет |
| 👤 | `fas fa-user` | Профиль |
| 🛡️ | `fas fa-shield-alt` | Админ |
| 🚪 | `fas fa-sign-out-alt` | Выйти |
| ☀️/🌙 | `fas fa-sun` / `fas fa-moon` | Тема |

### Hero секция
| Иконка | Класс | Использование |
|--------|-------|---------------|
| 🚀 | `fas fa-rocket` | Кнопка "Начать" |
| 📋 | `fas fa-clipboard-list` | Мои тесты |
| 📝 | `fas fa-tasks` | Смотреть тесты |
| 🎓 | `fas fa-user-graduate` | Студентов |
| 📄 | `fas fa-file-alt` | Тестов |
| 😊 | `fas fa-smile` | Довольных |

### Features
| Иконка | Класс | Использование |
|--------|-------|---------------|
| 🛡️ | `fas fa-shield-alt` | Анти-читинг |
| 🔀 | `fas fa-random` | Случайные вопросы |
| ⏱️ | `fas fa-stopwatch` | Таймер |
| 📊 | `fas fa-chart-bar` | Статистика |
| 🎛️ | `fas fa-sliders-h` | Настройки |
| 📱 | `fas fa-mobile-alt` | Адаптивность |

### How It Works
| Иконка | Класс | Использование |
|--------|-------|---------------|
| 👤 | `fas fa-user-plus` | Регистрация |
| 🔍 | `fas fa-search` | Выбрать тест |
| ✏️ | `fas fa-pencil-alt` | Пройти тест |
| 🏆 | `fas fa-trophy` | Результат |

### Tests
| Иконка | Класс | Использование |
|--------|-------|---------------|
| 🧮 | `fas fa-calculator` | Математика |
| 🧪 | `fas fa-flask` | Информатика |
| 📖 | `fas fa-book-open` | Русский язык |
| ⏰ | `fas fa-clock` | Время |
| ❓ | `fas fa-question-circle` | Вопросы |
| 🔄 | `fas fa-redo` | Попытки |

### Footer
| Иконка | Класс | Использование |
|--------|-------|---------------|
| 🎓 | `fas fa-graduation-cap` | Логотип |
| ✈️ | `fab fa-telegram-plane` | Telegram |
| VK | `fab fa-vk` | ВКонтакте |
| 📧 | `fas fa-envelope` | Email |
| ➡️ | `fas fa-angle-right` | Ссылки |

---

## 🎨 Стилизация иконок

### В кнопках и ссылках
```css
.btn i, a i {
  margin-right: 8px;
}
```

### В navbar
```css
.navbar-nav a i {
  margin-right: 6px;
  font-size: 0.9em;
}
```

### В feature карточках
```css
.feature-icon i {
  font-size: 2.5rem;
  color: #fff;
}
```

### В test карточках
```css
.test-card-icon i {
  font-size: 2rem;
  color: var(--gradient-start);
}
```

### Переключатель темы
```css
.theme-toggle i {
  font-size: 1.2rem;
}

/* Светлая тема */
.theme-toggle i {
  color: #f59e0b; /* Оранжевый */
}

/* Тёмная тема */
[data-theme="dark"] .theme-toggle i {
  color: #60a5fa; /* Синий */
}
```

---

## 🔧 Использование

### В HTML
```html
<!-- Иконка в тексте -->
<i class="fas fa-home"></i>

<!-- Иконка в кнопке -->
<button class="btn btn-primary">
  <i class="fas fa-save"></i> Сохранить
</button>

<!-- Иконка в ссылке -->
<a href="#">
  <i class="fas fa-user"></i> Профиль
</a>
```

### В JavaScript
```javascript
// Смена иконки
icon.className = 'fas fa-moon';

// Добавление иконки
element.innerHTML = '<i class="fas fa-check"></i> Готово';
```

---

## 📚 Категории иконок FontAwesome

### Solid (fas)
- Базовые иконки
- Наиболее используемые
- Полный набор

### Brands (fab)
- Логотипы брендов
- Соцсети (VK, Telegram, GitHub)
- Компании

### Regular (far)
- Контурные иконки
- Менее насыщенные
- Для второстепенных элементов

---

## 🎯 Популярные иконки для сайта

### Навигация
```html
<i class="fas fa-home"></i>        <!-- Главная -->
<i class="fas fa-bars"></i>        <!-- Меню -->
<i class="fas fa-search"></i>      <!-- Поиск -->
<i class="fas fa-user"></i>        <!-- Профиль -->
<i class="fas fa-cog"></i>         <!-- Настройки -->
```

### Действия
```html
<i class="fas fa-edit"></i>        <!-- Редактировать -->
<i class="fas fa-trash"></i>       <!-- Удалить -->
<i class="fas fa-save"></i>        <!-- Сохранить -->
<i class="fas fa-plus"></i>        <!-- Добавить -->
<i class="fas fa-check"></i>       <!-- Подтвердить -->
```

### Статусы
```html
<i class="fas fa-check-circle"></i> <!-- Успех -->
<i class="fas fa-exclamation-triangle"></i> <!-- Предупреждение -->
<i class="fas fa-times-circle"></i> <!-- Ошибка -->
<i class="fas fa-info-circle"></i>  <!-- Информация -->
```

### Временные
```html
<i class="fas fa-clock"></i>       <!-- Время -->
<i class="fas fa-calendar"></i>    <!-- Календарь -->
<i class="fas fa-history"></i>     <!-- История -->
<i class="fas fa-stopwatch"></i>   <!-- Таймер -->
```

---

## 🌐 CDN ссылки

### FontAwesome 6.5.1 (используется)
```html
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
```

### Альтернативные CDN
```html
<!-- jsDelivr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">

<!-- Unpkg -->
<link rel="stylesheet" href="https://unpkg.com/font-awesome@6.5.1/css/all.min.css">
```

---

## 📊 Поиск иконок

Полный каталог иконок: https://fontawesome.com/icons

### Поиск по названию:
- home, user, settings, search
- check, times, exclamation
- calendar, clock, timer
- arrow, chevron, angle

### Поиск по категории:
- Web Application
- Accessibility
- Animals
- Charts
- Files
- Brands

---

## ✅ Проверка работы

1. Откройте любую страницу
2. Проверьте что все иконки отображаются
3. Проверьте переключение темы (солнце/луна)
4. Проверьте что иконки имеют правильные цвета

---

## 🐛 Возможные проблемы

### Иконки не отображаются?

1. **Проверьте подключение CDN:**
   ```html
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   ```

2. **Проверьте консоль (F12):**
   - Нет ли ошибок сети
   - Нет ли CORS ошибок

3. **Проверьте классы:**
   ```html
   <!-- Правильно -->
   <i class="fas fa-home"></i>
   
   <!-- Неправильно -->
   <i class="fa-home"></i>
   ```

### Иконки неправильного размера?

Проверьте CSS:
```css
.feature-icon i {
  font-size: 2.5rem;
}
```

### Иконки не того цвета?

Проверьте наследование цвета:
```css
.feature-icon {
  color: #fff; /* Белый для иконок */
}
```

---

## 🎉 Готово!

Все страницы используют профессиональные иконки FontAwesome вместо эмодзи.

### Преимущества:
- ✅ Единый стиль на всех страницах
- ✅ Векторное качество (масштабируются без потерь)
- ✅ Быстрая загрузка через CDN
- ✅ Огромный выбор иконок (5000+)
- ✅ Поддержка всех браузеров
- ✅ Работает с тёмной темой
