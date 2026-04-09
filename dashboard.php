<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title data-i18n="nav.dashboard">Личный кабинет — TestPlatform</title>
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
      <span>🎓</span> TestPlatform
    </a>
    <ul class="navbar-nav" id="mainNav">
      <li><a href="dashboard.php" class="active"><img src="https://img.icons8.com/ios/18/dashboard.png" alt="" width="18" height="18"> Кабинет</a></li>
      <li><a href="profile.php"><img src="https://img.icons8.com/ios/18/user.png" alt="" width="18" height="18"> Профиль</a></li>
      <li data-admin class="hidden"><a href="admin.php"><img src="https://img.icons8.com/ios/18/admin-settings-male.png" alt="" width="18" height="18"> Админ</a></li>
      <li><a href="#" onclick="AuthManager.logout()"><img src="https://img.icons8.com/ios/18/exit.png" alt="" width="18" height="18"> Выйти</a></li>
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

<div class="dashboard-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <ul class="sidebar-menu">
      <li><a href="#" class="active" onclick="showTab('overview'); return false;"><img src="https://img.icons8.com/ios/18/bar-chart.png" alt="" width="18" height="18"> Обзор</a></li>
      <li><a href="#" onclick="showTab('tests'); return false;"><img src="https://img.icons8.com/ios/18/test-document.png" alt="" width="18" height="18"> Тесты</a></li>
      <li><a href="#" onclick="showTab('history'); return false;"><img src="https://img.icons8.com/ios/18/history.png" alt="" width="18" height="18"> История</a></li>
      <li><a href="profile.php"><img src="https://img.icons8.com/ios/18/user.png" alt="" width="18" height="18"> Профиль</a></li>
    </ul>
  </aside>

  <!-- MAIN -->
  <main class="dashboard-main">

    <!-- TAB: OVERVIEW -->
    <div id="tab-overview">
      <div class="welcome-banner">
        <h1>Добро пожаловать, <span data-username>Пользователь</span>! 👋</h1>
        <p>Готовы проверить свои знания?</p>
      </div>

      <h2 style="margin-bottom: 24px;">Ваша статистика</h2>
      <div class="stats-row" id="statsRow" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div class="stat-box">
          <div class="stat-value" id="statTests">—</div>
          <div class="stat-label">Тестов пройдено</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="statAvg">—%</div>
          <div class="stat-label">Средний балл</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="statBest">—%</div>
          <div class="stat-label">Лучший результат</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="statTime">—ч</div>
          <div class="stat-label">Время в тестах</div>
        </div>
      </div>

      <h2 style="margin: 40px 0 24px;">Последние результаты</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Тест</th>
              <th>Дата</th>
              <th>Баллы</th>
              <th>%</th>
              <th>Статус</th>
            </tr>
          </thead>
          <tbody id="recentResults">
            <tr>
              <td colspan="5" class="text-center" style="color: var(--text-gray);">Пока нет результатов</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB: TESTS -->
    <div id="tab-tests" class="hidden">
      <div class="page-header">
        <h1 class="page-title">Доступные тесты</h1>
        <p class="page-subtitle">Выберите тест для прохождения</p>
      </div>

      <div class="test-grid" id="testGrid">
        <div class="text-center" style="grid-column: 1/-1; padding: 60px 20px;">
          <div class="spinner" style="width: 40px; height: 40px; margin: 0 auto;"></div>
          <p class="text-muted mt-2">Загрузка тестов...</p>
        </div>
      </div>
    </div>

    <!-- TAB: HISTORY -->
    <div id="tab-history" class="hidden">
      <div class="page-header">
        <h1 class="page-title">История прохождений</h1>
        <p class="page-subtitle">Все ваши попытки прохождения тестов</p>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Тест</th>
              <th>Попытка</th>
              <th>Дата</th>
              <th>Баллы</th>
              <th>%</th>
              <th>Статус</th>
              <th>Время</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody id="historyTable">
            <tr>
              <td colspan="8" class="text-center" style="color: var(--text-gray);">Пока нет записей</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<div class="NotificationToast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js"></script>
