import './styles/app.css';
import './styles/my-training.css';
import './styles/certifications.css';
import './styles/admin.css';

function initializePasswordToggles() {
    const toggleButtons = document.querySelectorAll(
        '[data-password-toggle]'
    );

    toggleButtons.forEach((button) => {
        if (button.dataset.passwordToggleInitialized === 'true') {
            return;
        }

        const passwordField = button.closest('.password-field');

        if (!passwordField) {
            return;
        }

        const input = passwordField.querySelector(
            '[data-password-input]'
        );

        if (!input) {
            return;
        }

        button.dataset.passwordToggleInitialized = 'true';

        button.addEventListener('click', () => {
            const passwordIsVisible = input.type === 'text';

            input.type = passwordIsVisible
                ? 'password'
                : 'text';

            const label = passwordIsVisible
                ? 'Afficher le mot de passe'
                : 'Masquer le mot de passe';

            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);

            const icon = button.querySelector(
                '.password-toggle-icon'
            );

            if (icon) {
                icon.textContent = passwordIsVisible
                    ? '👁'
                    : '🙈';
            }
        });
    });
}

document.addEventListener(
    'DOMContentLoaded',
    initializePasswordToggles
);

document.addEventListener(
    'turbo:load',
    initializePasswordToggles
);