# 🌐 Мультиязычность (i18n) — Руководство

## ✅ Реализованные языки

- 🇷🇺 **Русский** (ru) — по умолчанию
- 🇰🇿 **Қазақша** (kk) — Казахский
- 🇬🇧 **English** (en) — Английский

---

## 📋 Как это работает

### 1. Переключатель языка

В navbar добавлен селектор языка:
```html
<div class="lang-selector">
  <select data-language-selector aria-label="Выбор языка"></select>
</div>
```

### 2. Перевод текстов

Используйте атрибут `data-i18n` для перевода текстов:

```html
<h1 data-i18n="hero.title">Заголовок</h1>
<p data-i18n="hero.description">Описание</p>
<button data-i18n="common.save">Сохранить</button>
```

### 3. Перевод placeholder'ов

Для input полей используйте `data-i18n-placeholder`:

```html
<input type="text" data-i18n-placeholder="auth.login.label" placeholder="Email или логин">
```

---

## 🔧 Использование в JavaScript

### Получить перевод
```javascript
const text = i18n.t('hero.title');
// или
const text = __('hero.title');
```

### Переключить язык
```javascript
i18n.toggle(); // Переключить на следующий
i18n.apply('en'); // Применить конкретный язык
```

### Получить текущий язык
```javascript
const lang = i18n.getCurrentLang(); // 'ru', 'kk', или 'en'
const name = i18n.getCurrentLangName(); // 'Русский', 'Қазақша', 'English'
```

---

## 📝 Добавление новых переводов

### 1. Откройте `public/js/i18n.js`

### 2. Добавьте ключ в словарь

```javascript
translations: {
  ru: {
    'my.new.key': 'Мой новый перевод',
    // ...
  },
  kk: {
    'my.new.key': 'Менің жаңа аудармам',
    // ...
  },
  en: {
    'my.new.key': 'My new translation',
    // ...
  }
}
```

### 3. Используйте в HTML

```html
<span data-i18n="my.new.key">Текст по умолчанию</span>
```

---

## 🎨 Стилизация переключателя

Переключатель языка автоматически скрывается на мобильных устройствах и отображается в меню.

### CSS классы:
- `.lang-selector` — десктопная версия
- `.lang-selector-mobile` — мобильная версия

---

## 🧪 Тестирование

### 1. Откройте главную страницу
```
http://localhost/test-platform/index.php
```

### 2. Выберите язык в dropdown
- 🇷🇺 Русский
- 🇰🇿 Қазақша
- 🇬🇧 English

### 3. Проверьте что:
- ✅ Все тексты перевелись
- ✅ Язык сохранился в localStorage
- ✅ При перезагрузке язык сохраняется

---

## 🐛 Возможные проблемы

### Тексты не переводятся?

1. **Проверьте ключи:**
   ```javascript
   console.log(i18n.t('hero.title'));
   ```

2. **Проверьте атрибуты:**
   ```html
   <h1 data-i18n="hero.title">...</h1>
   ```

3. **Перезагрузите страницу:**
   ```javascript
   i18n.translatePage();
   ```

### Селектор не работает?

1. Проверьте что `i18n.js` подключён:
   ```html
   <script src="public/js/i18n.js"></script>
   ```

2. Проверьте консоль на ошибки (F12)

3. Очистите localStorage:
   ```javascript
   localStorage.clear();
   ```

---

## 📊 Структура ключей

```
nav.*          — Навигация
hero.*         — Hero секция
features.*     — Преимущества
how.*          — Как это работает
tests.*        — Тесты
cta.*          — CTA секция
footer.*       — Footer
auth.*         — Авторизация
dashboard.*    — Кабинет
table.*        — Таблицы
common.*       — Общее
```

---

## 💡 Советы

1. **Всегда указывайте текст по умолчанию** в HTML:
   ```html
   <span data-i18n="key">Текст на русском</span>
   ```

2. **Используйте понятные ключи**:
   ```javascript
   // ✅ Хорошо
   'auth.login.submit'
   
   // ❌ Плохо
   'btn1', 'text_123'
   ```

3. **Переводите все языки сразу** при добавлении нового ключа

4. **Тестируйте на всех языках** перед деплоем

---

## 🔗 API

| Метод | Описание |
|-------|----------|
| `i18n.init()` | Инициализация (вызывается автоматически) |
| `i18n.apply(lang)` | Применить язык |
| `i18n.toggle()` | Переключить на следующий |
| `i18n.t(key)` | Получить перевод |
| `i18n.getCurrentLang()` | Текущий язык (код) |
| `i18n.getCurrentLangName()` | Название языка |
| `i18n.translatePage()` | Перевести страницу |

---

## 📱 Мобильные устройства

На мобильных переключатель языка скрыт в burger-меню.

---

## 🎯 Примеры использования

### В HTML:
```html
<!-- Простой текст -->
<h2 data-i18n="features.title">Заголовок</h2>

<!-- С HTML внутри -->
<p data-i18n="hero.description">Описание с <br> переносом</p>

<!-- Placeholder -->
<input data-i18n-placeholder="auth.login.label" placeholder="...">

<!-- Кнопка -->
<button class="btn" data-i18n="common.save">Сохранить</button>
```

### В JavaScript:
```javascript
// Перевод в alert
alert(__('common.error'));

// Динамический контент
element.textContent = i18n.t('dashboard.welcome');

// С переменной
const name = 'John';
element.innerHTML = `${__('dashboard.welcome')}, ${name}!`;
```

---

## ✅ Чеклист перед деплоем

- [ ] Все тексты на странице имеют `data-i18n` атрибуты
- [ ] Все ключи переведены на 3 языка
- [ ] Переключатель языка работает
- [ ] Язык сохраняется в localStorage
- [ ] На мобильных переключатель в меню
- [ ] Placeholder'ы переведены
- [ ] Тесты проходят на всех языках
