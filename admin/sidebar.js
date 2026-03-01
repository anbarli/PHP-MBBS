/**
 * Admin Sidebar Toggle
 * Sidebar açma/kapama işlevselliği
 */
(function() {
    'use strict';

    const initSidebar = () => {
        const sidebarWrapper = document.querySelector('.sidebar-wrapper');
        const toggleBtn = document.querySelector('.sidebar-toggle');

        if (!sidebarWrapper || !toggleBtn) return;

        // LocalStorage'dan durumu al
        const savedState = localStorage.getItem('adminSidebarCollapsed');
        const isMobile = window.innerWidth <= 991;

        // Başlangıç durumunu ayarla
        if (savedState === 'true' && !isMobile) {
            sidebarWrapper.classList.add('collapsed');
        }

        // Toggle butonu click event
        toggleBtn.addEventListener('click', () => {
            const isCollapsed = sidebarWrapper.classList.toggle('collapsed');

            // Mobilde farklı class kullan
            if (window.innerWidth <= 576) {
                sidebarWrapper.classList.toggle('mobile-open');
            }

            // Durumu kaydet (sadece desktop için)
            if (window.innerWidth > 991) {
                localStorage.setItem('adminSidebarCollapsed', isCollapsed);
            }
        });

        // Sidebar dışına tıklama ile kapatma (mobilde)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 576) {
                if (!sidebarWrapper.contains(e.target) && !toggleBtn.contains(e.target)) {
                    if (sidebarWrapper.classList.contains('mobile-open')) {
                        sidebarWrapper.classList.remove('mobile-open');
                    }
                }
            }
        });

        // Ekran boyutu değiştiğinde
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                const width = window.innerWidth;

                if (width <= 576) {
                    // Çok küçük ekranlarda mobile-open kontrolü
                    sidebarWrapper.classList.remove('collapsed');
                } else if (width <= 991) {
                    // Tablet: collapsed varsayılan
                    sidebarWrapper.classList.remove('mobile-open');
                } else {
                    // Desktop: saved state kullan
                    sidebarWrapper.classList.remove('mobile-open');
                    const savedState = localStorage.getItem('adminSidebarCollapsed');
                    if (savedState === 'true') {
                        sidebarWrapper.classList.add('collapsed');
                    } else {
                        sidebarWrapper.classList.remove('collapsed');
                    }
                }
            }, 250);
        });
    };

    // DOM hazır olduğunda başlat
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
})();
