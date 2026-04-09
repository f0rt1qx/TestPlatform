# 🎨 Design System — TestPlatform

## Обзор

Современный, привлекательный дизайн платформы с использованием градиентов, анимаций и микро-взаимодействий.

## 🎨 Цветовая палитра

### Основные цвета
```css
--primary: #4f46e5 (Индиго)
--primary-light: #818cf8
--primary-dark: #3730a3
--primary-glow: rgba(79, 70, 229, 0.3)

--accent: #ec4899 (Розовый)
--accent-light: #f472b6
```

### Семантические цвета
```css
--success: #10b981 (Зелёный)
--danger: #ef4444 (Красный)
--warning: #f59e0b (Оранжевый)
--info: #3b82f6 (Синий)
```

### Фоны
```css
--bg: #f8fafc (Светлый фон)
--bg-card: #ffffff (Карточки)
--bg-input: #f1f5f9 (Поля ввода)
--bg-gradient: linear-gradient(135deg, #667eea, #764ba2, #f093fb)
```

## 📐 Радиусы

```css
--radius-sm: 8px (Маленькие элементы)
--radius: 12px (Стандартный)
--radius-lg: 16px (Карточки)
--radius-xl: 24px (Модальные окна)
--radius-full: 9999px (Круглые кнопки)
```

## ✨ Тени

```css
--shadow-sm: 0 1px 2px rgba(0,0,0,0.05)
--shadow: 0 4px 6px rgba(0,0,0,0.1)
--shadow-md: 0 10px 15px rgba(0,0,0,0.1)
--shadow-lg: 0 20px 25px rgba(0,0,0,0.1)
--shadow-xl: 0 25px 50px rgba(0,0,0,0.25)
--shadow-glow: 0 0 40px rgba(79, 70, 229, 0.15)
```

## 🎭 Анимации

### Основные
- `fadeIn` — плавное появление
- `slideUp` — выезд снизу
- `scaleIn` — увеличение
- `pulse` — пульсация
- `shimmer` — мерцание (скелетоны)

### Длительности
```css
--transition-fast: 0.15s
--transition: 0.2s
--transition-slow: 0.3s
--transition-bounce: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55)
```

## 🧩 Компоненты

### Кнопки

#### Primary (Основная)
```html
<button class="btn btn-primary btn-lg">Начать</button>
```
- Градиентный фон
- Свечение при наведении
- Подъём на 2px

#### Secondary (Акцентная)
```html
<button class="btn btn-secondary">Действие</button>
```
- Розово-фиолетовый градиент

#### Outline (Контурная)
```html
<button class="btn btn-outline">Отмена</button>
```
- Прозрачный фон
- Цветная рамка

### Карточки

```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">Заголовок</h3>
    <p class="card-subtitle">Подзаголовок</p>
  </div>
  <div class="card-body">
    Содержимое
  </div>
</div>
```

**Эффекты:**
- Подъём при наведении
- Увеличение тени
- Плавные переходы

### Формы

```html
<div class="form-group">
  <label class="form-label">Название</label>
  <input class="form-control" placeholder="Введите...">
  <div class="form-hint">Подсказка</div>
</div>
```

**Состояния:**
- Focus: синяя рамка + свечение
- Hover: подсветка рамки
- Error: красная рамка

### Бейджи

```html
<span class="badge badge-success">Сдан</span>
<span class="badge badge-danger">Нет</span>
<span class="badge badge-warning">В процессе</span>
```

## 📱 Адаптивность

### Breakpoints
- Mobile: < 480px
- Tablet: < 768px
- Desktop: ≥ 768px

### Mobile особенности
- Бургер-меню
- Вертикальные кнопки
- Скрытая боковая панель
- Компактные карточки

## 🌙 Тёмная тема

Автоматически переключается через кнопку в навбаре.

**Изменения:**
- Тёмные фоны (#0f172a, #1e293b)
- Светлый текст (#f1f5f9)
- Увеличенные тени
- Более яркие акценты

## 🎯 Hero секция

```html
<section class="hero">
  <h1>Заголовок</h1>
  <p>Описание</p>
  <div class="hero-btns">
    <a class="btn btn-primary">Действие 1</a>
    <a class="btn btn-outline">Действие 2</a>
  </div>
</section>
```

**Особенности:**
- Градиентный фон
- Анимация появления
- Паттерн на фоне
- Статистика внизу

## 📊 Статистика

```html
<div class="stats-row">
  <div class="stat-box">
    <div class="stat-value">100</div>
    <div class="stat-label">Тестов</div>
  </div>
</div>
```

**Эффекты:**
- Градиентный текст
- Подъём при наведении
- Адаптивная сетка

## 🔔 Уведомления (Toast)

```javascript
Toast.success('Успешно!');
Toast.error('Ошибка!');
Toast.warning('Предупреждение');
Toast.info('Информация');
```

**Стили:**
- Цветная полоска слева
- Иконка
- Автоматическое исчезновение
- Анимация появления

## 📝 Таблицы

```html
<div class="table-wrap">
  <table>
    <thead>...</thead>
    <tbody>...</tbody>
  </table>
</div>
```

**Особенности:**
- Закруглённые углы
- Тень
- Подсветка строк при наведении
- Горизонтальный скролл на мобильных

## 🎯 Лучшие практики

### 1. Отступы
Используйте утилитарные классы:
- `mt-4` — большой отступ сверху
- `mb-3` — средний снизу
- `gap-2` — промежуток между flex-элементами

### 2. Типографика
- `text-muted` — вторичный текст
- `text-center` — по центру
- `text-xl` — крупный текст

### 3. Flexbox
```html
<div class="flex flex-between gap-2">
  <div>Слева</div>
  <div>Справа</div>
</div>
```

### 4. Скрытие элементов
- `hidden` — полное скрытие
- `data-user` / `data-guest` — по авторизации

## 🚀 Производительность

### Оптимизации
- CSS variables для тем
- Backdrop-filter для стекла
- Transform вместо position
- Will-change для анимаций

### Рекомендации
- Не более 3 анимаций одновременно
- Использовать CSS анимации вместо JS
- Ленивая загрузка изображений

## 📱 Тестирование

Проверьте на:
- Chrome (Desktop/Mobile)
- Safari (iOS/Mac)
- Firefox
- Edge

**Минимальные разрешения:**
- 320px (iPhone SE)
- 768px (iPad)
- 1920px (Desktop)

---

**Создано для TestPlatform** | 2024
