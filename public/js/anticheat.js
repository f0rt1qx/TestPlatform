var AntiCheat = (function() {

  function AntiCheat(cfg) {
    this.attemptId     = cfg.attemptId;
    this.onTerminate   = cfg.onTerminate || function() {};
    this.tabSwitches   = 0;
    this.MAX_SWITCHES  = 3;
    this._terminated   = false;
    this._active       = true;
    this._lastAnswerTs = Date.now();
    this._pendingLogs  = [];
    this._flushTimer   = null;

    const restored = sessionStorage.getItem('ac_' + this.attemptId);
    if (restored) this.tabSwitches = parseInt(restored) || 0;
  }

  AntiCheat.prototype.start = function() {
    this._blockCopyPaste();
    this._disableContextMenu();
    this._monitorVisibility();
    this._monitorFocusLoss();
    this._setupBeforeUnload();
    this._scheduleFlush();

    if (this.tabSwitches >= this.MAX_SWITCHES) {
      this._terminated = true;
      this._active = false;
      this.onTerminate();
    }
  };

  AntiCheat.prototype.stop = function() {
    this._active = false;
    this._sendPendingLogs();
    clearInterval(this._flushTimer);
  };

  AntiCheat.prototype.recordQuestionStart = function() {
    this._lastAnswerTs = Date.now();
  };

  AntiCheat.prototype.checkAnswerSpeed = function(questionId) {
    const timeDiff = (Date.now() - this._lastAnswerTs) / 1000;
    if (timeDiff < 3) this._queueLog('rapid_answer', { q: questionId, s: timeDiff }, 'medium');
  };

  AntiCheat.prototype._blockCopyPaste = function() {
    const ctx = this;
    document.addEventListener('copy', function(ev) {
      if (!ctx._active) return;
      ev.preventDefault();
      ctx._queueLog('copy_attempt', {}, 'low');
    });
    document.addEventListener('keydown', function(ev) {
      if (!ctx._active) return;
      const combo = ev.ctrlKey && 'capus'.indexOf(ev.key.toLowerCase()) !== -1;
      if (combo) {
        ev.preventDefault();
        ctx._queueLog('copy_attempt', { key: ev.key }, 'low');
      }
      const devTools = ev.key === 'F12' || (ev.ctrlKey && ev.shiftKey && 'ijc'.indexOf(ev.key.toLowerCase()) !== -1);
      if (devTools) {
        ev.preventDefault();
        ctx._queueLog('devtools_open', {}, 'high');
      }
    });
    document.body.classList.add('no-select');
  };

  AntiCheat.prototype._disableContextMenu = function() {
    const ctx = this;
    document.addEventListener('contextmenu', function(ev) {
      if (!ctx._active) return;
      ev.preventDefault();
      ctx._queueLog('right_click', {}, 'low');
    });
  };

  AntiCheat.prototype._monitorVisibility = function() {
    const ctx = this;
    document.addEventListener('visibilitychange', function() {
      if (!ctx._active || ctx._terminated) return;
      if (!document.hidden) return;
      ctx.tabSwitches++;
      sessionStorage.setItem('ac_' + ctx.attemptId, String(ctx.tabSwitches));
      ctx._queueLog('tab_switch', { count: ctx.tabSwitches }, 'high');
      ctx._onTabSwitch();
    });
  };

  AntiCheat.prototype._monitorFocusLoss = function() {
    const ctx = this;
    window.addEventListener('blur', function() {
      if (!ctx._active) return;
      ctx._queueLog('window_blur', {}, 'medium');
    });
  };

  AntiCheat.prototype._setupBeforeUnload = function() {
    const ctx = this;
    window.addEventListener('beforeunload', function(ev) {
      if (!ctx._active) return;
      ctx._sendPendingLogs();
      ev.preventDefault();
      ev.returnValue = '';
    });
  };

  AntiCheat.prototype._onTabSwitch = function() {
    if (this.tabSwitches >= this.MAX_SWITCHES) {
      this._terminated = true;
      this._active = false;
      this._sendPendingLogs();
      this.onTerminate();
    } else {
      this._renderWarningModal(this.tabSwitches);
    }
  };

  AntiCheat.prototype._showModal = function(used) {
    const existingModal = document.getElementById('ac-modal');
    if (existingModal) existingModal.remove();

    const isFinalWarning = (used === this.MAX_SWITCHES - 1);
    const dict = window.AC_TEXT || {};
    const modalEl = document.createElement('div');
    modalEl.id = 'ac-modal';
    modalEl.style.cssText =
      'position:fixed;inset:0;z-index:99999;' +
      'display:flex;align-items:center;justify-content:center;' +
      'padding:20px;background:rgba(0,0,0,0.45);' +
      'backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);' +
      'animation:acFadeIn .2s ease;';

    const borderClr = '#ef4444';
    const btnBg = isFinalWarning ? '#dc2626' : '#ef4444';
    const violationTxt = (dict.violation || 'Violation') + ' ' + used + ' ' + (dict.of || 'of') + ' ' + this.MAX_SWITCHES;

    modalEl.innerHTML =
      '<div style="background:#fff;border:2px solid ' + borderClr + ';border-radius:16px;max-width:420px;width:100%;padding:40px 36px 36px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.25);animation:acSlideUp .25s ease;position:relative;">' +
        '<div style="margin-bottom:20px;"><svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#1a1a1a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>' +
        '<h2 style="color:#ef4444;font-size:1.35rem;font-weight:800;margin-bottom:12px;font-family:system-ui,sans-serif;">' + (dict.title || 'Tab switch') + '</h2>' +
        '<p style="color:#555;font-size:.9rem;margin-bottom:28px;line-height:1.6;font-family:system-ui,sans-serif;">' + (dict.subtext || 'You switched tabs or minimized the browser.') + '</p>' +
        '<div style="margin-bottom:28px;"><span style="display:inline-block;color:#ef4444;font-weight:700;font-size:.9rem;font-family:system-ui,sans-serif;">' + violationTxt + '</span></div>' +
        '<button id="ac-ok" style="background:' + btnBg + ';color:#fff;border:none;border-radius:10px;padding:14px 32px;font-size:.95rem;font-weight:700;cursor:pointer;width:100%;font-family:system-ui,sans-serif;transition:opacity .15s;">' + (dict.btn || 'Got it, continue') + '</button>' +
      '</div>';

    document.body.appendChild(modalEl);

    document.getElementById('ac-ok').addEventListener('click', function() {
      modalEl.style.opacity = '0';
      modalEl.style.transition = 'opacity .15s';
      setTimeout(function() { if (modalEl.parentNode) modalEl.remove(); }, 150);
    });

    modalEl.addEventListener('click', function(ev) {
      if (ev.target === modalEl) ev.stopPropagation();
    });
  };

  AntiCheat.prototype._log = function(type, eventData, sev) {
    this._logQueue.push({ event_type: type, data: eventData || {}, severity: sev || 'low' });
  };

  AntiCheat.prototype._flush = function() {
    if (!this._logQueue.length || !this.attemptId) return;
    const batch = this._logQueue.slice();
    this._logQueue = [];
    const refId = this.attemptId;
    batch.forEach(function(item) {
      if (typeof API !== 'undefined') {
        API.logEvent({ attempt_id: refId, event_type: item.event_type, data: item.data }).catch(function() {});
      }
    });
  };

  AntiCheat.prototype._startFlushTimer = function() {
    const ctx = this;
    this._flushTimer = setInterval(function() { ctx._flush(); }, 5000);
  };

  return AntiCheat;
})();

;(function injectAntiCheatStyles() {
  const styleTag = document.createElement('style');
  styleTag.textContent =
    '.no-select{-webkit-user-select:none;-moz-user-select:none;user-select:none;}' +
    '@keyframes acFadeIn{from{opacity:0}to{opacity:1}}' +
    '@keyframes acSlideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}' +
    '#ac-ok:hover{opacity:.85}';
  document.head.appendChild(styleTag);
})();

if (typeof module !== 'undefined' && module.exports) {
  module.exports = AntiCheat;
}