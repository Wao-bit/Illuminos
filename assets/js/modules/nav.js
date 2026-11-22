import { qs, qsa } from './dom.js';

// Меню: бургер на узких экранах и закрытие выпадающего списка категорий.
export class Nav {
    constructor(root) {
        this.root = root;
        this.burger = qs('[data-nav-toggle]');
        this.menus = qsa('details.menu', root);

        this.burger?.addEventListener('click', () => {
            const open = this.root.classList.toggle('is-open');
            this.burger.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('click', (event) => {
            this.menus.forEach((menu) => { if (!menu.contains(event.target)) menu.open = false; });
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') this.menus.forEach((menu) => { menu.open = false; });
        });
    }
}
