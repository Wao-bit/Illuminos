import { qs, qsa, reducedMotion } from './dom.js';
import { request } from './http.js';

// Правила совпадают с серверными (app/Auth.php). Порядок — порядок проверки при отправке.
const RULES = {
    login: {
        login:    (v) => (v.trim() ? '' : 'Введите логин и пароль.'),
        password: (v) => (v ? '' : 'Введите логин и пароль.'),
    },
    register: {
        first_name: (v) => (v.trim() ? '' : 'Укажите имя и фамилию.'),
        last_name:  (v) => (v.trim() ? '' : 'Укажите имя и фамилию.'),
        login:      (v) => (/^[a-zA-Z0-9_]{3,20}$/.test(v) ? '' : 'Логин: 3–20 символов (буквы, цифры, _).'),
        email:      (v) => (/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v) ? '' : 'Некорректный e-mail.'),
        password:   (v) => (v.length >= 6 ? '' : 'Пароль должен быть не короче 6 символов.'),
        password2:  (v, form) => (v === form.elements.password.value ? '' : 'Пароли не совпадают.'),
        agree:      (_, form) => (form.elements.agree.checked ? '' : 'Необходимо согласие с правилами регистрации.'),
    },
};
const STRENGTH = ['', 'Слабый', 'Средний', 'Хороший', 'Надёжный'];

export class AuthForm {
    constructor(form) {
        this.form = form;
        this.mode = form.dataset.mode;
        this.rules = RULES[this.mode];
        this.banner = qs('[data-form-error]', form);
        this.touched = new Set();

        for (const name of Object.keys(this.rules)) {
            const field = form.elements[name];
            field.addEventListener('blur', () => { this.touched.add(name); this.validate(name); });
            field.addEventListener(field.type === 'checkbox' ? 'change' : 'input', () => {
                if (this.touched.has(name)) this.validate(name);
                if (name === 'password' && this.touched.has('password2')) this.validate('password2');
            });
        }

        form.addEventListener('submit', (event) => this.onSubmit(event));
        form.addEventListener('click', (event) => this.onToggle(event));
        if (this.mode === 'register') {
            this.initStrength();
            this.initLoginHint();
        }
    }

    validate(name) {
        const field = this.form.elements[name];
        const message = this.rules[name](field.type === 'checkbox' ? '' : field.value, this.form);
        this.showError(name, message);
        return message;
    }

    showError(name, message) {
        const field = this.form.elements[name];
        const slot = qs(`[data-error-for="${name}"]`, this.form);
        if (slot) slot.textContent = message;
        field.classList.toggle('is-invalid', Boolean(message));
        if (message) field.setAttribute('aria-invalid', 'true');
        else field.removeAttribute('aria-invalid');
    }

    fail(message, name) {
        this.banner.textContent = message;
        this.form.classList.remove('shake');
        void this.form.offsetWidth;
        if (!reducedMotion()) this.form.classList.add('shake');
        if (name && this.form.elements[name]) {
            this.showError(name, message);
            this.form.elements[name].focus();
        }
    }

    setBusy(busy, text) {
        const button = qs('button[type="submit"]', this.form);
        if (busy) {
            button.dataset.label = button.textContent;
            button.disabled = true;
            button.textContent = text;
        } else {
            button.disabled = false;
            button.textContent = button.dataset.label;
        }
    }

    async onSubmit(event) {
        event.preventDefault();
        this.banner.textContent = '';

        for (const name of Object.keys(this.rules)) {
            this.touched.add(name);
            const message = this.validate(name);
            if (message) return this.fail(message, name);
        }

        this.setBusy(true, this.mode === 'login' ? 'Входим…' : 'Создаём аккаунт…');
        const { ok, data } = await request(`/api/auth.php?action=${this.mode}`, { method: 'POST', body: new FormData(this.form) });
        if (ok) {
            window.location = data.redirect || '/index.php';
            return;
        }
        this.setBusy(false);
        this.fail(data?.error || 'Не удалось связаться с сервером. Повторите попытку.', data?.field);
    }

    onToggle(event) {
        const button = event.target.closest('[data-toggle-password]');
        if (!button) return;
        const input = qs('input', button.parentElement);
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        button.textContent = reveal ? 'Скрыть' : 'Показать';
        button.setAttribute('aria-pressed', String(reveal));
    }

    initStrength() {
        const meter = qs('[data-strength]', this.form);
        this.form.elements.password.addEventListener('input', (event) => {
            const v = event.target.value;
            let level = 0;
            if (v.length >= 6) level++;
            if (v.length >= 10) level++;
            if (/[a-zа-я]/.test(v) && /[A-ZА-Я]/.test(v)) level++;
            if (/\d/.test(v) && /[^\w\s]/.test(v)) level++;
            if (!v) level = 0;
            meter.dataset.level = level;
            qs('b', meter).textContent = STRENGTH[level];
        });
    }

    // Подсказка о занятости логина; окончательная проверка всё равно на сервере.
    initLoginHint() {
        const input = this.form.elements.login;
        const hint = qs('[data-login-hint]', this.form);
        let timer = 0;
        input.addEventListener('input', () => {
            clearTimeout(timer);
            hint.textContent = '';
            hint.className = 'field__hint';
            const value = input.value;
            if (this.rules.login(value)) return;
            timer = setTimeout(async () => {
                const { data } = await request('/api/auth.php?action=check_login&login=' + encodeURIComponent(value));
                if (!data?.ok || input.value !== value) return;
                hint.textContent = data.available ? 'Логин свободен.' : 'Этот логин уже занят.';
                hint.className = 'field__hint ' + (data.available ? 'is-ok' : 'is-bad');
            }, 450);
        });
    }
}
