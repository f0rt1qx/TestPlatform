/**
 * Birthday Picker Component
 * Компактный выбор даты рождения с быстрым выбором года/месяца/дня
 */

class BirthdayPicker {
  constructor(selector, options = {}) {
    this.input = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!this.input) throw new Error('BirthdayPicker: input element not found');

    this.options = {
      minAge: options.minAge ?? 6,
      maxAge: options.maxAge ?? 120,
      lang: options.lang || 'ru',
      ...options
    };

    this.isOpen = false;
    this.selectedDate = null;
    this.viewYear = null;
    this.viewMonth = null;
    this.dropdown = null;

    this.months = {
      ru: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
      en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    };

    this.weekdays = {
      ru: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
      en: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']
    };

    this._events = {};
    this.init();
  }

  init() {
    this.wrapInput();
    this.createDropdown();
    this.bindEvents();
    this.loadValue();
  }

  wrapInput() {
    const wrapper = document.createElement('div');
    wrapper.className = 'bp-wrapper';

    this.input.classList.add('bp-input');
    this.input.setAttribute('readonly', '');
    this.input.setAttribute('placeholder', 'ДД.ММ.ГГГГ');
    this.input.setAttribute('autocomplete', 'bday');

    const icon = document.createElement('div');
    icon.className = 'bp-icon';
    icon.innerHTML = `<svg viewBox="0 0 24 24">
      <rect x="3" y="4" width="18" height="18" rx="2"></rect>
      <line x1="16" y1="2" x2="16" y2="6"></line>
      <line x1="8" y1="2" x2="8" y2="6"></line>
      <line x1="3" y1="10" x2="21" y2="10"></line>
    </svg>`;

    this.clearBtn = document.createElement('button');
    this.clearBtn.className = 'bp-clear';
    this.clearBtn.innerHTML = '&times;';
    this.clearBtn.type = 'button';
    this.clearBtn.tabIndex = -1;

    wrapper.appendChild(this.input);
    wrapper.appendChild(this.clearBtn);
    wrapper.appendChild(icon);

    this.input.parentNode.insertBefore(wrapper, this.input);
    this.wrapper = wrapper;
  }

  createDropdown() {
    this.dropdown = document.createElement('div');
    this.dropdown.className = 'bp-dropdown';

    const now = new Date();
    const defaultYear = now.getFullYear() - 25;
    this.viewYear = defaultYear;
    this.viewMonth = 0;

    this.render();
    document.body.appendChild(this.dropdown);
  }

  render() {
    const lang = this.options.lang;
    const maxDate = this.getMaxDate();

    const headerHTML = `
      <div class="bp-header">
        <button class="bp-header-btn bp-prev-year" type="button" title="Предыдущий год">
          <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <span class="bp-month-year" data-action="month-picker">${this.months[lang][this.viewMonth]} ${this.viewYear}</span>
        <button class="bp-header-btn bp-next-year" type="button" title="Следующий год">
          <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
      </div>
    `;

    const selectorsHTML = `
      <div class="bp-selectors">
        <div class="bp-selector">
          <select class="bp-year-select" data-role="year"></select>
        </div>
        <div class="bp-selector">
          <select class="bp-month-select" data-role="month"></select>
        </div>
        <div class="bp-selector">
          <select class="bp-day-select" data-role="day"></select>
        </div>
      </div>
    `;

    const calendarHTML = `
      <div class="bp-calendar">
        <div class="bp-weekdays">
          ${this.weekdays[lang].map(d => `<div class="bp-weekday">${d}</div>`).join('')}
        </div>
        <div class="bp-days">
          ${this.generateDays()}
        </div>
      </div>
    `;

    const footerHTML = `
      <div class="bp-footer">
        <button class="bp-btn bp-btn-clear" type="button" data-action="clear">Очистить</button>
        <button class="bp-btn bp-btn-close" type="button" data-action="close">Готово</button>
      </div>
    `;

    this.dropdown.innerHTML = headerHTML + selectorsHTML + calendarHTML + footerHTML;

    this.populateSelectors();
    this.updateSelectorsFromView();
  }

  populateSelectors() {
    const yearSelect = this.dropdown.querySelector('.bp-year-select');
    const monthSelect = this.dropdown.querySelector('.bp-month-select');
    const daySelect = this.dropdown.querySelector('.bp-day-select');
    const lang = this.options.lang;
    const now = new Date();
    const minYear = now.getFullYear() - this.options.maxAge;
    const maxYear = now.getFullYear() - this.options.minAge;

    // Years (descending)
    yearSelect.innerHTML = '';
    for (let y = maxYear; y >= minYear; y--) {
      const opt = document.createElement('option');
      opt.value = y;
      opt.textContent = y;
      yearSelect.appendChild(opt);
    }

    // Months
    monthSelect.innerHTML = '';
    this.months[lang].forEach((name, i) => {
      const opt = document.createElement('option');
      opt.value = i;
      opt.textContent = name.substring(0, 3);
      monthSelect.appendChild(opt);
    });

    // Days (placeholder first)
    daySelect.innerHTML = '<option value="">День</option>';
    for (let d = 1; d <= 31; d++) {
      const opt = document.createElement('option');
      opt.value = d;
      opt.textContent = d;
      daySelect.appendChild(opt);
    }
  }

  updateSelectorsFromView() {
    const yearSelect = this.dropdown.querySelector('.bp-year-select');
    const monthSelect = this.dropdown.querySelector('.bp-month-select');
    const daySelect = this.dropdown.querySelector('.bp-day-select');

    if (yearSelect) yearSelect.value = this.viewYear;
    if (monthSelect) monthSelect.value = this.viewMonth;

    // Update days based on selected date or just refresh highlight
    if (this.selectedDate) {
      if (daySelect) daySelect.value = this.selectedDate.getDate();
    }
  }

  generateDays() {
    const firstDay = new Date(this.viewYear, this.viewMonth, 1);
    const lastDay = new Date(this.viewYear, this.viewMonth + 1, 0);
    const maxDate = this.getMaxDate();

    // Monday = 0
    let startDay = firstDay.getDay() - 1;
    if (startDay < 0) startDay = 6;

    const days = [];

    // Empty cells
    for (let i = 0; i < startDay; i++) {
      days.push('<button class="bp-day empty" type="button" disabled></button>');
    }

    // Days
    for (let day = 1; day <= lastDay.getDate(); day++) {
      const date = new Date(this.viewYear, this.viewMonth, day);
      date.setHours(0, 0, 0, 0);

      const classes = ['bp-day'];
      let disabled = false;

      if (date.getTime() === Date.now()) {
        classes.push('today');
      }

      if (this.selectedDate && date.getTime() === this.selectedDate.getTime()) {
        classes.push('selected');
      }

      if (date > maxDate) {
        disabled = true;
        classes.push('disabled');
      }

      days.push(`<button class="${classes.join(' ')}" type="button" ${disabled ? 'disabled' : ''} data-date="${this.viewYear}-${String(this.viewMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}">${day}</button>`);
    }

    return days.join('');
  }

  getMaxDate() {
    const now = new Date();
    const maxDate = new Date(now.getFullYear() - this.options.minAge, now.getMonth(), now.getDate());
    return maxDate;
  }

  bindEvents() {
    // Toggle
    this.input.addEventListener('click', () => this.toggle());

    // Clear
    this.clearBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      this.clear();
    });

    // Dropdown events
    this.dropdown.addEventListener('click', (e) => {
      e.stopPropagation();

      // Day selection
      const dayBtn = e.target.closest('.bp-day[data-date]');
      if (dayBtn && !dayBtn.disabled) {
        this.selectDate(dayBtn.dataset.date);
        return;
      }

      // Year navigation
      if (e.target.closest('.bp-prev-year')) {
        this.viewYear--;
        this.render();
        return;
      }
      if (e.target.closest('.bp-next-year')) {
        this.viewYear++;
        this.render();
        return;
      }

      // Actions
      const actionBtn = e.target.closest('[data-action]');
      if (actionBtn) {
        const action = actionBtn.dataset.action;
        if (action === 'clear') this.clear();
        if (action === 'close') this.close();
      }
    });

    // Selectors
    this.dropdown.addEventListener('change', (e) => {
      if (e.target.matches('.bp-year-select')) {
        this.viewYear = parseInt(e.target.value, 10);
        this.render();
        // Re-render but keep selector values
        this.updateSelectorsFromView();
      }
      if (e.target.matches('.bp-month-select')) {
        this.viewMonth = parseInt(e.target.value, 10);
        this.render();
        this.updateSelectorsFromView();
      }
      if (e.target.matches('.bp-day-select') && e.target.value) {
        const day = parseInt(e.target.value, 10);
        const dateStr = `${this.viewYear}-${String(this.viewMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        this.selectDate(dateStr);
      }
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!this.wrapper.contains(e.target) && !this.dropdown.contains(e.target)) {
        this.close();
      }
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.close();
    });
  }

  toggle() {
    this.isOpen ? this.close() : this.open();
  }

  open() {
    this.dropdown.classList.add('open');
    this.isOpen = true;
    this.adjustPosition();
    this.emit('open');
  }

  close() {
    this.dropdown.classList.remove('open');
    this.isOpen = false;
    this.emit('close');
  }

  selectDate(dateString) {
    const [year, month, day] = dateString.split('-').map(Number);
    this.selectedDate = new Date(year, month - 1, day);
    this.viewYear = year;
    this.viewMonth = month - 1;

    this.input.value = this.formatDate(this.selectedDate);
    this.clearBtn.classList.toggle('visible', true);

    this.render();
    this.close();
    this.emit('change', this.selectedDate);
  }

  clear() {
    this.selectedDate = null;
    this.input.value = '';
    this.clearBtn.classList.remove('visible');

    const now = new Date();
    this.viewYear = now.getFullYear() - 25;
    this.viewMonth = 0;
    this.render();
    this.emit('clear');
  }

  formatDate(date) {
    const d = String(date.getDate()).padStart(2, '0');
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const y = date.getFullYear();
    return `${d}.${m}.${y}`;
  }

  parseDate(str) {
    if (!str) return null;
    const parts = str.split('.');
    if (parts.length !== 3) return null;
    const d = parseInt(parts[0], 10);
    const m = parseInt(parts[1], 10) - 1;
    const y = parseInt(parts[2], 10);
    const date = new Date(y, m, d);
    return isNaN(date.getTime()) ? null : date;
  }

  loadValue() {
    const val = this.input.value;
    if (val) {
      const date = this.parseDate(val);
      if (date) {
        this.selectedDate = date;
        this.viewYear = date.getFullYear();
        this.viewMonth = date.getMonth();
        this.clearBtn.classList.add('visible');
      }
    }
  }

  adjustPosition() {
    const rect = this.wrapper.getBoundingClientRect();
    const calRect = this.dropdown.getBoundingClientRect();
    const viewportH = window.innerHeight;

    this.dropdown.classList.remove('above');
    if (rect.bottom + calRect.height > viewportH) {
      this.dropdown.classList.add('above');
    }
  }

  getValue() {
    return this.selectedDate ? new Date(this.selectedDate) : null;
  }

  setValue(date) {
    if (date instanceof Date && !isNaN(date.getTime())) {
      this.selectedDate = date;
      this.viewYear = date.getFullYear();
      this.viewMonth = date.getMonth();
      this.input.value = this.formatDate(date);
      this.clearBtn.classList.add('visible');
      this.render();
    }
  }

  on(event, callback) {
    if (!this._events[event]) this._events[event] = [];
    this._events[event].push(callback);
  }

  emit(event, data) {
    if (this._events[event]) {
      this._events[event].forEach(cb => cb(data));
    }
  }

  destroy() {
    this.close();
    if (this.dropdown && this.dropdown.parentNode) {
      this.dropdown.parentNode.removeChild(this.dropdown);
    }
    const wrapper = this.input.closest('.bp-wrapper');
    if (wrapper && wrapper.parentNode) {
      this.input.classList.remove('bp-input');
      this.input.removeAttribute('readonly');
      wrapper.parentNode.insertBefore(this.input, wrapper);
      wrapper.parentNode.removeChild(wrapper);
    }
  }
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-birthday-picker]').forEach(el => {
    const opts = {};
    if (el.dataset.minAge !== undefined) opts.minAge = parseInt(el.dataset.minAge, 10);
    if (el.dataset.maxAge !== undefined) opts.maxAge = parseInt(el.dataset.maxAge, 10);
    if (el.dataset.lang) opts.lang = el.dataset.lang;
    el._birthdayPicker = new BirthdayPicker(el, opts);
  });
});

if (typeof module !== 'undefined' && module.exports) {
  module.exports = BirthdayPicker;
}
