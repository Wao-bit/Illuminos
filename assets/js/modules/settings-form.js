import { request } from './http.js';
import { toast } from './toast.js';

export class SettingsForm {
    constructor(form) {
        this.form = form;
        this.banner = form.querySelector('[data-form-error]');
        form.addEventListener('submit', (event) => this.onSubmit(event));
    }

    async onSubmit(event) {
        event.preventDefault();
        this.banner.textContent = '';
        const button = this.form.querySelector('button[type="submit"]');
        button.disabled = true;

        const { ok, data } = await request('/api/settings.php', {
            method: 'POST',
            body: new FormData(this.form),
        });
        button.disabled = false;

        if (!ok) {
            this.banner.textContent = data?.error || 'Не удалось сохранить изменения.';
            if (data?.field && this.form.elements[data.field]) {
                this.form.elements[data.field].focus();
            }
            return;
        }
        ['current_password', 'new_password', 'new_password2'].forEach((name) => {
            if (this.form.elements[name]) this.form.elements[name].value = '';
        });
        toast('Изменения сохранены', { kind: 'success' });
    }
}