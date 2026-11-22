import { toast } from './toast.js';

async function copy(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        const area = Object.assign(document.createElement('textarea'), { value: text });
        area.style.cssText = 'position:fixed;opacity:0';
        document.body.append(area);
        area.select();
        const done = document.execCommand?.('copy') ?? false;
        area.remove();
        return done;
    }
}

// Кнопка «Поделиться» на страницах фильма и сериала.
export function initShare() {
    document.addEventListener('click', async (event) => {
        if (!event.target.closest('[data-share]')) return;
        const url = location.href.split('#')[0];
        if (navigator.share) {
            try { await navigator.share({ title: document.title, url }); return; }
            catch (error) { if (error.name === 'AbortError') return; }
        }
        const ok = await copy(url);
        toast(ok ? 'Ссылка скопирована' : 'Не удалось скопировать ссылку', { kind: ok ? 'success' : 'error' });
    });
}