<script>
  if (!AuthManager.isLoggedIn()) {
    window.location.href = 'login.php?redirect=' + encodeURIComponent(location.href);
  }

  let currentTab = 'overview';
  let allResults = [];

  document.addEventListener('DOMContentLoaded', () => {
    AuthManager.updateNavbar();
    loadOverview();
  });

  function showTab(tab) {
    document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
    event.target.classList.add('active');

    document.querySelectorAll('.dashboard-main > div').forEach(d => d.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    currentTab = tab;

    if (tab === 'tests') loadTests();
    if (tab === 'history') loadHistory();
    if (tab === 'overview') loadOverview();

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  async function loadOverview() {
    try {
      const res = await API.getMyResults();
      allResults = res.results || [];

      const passed = allResults.filter(r => r.passed == 1);
      const avgScore = allResults.length > 0
        ? Math.round(allResults.reduce((sum, r) => sum + parseFloat(r.percentage), 0) / allResults.length)
        : 0;
      const bestScore = allResults.length > 0
        ? Math.round(Math.max(...allResults.map(r => parseFloat(r.percentage))))
        : 0;
      const totalTime = allResults.reduce((sum, r) => sum + (parseInt(r.time_spent) || 0), 0);

      document.getElementById('statTests').textContent = passed.length;
      document.getElementById('statAvg').textContent = avgScore + '%';
      document.getElementById('statBest').textContent = bestScore + '%';
      document.getElementById('statTime').textContent = Math.round(totalTime / 3600) + 'ч';

      const recent = allResults.slice(0, 5);
      const tbody = document.getElementById('recentResults');

      if (recent.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="color: var(--text-gray);">Пока нет результатов</td></tr>';
      } else {
        tbody.innerHTML = recent.map(r => `
          <tr>
            <td><strong>${escapeHtml(r.test_title)}</strong></td>
            <td class="text-muted" style="font-size: 0.9rem;">${new Date(r.created_at).toLocaleDateString('ru')}</td>
            <td>${r.score}/${r.max_score}</td>
            <td><strong>${parseFloat(r.percentage).toFixed(1)}%</strong></td>
            <td><span class="badge ${r.passed == 1 ? 'badge-success' : 'badge-danger'}">${r.passed == 1 ? 'Сдан' : 'Нет'}</span></td>
          </tr>
        `).join('');
      }
    } catch (err) {
      console.error(err);
    }
  }

  async function loadTests() {
    try {
      const res = await API.getTests();
      const tests = res.tests || [];
      const grid = document.getElementById('testGrid');

      if (tests.length === 0) {
        grid.innerHTML = `
          <div class="empty-state">
            <div class="empty-state-icon"><img src="https://img.icons8.com/ios/48/document.png" alt="" width="48" height="48"></div>
            <h3>Тестов пока нет</h3>
            <p>Обратитесь к администратору для добавления тестов</p>
          </div>
        `;
        return;
      }

      grid.innerHTML = tests.map(t => `
        <div class="test-card">
          <div class="test-card-header">
            <div class="test-card-icon">${getTestIcon(t.title)}</div>
            <div>
              <div class="test-card-title">${escapeHtml(t.title)}</div>
              <div class="test-card-desc">${escapeHtml(t.description || 'Нет описания')}</div>
            </div>
          </div>
          <div class="test-card-meta">
            <span class="test-meta-item"><img src="https://img.icons8.com/ios/14/clock.png" alt="" width="14" height="14"> ${t.time_limit} мин</span>
            <span class="test-meta-item"><img src="https://img.icons8.com/ios/14/question-mark.png" alt="" width="14" height="14"> ${t.question_count} вопросов</span>
            <span class="test-meta-item"><img src="https://img.icons8.com/ios/14/refresh.png" alt="" width="14" height="14"> ${t.max_attempts} поп.</span>
          </div>
          <div class="test-card-actions">
            <button class="btn btn-primary btn-full" onclick="startTest(${t.id})">Начать тест →</button>
          </div>
        </div>
      `).join('');
    } catch (err) {
      document.getElementById('testGrid').innerHTML = `
        <div class="empty-state">
          <div class="alert alert-error">${err.message}</div>
        </div>
      `;
    }
  }

  async function loadHistory() {
    try {
      const res = await API.getMyResults();
      const results = res.results || [];
      const tbody = document.getElementById('historyTable');

      if (results.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center" style="color: var(--text-gray);">Пока нет записей</td></tr>';
        return;
      }

      tbody.innerHTML = results.map(r => `
        <tr>
          <td><strong>${escapeHtml(r.test_title)}</strong></td>
          <td>#${r.attempt_number}</td>
          <td class="text-muted" style="font-size: 0.9rem;">${new Date(r.created_at).toLocaleDateString('ru')}</td>
          <td>${r.score}/${r.max_score}</td>
          <td><strong>${parseFloat(r.percentage).toFixed(1)}%</strong></td>
          <td><span class="badge ${r.passed == 1 ? 'badge-success' : 'badge-danger'}">${r.passed == 1 ? 'Сдан' : 'Нет'}</span></td>
          <td class="text-muted" style="font-size: 0.9rem;">${formatTime(r.time_spent)}</td>
          <td>
            <a href="api/test.php?action=export_pdf&attempt_id=${r.attempt_id}" 
               class="btn btn-outline btn-sm" 
               target="_blank" 
               title="Скачать PDF">
              📄 PDF
            </a>
          </td>
        </tr>
      `).join('');
    } catch (err) {
      console.error(err);
    }
  }

  function startTest(testId) {
    window.location.href = 'test.php?id=' + testId;
  }

  function getTestIcon(title) {
    const icons = {
      'математ': '<img src="https://img.icons8.com/ios/48/calculator--v1.png" alt="" width="40" height="40">',
      'информат': '<img src="https://img.icons8.com/ios/48/electronics.png" alt="" width="40" height="40">',
      'русск': '<img src="https://img.icons8.com/ios/48/fine-print.png" alt="" width="40" height="40">',
      'истор': '<img src="https://img.icons8.com/ios/48/history.png" alt="" width="40" height="40">',
      'физ': '<img src="https://img.icons8.com/ios/48/physics.png" alt="" width="40" height="40">',
      'хим': '<img src="https://img.icons8.com/ios/48/test-tube.png" alt="" width="40" height="40">',
      'био': '<img src="https://img.icons8.com/ios/48/dna.png" alt="" width="40" height="40">',
      'геометр': '<img src="https://img.icons8.com/ios/48/ruler.png" alt="" width="40" height="40">',
      'алгебр': '<img src="https://img.icons8.com/ios/48/numbers.png" alt="" width="40" height="40">',
    };
    const lower = title.toLowerCase();
    for (const [key, icon] of Object.entries(icons)) {
      if (lower.includes(key)) return icon;
    }
    return '<img src="https://img.icons8.com/ios/48/document.png" alt="" width="40" height="40">';
  }

  function formatTime(seconds) {
    if (!seconds) return '—';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    if (h > 0) return `${h}ч ${m}м`;
    if (m > 0) return `${m}м ${s}с`;
    return `${s}с`;
  }

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
