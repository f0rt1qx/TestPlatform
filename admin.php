<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title data-i18n="admin.title">Админ-панель — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=4">
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

    /* Force stable theme switch on this page */
    .nav-theme-slot {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 48px !important;
      min-width: 48px !important;
      height: 26px !important;
      margin: 0 0 0 6px !important;
      padding: 0 !important;
      flex: 0 0 48px !important;
    }
    .theme-toggle[data-theme-toggle] {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 48px !important;
      min-width: 48px !important;
      max-width: 48px !important;
      height: 26px !important;
      min-height: 26px !important;
      max-height: 26px !important;
      margin: 0 !important;
      padding: 0 !important;
      border: none !important;
      background: transparent !important;
      box-shadow: none !important;
      transform: none !important;
      transition: none !important;
      overflow: visible !important;
      line-height: 0 !important;
      vertical-align: middle !important;
    }
    .theme-toggle[data-theme-toggle]:hover,
    .theme-toggle[data-theme-toggle]:active,
    .theme-toggle[data-theme-toggle]:focus {
      border: none !important;
      background: transparent !important;
      box-shadow: none !important;
      transform: none !important;
      transition: none !important;
      outline: none !important;
    }
    .theme-toggle[data-theme-toggle] .theme-toggle-track {
      display: block !important;
      position: relative !important;
      width: 48px !important;
      min-width: 48px !important;
      max-width: 48px !important;
      height: 26px !important;
      min-height: 26px !important;
      max-height: 26px !important;
      margin: 0 !important;
      padding: 0 !important;
      border-radius: 999px !important;
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.22), rgba(105, 240, 174, 0.34)) !important;
      border: 1px solid rgba(0, 200, 83, 0.22) !important;
      box-shadow: inset 0 1px 4px rgba(255, 255, 255, 0.22) !important;
      transform: none !important;
      transition: none !important;
      overflow: hidden !important;
    }
    .theme-toggle[data-theme-toggle] .theme-toggle-thumb {
      position: absolute !important;
      top: 50% !important;
      left: 2px !important;
      width: 20px !important;
      height: 20px !important;
      margin: 0 !important;
      border-radius: 50% !important;
      background: linear-gradient(135deg, #ffe082 0%, #fbbf24 100%) !important;
      box-shadow: 0 10px 18px rgba(251, 191, 36, 0.28) !important;
      transform: translateY(-50%) !important;
      transition: left 0.24s cubic-bezier(0.2, 0.8, 0.2, 1), background 0.24s ease, box-shadow 0.24s ease !important;
    }
    [data-theme="dark"] .theme-toggle[data-theme-toggle] .theme-toggle-track {
      background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(14, 116, 144, 0.88)) !important;
      border-color: rgba(125, 211, 252, 0.26) !important;
    }
    [data-theme="dark"] .theme-toggle[data-theme-toggle] .theme-toggle-thumb {
      left: 24px !important;
      background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%) !important;
      box-shadow: 0 10px 18px rgba(96, 165, 250, 0.24) !important;
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
      <li><a href="dashboard.php" data-i18n="nav.dashboard">Кабинет</a></li>
      <li><a href="admin.php" class="active" data-i18n="nav.admin">Админ</a></li>
      <li><a href="#" onclick="AuthManager.logout()" data-i18n="nav.logout">Выйти</a></li>
      <li>
        <div class="lang-selector">
          <select data-language-selector aria-label="Выбор языка"></select>
        </div>
      </li>
      <li class="nav-theme-slot"><button class="theme-toggle" data-theme-toggle title="Тема" data-i18n-title="common.theme"><span class="theme-toggle-track" aria-hidden="true"><span class="theme-toggle-thumb"></span></span></button></li>
    </ul>
    <button class="burger" id="burgerBtn">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<div class="container" style="padding:32px 20px;">
  <div class="page-title"><span>⚙️</span> <span data-i18n="admin.title">Админ-панель</span></div>

  <!-- Tabs -->
  <div class="admin-tabs">
    <button class="admin-tab active" onclick="switchTab('users')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg> <span data-i18n="admin.users">Пользователи</span></button>
    <button class="admin-tab" onclick="switchTab('tests')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg> <span data-i18n="admin.tests">Тесты</span></button>
    <button class="admin-tab" onclick="switchTab('logs')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg> <span data-i18n="admin.logs">Логи</span></button>
    <button class="admin-tab" onclick="switchTab('recordings')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg> <span data-i18n="admin.recordings">Записи</span></button>
    <button class="admin-tab" onclick="switchTab('results')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg> <span data-i18n="admin.results">Результаты</span></button>
  </div>

  <!-- USERS TAB -->
  <div id="tab-users">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3 data-i18n="admin.user-management">Управление пользователями</h3>
    </div>
    <div id="usersTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>

  <!-- TESTS TAB -->
  <div id="tab-tests" class="hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3 data-i18n="admin.test-management">Управление тестами</h3>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn btn-outline" onclick="downloadTemplate()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> <span data-i18n="admin.download-template">Шаблон CSV</span></button>
        <button class="btn btn-primary" onclick="openModal('importTestModal')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg> <span data-i18n="admin.import-csv">Импорт CSV</span></button>
        <button class="btn btn-primary" onclick="openModal('createTestModal')"><span data-i18n="admin.create-test">+ Создать тест</span></button>
      </div>
    </div>
    <div id="testsTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>

  <!-- LOGS TAB -->
  <div id="tab-logs" class="hidden">
    <h3 style="margin-bottom:20px;" data-i18n="admin.logs-title">Логи анти-читинг системы</h3>
    <div id="logsTable" class="table-wrap"><div class="page-loader"><div class="spinner"></div></div></div>
  </div>

  <!-- RECORDINGS TAB -->
  <div id="tab-recordings" class="hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3><i class="fas fa-video"></i> <span data-i18n="admin.recordings-title">Записи экрана</span></h3>
      <a href="recordings.php" class="btn btn-outline" target="_blank">
        <i class="fas fa-external-link-alt"></i> <span data-i18n="admin.recordings-open-full">Открыть в полном экране</span>
      </a>
    </div>
    <div id="recordingsList" class="table-wrap">
      <div style="background:var(--white);border-radius:var(--radius-lg);padding:40px;text-align:center;">
        <i class="fas fa-video" style="font-size:3rem;opacity:0.3;margin-bottom:16px;"></i>
        <p style="color:var(--text-gray);font-weight:600;" data-i18n="common.loading">Загрузка...</p>
      </div>
    </div>
  </div>
  <!-- RESULTS TAB -->
  <div id="tab-results" class="hidden">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <h3 data-i18n="admin.results-title">Все результаты</h3>
      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="api/admin.php?action=export_csv" class="btn btn-outline btn-sm"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg> <span data-i18n="admin.export-csv">Экспорт CSV</span></a>
        <a href="api/admin.php?action=export_pdf" class="btn btn-primary btn-sm" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 0v9.75m0-9.75c0-.621.504-1.125 1.125-1.125h.75c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.75m9-10.125v6.375c0 1.035-.75 1.875-1.688 2.063a48.128 48.128 0 0 1-5.062.469c-.75.075-1.5-.375-1.5-1.125V9.375c0-.75.75-1.2 1.5-1.125 1.688.15 3.375.3 5.063.469.937.187 1.687 1.031 1.687 2.063Z" /></svg> <span data-i18n="admin.export-pdf">Экспорт PDF</span></a>
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

