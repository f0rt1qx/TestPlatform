<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title>Профиль — Sapienta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=4">
  <style>
    /* === Profile top-bar === */
    body {
      overflow-x: hidden;
    }
    .profile-topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 64px;
      background: var(--white, #ffffff);
      border-bottom: 1px solid var(--border-light, #e2e8f0);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      z-index: 1000;
      box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    /* Left: Logo */
    .topbar-brand {
      display: flex;
      align-items: center;
      flex-shrink: 0;
    }
    .topbar-brand a {
      display: flex;
      align-items: center;
      text-decoration: none;
      color: inherit;
      transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .topbar-brand-text {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 1.15rem;
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
    .topbar-brand a:hover .topbar-brand-text {
      max-width: 150px;
      opacity: 1;
      margin-left: 10px;
    }
    .topbar-logo {
      border-radius: 8px;
      flex-shrink: 0;
      transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .topbar-brand a:hover .topbar-logo {
      transform: translateX(-12px);
    }
    /* Center: spacer */
    .topbar-center {
      flex: 1;
    }

    /* Header hide/show on scroll */
    .profile-topbar {
      transition: transform 0.3s ease;
    }
    .profile-topbar.navbar-hidden {
      transform: translateY(-100%);
    }
    /* Right: Actions */
    .topbar-actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-shrink: 0;
    }
    .topbar-back {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: var(--radius-md, 12px);
      border: 1px solid var(--border-light, #e2e8f0);
      background: transparent;
      color: var(--text, #1e293b);
      font-size: 0.875rem;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: all 0.2s;
    }
    .topbar-back:hover {
      background: var(--bg-light, #f1f5f9);
      border-color: var(--gradient-start, #00c853);
      color: var(--gradient-start, #00c853);
    }
    .topbar-theme-btn {
      margin-left: 0;
    }
    .topbar-theme-btn:hover {
      transform: none;
    }
    .topbar-theme-slot {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 48px !important;
      min-width: 48px !important;
      height: 26px !important;
      padding: 0 !important;
      margin: 0 !important;
      flex-shrink: 0 !important;
    }
    .topbar-theme-btn[data-theme-toggle] {
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
    .topbar-theme-btn[data-theme-toggle]:hover,
    .topbar-theme-btn[data-theme-toggle]:active,
    .topbar-theme-btn[data-theme-toggle]:focus {
      border: none !important;
      background: transparent !important;
      box-shadow: none !important;
      transform: none !important;
      transition: none !important;
      outline: none !important;
    }
    .topbar-theme-btn[data-theme-toggle] .theme-toggle-track {
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
    .topbar-theme-btn[data-theme-toggle] .theme-toggle-thumb {
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
    /* Profile content offset */
    .profile-content {
      padding-top: 64px;
    }

    /* Dark theme */
    [data-theme="dark"] .profile-topbar {
      background: #1e293b;
      border-bottom-color: #334155;
    }
    [data-theme="dark"] .topbar-username {
      color: #e2e8f0;
    }
    [data-theme="dark"] .topbar-dropdown {
      background: #1e293b;
      border-color: #334155;
    }
    [data-theme="dark"] .topbar-dropdown a {
      color: #e2e8f0;
    }
    [data-theme="dark"] .topbar-dropdown a:hover {
      background: #0f172a;
    }
    [data-theme="dark"] .topbar-dropdown a.logout {
      border-top-color: #334155;
    }
    [data-theme="dark"] .topbar-back {
      border-color: #334155;
      color: #e2e8f0;
    }
    [data-theme="dark"] .topbar-back:hover {
      background: #0f172a;
      border-color: var(--gradient-start, #00c853);
    }
    [data-theme="dark"] .topbar-theme-btn {
      color: #e2e8f0;
    }
    [data-theme="dark"] .topbar-theme-btn:hover {
      transform: none;
    }
    [data-theme="dark"] .topbar-theme-btn[data-theme-toggle] .theme-toggle-track {
      background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(14, 116, 144, 0.88)) !important;
      border-color: rgba(125, 211, 252, 0.26) !important;
    }
    [data-theme="dark"] .topbar-theme-btn[data-theme-toggle] .theme-toggle-thumb {
      left: 24px !important;
      background: linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%) !important;
      box-shadow: 0 10px 18px rgba(96, 165, 250, 0.24) !important;
    }

    /* Mobile */
    @media (max-width: 768px) {
      .profile-topbar {
        padding: 0 12px;
      }
      .topbar-back span {
        display: none;
      }
      .topbar-back {
        padding: 8px 10px;
      }
    }
    .birthdate-group {
      position: relative;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    #birthDate {
      min-height: 52px;
      letter-spacing: 0.02em;
      font-variant-numeric: tabular-nums;
      padding-right: 56px;
    }

    .birthdate-trigger {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      width: 38px;
      height: 38px;
      border: 1px solid rgba(0, 200, 83, 0.18);
      border-radius: 12px;
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.10), rgba(105, 240, 174, 0.18));
      color: var(--gradient-start);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .birthdate-trigger:hover {
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.18), rgba(105, 240, 174, 0.28));
      border-color: rgba(0, 200, 83, 0.35);
      transform: translateY(-50%) scale(1.03);
    }

    .birthdate-trigger svg {
      width: 18px;
      height: 18px;
    }

    .birthdate-popup {
      position: absolute;
      top: calc(100% + 10px);
      left: 0;
      z-index: 60;
      width: 320px;
      padding: 16px;
      background: var(--white);
      border: 1px solid rgba(0, 200, 83, 0.10);
      border-radius: 20px;
      box-shadow: 0 22px 48px rgba(0, 0, 0, 0.12), 0 10px 24px rgba(0, 200, 83, 0.10);
      opacity: 0;
      visibility: hidden;
      pointer-events: none;
      transform: translateY(10px) scale(0.96);
      transform-origin: top left;
      transition: opacity 0.22s ease, transform 0.24s cubic-bezier(0.2, 0.8, 0.2, 1), visibility 0.22s ease;
    }

    .birthdate-popup.open {
      opacity: 1;
      visibility: visible;
      pointer-events: auto;
      transform: translateY(0) scale(1);
    }

    .birthdate-popup-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 14px;
    }

    .birthdate-popup-title {
      font-weight: 700;
      color: var(--text-dark);
      font-size: 1rem;
    }

    .birthdate-popup-controls {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 8px;
      justify-content: center;
      position: relative;
    }

    .birthdate-chip {
      min-width: 0;
      height: 38px;
      padding: 0 12px;
      border-radius: 12px;
      border: 1px solid var(--border-light);
      background: var(--white);
      color: var(--text-dark);
      font-weight: 600;
      outline: none;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      cursor: pointer;
    }

    .birthdate-chip.month {
      flex: 1;
    }

    .birthdate-chip.year {
      width: 92px;
      flex-shrink: 0;
    }

    .birthdate-chip:hover,
    .birthdate-chip.is-open {
      border-color: var(--gradient-start);
      box-shadow: 0 0 0 3px rgba(0, 200, 83, 0.08);
    }

    .birthdate-chip svg {
      width: 14px;
      height: 14px;
      transition: transform 0.2s ease;
    }

    .birthdate-chip.is-open svg {
      transform: rotate(180deg);
    }

    .birthdate-overlay-list {
      position: absolute;
      top: 48px;
      background: var(--white);
      border: 1px solid var(--border-light);
      border-radius: 16px;
      box-shadow: 0 16px 34px rgba(0, 0, 0, 0.12), 0 8px 18px rgba(0, 200, 83, 0.08);
      padding: 8px;
      opacity: 0;
      visibility: hidden;
      transform: translateY(8px) scale(0.96);
      pointer-events: none;
      transition: opacity 0.18s ease, transform 0.22s ease, visibility 0.18s ease;
      z-index: 5;
    }

    .birthdate-overlay-list.open {
      opacity: 1;
      visibility: visible;
      transform: translateY(0) scale(1);
      pointer-events: auto;
    }

    .birthdate-overlay-list.months {
      left: 0;
      width: 180px;
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 6px;
    }

    .birthdate-overlay-list.years {
      right: 0;
      width: 108px;
      max-height: 220px;
      overflow-y: auto;
      display: grid;
      gap: 6px;
    }

    .birthdate-overlay-item {
      min-height: 34px;
      border: none;
      border-radius: 10px;
      background: transparent;
      color: var(--text-dark);
      font-weight: 600;
      cursor: pointer;
      transition: all 0.18s ease;
      padding: 0 10px;
      text-align: center;
    }

    .birthdate-overlay-item:hover {
      background: rgba(0, 200, 83, 0.08);
      color: var(--gradient-start);
    }

    .birthdate-overlay-item.is-active {
      background: var(--gradient-primary);
      color: #fff;
      box-shadow: 0 8px 16px rgba(0, 200, 83, 0.18);
    }

    .birthdate-nav {
      width: 36px;
      height: 36px;
      border-radius: 12px;
      border: 1px solid var(--border-light);
      background: var(--white);
      color: var(--text-dark);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .birthdate-nav:hover {
      border-color: var(--gradient-start);
      color: var(--gradient-start);
      background: rgba(0, 200, 83, 0.06);
    }

    .birthdate-weekdays,
    .birthdate-days {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 6px;
    }

    .birthdate-weekday {
      text-align: center;
      font-size: 0.78rem;
      font-weight: 700;
      color: var(--text-gray);
      padding: 6px 0;
    }

    .birthdate-day {
      height: 38px;
      border: none;
      border-radius: 12px;
      background: transparent;
      color: var(--text-dark);
      font-weight: 600;
      cursor: pointer;
      transition: all 0.18s ease;
    }

    .birthdate-day:hover:not(:disabled) {
      background: rgba(0, 200, 83, 0.08);
      color: var(--gradient-start);
    }

    .birthdate-day.is-today {
      background: rgba(0, 200, 83, 0.10);
      color: var(--gradient-start);
    }

    .birthdate-day.is-selected {
      background: var(--gradient-primary);
      color: #fff;
      box-shadow: 0 10px 18px rgba(0, 200, 83, 0.20);
    }

    .birthdate-day.is-other-month {
      color: var(--text-light);
    }

    .birthdate-day:disabled {
      opacity: 0.35;
      cursor: not-allowed;
    }

    .birthdate-popup-footer {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 14px;
    }

    .birthdate-footer-btn {
      flex: 1;
      height: 40px;
      border-radius: 12px;
      border: 1px solid var(--border-light);
      background: var(--white);
      color: var(--text-dark);
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .birthdate-footer-btn:hover {
      border-color: var(--gradient-start);
      color: var(--gradient-start);
    }

    [data-theme="dark"] .birthdate-trigger {
      border-color: rgba(105, 240, 174, 0.18);
      background: linear-gradient(135deg, rgba(0, 200, 83, 0.12), rgba(105, 240, 174, 0.12));
    }

    [data-theme="dark"] .birthdate-popup,
    [data-theme="dark"] .birthdate-nav,
    [data-theme="dark"] .birthdate-footer-btn,
    [data-theme="dark"] .birthdate-chip,
    [data-theme="dark"] .birthdate-overlay-list {
      background: var(--white);
      border-color: var(--border-light);
    }
  </style>
</head>
<body>

<!-- TOP BAR -->
<header class="profile-topbar" id="topbar">
  <!-- Left: Brand -->
  <div class="topbar-brand">
    <a href="index.php">
      <img src="src/logogreen.png" alt="Sapienta logo" width="36" height="36" class="topbar-logo">
      <span class="topbar-brand-text">Sapienta</span>
    </a>
  </div>

  <!-- Center: spacer -->
  <div class="topbar-center"></div>

  <!-- Right: Actions -->
  <div class="topbar-actions">
    <a href="dashboard.php" class="topbar-back">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
      <span>РљР°Р±РёРЅРµС‚</span>
    </a>
    <div class="topbar-theme-slot">
      <button class="topbar-theme-btn" data-theme-toggle title="РўРµРјР°">
        <span class="theme-toggle-track" aria-hidden="true"><span class="theme-toggle-thumb"></span></span>
      </button>
    </div>
  </div>
</header>

<div class="profile-content">
<div class="container" style="padding: 32px 20px; max-width: 1200px;">

  <!-- Header -->
  <div class="profile-header">
    <div class="profile-avatar-section">
      <img src="" alt="Avatar" class="profile-avatar-large" id="headerAvatar">
      <div class="profile-info">
        <h1 id="headerName">Р—Р°РіСЂСѓР·РєР°...</h1>
        <div class="role-badge" id="headerRole"></div>
        <p class="text-muted mt-2" id="headerBio" style="opacity: 0.9;"></p>
      </div>
    </div>
    <div class="profile-stats">
      <div class="stat-card">
        <div class="value" id="statAttempts">0</div>
        <div class="label">РџРѕРїС‹С‚РѕРє</div>
      </div>
      <div class="stat-card">
        <div class="value" id="statPassed">0</div>
        <div class="label">Сдано тестов</div>
      </div>
      <div class="stat-card">
        <div class="value" id="statAvg">0%</div>
        <div class="label">Средний балл</div>
      </div>
      <div class="stat-card">
        <div class="value" id="statTime">0С‡</div>
        <div class="label">Р’СЂРµРјСЏ РІ С‚РµСЃС‚Р°С…</div>
      </div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="profile-tabs">
    <button class="profile-tab active" onclick="switchTab('info')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a1.05 1.05 0 0 0 1.055 1.313l2.836-.708a.75.75 0 0 1 .852 1.063l-.02.041c-.293.585-.852.985-1.497 1.095l-2.777.47a2.25 2.25 0 0 1-2.12-1.066l-1.292-2.583a2.25 2.25 0 0 1 1.066-3.045l2.583-1.292a2.25 2.25 0 0 1 1.633.015Zm-4.5-4.5L6 6m4.5-4.5L12 3m4.5 4.5L18 9M6 6l-.75.75m10.5-.75L18 6m-12 0L6 7.5M6 6l.75.75m10.5-.75L18 7.5" /></svg> РРЅС„РѕСЂРјР°С†РёСЏ</button>
    <button class="profile-tab" onclick="switchTab('security')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg> Р‘РµР·РѕРїР°СЃРЅРѕСЃС‚СЊ</button>
    <button class="profile-tab" onclick="switchTab('avatar')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.311-10.311a1.125 1.125 0 1 1 1.591 1.591 1.125 1.125 0 0 1-1.591-1.591Z" /></svg> РђРІР°С‚Р°СЂРєР°</button>
    <button class="profile-tab" onclick="switchTab('achievements')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a1.125 1.125 0 0 0 1.125-1.125V5.625m-6.125 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v9.75m-6.125 0a1.125 1.125 0 0 1-1.125-1.125V5.625m0 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v1.5m-1.5-1.5h3m-3 0a1.125 1.125 0 0 0-1.125 1.125v1.5m4.5-1.5v1.5m-4.5 0h3" /></svg> Р”РѕСЃС‚РёР¶РµРЅРёСЏ</button>
    <button class="profile-tab" onclick="switchTab('activity')"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg> РђРєС‚РёРІРЅРѕСЃС‚СЊ</button>
  </div>

  <!-- Tab: Info -->
  <div id="tab-info" class="profile-section">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg> Р›РёС‡РЅР°СЏ РёРЅС„РѕСЂРјР°С†РёСЏ</div>
    <form id="profileForm">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">РРјСЏ</label>
          <input class="form-control" id="firstName" placeholder="РРІР°РЅ">
        </div>
        <div class="form-group">
          <label class="form-label">Р¤Р°РјРёР»РёСЏ</label>
          <input class="form-control" id="lastName" placeholder="РРІР°РЅРѕРІ">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Рћ СЃРµР±Рµ</label>
        <textarea class="form-control" id="bio" rows="3" placeholder="Расскажите немного о себе..."></textarea>
        <div class="form-hint">РњР°РєСЃРёРјСѓРј 500 СЃРёРјРІРѕР»РѕРІ</div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">РўРµР»РµС„РѕРЅ</label>
          <input class="form-control" id="phone" placeholder="+7 (999) 000-00-00">
        </div>
        <div class="form-group">
          <label class="form-label">Р“РѕСЂРѕРґ</label>
          <input class="form-control" id="city" placeholder="РњРѕСЃРєРІР°">
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Р’РµР±-СЃР°Р№С‚</label>
          <input class="form-control" id="website" placeholder="https://mysite.com">
        </div>
        <div class="form-group">
          <label class="form-label">Р”Р°С‚Р° СЂРѕР¶РґРµРЅРёСЏ</label>
          <div class="birthdate-group">
            <input class="form-control" type="text" id="birthDate" placeholder="Р”Р”.РњРњ.Р“Р“Р“Р“" inputmode="numeric" maxlength="10">
            <button type="button" class="birthdate-trigger" id="birthDateTrigger" aria-label="Р’С‹Р±СЂР°С‚СЊ РґР°С‚Сѓ">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4m8-4v4M3 10h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/>
              </svg>
            </button>
            <input type="hidden" id="birthDateIso">
            <div class="birthdate-popup" id="birthDatePopup">
              <div class="birthdate-popup-header">
                <button type="button" class="birthdate-nav" id="birthDatePrev" aria-label="РџСЂРµРґС‹РґСѓС‰РёР№ РјРµСЃСЏС†">вЂ№</button>
                <div class="birthdate-popup-controls">
                  <button type="button" class="birthdate-chip month" id="birthDateMonthChip">
                    <span id="birthDateMonthLabel"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                  </button>
                  <button type="button" class="birthdate-chip year" id="birthDateYearChip">
                    <span id="birthDateYearLabel"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                  </button>
                  <div class="birthdate-overlay-list months" id="birthDateMonthList"></div>
                  <div class="birthdate-overlay-list years" id="birthDateYearList"></div>
                </div>
                <button type="button" class="birthdate-nav" id="birthDateNext" aria-label="Следующий месяц">вЂє</button>
              </div>
              <div class="birthdate-weekdays">
                <div class="birthdate-weekday">РџРЅ</div>
                <div class="birthdate-weekday">Р’С‚</div>
                <div class="birthdate-weekday">Ср</div>
                <div class="birthdate-weekday">Р§С‚</div>
                <div class="birthdate-weekday">РџС‚</div>
                <div class="birthdate-weekday">Сб</div>
                <div class="birthdate-weekday">Р’СЃ</div>
              </div>
              <div class="birthdate-days" id="birthDateDays"></div>
              <div class="birthdate-popup-footer">
                <button type="button" class="birthdate-footer-btn" id="birthDateClear">РћС‡РёСЃС‚РёС‚СЊ</button>
                <button type="button" class="birthdate-footer-btn" id="birthDateToday">Сегодня</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">VK РїСЂРѕС„РёР»СЊ</label>
          <input class="form-control" id="socialVk" placeholder="https://vk.com/username">
        </div>
        <div class="form-group">
          <label class="form-label">Telegram</label>
          <input class="form-control" id="socialTg" placeholder="@username">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" id="saveProfileBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> Сохранить</button>
    </form>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #e2e8f0;">

    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg> РљРѕРЅС‚Р°РєС‚РЅР°СЏ РёРЅС„РѕСЂРјР°С†РёСЏ</div>
    <form id="contactForm">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">РРјСЏ РїРѕР»СЊР·РѕРІР°С‚РµР»СЏ</label>
          <input class="form-control" id="username" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" id="email" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" id="saveContactBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> Сохранить</button>
    </form>
  </div>

  <!-- Tab: Security -->
  <div id="tab-security" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg> Смена пароля</div>
    <form id="passwordForm" style="max-width: 500px;">
      <div class="form-group">
        <label class="form-label">РўРµРєСѓС‰РёР№ РїР°СЂРѕР»СЊ</label>
        <input class="form-control" type="password" id="currentPassword" required>
      </div>
      <div class="form-group">
        <label class="form-label">РќРѕРІС‹Р№ РїР°СЂРѕР»СЊ</label>
        <input class="form-control" type="password" id="newPassword" required>
        <div class="form-hint">РњРёРЅРёРјСѓРј 8 СЃРёРјРІРѕР»РѕРІ</div>
      </div>
      <div class="form-group">
        <label class="form-label">РџРѕРґС‚РІРµСЂР¶РґРµРЅРёРµ РїР°СЂРѕР»СЏ</label>
        <input class="form-control" type="password" id="confirmPassword" required>
      </div>
      <button type="submit" class="btn btn-primary" id="changePasswordBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg> РР·РјРµРЅРёС‚СЊ РїР°СЂРѕР»СЊ</button>
    </form>
  </div>

  <!-- Tab: Avatar -->
  <div id="tab-avatar" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.311-10.311a1.125 1.125 0 1 1 1.591 1.591 1.125 1.125 0 0 1-1.591-1.591Z" /></svg> РЈРїСЂР°РІР»РµРЅРёРµ Р°РІР°С‚Р°СЂРєРѕР№</div>
    <div class="avatar-upload">
      <img src="" alt="Avatar" class="avatar-preview" id="avatarPreview">
      <div class="avatar-actions">
        <button class="btn btn-primary" onclick="document.getElementById('avatarInput').click()"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg> Р’С‹Р±СЂР°С‚СЊ С„Р°Р№Р»</button>
        <button class="btn btn-danger" id="removeAvatarBtn"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg> РЈРґР°Р»РёС‚СЊ</button>
        <input type="file" id="avatarInput" class="hidden-input" accept="image/*">
        <div class="form-hint">JPG, PNG, GIF, WebP. РњР°РєСЃ. 5MB</div>
      </div>
    </div>
    <div id="avatarProgress" class="hidden" style="margin-top: 16px;">
      <div class="spinner"></div>
      <span class="text-muted" style="margin-left: 12px;">Р—Р°РіСЂСѓР·РєР°...</span>
    </div>
  </div>

  <!-- Tab: Achievements -->
  <div id="tab-achievements" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a1.125 1.125 0 0 0 1.125-1.125V5.625m-6.125 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v9.75m-6.125 0a1.125 1.125 0 0 1-1.125-1.125V5.625m0 0a1.125 1.125 0 0 1 1.125-1.125h1.5a1.125 1.125 0 0 1 1.125 1.125v1.5m-1.5-1.5h3m-3 0a1.125 1.125 0 0 0-1.125 1.125v1.5m4.5-1.5v1.5m-4.5 0h3" /></svg> Р’Р°С€Рё РґРѕСЃС‚РёР¶РµРЅРёСЏ</div>
    <div class="achievements-grid" id="achievementsGrid"></div>
  </div>

  <!-- Tab: Activity -->
  <div id="tab-activity" class="profile-section hidden">
    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg> РђРєС‚РёРІРЅРѕСЃС‚СЊ Р·Р° <span id="activityDays">30</span> РґРЅРµР№</div>
    <div class="activity-heatmap" id="activityHeatmap"></div>

    <hr style="margin: 32px 0; border: none; border-top: 1px solid #e2e8f0;">

    <div class="section-title"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg> РџРѕСЃР»РµРґРЅРёРµ СЂРµР·СѓР»СЊС‚Р°С‚С‹</div>
    <table class="results-table">
      <thead>
        <tr>
          <th>РўРµСЃС‚</th>
          <th>РџРѕРїС‹С‚РєР°</th>
          <th>Р‘Р°Р»Р»С‹</th>
          <th>%</th>
          <th>Статус</th>
          <th>Р”Р°С‚Р°</th>
        </tr>
      </thead>
      <tbody id="recentResults"></tbody>
    </table>
  </div>

</div>

<div class="NotificationToast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js?v=2"></script>
<script src="public/js/app.js?v=2"></script>
<script>
  if (!AuthManager.isLoggedIn()) {
    window.location.href = 'login.php?redirect=' + encodeURIComponent(location.href);
  }

  let profile = {};
  let currentTab = 'info';

  function formatIsoToDisplay(isoDate) {
    if (!isoDate) return '';
    const parts = isoDate.split('-');
    if (parts.length !== 3) return isoDate;
    return `${parts[2]}.${parts[1]}.${parts[0]}`;
  }

  function formatDisplayToIso(displayDate) {
    if (!displayDate) return '';
    const parts = displayDate.split('.');
    if (parts.length !== 3) return '';
    return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
  }

  function applyBirthDateMask(value) {
    const digits = String(value || '').replace(/\D/g, '').slice(0, 8);
    let out = '';
    if (digits.length > 0) out += digits.slice(0, 2);
    if (digits.length >= 3) out += '.' + digits.slice(2, 4);
    if (digits.length >= 5) out += '.' + digits.slice(4, 8);
    return out;
  }

  function isValidDisplayDate(displayDate) {
    if (!/^\d{2}\.\d{2}\.\d{4}$/.test(displayDate)) return false;
    const [day, month, year] = displayDate.split('.').map(Number);
    const date = new Date(year, month - 1, day);
    return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
  }

  function syncBirthDateFieldsFromDisplay() {
    const displayInput = document.getElementById('birthDate');
    const isoInput = document.getElementById('birthDateIso');
    const displayValue = displayInput.value.trim();

    if (isValidDisplayDate(displayValue)) {
      isoInput.value = formatDisplayToIso(displayValue);
    } else if (!displayValue) {
      isoInput.value = '';
    }
  }

  function initBirthDateControls() {
    const displayInput = document.getElementById('birthDate');
    const isoInput = document.getElementById('birthDateIso');
    const trigger = document.getElementById('birthDateTrigger');
    const popup = document.getElementById('birthDatePopup');
    const days = document.getElementById('birthDateDays');
    const monthChip = document.getElementById('birthDateMonthChip');
    const yearChip = document.getElementById('birthDateYearChip');
    const monthLabel = document.getElementById('birthDateMonthLabel');
    const yearLabel = document.getElementById('birthDateYearLabel');
    const monthList = document.getElementById('birthDateMonthList');
    const yearList = document.getElementById('birthDateYearList');
    const prevBtn = document.getElementById('birthDatePrev');
    const nextBtn = document.getElementById('birthDateNext');
    const clearBtn = document.getElementById('birthDateClear');
    const todayBtn = document.getElementById('birthDateToday');

    if (!displayInput || !isoInput || !trigger || !popup || !days || !monthChip || !yearChip || !monthLabel || !yearLabel || !monthList || !yearList || !prevBtn || !nextBtn || !clearBtn || !todayBtn || displayInput.dataset.maskReady === '1') return;
    displayInput.dataset.maskReady = '1';

    const monthNames = ['РЇРЅРІР°СЂСЊ', 'Р¤РµРІСЂР°Р»СЊ', 'РњР°СЂС‚', 'РђРїСЂРµР»СЊ', 'РњР°Р№', 'РСЋРЅСЊ', 'РСЋР»СЊ', 'РђРІРіСѓСЃС‚', 'Сентябрь', 'РћРєС‚СЏР±СЂСЊ', 'РќРѕСЏР±СЂСЊ', 'Р”РµРєР°Р±СЂСЊ'];
    const calendarState = {
      viewDate: new Date(),
      selectedIso: ''
    };
    const today = new Date();
    const maxYear = today.getFullYear();
    const minYear = maxYear - 120;

    function closeOverlayLists() {
      monthList.classList.remove('open');
      yearList.classList.remove('open');
      monthChip.classList.remove('is-open');
      yearChip.classList.remove('is-open');
    }

    function populateCalendarLists() {
      monthList.innerHTML = monthNames
        .map((name, index) => `<button type="button" class="birthdate-overlay-item" data-month="${index}">${name}</button>`)
        .join('');

      let yearsHtml = '';
      for (let year = maxYear; year >= minYear; year--) {
        yearsHtml += `<button type="button" class="birthdate-overlay-item" data-year="${year}">${year}</button>`;
      }
      yearList.innerHTML = yearsHtml;
    }

    function openPopup() {
      popup.classList.add('open');
      renderCalendar();
    }

    function closePopup() {
      popup.classList.remove('open');
    }

    function setSelectedIso(isoValue) {
      calendarState.selectedIso = isoValue || '';
      isoInput.value = calendarState.selectedIso;
      displayInput.value = formatIsoToDisplay(calendarState.selectedIso);
      if (calendarState.selectedIso) {
        const [year, month] = calendarState.selectedIso.split('-').map(Number);
        calendarState.viewDate = new Date(year, month - 1, 1);
      }
      renderCalendar();
    }

    function renderCalendar() {
      const year = calendarState.viewDate.getFullYear();
      const month = calendarState.viewDate.getMonth();
      monthLabel.textContent = monthNames[month];
      yearLabel.textContent = String(year);

      monthList.querySelectorAll('[data-month]').forEach((item) => {
        item.classList.toggle('is-active', parseInt(item.dataset.month, 10) === month);
      });
      yearList.querySelectorAll('[data-year]').forEach((item) => {
        item.classList.toggle('is-active', parseInt(item.dataset.year, 10) === year);
      });

      const firstDay = new Date(year, month, 1);
      const startWeekDay = (firstDay.getDay() + 6) % 7;
      const firstGridDate = new Date(year, month, 1 - startWeekDay);
      const todayIso = new Date().toISOString().split('T')[0];

      let html = '';
      for (let i = 0; i < 42; i++) {
        const current = new Date(firstGridDate);
        current.setDate(firstGridDate.getDate() + i);

        const iso = `${current.getFullYear()}-${String(current.getMonth() + 1).padStart(2, '0')}-${String(current.getDate()).padStart(2, '0')}`;
        const isOtherMonth = current.getMonth() !== month;
        const isToday = iso === todayIso;
        const isSelected = iso === calendarState.selectedIso;
        const disabled = current > new Date();

        html += `<button type="button" class="birthdate-day${isOtherMonth ? ' is-other-month' : ''}${isToday ? ' is-today' : ''}${isSelected ? ' is-selected' : ''}" data-iso="${iso}" ${disabled ? 'disabled' : ''}>${current.getDate()}</button>`;
      }

      days.innerHTML = html;
    }

    displayInput.addEventListener('input', () => {
      const masked = applyBirthDateMask(displayInput.value);
      displayInput.value = masked;
      syncBirthDateFieldsFromDisplay();
      if (isValidDisplayDate(masked)) {
        calendarState.selectedIso = formatDisplayToIso(masked);
        const [year, month] = calendarState.selectedIso.split('-').map(Number);
        calendarState.viewDate = new Date(year, month - 1, 1);
        renderCalendar();
      }
    });

    displayInput.addEventListener('blur', () => {
      const value = displayInput.value.trim();
      if (value && !isValidDisplayDate(value)) {
        NotificationToast.error('Р’РІРµРґРёС‚Рµ РґР°С‚Сѓ РІ С„РѕСЂРјР°С‚Рµ Р”Р”.РњРњ.Р“Р“Р“Р“');
      }
      syncBirthDateFieldsFromDisplay();
    });

    trigger.addEventListener('click', (e) => {
      e.stopPropagation();
      if (popup.classList.contains('open')) {
        closePopup();
        closeOverlayLists();
      } else {
        openPopup();
      }
    });

    prevBtn.addEventListener('click', () => {
      calendarState.viewDate = new Date(calendarState.viewDate.getFullYear(), calendarState.viewDate.getMonth() - 1, 1);
      renderCalendar();
    });

    nextBtn.addEventListener('click', () => {
      const candidate = new Date(calendarState.viewDate.getFullYear(), calendarState.viewDate.getMonth() + 1, 1);
      const thisMonth = new Date();
      const currentMonthStart = new Date(thisMonth.getFullYear(), thisMonth.getMonth(), 1);
      if (candidate <= currentMonthStart) {
        calendarState.viewDate = candidate;
        renderCalendar();
      }
    });

    monthChip.addEventListener('click', (e) => {
      e.stopPropagation();
      const willOpen = !monthList.classList.contains('open');
      closeOverlayLists();
      if (willOpen) {
        monthList.classList.add('open');
        monthChip.classList.add('is-open');
      }
    });

    yearChip.addEventListener('click', (e) => {
      e.stopPropagation();
      const willOpen = !yearList.classList.contains('open');
      closeOverlayLists();
      if (willOpen) {
        yearList.classList.add('open');
        yearChip.classList.add('is-open');
      }
    });

    monthList.addEventListener('click', (e) => {
      const button = e.target.closest('[data-month]');
      if (!button) return;
      calendarState.viewDate = new Date(calendarState.viewDate.getFullYear(), parseInt(button.dataset.month, 10), 1);
      closeOverlayLists();
      renderCalendar();
    });

    yearList.addEventListener('click', (e) => {
      const button = e.target.closest('[data-year]');
      if (!button) return;
      calendarState.viewDate = new Date(parseInt(button.dataset.year, 10), calendarState.viewDate.getMonth(), 1);
      closeOverlayLists();
      renderCalendar();
    });

    clearBtn.addEventListener('click', () => {
      displayInput.value = '';
      isoInput.value = '';
      calendarState.selectedIso = '';
      closeOverlayLists();
      renderCalendar();
      closePopup();
    });

    todayBtn.addEventListener('click', () => {
      const today = new Date();
      setSelectedIso(today.toISOString().split('T')[0]);
      closePopup();
    });

    days.addEventListener('click', (e) => {
      const button = e.target.closest('.birthdate-day[data-iso]');
      if (!button || button.disabled) return;
      setSelectedIso(button.dataset.iso);
      closeOverlayLists();
      closePopup();
    });

    document.addEventListener('click', (e) => {
      if (!popup.contains(e.target) && !trigger.contains(e.target) && e.target !== displayInput) {
        closeOverlayLists();
        closePopup();
      }
    });

    displayInput.addEventListener('focus', () => {
      renderCalendar();
    });

    populateCalendarLists();
    renderCalendar();
  }

  // Load profile on page load
  initBirthDateControls();
  loadProfile();

  async function loadProfile() {
    try {
      const res = await API.get('/profile.php?action=get');
      if (!res.success) throw new Error(res.message);

      profile = res.profile;
      const stats = res.statistics;
      const achievements = res.achievements;
      const recentResults = res.recent_results;

      // Header
      const avatarUrl = profile.avatar || 'public/img/default-avatar.svg';
      document.getElementById('headerAvatar').src = avatarUrl.startsWith('http') ? avatarUrl : (window.APP_URL || '') + '/' + avatarUrl;
      document.getElementById('headerName').textContent = (profile.first_name || profile.last_name) 
        ? `${profile.first_name || ''} ${profile.last_name || ''}`.trim() || profile.username 
        : profile.username;
      document.getElementById('headerRole').textContent = profile.role === 'admin' ? 'РђРґРјРёРЅРёСЃС‚СЂР°С‚РѕСЂ' : 'Студент';
      document.getElementById('headerBio').textContent = profile.bio || '';

      // Stats
      document.getElementById('statAttempts').textContent = stats.total_attempts;
      document.getElementById('statPassed').textContent = stats.passed_tests;
      document.getElementById('statAvg').textContent = Math.round(parseFloat(stats.avg_percentage)) + '%';
      document.getElementById('statTime').textContent = Math.round(stats.total_time_seconds / 3600) + 'С‡';

      // Profile form
      document.getElementById('firstName').value = profile.first_name || '';
      document.getElementById('lastName').value = profile.last_name || '';
      document.getElementById('bio').value = profile.bio || '';
      document.getElementById('phone').value = profile.phone || '';
      document.getElementById('city').value = profile.city || '';
      document.getElementById('website').value = profile.website || '';
      document.getElementById('birthDate').value = formatIsoToDisplay(profile.birth_date || '');
      document.getElementById('birthDateIso').value = profile.birth_date || '';
      document.getElementById('birthDate').dispatchEvent(new Event('input'));
      document.getElementById('socialVk').value = profile.social_vk || '';
      document.getElementById('socialTg').value = profile.social_tg || '';

      // Contact form
      document.getElementById('username').value = profile.username;
      document.getElementById('email').value = profile.email;

      // Avatar preview
      document.getElementById('avatarPreview').src = avatarUrl.startsWith('http') ? avatarUrl : (window.APP_URL || '') + '/' + avatarUrl;

      // Achievements
      renderAchievements(achievements);

      // Recent results
      renderRecentResults(recentResults);

      // Load activity
      loadActivity(30);

    } catch (err) {
      NotificationToast.error(err.message);
    }
  }

  function renderAchievements(achievements) {
    const grid = document.getElementById('achievementsGrid');
    grid.innerHTML = achievements.map(a => `
      <div class="achievement-card ${a.unlocked ? '' : 'locked'}">
        <div class="achievement-icon">${a.icon}</div>
        <div class="achievement-name">${a.name}</div>
        <div class="achievement-desc">${a.description}</div>
      </div>
    `).join('');
  }

  function renderRecentResults(results) {
    const tbody = document.getElementById('recentResults');
    if (!results || results.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">РџРѕРєР° РЅРµС‚ СЂРµР·СѓР»СЊС‚Р°С‚РѕРІ</td></tr>';
      return;
    }
    tbody.innerHTML = results.map(r => `
      <tr>
        <td><strong>${escapeHtml(r.test_title)}</strong></td>
        <td>#${r.attempt_number}</td>
        <td>${r.score}/${r.max_score}</td>
        <td><strong>${parseFloat(r.percentage).toFixed(1)}%</strong></td>
        <td><span class="badge ${r.passed ? 'badge-pass' : 'badge-fail'}">${r.passed ? 'Сдан' : 'РќРµС‚'}</span></td>
        <td class="text-muted" style="font-size: 0.85rem;">${new Date(r.created_at).toLocaleDateString('ru')}</td>
      </tr>
    `).join('');
  }

  async function loadActivity(days) {
    try {
      const res = await API.get('/profile.php?action=activity&days=' + days);
      if (!res.success) return;

      const activity = res.activity;
      const heatmap = document.getElementById('activityHeatmap');
      
      // Generate 30 days heatmap
      let html = '';
      const today = new Date();
      for (let i = days - 1; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        const count = activity[dateStr] || 0;
        const level = count === 0 ? '' : count === 1 ? 'level-1' : count === 2 ? 'level-2' : count <= 4 ? 'level-3' : 'level-4';
        html += `<div class="activity-day ${level}" title="${dateStr}: ${count} С‚РµСЃС‚РѕРІ"></div>`;
      }
      heatmap.innerHTML = html;
    } catch (err) {
      console.error(err);
    }
  }

  // Tab switching
  function switchTab(tab) {
    document.querySelectorAll('.profile-tab').forEach(b => b.classList.toggle('active', b.textContent.toLowerCase().includes(tab === 'info' ? 'РёРЅС„РѕСЂРјР°С†РёСЏ' : tab === 'security' ? 'Р±РµР·РѕРїР°СЃРЅРѕСЃС‚СЊ' : tab === 'avatar' ? 'Р°РІР°С‚Р°СЂ' : tab === 'achievements' ? 'РґРѕСЃС‚РёР¶РµРЅРёСЏ' : 'Р°РєС‚РёРІРЅРѕСЃС‚СЊ')));
    document.querySelectorAll('[id^="tab-"]').forEach(s => s.classList.add('hidden'));
    document.getElementById('tab-' + tab).classList.remove('hidden');
    currentTab = tab;
  }

  // Save profile
  document.getElementById('profileForm').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('saveProfileBtn');
    setLoading(btn, true);
    try {
      syncBirthDateFieldsFromDisplay();
      const birthDateValue = document.getElementById('birthDate').value.trim();
      if (birthDateValue && !isValidDisplayDate(birthDateValue)) {
        throw new Error('Р’РІРµРґРёС‚Рµ РєРѕСЂСЂРµРєС‚РЅСѓСЋ РґР°С‚Сѓ РІ С„РѕСЂРјР°С‚Рµ Р”Р”.РњРњ.Р“Р“Р“Р“');
      }
      await API.post('/profile.php?action=update', {
        bio: document.getElementById('bio').value,
        phone: document.getElementById('phone').value,
        city: document.getElementById('city').value,
        website: document.getElementById('website').value,
        birth_date: document.getElementById('birthDateIso').value || formatDisplayToIso(document.getElementById('birthDate').value),
        social_vk: document.getElementById('socialVk').value,
        social_tg: document.getElementById('socialTg').value,
        first_name: document.getElementById('firstName').value,
        last_name: document.getElementById('lastName').value,
        csrf_token: localStorage.getItem('csrf_token')
      });
      NotificationToast.success('РџСЂРѕС„РёР»СЊ РѕР±РЅРѕРІР»С‘РЅ');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  });

  // Save contact info
  document.getElementById('contactForm').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('saveContactBtn');
    setLoading(btn, true);
    try {
      const username = document.getElementById('username').value.trim();
      const email = document.getElementById('email').value.trim();

      if (username !== profile.username) {
        await API.post('/profile.php?action=change_username', {
          username,
          csrf_token: localStorage.getItem('csrf_token')
        });
      }
      if (email !== profile.email) {
        await API.post('/profile.php?action=change_email', {
          email,
          csrf_token: localStorage.getItem('csrf_token')
        });
      }
      NotificationToast.success('Р”Р°РЅРЅС‹Рµ РѕР±РЅРѕРІР»РµРЅС‹');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  });

  // Change password
  document.getElementById('passwordForm').addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('changePasswordBtn');
    setLoading(btn, true);
    try {
      const newPwd = document.getElementById('newPassword').value;
      const confirm = document.getElementById('confirmPassword').value;
      
      if (newPwd !== confirm) {
        throw new Error('РџР°СЂРѕР»Рё РЅРµ СЃРѕРІРїР°РґР°СЋС‚');
      }

      await API.post('/profile.php?action=change_password', {
        current_password: document.getElementById('currentPassword').value,
        new_password: newPwd,
        confirm_password: confirm,
        csrf_token: localStorage.getItem('csrf_token')
      });
      NotificationToast.success('РџР°СЂРѕР»СЊ РёР·РјРµРЅС‘РЅ');
      e.target.reset();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      setLoading(btn, false);
    }
  });

  // Upload avatar
  document.getElementById('avatarInput').addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('csrf_token', localStorage.getItem('csrf_token'));

    document.getElementById('avatarProgress').classList.remove('hidden');

    try {
    const response = await fetch((window.APP_URL || '') + '/profile.php?action=upload_avatar', {
        method: 'POST',
        body: formData,
        credentials: 'include',
      });
      const data = await response.json();
      if (!data.success) throw new Error(data.message);
      
      NotificationToast.success('РђРІР°С‚Р°СЂРєР° Р·Р°РіСЂСѓР¶РµРЅР°');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    } finally {
      document.getElementById('avatarProgress').classList.add('hidden');
      e.target.value = '';
    }
  });

  // Remove avatar
  document.getElementById('removeAvatarBtn').addEventListener('click', async () => {
    if (!confirm('РЈРґР°Р»РёС‚СЊ Р°РІР°С‚Р°СЂРєСѓ?')) return;
    try {
      await API.post('/profile.php?action=remove_avatar', {
        csrf_token: localStorage.getItem('csrf_token')
      });
      NotificationToast.success('РђРІР°С‚Р°СЂРєР° СѓРґР°Р»РµРЅР°');
      loadProfile();
    } catch (err) {
      NotificationToast.error(err.message);
    }
  });

  function escapeHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  // Topbar hide on scroll down, show on scroll up
  let lastScrollY = window.scrollY;
  const topbar = document.getElementById('topbar');

  window.addEventListener('scroll', () => {
    if (!topbar) return;
    const currentY = window.scrollY;
    if (currentY > lastScrollY && currentY > 100) {
      topbar.classList.add('navbar-hidden');
    } else {
      topbar.classList.remove('navbar-hidden');
    }
    lastScrollY = currentY;
  });
</script>
</div><!-- /profile-content wrapper -->
</body>
</html>
