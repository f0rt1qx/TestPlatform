<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title>Админ-панель — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=2">
  <style>
    .severity-high   { color: var(--danger);  font-weight:700; }
    .severity-medium { color: var(--warning); font-weight:700; }
    .severity-low    { color: var(--text-gray); }
    .log-event { font-family: monospace; font-size:.85rem; background:var(--bg-light); padding:4px 8px; border-radius:6px; }
    .cheat-score { font-weight: 600; }
    .cheat-score.safe { color: var(--success); }
    .cheat-score.moderate { color: var(--warning); }
    .cheat-score.danger { color: var(--danger); }

    /* Import modal styles */
    .import-warning {
      background: #fffbeb;
      border: 1px solid #fbbf24;
      border-radius: 8px;
      padding: 14px;
      margin-bottom: 16px;
    }
    .import-warning strong { color: #b45309; font-size: .85rem; }
    .import-warning ul { margin: 8px 0 0 20px; color: #92400e; font-size: .85rem; line-height: 1.6; }
    .import-warning code { background: rgba(180,83,0,.1); padding: 1px 5px; border-radius: 4px; font-size: .8rem; }

    [data-theme="dark"] .import-warning {
      background: rgba(251,191,36,.08);
      border-color: rgba(251,191,36,.25);
    }
    [data-theme="dark"] .import-warning strong { color: #fbbf24; }
    [data-theme="dark"] .import-warning ul { color: #d4a054; }
    [data-theme="dark"] .import-warning code { background: rgba(251,191,36,.12); }

    .file-upload-area { position: relative; }
    .file-upload-area input[type="file"] {
      position: absolute;
      inset: 0;
      opacity: 0;
      cursor: pointer;
      width: 100%;
      height: 100%;
    }
    .file-upload-placeholder {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding: 18px 20px;
      border: 2px dashed var(--border);
      border-radius: 10px;
      background: var(--bg-light);
      color: var(--muted);
      font-size: .9rem;
      transition: all .2s ease;
      pointer-events: none;
    }
    .file-upload-placeholder svg { opacity: .5; flex-shrink: 0; }
    .file-upload-area:hover .file-upload-placeholder {
      border-color: var(--primary, #4f46e5);
      background: rgba(79,70,229,.04);
    }
    .file-upload-area:hover .file-upload-placeholder svg { opacity: .8; }
    .file-upload-name {
      padding: 10px 14px;
      background: var(--bg-light);
      border-radius: 8px;
      font-size: .85rem;
      font-weight: 500;
      color: var(--text-dark);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .file-upload-name::before {
      content: '';
      display: inline-block;
      width: 6px;
      height: 6px;
      background: var(--success, #22c55e);
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* Create test modal styles */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 12px;
      margin-bottom: 4px;
    }
    .form-row-2 {
      grid-template-columns: 1fr 1fr;
    }
    .form-checks {
      display: flex;
      gap: 24px;
      padding: 12px 0;
    }
    .form-check {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      font-size: .9rem;
      color: var(--text-gray);
    }
    .form-check input[type="checkbox"] {
      width: 18px;
      height: 18px;
      accent-color: var(--primary, #4f46e5);
      cursor: pointer;
    }
    @media (max-width: 520px) {
      .form-row { grid-template-columns: 1fr; }
      .form-checks { flex-direction: column; gap: 12px; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="container">
    <a href="index.php" class="navbar-brand">
      <img src="src/logo.png" alt="Sapienta logo" width="56" height="56" class="navbar-logo">
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
      <li><button class="theme-toggle" data-theme-toggle title="Тема"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg></button></li>
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
    <button class="admin-tab active" onclick="switchTab('users')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg> Пользователи</button>
    <button class="admin-tab" onclick="switchTab('tests')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg> Тесты</button>
    <button class="admin-tab" onclick="switchTab('logs')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg> Логи</button>
    <button class="admin-tab" onclick="switchTab('eye_tracking')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.964 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg> Eye-tracking</button>
    <button class="admin-tab" onclick="switchTab('results')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg> Результаты</button>
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
        <button class="btn btn-outline" onclick="downloadTemplate()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Шаблон CSV</button>
        <button class="btn btn-primary" onclick="openModal('importTestModal')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg> Импорт CSV</button>
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
        <a href="api/admin.php?action=export_csv" class="btn btn-outline btn-sm"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> Экспорт CSV</a>
        <a href="api/admin.php?action=export_pdf" class="btn btn-primary btn-sm" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 0v9.75m0-9.75c0-.621.504-1.125 1.125-1.125h.75c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.75m9-10.125v6.375c0 1.035-.75 1.875-1.688 2.063a48.128 48.128 0 0 1-5.062.469c-.75.075-1.5-.375-1.5-1.125V9.375c0-.75.75-1.2 1.5-1.125 1.688.15 3.375.3 5.063.469.937.187 1.687 1.031 1.687 2.063Z" /></svg> Экспорт PDF</a>
      </div>
    </div>
    <div id="resultsTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>
</div>

<!-- MODAL: Create Test -->
<div class="modal-overlay hidden" id="createTestModal">
  <div class="modal" style="max-width:680px;">
    <div class="modal-header">
      <div class="modal-title">Создать новый тест</div>
      <button class="modal-close" onclick="closeModal('createTestModal')">✕</button>
    </div>
    <form id="createTestForm">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Название</label>
          <input class="form-control" id="testTitle" placeholder="Введите название теста" required>
        </div>
        <div class="form-group">
          <label class="form-label">Описание</label>
          <textarea class="form-control" id="testDesc" rows="3" placeholder="Краткое описание теста..."></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
              Время (мин)
            </label>
            <input class="form-control" id="testTime" type="number" value="30" min="1" max="300">
          </div>
          <div class="form-group">
            <label class="form-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182" /></svg>
              Попытки
            </label>
            <input class="form-control" id="testAttempts" type="number" value="1" min="1" max="10">
          </div>
          <div class="form-group">
            <label class="form-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
              Проходной %
            </label>
            <input class="form-control" id="testPassScore" type="number" value="60" min="1" max="100">
          </div>
        </div>

        <div class="form-checks">
          <label class="form-check">
            <input type="checkbox" id="shuffleQ" checked>
            <span>Перемешивать вопросы</span>
          </label>
          <label class="form-check">
            <input type="checkbox" id="shuffleA" checked>
            <span>Перемешивать ответы</span>
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('createTestModal')">Отмена</button>
        <button type="submit" class="btn btn-primary" id="createTestBtn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Создать тест
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: Import CSV -->
<style>
  #importTestModal .modal {
    max-width: 720px !important;
    max-height: none !important;
    overflow: visible !important;
  }
  @media (max-width: 768px) {
    #importTestModal .modal {
      max-width: calc(100vw - 32px) !important;
    }
  }
</style>
<div class="modal-overlay hidden" id="importTestModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Импорт теста из CSV</div>
      <button class="modal-close" onclick="closeModal('importTestModal')">✕</button>
    </div>

    <div class="modal-body">
      <p class="text-muted" style="font-size:.9rem;margin-bottom:16px;">
        Загрузите CSV файл с вопросами и ответами. Каждый вопрос должен быть в отдельной строке.
      </p>

      <div style="background:var(--bg-light);border:1px solid var(--border-light);border-radius:8px;padding:14px;margin-bottom:16px;">
        <strong style="font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);">Формат CSV:</strong>
        <code style="display:block;font-size:.75rem;color:var(--text-gray);margin-top:8px;word-break:break-all;line-height:1.5;">
          test_title, test_description, time_limit, max_attempts, pass_score, question_text, question_type, points, answer_text, is_correct
        </code>
      </div>

      <div class="import-warning">
        <strong>⚠️ Важно:</strong>
        <ul>
          <li>Файл должен быть в формате CSV (UTF-8)</li>
          <li>Все строки одного теста должны идти подряд</li>
          <li><code>question_type</code>: <code>single</code> (один ответ) или <code>multiple</code> (несколько)</li>
          <li><code>is_correct</code>: <code>1</code> для правильного ответа, <code>0</code> для неправильного</li>
        </ul>
      </div>

      <form id="importForm" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Выберите CSV файл</label>
          <div class="file-upload-area">
            <input type="file" id="csvFile" accept=".csv" required>
            <div class="file-upload-placeholder">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
              <span>Нажмите для выбора файла</span>
            </div>
            <div class="file-upload-name hidden"></div>
          </div>
        </div>

        <div id="importProgress" class="hidden" style="text-align:center;padding:20px;">
          <div class="spinner" style="width:32px;height:32px;border-width:3px;margin:0 auto;"></div>
          <p class="text-muted" style="margin-top:12px;">Импорт тестов...</p>
        </div>

        <div id="importResult" class="hidden" style="margin-top:16px;"></div>
      </form>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('importTestModal')">Отмена</button>
      <button type="submit" class="btn btn-primary" id="importBtn" form="importForm">Импортировать</button>
    </div>
  </div>
</div>

<!-- MODAL: Add Question -->
<style>
  #addQuestionModal .modal {
    max-width: 700px !important;
    max-height: none !important;
    overflow: visible !important;
  }
  @media (max-width: 768px) {
    #addQuestionModal .modal { max-width: calc(100vw - 32px) !important; }
  }

  .answer-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    animation: fadeIn 0.2s ease;
  }
  .answer-row .form-control { flex: 1; margin: 0; }
  .answer-correct {
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    font-size: .85rem;
    color: var(--text-gray);
    cursor: pointer;
    flex-shrink: 0;
  }
  .answer-correct input { accent-color: var(--primary, #4f46e5); cursor: pointer; width: 16px; height: 16px; }
  .answer-remove {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    background: var(--bg-light);
    color: var(--text-gray);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all .2s ease;
    font-size: 1rem;
  }
  .answer-remove:hover { background: var(--danger); color: #fff; }
  .answers-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
  }
  .btn-add-answer {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: .85rem;
    border-radius: 6px;
    border: 1px dashed var(--border);
    background: none;
    color: var(--text-gray);
    cursor: pointer;
    transition: all .2s ease;
  }
  .btn-add-answer:hover {
    border-color: var(--primary, #4f46e5);
    color: var(--primary, #4f46e5);
    background: rgba(79,70,229,.04);
  }
  .btn-add-answer svg { flex-shrink: 0; }
</style>
<div class="modal-overlay hidden" id="addQuestionModal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Добавить вопрос</div>
      <button class="modal-close" onclick="closeModal('addQuestionModal')">✕</button>
    </div>
    <form id="addQuestionForm">
      <input type="hidden" id="aqTestId">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Вопрос</label>
          <textarea class="form-control" id="aqText" rows="3" required placeholder="Текст вопроса..."></textarea>
        </div>
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" /></svg>
              Тип вопроса
            </label>
            <select class="form-control" id="aqType">
              <option value="single">Один правильный</option>
              <option value="multiple">Несколько правильных</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="display:inline;vertical-align:middle;margin-right:4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
              Баллов
            </label>
            <input class="form-control" id="aqPoints" type="number" value="1" min="1">
          </div>
        </div>

        <div class="answers-header">
          <label class="form-label" style="margin:0;">Варианты ответов</label>
          <button type="button" class="btn-add-answer" onclick="addAnswerRow()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Добавить
          </button>
        </div>
        <div id="answersContainer"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addQuestionModal')">Отмена</button>
        <button type="submit" class="btn btn-primary" id="addQBtn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          Добавить
        </button>
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

  // Show filename on select
  document.getElementById('csvFile').addEventListener('change', function() {
    const placeholder = this.closest('.file-upload-area').querySelector('.file-upload-placeholder');
    const nameEl = this.closest('.file-upload-area').querySelector('.file-upload-name');
    if (this.files.length > 0) {
      placeholder.classList.add('hidden');
      nameEl.classList.remove('hidden');
      nameEl.textContent = this.files[0].name;
    } else {
      placeholder.classList.remove('hidden');
      nameEl.classList.add('hidden');
    }
  });

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
    row.className = 'answer-row';
    row.innerHTML = `
      <input class="form-control" placeholder="Вариант ответа ${idx + 1}" name="ans_text_${idx}">
      <label class="answer-correct">
        <input type="checkbox" name="ans_correct_${idx}">
        Верный
      </label>
      <button type="button" class="answer-remove" onclick="this.closest('.answer-row').remove()">✕</button>
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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.964 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg> Детали
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
