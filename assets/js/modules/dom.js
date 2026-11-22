// Мелкие помощники для работы с DOM.
export const qs = (selector, root = document) => root.querySelector(selector);
export const qsa = (selector, root = document) => [...root.querySelectorAll(selector)];

/** h('a', {href: '/x', class: 'link'}, 'Текст') — создаёт элемент без innerHTML. */
export function h(tag, props = {}, ...children) {
    const el = document.createElement(tag);
    for (const [key, value] of Object.entries(props)) {
        if (value === false || value == null) continue;
        if (key === 'class') el.className = value;
        else if (key.startsWith('on')) el.addEventListener(key.slice(2), value);
        else el.setAttribute(key, value === true ? '' : value);
    }
    el.append(...children.flat().filter((c) => c != null && c !== false));
    return el;
}

export const reducedMotion = () => matchMedia('(prefers-reduced-motion: reduce)').matches;
