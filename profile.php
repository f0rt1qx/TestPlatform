<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Профиль — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="container">
    <a href="index.php" class="navbar-brand">
      <img src="src/logo.png" alt="Sapienta logo" width="48" height="48" class="navbar-logo"> Sapienta
    </a>
    <ul class="navbar-nav" id="mainNav">
      <li><a href="dashboard.php">Кабинет</a></li>
      <li><a href="profile.php" class="active">Профиль</a></li>
      <li data-admin class="hidden"><a href="admin.php">Админ</a></li>
      <li><a href="#" onclick="AuthManager.logout()">Выйти</a></li>
      <li>
        <div class="lang-selector">
          <select data-language-selector aria-label="Выбор языка"></select>
        </div>
      </li>
      <li><button class="theme-toggle" data-theme-toggle title="Тема"><img src="https://img.icons8.com/ios/18/crescent-moon.png" alt="" width="18" height="18"></button></li>
    </ul>
    <button class="burger" id="burgerBtn">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

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
    <button class="profile-tab active" onclick="switchTab('info')"><img src="https://img.icons8.com/fluency-systems-regular/48/info-squared.png" alt="" width="18" height="18"> Информация</button>
    <button class="profile-tab" onclick="switchTab('security')"><img src="https://img.icons8.com/ios/18/lock--v1.png" alt="" width="18" height="18"> Безопасность</button>
    <button class="profile-tab" onclick="switchTab('avatar')"><img src="https://img.icons8.com/ios/18/image.png" alt="" width="18" height="18"> Аватарка</button>
    <button class="profile-tab" onclick="switchTab('achievements')"><img src="https://img.icons8.com/ios/18/trophy--v1.png" alt="" width="18" height="18"> Достижения</button>
    <button class="profile-tab" onclick="switchTab('activity')"><img src="https://img.icons8.com/glyph-neue/64/activity.png" alt="" width="18" height="18"> Активность</button>
  </div>

  <!-- Tab: Info -->
  <div id="tab-info" class="profile-section">
    <div class="section-title"><img src="https://img.icons8.com/fluency-systems-regular/48/password-book.png" alt="" width="18" height="18"> Личная информация</div>
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
      <button type="submit" class="btn btn-primary" id="saveProfileBtn"><img src="https://img.icons8.com/ios/18/save.png" alt="" width="18" height="18"> Сохранить</button>
    </form>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #e2e8f0;">

    <div class="section-title"><img src="https://img.icons8.com/ios/18/email.png" alt="" width="18" height="18"> Контактная информация</div>
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
      <button type="submit" class="btn btn-primary" id="saveContactBtn"><img src="https://img.icons8.com/ios/18/save.png" alt="" width="18" height="18"> Сохранить</button>
    </form>
  </div>

  <!-- Tab: Security -->
  <div id="tab-security" class="profile-section hidden">
    <div class="section-title"><img src="https://img.icons8.com/ios/18/key--v1.png" alt="" width="18" height="18"> Смена пароля</div>
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
      <button type="submit" class="btn btn-primary" id="changePasswordBtn"><img src="https://img.icons8.com/ios/18/key--v1.png" alt="" width="18" height="18"> Изменить пароль</button>
    </form>
  </div>

  <!-- Tab: Avatar -->
  <div id="tab-avatar" class="profile-section hidden">
    <div class="section-title"><img src="https://img.icons8.com/ios/18/image.png" alt="" width="18" height="18"> Управление аватаркой</div>
    <div class="avatar-upload">
      <img src="" alt="Avatar" class="avatar-preview" id="avatarPreview">
      <div class="avatar-actions">
        <button class="btn btn-primary" onclick="document.getElementById('avatarInput').click()">📁 Выбрать файл</button>
        <button class="btn btn-danger" id="removeAvatarBtn">🗑️ Удалить</button>
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
    <div class="section-title"><img src="https://img.icons8.com/ios/18/trophy--v1.png" alt="" width="18" height="18"> Ваши достижения</div>
    <div class="achievements-grid" id="achievementsGrid"></div>
  </div>

  <!-- Tab: Activity -->
  <div id="tab-activity" class="profile-section hidden">
    <div class="section-title"><img src="https://img.icons8.com/ios/18/increasing-chart.png" alt="" width="18" height="18"> Активность за <span id="activityDays">30</span> дней</div>
    <div class="activity-heatmap" id="activityHeatmap"></div>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #e2e8f0;">

    <div class="section-title"><img src="https://img.icons8.com/ios/18/list.png" alt="" width="18" height="18"> Последние результаты</div>
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

  // Navbar hide on scroll down, show on scroll up
  let lastScrollY = window.scrollY;
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    if (!navbar) return;
    const currentY = window.scrollY;
    if (currentY > 50) navbar.classList.add('scrolled');
    else navbar.classList.remove('scrolled');
    if (currentY > lastScrollY && currentY > 120) navbar.classList.add('navbar-hidden');
    else navbar.classList.remove('navbar-hidden');
    lastScrollY = currentY;
  });

  // Burger menu
  const burgerBtn = document.getElementById('burgerBtn');
  const mainNav = document.getElementById('mainNav');

  burgerBtn.addEventListener('click', () => {
    burgerBtn.classList.toggle('active');
    mainNav.classList.toggle('open');
  });
</script>
</body>
</html>
