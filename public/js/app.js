const ThemeManager = {
  currentTheme: null,

  init() {
    this.currentTheme = localStorage.getItem('theme') ?? 'light';
    this.apply(this.currentTheme);
  },

  apply(themeName) {
    document.documentElement.setAttribute('data-theme', themeName);
    localStorage.setItem('theme', themeName);
    this._refreshToggleIcons();
  },

  toggle() {
    const curr = document.documentElement.getAttribute('data-theme') ?? 'light';
    this.apply(curr === 'dark' ? 'light' : 'dark');
  },

  _refreshToggleIcons() {
    const activeTheme = document.documentElement.getAttribute('data-theme');
    for (const toggleEl of document.querySelectorAll('[data-theme-toggle]')) {
      const iconEl = toggleEl.querySelector('i');
      iconEl && (iconEl.className = activeTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun');
    }
  }
};
ThemeManager.init();

ThemeManager._animTimer = null;
ThemeManager._ensureToggleMarkup = function (toggleEl) {
  if (!toggleEl.querySelector('.theme-toggle-track')) {
    toggleEl.innerHTML = '<span class="theme-toggle-track" aria-hidden="true"><span class="theme-toggle-thumb"></span></span>';
  }
  return {
    track: toggleEl.querySelector('.theme-toggle-track'),
    thumb: toggleEl.querySelector('.theme-toggle-thumb')
  };
};

ThemeManager._applyInlineToggleStyles = function (toggleEl, activeTheme) {
  const isDashboardToggle = toggleEl.classList.contains('theme-toggle') && !!toggleEl.querySelector('[data-theme-state-label]');
  if (isDashboardToggle) {
    return;
  }

  const { track, thumb } = this._ensureToggleMarkup(toggleEl);
  if (!track || !thumb) {
    return;
  }

  Object.assign(toggleEl.style, {
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '48px',
    minWidth: '48px',
    maxWidth: '48px',
    height: '26px',
    minHeight: '26px',
    maxHeight: '26px',
    margin: '0',
    padding: '0',
    border: 'none',
    background: 'transparent',
    boxShadow: 'none',
    transform: 'none',
    transition: 'none',
    overflow: 'visible',
    lineHeight: '0',
    verticalAlign: 'middle',
    appearance: 'none',
    WebkitAppearance: 'none',
    outline: 'none'
  });

  Object.assign(track.style, {
    display: 'block',
    position: 'relative',
    width: '48px',
    minWidth: '48px',
    maxWidth: '48px',
    height: '26px',
    minHeight: '26px',
    maxHeight: '26px',
    margin: '0',
    padding: '0',
    borderRadius: '999px',
    background: activeTheme === 'dark'
      ? 'linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(14, 116, 144, 0.88))'
      : 'linear-gradient(135deg, rgba(0, 200, 83, 0.22), rgba(105, 240, 174, 0.34))',
    border: activeTheme === 'dark'
      ? '1px solid rgba(125, 211, 252, 0.26)'
      : '1px solid rgba(0, 200, 83, 0.22)',
    boxShadow: 'inset 0 1px 4px rgba(255, 255, 255, 0.22)',
    transform: 'none',
    transition: 'none',
    overflow: 'hidden'
  });

  Object.assign(thumb.style, {
    position: 'absolute',
    top: '50%',
    left: activeTheme === 'dark' ? '24px' : '2px',
    width: '20px',
    height: '20px',
    margin: '0',
    borderRadius: '50%',
    background: activeTheme === 'dark'
      ? 'linear-gradient(135deg, #93c5fd 0%, #60a5fa 100%)'
      : 'linear-gradient(135deg, #ffe082 0%, #fbbf24 100%)',
    boxShadow: activeTheme === 'dark'
      ? '0 10px 18px rgba(96, 165, 250, 0.24)'
      : '0 10px 18px rgba(251, 191, 36, 0.28)',
    transform: 'translateY(-50%)',
    transition: 'left 0.24s cubic-bezier(0.2, 0.8, 0.2, 1), background 0.24s ease, box-shadow 0.24s ease'
  });
};

ThemeManager._refreshToggleState = function () {
  const activeTheme = document.documentElement.getAttribute('data-theme');
  for (const toggleEl of document.querySelectorAll('[data-theme-toggle]')) {
    toggleEl.setAttribute('aria-pressed', activeTheme === 'dark' ? 'true' : 'false');
    toggleEl.dataset.themeState = activeTheme;
    this._applyInlineToggleStyles(toggleEl, activeTheme);
    const stateLabel = toggleEl.querySelector('[data-theme-state-label]');
    if (stateLabel) {
      stateLabel.textContent = activeTheme === 'dark' ? 'Тёмная' : 'Светлая';
    }
  }
};

ThemeManager.apply = function (themeName) {
  document.documentElement.classList.add('theme-animating');
  document.documentElement.setAttribute('data-theme', themeName);
  localStorage.setItem('theme', themeName);
  this.currentTheme = themeName;
  this._refreshToggleState();
  clearTimeout(this._animTimer);
  this._animTimer = setTimeout(() => {
    document.documentElement.classList.remove('theme-animating');
  }, 320);
};

ThemeManager._refreshToggleState();

document.addEventListener('DOMContentLoaded', () => {
  for (const btn of document.querySelectorAll('[data-theme-toggle]')) {
    btn.addEventListener('click', e => { e.preventDefault(); ThemeManager.toggle(); });
  }

  const burgerEl = document.querySelector('.burger');
  const navMenu = document.querySelector('.navbar-nav');
  burgerEl && navMenu && burgerEl.addEventListener('click', () => navMenu.classList.toggle('open'));

  AuthManager.updateNavbar();
});

const NotificationToast = {
  _container: null,

  init() {
    this._container ??= document.createElement('div');
    this._container.className = 'toast-container';
    document.body.appendChild(this._container);
  },

  show(msg, kind = 'info', duration = 4000) {
    this.init();
    const iconMap = { success: 'вњ…', error: 'вќЊ', warning: 'вљ пёЏ', info: 'в„№пёЏ' };
    const el = document.createElement('div');
    el.className = `toast ${kind}`;
    el.innerHTML = `<span>${iconMap[kind] ?? iconMap.info}</span><span>${msg}</span>`;
    this._container.appendChild(el);

    setTimeout(() => {
      el.style.animation = 'fadeOut .3s ease forwards';
      setTimeout(() => el.remove(), 300);
    }, duration);
  },

  success(m) { this.show(m, 'success'); },
  error(m) { this.show(m, 'error', 6000); },
  warning(m) { this.show(m, 'warning'); }
};

const API = {
  get baseUrl() {
    return (window.APP_URL || '') + '/api';
  },

  _getAuthHeaders() {
    const headers = { 'Content-Type': 'application/json' };
    const token = localStorage.getItem('auth_token');
    if (token) {
      headers.Authorization = `Bearer ${token}`;
    }
    return headers;
  },

  async request(endpoint, method = 'GET', payload = null) {
    const reqUrl = this.baseUrl + endpoint;
    const fetchOpts = {
      method,
      headers: this._getAuthHeaders(),
      credentials: 'include',
    };
    payload && (fetchOpts.body = JSON.stringify(payload));

    let httpResponse;
    try {
      httpResponse = await fetch(reqUrl, fetchOpts);
    } catch (netErr) {
      throw new Error('РћС€РёР±РєР° СЃРµС‚Рё. РџСЂРѕРІРµСЂСЊС‚Рµ С‡С‚Рѕ XAMPP Р·Р°РїСѓС‰РµРЅ.');
    }

    const ct = httpResponse.headers.get('content-type') ?? '';
    ct.includes('application/json') || (() => {
      throw new Error(
        `РЎРµСЂРІРµСЂ РІРµСЂРЅСѓР» HTML РІРјРµСЃС‚Рѕ JSON. РџСЂРѕРІРµСЂСЊС‚Рµ:\n` +
        `1. Apache Рё MySQL Р·Р°РїСѓС‰РµРЅС‹ РІ XAMPP\n` +
        `2. Р¤Р°Р№Р» ${reqUrl} СЃСѓС‰РµСЃС‚РІСѓРµС‚\n` +
        `3. APP_URL = "${window.APP_URL}" РІРµСЂРЅС‹Р№`
      );
    })();

    const responseData = await httpResponse.json();
    httpResponse.ok || (() => { throw new Error(responseData.message ?? 'РћС€РёР±РєР° СЃРµСЂРІРµСЂР°'); })();

    return responseData;
  },

  get(endpoint) { return this.request(endpoint, 'GET'); },
  post(endpoint, body) { return this.request(endpoint, 'POST', body); },
  delete(endpoint) { return this.request(endpoint, 'DELETE'); },


  _csrfPromise: null,

  async _ensureCsrf() {
    if (this._csrfPromise) { await this._csrfPromise; return; }
    this._csrfPromise = (async () => {
      try {
        const resp = await fetch((window.APP_URL ?? '') + '/api/auth.php?action=csrf_token', {
          credentials: 'include'
        });
        if (resp.ok) {
          const ct = resp.headers.get('content-type') || '';
          if (ct.includes('application/json')) {
            const d = await resp.json();
            if (d.csrf_token) {
              localStorage.setItem('csrf_token', d.csrf_token);
            }
          }
        }
      } catch (e) { /* ignore */ }
      finally { this._csrfPromise = null; }
    })();
    await this._csrfPromise;
  },

  async _freshCsrf() {
    this._csrfPromise = null;
    await this._ensureCsrf();
  },

  async login(username, password) {
    await this._freshCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    // CSRF token is optional for login (server doesn't validate it)
    // but we still try to get one for subsequent requests
    return this.post('/auth.php?action=login', { login: username, password, csrf_token: csrfVal || '' });
  },

  async register(regData) {
    await this._freshCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    // CSRF token is optional for register
    return this.post('/auth.php?action=register', { ...regData, csrf_token: csrfVal || '' });
  },

  logout() { return this.post('/auth.php?action=logout', {}); },
  getMe() { return this.get('/auth.php?action=me'); },

  getTests() { return this.get('/test.php?action=list'); },

  async startTest(tid) {
    await this._ensureCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/test.php?action=start', { test_id: tid, csrf_token: csrfVal });
  },

  async submitTest(submissionData) {
    await this._ensureCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/test.php?action=submit', { ...submissionData, csrf_token: csrfVal });
  },

  logEvent(evtData) { return this.post('/test.php?action=log_event', evtData); },
  getMyResults() { return this.get('/test.php?action=my_results'); },
  getResultDetail(attemptId) { return this.get(`/test.php?action=result_detail&attempt_id=${attemptId}`); },

  adminUsers() { return this.get('/admin.php?action=users'); },
  adminTests() { return this.get('/admin.php?action=tests'); },
  adminLogs() { return this.get('/admin.php?action=logs'); },
  adminResults() { return this.get('/admin.php?action=results'); },

  async createTest(testData) {
    await this._ensureCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=create_test', { ...testData, csrf_token: csrfVal });
  },

  deleteTest(tid) { return this.delete(`/admin.php?action=delete_test&test_id=${tid}`); },

  async toggleTest(tid, isActive) {
    await this._ensureCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=toggle_test', { test_id: tid, active: isActive, csrf_token: csrfVal });
  },

  async blockUser(uid, isBlocked) {
    await this._ensureCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=block_user', { user_id: uid, block: isBlocked, csrf_token: csrfVal });
  },

  async addQuestion(qData) {
    await this._ensureCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=add_question', { ...qData, csrf_token: csrfVal });
  },
};

const AuthManager = {
  _cachedToken: null,
  _cachedUser: null,

  getToken() {
    this._cachedToken ??= localStorage.getItem('auth_token') ?? this._readCookie('auth_token');
    return this._cachedToken ?? null;
  },

  _readCookie(cookieName) {
    const cookieMatch = document.cookie.match(new RegExp('(?:^|; )' + cookieName + '=([^;]*)'));
    return cookieMatch ? decodeURIComponent(cookieMatch[1]) : null;
  },

  saveToken(tok) {
    this._cachedToken = tok;
    localStorage.setItem('auth_token', tok);
  },

  getUser() {
    if (this._cachedUser) return this._cachedUser;
    const raw = localStorage.getItem('auth_user');
    if (!raw) return null;
    try {
      this._cachedUser = JSON.parse(raw);
      return this._cachedUser;
    } catch (_) {
      localStorage.removeItem('auth_user');
      return null;
    }
  },

  saveUser(user) {
    this._cachedUser = user ?? null;
    if (user) {
      localStorage.setItem('auth_user', JSON.stringify(user));
    } else {
      localStorage.removeItem('auth_user');
    }
  },

  async login(username, password) {
    const apiResp = await API.login(username, password);
    apiResp.token && this.saveToken(apiResp.token);
    apiResp.success && apiResp.user && this.saveUser(apiResp.user);
    apiResp.csrf_token && localStorage.setItem('csrf_token', apiResp.csrf_token);
    return apiResp;
  },

  async register(regPayload) {
    const apiResp = await API.register(regPayload);
    apiResp.token && this.saveToken(apiResp.token);
    apiResp.success && apiResp.user && this.saveUser(apiResp.user);
    apiResp.csrf_token && localStorage.setItem('csrf_token', apiResp.csrf_token);
    return apiResp;
  },

  async logout() {
    try { await API.logout(); } catch (err) {
      console.warn('Logout API call failed:', err);
    } finally {
      this._cachedToken = null;
      this._cachedUser = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      localStorage.removeItem('csrf_token');
      document.cookie = 'auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
      window.location.href = (window.APP_URL ?? '') + '/index.php';
    }
  },

  isLoggedIn() {
    return !!(this.getUser() || this.getToken());
  },

  getPayload() {
    const tok = this.getToken();
    if (!tok) {
      return this.getUser();
    }
    try {
      const b64 = tok.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
      return JSON.parse(atob(b64));
    } catch (e) {
      return this.getUser();
    }
  },

  isAdmin() {
    const pl = this.getPayload();
    return pl?.role === 'admin';
  },

  updateNavbar() {
    const loggedIn = this.isLoggedIn();
    const pl = this.getPayload();

    for (const el of document.querySelectorAll('[data-guest]')) el.classList.toggle('hidden', !!loggedIn);
    for (const el of document.querySelectorAll('[data-user]')) el.classList.toggle('hidden', !loggedIn);
    for (const el of document.querySelectorAll('[data-admin]')) el.classList.toggle('hidden', !this.isAdmin());

    pl && (() => {
      for (const el of document.querySelectorAll('[data-username]')) el.textContent = pl.username;
    })();
  }
};

function setLoading(btn, loading) {
  loading
    ? (btn.dataset.origText = btn.innerHTML, btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:6px;"></span>Р—Р°РіСЂСѓР·РєР°...', btn.disabled = true)
    : (btn.innerHTML = btn.dataset.origText ?? btn.innerHTML, btn.disabled = false);
}

function clearErrors(form) {
  for (const e of form.querySelectorAll('.form-error')) e.remove();
  for (const e of form.querySelectorAll('.form-control')) e.style.borderColor = '';
}

function openModal(id) {
  document.getElementById(id)?.classList.remove('hidden');
}

function closeModal(id) {
  document.getElementById(id)?.classList.add('hidden');

  // Reset import modal state
  if (id === 'importTestModal') {
    const form = document.getElementById('importForm');
    if (form) form.reset();
    const placeholder = document.querySelector('.file-upload-placeholder');
    const nameEl = document.querySelector('.file-upload-name');
    const result = document.getElementById('importResult');
    const progress = document.getElementById('importProgress');
    if (placeholder) placeholder.classList.remove('hidden');
    if (nameEl) { nameEl.classList.add('hidden'); nameEl.textContent = ''; }
    if (result) { result.classList.add('hidden'); result.innerHTML = ''; }
    if (progress) progress.classList.add('hidden');
    const btn = document.getElementById('importBtn');
    if (btn) { btn.disabled = false; btn.textContent = 'РРјРїРѕСЂС‚РёСЂРѕРІР°С‚СЊ'; }
  }

  // Reset create test modal state
  if (id === 'createTestModal') {
    const form = document.getElementById('createTestForm');
    if (form) {
      form.reset();
      document.getElementById('testTime').value = '30';
      document.getElementById('testAttempts').value = '1';
      document.getElementById('testPassScore').value = '60';
    }
    const btn = document.getElementById('createTestBtn');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg> РЎРѕР·РґР°С‚СЊ С‚РµСЃС‚';
    }
  }

  // Reset add question modal state
  if (id === 'addQuestionModal') {
    const form = document.getElementById('addQuestionForm');
    if (form) form.reset();
    const container = document.getElementById('answersContainer');
    if (container) container.innerHTML = '';
    const btn = document.getElementById('addQBtn');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg> Р”РѕР±Р°РІРёС‚СЊ';
    }
  }
}

document.addEventListener('click', e => {
  e.target.classList.contains('modal-overlay') && e.target.classList.add('hidden');
});

async function initCsrfToken() {
  await API._ensureCsrf();
}

document.readyState === 'loading'
  ? document.addEventListener('DOMContentLoaded', initCsrfToken)
  : initCsrfToken();

