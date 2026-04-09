<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-i18n="auth.register.title">Регистрация — TestPlatform</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css">
  <style>
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
  </style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php" style="text-decoration:none;">
        <h1>🎓 TestPlatform</h1>
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
        <input class="form-control" type="password" id="password" name="password" placeholder="Минимум 8 символов" data-i18n-placeholder="auth.register.password" autocomplete="new-password" required>
        <div class="strength-bar" id="strengthBar" style="display: none;">
          <div class="strength-fill" id="strengthProgress"></div>
        </div>
        <div class="strength-label" id="strengthLabel"></div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirm" data-i18n="auth.register.password-confirm">Подтверждение пароля *</label>
        <input class="form-control" type="password" id="password_confirm" placeholder="Повторите пароль" data-i18n-placeholder="auth.register.password-confirm" autocomplete="new-password" required>
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

    <div class="text-center mt-3">
      <a href="login-otp.php" style="color: var(--gradient-start); font-weight: 600; font-size: 0.9rem;">
        <img src="https://img.icons8.com/ios/18/key--v1.png" alt="" width="18" height="18"> Войти по коду (без пароля)
      </a>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js"></script>
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
</script>
</body>
</html>
