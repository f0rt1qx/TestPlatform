<?php
require_once __DIR__ . '/src/bootstrap.php';

$stats = ['students' => 0, 'tests' => 0, 'satisfied' => 0];
try {
  $pdo = Database::getInstance();
  $stats['students']   = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
  $stats['tests']      = (int)$pdo->query("SELECT COUNT(*) FROM tests WHERE is_active = 1")->fetchColumn();
  $totalAttempts       = (int)$pdo->query("SELECT COUNT(*) FROM attempts")->fetchColumn();
  $passedAttempts      = (int)$pdo->query("SELECT COUNT(*) FROM results WHERE passed = 1")->fetchColumn();
  $stats['satisfied']  = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100) : 0;
} catch (Exception $e) { /* silently ignore if DB not ready */ }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="src/favicon.ico" type="image/x-icon">
  <title>Sapienta — Честное онлайн-тестирование</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css?v=2">
  <style>
    /* === Index page button styles === */

    /* Hero section buttons: green gradient → white on hover */
    .hero .btn-primary {
      background: linear-gradient(135deg, #00c853 0%, #00e676 100%);
      color: #fff;
    }
    .hero .btn-primary:hover {
      background: #fff;
      color: #00c853;
      box-shadow: 0 8px 32px rgba(0, 200, 83, 0.3);
      transform: translateY(-3px) scale(1.04);
    }
    .hero .btn-outline {
      color: #fff;
      border-color: rgba(255, 255, 255, 0.6);
    }
    .hero .btn-outline:hover {
      background: #fff;
      color: #00c853;
      border-color: #fff;
      box-shadow: 0 8px 32px rgba(255, 255, 255, 0.2);
      transform: translateY(-3px) scale(1.04);
    }

    /* CTA section buttons */
    .cta-section .btn-primary {
      background: linear-gradient(135deg, #00c853 0%, #00e676 100%);
      color: #fff;
    }
    .cta-section .btn-primary:hover {
      background: #fff;
      color: #00c853;
      box-shadow: 0 8px 32px rgba(0, 200, 83, 0.3);
      transform: translateY(-3px) scale(1.04);
    }

    /* Typewriter effect */
    .typewriter {
      display: inline;
    }

    /* Test preview card buttons: text turns white on hover */
    .tests-preview .test-card-actions .btn-primary:hover {
      color: #fff;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar" id="navbar">
  <div class="container">
    <a href="index.php" class="navbar-brand">
      <img src="src/logo.png" alt="Sapienta logo" width="56" height="56" style="width:56px;height:56px;object-fit:contain;">
    </a>

    <ul class="navbar-nav" id="mainNav">
      <li data-guest><a href="login.php" class="btn btn-primary btn-sm" data-i18n="nav.login">Войти</a></li>
      <li data-guest><a href="register.php" class="btn btn-secondary btn-sm" data-i18n="nav.register">Регистрация</a></li>
      <li data-user class="hidden"><a href="dashboard.php" data-i18n="nav.dashboard">Кабинет</a></li>
      <li data-user class="hidden"><a href="profile.php" data-i18n="nav.profile">Профиль</a></li>
      <li data-admin class="hidden"><a href="admin.php" data-i18n="nav.admin">Админ</a></li>
      <li data-user class="hidden"><a href="#" onclick="AuthManager.logout()" data-i18n="nav.logout">Выйти</a></li>
      <li>
        <div class="lang-selector">
          <select data-language-selector aria-label="Выбор языка"></select>
        </div>
      </li>
      <li><button class="theme-toggle" data-theme-toggle title="Сменить тему"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg></button></li>
    </ul>

    <button class="burger" aria-label="Меню" id="burgerBtn">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-content fade-in">
      <h1 class="typewriter" data-i18n="hero.title">Честное онлайн-тестирование<br>с защитой от списывания</h1>
      <p class="typewriter-desc" data-i18n="hero.description" style="opacity:0;transition:opacity 0.5s;">Современная платформа для проверки знаний с анти-читинг системой, логированием действий и детальной аналитикой результатов.</p>
      <div class="hero-btns">
        <a href="register.php" class="btn btn-primary btn-lg" data-guest data-i18n="hero.btn.start"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg> Начать бесплатно</a>
        <a href="dashboard.php" class="btn btn-primary btn-lg hidden" data-user data-i18n="hero.btn.my-tests"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>Мои тесты</a>
        <a href="#tests-preview" class="btn btn-outline btn-lg" data-guest data-i18n="hero.btn.view-tests"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg> Смотреть тесты</a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat">
          <span class="hero-stat-value"><?= $stats['students'] > 0 ? number_format($stats['students'], 0, '.', ' ') : '—' ?></span>
          <span class="hero-stat-label" data-i18n="hero.stats.students"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg> Студентов</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-value"><?= $stats['tests'] > 0 ? number_format($stats['tests'], 0, '.', ' ') : '—' ?></span>
          <span class="hero-stat-label" data-i18n="hero.stats.tests"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm-3.75 7.5h7.5m-7.5 3h7.5m-6 3h4.5m-4.5-10.5h1.5" /></svg> Тестов</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-value"><?= $stats['satisfied'] > 0 ? $stats['satisfied'] . '%' : '—' ?></span>
          <span class="hero-stat-label" data-i18n="hero.stats.satisfied"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.466.73-3.555" /></svg> Довольных</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="container">
    <div class="section-header fade-in">
      <h2 data-i18n="features.title">Почему <span class="text-gradient">Sapienta</span>?</h2>
      <p data-i18n="features.subtitle">Надёжные инструменты для честной проверки знаний</p>
    </div>

    <div class="features-grid">
      <div class="feature-card fade-in">
        <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg></div>
        <h3 data-i18n="feature.anticheat.title">Анти-читинг система</h3>
        <p data-i18n="feature.anticheat.desc">Отслеживание переключения вкладок, копирования и подозрительного поведения в реальном времени.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg></div>
        <h3 data-i18n="feature.random.title">Случайные вопросы</h3>
        <p data-i18n="feature.random.desc">Вопросы и варианты ответов перемешиваются каждый раз — никаких шаблонов.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg></div>
        <h3 data-i18n="feature.timer.title">Таймер и автосохранение</h3>
        <p data-i18n="feature.timer.desc">Точный обратный отсчёт и автоматическое сохранение ответов каждые 30 секунд.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg></div>
        <h3 data-i18n="feature.stats.title">Детальная статистика</h3>
        <p data-i18n="feature.stats.desc">Полная аналитика результатов, прогресс и честность прохождения для каждого студента.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" /></svg></div>
        <h3 data-i18n="feature.settings.title">Гибкие настройки</h3>
        <p data-i18n="feature.settings.desc">Настраивайте время, количество попыток, проходной балл и параметры перемешивания.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="40" height="40"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg></div>
        <h3 data-i18n="feature.responsive.title">Адаптивный дизайн</h3>
        <p data-i18n="feature.responsive.desc">Работает на любых устройствах: компьютеры, планшеты и смартфоны.</p>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-it-works" id="how-it-works">
  <div class="container">
    <div class="section-header fade-in">
      <h2 data-i18n="how.title">Как это работает?</h2>
      <p data-i18n="how.subtitle">Простой процесс от регистрации до результата</p>
    </div>

    <div class="steps">
      <div class="step fade-in">
        <div class="step-num"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM4 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 10.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg></div>
        <h4 data-i18n="how.step1.title">Регистрация</h4>
        <p data-i18n="how.step1.desc">Создайте аккаунт за пару минут</p>
      </div>

      <div class="step fade-in">
        <div class="step-num"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg></div>
        <h4 data-i18n="how.step2.title">Выберите тест</h4>
        <p data-i18n="how.step2.desc">Найдите нужный тест в каталоге</p>
      </div>

      <div class="step fade-in">
        <div class="step-num"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg></div>
        <h4 data-i18n="how.step3.title">Пройдите тест</h4>
        <p data-i18n="how.step3.desc">Ответьте на вопросы честно</p>
      </div>

      <div class="step fade-in">
        <div class="step-num"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a1.5 1.5 0 011.5 1.5v1.5a1.5 1.5 0 01-1.5 1.5h-9a1.5 1.5 0 01-1.5-1.5v-1.5a1.5 1.5 0 011.5-1.5m9 0V15a5.25 5.25 0 00-5.25-5.25h-1.5A5.25 5.25 0 004.5 15v3.75m12 0v-9.75a1.5 1.5 0 00-1.5-1.5h-9a1.5 1.5 0 00-1.5 1.5v9.75m12 0a1.5 1.5 0 01-1.5 1.5h-9a1.5 1.5 0 01-1.5-1.5m0 0V12m0 0a3.75 3.75 0 013.75-3.75M8.25 12a3.75 3.75 0 013.75-3.75m0 0A3.75 3.75 0 0115.75 4.5h-7.5A3.75 3.75 0 014.5 8.25v0" /></svg></div>
        <h4 data-i18n="how.step4.title">Получите результат</h4>
        <p data-i18n="how.step4.desc">Узнайте свой балл сразу</p>
      </div>
    </div>
  </div>
</section>

<!-- TESTS PREVIEW -->
<section class="tests-preview" id="tests-preview">
  <div class="container">
    <div class="section-header fade-in">
      <h2 data-i18n="tests.title">Доступные тесты</h2>
      <p data-i18n="tests.subtitle">Выберите тест для прохождения</p>
    </div>

    <div class="test-grid" id="testGrid">
      <div class="test-card fade-in">
        <div class="test-card-header">
          <div class="test-card-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z" /></svg></div>
          <div>
            <div class="test-card-title">Математика 5 класс</div>
            <div class="test-card-desc">Базовые арифметические операции</div>
          </div>
        </div>
        <div class="test-card-meta">
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> 30 <span data-i18n="tests.meta.minutes">мин</span></span>
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg> 10 <span data-i18n="tests.meta.questions">вопросов</span></span>
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg> 1 <span data-i18n="tests.meta.attempts">попытка</span></span>
        </div>
        <div class="test-card-actions">
          <button class="btn btn-primary btn-full" data-guest disabled data-i18n="tests.btn.login-to-start">Войдите чтобы начать</button>
          <a href="test.php" class="btn btn-primary btn-full hidden" data-user data-i18n="tests.btn.start">Начать тест</a>
        </div>
      </div>

      <div class="test-card fade-in">
        <div class="test-card-header">
          <div class="test-card-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z" /></svg></div>
          <div>
            <div class="test-card-title">Основы информатики</div>
            <div class="test-card-desc">Компьютерные технологии</div>
          </div>
        </div>
        <div class="test-card-meta">
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> 20 <span data-i18n="tests.meta.minutes">мин</span></span>
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg> 15 <span data-i18n="tests.meta.questions">вопросов</span></span>
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg> 2 <span data-i18n="tests.meta.attempts">попытки</span></span>
        </div>
        <div class="test-card-actions">
          <button class="btn btn-primary btn-full" data-guest disabled data-i18n="tests.btn.login-to-start">Войдите чтобы начать</button>
          <a href="test.php" class="btn btn-primary btn-full hidden" data-user data-i18n="tests.btn.start">Начать тест</a>
        </div>
      </div>

      <div class="test-card fade-in">
        <div class="test-card-header">
          <div class="test-card-icon"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg></div>
          <div>
            <div class="test-card-title">Русский язык</div>
            <div class="test-card-desc">Грамматика и правописание</div>
          </div>
        </div>
        <div class="test-card-meta">
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> 45 <span data-i18n="tests.meta.minutes">мин</span></span>
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" /></svg> 20 <span data-i18n="tests.meta.questions">вопросов</span></span>
          <span class="test-meta-item"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg> 1 <span data-i18n="tests.meta.attempts">попытка</span></span>
        </div>
        <div class="test-card-actions">
          <button class="btn btn-primary btn-full" data-guest disabled data-i18n="tests.btn.login-to-start">Войдите чтобы начать</button>
          <a href="test.php" class="btn btn-primary btn-full hidden" data-user data-i18n="tests.btn.start">Начать тест</a>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <a href="register.php" class="btn btn-primary btn-lg" data-guest data-i18n="hero.btn.start"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" /></svg> Начать бесплатно</a>
      <a href="dashboard.php" class="btn btn-primary btn-lg hidden" data-user data-i18n="hero.btn.my-tests"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg> Мои тесты</a>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content fade-in">
      <h2 data-i18n="cta.title">Готовы начать?</h2>
      <p data-i18n="cta.description">Присоединяйтесь к тысячам студентов и преподавателей, которые уже используют Sapienta для честного тестирования.</p>
      <div class="cta-btns">
        <a href="register.php" class="btn btn-primary btn-lg" data-guest data-i18n="cta.btn.create-account">🚀 Создать аккаунт</a>
        <a href="dashboard.php" class="btn btn-primary btn-lg hidden" data-user data-i18n="cta.btn.go-dashboard">📋 Перейти в кабинет</a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="container">
    <div class="footer-content">
      <div class="footer-brand">
        <img src="src/logogreen.png" alt="Sapienta logo" width="60" height="60" style="width:60px;height:60px;object-fit:contain;filter:none!important;margin-bottom:12px;">
        <p data-i18n="footer.description">Современная платформа для честного онлайн-тестирования с защитой от списывания и детальной аналитикой.</p>
        <div class="footer-social">
          <a href="#" aria-label="Telegram"><i class="fab fa-telegram-plane"></i></a>
          <a href="#" aria-label="VK"><i class="fab fa-vk"></i></a>
          <a href="#" aria-label="Email"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" /></svg></a>
        </div>
      </div>

      <div>
        <h4 class="footer-title" data-i18n="footer.platform">Платформа</h4>
        <ul class="footer-links">
          <li><a href="#features" data-i18n="footer.features"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg> Возможности</a></li>
          <li><a href="#tests-preview" data-i18n="footer.tests"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg> Тесты</a></li>
          <li><a href="dashboard.php" data-i18n="nav.dashboard"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg> Кабинет</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer-title" data-i18n="footer.support">Поддержка</h4>
        <ul class="footer-links">
          <li><a href="#" data-i18n="footer.help"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg> Помощь</a></li>
          <li><a href="#" data-i18n="footer.contacts"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg> Контакты</a></li>
          <li><a href="#" data-i18n="footer.faq"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg> FAQ</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2026 Sapienta. <span data-i18n="footer.rights">Все права защищены.</span></p>
    </div>
  </div>
</footer>

<div class="toast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/i18n.js"></script>
<script src="public/js/app.js"></script>
<script>
// Navbar hide on scroll down, show on scroll up
let lastScrollY = window.scrollY;
const navbar = document.getElementById('navbar');

window.addEventListener('scroll', () => {
  if (!navbar) return;
  const currentY = window.scrollY;

  if (currentY > 50) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }

  if (currentY > lastScrollY && currentY > 120) {
    navbar.classList.add('navbar-hidden');
  } else {
    navbar.classList.remove('navbar-hidden');
  }

  lastScrollY = currentY;
});

// Burger menu
const burgerBtn = document.getElementById('burgerBtn');
const mainNav = document.getElementById('mainNav');

if (burgerBtn && mainNav) {
  burgerBtn.addEventListener('click', () => {
    burgerBtn.classList.toggle('active');
    mainNav.classList.toggle('open');
    document.body.style.overflow = mainNav.classList.contains('open') ? 'hidden' : '';
  });

  // Close menu on link click
  mainNav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      burgerBtn.classList.remove('active');
      mainNav.classList.remove('open');
      document.body.style.overflow = '';
    });
  });
}

