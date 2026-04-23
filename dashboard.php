<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title data-i18n="nav.dashboard">Личный кабинет — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=4">
  <style>
    /* === Dashboard layout: fixed sidebar + main content === */
    body {
      overflow-x: hidden;
    }
    .dashboard-layout {
      display: block !important;
      margin-top: 0 !important;
      min-height: 100vh;
    }
    .sidebar {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 260px !important;
      height: 100vh !important;
      display: flex !important;
      flex-direction: column !important;
      padding: 0 !important;
      z-index: 100 !important;
      border-right: 1px solid var(--border-light, #e2e8f0) !important;
      background: var(--white, #ffffff) !important;
      overflow: hidden !important;
      box-shadow: 2px 0 8px rgba(0,0,0,0.04);
    }
    .sidebar-brand {
      padding: 20px;
      border-bottom: 1px solid var(--border-light, #e2e8f0);
      flex-shrink: 0;
    }
    .sidebar-brand a {
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      color: inherit;
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sidebar-brand-text {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.25rem;
      color: var(--gradient-start, #00c853);
      max-width: 0;
      overflow: hidden;
      white-space: nowrap;
      opacity: 0;
      transition: max-width 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                  opacity 0.25s ease,
                  margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
      margin-left: 0;
    }
    .sidebar-brand a:hover .sidebar-brand-text {
      max-width: 150px;
      opacity: 1;
      margin-left: 12px;
    }
    .sidebar-logo {
      border-radius: 8px;
      flex-shrink: 0;
      transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sidebar-brand a:hover .sidebar-logo {
      transform: translateX(-12px);
    }
    .sidebar-menu {
      flex: 1;
      padding: 16px 12px;
      overflow-y: auto;
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .sidebar-menu a {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 18px;
      border-radius: var(--radius-md, 12px);
      color: var(--text-gray, #64748b);
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s;
    }
    .sidebar-menu a:hover {
      background: var(--bg-light, #f1f5f9);
      color: var(--gradient-start, #00c853);
    }
    .sidebar-menu a.active {
      background: rgba(0, 200, 83, 0.1);
      color: var(--gradient-start, #00c853);
    }
    .sidebar-footer {
      padding: 16px 12px;
      border-top: 1px solid var(--border-light, #e2e8f0);
      display: flex;
      flex-direction: column;
      gap: 8px;
      flex-shrink: 0;
    }
    .sidebar-footer-item {
      width: 100%;
    }
    .sidebar-logout {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px 18px;
      border-radius: var(--radius-md, 12px);
      color: #ef4444;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.2s;
      cursor: pointer;
    }
    .sidebar-logout:hover {
      background: rgba(239, 68, 68, 0.1);
      color: #dc2626;
    }
    .sidebar-footer .theme-toggle {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 14px 16px;
      border-radius: 16px;
      border: 1px solid rgba(0, 200, 83, 0.12);
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.05), rgba(105, 240, 174, 0.08));
      color: var(--text-dark, #1e293b);
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.22s ease, border-color 0.22s ease, color 0.22s ease, box-shadow 0.22s ease;
      position: relative;
      overflow: hidden;
      transform: none !important;
    }
    .sidebar-footer .theme-toggle:hover {
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.08), rgba(105, 240, 174, 0.14));
      color: var(--gradient-start, #00c853);
      border-color: rgba(0, 200, 83, 0.24);
      box-shadow: 0 12px 24px rgba(0, 200, 83, 0.10);
      transform: none !important;
    }
    .sidebar-footer .theme-toggle:active {
      transform: none !important;
    }
    .sidebar-footer .theme-toggle::before {
      display: none;
    }
    .sidebar-footer .theme-toggle::after {
      display: none;
    }
    .sidebar-theme-copy {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 2px;
      min-width: 0;
      position: relative;
      z-index: 2;
    }
    .sidebar-theme-title {
      font-size: 0.95rem;
      font-weight: 700;
      color: inherit;
    }
    .sidebar-theme-state {
      font-size: 0.78rem;
      color: var(--text-gray, #64748b);
      font-weight: 600;
      transition: color 0.2s ease;
    }
    .sidebar-theme-switch {
      width: 62px;
      height: 34px;
      border-radius: 999px;
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.22), rgba(105, 240, 174, 0.34));
      border: 1px solid rgba(0, 200, 83, 0.22);
      position: relative;
      flex-shrink: 0;
      box-shadow: inset 0 1px 4px rgba(255, 255, 255, 0.28), 0 8px 18px rgba(0, 200, 83, 0.16);
      transition: background 0.24s ease, border-color 0.24s ease, box-shadow 0.24s ease;
    }
    .sidebar-theme-switch::before {
      content: '';
      position: absolute;
      inset: 4px;
      border-radius: inherit;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.28), rgba(255, 255, 255, 0.08));
    }
    .sidebar-theme-switch::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 6px;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      transform: translateY(-50%);
      background: linear-gradient(135deg, #ffe082 0%, #fbbf24 100%);
      box-shadow: 0 10px 18px rgba(251, 191, 36, 0.24);
      transition: left 0.24s cubic-bezier(0.2, 0.8, 0.2, 1), background 0.24s ease, box-shadow 0.24s ease;
    }
    [data-theme="dark"] .sidebar-theme-state {
      color: var(--text-gray, #94a3b8);
    }
    [data-theme="dark"] .sidebar-footer .theme-toggle {
      border-color: rgba(148, 163, 184, 0.14);
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.55), rgba(30, 41, 59, 0.72));
      color: var(--text-dark, #f1f5f9);
      box-shadow: none;
    }
    [data-theme="dark"] .sidebar-footer .theme-toggle:hover {
      background: linear-gradient(135deg, rgba(30, 41, 59, 0.88), rgba(51, 65, 85, 0.92));
      border-color: rgba(96, 165, 250, 0.26);
      color: #93c5fd;
    }
    [data-theme="dark"] .sidebar-theme-switch {
      background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(14, 116, 144, 0.88));
      border-color: rgba(125, 211, 252, 0.26);
      box-shadow: inset 0 1px 4px rgba(255, 255, 255, 0.05), 0 8px 18px rgba(14, 165, 233, 0.18);
    }
    [data-theme="dark"] .sidebar-theme-switch::before {
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.03));
    }
    [data-theme="dark"] .sidebar-theme-switch::after {
      left: 34px;
      background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%);
      box-shadow: 0 10px 18px rgba(96, 165, 250, 0.22);
    }
    .sidebar-footer .lang-selector {
      width: 100%;
      padding: 8px 0;
    }
    .sidebar-footer .lang-selector select {
      width: 100%;
      padding: 8px 12px;
      border-radius: var(--radius-md, 12px);
      border: 1px solid var(--border-light, #e2e8f0);
      background: var(--bg-gray, #f8fafc);
      color: var(--text, #1e293b);
      font-size: 0.875rem;
      cursor: pointer;
    }
    .dashboard-main {
      margin-left: 260px;
      padding: 40px;
      min-height: 100vh;
      background: var(--bg-gray, #f8fafc);
    }

    /* Welcome banner */
    .welcome-banner {
      background: var(--gradient-primary, linear-gradient(135deg, #00c853 0%, #69f0ae 100%));
      border-radius: var(--radius-xl, 16px);
      padding: 32px;
      color: #fff;
      margin-bottom: 32px;
      position: relative;
      overflow: hidden;
      box-shadow: var(--shadow-lg, 0 4px 24px rgba(0,0,0,0.1));
    }
    .welcome-banner::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -10%;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    .welcome-banner h1 {
      margin: 0 0 8px 0;
      font-size: 1.8rem;
    }
    .welcome-banner p {
      opacity: 0.95;
      margin: 0;
      color: rgba(255, 255, 255, 0.95);
    }

    /* Stat boxes */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }
    .stat-box {
      text-align: center;
      padding: 32px 24px;
      background: var(--white, #ffffff);
      border-radius: var(--radius-lg, 12px);
      box-shadow: var(--shadow-md, 0 2px 12px rgba(0,0,0,0.06));
      border: 1px solid var(--border-light, #e2e8f0);
      transition: all 0.2s;
    }
    .stat-box:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg, 0 8px 24px rgba(0,0,0,0.1));
    }
    .stat-box .stat-value {
      font-size: 2rem;
      font-weight: 800;
      color: var(--gradient-start, #00c853);
      margin-bottom: 8px;
      line-height: 1.2;
    }
    .stat-box .stat-label {
      color: var(--text-gray, #64748b);
      font-size: 0.9rem;
      font-weight: 500;
    }

    /* Test grid overrides */
    .test-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
      margin-top: 0;
    }
    .test-card {
      background: var(--white, #ffffff);
      border-radius: var(--radius-lg, 12px);
      padding: 24px;
      box-shadow: var(--shadow-md, 0 2px 12px rgba(0,0,0,0.06));
      border: 1px solid var(--border-light, #e2e8f0);
      transition: all 0.2s;
      display: flex;
      flex-direction: column;
    }
    .test-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg, 0 8px 24px rgba(0,0,0,0.1));
      border-color: var(--gradient-start, #00c853);
    }
    .test-card-header {
      display: flex;
      gap: 16px;
      margin-bottom: 16px;
    }
    .test-card-icon {
      width: 56px;
      height: 56px;
      border-radius: var(--radius-md, 12px);
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.1) 0%, rgba(105, 240, 174, 0.15) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .test-card-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-dark, #1e293b);
      margin-bottom: 4px;
    }
    .test-card-desc {
      color: var(--text-gray, #64748b);
      font-size: 0.85rem;
      line-height: 1.5;
    }
    .test-card-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin-top: 16px;
      padding-top: 16px;
      border-top: 1px solid var(--border-light, #e2e8f0);
    }
    .test-meta-item {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 0.85rem;
      color: var(--text-gray, #64748b);
    }
    .test-card-actions {
      margin-top: auto;
      padding-top: 20px;
    }

    /* Empty state */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      background: var(--white, #ffffff);
      border-radius: var(--radius-lg, 12px);
      border: 1px dashed var(--border-light, #e2e8f0);
    }
    .empty-state-icon {
      color: var(--text-gray, #94a3b8);
      margin-bottom: 16px;
    }
    .empty-state h3 {
      margin: 12px 0 8px;
      color: var(--text-dark, #1e293b);
    }
    .empty-state p {
      color: var(--text-gray, #64748b);
    }

    /* PDF download button */
    .btn-pdf {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
      padding: 6px 12px;
      border-radius: 8px;
      border: 1px solid var(--border-light, #e2e8f0);
      background: var(--bg-gray, #f8fafc);
      color: var(--text-dark, #1e293b);
      font-size: 0.8rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
      white-space: nowrap;
    }
    .btn-pdf:hover {
      background: var(--gradient-start, #00c853);
      border-color: var(--gradient-start, #00c853);
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0, 200, 83, 0.3);
    }
    [data-theme="dark"] .btn-pdf {
      background: #0f172a;
      border-color: #334155;
      color: #e2e8f0;
    }
    [data-theme="dark"] .btn-pdf:hover {
      background: var(--gradient-start, #00c853);
      border-color: var(--gradient-start, #00c853);
      color: #fff;
    }

    /* Dark theme */
    [data-theme="dark"] .sidebar {
      background: #1e293b !important;
      border-right-color: #334155 !important;
    }
    [data-theme="dark"] .sidebar-brand {
      border-bottom-color: #334155;
    }
    [data-theme="dark"] .sidebar-footer {
      border-top-color: #334155;
    }
    [data-theme="dark"] .sidebar-footer .lang-selector select {
      border-color: #334155;
      background: #0f172a;
      color: #e2e8f0;
    }
    [data-theme="dark"] .stat-box {
      background: #1e293b;
      border-color: #334155;
    }
    [data-theme="dark"] .test-card {
      background: #1e293b;
      border-color: #334155;
    }
    [data-theme="dark"] .empty-state {
      background: #1e293b;
      border-color: #334155;
    }

    /* Mobile: hide sidebar, show burger */
    @media (max-width: 1024px) {
      .sidebar {
        transform: translateX(-100%) !important;
        transition: transform 0.3s ease;
      }
      .sidebar.open {
        transform: translateX(0) !important;
      }
      .dashboard-main {
        margin-left: 0 !important;
      }
      .burger {
        display: flex !important;
        position: fixed;
        top: 16px;
        left: 16px;
        z-index: 200;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        border: none;
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(12px);
        cursor: pointer;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
      }
      .burger span {
        display: block;
        width: 20px;
        height: 2px;
        background: white;
        border-radius: 2px;
        transition: all 0.3s;
      }
      .burger.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
      }
      .burger.active span:nth-child(2) {
        opacity: 0;
      }
      .burger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(5px, -5px);
      }
    }

    @media (min-width: 1025px) {
      .burger {
        display: none !important;
      }
    }

    @media (max-width: 768px) {
      .dashboard-main {
        padding: 24px 16px !important;
      }
      .welcome-banner {
        padding: 24px;
      }
      .welcome-banner h1 {
        font-size: 1.4rem;
      }
      .stat-box {
        padding: 24px;
      }
    }
  </style>
</head>
<body>

<!-- Mobile Burger Button -->
<button class="burger" id="burgerBtn" aria-label="Menu">
  <span></span>
  <span></span>
  <span></span>
</button>

<div class="dashboard-layout">
  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <a href="index.php">
        <img src="src/logogreen.png" alt="Sapienta logo" width="40" height="40" class="sidebar-logo">
        <span class="sidebar-brand-text">Sapienta</span>
      </a>
    </div>

    <ul class="sidebar-menu">
      <li><a href="#" class="active" onclick="showTab('overview'); return false;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg> <span data-i18n="dashboard.overview">Обзор</span></a></li>
      <li><a href="#" onclick="showTab('tests'); return false;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg> <span data-i18n="dashboard.tests">Тесты</span></a></li>
      <li><a href="#" onclick="showTab('history'); return false;"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> <span data-i18n="dashboard.history">История</span></a></li>
      <li><a href="profile.php"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg> <span data-i18n="nav.profile">Профиль</span></a></li>
      <li data-admin class="hidden"><a href="admin.php"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg> <span data-i18n="nav.admin">Админ</span></a></li>
    </ul>

    <div class="sidebar-footer">
      <div class="sidebar-footer-item">
        <div class="lang-selector">
          <select data-language-selector aria-label="Выбор языка"></select>
        </div>
      </div>
      <div class="sidebar-footer-item">
        <button class="theme-toggle" data-theme-toggle title="Тема">
          <span class="sidebar-theme-copy">
            <span class="sidebar-theme-title">Тема</span>
            <span class="sidebar-theme-state" data-theme-state-label>Светлая</span>
          </span>
          <span class="sidebar-theme-switch" aria-hidden="true"></span>
        </button>
      </div>
      <div class="sidebar-footer-item">
        <a href="#" class="sidebar-logout" onclick="AuthManager.logout(); return false;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
          Выйти
        </a>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="dashboard-main">

    <!-- TAB: OVERVIEW -->
    <div id="tab-overview">
      <div class="welcome-banner">
        <h1 data-i18n="dashboard.welcome">Добро пожаловать, <span data-username>Пользователь</span>! 👋</h1>
        <p data-i18n="dashboard.ready">Готовы проверить свои знания?</p>
      </div>

      <h2 style="margin-bottom: 24px;" data-i18n="dashboard.stats.title">Ваша статистика</h2>
      <div class="stats-row" id="statsRow">
        <div class="stat-box">
          <div class="stat-value" id="statTests">—</div>
          <div class="stat-label" data-i18n="dashboard.stats.tests">Тестов пройдено</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="statAvg">—%</div>
          <div class="stat-label" data-i18n="dashboard.stats.avg">Средний балл</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="statBest">—%</div>
          <div class="stat-label" data-i18n="dashboard.stats.best">Лучший результат</div>
        </div>
        <div class="stat-box">
          <div class="stat-value" id="statTime">—ч</div>
          <div class="stat-label" data-i18n="dashboard.stats.time">Время в тестах</div>
        </div>
      </div>

      <h2 style="margin: 40px 0 24px;" data-i18n="dashboard.recent.title">Последние результаты</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th data-i18n="table.test">Тест</th>
              <th data-i18n="table.date">Дата</th>
              <th data-i18n="table.score">Баллы</th>
              <th data-i18n="table.percent">%</th>
              <th data-i18n="table.status">Статус</th>
            </tr>
          </thead>
          <tbody id="recentResults">
            <tr>
              <td colspan="5" class="text-center" style="color: var(--text-gray);" data-i18n="table.no-results">Пока нет результатов</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- TAB: TESTS -->
    <div id="tab-tests" class="hidden">
      <div class="page-header">
        <h1 class="page-title" data-i18n="dashboard.tests.title">Доступные тесты</h1>
        <p class="page-subtitle" data-i18n="dashboard.tests.subtitle">Выберите тест для прохождения</p>
      </div>

      <div class="test-grid" id="testGrid">
        <div class="text-center" style="grid-column: 1/-1; padding: 60px 20px;">
          <div class="spinner" style="width: 40px; height: 40px; margin: 0 auto;"></div>
          <p class="text-muted mt-2" data-i18n="common.loading">Загрузка тестов...</p>
        </div>
      </div>
    </div>

    <!-- TAB: HISTORY -->
    <div id="tab-history" class="hidden">
      <div class="page-header">
        <h1 class="page-title" data-i18n="dashboard.history.title">История прохождений</h1>
        <p class="page-subtitle" data-i18n="dashboard.history.subtitle">Все ваши попытки прохождения тестов</p>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th data-i18n="table.test">Тест</th>
              <th data-i18n="table.attempt">Попытка</th>
              <th data-i18n="table.date">Дата</th>
              <th data-i18n="table.score">Баллы</th>
              <th data-i18n="table.percent">%</th>
              <th data-i18n="table.status">Статус</th>
              <th data-i18n="table.time">Время</th>
              <th data-i18n="table.actions">Действия</th>
            </tr>
          </thead>
          <tbody id="historyTable">
            <tr>
              <td colspan="8" class="text-center" style="color: var(--text-gray);" data-i18n="table.no-records">Пока нет записей</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<div class="NotificationToast-container" id="toastContainer"></div>

<script src="public/js/config.js?v=3"></script>
<script src="public/js/i18n.js?v=3"></script>
<script src="public/js/app.js?v=3"></script>
<script>
  (async () => {
    if (AuthManager.isLoggedIn()) return;
    try {
      const me = await API.getMe();
      if (me && me.success && me.user) {
        AuthManager.saveUser(me.user);
        return;
      }
    } catch (_) {}
    window.location.href = 'login.php?redirect=' + encodeURIComponent(location.href);
  })();

  let currentTab = 'overview';
  let allResults = [];

  document.addEventListener('DOMContentLoaded', () => {
    AuthManager.updateNavbar();
    loadOverview();
  });

  function showTab(tab) {
    document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
    const links = document.querySelectorAll('.sidebar-menu a');
    links.forEach(link => {
      const onclick = link.getAttribute('onclick');
      if (onclick && onclick.includes(`showTab('${tab}')`)) {
        link.classList.add('active');
      }
    });

    document.querySelectorAll('.dashboard-main > div').forEach(d => d.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    currentTab = tab;

    if (tab === 'tests') loadTests();
    if (tab === 'history') loadHistory();
    if (tab === 'overview') loadOverview();

    // Re-apply translations after tab switch
    if (typeof i18n !== 'undefined' && i18n.translatePage) {
      setTimeout(() => i18n.translatePage(), 50);
    }

    // Close mobile sidebar after tab change
    if (window.innerWidth <= 1024) {
      const burger = document.getElementById('burgerBtn');
      const sb = document.querySelector('.sidebar');
      if (burger) burger.classList.remove('active');
      if (sb) sb.classList.remove('open');
    }

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

      const tPassed = i18n.t('table.passed');
      const tFailed = i18n.t('table.failed');
      const tNoResults = i18n.t('table.no-results');

      if (recent.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center" style="color: var(--text-gray);">${tNoResults}</td></tr>`;
      } else {
        tbody.innerHTML = recent.map(r => `
          <tr>
            <td><strong>${escapeHtml(r.test_title)}</strong></td>
            <td class="text-muted" style="font-size: 0.9rem;">${new Date(r.created_at).toLocaleDateString('ru')}</td>
            <td>${r.score}/${r.max_score}</td>
            <td><strong>${parseFloat(r.percentage).toFixed(1)}%</strong></td>
            <td><span class="badge ${r.passed == 1 ? 'badge-success' : 'badge-danger'}">${r.passed == 1 ? tPassed : tFailed}</span></td>
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
            <div class="empty-state-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg></div>
            <h3 data-i18n="dashboard.no-tests">Тестов пока нет</h3>
            <p data-i18n="dashboard.no-tests-desc">Обратитесь к администратору для добавления тестов</p>
          </div>
        `;
        if (typeof i18n !== 'undefined') i18n.translatePage();
        return;
      }

      const tMin = i18n.t('tests.meta.minutes');
      const tQ = i18n.t('tests.meta.questions');
      const tA = i18n.t('tests.meta.attempts');
      const tStart = i18n.t('dashboard.start-test');
      const tNoDesc = i18n.t('common.loading');

      grid.innerHTML = tests.map(t => `
        <div class="test-card">
          <div class="test-card-header">
            <div class="test-card-icon">${getTestIcon(t.title)}</div>
            <div>
              <div class="test-card-title">${escapeHtml(t.title)}</div>
              <div class="test-card-desc">${escapeHtml(t.description || tNoDesc)}</div>
            </div>
          </div>
          <div class="test-card-meta">
            <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> ${t.time_limit} ${tMin}</span>
            <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 0 1-9 9m9-9a9 9 0 0 0-9-9m9 9H3m9 9a9 9 0 0 1-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 0 1 9-9" /></svg> ${t.question_count} ${tQ}</span>
            <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg> ${t.max_attempts} ${tA}</span>
          </div>
          <div class="test-card-actions">
            <button class="btn btn-primary btn-full" onclick="startTest(${t.id})">${tStart} →</button>
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

      const tPassed = i18n.t('table.passed');
      const tFailed = i18n.t('table.failed');
      const tNoRecords = i18n.t('table.no-records');
      const tPdf = i18n.t('common.download');

      if (results.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center" style="color: var(--text-gray);">${tNoRecords}</td></tr>`;
        return;
      }

      tbody.innerHTML = results.map(r => `
        <tr>
          <td><strong>${escapeHtml(r.test_title)}</strong></td>
          <td>#${r.attempt_number}</td>
          <td class="text-muted" style="font-size: 0.9rem;">${new Date(r.created_at).toLocaleDateString('ru')}</td>
          <td>${r.score}/${r.max_score}</td>
          <td><strong>${parseFloat(r.percentage).toFixed(1)}%</strong></td>
          <td><span class="badge ${r.passed == 1 ? 'badge-success' : 'badge-danger'}">${r.passed == 1 ? tPassed : tFailed}</span></td>
          <td class="text-muted" style="font-size: 0.9rem;">${formatTime(r.time_spent)}</td>
          <td>
            <a href="api/test.php?action=export_pdf&attempt_id=${r.attempt_id}"
               class="btn btn-pdf"
               target="_blank"
               title="${tPdf} PDF">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14" style="display: inline-block; vertical-align: middle; margin-right: 4px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg> PDF
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
      'математ': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm2.252-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm2.504 4.5h.008v.008h-.008v-.008Zm-7.5 0h.008v.008h-.008V18Zm0-2.25h.008v.008h-.008v-.008Zm0-2.25h.008v.008h-.008v-.008ZM9.75 3.75h4.5a2.25 2.25 0 0 1 2.25 2.25v12a2.25 2.25 0 0 1-2.25 2.25h-4.5a2.25 2.25 0 0 1-2.25-2.25v-12A2.25 2.25 0 0 1 9.75 3.75ZM12 15.75h.008v.008H12v-.008Z" /></svg>',
      'информат': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" /></svg>',
      'русск': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.466.729-3.558" /></svg>',
      'истор': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
      'физ': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>',
      'хим': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714a2.25 2.25 0 0 0 .659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-2.47 2.47a2.25 2.25 0 0 1-1.59.659H9.06a2.25 2.25 0 0 1-1.59-.659L5 14.5m14 0V9.75A2.25 2.25 0 0 0 16.75 7.5h-9.5A2.25 2.25 0 0 0 5 9.75v4.75m0 0h14M5 14.5V19.5a2.25 2.25 0 0 0 2.25 2.25h9.5A2.25 2.25 0 0 0 19 19.5v-5" /></svg>',
      'био': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714a2.25 2.25 0 0 0 .659 1.591L19 14.5M14.25 3.104c.251.023.501.05.75.082M19 14.5l-2.47 2.47a2.25 2.25 0 0 1-1.59.659H9.06a2.25 2.25 0 0 1-1.59-.659L5 14.5m14 0V19.5a2.25 2.25 0 0 1-2.25 2.25H9.75A2.25 2.25 0 0 1 7.5 19.5v-5" /></svg>',
      'геометр': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" /></svg>',
      'геогр': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.466.729-3.558" /></svg>',
      'алгебр': '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm2.252-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm2.504 4.5h.008v.008h-.008v-.008Zm-7.5 0h.008v.008h-.008V18Zm0-2.25h.008v.008h-.008v-.008Zm0-2.25h.008v.008h-.008v-.008ZM9.75 3.75h4.5a2.25 2.25 0 0 1 2.25 2.25v12a2.25 2.25 0 0 1-2.25 2.25h-4.5a2.25 2.25 0 0 1-2.25-2.25v-12A2.25 2.25 0 0 1 9.75 3.75ZM12 15.75h.008v.008H12v-.008Z" /></svg>',
    };
    const lower = title.toLowerCase();
    for (const [key, icon] of Object.entries(icons)) {
      if (lower.includes(key)) return icon;
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="32" height="32"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>';
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

  // Burger menu for mobile
  const burgerBtn = document.getElementById('burgerBtn');
  const sidebar = document.querySelector('.sidebar');

  if (burgerBtn && sidebar) {
    burgerBtn.addEventListener('click', () => {
      burgerBtn.classList.toggle('active');
      sidebar.classList.toggle('open');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 1024 && sidebar.classList.contains('open')) {
        if (!sidebar.contains(e.target) && e.target !== burgerBtn) {
          burgerBtn.classList.remove('active');
          sidebar.classList.remove('open');
        }
      }
    });
  }
</script>
</body>
</html>
