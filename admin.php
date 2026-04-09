<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Админ-панель — TestPlatform</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css">
  <style>
    .severity-high   { color: var(--danger);  font-weight:700; }
    .severity-medium { color: var(--warning); font-weight:700; }
    .severity-low    { color: var(--text-gray); }
    .log-event { font-family: monospace; font-size:.85rem; background:var(--bg-light); padding:4px 8px; border-radius:6px; }
    .cheat-score { font-weight: 600; }
    .cheat-score.safe { color: var(--success); }
    .cheat-score.moderate { color: var(--warning); }
    .cheat-score.danger { color: var(--danger); }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="container">
    <a href="index.php" class="navbar-brand">
      <img src="https://img.icons8.com/ios/24/graduation-cap.png" alt="" width="24" height="24"> TestPlatform
    </a>
    <ul class="navbar-nav" id="mainNav">
      <li><a href="dashboard.php">Кабинет</a></li>
      <li><a href="admin.php" class="active">Админ</a></li>
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

<div class="container" style="padding:32px 20px;">
  <div class="page-title">⚙️ Админ-панель</div>

  <!-- Tabs -->
  <div class="admin-tabs">
    <button class="admin-tab active" onclick="switchTab('users')"><img src="https://img.icons8.com/ios/18/users.png" alt="" width="18" height="18"> Пользователи</button>
    <button class="admin-tab" onclick="switchTab('tests')"><img src="https://img.icons8.com/ios/18/test-document.png" alt="" width="18" height="18"> Тесты</button>
    <button class="admin-tab" onclick="switchTab('logs')"><img src="https://img.icons8.com/ios/18/log.png" alt="" width="18" height="18"> Логи</button>
    <button class="admin-tab" onclick="switchTab('eye_tracking')"><img src="https://img.icons8.com/ios/18/visible.png" alt="" width="18" height="18"> Eye-tracking</button>
    <button class="admin-tab" onclick="switchTab('results')"><img src="https://img.icons8.com/ios/18/bar-chart.png" alt="" width="18" height="18"> Результаты</button>
  </div>

  <!-- USERS TAB -->
  <div id="tab-users">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3>Управление пользователями</h3>
    </div>
    <div id="usersTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>

  <!-- TESTS TAB -->
  <div id="tab-tests" class="hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3>Управление тестами</h3>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn btn-outline" onclick="downloadTemplate()">📥 Шаблон CSV</button>
        <button class="btn btn-primary" onclick="openModal('importTestModal')">📤 Импорт CSV</button>
        <button class="btn btn-primary" onclick="openModal('createTestModal')">+ Создать тест</button>
      </div>
    </div>
    <div id="testsTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>

  <!-- LOGS TAB -->
  <div id="tab-logs" class="hidden">
    <h3 style="margin-bottom:20px;">Логи анти-читинг системы</h3>
    <div id="logsTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>

  <!-- EYE-TRACKING TAB -->
  <div id="tab-eye_tracking" class="hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3>👁️ Данные eye-tracking</h3>
      <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <select id="eyeTestFilter" onchange="loadEyeTrackingData()" style="padding:8px 12px;border-radius:8px;border:1px solid var(--border-light);font-size:0.9rem;">
          <option value="">Все тесты</option>
        </select>
        <select id="eyeAttemptFilter" onchange="loadEyeTrackingData()" style="padding:8px 12px;border-radius:8px;border:1px solid var(--border-light);font-size:0.9rem;">
          <option value="">Все попытки</option>
        </select>
      </div>
    </div>
    
    <!-- Stats Summary -->
    <div id="eyeStatsSummary" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;"></div>
    
    <!-- Visualization -->
    <div id="eyeVisualization" style="background:var(--white);border-radius:var(--radius-lg);padding:24px;margin-bottom:24px;box-shadow:var(--shadow-md);display:none;">
      <h4 style="margin-bottom:16px;">📊 Визуализация фиксаций</h4>
      <canvas id="eyeHeatmapCanvas" width="800" height="600" style="width:100%;border-radius:8px;background:#f8fafc;"></canvas>
    </div>
    
    <!-- Detailed Data -->
    <div id="eyeTrackingTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>

  <!-- RESULTS TAB -->
  <div id="tab-results" class="hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3>Все результаты</h3>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="api/admin.php?action=export_csv" class="btn btn-outline btn-sm">📥 Экспорт CSV</a>
        <a href="api/admin.php?action=export_pdf" class="btn btn-primary btn-sm" target="_blank">📄 Экспорт PDF</a>
      </div>
    </div>
    <div id="resultsTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>
