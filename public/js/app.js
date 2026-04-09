const ThemeManager = {
  currentTheme: null,

  init() {
    this.currentTheme = localStorage.getItem('theme') || 'light';
    this.apply(this.currentTheme);
  },

  apply(themeName) {
    document.documentElement.setAttribute('data-theme', themeName);
    localStorage.setItem('theme', themeName);
    this._refreshToggleIcons();
  },

  toggle() {
    const curr = document.documentElement.getAttribute('data-theme') || 'light';
    const nextTheme = curr === 'dark' ? 'light' : 'dark';
    this.apply(nextTheme);
  },

  _refreshToggleIcons() {
    const activeTheme = document.documentElement.getAttribute('data-theme');
    const toggles = document.querySelectorAll('[data-theme-toggle]');
    toggles.forEach(function(toggle) {
      const iconEl = toggle.querySelector('i');
      if (!iconEl) return;
      iconEl.className = activeTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
    });
  }
};
ThemeManager.init();

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      ThemeManager.toggle();
    });
  });

  const burgerEl = document.querySelector('.burger');
  const navMenu = document.querySelector('.navbar-nav');
  if (burgerEl && navMenu) {
    burgerEl.addEventListener('click', function() {
      navMenu.classList.toggle('open');
    });
  }

  AuthManager.updateNavbar();
});

const NotificationToast = {
  _container: null,

  init() {
    if (this._container) return;
    this._container = document.createElement('div');
    this._container.className = 'toast-container';
    document.body.appendChild(this._container);
  },

  show(msg, kind = 'info', duration = 4000) {
    this.init();
    const iconMap = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const el = document.createElement('div');
    el.className = `toast ${kind}`;
    el.innerHTML = `<span>${iconMap[kind] || iconMap.info}</span><span>${msg}</span>`;
    this._container.appendChild(el);

    setTimeout(function() {
      el.style.animation = 'fadeOut .3s ease forwards';
      setTimeout(function() { el.remove(); }, 300);
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
    if (t) hdr['Authorization'] = `Bearer ${t}`;
    return hdr;
  },

  async request(endpoint, method = 'GET', payload = null) {
    const reqUrl = this.baseUrl + endpoint;
    const fetchOpts = {
      method,
      headers: this._getAuthHeaders(),
      credentials: 'include',
    };

    if (payload) fetchOpts.body = JSON.stringify(payload);

    let httpResponse;
    try {
      httpResponse = await fetch(reqUrl, fetchOpts);
    } catch (netErr) {
      throw new Error('Ошибка сети. Проверьте что XAMPP запущен.');
    }

    const ct = httpResponse.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const rawText = await httpResponse.text();
      console.error('[API] Получен не-JSON ответ от', reqUrl, ':', rawText.substring(0, 200));
      throw new Error(
        `Сервер вернул HTML вместо JSON. Проверьте:\n` +
        `1. Apache и MySQL запущены в XAMPP\n` +
        `2. Файл ${reqUrl} существует\n` +
        `3. APP_URL = "${window.APP_URL}" верный`
      );
    }

    const responseData = await httpResponse.json();
    if (!httpResponse.ok) {
      throw new Error(responseData.message || 'Ошибка сервера');
    }

    return responseData;
  },

  get(endpoint) { return this.request(endpoint, 'GET'); },
  post(endpoint, body) { return this.request(endpoint, 'POST', body); },
  delete(endpoint) { return this.request(endpoint, 'DELETE'); },


  async login(username, password) {
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/auth.php?action=login', { login: username, password, csrf_token: csrfVal });
  },

  async register(regData) {
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/auth.php?action=register', { ...regData, csrf_token: csrfVal });
  },

  logout() { return this.post('/auth.php?action=logout', {}); },
  getMe() { return this.get('/auth.php?action=me'); },

  getTests() { return this.get('/test.php?action=list'); },

  async startTest(tid) {
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/test.php?action=start', { test_id: tid, csrf_token: csrfVal });
  },

  async submitTest(submissionData) {
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
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=create_test', { ...testData, csrf_token: csrfVal });
  },

  deleteTest(tid) { return this.delete(`/admin.php?action=delete_test&test_id=${tid}`); },

  async toggleTest(tid, isActive) {
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=toggle_test', { test_id: tid, active: isActive, csrf_token: csrfVal });
  },

  async blockUser(uid, isBlocked) {
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=block_user', { user_id: uid, block: isBlocked, csrf_token: csrfVal });
  },

  async addQuestion(qData) {
    const csrfVal = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=add_question', { ...qData, csrf_token: csrfVal });
  },
};

const AuthManager = {
  _cachedToken: null,

  getToken() {
    if (!this._cachedToken) {
      this._cachedToken = localStorage.getItem('auth_token') || this._readCookie('auth_token');
    }
    return this._cachedToken || null;
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
    if (apiResp.success && apiResp.token) this.saveToken(apiResp.token);
    if (apiResp.csrf_token) localStorage.setItem('csrf_token', apiResp.csrf_token);
    return apiResp;
  },

  async register(regPayload) {
    const apiResp = await API.register(regPayload);
    if (apiResp.success && apiResp.token) this.saveToken(apiResp.token);
    if (apiResp.csrf_token) localStorage.setItem('csrf_token', apiResp.csrf_token);
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
      window.location.href = (window.APP_URL || '') + '/index.php';
    }
  },

  isLoggedIn() { return !!this.getToken(); },

  getPayload() {
    const tok = this.getToken();
    if (!tok) return null;
    try {
      const b64 = tok.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
      return JSON.parse(atob(b64));
    } catch (e) {
      return null;
    }
  },

  isAdmin() {
    const pl = this.getPayload();
    return pl && pl.role === 'admin';
  },

  updateNavbar() {
    const loggedIn = this.isLoggedIn();
    const pl = this.getPayload();

    document.querySelectorAll('[data-guest]').forEach(function(el) {
      el.classList.toggle('hidden', loggedIn);
    });
    document.querySelectorAll('[data-user]').forEach(function(el) {
      el.classList.toggle('hidden', !loggedIn);
    });
    document.querySelectorAll('[data-admin]').forEach(function(el) {
      el.classList.toggle('hidden', !AuthManager.isAdmin());
    });

    if (pl) {
      document.querySelectorAll('[data-username]').forEach(function(el) {
        el.textContent = pl.username;
      });
    }
  }
};

function setLoading(btn, loading) {
  if (loading) {
    btn.dataset.origText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;border-width:2px;display:inline-block;vertical-align:middle;margin-right:6px;"></span>Загрузка...';
    btn.disabled = true;
  } else {
    btn.innerHTML = btn.dataset.origText || btn.innerHTML;
    btn.disabled = false;
  }
}

function clearErrors(form) {
  form.querySelectorAll('.form-error').forEach(e => e.remove());
  form.querySelectorAll('.form-control').forEach(e => e.style.borderColor = '');
}

function openModal(id) {
  document.getElementById(id)?.classList.remove('hidden');
}

function closeModal(id) {
  document.getElementById(id)?.classList.add('hidden');
}

document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.add('hidden');
  }
});

async function initCsrfToken() {
  try {
    
    const response = await fetch((window.APP_URL || '') + '/api/auth.php?action=csrf_token', {
      method: 'GET',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' }
    });

    if (response.ok) {
      const data = await response.json();
      if (data.csrf_token) {
        localStorage.setItem('csrf_token', data.csrf_token);
      }
    }
  } catch (error) {
    
    console.warn('[CSRF] Failed to load token:', error.message);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCsrfToken);
} else {
  initCsrfToken();
}