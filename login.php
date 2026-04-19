<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title data-i18n="auth.login.title">Вход — Sapienta</title>
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
      <p data-i18n="auth.login.subtitle">Войдите в свой аккаунт</p>
    </div>

    <div id="alertBox"></div>

    <form id="loginForm" novalidate>
      <div class="form-group">
        <label class="form-label" for="login" data-i18n="auth.login.label">Email или логин</label>
        <div class="input-group">
          <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg></span>
          <input class="form-control" type="text" id="login" name="login" placeholder="user@example.com" data-i18n-placeholder="auth.login.label" autocomplete="username" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password" data-i18n="auth.login.password">Пароль</label>
        <div class="input-group" style="position:relative;">
          <span class="input-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg></span>
          <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" data-i18n-placeholder="auth.login.password" autocomplete="current-password" required style="padding-right:48px;">
          <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Показать пароль">
            <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.964 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
            <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
          </button>
        </div>
        <div style="text-align:right;margin-top:8px;">
          <a href="forgot-password.php" style="font-size:.9rem; color: var(--gradient-start);" data-i18n="auth.login.forgot">Забыли пароль?</a>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" id="submitBtn" data-i18n="auth.login.submit">
        Войти
      </button>
    </form>

    <p class="text-center mt-3" style="font-size:.95rem; color: var(--text-gray);">
      <span data-i18n="auth.login.no-account">Нет аккаунта?</span> <a href="register.php" style="color: var(--gradient-start); font-weight: 600;" data-i18n="auth.login.register">Зарегистрироваться</a>
    </p>

    <div class="text-center">
      <a href="index.php" class="btn btn-ghost btn-sm" data-i18n="auth.login.back">← На главную</a>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js?v=2"></script>
<script>
  if (AuthManager.isLoggedIn()) {
    window.location.href = 'dashboard.php';
  }

  const form = document.getElementById('loginForm');
  const alertBox = document.getElementById('alertBox');
  const submitBtn = document.getElementById('submitBtn');

  form.addEventListener('submit', async e => {
    e.preventDefault();
    clearErrors(form);
    alertBox.innerHTML = '';

    const login = document.getElementById('login').value.trim();
    const password = document.getElementById('password').value;

    if (!login || !password) {
      alertBox.innerHTML = '<div class="alert alert-error">⚠️ Заполните все поля</div>';
      return;
    }

    setLoading(submitBtn, true);
    try {
      const res = await AuthManager.login(login, password);
      if (res.success) {
        NotificationToast.success('Добро пожаловать, ' + res.user.username + '! 🎉');
        const redirect = new URLSearchParams(location.search).get('redirect');
        setTimeout(() => {
          window.location.href = redirect || (res.user.role === 'admin' ? 'admin.php' : 'dashboard.php');
        }, 500);
      } else {
        alertBox.innerHTML = `<div class="alert alert-error">⚠️ ${res.message || 'Ошибка входа'}</div>`;
      }
    } catch (e) {
      alertBox.innerHTML = `<div class="alert alert-error">⚠️ ${e.message}</div>`;
    } finally {
      setLoading(submitBtn, false);
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
