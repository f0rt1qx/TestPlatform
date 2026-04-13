/**
 * Calendar DatePicker Component
 * Легковесный, переиспользуемый компонент календаря
 */

class DatePicker {
  constructor(selector, options = {}) {
    this.input = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!this.input) throw new Error('Input element not found');

    this.options = {
      format: options.format || 'DD.MM.YYYY',
      lang: options.lang || 'ru',
      minDate: options.minDate || null,
      maxDate: options.maxDate || null,
      ...options
    };

    this.isOpen = false;
    this.currentDate = new Date();
    this.selectedDate = null;
    this.calendar = null;

    this.months = {
      ru: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
      en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    };

    this.weekdays = {
      ru: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
      en: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']
    };

    this.init();
  }

  init() {
    this.wrapInput();
    this.createCalendar();
    this.bindEvents();
    this.loadValue();
  }

  wrapInput() {
    const wrapper = document.createElement('div');
    wrapper.className = 'cal-input-wrapper';

    this.input.classList.add('cal-input');
    this.input.setAttribute('readonly', '');
    this.input.setAttribute('placeholder', this.options.format);

    const icon = document.createElement('div');
    icon.className = 'cal-icon';
    icon.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
      <line x1="16" y1="2" x2="16" y2="6"></line>
      <line x1="8" y1="2" x2="8" y2="6"></line>
      <line x1="3" y1="10" x2="21" y2="10"></line>
    </svg>`;

    this.clearBtn = document.createElement('button');
    this.clearBtn.className = 'cal-clear';
    this.clearBtn.innerHTML = '&times;';
    this.clearBtn.type = 'button';

    wrapper.appendChild(this.input);
    wrapper.appendChild(this.clearBtn);
    wrapper.appendChild(icon);

    this.input.parentNode.insertBefore(wrapper, this.input);
    this.wrapper = wrapper;
  }

  createCalendar() {
    this.calendar = document.createElement('div');
    this.calendar.className = 'calendar-popup';

    this.render();
    document.body.appendChild(this.calendar);
  }

  render() {
    const year = this.currentDate.getFullYear();
    const month = this.currentDate.getMonth();
    const lang = this.options.lang;

    const headerHTML = `
      <div class="cal-header">
        <div class="cal-month-nav">
          <button class="cal-btn cal-prev" type="button">
            <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
          </button>
          <span class="cal-month-year">${this.months[lang][month]} ${year}</span>
          <button class="cal-btn cal-next" type="button">
            <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"></polyline></svg>
          </button>
        </div>
      </div>
    `;

    const weekdaysHTML = `
      <div class="cal-weekdays">
        ${this.weekdays[lang].map(d => `<div class="cal-weekday">${d}</div>`).join('')}
      </div>
    `;

    const daysHTML = this.generateDays(year, month);

    const footerHTML = `
      <div class="cal-footer">
        <button class="cal-footer-btn cal-btn-today" type="button" data-action="today">Сегодня</button>
        <button class="cal-footer-btn cal-btn-clear" type="button" data-action="clear">Очистить</button>
      </div>
    `;

    this.calendar.innerHTML = headerHTML + weekdaysHTML + daysHTML + footerHTML;
  }

  generateDays(year, month) {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Monday = 0, Sunday = 6
    let startDay = firstDay.getDay() - 1;
    if (startDay < 0) startDay = 6;

    const days = [];

    // Empty cells before first day
    for (let i = 0; i < startDay; i++) {
      days.push('<button class="cal-day empty" type="button" disabled></button>');
    }

    // Days of month
    for (let day = 1; day <= lastDay.getDate(); day++) {
      const date = new Date(year, month, day);
      date.setHours(0, 0, 0, 0);

      const classes = ['cal-day'];
      let disabled = false;

      if (date.getTime() === today.getTime()) {
        classes.push('today');
      }

      if (this.selectedDate && date.getTime() === this.selectedDate.getTime()) {
        classes.push('selected');
      }

      if (this.options.minDate && date < new Date(this.options.minDate)) {
        disabled = true;
        classes.push('disabled');
      }

      if (this.options.maxDate && date > new Date(this.options.maxDate)) {
        disabled = true;
        classes.push('disabled');
      }

      days.push(`<button class="${classes.join(' ')}" type="button" ${disabled ? 'disabled' : ''} data-date="${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}">${day}</button>`);
    }

    return `<div class="cal-days">${days.join('')}</div>`;
  }

  bindEvents() {
    // Toggle calendar on input click
    this.input.addEventListener('click', () => this.toggle());

    // Clear button
    this.clearBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      this.clear();
    });

    // Calendar events (delegation)
    this.calendar.addEventListener('click', (e) => {
      e.stopPropagation();

      const target = e.target.closest('[data-date]');
      if (target && !target.disabled) {
        this.selectDate(target.dataset.date);
        return;
      }

      if (e.target.closest('.cal-prev')) {
        this.changeMonth(-1);
        return;
      }

      if (e.target.closest('.cal-next')) {
        this.changeMonth(1);
        return;
      }

      const actionBtn = e.target.closest('[data-action]');
      if (actionBtn) {
        const action = actionBtn.dataset.action;
        if (action === 'today') this.selectToday();
        if (action === 'clear') this.clear();
      }
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (!this.wrapper.contains(e.target) && !this.calendar.contains(e.target)) {
        this.close();
      }
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') this.close();
    });

    // Keyboard navigation
    this.input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') this.toggle();
    });
  }

  toggle() {
    this.isOpen ? this.close() : this.open();
  }

  open() {
    this.calendar.classList.add('open');
    this.isOpen = true;
    this.adjustPosition();
    this.emit('open');
  }

  close() {
    this.calendar.classList.remove('open');
    this.isOpen = false;
    this.emit('close');
  }

  selectDate(dateString) {
    const [year, month, day] = dateString.split('-').map(Number);
    this.selectedDate = new Date(year, month - 1, day);
    this.input.value = this.formatDate(this.selectedDate);
    this.clearBtn.classList.toggle('visible', !!this.selectedDate);
    this.render();
    this.close();
    this.emit('change', this.selectedDate);
  }

  selectToday() {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    this.currentDate = new Date(today);
    this.selectDate(`${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`);
  }

  clear() {
    this.selectedDate = null;
    this.input.value = '';
    this.clearBtn.classList.remove('visible');
    this.render();
    this.emit('clear');
  }

  changeMonth(delta) {
    this.currentDate.setMonth(this.currentDate.getMonth() + delta);
    this.render();
    this.emit('monthChange', { year: this.currentDate.getFullYear(), month: this.currentDate.getMonth() });
  }

  formatDate(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return this.options.format
      .replace('DD', day)
      .replace('MM', month)
      .replace('YYYY', year);
  }

  parseDate(dateString) {
    if (!dateString) return null;
    const parts = dateString.split('.');
    if (parts.length !== 3) return null;

    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const year = parseInt(parts[2], 10);

    const date = new Date(year, month, day);
    if (isNaN(date.getTime())) return null;

    return date;
  }

  loadValue() {
    const value = this.input.value;
    if (value) {
      const date = this.parseDate(value);
      if (date) {
        this.selectedDate = date;
        this.currentDate = new Date(date);
        this.clearBtn.classList.add('visible');
      }
    }
  }

  getValue() {
    return this.selectedDate;
  }

  setValue(date) {
    if (date instanceof Date && !isNaN(date.getTime())) {
      this.selectedDate = date;
      this.currentDate = new Date(date);
      this.input.value = this.formatDate(date);
      this.clearBtn.classList.add('visible');
      this.render();
    }
  }

  adjustPosition() {
    const rect = this.wrapper.getBoundingClientRect();
    const calRect = this.calendar.getBoundingClientRect();
    const viewportHeight = window.innerHeight;

    this.calendar.classList.remove('above', 'right');

    // Check if there's space below
    if (rect.bottom + calRect.height > viewportHeight) {
      this.calendar.classList.add('above');
    }

    // Check if there's space on the right
    if (rect.left + calRect.width > window.innerWidth) {
      this.calendar.classList.add('right');
    }
  }

  on(event, callback) {
    if (!this._events) this._events = {};
    if (!this._events[event]) this._events[event] = [];
    this._events[event].push(callback);
  }

  emit(event, data) {
    if (!this._events) return;
    if (this._events[event]) {
      this._events[event].forEach(cb => cb(data));
    }
  }

  destroy() {
    this.close();
    if (this.calendar && this.calendar.parentNode) {
      this.calendar.parentNode.removeChild(this.calendar);
    }
    const wrapper = this.input.closest('.cal-input-wrapper');
    if (wrapper && wrapper.parentNode) {
      this.input.classList.remove('cal-input');
      this.input.removeAttribute('readonly');
      wrapper.parentNode.insertBefore(this.input, wrapper);
      wrapper.parentNode.removeChild(wrapper);
    }
  }
}

// Auto-initialize via data attribute
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-datepicker]').forEach(el => {
    const options = {};
    if (el.dataset.dateFormat) options.format = el.dataset.dateFormat;
    if (el.dataset.lang) options.lang = el.dataset.lang;
    if (el.dataset.minDate) options.minDate = el.dataset.minDate;
    if (el.dataset.maxDate) options.maxDate = el.dataset.maxDate;

    el._datepicker = new DatePicker(el, options);
  });
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = DatePicker;
}