<script src="public/js/config.js?v=3"></script>
<script src="public/js/i18n.js?v=3"></script>
<script src="public/js/app.js?v=3"></script>
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

  const allTabs = ['users','tests','logs','recordings','results'];
  let loadedTabs = {};
  let currentAdminTab = 'users';

  function tr(key, fallback = '') {
    if (!window.i18n || typeof window.i18n.t !== 'function') {
      return fallback || key;
    }

    const translated = window.i18n.t(key);
    if (!translated || translated === key) {
      return fallback || key;
    }

    return translated;
  }

  function switchTab(name) {
    currentAdminTab = name;
    allTabs.forEach(t => {
      document.getElementById('tab-' + t).classList.toggle('hidden', t !== name);
    });
    document.querySelectorAll('.admin-tab').forEach((btn, i) => {
      btn.classList.toggle('active', allTabs[i] === name);
    });
    loadTab(name);
  }

  async function loadTab(tab) {
    currentAdminTab = tab;
    if (loadedTabs[tab]) return;
    loadedTabs[tab] = true;

    if (tab === 'users') await loadUsers();
    if (tab === 'tests') await loadTests();
    if (tab === 'logs')  await loadLogs();
    if (tab === 'recordings') await loadRecordings();

    if (tab === 'results') await loadResults();
  }

  async function rerenderAdminLanguage() {
    loadedTabs[currentAdminTab] = false;
    await loadTab(currentAdminTab);
  }

  window.addEventListener('i18n:changed', () => {
    rerenderAdminLanguage().catch(() => {});
  });

  // ── Users ──────────────────────────────────────────────────────────────────
  async function loadUsers() {
    try {
      const res = await API.adminUsers();
      const users = res.users || [];
      document.getElementById('usersTable').innerHTML = `
        <table>
          <thead><tr><th>ID</th><th>${tr('table.user', 'Пользователь')}</th><th>${tr('table.email', 'Email')}</th><th>${tr('table.role', 'Роль')}</th><th>${tr('table.status', 'Статус')}</th><th>${tr('table.date', 'Дата')}</th><th>${tr('table.actions', 'Действия')}</th></tr></thead>
          <tbody>
            ${users.map(u => `
              <tr>
                <td>#${u.id}</td>
                <td><strong>${e(u.username)}</strong><br><span class="text-muted" style="font-size:.8rem;">${e(u.first_name||'')} ${e(u.last_name||'')}</span></td>
                <td>${e(u.email)}</td>
                <td><span class="badge ${u.role === 'admin' ? 'badge-warning' : 'badge-info'}">${u.role}</span></td>
                <td>
                  ${u.is_blocked ? `<span class="badge badge-danger">${tr('table.block', 'Блок')}</span>` : `<span class="badge badge-success">${tr('admin.active', 'Активен')}</span>`}
                  ${u.email_verified ? '' : `<span class="badge badge-warning" style="margin-left:4px;">${tr('admin.email-unverified', 'Email не подтвержден')}</span>`}
                </td>
                <td class="text-muted" style="font-size:.8rem;">${new Date(u.created_at).toLocaleDateString('ru')}</td>
                <td>
                  <button class="btn btn-sm ${u.is_blocked ? 'btn-success' : 'btn-danger'}"
                          onclick="toggleBlock(${u.id}, ${!u.is_blocked})">
                    ${u.is_blocked ? tr('table.unblock', 'Разблокировать') : tr('table.block', 'Заблокировать')}
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
      NotificationToast.success(block ? tr('admin.user-blocked', 'Пользователь заблокирован') : tr('admin.user-unblocked', 'Пользователь разблокирован'));
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
          <thead><tr><th>ID</th><th>${tr('admin.test-name', 'Название')}</th><th>${tr('admin.questions', 'Вопросы')}</th><th>${tr('table.time', 'Время')}</th><th>${tr('table.attempt', 'Попытки')}</th><th>${tr('table.status', 'Статус')}</th><th>${tr('table.actions', 'Действия')}</th></tr></thead>
          <tbody>
            ${tests.map(t => `
              <tr>
                <td>#${t.id}</td>
                <td><strong>${e(t.title)}</strong></td>
                <td>${t.question_count}</td>
                <td>${t.time_limit} ${tr('tests.meta.minutes', 'мин')}</td>
                <td>${t.max_attempts}</td>
                <td><span class="badge ${t.is_active ? 'badge-success' : 'badge-danger'}">${t.is_active ? tr('admin.active', 'Активен') : tr('admin.hidden', 'Скрыт')}</span></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                  <button class="btn btn-sm btn-ghost" onclick="openAddQuestion(${t.id})">+ ${tr('admin.question', 'Вопрос')}</button>
                  <button class="btn btn-sm btn-ghost" onclick="toggleTestActive(${t.id}, ${!t.is_active})">${t.is_active ? tr('admin.hide', 'Скрыть') : tr('admin.show', 'Показать')}</button>
                  <button class="btn btn-sm btn-danger" onclick="deleteTest(${t.id})">${tr('table.delete', 'Удалить')}</button>
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
      NotificationToast.success(tr('admin.test-created', 'Тест создан'));
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
      NotificationToast.error(tr('admin.choose-file', 'Выберите файл'));
      return;
    }
    
    if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
      NotificationToast.error(tr('admin.only-csv', 'Только CSV файлы'));
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
    btn.textContent = tr('admin.importing', 'Импорт...');
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
        throw new Error(data.message || tr('admin.import-error', 'Ошибка импорта'));
      }
      
      // Показываем результат
      const stats = data.data || {};
      result.innerHTML = `
        <div class="alert alert-success" style="margin:0;">
          <strong>✅ ${tr('admin.import-complete', 'Импорт завершен')}</strong><br>
          ${tr('admin.tests-created', 'Создано тестов')}: <strong>${stats.tests_created || 0}</strong><br>
          ${tr('admin.questions-created', 'Создано вопросов')}: <strong>${stats.questions_created || 0}</strong><br>
          ${tr('admin.answers-created', 'Создано ответов')}: <strong>${stats.answers_created || 0}</strong>
        </div>
      `;
      
      if (stats.errors && stats.errors.length > 0) {
        result.innerHTML += `
          <div class="alert alert-warning" style="margin-top:12px;max-height:200px;overflow-y:auto;">
            <strong>⚠️ ${tr('common.error', 'Ошибки')}:</strong><br>
            <ul style="margin:8px 0 0 20px;font-size:.85rem;">
              ${stats.errors.map(e => `<li>${e}</li>`).join('')}
            </ul>
          </div>
        `;
      }
      
      result.classList.remove('hidden');
      fileInput.value = '';
      
      NotificationToast.success(tr('admin.tests-imported', 'Тесты импортированы'));
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
      btn.textContent = tr('admin.import-button', 'Импортировать');
      progress.classList.add('hidden');
    }
  });

  async function deleteTest(id) {
    if (!confirm(tr('admin.confirm-delete-test', 'Удалить тест? Все вопросы и результаты будут удалены.'))) return;
    try {
      await API.deleteTest(id);
      NotificationToast.success(tr('admin.test-deleted', 'Тест удален'));
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
      <input class="form-control" placeholder="${tr('admin.answer-option', 'Вариант ответа')} ${idx + 1}" name="ans_text_${idx}">
      <label class="answer-correct">
        <input type="checkbox" name="ans_correct_${idx}">
        ${tr('admin.correct', 'Верный')}
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
    if (!answers.length) { NotificationToast.error(tr('admin.add-answer-required', 'Добавьте хотя бы один вариант')); return; }

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
      NotificationToast.success(tr('admin.question-added', 'Вопрос добавлен'));
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
          <thead><tr><th>${tr('table.time', 'Время')}</th><th>${tr('table.user', 'Пользователь')}</th><th>${tr('table.test', 'Тест')}</th><th>${tr('admin.logs-event', 'Событие')}</th><th>${tr('admin.logs-severity', 'Важность')}</th></tr></thead>
          <tbody>
            ${logs.map(l => `
              <tr>
                <td class="text-muted" style="font-size:.8rem;white-space:nowrap;">${new Date(l.created_at).toLocaleString()}</td>
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

  // ── Recordings ────────────────────────────────────────────────────────────
  async function loadRecordings() {
    try {
      // Build headers with JWT token if available
      const headers = {
        'Accept': 'application/json'
      };
      
      // Try to get JWT token from AuthManager
      const token = AuthManager && AuthManager.getToken ? AuthManager.getToken() : null;
      if (token) {
        headers['Authorization'] = `Bearer ${token}`;
      }

      const res = await fetch('api/recordings-list.php', {
        credentials: 'include',
        headers: headers
      });
      const data = await res.json();
      
      if (!data.success) {
        throw new Error(data.message || 'Failed to load recordings');
      }

      const recordings = data.recordings || [];
      const stats = data.stats || {};

      if (recordings.length === 0) {
        document.getElementById('recordingsList').innerHTML = `
          <div style="background:var(--white);border-radius:var(--radius-lg);padding:60px;text-align:center;">
            <i class="fas fa-video-slash" style="font-size:4rem;opacity:0.3;margin-bottom:20px;"></i>
            <p style="color:var(--text-gray);font-weight:600;font-size:1.1rem;">${tr('table.no-records', 'Записей пока нет')}</p>
            <p style="color:var(--text-gray);font-size:0.9rem;margin-top:8px;">${tr('admin.recordings-empty-desc', 'Записи появятся здесь, когда студенты начнут проходить тесты')}</p>
          </div>
        `;
        return;
      }

      let statsHTML = '';
      if (stats.total_recordings) {
        statsHTML = `
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">
            <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);">
              <div style="font-size:2rem;font-weight:800;color:var(--gradient-start);">${stats.total_recordings}</div>
              <div style="font-size:0.85rem;color:var(--text-gray);font-weight:600;">${tr('admin.recordings-total', 'Всего записей')}</div>
            </div>
            <div style="background:var(--white);border-radius:var(--radius-lg);padding:20px;box-shadow:var(--shadow-sm);">
              <div style="font-size:2rem;font-weight:800;color:var(--gradient-start);">${stats.total_size ? Math.round(stats.total_size / 1024 / 1024) + ' MB' : '0 MB'}</div>
              <div style="font-size:0.85rem;color:var(--text-gray);font-weight:600;">${tr('admin.recordings-size', 'Общий размер')}</div>
            </div>
          </div>
        `;
      }

      document.getElementById('recordingsList').innerHTML = `
        ${statsHTML}
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>${tr('admin.recordings-student', 'Студент')}</th>
              <th>${tr('table.test', 'Тест')}</th>
              <th>${tr('admin.recordings-size', 'Размер')}</th>
              <th>${tr('admin.recordings-duration', 'Длительность')}</th>
              <th>${tr('table.date', 'Дата')}</th>
              <th>${tr('table.actions', 'Действия')}</th>
            </tr>
          </thead>
          <tbody>
            ${recordings.slice(0, 10).map(r => `
              <tr>
                <td>#${r.id}</td>
                <td>
                  <div style="font-weight:600;">${e(r.username || tr('admin.recordings-unknown', 'Неизвестно'))}</div>
                  <div style="font-size:0.8rem;color:var(--text-gray);">${e(r.email || '')}</div>
                </td>
                <td>
                  <div style="font-weight:600;">${e(r.test_title || tr('admin.recordings-unknown', 'Неизвестно'))}</div>
                  <div style="font-size:0.8rem;color:var(--text-gray);">${tr('table.attempt', 'Попытка')} #${r.attempt_id}</div>
                </td>
                <td style="color:var(--text-gray);">${r.file_size ? (r.file_size / 1024 / 1024).toFixed(2) + ' MB' : '—'}</td>
                <td style="color:var(--text-gray);font-variant-numeric:tabular-nums;">${r.duration ? Math.round(r.duration / 1000) + tr('admin.recordings-seconds-short', 'с') : '—'}</td>
                <td style="color:var(--text-gray);font-size:0.85rem;">${new Date(r.created_at).toLocaleString()}</td>
                <td>
                  <a href="recordings.php?recording_id=${r.id}" class="btn btn-primary" style="padding:8px 16px;font-size:0.85rem;" target="_blank">
                    <i class="fas fa-play"></i> ${tr('admin.recordings-view', 'Смотреть')}
                  </a>
                </td>
              </tr>
            `).join('')}
          </tbody>
        </table>
        ${recordings.length > 10 ? `
          <div style="text-align:center;padding:20px;">
            <a href="recordings.php" class="btn btn-outline" target="_blank">
              ${tr('admin.recordings-show-all', 'Показать все')} ${recordings.length} ${tr('admin.recordings-items', 'записей')} <i class="fas fa-external-link-alt"></i>
            </a>
          </div>
        ` : ''}
      `;
    } catch(err) {
      document.getElementById('recordingsList').innerHTML = `
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i>
          ${tr('admin.recordings-load-error', 'Ошибка загрузки записей')}: ${err.message}
        </div>
      `;
    }
  }
  // ── Results ───────────────────────────────────────────────────────────────
  async function loadResults() {
    try {
      const res = await API.adminResults();
      const results = res.results || [];
      document.getElementById('resultsTable').innerHTML = `
        <table>
          <thead><tr><th>${tr('table.user', 'Пользователь')}</th><th>${tr('table.test', 'Тест')}</th><th>${tr('table.attempt', 'Попытка')}</th><th>%</th><th>${tr('table.status', 'Статус')}</th><th>${tr('admin.results-honesty', 'Честность')}</th><th>${tr('table.time', 'Время')}</th><th>${tr('table.date', 'Дата')}</th></tr></thead>
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
                  <td><span class="badge ${r.passed==1?'badge-success':'badge-danger'}">${r.passed==1 ? tr('table.passed', 'Сдан') : tr('table.failed', 'Нет')}</span></td>
                  <td><span class="cheat-score ${cc}">${cheat}</span></td>
                  <td class="text-muted" style="font-size:.8rem;">${Math.floor(r.time_spent/60)}${tr('admin.results-minutes-short', 'м')} ${r.time_spent%60}${tr('admin.recordings-seconds-short', 'с')}</td>
                  <td class="text-muted" style="font-size:.8rem;">${new Date(r.created_at).toLocaleDateString()}</td>
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




