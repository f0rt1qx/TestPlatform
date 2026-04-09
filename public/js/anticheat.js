/* anticheat.js - Anti-cheat system. 3 tab switches = disqualification */

var AntiCheat = (function() {

  function AntiCheat(options) {
    this.attemptId        = options.attemptId;
    this.onTerminate      = options.onTerminate || function() {};
    this.tabSwitches      = 0;
    this.MAX_SWITCHES     = 3;
    this._terminated      = false;
    this._active          = true;
    this._lastAnswerTime  = Date.now();
    this._logQueue        = [];
    this._flushTimer      = null;

    var saved = sessionStorage.getItem('ac_' + this.attemptId);
    if (saved) this.tabSwitches = parseInt(saved) || 0;
  }

  AntiCheat.prototype.start = function() {
    this._blockCopy();
    this._blockRightClick();
    this._trackVisibility();
    this._trackBlur();
    this._warnBeforeLeave();
    this._startFlushTimer();

    if (this.tabSwitches >= this.MAX_SWITCHES) {
      this._terminated = true;
      this._active = false;
      this.onTerminate();
    }
  };

  AntiCheat.prototype.stop = function() {
    this._active = false;
    this._flush();
    clearInterval(this._flushTimer);
  };

  AntiCheat.prototype.recordQuestionStart = function() {
    this._lastAnswerTime = Date.now();
  };

  AntiCheat.prototype.checkAnswerSpeed = function(qId) {
    var elapsed = (Date.now() - this._lastAnswerTime) / 1000;
    if (elapsed < 3) this._log('rapid_answer', { q: qId, s: elapsed }, 'medium');
  };

  AntiCheat.prototype._blockCopy = function() {
    var self = this;
    document.addEventListener('copy', function(e) {
      if (!self._active) return;
      e.preventDefault();
      self._log('copy_attempt', {}, 'low');
    });
    document.addEventListener('keydown', function(e) {
      if (!self._active) return;
      if (e.ctrlKey && 'capus'.indexOf(e.key.toLowerCase()) !== -1) {
        e.preventDefault();
        self._log('copy_attempt', { key: e.key }, 'low');
      }
      if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && 'ijc'.indexOf(e.key.toLowerCase()) !== -1)) {
        e.preventDefault();
        self._log('devtools_open', {}, 'high');
      }
    });
    document.body.classList.add('no-select');
  };

  AntiCheat.prototype._blockRightClick = function() {
    var self = this;
    document.addEventListener('contextmenu', function(e) {
      if (!self._active) return;
      e.preventDefault();
      self._log('right_click', {}, 'low');
    });
  };

  AntiCheat.prototype._trackVisibility = function() {
    var self = this;
    document.addEventListener('visibilitychange', function() {
      if (!self._active || self._terminated) return;
      if (document.hidden) {
        self.tabSwitches++;
        sessionStorage.setItem('ac_' + self.attemptId, self.tabSwitches);
        self._log('tab_switch', { count: self.tabSwitches }, 'high');
        self._handleSwitch();
      }
    });
  };

  AntiCheat.prototype._trackBlur = function() {
    var self = this;
    window.addEventListener('blur', function() {
      if (!self._active) return;
      self._log('window_blur', {}, 'medium');
    });
  };

  AntiCheat.prototype._warnBeforeLeave = function() {
    var self = this;
    window.addEventListener('beforeunload', function(e) {
      if (!self._active) return;
      self._flush();
      e.preventDefault();
      e.returnValue = '';
    });
  };

  AntiCheat.prototype._handleSwitch = function() {
    if (this.tabSwitches >= this.MAX_SWITCHES) {
      this._terminated = true;
      this._active = false;
      this._flush();
      this.onTerminate();
    } else {
      this._showModal(this.tabSwitches);
    }
  };

  AntiCheat.prototype._showModal = function(used) {
    var old = document.getElementById('ac-modal');
    if (old) old.remove();

    var isLast = (used === this.MAX_SWITCHES - 1);
    var t = window.AC_TEXT || {};

    var wrap = document.createElement('div');
    wrap.id = 'ac-modal';
    wrap.style.cssText =
      'position:fixed;inset:0;z-index:99999;' +
      'display:flex;align-items:center;justify-content:center;' +
      'padding:20px;background:rgba(0,0,0,0.45);' +
      'backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);' +
      'animation:acFadeIn .2s ease;';

    var borderColor = isLast ? '#ef4444' : '#ef4444';
    var btnBg       = isLast ? '#dc2626' : '#ef4444';
    var counterText = (t.violation || 'Violation') + ' ' + used + ' ' + (t.of || 'of') + ' ' + this.MAX_SWITCHES;

    wrap.innerHTML =
      '<div style="' +
        'background:#fff;' +
        'border:2px solid ' + borderColor + ';' +
        'border-radius:16px;' +
        'max-width:420px;width:100%;' +
        'padding:40px 36px 36px;' +
        'text-align:center;' +
        'box-shadow:0 20px 60px rgba(0,0,0,.25);' +
        'animation:acSlideUp .25s ease;' +
        'position:relative;' +
      '">' +

        /* eye icon */
        '<div style="margin-bottom:20px;">' +
          '<svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="#1a1a1a" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>' +
            '<circle cx="12" cy="12" r="3"/>' +
          '</svg>' +
        '</div>' +

        /* title */
        '<h2 style="' +
          'color:#ef4444;font-size:1.35rem;font-weight:800;margin-bottom:12px;' +
          'font-family:system-ui,sans-serif;' +
        '">' + (t.title || 'Tab switch') + '</h2>' +

        /* subtitle */
        '<p style="color:#555;font-size:.9rem;margin-bottom:28px;line-height:1.6;font-family:system-ui,sans-serif;">' +
          (t.subtext || 'You switched tabs or minimized the browser.') +
        '</p>' +

        /* counter badge */
        '<div style="margin-bottom:28px;">' +
          '<span style="' +
            'display:inline-block;' +
            'color:#ef4444;font-weight:700;font-size:.9rem;' +
            'font-family:system-ui,sans-serif;' +
          '">' + counterText + '</span>' +
        '</div>' +

        /* button */
        '<button id="ac-ok" style="' +
          'background:' + btnBg + ';color:#fff;border:none;' +
          'border-radius:10px;padding:14px 32px;font-size:.95rem;font-weight:700;' +
          'cursor:pointer;width:100%;font-family:system-ui,sans-serif;' +
          'transition:opacity .15s;' +
        '">' + (t.btn || 'Got it, continue') + '</button>' +

      '</div>';

    document.body.appendChild(wrap);

    document.getElementById('ac-ok').addEventListener('click', function() {
      wrap.style.opacity = '0';
      wrap.style.transition = 'opacity .15s';
      setTimeout(function() { if (wrap.parentNode) wrap.remove(); }, 150);
    });

    wrap.addEventListener('click', function(e) {
      if (e.target === wrap) {
        /* clicking backdrop does nothing - must press button */
        e.stopPropagation();
      }
    });
  };

  AntiCheat.prototype._log = function(type, data, sev) {
    this._logQueue.push({ event_type: type, data: data || {}, severity: sev || 'low' });
  };

  AntiCheat.prototype._flush = function() {
    if (!this._logQueue.length || !this.attemptId) return;
    var q = this._logQueue.slice();
    this._logQueue = [];
    var aid = this.attemptId;
    q.forEach(function(item) {
      if (typeof API !== 'undefined') {
        API.logEvent({ attempt_id: aid, event_type: item.event_type, data: item.data })
           .catch(function() {});
      }
    });
  };

  AntiCheat.prototype._startFlushTimer = function() {
    var self = this;
    this._flushTimer = setInterval(function() { self._flush(); }, 5000);
  };

  return AntiCheat;
})();

/* CSS */
(function() {
  var s = document.createElement('style');
  s.textContent =
    '.no-select{-webkit-user-select:none;-moz-user-select:none;user-select:none;}' +
    '@keyframes acFadeIn{from{opacity:0}to{opacity:1}}' +
    '@keyframes acSlideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}' +
    '#ac-ok:hover{opacity:.85}';
  document.head.appendChild(s);
})();
