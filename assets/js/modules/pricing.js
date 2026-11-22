import { qs, qsa } from './dom.js';

// Покупка тарифа: сначала окно подтверждения (<dialog>), потом обычная отправка формы.
export class Pricing {
    constructor(root) {
        this.dialog = qs('#pay-dialog', root.parentElement);
        this.pending = null;
        if (!this.dialog || typeof this.dialog.showModal !== 'function') return; // без <dialog> форма уходит сразу

        qsa('[data-pay-form]', root).forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                this.pending = form;
                qs('[data-pay-name]', this.dialog).textContent = form.dataset.name;
                qs('[data-pay-price]', this.dialog).textContent = form.dataset.price;
                qs('[data-pay-note]', this.dialog).textContent = form.dataset.note || 'Подписка будет активирована сразу после оплаты.';
                this.dialog.showModal();
            });
        });

        this.dialog.addEventListener('close', () => {
            if (this.dialog.returnValue === 'ok' && this.pending) {
                const button = qs('button[type="submit"]', this.pending);
                button.disabled = true;
                button.textContent = 'Обрабатываем платёж…';
                this.pending.submit();
            }
            this.dialog.returnValue = '';
            this.pending = null;
        });
    }
}