// Scroll animations
const observerOptions = {
  root: null,
  rootMargin: '0px',
  threshold: 0.1
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, observerOptions);

document.querySelectorAll('.fade-in').forEach(el => {
  observer.observe(el);
});

// === Typewriter effect for hero title + description ===
(function() {
  const titleEl = document.querySelector('.typewriter');
  const descEl = document.querySelector('.typewriter-desc');
  if (!titleEl) return;

  // Title
  let titleRaw = titleEl.innerHTML;
  let titleLines = titleRaw.split(/<br\s*\/?>/i);

  // Description — read once
  let descText = descEl ? descEl.textContent : '';

  let titleCharIdx = 0;
  let titleLineIdx = 0;
  let descCharIdx = 0;

  function renderTitle() {
    let text = '';
    for (let i = 0; i < titleLineIdx; i++) {
      text += titleLines[i] + '<br>';
    }
    if (titleLineIdx < titleLines.length) {
      text += titleLines[titleLineIdx].substring(0, titleCharIdx);
    }
    titleEl.innerHTML = text;
  }

  function renderDesc() {
    if (descEl) {
      descEl.textContent = descText.substring(0, descCharIdx);
    }
  }

  function typeTitle() {
    if (titleLineIdx >= titleLines.length) {
      // Show description and start typing it
      if (descEl) descEl.style.opacity = '1';
      setTimeout(typeDesc, 150);
      return;
    }
    const line = titleLines[titleLineIdx];
    if (titleCharIdx < line.length) {
      titleCharIdx++;
      renderTitle();
      const delay = line[titleCharIdx - 1] === ' ' ? 40 : 30 + Math.random() * 30;
      setTimeout(typeTitle, delay);
    } else {
      titleLineIdx++;
      titleCharIdx = 0;
      renderTitle();
      setTimeout(typeTitle, 200);
    }
  }

  function typeDesc() {
    if (descCharIdx >= descText.length) return;
    descCharIdx++;
    renderDesc();
    setTimeout(typeDesc, 15 + Math.random() * 15);
  }

  function startTyping() {
    titleRaw = titleEl.innerHTML;
    titleLines = titleRaw.split(/<br\s*\/?>/i);
    if (descEl) descText = descEl.textContent || '';
    titleCharIdx = 0;
    titleLineIdx = 0;
    descCharIdx = 0;
    if (descEl) {
      descEl.style.opacity = '0';
      descEl.textContent = '';
    }
    setTimeout(typeTitle, 400);
  }

  startTyping();

  // Hook into i18n — restart after translation applied
  const origApply = i18n.apply.bind(i18n);
  i18n.apply = function(lang) {
    origApply(lang);
    // Small delay so DOM updates
    setTimeout(startTyping, 50);
  };
})();
</script>
</body>
</html>
