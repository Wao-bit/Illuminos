import { qs, qsa } from './dom.js';

// Сериал: кнопка «Развернуть все / Свернуть все» для сезонов.
export class Seasons {
    constructor(root) {
        const button = qs('[data-seasons-toggle]', root);
        if (!button) return;
        button.addEventListener('click', () => {
            const items = qsa('details.season', root);
            const openAll = items.some((item) => !item.open);
            items.forEach((item) => { item.open = openAll; });
            button.textContent = openAll ? 'Свернуть все' : 'Развернуть все';
        });
    }
}
