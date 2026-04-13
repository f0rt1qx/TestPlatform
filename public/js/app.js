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
    const iconMap = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
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
    const hdr = { 'Content-Type': 'application/json' };
    const t = AuthManager.getToken();
    t && (hdr['Authorization'] = `Bearer ${t}`);
    return hdr;
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
      throw new Error('Ошибка сети. Проверьте что XAMPP запущен.');
    }

    const ct = httpResponse.headers.get('content-type') ?? '';
    ct.includes('application/json') || (() => {
      throw new Error(
        `Сервер вернул HTML вместо JSON. Проверьте:\n` +
        `1. Apache и MySQL запущены в XAMPP\n` +
        `2. Файл ${reqUrl} существует\n` +
        `3. APP_URL = "${window.APP_URL}" верный`
      );
    })();

    const responseData = await httpResponse.json();
    httpResponse.ok || (() => { throw new Error(responseData.message ?? 'Ошибка сервера'); })();

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
    if (!csrfVal) throw new Error('Не удалось получить CSRF токен');
    return this.post('/auth.php?action=login', { login: username, password, csrf_token: csrfVal });
  },

  async register(regData) {
    await this._freshCsrf();
    const csrfVal = localStorage.getItem('csrf_token');
    if (!csrfVal) throw new Error('Не удалось получить CSRF токен');
    return this.post('/auth.php?action=register', { ...regData, csrf_token: csrfVal });
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
  adminEyeTracking(prms = {}) {
    const qp = new URLSearchParams();
    if (prms.test_id) qp.set('test_id', prms.test_id);
    if (prms.attempt_id) qp.set('attempt_id', prms.attempt_id);
    return this.get('/admin.php?action=eye_tracking&' + qp.toString());
  },
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

  async login(username, password) {
    const apiResp = await API.login(username, password);
    apiResp.success && apiResp.token && this.saveToken(apiResp.token);
    apiResp.csrf_token && localStorage.setItem('csrf_token', apiResp.csrf_token);
    return apiResp;
  },

  async register(regPayload) {
    const apiResp = await API.register(regPayload);
    apiResp.success && apiResp.token && this.saveToken(apiResp.token);
    apiResp.csrf_token && localStorage.setItem('csrf_token', apiResp.csrf_token);
    return apiResp;
  },

  async logout() {
    try { await API.logout(); } catch (err) {
      console.warn('Logout API call failed:', err);
    } finally {
      this._cachedToken = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('csrf_token');
      document.cookie = 'auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
      window.location.href = (window.APP_URL ?? '') + '/index.php';
    }
  },

  isLoggedIn() { return !!this.getToken(); },

  getPayload() {
    const tok = this.getToken();
    tok || (() => { return null; })();
    try {
      const b64 = tok.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
      return JSON.parse(atob(b64));
    } catch (e) {
      return null;
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
    ? (btn.dataset.origText = btn.innerHTML, btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:6px;"></span>Загрузка...', btn.disabled = true)
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
    if (btn) { btn.disabled = false; btn.textContent = 'Импортировать'; }
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
      btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg> Создать тест';
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
      btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg> Добавить';
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