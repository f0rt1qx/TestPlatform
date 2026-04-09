<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-i18n="auth.login.title">Вход — TestPlatform</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css">
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php" style="text-decoration:none;">
        <h1>🎓 TestPlatform</h1>
      </a>
      <p data-i18n="auth.login.subtitle">Войдите в свой аккаунт</p>
    </div>

    <div id="alertBox"></div>

    <form id="loginForm" novalidate>
      <div class="form-group">
        <label class="form-label" for="login" data-i18n="auth.login.label">Email или логин</label>
        <div class="input-group">
          <span class="input-icon">📧</span>
          <input class="form-control" type="text" id="login" name="login" placeholder="user@example.com" data-i18n-placeholder="auth.login.label" autocomplete="username" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password" data-i18n="auth.login.password">Пароль</label>
        <input class="form-control" type="password" id="password" name="password" placeholder="••••••••" data-i18n-placeholder="auth.login.password" autocomplete="current-password" required>
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

    <div class="text-center mt-3">
      <a href="login-otp.php" style="color: var(--gradient-start); font-weight: 600; font-size: 0.9rem;">
        <i class="fas fa-key"></i> Войти по коду (без пароля)
      </a>
    </div>

    <div class="text-center">
      <a href="index.php" class="btn btn-ghost btn-sm" data-i18n="auth.login.back">← На главную</a>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js"></script>
<script>
  if (Auth.isLoggedIn()) {
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
      const res = await Auth.login(login, password);
      if (res.success) {
        Toast.success('Добро пожаловать, ' + res.user.username + '! 🎉');
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
</script>
</body>
</html>
