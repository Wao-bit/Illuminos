import { qs } from './dom.js';
import { request } from './http.js';
import { toast } from './toast.js';

// Очистка истории просмотра: два нажатия вместо диалога подтверждения.
export function initHistory() {
    const button = qs('[data-clear-history]');
    if (!button) return;
    const label = button.textContent;
    let armedTimer = 0;

    const reset = () => {
        clearTimeout(armedTimer);
        button.textContent = label;
        button.classList.remove('is-armed');
        button.dataset.armed = '';
    };

    button.addEventListener('click', async () => {
        if (!button.dataset.armed) {
            button.dataset.armed = '1';
            button.textContent = 'Нажмите ещё раз, чтобы очистить';
            button.classList.add('is-armed');
            armedTimer = setTimeout(reset, 4000);
            return;
        }
        reset();
        const { ok } = await request('/api/history.php', { method: 'POST' });
        if (!ok) return toast('Не удалось очистить историю.', { kind: 'error' });

        qs('[data-history]').innerHTML = '<p class="empty">История пуста.</p>';
        const counter = qs('[data-count-history]');
        if (counter) counter.textContent = '0';
        toast('История просмотра очищена', { kind: 'success' });
    });
}
