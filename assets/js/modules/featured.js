import { qsa } from './dom.js';

// «Афиша недели»: список названий управляет большой панелью.
// Работает как набор вкладок: стрелки ↑ ↓ на клавиатуре, наведение мыши — предпросмотр.
export class Featured {
    constructor(root) {
        this.tabs = qsa('[role="tab"]', root);
        this.panels = qsa('[role="tabpanel"]', root);
        this.current = Math.max(0, this.tabs.findIndex((t) => t.getAttribute('aria-selected') === 'true'));

        const canHover = matchMedia('(hover: hover) and (pointer: fine)').matches;
        this.tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => this.select(i));
            if (canHover) tab.addEventListener('mouseenter', () => this.select(i));
            tab.addEventListener('keydown', (event) => this.onKey(event, i));
        });
    }

    onKey(event, index) {
        const last = this.tabs.length - 1;
        const next = { ArrowDown: index === last ? 0 : index + 1, ArrowUp: index === 0 ? last : index - 1, Home: 0, End: last }[event.key];
        if (next === undefined) return;
        event.preventDefault();
        this.select(next);
        this.tabs[next].focus();
    }

    select(index) {
        if (index === this.current) return;
        this.tabs.forEach((tab, i) => {
            tab.setAttribute('aria-selected', String(i === index));
            tab.tabIndex = i === index ? 0 : -1;
        });
        this.panels.forEach((panel, i) => {
            panel.hidden = i !== index;
            panel.classList.remove('is-active');
        });
        void this.panels[index].offsetWidth;          // перезапуск анимации «занавеса»
        this.panels[index].classList.add('is-active');
        this.current = index;
    }
}
