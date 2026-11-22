import { qsa } from './dom.js';

// Вкладки личного кабинета. Без JS все разделы просто идут друг за другом.
export class Tabs {
    constructor(root) {
        this.tabs = qsa('[role="tab"]', root);
        this.panels = this.tabs.map((tab) => document.getElementById(tab.getAttribute('aria-controls')));

        const fromHash = this.panels.findIndex((p) => `#${p.id.replace('panel-', '')}` === location.hash);
        this.select(fromHash >= 0 ? fromHash : 0, false);

        this.tabs.forEach((tab, i) => {
            tab.addEventListener('click', () => this.select(i));
            tab.addEventListener('keydown', (event) => {
                const shift = { ArrowRight: 1, ArrowLeft: -1 }[event.key];
                if (!shift) return;
                const next = (i + shift + this.tabs.length) % this.tabs.length;
                this.select(next);
                this.tabs[next].focus();
            });
        });
    }

    select(index, remember = true) {
        this.tabs.forEach((tab, i) => {
            tab.setAttribute('aria-selected', String(i === index));
            tab.tabIndex = i === index ? 0 : -1;
        });
        this.panels.forEach((panel, i) => { panel.hidden = i !== index; });
        if (remember) history.replaceState(null, '', '#' + this.panels[index].id.replace('panel-', ''));
    }
}
