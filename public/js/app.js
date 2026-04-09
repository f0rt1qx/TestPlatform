/**
 * app.js — общая логика: темы, Toast, API-клиент, Auth
 */

/* ── Theme ──────────────────────────────────────────────────────────────── */
const Theme = {
  init() {
    // Проверяем сохраненную тему или используем светлую по умолчанию
    const saved = localStorage.getItem('theme') || 'light';
    this.apply(saved);
    console.log('[THEME] Инициализация темы:', saved);
  },
  apply(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    this.updateIcon();
    console.log('[THEME] Применена тема:', theme);
  },
  toggle() {
    const current = document.documentElement.getAttribute('data-theme') || 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    console.log('[THEME] Переключение с', current, 'на', next);
    this.apply(next);
  },
  updateIcon() {
    const theme = document.documentElement.getAttribute('data-theme');
    const toggles = document.querySelectorAll('[data-theme-toggle]');
    toggles.forEach(toggle => {
      const icon = toggle.querySelector('i');
      if (icon) {
        if (theme === 'dark') {
          icon.className = 'fas fa-moon';
        } else {
          icon.className = 'fas fa-sun';
        }
      }
    });
  }
};
Theme.init();

document.addEventListener('DOMContentLoaded', () => {
  // Переключатель темы
  document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      Theme.toggle();
    });
  });
  
  const burger = document.querySelector('.burger');
  const nav = document.querySelector('.navbar-nav');
  if (burger && nav) {
    burger.addEventListener('click', () => nav.classList.toggle('open'));
  }
  
  Auth.updateNavbar();
});

/* ── Toast ───────────────────────────────────────────────────────────────── */
const Toast = {
  container: null,
  
  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  },
  
  show(message, type = 'info', duration = 4000) {
    this.init();
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `<span>${icons[type] || icons.info}</span><span>${message}</span>`;
    this.container.appendChild(toast);
    
    setTimeout(() => {
      toast.style.animation = 'fadeOut .3s ease forwards';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },
  
  success(msg) { this.show(msg, 'success'); },
  error(msg) { this.show(msg, 'error', 6000); },
  warning(msg) { this.show(msg, 'warning'); },
};

/* ── API Client ──────────────────────────────────────────────────────────── */
const API = {
  get baseUrl() {
    return (window.APP_URL || '') + '/api';
  },

  _headers() {
    const headers = { 'Content-Type': 'application/json' };
    const token = Auth.getToken();
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    return headers;
  },

  async request(endpoint, method = 'GET', body = null) {
    const url = this.baseUrl + endpoint;
    const options = { 
      method, 
      headers: this._headers(),
      credentials: 'include',
    };
    
    if (body) {
      options.body = JSON.stringify(body);
    }

    let response;
    try {
      response = await fetch(url, options);
    } catch (error) {
      throw new Error('Ошибка сети. Проверьте что XAMPP запущен.');
    }

    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      const text = await response.text();
      console.error('[API] Получен не-JSON ответ от', url, ':', text.substring(0, 200));
      throw new Error(
        `Сервер вернул HTML вместо JSON. Проверьте:\n` +
        `1. Apache и MySQL запущены в XAMPP\n` +
        `2. Файл ${url} существует\n` +
        `3. APP_URL = "${window.APP_URL}" верный`
      );
    }

    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Ошибка сервера');
    }
    
    return data;
  },

  get(endpoint) { return this.request(endpoint, 'GET'); },
  post(endpoint, body) { return this.request(endpoint, 'POST', body); },
  delete(endpoint) { return this.request(endpoint, 'DELETE'); },

  // Auth
  async login(login, password) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/auth.php?action=login', { login, password, csrf_token: csrfToken });
  },
  
  async register(data) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/auth.php?action=register', { ...data, csrf_token: csrfToken });
  },
  
  logout() { return this.post('/auth.php?action=logout', {}); },
  getMe() { return this.get('/auth.php?action=me'); },

  // Tests
  getTests() { return this.get('/test.php?action=list'); },
  
  async startTest(testId) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/test.php?action=start', { test_id: testId, csrf_token: csrfToken });
  },
  
  async submitTest(data) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/test.php?action=submit', { ...data, csrf_token: csrfToken });
  },
  
  logEvent(data) { return this.post('/test.php?action=log_event', data); },
  getMyResults() { return this.get('/test.php?action=my_results'); },
  getResultDetail(id) { return this.get(`/test.php?action=result_detail&attempt_id=${id}`); },

  // Admin
  adminUsers() { return this.get('/admin.php?action=users'); },
  adminTests() { return this.get('/admin.php?action=tests'); },
  adminLogs() { return this.get('/admin.php?action=logs'); },
  adminEyeTracking(params = {}) {
    const query = new URLSearchParams();
    if (params.test_id) query.set('test_id', params.test_id);
    if (params.attempt_id) query.set('attempt_id', params.attempt_id);
    return this.get('/admin.php?action=eye_tracking&' + query.toString());
  },
  adminResults() { return this.get('/admin.php?action=results'); },
  
  async createTest(data) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=create_test', { ...data, csrf_token: csrfToken });
  },
  
  deleteTest(id) { return this.delete(`/admin.php?action=delete_test&test_id=${id}`); },
  
  async toggleTest(id, active) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=toggle_test', { test_id: id, active, csrf_token: csrfToken });
  },
  
  async blockUser(id, block) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=block_user', { user_id: id, block, csrf_token: csrfToken });
  },
  
  async addQuestion(data) {
    const csrfToken = localStorage.getItem('csrf_token');
    return this.post('/admin.php?action=add_question', { ...data, csrf_token: csrfToken });
  },
};

