<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title>Восстановление пароля — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=4">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php" style="text-decoration:none;"><h1><img src="src/logo.png" alt="Sapienta logo" width="80" height="80" style="width:80px;height:80px;object-fit:contain;filter:none!important;"></h1></a>
      <p>Восстановление пароля</p>
    </div>

    <div id="step1">
      <div id="alertBox"></div>
      <p class="text-muted mb-3">Введите email вашего аккаунта. Мы отправим ссылку для сброса пароля.</p>
      <form id="forgotForm">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" id="email" placeholder="your@email.com" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full" id="submitBtn">Отправить ссылку</button>
      </form>
    </div>

    <div id="step2" class="hidden text-center">
      <div style="font-size:3rem;margin-bottom:16px;">📧</div>
      <h3>Проверьте почту</h3>
      <p class="text-muted mt-2">Если аккаунт с таким email существует, мы отправили ссылку для сброса.</p>
      <p class="text-muted" style="font-size:.85rem;margin-top:8px;" id="devNote"></p>
    </div>

    <div class="text-center">
      <a href="login.php" class="btn btn-ghost btn-sm">← Вернуться ко входу</a>
    </div>
  </div>
</div>

<div class="toast-container"></div>
<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js?v=2"></script>
<script>
  document.getElementById('forgotForm').addEventListener('submit', async e => {
    e.preventDefault();
    const email = document.getElementById('email').value.trim();
    const btn   = document.getElementById('submitBtn');
    setLoading(btn, true);

    try {
      // В XAMPP режиме показываем токен напрямую (заглушка)
      const res = await fetch('api/auth.php?action=forgot_password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });
      const data = await res.json();

      document.getElementById('step1').classList.add('hidden');
      document.getElementById('step2').classList.remove('hidden');

      if (data.dev_token) {
        document.getElementById('devNote').innerHTML =
          `🛠 Режим разработки: <a href="reset-password.php?token=${data.dev_token}">ссылка для сброса</a>`;
      }
    } catch(err) {
      document.getElementById('alertBox').innerHTML =
        `<div class="alert alert-error">${err.message}</div>`;
    } finally {
      setLoading(btn, false);
    }
  });
</script>
</body>
</html>
