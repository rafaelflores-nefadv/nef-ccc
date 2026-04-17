import './bootstrap';

import Alpine from 'alpinejs';

const THEME_STORAGE_KEY = 'nf-theme-preference';

const getPreferredTheme = () => {
    const savedTheme = window.localStorage.getItem(THEME_STORAGE_KEY);

    if (savedTheme === 'dark' || savedTheme === 'light') {
        return savedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
};

const applyTheme = (theme) => {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.dataset.theme = theme;
};

const syncThemeToggleLabel = () => {
    const currentTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    const labels = document.querySelectorAll('[data-theme-toggle-label]');

    labels.forEach((element) => {
        element.textContent = currentTheme === 'dark' ? 'White mode' : 'Dark mode';
    });
};

window.toggleTheme = () => {
    const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

    applyTheme(nextTheme);
    window.localStorage.setItem(THEME_STORAGE_KEY, nextTheme);
    syncThemeToggleLabel();
};

applyTheme(getPreferredTheme());

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    syncThemeToggleLabel();
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.dataset.disableOnSubmit === 'false') {
        return;
    }

    const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');

    submitButtons.forEach((button) => {
        if (button instanceof HTMLButtonElement || button instanceof HTMLInputElement) {
            button.disabled = true;

            if (button instanceof HTMLButtonElement && button.dataset.loadingText) {
                button.dataset.originalText = button.innerText;
                button.innerText = button.dataset.loadingText;
            }
        }
    });
});
