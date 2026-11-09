const searchInput = document.querySelector('[data-organization-search]');
const organizationCards = [...document.querySelectorAll('[data-organization-card]')];
const emptySearch = document.querySelector('[data-empty-search]');

searchInput?.addEventListener('input', (event) => {
    const query = event.target.value.trim().toLocaleLowerCase('id');
    let visibleCards = 0;

    organizationCards.forEach((card) => {
        const matches = card.dataset.searchText.includes(query);
        card.hidden = !matches;
        visibleCards += Number(matches);
    });

    if (emptySearch) {
        emptySearch.hidden = visibleCards > 0;
    }
});

const menuToggle = document.querySelector('[data-menu-toggle]');
const navigation = document.querySelector('[data-navigation]');

menuToggle?.addEventListener('click', () => {
    const isOpen = navigation?.classList.toggle('is-open') ?? false;
    menuToggle.setAttribute('aria-expanded', String(isOpen));
});

navigation?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
        navigation.classList.remove('is-open');
        menuToggle?.setAttribute('aria-expanded', 'false');
    });
});

const themeToggle = document.querySelector('[data-theme-toggle]');
const themeIcon = document.querySelector('[data-theme-icon]');
const savedTheme = localStorage.getItem('ft-theme');

if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
}

const updateThemeButton = () => {
    const isDark = document.body.classList.contains('dark-mode');
    themeIcon.textContent = isDark ? '☀' : '☾';
    themeToggle?.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
};

updateThemeButton();

themeToggle?.addEventListener('click', () => {
    document.body.classList.toggle('dark-mode');
    localStorage.setItem('ft-theme', document.body.classList.contains('dark-mode') ? 'dark' : 'light');
    updateThemeButton();
});