</div>

<!-- MODAL: Create Test -->
<div class="modal-overlay hidden" id="createTestModal">
  <div class="modal" style="max-width:640px;">
    <div class="modal-header">
      <div class="modal-title">Создать новый тест</div>
      <button class="modal-close" onclick="closeModal('createTestModal')">✕</button>
    </div>
    <form id="createTestForm">
      <div class="form-group">
        <label class="form-label">Название *</label>
        <input class="form-control" id="testTitle" placeholder="Введите название теста" required>
      </div>
      <div class="form-group">
        <label class="form-label">Описание</label>
        <textarea class="form-control" id="testDesc" rows="3" placeholder="Краткое описание..."></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Время (мин)</label>
          <input class="form-control" id="testTime" type="number" value="30" min="1" max="300">
        </div>
        <div class="form-group">
          <label class="form-label">Попытки</label>
          <input class="form-control" id="testAttempts" type="number" value="1" min="1" max="10">
        </div>
        <div class="form-group">
          <label class="form-label">Проходной % </label>
          <input class="form-control" id="testPassScore" type="number" value="60" min="1" max="100">
        </div>
      </div>
      <div style="display:flex;gap:20px;margin-bottom:20px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" id="shuffleQ" checked> Перемешивать вопросы
        </label>
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" id="shuffleA" checked> Перемешивать ответы
        </label>
      </div>
      <div style="display:flex;gap:12px;justify-content:flex-end;">
        <button type="button" class="btn btn-ghost" onclick="closeModal('createTestModal')">Отмена</button>
        <button type="submit" class="btn btn-primary" id="createTestBtn">Создать тест</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Import CSV -->
<div class="modal-overlay hidden" id="importTestModal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header">
      <div class="modal-title">📤 Импорт теста из CSV</div>
      <button class="modal-close" onclick="closeModal('importTestModal')">✕</button>
    </div>
    
    <div style="padding:0 0 20px;">
      <p class="text-muted" style="font-size:.9rem;margin-bottom:16px;">
        Загрузите CSV файл с вопросами и ответами. Каждый вопрос должен быть в отдельной строке.
      </p>
      
      <div style="background:var(--bg-input);border-radius:8px;padding:16px;margin-bottom:16px;">
        <strong style="font-size:.85rem;text-transform:uppercase;color:var(--muted);">Формат CSV:</strong>
        <code style="display:block;font-size:.75rem;color:var(--muted);margin-top:8px;word-break:break-all;">
          test_title, test_description, time_limit, max_attempts, pass_score, question_text, question_type, points, answer_text, is_correct
        </code>
      </div>
      
      <div style="background:#fffbeb;border:1px solid #fbbf24;border-radius:8px;padding:14px;margin-bottom:16px;">
        <strong style="color:#b45309;font-size:.85rem;">⚠️ Важно:</strong>
        <ul style="margin:8px 0 0 20px;color:#92400e;font-size:.85rem;line-height:1.6;">
          <li>Файл должен быть в формате CSV (UTF-8)</li>
          <li>Все строки одного теста должны идти подряд</li>
          <li><code>question_type</code>: <code>single</code> (один ответ) или <code>multiple</code> (несколько)</li>
          <li><code>is_correct</code>: <code>1</code> для правильного ответа, <code>0</code> для неправильного</li>
        </ul>
      </div>
      
      <form id="importForm" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Выберите CSV файл *</label>
          <input type="file" id="csvFile" accept=".csv" required 
                 style="display:block;width:100%;padding:10px;border:2px dashed var(--border);border-radius:8px;background:var(--bg-input);cursor:pointer;">
        </div>
        
        <div id="importProgress" class="hidden" style="text-align:center;padding:20px;">
          <div class="spinner" style="width:32px;height:32px;border-width:3px;"></div>
          <p class="text-muted" style="margin-top:12px;">Импорт тестов...</p>
        </div>
        
        <div id="importResult" class="hidden" style="margin-top:16px;"></div>
        
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
          <button type="button" class="btn btn-ghost" onclick="closeModal('importTestModal')">Отмена</button>
          <button type="submit" class="btn btn-primary" id="importBtn">Импортировать</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Add Question -->
