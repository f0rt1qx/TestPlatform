<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title data-i18n="auth.register.title">Регистрация — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=4">
  <style>
    .auth-card .auth-logo {
      padding: 32px 24px 20px;
      text-align: center;
    }
    .auth-card .auth-logo a {
      display: flex;
      justify-content: center;
    }
    .auth-card .auth-logo img {
      width: 80px;
      height: 80px;
      object-fit: contain;
    }
    .auth-card .auth-logo p {
      margin-top: 16px;
      text-align: center;
    }
    /* Register-specific */
    .strength-bar {
      height: 6px;
      background: var(--border-light);
      border-radius: 3px;
      margin-top: 10px;
      overflow: hidden;
    }
    .strength-fill {
      height: 100%;
      width: 0;
      transition: all 0.3s ease;
      border-radius: 3px;
    }
    .strength-label {
      font-size: 0.85rem;
      margin-top: 6px;
      font-weight: 600;
    }
    .password-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      padding: 4px;
      cursor: pointer;
      color: var(--muted, #94a3b8);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: color .2s ease;
      z-index: 1;
    }
    .password-toggle:hover { color: var(--text-dark, #1e293b); }
    .password-toggle svg { display: block; }
  </style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php" style="text-decoration:none;">
        <img src="src/logogreen.png" alt="Sapienta logo" width="80" height="80" style="width:80px;height:80px;object-fit:contain;">
      </a>
      <p data-i18n="auth.register.subtitle">Создайте аккаунт</p>
    </div>

    <div id="alertBox"></div>

    <form id="registerForm" novalidate>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label" for="first_name" data-i18n="auth.register.first-name">Имя</label>
          <input class="form-control" type="text" id="first_name" name="first_name" placeholder="Иван" data-i18n-placeholder="auth.register.first-name">
        </div>
        <div class="form-group">
          <label class="form-label" for="last_name" data-i18n="auth.register.last-name">Фамилия</label>
          <input class="form-control" type="text" id="last_name" name="last_name" placeholder="Иванов" data-i18n-placeholder="auth.register.last-name">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="username" data-i18n="auth.register.username">Имя пользователя *</label>
        <input class="form-control" type="text" id="username" name="username" placeholder="ivan_ivanov" data-i18n-placeholder="auth.register.username" required>
        <div class="form-hint" data-i18n="auth.register.username-hint">Только латиница, цифры, _. От 3 до 50 символов.</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="email" data-i18n="auth.register.email">Email *</label>
        <input class="form-control" type="email" id="email" name="email" placeholder="ivan@example.com" data-i18n-placeholder="auth.register.email" autocomplete="email" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password" data-i18n="auth.register.password">Пароль *</label>
        <div class="input-group" style="position:relative;">
          <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></span>
          <input class="form-control" type="password" id="password" name="password" placeholder="Минимум 8 символов" data-i18n-placeholder="auth.register.password" autocomplete="new-password" required style="padding-right:48px;">
          <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Показать пароль">
            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.964 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
          </button>
        </div>
        <div class="strength-bar" id="strengthBar" style="display: none;">
          <div class="strength-fill" id="strengthProgress"></div>
        </div>
        <div class="strength-label" id="strengthLabel"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirm" data-i18n="auth.register.password-confirm">Подтверждение пароля *</label>
        <div class="input-group" style="position:relative;">
          <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg></span>
          <input class="form-control" type="password" id="password_confirm" placeholder="Повторите пароль" data-i18n-placeholder="auth.register.password-confirm" autocomplete="new-password" required style="padding-right:48px;">
          <button type="button" class="password-toggle" onclick="togglePassword('password_confirm', this)" aria-label="Показать пароль">
            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.964 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
          </button>
        </div>
      </div>

      <div class="form-group" style="display:flex;align-items:flex-start;gap:12px;">
        <input type="checkbox" id="agree" style="margin-top:4px;flex-shrink:0;width:20px;height:20px;" required>
        <label for="agree" style="font-size:.9rem;cursor:pointer; color: var(--text-gray);" data-i18n="auth.register.agree">Я согласен с условиями использования платформы и понимаю, что мои действия при прохождении тестов записываются.</label>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" id="submitBtn" data-i18n="auth.register.submit">
        Зарегистрироваться
      </button>
    </form>

    <p class="text-center mt-3" style="font-size:.95rem; color: var(--text-gray);">
      <span data-i18n="auth.register.have-account">Уже есть аккаунт?</span> <a href="login.php" style="color: var(--gradient-start); font-weight: 600;" data-i18n="auth.register.login">Войти</a>
    </p>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js?v=2"></script>
<script>
  if (AuthManager.isLoggedIn()) window.location.href = 'dashboard.php';

  // Индикатор силы пароля
  const pwdInput = document.getElementById('password');
  pwdInput.addEventListener('input', () => {
    const v = pwdInput.value;
    const bar = document.getElementById('strengthBar');
    const prog = document.getElementById('strengthProgress');
    const label = document.getElementById('strengthLabel');

    if (!v) { bar.style.display = 'none'; return; }
    bar.style.display = 'block';

    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^a-zA-Z0-9]/.test(v)) score++;

    const levels = [
      { pct: '25%', color: '#ef4444', text: 'Очень слабый' },
      { pct: '50%', color: '#f59e0b', text: 'Слабый' },
      { pct: '75%', color: '#3b82f6', text: 'Нормальный' },
      { pct: '100%',color: '#10b981', text: 'Сильный' },
    ];
    const level = levels[score - 1] || levels[0];
    prog.style.width = level.pct;
    prog.style.background = level.color;
    label.textContent = level.text;
    label.style.color = level.color;
  });

  // Форма
  document.getElementById('registerForm').addEventListener('submit', async e => {
    e.preventDefault();
    clearErrors(e.target);
    document.getElementById('alertBox').innerHTML = '';

    const username = document.getElementById('username').value.trim();
    const email    = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirm  = document.getElementById('password_confirm').value;
    const agree    = document.getElementById('agree').checked;

    const errors = [];
    if (!username || username.length < 3) errors.push('Имя пользователя: минимум 3 символа');
    if (!/^[a-zA-Z0-9_]+$/.test(username)) errors.push('Имя пользователя: только латиница, цифры, _');
    if (!email || !email.includes('@')) errors.push('Введите корректный email');
    if (password.length < 8) errors.push('Пароль: минимум 8 символов');
    if (password !== confirm) errors.push('Пароли не совпадают');
    if (!agree) errors.push('Необходимо согласиться с условиями');

    if (errors.length) {
      document.getElementById('alertBox').innerHTML = errors.map(e =>
        `<div class="alert alert-error">${e}</div>`
      ).join('');
      return;
    }

    const btn = document.getElementById('submitBtn');
    setLoading(btn, true);
    try {
      console.log('[REGISTER] Отправка данных:', { username, email, first_name: document.getElementById('first_name').value.trim(), last_name: document.getElementById('last_name').value.trim() });
      
      const res = await AuthManager.register({
        username,
        email,
        password,
        first_name: document.getElementById('first_name').value.trim(),
        last_name:  document.getElementById('last_name').value.trim(),
      });
      
      console.log('[REGISTER] Ответ сервера:', res);
      
      if (res.success) {
        NotificationToast.success('Аккаунт создан! Добро пожаловать! 🎉');
        setTimeout(() => window.location.href = 'dashboard.php', 800);
      } else {
        // Показываем ошибки валидации от сервера
        const errorMessages = res.errors || [res.message || 'Ошибка регистрации'];
        document.getElementById('alertBox').innerHTML = errorMessages.map(e =>
          `<div class="alert alert-error">${e}</div>`
        ).join('');
      }
    } catch (err) {
      console.error('[REGISTER] Ошибка:', err);
      let errorMsg = err.message || 'Произошла ошибка при регистрации';
      
      // Добавляем подсказки
      if (err.message.includes('HTML') || err.message.includes('JSON')) {
        errorMsg += '<br><br>💡 <strong>Возможная проблема:</strong> Сервер вернул HTML вместо JSON.<br>' +
          'Проверьте что:<br>' +
          '1. Apache и MySQL запущены в XAMPP<br>' +
          '2. База данных создана и импортирован sql/database.sql<br>' +
          '3. Откройте <a href="test-db.php" target="_blank">test-db.php</a> для проверки БД';
      }
      
      document.getElementById('alertBox').innerHTML =
        `<div class="alert alert-error">${errorMsg}</div>`;
    } finally {
      setLoading(btn, false);
    }
  });

  function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const eyeOpen = btn.querySelector('.eye-open');
    const eyeClosed = btn.querySelector('.eye-closed');
    if (input.type === 'password') {
      input.type = 'text';
      eyeOpen.classList.add('hidden');
      eyeClosed.classList.remove('hidden');
    } else {
      input.type = 'password';
      eyeOpen.classList.remove('hidden');
      eyeClosed.classList.add('hidden');
    }
  }
</script>
</body>
</html>
