/**
 * Admin Sidebar Toggle
 * Desktop: collapsible sidebar
 * Tablet/Mobile: drawer with backdrop and ESC close
 */
(function () {
    'use strict';

    const DESKTOP_BREAKPOINT = 991;
    const STORAGE_KEY = 'adminSidebarCollapsed';
    const BACKDROP_CLASS = 'admin-sidebar-backdrop';
    const BODY_OPEN_CLASS = 'admin-drawer-open';

    const isDesktop = () => window.innerWidth > DESKTOP_BREAKPOINT;

    const initSidebar = () => {
        const sidebarWrapper = document.querySelector('.sidebar-wrapper');
        const toggleBtn = document.querySelector('.sidebar-toggle');

        if (!sidebarWrapper || !toggleBtn) {
            return false;
        }

        if (!sidebarWrapper.id) {
            sidebarWrapper.id = 'adminSidebar';
        }

        toggleBtn.setAttribute('aria-controls', sidebarWrapper.id);
        toggleBtn.setAttribute('type', 'button');

        let toggleIcon = toggleBtn.querySelector('i');
        if (!toggleIcon) {
            toggleIcon = document.createElement('i');
            toggleBtn.appendChild(toggleIcon);
        }

        let toggleLabel = toggleBtn.querySelector('.sidebar-toggle-label');
        if (!toggleLabel) {
            toggleLabel = document.createElement('span');
            toggleLabel.className = 'sidebar-toggle-label';
            toggleBtn.appendChild(toggleLabel);
        }

        let backdrop = document.querySelector('.' + BACKDROP_CLASS);
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = BACKDROP_CLASS;
            document.body.appendChild(backdrop);
        }

        const syncAria = () => {
            const expanded = isDesktop()
                ? !sidebarWrapper.classList.contains('collapsed')
                : sidebarWrapper.classList.contains('mobile-open');

            toggleBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        };

        const updateToggleButton = () => {
            const desktop = isDesktop();
            const expanded = desktop
                ? !sidebarWrapper.classList.contains('collapsed')
                : sidebarWrapper.classList.contains('mobile-open');

            toggleBtn.classList.toggle('expanded', expanded);

            if (desktop) {
                if (expanded) {
                    toggleIcon.className = 'bi bi-chevron-left';
                    toggleLabel.textContent = 'Menüyü Daralt';
                    toggleBtn.setAttribute('aria-label', 'Menüyü daralt');
                    toggleBtn.setAttribute('title', 'Menüyü Daralt');
                } else {
                    toggleIcon.className = 'bi bi-chevron-right';
                    toggleLabel.textContent = 'Menüyü Aç';
                    toggleBtn.setAttribute('aria-label', 'Menüyü aç');
                    toggleBtn.setAttribute('title', 'Menüyü Aç');
                }

                return;
            }

            if (expanded) {
                toggleIcon.className = 'bi bi-x-lg';
                toggleLabel.textContent = 'Menüyü Kapat';
                toggleBtn.setAttribute('aria-label', 'Menüyü kapat');
                toggleBtn.setAttribute('title', 'Menüyü Kapat');
            } else {
                toggleIcon.className = 'bi bi-list';
                toggleLabel.textContent = 'Menüyü Aç';
                toggleBtn.setAttribute('aria-label', 'Menüyü aç');
                toggleBtn.setAttribute('title', 'Menüyü Aç');
            }
        };

        const setDrawerState = (open) => {
            sidebarWrapper.classList.toggle('mobile-open', open);
            backdrop.classList.toggle('active', open);
            document.body.classList.toggle(BODY_OPEN_CLASS, open);
            syncAria();
            updateToggleButton();
        };

        const applyResponsiveState = () => {
            const savedCollapsed = localStorage.getItem(STORAGE_KEY) === 'true';

            if (isDesktop()) {
                sidebarWrapper.classList.remove('mobile-open');
                document.body.classList.remove(BODY_OPEN_CLASS);
                backdrop.classList.remove('active');
                sidebarWrapper.classList.toggle('collapsed', savedCollapsed);
            } else {
                sidebarWrapper.classList.remove('collapsed');
                setDrawerState(false);
            }

            syncAria();
            updateToggleButton();
        };

        const mainWrapper = document.querySelector('.admin-main-wrapper');
        sidebarWrapper.classList.add('sidebar-no-transition');
        if (mainWrapper) {
            mainWrapper.classList.add('sidebar-no-transition');
        }

        applyResponsiveState();

        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                sidebarWrapper.classList.remove('sidebar-no-transition');
                if (mainWrapper) {
                    mainWrapper.classList.remove('sidebar-no-transition');
                }
            });
        });

        toggleBtn.addEventListener('click', () => {
            if (isDesktop()) {
                const collapsed = sidebarWrapper.classList.toggle('collapsed');
                localStorage.setItem(STORAGE_KEY, collapsed ? 'true' : 'false');
                syncAria();
                updateToggleButton();
                return;
            }

            const shouldOpen = !sidebarWrapper.classList.contains('mobile-open');
            setDrawerState(shouldOpen);
        });

        backdrop.addEventListener('click', () => {
            if (!isDesktop()) {
                setDrawerState(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !isDesktop() && sidebarWrapper.classList.contains('mobile-open')) {
                setDrawerState(false);
                toggleBtn.focus();
            }
        });

        let resizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(applyResponsiveState, 150);
        });

        return true;
    };

    if (!initSidebar()) {
        document.addEventListener('DOMContentLoaded', initSidebar, { once: true });
    }
})();
