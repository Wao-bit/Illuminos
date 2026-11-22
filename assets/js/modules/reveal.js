// Плавное проявление карточек при прокрутке (сетки с классом .reveal-group).
export function initReveal() {
    const groups = document.querySelectorAll('.reveal-group');
    const show = (group) => group.classList.add('is-visible');

    if (!('IntersectionObserver' in window)) {
        groups.forEach(show);
        return;
    }
    const observer = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            show(entry.target);
            observer.unobserve(entry.target);
        }
    }, { rootMargin: '0px 0px -8% 0px' });

    groups.forEach((group) => {
        [...group.children].forEach((child, i) => child.style.setProperty('--i', i % 8));
        observer.observe(group);
    });
}