<div class="modal-overlay hidden" id="addQuestionModal">
  <div class="modal" style="max-width:680px;">
    <div class="modal-header">
      <div class="modal-title">Добавить вопрос</div>
      <button class="modal-close" onclick="closeModal('addQuestionModal')">✕</button>
    </div>
    <form id="addQuestionForm">
      <input type="hidden" id="aqTestId">
      <div class="form-group">
        <label class="form-label">Вопрос *</label>
        <textarea class="form-control" id="aqText" rows="3" required placeholder="Текст вопроса..."></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="form-group">
          <label class="form-label">Тип вопроса</label>
          <select class="form-control" id="aqType">
            <option value="single">Один правильный</option>
            <option value="multiple">Несколько правильных</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Баллов</label>
          <input class="form-control" id="aqPoints" type="number" value="1" min="1">
        </div>
      </div>

      <label class="form-label">Варианты ответов *</label>
      <div id="answersContainer"></div>
      <button type="button" class="btn btn-ghost btn-sm mt-1" onclick="addAnswerRow()">+ Добавить вариант</button>

      <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addQuestionModal')">Отмена</button>
        <button type="submit" class="btn btn-primary" id="addQBtn">Добавить</button>
      </div>
    </form>
  </div>
</div>

<div class="NotificationToast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js"></script>
<script>
  // AuthManager guard
  document.addEventListener('DOMContentLoaded', () => {
    if (!AuthManager.isLoggedIn() || !AuthManager.isAdmin()) {
      window.location.href = 'login.php';
    }
    loadTab('users');
  });

  // Download CSV template
  function downloadTemplate() {
    window.location.href = (window.APP_URL || '') + '/api/import.php?action=template';
  }

  const allTabs = ['users','tests','logs','results'];
  let loadedTabs = {};

  function switchTab(name) {
    allTabs.forEach(t => {
      document.getElementById('tab-' + t).classList.toggle('hidden', t !== name);
    });
    document.querySelectorAll('.admin-tab').forEach((btn, i) => {
      btn.classList.toggle('active', allTabs[i] === name);
    });
    loadTab(name);
  }

  async function loadTab(tab) {
    if (loadedTabs[tab]) return;
    loadedTabs[tab] = true;

    if (tab === 'users') await loadUsers();
    if (tab === 'tests') await loadTests();
    if (tab === 'logs')  await loadLogs();
    if (tab === 'eye_tracking') await loadEyeTrackingData();
    if (tab === 'results') await loadResults();
  }

  // ── Users ──────────────────────────────────────────────────────────────────
  async function loadUsers() {
    try {
      const res = await API.adminUsers();
      const users = res.users || [];
      document.getElementById('usersTable').innerHTML = `
        <table>
          <thead><tr><th>ID</th><th>Пользователь</th><th>Email</th><th>Роль</th><th>Статус</th><th>Дата</th><th>Действия</th></tr></thead>
          <tbody>
            ${users.map(u => `
              <tr>
                <td>#${u.id}</td>
                <td><strong>${e(u.username)}</strong><br><span class="text-muted" style="font-size:.8rem;">${e(u.first_name||'')} ${e(u.last_name||'')}</span></td>
                <td>${e(u.email)}</td>
                <td><span class="badge ${u.role === 'admin' ? 'badge-warning' : 'badge-info'}">${u.role}</span></td>
                <td>
                  ${u.is_blocked ? '<span class="badge badge-danger">Блок</span>' : '<span class="badge badge-success">Активен</span>'}
                  ${u.email_verified ? '' : '<span class="badge badge-warning" style="margin-left:4px;">Email не подтверждён</span>'}
                </td>
                <td class="text-muted" style="font-size:.8rem;">${new Date(u.created_at).toLocaleDateString('ru')}</td>
                <td>
                  <button class="btn btn-sm ${u.is_blocked ? 'btn-success' : 'btn-danger'}"
                          onclick="toggleBlock(${u.id}, ${!u.is_blocked})">
                    ${u.is_blocked ? 'Разблокировать' : 'Заблокировать'}
                  </button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;
    } catch(err) {
      document.getElementById('usersTable').innerHTML = `<div class="alert alert-error">${err.message}</div>`;
    }
  }

  async function toggleBlock(userId, block) {
    try {
      await API.blockUser(userId, block);
      NotificationToast.success(block ? 'Пользователь заблокирован' : 'Пользователь разблокирован');
      loadedTabs.users = false;
      loadUsers();
    } catch(e) { NotificationToast.error(e.message); }
  }

  // ── Tests ──────────────────────────────────────────────────────────────────
  async function loadTests() {
    try {
      const res = await API.adminTests();
      const tests = res.tests || [];
      document.getElementById('testsTable').innerHTML = `
        <table>
          <thead><tr><th>ID</th><th>Название</th><th>Вопросы</th><th>Время</th><th>Попытки</th><th>Статус</th><th>Действия</th></tr></thead>
          <tbody>
            ${tests.map(t => `
              <tr>
                <td>#${t.id}</td>
                <td><strong>${e(t.title)}</strong></td>
                <td>${t.question_count}</td>
                <td>${t.time_limit} мин</td>
                <td>${t.max_attempts}</td>
                <td><span class="badge ${t.is_active ? 'badge-success' : 'badge-danger'}">${t.is_active ? 'Активен' : 'Скрыт'}</span></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                  <button class="btn btn-sm btn-ghost" onclick="openAddQuestion(${t.id})">+ Вопрос</button>
                  <button class="btn btn-sm btn-ghost" onclick="toggleTestActive(${t.id}, ${!t.is_active})">${t.is_active ? 'Скрыть' : 'Показать'}</button>
                  <button class="btn btn-sm btn-danger" onclick="deleteTest(${t.id})">Удалить</button>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;
    } catch(err) {
      document.getElementById('testsTable').innerHTML = `<div class="alert alert-error">${err.message}</div>`;
    }
  }

  // Create test form
  document.getElementById('createTestForm').addEventListener('submit', async ev => {
    ev.preventDefault();
    const btn = document.getElementById('createTestBtn');
    setLoading(btn, true);
    try {
      await API.createTest({
        title:            document.getElementById('testTitle').value,
        description:      document.getElementById('testDesc').value,
        time_limit:       parseInt(document.getElementById('testTime').value),
        max_attempts:     parseInt(document.getElementById('testAttempts').value),
        pass_score:       parseInt(document.getElementById('testPassScore').value),
        shuffle_questions: document.getElementById('shuffleQ').checked ? 1 : 0,
        shuffle_answers:   document.getElementById('shuffleA').checked ? 1 : 0,
      });
      NotificationToast.success('Тест создан!');
      closeModal('createTestModal');
      loadedTabs.tests = false;
      loadTests();
    } catch(err) { NotificationToast.error(err.message); }
    finally { setLoading(btn, false); }
  });

  // Import CSV form
  document.getElementById('importForm').addEventListener('submit', async ev => {
    ev.preventDefault();
    
    const fileInput = document.getElementById('csvFile');
    const file = fileInput.files[0];
    
    if (!file) {
      NotificationToast.error('Выберите файл');
      return;
    }
    
    if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
      NotificationToast.error('Только CSV файлы');
      return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    
    // Получаем CSRF токен
    const csrfToken = localStorage.getItem('csrf_token');
    formData.append('csrf_token', csrfToken);
    
    const btn = document.getElementById('importBtn');
    const progress = document.getElementById('importProgress');
    const result = document.getElementById('importResult');
    
    btn.disabled = true;
    btn.textContent = 'Импорт...';
    progress.classList.remove('hidden');
    result.classList.add('hidden');
    result.innerHTML = '';
    
    try {
      const response = await fetch((window.APP_URL || '') + '/api/import.php?action=csv', {
        method: 'POST',
        body: formData,
        credentials: 'include',
      });
      
      const data = await response.json();
      
      if (!data.success) {
        throw new Error(data.message || 'Ошибка импорта');
      }
      
      // Показываем результат
      const stats = data.data || {};
      result.innerHTML = `
        <div class="alert alert-success" style="margin:0;">
          <strong>✅ Импорт завершён!</strong><br>
          Создано тестов: <strong>${stats.tests_created || 0}</strong><br>
          Создано вопросов: <strong>${stats.questions_created || 0}</strong><br>
          Создано ответов: <strong>${stats.answers_created || 0}</strong>
        </div>
      `;
      
      if (stats.errors && stats.errors.length > 0) {
        result.innerHTML += `
          <div class="alert alert-warning" style="margin-top:12px;max-height:200px;overflow-y:auto;">
            <strong>⚠️ Ошибки:</strong><br>
            <ul style="margin:8px 0 0 20px;font-size:.85rem;">
              ${stats.errors.map(e => `<li>${e}</li>`).join('')}
            </ul>
          </div>
        `;
      }
      
      result.classList.remove('hidden');
      fileInput.value = '';
      
      NotificationToast.success('Тесты импортированы!');
      loadedTabs.tests = false;
      
      // Закрываем через 2 секунды если успешно
      setTimeout(() => {
        closeModal('importTestModal');
        loadTests();
      }, 2000);
      
    } catch (err) {
      result.innerHTML = `<div class="alert alert-error" style="margin:0;">${err.message}</div>`;
      result.classList.remove('hidden');
      NotificationToast.error(err.message);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Импортировать';
      progress.classList.add('hidden');
    }
  });

  async function deleteTest(id) {
    if (!confirm('Удалить тест? Все вопросы и результаты будут удалены.')) return;
    try {
      await API.deleteTest(id);
      NotificationToast.success('Тест удалён');
      loadedTabs.tests = false;
      loadTests();
    } catch(e) { NotificationToast.error(e.message); }
  }

  async function toggleTestActive(id, active) {
    try {
      await API.toggleTest(id, active);
      loadedTabs.tests = false;
      loadTests();
    } catch(e) { NotificationToast.error(e.message); }
  }

  // ── Add Question ──────────────────────────────────────────────────────────
  function openAddQuestion(testId) {
    document.getElementById('aqTestId').value = testId;
    document.getElementById('aqText').value = '';
    document.getElementById('aqPoints').value = 1;
    document.getElementById('answersContainer').innerHTML = '';
    addAnswerRow(); addAnswerRow(); addAnswerRow(); addAnswerRow();
    openModal('addQuestionModal');
  }

  function addAnswerRow() {
    const container = document.getElementById('answersContainer');
    const idx = container.children.length;
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;align-items:center;';
    row.innerHTML = `
      <input class="form-control" placeholder="Вариант ответа ${idx+1}" name="ans_text_${idx}" style="flex:1;">
      <label style="display:flex;align-items:center;gap:4px;white-space:nowrap;font-size:.85rem;cursor:pointer;">
        <input type="checkbox" name="ans_correct_${idx}"> Верный
      </label>
      <button type="button" class="btn btn-sm btn-danger" onclick="this.parentNode.remove()">✕</button>
    `;
    container.appendChild(row);
  }

  document.getElementById('addQuestionForm').addEventListener('submit', async ev => {
    ev.preventDefault();
    const testId = document.getElementById('aqTestId').value;
    const rows = document.getElementById('answersContainer').children;
    const answers = [];
    for (const row of rows) {
      const text    = row.querySelector('[name^="ans_text"]').value.trim();
      const correct = row.querySelector('[name^="ans_correct"]').checked;
      if (text) answers.push({ answer_text: text, is_correct: correct ? 1 : 0 });
    }
    if (!answers.length) { NotificationToast.error('Добавьте хотя бы один вариант'); return; }

    const btn = document.getElementById('addQBtn');
    setLoading(btn, true);
    try {
      await API.addQuestion({
        test_id:       parseInt(testId),
        question_text: document.getElementById('aqText').value,
        question_type: document.getElementById('aqType').value,
        points:        parseInt(document.getElementById('aqPoints').value),
        answers,
      });
      NotificationToast.success('Вопрос добавлен!');
      closeModal('addQuestionModal');
      loadedTabs.tests = false;
      loadTests();
    } catch(e) { NotificationToast.error(e.message); }
    finally { setLoading(btn, false); }
  });

  // ── Logs ──────────────────────────────────────────────────────────────────
  async function loadLogs() {
    try {
      const res = await API.adminLogs();
      const logs = res.logs || [];
      document.getElementById('logsTable').innerHTML = `
        <table>
          <thead><tr><th>Время</th><th>Пользователь</th><th>Тест</th><th>Событие</th><th>Важность</th></tr></thead>
          <tbody>
            ${logs.map(l => `
              <tr>
                <td class="text-muted" style="font-size:.8rem;white-space:nowrap;">${new Date(l.created_at).toLocaleString('ru')}</td>
                <td>${e(l.username)}</td>
                <td style="font-size:.85rem;">${e(l.test_title)}</td>
                <td><span class="log-event">${l.event_type}</span></td>
                <td><span class="severity-${l.severity}">${l.severity.toUpperCase()}</span></td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      `;
    } catch(err) {
      document.getElementById('logsTable').innerHTML = `<div class="alert alert-error">${err.message}</div>`;
    }
  }

  // ── Eye-Tracking Data ─────────────────────────────────────────────────────
  async function loadEyeTrackingData() {
    try {
      const testFilter = document.getElementById('eyeTestFilter').value;
      const attemptFilter = document.getElementById('eyeAttemptFilter').value;

      const res = await API.adminEyeTracking({
        test_id: testFilter || null,
        attempt_id: attemptFilter || null
      });
      
      const eyeData = res.data || [];
      
      // Update test filter dropdown
      if (!document.getElementById('eyeTestFilter').dataset.loaded) {
        const tests = res.tests || [];
        const select = document.getElementById('eyeTestFilter');
        tests.forEach(test => {
          const opt = document.createElement('option');
          opt.value = test.id;
          opt.textContent = test.title;
          select.appendChild(opt);
        });
        document.getElementById('eyeTestFilter').dataset.loaded = 'true';
      }

      // Calculate stats
      const stats = calculateEyeTrackingStats(eyeData);
      
      // Display stats summary
      displayEyeStatsSummary(stats);
      
      // Display table
      displayEyeTrackingTable(eyeData);
      
      // Draw visualization
      if (eyeData.length > 0) {
        document.getElementById('eyeVisualization').style.display = 'block';
        drawEyeHeatmap(eyeData);
      }

    } catch(err) {
      document.getElementById('eyeTrackingTable').innerHTML = `<div class="alert alert-error">${err.message}</div>`;
    }
  }

  function calculateEyeTrackingStats(data) {
    let totalFixations = 0;
    let totalDuration = 0;
    let fixationCount = 0;
    const userStats = {};

    data.forEach(log => {
      const eventData = typeof log.event_data === 'string' 
        ? JSON.parse(log.event_data) 
        : log.event_data;
      
      const fixations = eventData.fixations || [];
      const count = eventData.count || fixations.length;
      
      totalFixations += count;
      
      fixations.forEach(fix => {
        if (fix.duration) {
          totalDuration += fix.duration;
          fixationCount++;
        }
      });

      const userId = log.user_id;
      if (!userStats[userId]) {
        userStats[userId] = {
          username: log.username,
          test: log.test_title,
          fixations: 0,
          attempts: 0
        };
      }
      userStats[userId].fixations += count;
      userStats[userId].attempts++;
    });

    return {
      totalFixations,
      avgDuration: fixationCount > 0 ? Math.round(totalDuration / fixationCount) : 0,
      uniqueUsers: Object.keys(userStats).length,
      recordsCount: data.length,
      userStats
    };
  }

  function displayEyeStatsSummary(stats) {
    const container = document.getElementById('eyeStatsSummary');
    
    if (stats.recordsCount === 0) {
      container.innerHTML = `
        <div style="grid-column:1/-1;padding:40px;text-align:center;background:var(--bg-light);border-radius:var(--radius-lg);">
          <div style="font-size:3rem;margin-bottom:12px;">👁️</div>
          <div style="font-size:1.1rem;font-weight:700;color:var(--text-dark);margin-bottom:8px;">Нет данных eye-tracking</div>
          <div style="color:var(--text-gray);font-size:0.9rem;">Данные появятся после прохождения тестов с включённым eye-tracking</div>
        </div>
      `;
      return;
    }

    container.innerHTML = `
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);border:1px solid var(--border-light);">
        <div style="font-size:2rem;font-weight:800;color:var(--gradient-primary);margin-bottom:4px;">${stats.totalFixations.toLocaleString()}</div>
        <div style="font-size:0.8rem;color:var(--text-gray);font-weight:600;">Всего фиксаций</div>
      </div>
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);border:1px solid var(--border-light);">
        <div style="font-size:2rem;font-weight:800;color:#10b981;margin-bottom:4px;">${stats.avgDuration}ms</div>
        <div style="font-size:0.8rem;color:var(--text-gray);font-weight:600;">Средняя длительность</div>
      </div>
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);border:1px solid var(--border-light);">
        <div style="font-size:2rem;font-weight:800;color:#8b5cf6;margin-bottom:4px;">${stats.uniqueUsers}</div>
        <div style="font-size:0.8rem;color:var(--text-gray);font-weight:600;">Уникальных пользователей</div>
      </div>
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);border:1px solid var(--border-light);">
        <div style="font-size:2rem;font-weight:800;color:#f59e0b;margin-bottom:4px;">${stats.recordsCount}</div>
        <div style="font-size:0.8rem;color:var(--text-gray);font-weight:600;">Записей в логе</div>
      </div>
    `;
  }

  function displayEyeTrackingTable(data) {
    const container = document.getElementById('eyeTrackingTable');
    
    if (data.length === 0) {
      container.innerHTML = '';
      return;
    }

    container.innerHTML = `
      <table>
        <thead>
          <tr>
            <th>Время</th>
            <th>Пользователь</th>
            <th>Тест</th>
            <th>Фиксаций</th>
            <th>Средняя длительность</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          ${data.map(log => {
            const eventData = typeof log.event_data === 'string' 
              ? JSON.parse(log.event_data) 
              : log.event_data;
            
            const fixations = eventData.fixations || [];
            const count = eventData.count || fixations.length;
            const avgDuration = fixations.length > 0 
              ? Math.round(fixations.reduce((sum, f) => sum + (f.duration || 0), 0) / fixations.length) 
              : 0;

            return `
              <tr>
                <td style="font-size:.8rem;white-space:nowrap;">${new Date(log.created_at).toLocaleString('ru')}</td>
                <td><strong>${e(log.username)}</strong><br><span class="text-muted" style="font-size:.8rem;">ID: ${log.user_id}</span></td>
                <td style="font-size:.9rem;">${e(log.test_title || '—')}</td>
                <td><span style="background:#ede9fe;color:#7c3aed;padding:4px 10px;border-radius:6px;font-weight:700;font-size:.85rem;">${count}</span></td>
                <td style="font-size:.85rem;">${avgDuration}ms</td>
                <td>
                  <button class="btn btn-sm btn-outline" onclick="viewFixationDetails(${log.id})" style="padding:6px 12px;font-size:.8rem;">
                    <img src="https://img.icons8.com/ios/18/visible.png" alt="" width="18" height="18"> Детали
                  </button>
                </td>
              </tr>
            `;
          }).join('')}
        </tbody>
      </table>
    `;
  }

  function drawEyeHeatmap(data) {
    const canvas = document.getElementById('eyeHeatmapCanvas');
    const ctx = canvas.getContext('2d');
    
    // Clear canvas
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Collect all fixation points
    const points = [];
    data.forEach(log => {
      const eventData = typeof log.event_data === 'string' 
        ? JSON.parse(log.event_data) 
        : log.event_data;
      
      const fixations = eventData.fixations || [];
      fixations.forEach(fix => {
        if (fix.startX && fix.startY) {
          points.push({ x: fix.startX, y: fix.startY, duration: fix.duration || 100 });
        }
      });
    });

    if (points.length === 0) return;

    // Draw heatmap
    points.forEach(point => {
      const radius = Math.max(20, point.duration / 10);
      const alpha = Math.min(0.6, point.duration / 1000);
      
      const gradient = ctx.createRadialGradient(point.x, point.y, 0, point.x, point.y, radius);
      gradient.addColorStop(0, `rgba(99, 102, 241, ${alpha})`);
      gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');
      
      ctx.fillStyle = gradient;
      ctx.beginPath();
      ctx.arc(point.x, point.y, radius, 0, Math.PI * 2);
      ctx.fill();
    });

    // Draw fixation points
    points.forEach(point => {
      ctx.fillStyle = '#6366f1';
      ctx.beginPath();
      ctx.arc(point.x, point.y, 4, 0, Math.PI * 2);
      ctx.fill();
    });
  }

  function viewFixationDetails(logId) {
    NotificationToast.info('Просмотр деталей фиксации #' + logId);
    // TODO: Implement modal with detailed fixation data
  }

  // ── Results ───────────────────────────────────────────────────────────────
  async function loadResults() {
    try {
      const res = await API.adminResults();
      const results = res.results || [];
      document.getElementById('resultsTable').innerHTML = `
        <table>
          <thead><tr><th>Пользователь</th><th>Тест</th><th>Попытка</th><th>%</th><th>Статус</th><th>Честность</th><th>Время</th><th>Дата</th></tr></thead>
          <tbody>
            ${results.map(r => {
              const cheat = parseInt(r.cheat_score);
              const cc = cheat >= 40 ? 'danger' : cheat >= 15 ? 'moderate' : 'safe';
              return `
                <tr>
                  <td><strong>${e(r.username)}</strong><br><span class="text-muted" style="font-size:.8rem;">${e(r.email)}</span></td>
                  <td style="font-size:.9rem;">${e(r.test_title)}</td>
                  <td>${r.attempt_number}</td>
                  <td><strong>${parseFloat(r.percentage).toFixed(1)}%</strong></td>
                  <td><span class="badge ${r.passed==1?'badge-success':'badge-danger'}">${r.passed==1?'Сдан':'Нет'}</span></td>
                  <td><span class="cheat-score ${cc}">${cheat}</span></td>
                  <td class="text-muted" style="font-size:.8rem;">${Math.floor(r.time_spent/60)}м ${r.time_spent%60}с</td>
                  <td class="text-muted" style="font-size:.8rem;">${new Date(r.created_at).toLocaleDateString('ru')}</td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
      `;
    } catch(err) {
      document.getElementById('resultsTable').innerHTML = `<div class="alert alert-error">${err.message}</div>`;
    }
  }

  function e(str) {
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
