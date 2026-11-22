// Точка входа. Компоненты подключаются по атрибуту data-component,
// а глобальные поведения (избранное, подсказки, проявление) — один раз на страницу.
import { Nav } from './modules/nav.js';
import { SearchPanel } from './modules/search.js';
import { Featured } from './modules/featured.js';
import { Filters } from './modules/filters.js';
import { Tabs } from './modules/tabs.js';
import { Seasons } from './modules/seasons.js';
import { Pricing } from './modules/pricing.js';
import { AuthForm } from './modules/auth-form.js';
import { initFavorites } from './modules/favorites.js';
import { initHistory } from './modules/history.js';
import { initShare } from './modules/share.js';
import { initReveal } from './modules/reveal.js';
import { SettingsForm } from './modules/settings-form.js';
import { Checkout } from './modules/checkout.js';


const components = {
    nav: Nav,
    search: SearchPanel,
    featured: Featured,
    filters: Filters,
    tabs: Tabs,
    seasons: Seasons,
    pricing: Pricing,
    'auth-form': AuthForm,
    'settings-form': SettingsForm,
    checkout: Checkout,
};

for (const el of document.querySelectorAll('[data-component]')) {
    const Component = components[el.dataset.component];
    if (Component) new Component(el);
}

initFavorites();
initHistory();
initShare();
initReveal();
