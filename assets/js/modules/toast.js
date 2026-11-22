import { h } from './dom.js';

/**
 * toast('Текст', { kind: 'success' | 'error', action: { label, href?, onClick? } })
 */
export function toast(text, { kind = 'info', action = null, timeout = 4500 } = {}) {
    const host = document.getElementById('toasts');
    if (!host) return;
    while (host.children.length >= 3) host.firstElementChild.remove();

    const dismiss = () => {
        el.classList.remove('is-in');
        setTimeout(() => el.remove(), 350);
    };
    const actionEl = action && (action.href
        ? h('a', { href: action.href }, action.label)
        : h('button', { type: 'button', onclick: () => { action.onClick?.(); dismiss(); } }, action.label));

    const el = h('div', { class: `toast toast--${kind}`, role: kind === 'error' ? 'alert' : 'status' },
        h('span', {}, text), actionEl);
    host.append(el);
    requestAnimationFrame(() => el.classList.add('is-in'));
    setTimeout(dismiss, timeout);
}
