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

function initializeMobileMenu() {
    const menuButton = document.querySelector(
        '[data-menu-toggle]'
    );

    const navigation = document.querySelector(
        '[data-main-navigation]'
    );

    if (!menuButton || !navigation) {
        return;
    }

    if (menuButton.dataset.menuToggleInitialized === 'true') {
        return;
    }

    menuButton.dataset.menuToggleInitialized = 'true';

    menuButton.addEventListener('click', () => {
        const menuIsOpen = navigation.classList.toggle('is-open');

        menuButton.classList.toggle(
            'is-open',
            menuIsOpen
        );

        menuButton.setAttribute(
            'aria-expanded',
            menuIsOpen ? 'true' : 'false'
        );

        menuButton.setAttribute(
            'aria-label',
            menuIsOpen
                ? 'Fermer le menu'
                : 'Ouvrir le menu'
        );
    });

    navigation.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            navigation.classList.remove('is-open');
            menuButton.classList.remove('is-open');

            menuButton.setAttribute(
                'aria-expanded',
                'false'
            );

            menuButton.setAttribute(
                'aria-label',
                'Ouvrir le menu'
            );
        });
    });
}

function initializeApp() {
    initializePasswordToggles();
    initializeMobileMenu();
}

document.addEventListener(
    'DOMContentLoaded',
    initializeApp
);

document.addEventListener(
    'turbo:load',
    initializeApp
);