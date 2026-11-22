import { h, qs } from './dom.js';
import { request } from './http.js';

// Панель поиска под шапкой: открывается кнопкой или клавишей «/», показывает подсказки.
export class SearchPanel {
    constructor(root) {
        this.root = root;
        this.input = qs('input[type="search"]', root);
        this.list = qs('.searchbar__results', root);
        this.opener = qs('[data-search-open]');
        this.items = [];
        this.active = -1;
        this.timer = 0;

        this.opener.addEventListener('click', () => (this.root.hidden ? this.open() : this.close()));
        qs('[data-search-close]', root).addEventListener('click', () => this.close());
        this.input.addEventListener('input', () => this.onInput());
        this.input.addEventListener('keydown', (event) => this.onKey(event));
        document.addEventListener('keydown', (event) => this.onGlobalKey(event));
        document.addEventListener('click', (event) => {
            if (!this.root.hidden && !this.root.contains(event.target) && !this.opener.contains(event.target)) this.clearList();
        });
    }

    open() {
        this.root.hidden = false;
        this.opener.setAttribute('aria-expanded', 'true');
        this.input.focus();
        this.input.select();
    }

    close() {
        this.root.hidden = true;
        this.opener.setAttribute('aria-expanded', 'false');
        this.clearList();
        this.opener.focus();
    }

    onGlobalKey(event) {
        const typing = /^(INPUT|TEXTAREA|SELECT)$/.test(event.target.tagName) || event.target.isContentEditable;
        if (event.key === '/' && !typing && !event.metaKey && !event.ctrlKey) {
            event.preventDefault();
            this.open();
        } else if (event.key === 'Escape' && !this.root.hidden) {
            this.close();
        }
    }

    onInput() {
        clearTimeout(this.timer);
        const query = this.input.value.trim();
        if (query.length < 2) return this.clearList();
        this.timer = setTimeout(() => this.fetch(query), 220);
    }

    async fetch(query) {
        this.controller?.abort();
        this.controller = new AbortController();
        const { data, aborted } = await request('/api/search.php?q=' + encodeURIComponent(query), { signal: this.controller.signal });
        if (aborted || !Array.isArray(data) || this.input.value.trim() !== query) return;
        this.render(data, query);
    }

    highlight(text, query) {
        const at = text.toLowerCase().indexOf(query.toLowerCase());
        if (at < 0) return text;
        return [text.slice(0, at), h('mark', {}, text.slice(at, at + query.length)), text.slice(at + query.length)];
    }

    render(rows, query) {
        this.list.replaceChildren();
        this.items = [];
        if (!rows.length) {
            this.list.append(h('li', { class: 'searchbar__empty' }, 'Ничего не найдено.'));
        }
        rows.forEach((row, i) => {
            const thumb = h('span', { class: 'searchbar__thumb' });
            thumb.innerHTML = row.art; // SVG собран сервером из фиксированных фигур, пользовательского текста в нём нет
            const link = h('a', { href: row.url, id: `suggest-${i}`, role: 'option' },
                thumb,
                h('span', {}, h('strong', {}, this.highlight(row.title, query)), h('br'), h('span', { class: 'mono muted' }, row.meta)));
            this.items.push(link);
            this.list.append(h('li', {}, link));
        });
        this.list.hidden = false;
        this.input.setAttribute('aria-expanded', 'true');
        this.active = -1;
    }

    clearList() {
        this.list.hidden = true;
        this.list.replaceChildren();
        this.items = [];
        this.active = -1;
        this.input.setAttribute('aria-expanded', 'false');
        this.input.removeAttribute('aria-activedescendant');
    }

    onKey(event) {
        if (this.list.hidden || !this.items.length) return;
        const count = this.items.length;
        if (event.key === 'ArrowDown') this.move((this.active + 1) % count, event);
        else if (event.key === 'ArrowUp') this.move((this.active - 1 + count) % count, event);
        else if (event.key === 'Enter' && this.active >= 0) { event.preventDefault(); this.items[this.active].click(); }
    }

    move(index, event) {
        event.preventDefault();
        this.items.forEach((item, i) => item.classList.toggle('is-active', i === index));
        this.active = index;
        this.input.setAttribute('aria-activedescendant', this.items[index].id);
    }
}
