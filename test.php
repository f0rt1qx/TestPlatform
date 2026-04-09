<?php header('Content-Type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Тест — TestPlatform</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- FontAwesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="public/css/modern.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: var(--bg-gray);
      color: var(--text-dark);
      min-height: 100vh;
    }

    #testLoading {
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh; flex-direction: column; gap: 16px;
      background: var(--bg-gray);
    }
    .load-spinner {
      width: 44px; height: 44px; border-radius: 50%;
      border: 3px solid var(--border-light);
      border-top-color: var(--gradient-start);
      animation: spin .7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .tp-topbar {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      height: 64px;
      background: var(--white);
      border-bottom: 1px solid var(--border-light);
      display: flex; align-items: center;
      padding: 0 20px;
      gap: 16px;
      box-shadow: var(--shadow-sm);
    }
    .tp-brand {
      display: flex; align-items: center; gap: 8px;
      font-weight: 800; font-size: .95rem; color: var(--text-dark);
      text-decoration: none; flex-shrink: 0;
    }
    .tp-brand-icon {
      width: 32px; height: 32px; border-radius: 10px;
      background: var(--gradient-primary);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: .85rem; font-weight: 800;
    }
    .tp-title {
      flex: 1; font-weight: 700; font-size: .95rem;
      color: var(--text-dark); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .tp-status {
      display: flex; align-items: center; gap: 6px;
      font-size: .78rem; font-weight: 600;
      padding: 5px 12px; border-radius: 20px;
      flex-shrink: 0;
    }
    .tp-status.active { background: #dcfce7; color: #15803d; }
    .tp-status.violation { background: #fee2e2; color: #dc2626; }
    .tp-status-dot {
      width: 7px; height: 7px; border-radius: 50%;
      background: currentColor; animation: pulse 1.5s ease infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    .tp-timer {
      font-size: 1.1rem; font-weight: 800; letter-spacing: .5px;
      padding: 6px 14px; border-radius: 10px;
      background: var(--bg-light); border: 2px solid var(--border-light);
      color: var(--text-dark); flex-shrink: 0; font-variant-numeric: tabular-nums;
      transition: all .3s;
    }
    .tp-timer.warning { background: #fffbeb; border-color: #fbbf24; color: #b45309; }
    .tp-timer.danger  { background: #fef2f2; border-color: #f87171; color: #dc2626; }
    .tp-layout { display: grid; grid-template-columns: 1fr 300px; gap: 0; margin-top: 64px; min-height: calc(100vh - 64px); }
    .tp-main { padding: 32px 40px 120px; max-width: 800px; }
    .tp-q-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .tp-q-num { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--text-gray); }
    .tp-q-points { font-size: .78rem; font-weight: 600; background: #ede9fe; color: #7c3aed; padding: 3px 10px; border-radius: 20px; }
    .tp-progress-bar { height: 4px; background: var(--border-light); border-radius: 2px; margin-bottom: 28px; overflow: hidden; }
    .tp-progress-fill { height: 100%; border-radius: 2px; background: var(--gradient-primary); transition: width .4s ease; }
    .tp-question-text { font-size: 1.15rem; font-weight: 600; line-height: 1.65; color: var(--text-dark); margin-bottom: 28px; }
    .tp-hint { font-size: .8rem; color: #7c3aed; font-weight: 600; background: #ede9fe; padding: 5px 12px; border-radius: 6px; display: inline-block; margin-bottom: 16px; }
    .tp-answers { display: flex; flex-direction: column; gap: 10px; margin-bottom: 36px; }
    .tp-answer { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-radius: var(--radius-md); border: 2px solid var(--border-light); background: var(--white); cursor: pointer; transition: all .18s ease; user-select: none; }
    .tp-answer:hover { border-color: var(--gradient-start); background: var(--bg-light); }
    .tp-answer.selected { border-color: var(--gradient-start); background: rgba(0, 200, 83, 0.1); }
    .tp-answer input { display: none; }
    .tp-answer-marker { width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0; border: 2px solid var(--border-light); background: var(--white); display: flex; align-items: center; justify-content: center; transition: all .18s; }
    .tp-answer.checkbox .tp-answer-marker { border-radius: 6px; }
    .tp-answer.selected .tp-answer-marker { border-color: var(--gradient-start); background: var(--gradient-start); }
    .tp-answer.selected .tp-answer-marker::after { content: ''; display: block; width: 6px; height: 6px; border-radius: 50%; background: #fff; }
    .tp-answer.checkbox.selected .tp-answer-marker::after { width: 5px; height: 9px; border-radius: 0; border: 2px solid #fff; border-top: none; border-left: none; transform: rotate(45deg) translate(-1px, -1px); background: transparent; }
    .tp-answer-label { font-size: .95rem; font-weight: 500; color: var(--text-dark); line-height: 1.5; }
    .tp-answer-letter { width: 28px; height: 28px; border-radius: 7px; flex-shrink: 0; background: var(--bg-light); color: var(--text-gray); display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; transition: all .18s; }
    .tp-answer.selected .tp-answer-letter { background: var(--gradient-start); color: #fff; }
    .tp-nav { display: flex; align-items: center; justify-content: space-between; position: fixed; bottom: 0; left: 0; width: calc(100% - 300px); background: var(--white); border-top: 1px solid var(--border-light); padding: 14px 40px; box-shadow: 0 -2px 12px rgba(0,0,0,.06); z-index: 90; }
    .tp-nav-info { font-size: .82rem; color: var(--text-gray); font-weight: 500; }
    .tp-nav-btns { display: flex; gap: 10px; align-items: center; }
    .tp-btn { padding: 9px 20px; border-radius: var(--radius-md); font-size: .85rem; font-weight: 700; cursor: pointer; border: none; font-family: 'Inter', sans-serif; transition: all .15s; }
    .tp-btn-outline { background: var(--bg-light); color: var(--text-gray); }
    .tp-btn-outline:hover { background: var(--border-light); }
    .tp-btn-primary { background: var(--gradient-primary); color: #fff; box-shadow: var(--shadow-md); }
    .tp-btn-primary:hover { opacity: .9; transform: translateY(-1px); box-shadow: var(--shadow-lg); }
    .tp-btn-success { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 3px 10px rgba(16,185,129,.3); }
    .tp-btn-success:hover { opacity: .9; transform: translateY(-1px); }
    .tp-btn:disabled { opacity: .4; cursor: not-allowed; transform: none !important; }
    .tp-sidebar { background: var(--white); border-left: 1px solid var(--border-light); padding: 24px 20px; position: fixed; top: 64px; right: 0; width: 300px; height: calc(100vh - 64px); overflow-y: auto; display: flex; flex-direction: column; gap: 24px; }
    .tp-sidebar-section h4 { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--text-light); margin-bottom: 14px; }
    .tp-qmap { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; }
    .tp-qmap-btn { aspect-ratio: 1; border-radius: 8px; border: 2px solid var(--border-light); background: var(--bg-light); color: var(--text-gray); font-size: .8rem; font-weight: 700; cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center; }
    .tp-qmap-btn:hover { border-color: var(--gradient-start); background: var(--bg-light); }
    .tp-qmap-btn.active { border-color: var(--gradient-start); background: var(--gradient-start); color: #fff; }
    .tp-qmap-btn.answered { border-color: #10b981; background: #dcfce7; color: #15803d; }
    .tp-log { display: flex; flex-direction: column; gap: 8px; }
    .tp-log-item { display: flex; gap: 10px; align-items: flex-start; padding: 8px 10px; border-radius: 8px; background: var(--bg-light); font-size: .75rem; animation: logIn .2s ease; }
    @keyframes logIn { from{opacity:0;transform:translateY(-4px)} to{opacity:1;transform:translateY(0)} }
    .tp-log-item.warn { background: #fff7ed; }
    .tp-log-item.error { background: #fef2f2; }
    .tp-log-time { color: var(--text-light); flex-shrink: 0; font-variant-numeric: tabular-nums; }
    .tp-log-icon { flex-shrink: 0; font-size: .85rem; }
    .tp-log-text { color: var(--text-gray); line-height: 1.4; }
    .tp-stat-row { display: flex; gap: 8px; }
    .tp-stat { flex: 1; padding: 10px 12px; border-radius: 10px; background: var(--bg-light); border: 1px solid var(--border-light); text-align: center; }
    .tp-stat-val { font-size: 1.1rem; font-weight: 800; color: var(--text-dark); }
    .tp-stat-lbl { font-size: .68rem; color: var(--text-light); font-weight: 600; margin-top: 2px; }
    #resultUI { min-height: 100vh; background: var(--bg-gray); display: flex; align-items: center; justify-content: center; padding: 40px 20px; }
    .tp-result-card { background: var(--white); border-radius: var(--radius-xl); padding: 48px 40px; max-width: 560px; width: 100%; text-align: center; box-shadow: var(--shadow-xl); animation: resultIn .4s ease; }
    @keyframes resultIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    .tp-result-icon { font-size: 4rem; margin-bottom: 20px; }
    .tp-result-pct { font-size: 3.5rem; font-weight: 900; margin-bottom: 6px; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .tp-result-pct.failed { background: linear-gradient(135deg,#ef4444,#dc2626); }
    .tp-result-label { font-size: 1rem; color: var(--text-gray); margin-bottom: 32px; font-weight: 500; }
    .tp-result-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 28px; }
    .tp-result-stat { padding: 16px 10px; border-radius: 12px; background: var(--bg-light); border: 1px solid var(--border-light); }
    .tp-result-stat-val { font-size: 1.2rem; font-weight: 800; color: var(--text-dark); }
    .tp-result-stat-lbl { font-size: .72rem; color: var(--text-light); font-weight: 600; margin-top: 4px; }
    .tp-result-btns { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 8px; }
    .tp-result-btn { padding: 11px 24px; border-radius: 10px; font-size: .88rem; font-weight: 700; cursor: pointer; text-decoration: none; font-family: 'Inter', sans-serif; transition: all .15s; }
    .tp-result-btn.primary { background: var(--gradient-primary); color: #fff; box-shadow: var(--shadow-md); }
    .tp-result-btn.outline { background: var(--bg-light); color: var(--text-gray); border: 1.5px solid var(--border-light); }
    .tp-result-btn:hover { opacity: .88; transform: translateY(-1px); }
    #disqualifyScreen { position: fixed; inset: 0; z-index: 99998; background: #0f172a; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .tp-disq { max-width: 480px; width: 100%; text-align: center; animation: resultIn .4s ease; }
    .tp-disq-icon { font-size: 4.5rem; margin-bottom: 24px; }
    .tp-disq h1 { color: #ef4444; font-size: 1.9rem; font-weight: 900; margin-bottom: 14px; }
    .tp-disq p { color: #94a3b8; font-size: .95rem; line-height: 1.7; margin-bottom: 28px; }
    .tp-disq-dots { display: flex; gap: 10px; justify-content: center; margin-bottom: 32px; }
    .tp-disq-dot { width: 14px; height: 14px; border-radius: 50%; background: #ef4444; }
    .toast-container { position: fixed; bottom: 80px; right: 24px; z-index: 9999; }
    @media (max-width: 768px) { .tp-layout { grid-template-columns: 1fr; } .tp-sidebar { display: none; } .tp-nav { width: 100%; padding: 12px 20px; } .tp-main { padding: 24px 20px 100px; } }
  </style>
</head>
<body>

<!-- LOADING -->
<div id="testLoading">
  <div class="load-spinner"></div>
  <p style="color:#64748b;font-size:.9rem;font-weight:500;">Загружаем тест...</p>
</div>

<!-- TEST UI -->
<div id="testUI" class="hidden">

  <!-- Topbar -->
  <div class="tp-topbar">
    <a href="index.php" class="tp-brand">
      <div class="tp-brand-icon">T</div>
      TestPlatform
    </a>
    <div class="tp-title" id="topbarTitle"></div>
    <div class="tp-status active" id="statusBadge">
      <div class="tp-status-dot"></div>
      <span id="statusText">Защита активна</span>
    </div>
    <div class="tp-timer" id="timer">00:00</div>
  </div>

  <!-- Layout -->
  <div class="tp-layout">

    <!-- Main question area -->
    <div class="tp-main">
      <div class="tp-q-header">
        <span class="tp-q-num" id="qNum">Вопрос 1 из 10</span>
        <span class="tp-q-points" id="qPoints">1 балл</span>
      </div>
      <div class="tp-progress-bar">
        <div class="tp-progress-fill" id="progressFill" style="width:0%"></div>
      </div>
      <div class="tp-question-text" id="questionText"></div>
      <div id="hintBlock" class="hidden">
        <span class="tp-hint">Выберите все правильные ответы</span>
      </div>
      <div class="tp-answers" id="answersContainer"></div>
    </div>

    <!-- Sidebar -->
    <div class="tp-sidebar">

      <!-- Progress stats -->
      <div class="tp-sidebar-section">
        <h4>Прогресс</h4>
        <div class="tp-stat-row">
          <div class="tp-stat">
            <div class="tp-stat-val" id="statAnswered">0</div>
            <div class="tp-stat-lbl">Отвечено</div>
          </div>
          <div class="tp-stat">
            <div class="tp-stat-val" id="statLeft">0</div>
            <div class="tp-stat-lbl">Осталось</div>
          </div>
        </div>
      </div>

      <!-- Question map -->
      <div class="tp-sidebar-section">
        <h4>Карта вопросов</h4>
        <div class="tp-qmap" id="questionMap"></div>
      </div>

      <!-- Monitor log -->
      <div class="tp-sidebar-section">
        <h4>Лог мониторинга</h4>
        <div class="tp-log" id="monitorLog">
          <div class="tp-log-item">
            <span class="tp-log-time" id="startTime"></span>
            <span class="tp-log-icon">▶</span>
            <span class="tp-log-text"><strong>Тест начат</strong> · Мониторинг активирован</span>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Bottom nav -->
  <div class="tp-nav">
    <div class="tp-nav-info" id="navInfo">Отвечено: 0 / 0</div>
    <div class="tp-nav-btns">
      <button class="tp-btn tp-btn-outline" id="prevBtn" onclick="goQuestion(currentQ - 1)" disabled>← Назад</button>
      <button class="tp-btn tp-btn-primary" id="nextBtn" onclick="goQuestion(currentQ + 1)">Далее →</button>
      <button class="tp-btn tp-btn-success hidden" id="finishBtn" onclick="confirmSubmit()">✓ Завершить тест</button>
    </div>
  </div>

</div>

<!-- RESULT UI -->
<div id="resultUI" class="hidden">
  <div class="tp-result-card">
    <div class="tp-result-icon" id="resultIcon"></div>
    <div class="tp-result-pct" id="resultScore"></div>
    <div class="tp-result-label" id="resultLabel"></div>
    <div class="tp-result-stats">
      <div class="tp-result-stat">
        <div class="tp-result-stat-val" id="resScore">—</div>
        <div class="tp-result-stat-lbl">Баллов</div>
      </div>
      <div class="tp-result-stat">
        <div class="tp-result-stat-val" id="resTime">—</div>
        <div class="tp-result-stat-lbl">Время</div>
      </div>
      <div class="tp-result-stat">
        <div class="tp-result-stat-val" id="resCheat">—</div>
        <div class="tp-result-stat-lbl">Честность</div>
      </div>
    </div>
    <div id="cheatWarning" class="tp-cheat-warn hidden"></div>
    <div class="tp-result-btns">
      <a href="#" onclick="exportResultPDF(); return false;" class="tp-result-btn outline">📄 Скачать PDF</a>
      <a href="dashboard.php" class="tp-result-btn outline">📋 Мои результаты</a>
      <a href="index.php"     class="tp-result-btn primary">🏠 На главную</a>
    </div>
  </div>
</div>

<!-- DISQUALIFY SCREEN -->
<div id="disqualifyScreen" class="hidden">
  <div class="tp-disq">
    <div class="tp-disq-icon">🚫</div>
    <h1>Тест завершён</h1>
    <p>Вы исчерпали все <strong style="color:#f1f5f9;">3 предупреждения</strong> за сворачивание страницы.<br>
       Тест заблокирован для <strong style="color:#ef4444;">повторного прохождения</strong>.</p>
    <div class="tp-disq-dots">
      <div class="tp-disq-dot"></div>
      <div class="tp-disq-dot"></div>
      <div class="tp-disq-dot"></div>
    </div>
    <a href="dashboard.php" style="display:inline-block;background:#4361ee;color:#fff;
       border-radius:10px;padding:13px 32px;font-size:.95rem;font-weight:700;text-decoration:none;">
      Перейти в личный кабинет
    </a>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="public/js/config.js"></script>
<script src="public/js/app.js"></script>
<script>
window.AC_TEXT = {
  title:     'Смена вкладки',
  subtext:   'Вы переключились на другую вкладку или свернули окно браузера.',
  violation: 'Нарушение',
  of:        'из',
  btn:       'Понятно, продолжить'
};
</script>
<script src="public/js/anticheat.js"></script>
<script src="public/js/eye-tracker.js"></script>
<script>
  if (!Auth.isLoggedIn()) {
    window.location.href = 'login.php?redirect=' + encodeURIComponent(location.href);
  }
  var testId = new URLSearchParams(location.search).get('id');
  if (!testId) window.location.href = 'dashboard.php';

  var test         = null;
  var attemptId    = null;
  var userAnswers  = {};
  var timerInt     = null;
  var totalSeconds = 0;
  var elapsedSec   = 0;
  var antiCheat    = null;
  var eyeTracker   = null;
  var autoSaveInt  = null;
  var isTerminated = false;
  var isSubmitting = false;
  var currentQ     = 0;   // index into test.questions

  // ── INIT ───────────────────────────────────────────────────────────────────
  async function initTest() {
    try {
      var res = await API.startTest(parseInt(testId));
      if (!res.success) {
        var extra = res.disqualified
          ? '<p style="color:#ef4444;margin-top:10px;font-weight:600;">🚫 Тест заблокирован из-за нарушений.</p>' : '';
        document.getElementById('testLoading').innerHTML =
          '<div style="text-align:center;padding:40px;">' +
          '<p style="color:#ef4444;font-weight:700;font-size:1.1rem;">' + escHtml(res.message || 'Ошибка') + '</p>' +
          extra + '<a href="dashboard.php" style="display:inline-block;margin-top:20px;' +
          'background:#4361ee;color:#fff;padding:10px 24px;border-radius:9px;font-weight:700;' +
          'text-decoration:none;">← Назад</a></div>';
        return;
      }

      test         = res.test;
      attemptId    = res.attempt_id;
      totalSeconds = test.time_limit * 60;

      if (res.resumed) {
        var started = new Date(res.started_at);
        elapsedSec  = Math.min(Math.floor((Date.now() - started.getTime()) / 1000), totalSeconds);
      }

      document.getElementById('topbarTitle').textContent = test.title;
      document.getElementById('startTime').textContent   = new Date().toLocaleTimeString('ru', {hour:'2-digit',minute:'2-digit',second:'2-digit'});

      buildQuestionMap();
      renderQuestion(0);
      restoreDraft();
      startTimer();
      startAutoSave();
      initAntiCheat();
      initEyeTracker();

      document.getElementById('testLoading').classList.add('hidden');
      document.getElementById('testUI').classList.remove('hidden');

    } catch(e) {
      document.getElementById('testLoading').innerHTML =
        '<div style="text-align:center;padding:40px;"><p style="color:#ef4444;font-weight:600;">' +
        escHtml(e.message) + '</p></div>';
    }
  }

  // ── QUESTION MAP ───────────────────────────────────────────────────────────
  function buildQuestionMap() {
    var map = document.getElementById('questionMap');
    map.innerHTML = '';
    test.questions.forEach(function(q, i) {
      var btn = document.createElement('button');
      btn.className = 'tp-qmap-btn' + (i === 0 ? ' active' : '');
      btn.textContent = i + 1;
      btn.onclick = function() { goQuestion(i); };
      btn.id = 'qmap-' + i;
      map.appendChild(btn);
    });
    updateStats();
  }

  function updateMap() {
    test.questions.forEach(function(q, i) {
      var btn = document.getElementById('qmap-' + i);
      if (!btn) return;
      var ans = userAnswers[q.id];
      var answered = ans && ans.length > 0;
      btn.className = 'tp-qmap-btn' +
        (i === currentQ ? ' active' : '') +
        (answered ? ' answered' : '');
    });
  }

  // ── RENDER QUESTION ────────────────────────────────────────────────────────
  function renderQuestion(idx) {
    if (!test || idx < 0 || idx >= test.questions.length) return;
    currentQ = idx;
    var q    = test.questions[idx];
    var isMultiple = q.question_type === 'multiple';
    var letters    = ['A','B','C','D','E','F','G','H'];

    document.getElementById('qNum').textContent    = 'Вопрос ' + (idx+1) + ' из ' + test.questions.length;
    document.getElementById('qPoints').textContent = q.points + ' ' + plural(q.points, 'балл','балла','баллов');
    document.getElementById('questionText').textContent = q.question_text;
    document.getElementById('hintBlock').classList.toggle('hidden', !isMultiple);

    var container = document.getElementById('answersContainer');
    container.innerHTML = '';

    var currentSel = userAnswers[q.id] || [];

    q.answers.forEach(function(a, ai) {
      var isSelected = currentSel.indexOf(a.id) !== -1;
      var div = document.createElement('div');
      div.className = 'tp-answer' + (isMultiple ? ' checkbox' : '') + (isSelected ? ' selected' : '');
      div.style.cursor = 'pointer';

      var input = document.createElement('input');
      input.type = isMultiple ? 'checkbox' : 'radio';
      input.name = 'q' + q.id;
      input.value = a.id;
      input.checked = isSelected;
      input.style.display = 'none';

      var marker = document.createElement('div');
      marker.className = 'tp-answer-marker';

      var letter = document.createElement('span');
      letter.className = 'tp-answer-letter';
      letter.textContent = letters[ai] || (ai+1);

      var label = document.createElement('span');
      label.className = 'tp-answer-label';
      label.textContent = escHtml(a.answer_text);

      div.appendChild(input);
      div.appendChild(marker);
      div.appendChild(letter);
      div.appendChild(label);

      div.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        onAnswer(q.id, a.id, isMultiple ? 'checkbox' : 'radio');
        // Update UI for all answers
        container.querySelectorAll('.tp-answer').forEach(function(el, ei) {
          var aid = q.answers[ei].id;
          var sel = (userAnswers[q.id] || []).indexOf(aid) !== -1;
          el.classList.toggle('selected', sel);
          el.querySelector('input').checked = sel;
        });
        updateMap();
        updateStats();
      });

      container.appendChild(div);
    });

    // progress fill
    var pct = ((idx + 1) / test.questions.length * 100);
    document.getElementById('progressFill').style.width = pct + '%';

    // nav buttons
    document.getElementById('prevBtn').disabled = (idx === 0);
    var isLast = (idx === test.questions.length - 1);
    document.getElementById('nextBtn').classList.toggle('hidden', isLast);
    document.getElementById('finishBtn').classList.toggle('hidden', !isLast);

    updateMap();
    updateStats();
  }

  function goQuestion(idx) {
    if (!test || idx < 0 || idx >= test.questions.length) return;
    renderQuestion(idx);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // ── ANSWER ─────────────────────────────────────────────────────────────────
  function onAnswer(qId, aId, type) {
    if (isTerminated) return;
    if (antiCheat) { antiCheat.checkAnswerSpeed(qId); antiCheat.recordQuestionStart(); }

    if (type === 'radio') {
      userAnswers[qId] = [aId];
    } else {
      var cur = userAnswers[qId] || [];
      var ix  = cur.indexOf(aId);
      if (ix === -1) cur.push(aId); else cur.splice(ix, 1);
      userAnswers[qId] = cur;
    }
  }

  // ── STATS ──────────────────────────────────────────────────────────────────
  function updateStats() {
    if (!test) return;
    var total    = test.questions.length;
    var answered = test.questions.filter(function(q) {
      return userAnswers[q.id] && userAnswers[q.id].length > 0;
    }).length;
    document.getElementById('statAnswered').textContent = answered;
    document.getElementById('statLeft').textContent     = total - answered;
    document.getElementById('navInfo').textContent      = 'Отвечено: ' + answered + ' / ' + total;
  }

  // ── TIMER ──────────────────────────────────────────────────────────────────
  function startTimer() {
    var el = document.getElementById('timer');
    timerInt = setInterval(function() {
      elapsedSec++;
      var rem = totalSeconds - elapsedSec;
      if (rem <= 0) { clearInterval(timerInt); submitTest(); return; }
      var m = String(Math.floor(rem / 60)).padStart(2,'0');
      var s = String(rem % 60).padStart(2,'0');
      el.textContent = m + ':' + s;
      el.className = 'tp-timer' + (rem <= 60 ? ' danger' : rem <= totalSeconds * 0.25 ? ' warning' : '');
    }, 1000);
  }

  // ── AUTOSAVE ───────────────────────────────────────────────────────────────
  function startAutoSave() {
    autoSaveInt = setInterval(function() {
      if (!isTerminated) localStorage.setItem('draft_' + attemptId, JSON.stringify(userAnswers));
    }, 30000);
  }

  function restoreDraft() {
    try {
      var d = localStorage.getItem('draft_' + attemptId);
      if (!d) return;
      var saved = JSON.parse(d);
      // Convert keys to ints
      Object.keys(saved).forEach(function(k) { userAnswers[parseInt(k)] = saved[k]; });
      updateMap(); updateStats();
    } catch(e) {}
  }

  // ── ANTI-CHEAT ─────────────────────────────────────────────────────────────
  function initAntiCheat() {
    antiCheat = new AntiCheat({
      attemptId: attemptId,
      onTerminate: handleDisqualification
    });
    antiCheat.start();
    antiCheat.recordQuestionStart();

    // Hook into anticheat to add log entries
    var origTrack = AntiCheat.prototype._trackVisibility;
    // Add log entries on tab switch via override
    var _origHandle = antiCheat._handleSwitch.bind(antiCheat);
    antiCheat._handleSwitch = function() {
      addLog('warn', '⚠', 'Смена вкладки — зафиксировано');
      updateStatusBadge(antiCheat.tabSwitches);
      _origHandle();
    };
  }

  // ── EYE-TRACKING ─────────────────────────────────────────────────────────
  async function initEyeTracker() {
    try {
      eyeTracker = new EyeTracker({
        attemptId: attemptId,
        onGazeData: function(gazePoint) {
          // Optional: handle individual gaze points
          // console.log('Gaze at:', gazePoint.x, gazePoint.y);
        },
        onCalibrationComplete: function() {
          addLog('info', '👁', 'Eye-tracking калиброван и активен');
        }
      });

      var success = await eyeTracker.start();
      
      if (success) {
        addLog('info', '👁', 'Eye-tracking инициализирован...');
      } else {
        addLog('warn', '⚠', 'Eye-tracking недоступен (тест продолжится без него)');
      }
    } catch (err) {
      console.error('[EyeTracker] Init error:', err);
      addLog('warn', '⚠', 'Eye-tracking недоступен');
    }
  }

  function addLog(type, icon, text) {
    var log  = document.getElementById('monitorLog');
    var item = document.createElement('div');
    item.className = 'tp-log-item' + (type === 'warn' ? ' warn' : type === 'error' ? ' error' : '');
    var time = new Date().toLocaleTimeString('ru',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
    item.innerHTML =
      '<span class="tp-log-time">' + time + '</span>' +
      '<span class="tp-log-icon">' + icon + '</span>' +
      '<span class="tp-log-text">' + text + '</span>';
    log.insertBefore(item, log.firstChild);
    // keep max 8 entries
    while (log.children.length > 8) log.removeChild(log.lastChild);
  }

  function updateStatusBadge(violations) {
    var badge = document.getElementById('statusBadge');
    var text  = document.getElementById('statusText');
    if (violations >= 2) {
      badge.className = 'tp-status violation';
      text.textContent = violations + ' нарушения';
    } else if (violations >= 1) {
      badge.className = 'tp-status violation';
      text.textContent = violations + ' нарушение';
    }
  }

  // ── DISQUALIFY ─────────────────────────────────────────────────────────────
  async function handleDisqualification() {
    isTerminated = true;
    clearInterval(timerInt); clearInterval(autoSaveInt);
    if (eyeTracker) eyeTracker.stop();
    localStorage.removeItem('draft_' + attemptId);
    document.getElementById('testUI').classList.add('hidden');
    try {
      await API.submitTest({ attempt_id: attemptId, answers: userAnswers, time_spent: elapsedSec, disqualified: true });
    } catch(e) {}
    document.getElementById('disqualifyScreen').classList.remove('hidden');
  }

  // ── SUBMIT ─────────────────────────────────────────────────────────────────
  function confirmSubmit() {
    var total    = test.questions.length;
    var answered = test.questions.filter(function(q) {
      return userAnswers[q.id] && userAnswers[q.id].length > 0;
    }).length;
    var unanswered = total - answered;

    if (unanswered > 0) {
      if (!confirm('Вы ответили на ' + answered + ' из ' + total + ' вопросов.\nОставить ' + unanswered + ' без ответа и завершить тест?')) return;
    }
    submitTest();
  }

  async function submitTest() {
    if (isTerminated || isSubmitting) return;
    isSubmitting = true;
    clearInterval(timerInt); clearInterval(autoSaveInt);
    if (antiCheat) antiCheat.stop();
    if (eyeTracker) eyeTracker.stop();

    var btn = document.getElementById('finishBtn');
    if (btn) { btn.disabled = true; btn.textContent = 'Отправляем...'; }

    try {
      var res = await API.submitTest({ attempt_id: attemptId, answers: userAnswers, time_spent: elapsedSec });
      localStorage.removeItem('draft_' + attemptId);
      sessionStorage.removeItem('ac_' + attemptId);
      if (res.success) showResult(res.result);
      else { alert(res.message || 'Ошибка'); isSubmitting = false; if(btn){btn.disabled=false;btn.textContent='✓ Завершить тест';} }
    } catch(e) {
      alert(e.message); isSubmitting = false;
      if(btn){btn.disabled=false;btn.textContent='✓ Завершить тест';}
    }
  }

  // ── RESULT ─────────────────────────────────────────────────────────────────
  function showResult(result) {
    document.getElementById('testUI').classList.add('hidden');
    document.getElementById('resultUI').classList.remove('hidden');

    var pct    = parseFloat(result.percentage).toFixed(1);
    var passed = result.passed;
    var cheat  = result.cheat_score;

    document.getElementById('resultIcon').textContent  = passed ? '🏆' : '😔';
    document.getElementById('resultScore').textContent = pct + '%';
    document.getElementById('resultScore').className   = 'tp-result-pct' + (passed ? '' : ' failed');
    document.getElementById('resultLabel').textContent = passed ? 'Тест сдан! Отличная работа!' : 'Тест не сдан. Попробуйте ещё раз.';
    document.getElementById('resScore').textContent    = result.score + '/' + result.max_score;
    document.getElementById('resTime').textContent     = Math.floor(elapsedSec/60) + 'м ' + (elapsedSec%60) + 'с';

    var cheatIcon  = cheat >= 40 ? '⚠️' : cheat >= 15 ? '⚡' : '✅';
    document.getElementById('resCheat').textContent = cheatIcon + ' ' + cheat;

    if (cheat >= 40) {
      var w = document.getElementById('cheatWarning');
      w.classList.remove('hidden');
      w.textContent = '⚠️ Зафиксирована подозрительная активность (' + cheat + '/100). Результат передан преподавателю.';
    }
  }

  // ── HELPERS ────────────────────────────────────────────────────────────────
  function escHtml(str) {
    var d = document.createElement('div'); d.textContent = str || ''; return d.innerHTML;
  }
  function plural(n, one, few, many) {
    var mod10 = n % 10, mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
    return many;
  }

  // ── EXPORT PDF ─────────────────────────────────────────────────────────────
  function exportResultPDF() {
    if (!attemptId) {
      Toast.error('Не удалось найти ID попытки');
      return;
    }
    window.open('api/test.php?action=export_pdf&attempt_id=' + attemptId, '_blank');
  }

  initTest();
</script>
</body>
</html>
