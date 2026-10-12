const menu = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#navigation');
function closeMenu() { navigation.classList.remove('is-open'); menu.setAttribute('aria-expanded', 'false'); }
menu.addEventListener('click', () => {
    const open = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(open));
    navigation.classList.toggle('is-open', open);
});
navigation.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeMenu(); });
document.querySelectorAll('[data-filter]').forEach(button => {
    button.addEventListener('click', () => {
        document.querySelectorAll('[data-filter]').forEach(other => other.setAttribute('aria-pressed', String(other === button)));
        document.querySelectorAll('[data-category]').forEach(photo => { photo.hidden = button.dataset.filter !== 'all' && photo.dataset.category !== button.dataset.filter; });
    });
});
const dialog = document.querySelector('#lightbox');
document.querySelectorAll('[data-photo]').forEach(button => {
    button.addEventListener('click', () => {
        dialog.querySelector('img').src = button.dataset.photo;
        dialog.querySelector('img').alt = button.dataset.caption;
        dialog.querySelector('p').textContent = button.dataset.caption;
        dialog.showModal();
    });
});
dialog.querySelector('button').addEventListener('click', () => dialog.close());
dialog.addEventListener('click', event => { if (event.target === dialog) { const r = dialog.getBoundingClientRect(); if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) dialog.close(); } });
document.querySelectorAll('.languages a').forEach(link => {
    if (window.location.hash) link.hash = window.location.hash;
});
window.addEventListener('hashchange', () => document.querySelectorAll('.languages a').forEach(link => { link.hash = window.location.hash; }));
