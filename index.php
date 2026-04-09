<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TestPlatform — Честное онлайн-тестирование</title>
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
      <i class="fas fa-graduation-cap"></i> TestPlatform
    </a>

    <ul class="navbar-nav" id="mainNav">
      <li data-guest><a href="login.php" class="btn btn-primary btn-sm" data-i18n="nav.login"><i class="fas fa-sign-in-alt"></i> Войти</a></li>
      <li data-guest><a href="register.php" class="btn btn-secondary btn-sm" data-i18n="nav.register"><i class="fas fa-user-plus"></i> Регистрация</a></li>
      <li data-user class="hidden"><a href="dashboard.php" data-i18n="nav.dashboard"><i class="fas fa-th-large"></i> Кабинет</a></li>
      <li data-user class="hidden"><a href="profile.php" data-i18n="nav.profile"><i class="fas fa-user"></i> Профиль</a></li>
      <li data-admin class="hidden"><a href="admin.php" data-i18n="nav.admin"><i class="fas fa-shield-alt"></i> Админ</a></li>
      <li data-user class="hidden"><a href="#" onclick="AuthManager.logout()" data-i18n="nav.logout"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
      <li>
        <div class="lang-selector">
          <select data-language-selector aria-label="Выбор языка"></select>
        </div>
      </li>
      <li><button class="theme-toggle" data-theme-toggle title="Сменить тему"><i class="fas fa-sun"></i></button></li>
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
      <h1 data-i18n="hero.title">Честное онлайн-тестирование<br>с защитой от списывания</h1>
      <p data-i18n="hero.description">Современная платформа для проверки знаний с анти-читинг системой, логированием действий и детальной аналитикой результатов.</p>
      <div class="hero-btns">
        <a href="register.php" class="btn btn-primary btn-lg" data-guest data-i18n="hero.btn.start"><i class="fas fa-rocket"></i> Начать бесплатно</a>
        <a href="dashboard.php" class="btn btn-primary btn-lg hidden" data-user data-i18n="hero.btn.my-tests"><i class="fas fa-clipboard-list"></i> Мои тесты</a>
        <a href="#tests-preview" class="btn btn-outline btn-lg" data-guest data-i18n="hero.btn.view-tests"><i class="fas fa-tasks"></i> Смотреть тесты</a>
      </div>

      <div class="hero-stats">
        <div class="hero-stat">
          <span class="hero-stat-value">1000+</span>
          <span class="hero-stat-label" data-i18n="hero.stats.students"><i class="fas fa-user-graduate"></i> Студентов</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-value">500+</span>
          <span class="hero-stat-label" data-i18n="hero.stats.tests"><i class="fas fa-file-alt"></i> Тестов</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-value">99%</span>
          <span class="hero-stat-label" data-i18n="hero.stats.satisfied"><i class="fas fa-smile"></i> Довольных</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
  <div class="container">
    <div class="section-header fade-in">
      <h2 data-i18n="features.title">Почему <span class="text-gradient">TestPlatform</span>?</h2>
      <p data-i18n="features.subtitle">Надёжные инструменты для честной проверки знаний</p>
    </div>

    <div class="features-grid">
      <div class="feature-card fade-in">
        <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
        <h3 data-i18n="feature.anticheat.title">Анти-читинг система</h3>
        <p data-i18n="feature.anticheat.desc">Отслеживание переключения вкладок, копирования и подозрительного поведения в реальном времени.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><i class="fas fa-random"></i></div>
        <h3 data-i18n="feature.random.title">Случайные вопросы</h3>
        <p data-i18n="feature.random.desc">Вопросы и варианты ответов перемешиваются каждый раз — никаких шаблонов.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><i class="fas fa-stopwatch"></i></div>
        <h3 data-i18n="feature.timer.title">Таймер и автосохранение</h3>
        <p data-i18n="feature.timer.desc">Точный обратный отсчёт и автоматическое сохранение ответов каждые 30 секунд.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><i class="fas fa-chart-bar"></i></div>
        <h3 data-i18n="feature.stats.title">Детальная статистика</h3>
        <p data-i18n="feature.stats.desc">Полная аналитика результатов, прогресс и честность прохождения для каждого студента.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><i class="fas fa-sliders-h"></i></div>
        <h3 data-i18n="feature.settings.title">Гибкие настройки</h3>
        <p data-i18n="feature.settings.desc">Настраивайте время, количество попыток, проходной балл и параметры перемешивания.</p>
      </div>

      <div class="feature-card fade-in">
        <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
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
        <div class="step-num"><i class="fas fa-user-plus"></i></div>
        <h4 data-i18n="how.step1.title">Регистрация</h4>
        <p data-i18n="how.step1.desc">Создайте аккаунт за пару минут</p>
      </div>

      <div class="step fade-in">
        <div class="step-num"><i class="fas fa-search"></i></div>
        <h4 data-i18n="how.step2.title">Выберите тест</h4>
        <p data-i18n="how.step2.desc">Найдите нужный тест в каталоге</p>
      </div>

      <div class="step fade-in">
        <div class="step-num"><i class="fas fa-pencil-alt"></i></div>
        <h4 data-i18n="how.step3.title">Пройдите тест</h4>
        <p data-i18n="how.step3.desc">Ответьте на вопросы честно</p>
      </div>

      <div class="step fade-in">
        <div class="step-num"><i class="fas fa-trophy"></i></div>
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
          <div class="test-card-icon"><i class="fas fa-calculator"></i></div>
          <div>
            <div class="test-card-title">Математика 5 класс</div>
            <div class="test-card-desc">Базовые арифметические операции</div>
          </div>
        </div>
        <div class="test-card-meta">
          <span class="test-meta-item"><i class="fas fa-clock"></i> 30 <span data-i18n="tests.meta.minutes">мин</span></span>
          <span class="test-meta-item"><i class="fas fa-question-circle"></i> 10 <span data-i18n="tests.meta.questions">вопросов</span></span>
          <span class="test-meta-item"><i class="fas fa-redo"></i> 1 <span data-i18n="tests.meta.attempts">попытка</span></span>
        </div>
        <div class="test-card-actions">
          <button class="btn btn-primary btn-full" data-guest disabled data-i18n="tests.btn.login-to-start">Войдите чтобы начать</button>
          <a href="test.php" class="btn btn-primary btn-full hidden" data-user data-i18n="tests.btn.start">Начать тест</a>
        </div>
      </div>

      <div class="test-card fade-in">
        <div class="test-card-header">
          <div class="test-card-icon"><i class="fas fa-flask"></i></div>
          <div>
            <div class="test-card-title">Основы информатики</div>
            <div class="test-card-desc">Компьютерные технологии</div>
          </div>
        </div>
        <div class="test-card-meta">
          <span class="test-meta-item"><i class="fas fa-clock"></i> 20 <span data-i18n="tests.meta.minutes">мин</span></span>
          <span class="test-meta-item"><i class="fas fa-question-circle"></i> 15 <span data-i18n="tests.meta.questions">вопросов</span></span>
          <span class="test-meta-item"><i class="fas fa-redo"></i> 2 <span data-i18n="tests.meta.attempts">попытки</span></span>
        </div>
        <div class="test-card-actions">
          <button class="btn btn-primary btn-full" data-guest disabled data-i18n="tests.btn.login-to-start">Войдите чтобы начать</button>
          <a href="test.php" class="btn btn-primary btn-full hidden" data-user data-i18n="tests.btn.start">Начать тест</a>
        </div>
      </div>

      <div class="test-card fade-in">
        <div class="test-card-header">
          <div class="test-card-icon"><i class="fas fa-book-open"></i></div>
          <div>
            <div class="test-card-title">Русский язык</div>
            <div class="test-card-desc">Грамматика и правописание</div>
          </div>
        </div>
        <div class="test-card-meta">
          <span class="test-meta-item"><i class="fas fa-clock"></i> 45 <span data-i18n="tests.meta.minutes">мин</span></span>
          <span class="test-meta-item"><i class="fas fa-question-circle"></i> 20 <span data-i18n="tests.meta.questions">вопросов</span></span>
          <span class="test-meta-item"><i class="fas fa-redo"></i> 1 <span data-i18n="tests.meta.attempts">попытка</span></span>
        </div>
        <div class="test-card-actions">
          <button class="btn btn-primary btn-full" data-guest disabled data-i18n="tests.btn.login-to-start">Войдите чтобы начать</button>
          <a href="test.php" class="btn btn-primary btn-full hidden" data-user data-i18n="tests.btn.start">Начать тест</a>
        </div>
      </div>
    </div>

    <div class="text-center mt-4">
      <a href="register.php" class="btn btn-primary btn-lg" data-guest data-i18n="hero.btn.start"><i class="fas fa-rocket"></i> Начать бесплатно</a>
      <a href="dashboard.php" class="btn btn-primary btn-lg hidden" data-user data-i18n="hero.btn.my-tests"><i class="fas fa-clipboard-list"></i> Мои тесты</a>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
  <div class="container">
    <div class="cta-content fade-in">
      <h2 data-i18n="cta.title">Готовы начать?</h2>
      <p data-i18n="cta.description">Присоединяйтесь к тысячам студентов и преподавателей, которые уже используют TestPlatform для честного тестирования.</p>
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
        <h3 data-i18n="footer.brand"><i class="fas fa-graduation-cap"></i> TestPlatform</h3>
        <p data-i18n="footer.description">Современная платформа для честного онлайн-тестирования</p>
        <div class="footer-social">
          <a href="#" aria-label="Telegram"><i class="fab fa-telegram-plane"></i></a>
          <a href="#" aria-label="VK"><i class="fab fa-vk"></i></a>
          <a href="#" aria-label="Email"><i class="fas fa-envelope"></i></a>
        </div>
      </div>

      <div>
        <h4 class="footer-title" data-i18n="footer.platform">Платформа</h4>
        <ul class="footer-links">
          <li><a href="#features" data-i18n="footer.features"><i class="fas fa-angle-right"></i> Возможности</a></li>
          <li><a href="#tests-preview" data-i18n="footer.tests"><i class="fas fa-angle-right"></i> Тесты</a></li>
          <li><a href="dashboard.php" data-i18n="nav.dashboard"><i class="fas fa-angle-right"></i> Кабинет</a></li>
        </ul>
      </div>

      <div>
        <h4 class="footer-title" data-i18n="footer.support">Поддержка</h4>
        <ul class="footer-links">
          <li><a href="#" data-i18n="footer.help"><i class="fas fa-angle-right"></i> Помощь</a></li>
          <li><a href="#" data-i18n="footer.contacts"><i class="fas fa-angle-right"></i> Контакты</a></li>
          <li><a href="#" data-i18n="footer.faq"><i class="fas fa-angle-right"></i> FAQ</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2024 TestPlatform. <span data-i18n="footer.rights">Все права защищены.</span></p>
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
</script>
</body>
</html>
