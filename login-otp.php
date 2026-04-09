<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Вход по коду — TestPlatform</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css">
  <style>
    .otp-inputs {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin: 20px 0;
    }
    .otp-input {
      width: 52px;
      height: 60px;
      text-align: center;
      font-size: 26px;
      font-weight: 800;
      border: 2px solid var(--border-light);
      border-radius: 10px;
      background: var(--bg-input);
      transition: all 0.2s;
      outline: none;
      color: var(--text-dark);
    }
    .otp-input:focus {
      border-color: var(--gradient-start);
      background: var(--white);
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
      transform: scale(1.05);
    }
    .otp-input.filled {
      border-color: var(--gradient-start);
      background: rgba(37, 99, 235, 0.05);
    }
    .otp-timer {
      text-align: center;
      padding: 12px;
      background: var(--bg-light);
      border-radius: 10px;
      margin: 16px 0;
    }
    .otp-timer-value {
      font-size: 28px;
      font-weight: 900;
      color: var(--gradient-start);
    }
    .otp-timer-label {
      font-size: 11px;
      color: var(--text-gray);
      font-weight: 600;
      margin-top: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .email-display {
      background: var(--bg-light);
      padding: 10px 14px;
      border-radius: 8px;
      font-weight: 600;
      color: var(--gradient-start);
      margin-bottom: 16px;
      text-align: center;
      font-size: 0.95rem;
    }
    .method-selector {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }
    .method-btn {
      flex: 1;
      padding: 12px;
      border: 2px solid var(--border-light);
      border-radius: 10px;
      background: var(--white);
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      color: var(--text-dark);
    }
    .method-btn:hover {
      border-color: var(--gradient-start);
      background: var(--bg-light);
    }
    .method-btn.active {
      border-color: var(--gradient-start);
      background: var(--gradient-start);
      color: var(--white);
    }
    .method-btn i {
      font-size: 16px;
    }
    .development-notice {
      background: #fffbeb;
      border: 1px solid #fbbf24;
      border-radius: 8px;
      padding: 12px;
      margin-top: 16px;
      font-size: 13px;
    }
    .development-notice strong {
      color: #b45309;
    }
    .development-notice code {
      display: block;
      background: #fef3c7;
      padding: 8px;
      border-radius: 6px;
      font-size: 22px;
      font-weight: 900;
      letter-spacing: 6px;
      color: var(--gradient-start);
      margin-top: 8px;
      text-align: center;
    }
    .resend-link {
      text-align: center;
      margin-top: 12px;
    }
    .resend-link a {
      color: var(--gradient-start);
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      transition: all 0.2s;
    }
    .resend-link a:hover {
      text-decoration: underline;
    }
    .resend-link a.disabled {
      color: var(--text-gray);
      pointer-events: none;
    }
    .resend-timer {
      font-size: 12px;
      color: var(--text-gray);
      margin-top: 4px;
    }
    .step {
      animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .back-to-login {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 12px;
      color: var(--text-gray);
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.2s;
    }
    .back-to-login:hover {
      color: var(--gradient-start);
    }
  </style>
</head>
<body>

<div class="AuthManager-wrapper">
  <div class="AuthManager-card">
    <div class="AuthManager-logo">
      <a href="index.php" style="text-decoration:none;">
        <h1>🎓 TestPlatform</h1>
      </a>
      <p id="pageSubtitle">Войдите по коду подтверждения</p>
    </div>

    <div id="alertBox"></div>

    <!-- Шаг 1: Email -->
    <div id="step-email" class="step">
      <form id="emailForm">
        <div class="form-group">
          <label class="form-label" for="emailInput">📧 Email</label>
          <div class="input-group">
            <span class="input-icon">✉️</span>
            <input class="form-control" type="email" id="emailInput" placeholder="your@email.com" autocomplete="email" required>
          </div>
        </div>

        <div class="method-selector">
          <button type="button" class="method-btn active" data-method="email" onclick="selectMethod('email')">
            <i class="fas fa-envelope"></i>
            Email
          </button>
          <button type="button" class="method-btn" data-method="sms" onclick="selectMethod('sms')">
            <i class="fas fa-sms"></i>
            SMS
          </button>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" id="sendCodeBtn">
          Получить код
        </button>
      </form>

      <p class="text-center mt-3" style="font-size:.95rem; color: var(--text-gray);">
        <a href="login.php" class="back-to-login">
          <i class="fas fa-arrow-left"></i>
          Вернуться ко входу
        </a>
      </p>
    </div>

    <!-- Шаг 2: Ввод кода -->
    <div id="step-code" class="step hidden">
      <div class="email-display" id="emailDisplay"></div>

      <form id="codeForm">
        <div class="form-group">
          <label class="form-label" style="text-align:center;">Введите 6-значный код</label>
          
          <div class="otp-inputs">
            <input type="text" class="otp-input" maxlength="1" data-index="0" inputmode="numeric" autofocus>
            <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric">
            <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric">
            <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric">
            <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric">
            <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric">
          </div>
        </div>

        <div class="otp-timer">
          <div class="otp-timer-value" id="timerValue">05:00</div>
          <div class="otp-timer-label">До истечения кода</div>
        </div>

        <button type="button" class="btn btn-primary btn-full btn-lg" id="verifyCodeBtn" disabled>
          Подтвердить вход
        </button>
      </form>

      <div class="resend-link">
        <a href="#" id="resendLink" class="disabled" onclick="resendCode(); return false;">
          Отправить код повторно
        </a>
        <div id="resendTimer" class="resend-timer"></div>
      </div>

      <p class="text-center mt-3" style="font-size:.95rem; color: var(--text-gray);">
        <a href="#" class="back-to-login" onclick="backToEmail(); return false;">
          <i class="fas fa-arrow-left"></i>
          Изменить email
        </a>
      </p>
    </div>

    <!-- Development notice -->
    <div id="devNotice" class="development-notice hidden">
      <strong>🔧 Режим разработки:</strong>
      <div style="margin-top:6px;font-size:12px;color:#92400e;">Ваш код подтверждения:</div>
      <code id="devCode"></code>
      <div style="margin-top:8px;font-size:11px;color:#92400e;">
        В production режиме код будет отправлен на email
      </div>
    </div>

    <hr style="border:none;border-top:1px solid var(--border-light);margin:20px 0;">

    <div class="text-center">
      <a href="index.php" class="btn btn-ghost btn-sm">← На главную</a>
    </div>
  </div>
</div>

<div class="NotificationToast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js"></script>
<script>
  if (AuthManager.isLoggedIn()) {
    window.location.href = 'dashboard.php';
  }

  let currentEmail = '';
  let currentMethod = 'email';
  let timerInterval = null;
  let resendInterval = null;
  let expiresIn = 300;

  // ── Email form ──────────────────────────────────────────────────────────
  document.getElementById('emailForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('emailInput').value.trim();
    
    if (!email || !email.includes('@')) {
      NotificationToast.error('Введите корректный email');
      return;
    }

    currentEmail = email;
    await requestOTP(email);
  });

  // ── Method selector ─────────────────────────────────────────────────────
  function selectMethod(method) {
    currentMethod = method;
    document.querySelectorAll('.method-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.method === method);
    });
  }

  // ── Request OTP ─────────────────────────────────────────────────────────
  async function requestOTP(email) {
    const btn = document.getElementById('sendCodeBtn');
    setLoading(btn, true);

    try {
      const res = await fetch((window.APP_URL || '') + '/api/otp.php?action=request', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, method: currentMethod })
      });

      const data = await res.json();

      if (!data.success) {
        throw new Error(data.message);
      }

      // Показываем код для разработки
      if (data.development_code) {
        document.getElementById('devCode').textContent = data.development_code;
        document.getElementById('devNotice').classList.remove('hidden');
      }

      // Переходим к вводу кода
      showStep('code');
      document.getElementById('emailDisplay').textContent = '📧 ' + email;
      document.getElementById('pageSubtitle').textContent = 'Введите код из письма';
      
      // Запускаем таймер
      startTimer(data.expires_in || 300);
      startResendCooldown();

      // Фокус на первое поле
      setTimeout(() => {
        document.querySelector('.otp-input[data-index="0"]').focus();
      }, 100);

      NotificationToast.success('Код отправлен!');
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  }

  // ── OTP inputs ──────────────────────────────────────────────────────────
  const otpInputs = document.querySelectorAll('.otp-input');
  
  otpInputs.forEach((input, index) => {
    // Только цифры
    input.addEventListener('input', (e) => {
      const value = e.target.value.replace(/[^0-9]/g, '');
      e.target.value = value;

      if (value) {
        e.target.classList.add('filled');
        // Переход к следующему
        const next = document.querySelector(`.otp-input[data-index="${index + 1}"]`);
        if (next) next.focus();
      } else {
        e.target.classList.remove('filled');
      }

      checkCodeComplete();
    });

    // Backspace
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !e.target.value) {
        const prev = document.querySelector(`.otp-input[data-index="${index - 1}"]`);
        if (prev) {
          prev.focus();
          prev.value = '';
          prev.classList.remove('filled');
        }
      }
    });

    // Paste
    input.addEventListener('paste', (e) => {
      e.preventDefault();
      const paste = (e.clipboardData || window.clipboardData).getData('text').trim();
      if (/^\d{6}$/.test(paste)) {
        paste.split('').forEach((char, i) => {
          const inp = document.querySelector(`.otp-input[data-index="${i}"]`);
          if (inp) {
            inp.value = char;
            inp.classList.add('filled');
          }
        });
        checkCodeComplete();
        document.querySelector('.otp-input[data-index="5"]').focus();
      }
    });
  });

  // ── Check code complete ─────────────────────────────────────────────────
  function checkCodeComplete() {
    let code = '';
    otpInputs.forEach(input => code += input.value);
    
    const btn = document.getElementById('verifyCodeBtn');
    btn.disabled = code.length !== 6;
  }

  // ── Verify code ─────────────────────────────────────────────────────────
  document.getElementById('verifyCodeBtn').addEventListener('click', async () => {
    let code = '';
    otpInputs.forEach(input => code += input.value);

    if (code.length !== 6) {
      NotificationToast.error('Введите полный 6-значный код');
      return;
    }

    const btn = document.getElementById('verifyCodeBtn');
    setLoading(btn, true);

    try {
      const res = await fetch((window.APP_URL || '') + '/api/otp.php?action=verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email: currentEmail,
          code,
          csrf_token: localStorage.getItem('csrf_token')
        })
      });

      const data = await res.json();

      if (!data.success) {
        if (data.remaining_attempts !== undefined) {
          NotificationToast.warning(`${data.message}. Осталось попыток: ${data.remaining_attempts}`);
        } else {
          NotificationToast.error(data.message);
        }
        return;
      }

      // Успешный вход
      localStorage.setItem('auth_token', data.token);
      if (data.csrf_token) {
        localStorage.setItem('csrf_token', data.csrf_token);
      }

      NotificationToast.success('Вход выполнен! Перенаправление...');
      
      setTimeout(() => {
        window.location.href = 'dashboard.php';
      }, 1000);
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  });

  // ── Timer ───────────────────────────────────────────────────────────────
  function startTimer(seconds) {
    expiresIn = seconds;
    clearInterval(timerInterval);

    timerInterval = setInterval(() => {
      expiresIn--;
      if (expiresIn <= 0) {
        clearInterval(timerInterval);
        document.getElementById('timerValue').textContent = '00:00';
        document.getElementById('timerValue').style.color = 'var(--danger)';
        return;
      }

      const mins = Math.floor(expiresIn / 60);
      const secs = expiresIn % 60;
      document.getElementById('timerValue').textContent = 
        `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;

      // Предупреждение когда меньше минуты
      if (expiresIn < 60) {
        document.getElementById('timerValue').style.color = 'var(--danger)';
      }
    }, 1000);
  }

  // ── Resend cooldown ─────────────────────────────────────────────────────
  function startResendCooldown() {
    let cooldown = 60;
    const resendLink = document.getElementById('resendLink');
    const resendTimer = document.getElementById('resendTimer');

    resendLink.classList.add('disabled');
    clearInterval(resendInterval);

    resendInterval = setInterval(() => {
      cooldown--;
      if (cooldown <= 0) {
        clearInterval(resendInterval);
        resendLink.classList.remove('disabled');
        resendTimer.textContent = '';
        return;
      }
      resendTimer.textContent = `Можно через ${cooldown} сек`;
    }, 1000);
  }

  // ── Resend code ─────────────────────────────────────────────────────────
  async function resendCode() {
    try {
      const res = await fetch((window.APP_URL || '') + '/api/otp.php?action=resend', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: currentEmail, method: currentMethod })
      });

      const data = await res.json();

      if (!data.success) {
        throw new Error(data.message);
      }

      // Обновляем код для разработки
      if (data.development_code) {
        document.getElementById('devCode').textContent = data.development_code;
      }

      // Перезапускаем таймер
      startTimer(data.expires_in || 300);
      startResendCooldown();

      NotificationToast.success('Новый код отправлен!');
    } catch (err) {
      NotificationToast.error(err.message);
    }
  }

  // ── Helpers ─────────────────────────────────────────────────────────────
  function showStep(step) {
    document.getElementById('step-email').classList.toggle('hidden', step !== 'email');
    document.getElementById('step-code').classList.toggle('hidden', step !== 'code');
  }

  function backToEmail() {
    showStep('email');
    document.getElementById('devNotice').classList.add('hidden');
    document.getElementById('pageSubtitle').textContent = 'Войдите по коду подтверждения';
    clearInterval(timerInterval);
    clearInterval(resendInterval);
    
    // Очищаем поля
    otpInputs.forEach(input => {
      input.value = '';
      input.classList.remove('filled');
    });
  }

  function setLoading(btn, loading) {
    if (loading) {
      btn.disabled = true;
      btn.dataset.originalText = btn.textContent;
      btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:8px;"></span> Подождите...';
    } else {
      btn.disabled = false;
      btn.textContent = btn.dataset.originalText || 'Продолжить';
    }
  }
</script>
</body>
</html>
