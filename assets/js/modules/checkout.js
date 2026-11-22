/**
 * Форматирует российский номер.
 * Принимает любые символы, оставляет только цифры.
 * Если номер начинается с 7 или 8 — считает это кодом страны и срезает,
 * всегда подставляя +7. Остальные цифры — 10-значный номер.
 *
 *   8 999 123-45-67    → +7 999 123-45-67
 *   7 999 123-45-67    → +7 999 123-45-67
 *   +7 (999) 123-45-67 → +7 999 123-45-67
 *   9991234567         → +7 999 123-45-67
 */
function formatRuPhone(raw) {
    let digits = String(raw).replace(/\D/g, '');
    if (!digits) return '';
    if (digits[0] === '7' || digits[0] === '8') digits = digits.slice(1);
    digits = digits.slice(0, 10);

    let out = '+7';
    if (digits.length) out += ' ' + digits.slice(0, 3);
    if (digits.length > 3) out += ' ' + digits.slice(3, 6);
    if (digits.length > 6) out += '-' + digits.slice(6, 8);
    if (digits.length > 8) out += '-' + digits.slice(8, 10);
    return out;
}

export class Checkout {
    constructor(form) {
        this.form = form;
        this.sections = form.querySelectorAll('[data-pay-section]');

        form.querySelectorAll('input[name="method"]').forEach((input) => {
            input.addEventListener('change', () => this.update());
        });

        this.initPhoneMask();
        this.initCardMask();
        this.initExpiryMask();
        this.update();
    }

    update() {
        const value = this.form.querySelector('input[name="method"]:checked')?.value || '';
        this.sections.forEach((section) => {
            section.hidden = section.dataset.paySection !== value;
        });
    }

    initPhoneMask() {
        const input = this.form.elements.sbp_phone;
        if (!input) return;

        const apply = () => {
            const formatted = formatRuPhone(input.value);
            if (input.value !== formatted) input.value = formatted;
        };
        input.addEventListener('input', apply);
        input.addEventListener('blur', apply);

        // Плейсхолдер «9991234567» — если пользователь стирает всё, поле пустое,
        // и следующий ввод снова проходит через маску.
    }

    initCardMask() {
        const input = this.form.elements.card_number;
        if (!input) return;
        input.addEventListener('input', () => {
            const digits = input.value.replace(/\D/g, '').slice(0, 19);
            input.value = digits.replace(/(\d{4})(?=\d)/g, '$1 ');
        });
    }

    initExpiryMask() {
        const input = this.form.elements.card_expiry;
        if (!input) return;
        input.addEventListener('input', () => {
            let digits = input.value.replace(/\D/g, '').slice(0, 4);
            if (digits.length >= 3) digits = digits.slice(0, 2) + '/' + digits.slice(2);
            input.value = digits;
        });
    }
}