import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