/* ── Auth ────────────────────────────────────────────────────────────────── */
const Auth = {
  _token: null,

  getToken() {
    if (!this._token) {
      this._token = localStorage.getItem('auth_token') || this._getCookie('auth_token');
    }
    return this._token || null;
  },

  _getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  },

  saveToken(token) {
    this._token = token;
    localStorage.setItem('auth_token', token);
  },

  async login(login, password) {
    const res = await API.login(login, password);
    if (res.success && res.token) {
      this.saveToken(res.token);
    }
    if (res.csrf_token) {
      localStorage.setItem('csrf_token', res.csrf_token);
    }
    return res;
  },

  async register(data) {
    const res = await API.register(data);
    if (res.success && res.token) {
      this.saveToken(res.token);
    }
    if (res.csrf_token) {
      localStorage.setItem('csrf_token', res.csrf_token);
    }
    return res;
  },

  async logout() {
    try { 
      await API.logout(); 
    } catch (error) {
      console.warn('Logout API call failed:', error);
    } finally {
      this._token = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('csrf_token');
      document.cookie = 'auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
      window.location.href = (window.APP_URL || '') + '/index.php';
    }
  },

  isLoggedIn() { return !!this.getToken(); },

  getPayload() {
    const token = this.getToken();
    if (!token) return null;
    try {
      const base64 = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
      return JSON.parse(atob(base64));
    } catch {
      return null;
    }
  },

  isAdmin() {
    const payload = this.getPayload();
    return payload && payload.role === 'admin';
  },

  updateNavbar() {
    const loggedIn = this.isLoggedIn();
    const payload = this.getPayload();
    
    document.querySelectorAll('[data-guest]').forEach(el => {
      el.classList.toggle('hidden', loggedIn);
    });
    document.querySelectorAll('[data-user]').forEach(el => {
      el.classList.toggle('hidden', !loggedIn);
    });
    document.querySelectorAll('[data-admin]').forEach(el => {
      el.classList.toggle('hidden', !this.isAdmin());
    });
    
    if (payload) {
      document.querySelectorAll('[data-username]').forEach(el => {
        el.textContent = payload.username;
      });
    }
  },
};

/* ── Helpers ─────────────────────────────────────────────────────────────── */
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

// Получение CSRF токена при загрузке страницы
async function initCsrfToken() {
  try {
    // Пробуем получить токен через специальный эндпоинт (работает для всех)
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
    // Тихая ошибка - токен будет получен при первом запросе
    console.warn('[CSRF] Failed to load token:', error.message);
  }
}

// Инициализация CSRF токена
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCsrfToken);
} else {
  initCsrfToken();
}
