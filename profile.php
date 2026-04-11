<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title>Профиль — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=2">
  <style>
    /* === Profile top-bar === */
    body {
      overflow-x: hidden;
    }
    .profile-topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 64px;
      background: var(--white, #ffffff);
      border-bottom: 1px solid var(--border-light, #e2e8f0);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      z-index: 1000;
      box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    /* Left: Logo */
    .topbar-brand {
      display: flex;
      align-items: center;
      flex-shrink: 0;
    }
    .topbar-brand a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: inherit;
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .topbar-brand-text {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.15rem;
      color: var(--gradient-start, #00c853);
      max-width: 0;
      overflow: hidden;
      white-space: nowrap;
      opacity: 0;
      transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                  opacity 0.25s ease,
                  margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      margin-left: 0;
    }
    .topbar-brand a:hover .topbar-brand-text {
      max-width: 150px;
      opacity: 1;
      margin-left: 10px;
    }
    .topbar-logo {
      border-radius: 8px;
      flex-shrink: 0;
      transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .topbar-brand a:hover .topbar-logo {
      transform: translateX(-12px);
    }
    /* Center: spacer */
    .topbar-center {
      flex: 1;
    }

    /* Header hide/show on scroll */
    .profile-topbar {
      transition: transform 0.3s ease;
    }
    .profile-topbar.navbar-hidden {
      transform: translateY(-100%);
    }
    /* Right: Actions */
    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-shrink: 0;
    }
    .topbar-back {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: var(--radius-md, 12px);
      border: 1px solid var(--border-light, #e2e8f0);
      background: transparent;
      color: var(--text, #1e293b);
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s;
    }
    .topbar-back:hover {
      background: var(--bg-light, #f1f5f9);
      border-color: var(--gradient-start, #00c853);
      color: var(--gradient-start, #00c853);
    }
    .topbar-theme-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      border: 1px solid var(--border-light, #e2e8f0);
      background: transparent;
      color: var(--text-gray, #64748b);
      cursor: pointer;
      transition: all 0.2s;
    }
    .topbar-theme-btn:hover {
      background: var(--bg-light, #f1f5f9);
      color: var(--gradient-start, #00c853);
      border-color: var(--gradient-start, #00c853);
    }
    /* Profile content offset */
    .profile-content {
      padding-top: 64px;
    }

    /* Dark theme */
    [data-theme="dark"] .profile-topbar {
      background: #1e293b;
      border-bottom-color: #334155;
    }
    [data-theme="dark"] .topbar-username {
      color: #e2e8f0;
    }
    [data-theme="dark"] .topbar-dropdown {
      background: #1e293b;
      border-color: #334155;
    }
    [data-theme="dark"] .topbar-dropdown a {
      color: #e2e8f0;
    }
    [data-theme="dark"] .topbar-dropdown a:hover {
      background: #0f172a;
    }
    [data-theme="dark"] .topbar-dropdown a.logout {
      border-top-color: #334155;
    }
    [data-theme="dark"] .topbar-back {
      border-color: #334155;
      color: #e2e8f0;
    }
    [data-theme="dark"] .topbar-back:hover {
      background: #0f172a;
      border-color: var(--gradient-start, #00c853);
    }
    [data-theme="dark"] .topbar-theme-btn {
      border-color: #334155;
      color: #94a3b8;
    }
    [data-theme="dark"] .topbar-theme-btn:hover {
      background: #0f172a;
    }

    /* Mobile */
    @media (max-width: 768px) {
      .profile-topbar {
        padding: 0 12px;
      }
      .topbar-back span {
        display: none;
      }
      .topbar-back {
        padding: 8px 10px;
      }
    }
  </style>
</head>
<body>

<!-- TOP BAR -->
<header class="profile-topbar" id="topbar">
  <!-- Left: Brand -->
  <div class="topbar-brand">
    <a href="index.php">
      <img src="src/logogreen.png" alt="Sapienta logo" width="36" height="36" class="topbar-logo">
      <span class="topbar-brand-text">Sapienta</span>
    </a>
  </div>

  <!-- Center: spacer -->
  <div class="topbar-center"></div>

  <!-- Right: Actions -->
  <div class="topbar-actions">
    <a href="dashboard.php" class="topbar-back">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
      <span>Кабинет</span>
    </a>
    <button class="topbar-theme-btn" data-theme-toggle title="Тема">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
    </button>
  </div>
</header>

<div class="profile-content">
<div class="container" style="padding: 32px 20px; max-width: 1200px;">

  <!-- Header -->
  <div class="profile-header">
    <div class="profile-avatar-section">
      <img src="" alt="Avatar" class="profile-avatar-large" id="headerAvatar">
      <div class="profile-info">
        <h1 id="headerName">Загрузка...</h1>
        <div class="role-badge" id="headerRole"></div>
        <p class="text-muted mt-2" id="headerBio" style="opacity: 0.9;"></p>
      </div>
    </div>
    <div class="profile-stats">
      <div class="stat-card">
        <div class="value" id="statAttempts">0</div>
        <div class="label">Попыток</div>
      </div>
      <div class="stat-card">
        <div class="value" id="statPassed">0</div>
        <div class="label">Сдано тестов</div>
      </div>
      <div class="stat-card">
        <div class="value" id="statAvg">0%</div>
        <div class="label">Средний балл</div>
      </div>
      <div class="stat-card">
        <div class="value" id="statTime">0ч</div>
        <div class="label">Время в тестах</div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="profile-tabs">
    <button class="profile-tab active" onclick="switchTab('info')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a1.05 1.05 0 0 0 1.055 1.313l2.836-.708a.75.75 0 0 1 .852 1.063l-.02.041c-.293.585-.852.985-1.497 1.095l-2.777.47a2.25 2.25 0 0 1-2.12-1.066l-1.292-2.583a2.25 2.25 0 0 1 1.066-3.045l2.583-1.292a2.25 2.25 0 0 1 1.633.015Zm-4.5-4.5L6 6m4.5-4.5L12 3m4.5 4.5L18 9M6 6l-.75.75m10.5-.75L18 6m-12 0L6 7.5M6 6l.75.75m10.5-.75L18 7.5" /></svg> Информация</button>
    <button class="profile-tab" onclick="switchTab('security')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg> Безопасность</button>
    <button class="profile-tab" onclick="switchTab('avatar')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.311-10.311a1.125 1.125 0 1 1 1.591 1.591 1.125 1.125 0 0 1-1.591-1.591Z" /></svg> Аватарка</button>
    <button class="profile-tab" onclick="switchTab('achievements')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a1.125 1.125 0 0 0 1.125-1.125V5.625m-6.125 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v9.75m-6.125 0a1.125 1.125 0 0 1-1.125-1.125V5.625m0 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v1.5m-1.5-1.5h3m-3 0a1.125 1.125 0 0 0-1.125 1.125v1.5m4.5-1.5v1.5m-4.5 0h3" /></svg> Достижения</button>
    <button class="profile-tab" onclick="switchTab('activity')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg> Активность</button>
  </div>

  <!-- Tab: Info -->
  <div id="tab-info" class="profile-section">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg> Личная информация</div>
    <form id="profileForm">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Имя</label>
          <input class="form-control" id="firstName" placeholder="Иван">
        </div>
        <div class="form-group">
          <label class="form-label">Фамилия</label>
          <input class="form-control" id="lastName" placeholder="Иванов">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">О себе</label>
        <textarea class="form-control" id="bio" rows="3" placeholder="Расскажите немного о себе..."></textarea>
        <div class="form-hint">Максимум 500 символов</div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Телефон</label>
          <input class="form-control" id="phone" placeholder="+7 (999) 000-00-00">
        </div>
        <div class="form-group">
          <label class="form-label">Город</label>
          <input class="form-control" id="city" placeholder="Москва">
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Веб-сайт</label>
          <input class="form-control" id="website" placeholder="https://mysite.com">
        </div>
        <div class="form-group">
          <label class="form-label">Дата рождения</label>
          <input class="form-control" type="date" id="birthDate">
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">VK профиль</label>
          <input class="form-control" id="socialVk" placeholder="https://vk.com/username">
        </div>
        <div class="form-group">
          <label class="form-label">Telegram</label>
          <input class="form-control" id="socialTg" placeholder="@username">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" id="saveProfileBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> Сохранить</button>
    </form>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #e2e8f0;">

    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg> Контактная информация</div>
    <form id="contactForm">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Имя пользователя</label>
          <input class="form-control" id="username" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" id="email" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" id="saveContactBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> Сохранить</button>
    </form>
  </div>

  <!-- Tab: Security -->
  <div id="tab-security" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg> Смена пароля</div>
    <form id="passwordForm" style="max-width: 500px;">
      <div class="form-group">
        <label class="form-label">Текущий пароль</label>
        <input class="form-control" type="password" id="currentPassword" required>
      </div>
      <div class="form-group">
        <label class="form-label">Новый пароль</label>
        <input class="form-control" type="password" id="newPassword" required>
        <div class="form-hint">Минимум 8 символов</div>
      </div>
      <div class="form-group">
        <label class="form-label">Подтверждение пароля</label>
        <input class="form-control" type="password" id="confirmPassword" required>
      </div>
      <button type="submit" class="btn btn-primary" id="changePasswordBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg> Изменить пароль</button>
    </form>
  </div>

  <!-- Tab: Avatar -->
  <div id="tab-avatar" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.311-10.311a1.125 1.125 0 1 1 1.591 1.591 1.125 1.125 0 0 1-1.591-1.591Z" /></svg> Управление аватаркой</div>
    <div class="avatar-upload">
      <img src="" alt="Avatar" class="avatar-preview" id="avatarPreview">
      <div class="avatar-actions">
        <button class="btn btn-primary" onclick="document.getElementById('avatarInput').click()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg> Выбрать файл</button>
        <button class="btn btn-danger" id="removeAvatarBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg> Удалить</button>
        <input type="file" id="avatarInput" class="hidden-input" accept="image/*">
        <div class="form-hint">JPG, PNG, GIF, WebP. Макс. 5MB</div>
      </div>
    </div>
    <div id="avatarProgress" class="hidden" style="margin-top: 16px;">
      <div class="spinner"></div>
      <span class="text-muted" style="margin-left: 12px;">Загрузка...</span>
    </div>
  </div>

  <!-- Tab: Achievements -->
  <div id="tab-achievements" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a1.125 1.125 0 0 0 1.125-1.125V5.625m-6.125 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v9.75m-6.125 0a1.125 1.125 0 0 1-1.125-1.125V5.625m0 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v1.5m-1.5-1.5h3m-3 0a1.125 1.125 0 0 0-1.125 1.125v1.5m4.5-1.5v1.5m-4.5 0h3" /></svg> Ваши достижения</div>
    <div class="achievements-grid" id="achievementsGrid"></div>
  </div>

  <!-- Tab: Activity -->
  <div id="tab-activity" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg> Активность за <span id="activityDays">30</span> дней</div>
    <div class="activity-heatmap" id="activityHeatmap"></div>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #e2e8f0;">

    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg> Последние результаты</div>
    <table class="results-table">
      <thead>
        <tr>
          <th>Тест</th>
          <th>Попытка</th>
          <th>Баллы</th>
          <th>%</th>
          <th>Статус</th>
          <th>Дата</th>
        </tr>
      </thead>
      <tbody id="recentResults"></tbody>
    </table>
  </div>

</div>

<div class="NotificationToast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js"></script>
<script>
  if (!AuthManager.isLoggedIn()) {
    window.location.href = 'login.php?redirect=' + encodeURIComponent(location.href);
  }

  let profile = {};
  let currentTab = 'info';

  // Load profile on page load
  loadProfile();

  async function loadProfile() {
    try {
      const res = await API.get('/profile.php?action=get');
      if (!res.success) throw new Error(res.message);

      profile = res.profile;
      const stats = res.statistics;
      const achievements = res.achievements;
      const recentResults = res.recent_results;

      // Header
      const avatarUrl = profile.avatar || 'public/img/default-avatar.svg';
      document.getElementById('headerAvatar').src = avatarUrl.startsWith('http') ? avatarUrl : (window.APP_URL || '') + '/' + avatarUrl;
      document.getElementById('headerName').textContent = (profile.first_name || profile.last_name) 
        ? `${profile.first_name || ''} ${profile.last_name || ''}`.trim() || profile.username 
        : profile.username;
      document.getElementById('headerRole').textContent = profile.role === 'admin' ? 'Администратор' : 'Студент';
      document.getElementById('headerBio').textContent = profile.bio || '';

      // Stats
      document.getElementById('statAttempts').textContent = stats.total_attempts;
      document.getElementById('statPassed').textContent = stats.passed_tests;
      document.getElementById('statAvg').textContent = Math.round(parseFloat(stats.avg_percentage)) + '%';
      document.getElementById('statTime').textContent = Math.round(stats.total_time_seconds / 3600) + 'ч';

      // Profile form
      document.getElementById('firstName').value = profile.first_name || '';
      document.getElementById('lastName').value = profile.last_name || '';
      document.getElementById('bio').value = profile.bio || '';
      document.getElementById('phone').value = profile.phone || '';
      document.getElementById('city').value = profile.city || '';
      document.getElementById('website').value = profile.website || '';
      document.getElementById('birthDate').value = profile.birth_date || '';
      document.getElementById('socialVk').value = profile.social_vk || '';
      document.getElementById('socialTg').value = profile.social_tg || '';

      // Contact form
      document.getElementById('username').value = profile.username;
      document.getElementById('email').value = profile.email;

      // Avatar preview
      document.getElementById('avatarPreview').src = avatarUrl.startsWith('http') ? avatarUrl : (window.APP_URL || '') + '/' + avatarUrl;

      // Achievements
      renderAchievements(achievements);

      // Recent results
      renderRecentResults(recentResults);

      // Load activity
      loadActivity(30);

    } catch (err) {
      NotificationToast.error(err.message);
    }
  }

  function renderAchievements(achievements) {
    const grid = document.getElementById('achievementsGrid');
    grid.innerHTML = achievements.map(a => `
      <div class="achievement-card ${a.unlocked ? '' : 'locked'}">
        <div class="achievement-icon">${a.icon}</div>
        <div class="achievement-name">${a.name}</div>
        <div class="achievement-desc">${a.description}</div>
      </div>
    `).join('');
  }

  function renderRecentResults(results) {
    const tbody = document.getElementById('recentResults');
    if (!results || results.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Пока нет результатов</td></tr>';
      return;
    }
    tbody.innerHTML = results.map(r => `
      <tr>
        <td><strong>${escapeHtml(r.test_title)}</strong></td>
        <td>#${r.attempt_number}</td>
        <td>${r.score}/${r.max_score}</td>
        <td><strong>${parseFloat(r.percentage).toFixed(1)}%</strong></td>
        <td><span class="badge ${r.passed ? 'badge-pass' : 'badge-fail'}">${r.passed ? 'Сдан' : 'Нет'}</span></td>
        <td class="text-muted" style="font-size: 0.85rem;">${new Date(r.created_at).toLocaleDateString('ru')}</td>
      </tr>
    `).join('');
  }

  async function loadActivity(days) {
    try {
      const res = await API.get('/profile.php?action=activity&days=' + days);
      if (!res.success) return;

      const activity = res.activity;
      const heatmap = document.getElementById('activityHeatmap');
      
      // Generate 30 days heatmap
      let html = '';
      const today = new Date();
      for (let i = days - 1; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        const count = activity[dateStr] || 0;
        const level = count === 0 ? '' : count === 1 ? 'level-1' : count === 2 ? 'level-2' : count <= 4 ? 'level-3' : 'level-4';
        html += `<div class="activity-day ${level}" title="${dateStr}: ${count} тестов"></div>`;
      }
      heatmap.innerHTML = html;
    } catch (err) {
      console.error(err);
    }
  }

  // Tab switching
  function switchTab(tab) {
    document.querySelectorAll('.profile-tab').forEach(b => b.classList.toggle('active', b.textContent.toLowerCase().includes(tab === 'info' ? 'информация' : tab === 'security' ? 'безопасность' : tab === 'avatar' ? 'аватар' : tab === 'achievements' ? 'достижения' : 'активность')));
    document.querySelectorAll('[id^="tab-"]').forEach(s => s.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    currentTab = tab;
  }

  // Save profile
  document.getElementById('profileForm').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('saveProfileBtn');
    setLoading(btn, true);
    try {
      await API.post('/profile.php?action=update', {
        bio: document.getElementById('bio').value,
        phone: document.getElementById('phone').value,
        city: document.getElementById('city').value,
        website: document.getElementById('website').value,
        birth_date: document.getElementById('birthDate').value,
        social_vk: document.getElementById('socialVk').value,
        social_tg: document.getElementById('socialTg').value,
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        csrf_token: localStorage.getItem('csrf_token')
      });
      NotificationToast.success('Профиль обновлён');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  });

  // Save contact info
  document.getElementById('contactForm').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('saveContactBtn');
    setLoading(btn, true);
    try {
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();

      if (username !== profile.username) {
        await API.post('/profile.php?action=change_username', {
          username,
          csrf_token: localStorage.getItem('csrf_token')
        });
      }
      if (email !== profile.email) {
        await API.post('/profile.php?action=change_email', {
          email,
          csrf_token: localStorage.getItem('csrf_token')
        });
      }
      NotificationToast.success('Данные обновлены');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  });

  // Change password
  document.getElementById('passwordForm').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('changePasswordBtn');
    setLoading(btn, true);
    try {
      const newPwd = document.getElementById('newPassword').value;
      const confirm = document.getElementById('confirmPassword').value;
      
      if (newPwd !== confirm) {
        throw new Error('Пароли не совпадают');
      }

      await API.post('/profile.php?action=change_password', {
        current_password: document.getElementById('currentPassword').value,
        new_password: newPwd,
        confirm_password: confirm,
        csrf_token: localStorage.getItem('csrf_token')
      });
      NotificationToast.success('Пароль изменён');
      e.target.reset();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  });

  // Upload avatar
  document.getElementById('avatarInput').addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('csrf_token', localStorage.getItem('csrf_token'));

    document.getElementById('avatarProgress').classList.remove('hidden');

    try {
      const response = await fetch((window.APP_URL || '') + '/api/profile.php?action=upload_avatar', {
        method: 'POST',
        body: formData,
        credentials: 'include',
      });
      const data = await response.json();
      if (!data.success) throw new Error(data.message);
      
      NotificationToast.success('Аватарка загружена');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      document.getElementById('avatarProgress').classList.add('hidden');
      e.target.value = '';
    }
  });

  // Remove avatar
  document.getElementById('removeAvatarBtn').addEventListener('click', async () => {
    if (!confirm('Удалить аватарку?')) return;
    try {
      await API.post('/profile.php?action=remove_avatar', {
        csrf_token: localStorage.getItem('csrf_token')
      });
      NotificationToast.success('Аватарка удалена');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    }
  });

  function escapeHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  // Topbar hide on scroll down, show on scroll up
  let lastScrollY = window.scrollY;
  const topbar = document.getElementById('topbar');

  window.addEventListener('scroll', () => {
    if (!topbar) return;
    const currentY = window.scrollY;
    if (currentY > lastScrollY && currentY > 100) {
      topbar.classList.add('navbar-hidden');
    } else {
      topbar.classList.remove('navbar-hidden');
    }
    lastScrollY = currentY;
  });
</script>
</div><!-- /profile-content wrapper -->
</body>
</html>
