import { qsa, reducedMotion } from './dom.js';
import { postForm } from './http.js';
import { toast } from './toast.js';

function paint(button, on) {
    button.classList.toggle('is-on', on);
    button.setAttribute('aria-pressed', String(on));
    button.setAttribute('aria-label', on ? 'Убрать из избранного' : 'Добавить в избранное');
    const label = button.querySelector('[data-bookmark-text]');
    if (label) label.textContent = on ? 'В избранном' : 'В избранное';
}

async function toggle(button) {
    if (button.dataset.busy) return;
    button.dataset.busy = '1';
    const id = button.dataset.favorite;

    const { ok, status, data } = await postForm('/api/favorites.php', { content_id: id });
    delete button.dataset.busy;

    if (status === 403) {
        toast('Сессия истекла. Войдите снова.', { kind: 'error', action: { label: 'Войти', href: '/login.php' } });
        return;
    }
    if (!ok) {
        toast('Не удалось обновить избранное. Повторите попытку.', { kind: 'error' });
        return;
    }

    const on = Boolean(data.favorite);
    // Один и тот же фильм может быть на странице несколько раз — обновляем все кнопки
    qsa(`[data-favorite="${CSS.escape(id)}"]`).forEach((b) => paint(b, on));

    if (!reducedMotion()) {
        button.classList.remove('is-pop');
        void button.offsetWidth;
        if (on) button.classList.add('is-pop');
    }

    // В кабинете карточка остаётся на месте, но тускнеет: удаление можно отменить
    const grid = button.closest('[data-favorites-grid]');
    if (grid) {
        button.closest('.card')?.classList.toggle('is-dimmed', !on);
        const counter = document.querySelector('[data-count-favorites]');
        if (counter) counter.textContent = String(qsa('.bookmark.is-on', grid).length);
    }

    on ? toast('Добавлено в избранное')
       : toast('Убрано из избранного', { action: { label: 'Вернуть', onClick: () => toggle(button) } });
}

export function initFavorites() {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-favorite]');
        if (!button) return;
        event.preventDefault();
        if (button.hasAttribute('data-guest')) {
            toast('Войдите, чтобы добавлять в избранное', { action: { label: 'Войти', href: '/login.php' } });
            return;
        }
        toggle(button);
    });
}
