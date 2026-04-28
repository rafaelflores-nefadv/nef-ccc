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

const initializeNotificacoesCheckboxGuard = () => {
    const form = document.querySelector('form[data-notificacoes-form="true"]');

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const checkboxes = Array.from(
        form.querySelectorAll('input[type="checkbox"][data-notificacoes-checkbox="true"]'),
    );

    if (checkboxes.length === 0) {
        return;
    }

    let previousState = new Map();
    const checkboxSelector = 'input[type="checkbox"][data-notificacoes-checkbox="true"]';

    const snapshotState = () => {
        previousState = new Map(
            checkboxes.map((checkbox) => [checkbox, checkbox.checked]),
        );
    };

    const shouldSnapshotFromTarget = (target) => {
        if (!(target instanceof Element)) {
            return false;
        }

        if (target.matches(checkboxSelector)) {
            return true;
        }

        const label = target.closest('label');

        return Boolean(label?.querySelector(checkboxSelector));
    };

    // Snapshot before user interaction to restore unintended side effects
    // from third-party/global listeners that force single selection.
    form.addEventListener('pointerdown', (event) => {
        if (shouldSnapshotFromTarget(event.target)) {
            snapshotState();
        }
    }, true);

    form.addEventListener('keydown', (event) => {
        if ((event.key === ' ' || event.key === 'Enter') && shouldSnapshotFromTarget(event.target)) {
            snapshotState();
        }
    }, true);

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', () => {
            setTimeout(() => {
                checkboxes.forEach((otherCheckbox) => {
                    if (otherCheckbox === checkbox) {
                        return;
                    }

                    const previousChecked = previousState.get(otherCheckbox);

                    if (previousChecked === true && otherCheckbox.checked === false) {
                        otherCheckbox.checked = true;
                    }
                });
            }, 0);
        }, true);
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initializeNotificacoesCheckboxGuard();
});

let confirmModalOnConfirm = null;

const closeConfirmModal = () => {
    const modal = document.getElementById('global-confirm-modal');

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    confirmModalOnConfirm = null;
};

const openConfirmModal = ({ title, message, confirmText, variant, onConfirm }) => {
    const modal = document.getElementById('global-confirm-modal');

    if (!modal) {
        if (typeof onConfirm === 'function') {
            onConfirm();
        }

        return;
    }

    const titleElement = document.getElementById('global-confirm-modal-title');
    const messageElement = document.getElementById('global-confirm-modal-message');
    const iconElement = document.getElementById('global-confirm-modal-icon');
    const confirmButton = modal.querySelector('[data-confirm-modal-confirm]');

    if (titleElement) {
        titleElement.textContent = title || 'Confirmar ação';
    }

    if (messageElement) {
        messageElement.textContent = message || 'Tem certeza de que deseja continuar?';
    }

    if (confirmButton instanceof HTMLButtonElement) {
        confirmButton.textContent = confirmText || 'Confirmar';
        confirmButton.className = 'inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold text-white transition';

        if (variant === 'primary') {
            confirmButton.classList.add('bg-blue-600', 'hover:bg-blue-500');
        } else {
            confirmButton.classList.add('bg-rose-600', 'hover:bg-rose-500');
        }
    }

    if (iconElement instanceof HTMLElement) {
        iconElement.className = 'inline-flex h-9 w-9 items-center justify-center rounded-full';

        if (variant === 'primary') {
            iconElement.classList.add('bg-blue-100', 'text-blue-700');
        } else {
            iconElement.classList.add('bg-rose-100', 'text-rose-700');
        }
    }

    confirmModalOnConfirm = onConfirm;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

document.addEventListener('click', (event) => {
    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    if (target.closest('[data-confirm-modal-close]') || target.closest('[data-confirm-modal-cancel]')) {
        closeConfirmModal();
        return;
    }

    if (target.closest('[data-confirm-modal-confirm]')) {
        const callback = confirmModalOnConfirm;
        closeConfirmModal();

        if (typeof callback === 'function') {
            callback();
        }
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    const modal = document.getElementById('global-confirm-modal');

    if (!modal || modal.classList.contains('hidden')) {
        return;
    }

    closeConfirmModal();
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    if (form.dataset.confirm !== 'true') {
        return;
    }

    if (form.dataset.confirmBypass === '1') {
        delete form.dataset.confirmBypass;
        return;
    }

    event.preventDefault();

    openConfirmModal({
        title: form.dataset.confirmTitle,
        message: form.dataset.confirmMessage,
        confirmText: form.dataset.confirmText,
        variant: form.dataset.confirmVariant,
        onConfirm: () => {
            form.dataset.confirmBypass = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            HTMLFormElement.prototype.submit.call(form);
        },
    });
}, true);

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (event.defaultPrevented) {
        return;
    }

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
