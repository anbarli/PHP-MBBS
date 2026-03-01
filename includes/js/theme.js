(() => {
    'use strict';

    const STORAGE_KEY = 'theme';
    const darkModeToggle = document.getElementById('darkModeToggle');
    const moonIcon = document.getElementById('moonIcon');
    const sunIcon = document.getElementById('sunIcon');

    const getPreferredTheme = () => {
        try {
            const storedTheme = localStorage.getItem(STORAGE_KEY);
            if (storedTheme === 'dark' || storedTheme === 'light') {
                return storedTheme;
            }
        } catch (e) {
            // localStorage might be blocked.
        }

        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    };

    const applyTheme = (theme) => {
        const isDark = theme === 'dark';
        document.body.classList.toggle('dark-mode', isDark);
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');

        if (moonIcon && sunIcon) {
            moonIcon.style.display = isDark ? 'none' : 'inline';
            sunIcon.style.display = isDark ? 'inline' : 'none';
        }

        if (darkModeToggle) {
            darkModeToggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            darkModeToggle.setAttribute('title', isDark ? 'Acik tema' : 'Koyu tema');
            darkModeToggle.setAttribute('aria-label', isDark ? 'Acik tema' : 'Koyu tema');
        }
    };

    const setTheme = (theme) => {
        applyTheme(theme);
        try {
            localStorage.setItem(STORAGE_KEY, theme);
        } catch (e) {
            // Ignore storage failures.
        }
    };

    applyTheme(getPreferredTheme());

    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', () => {
            const nextTheme = document.body.classList.contains('dark-mode') ? 'light' : 'dark';
            setTheme(nextTheme);
        });
    }
})();
