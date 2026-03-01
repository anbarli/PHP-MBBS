(() => {
    'use strict';

    const config = window.PHP_MBBS_CONFIG || {};

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                }
                img.classList.remove('lazy');
                observer.unobserve(img);
            });
        });

        document.querySelectorAll('img[data-src]').forEach((img) => imageObserver.observe(img));
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }

    const preloadCriticalResources = () => {
        const urls = Array.isArray(config.prefetchUrls) ? config.prefetchUrls : [];
        urls.forEach((url) => {
            if (!url) {
                return;
            }
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        });
    };

    if ('requestIdleCallback' in window) {
        requestIdleCallback(preloadCriticalResources);
    } else {
        setTimeout(preloadCriticalResources, 1000);
    }

    const onReady = () => {
        document.body.classList.remove('loading');
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 100);

        if (config.gaTrackingId && typeof gtag !== 'undefined') {
            gtag('config', config.gaTrackingId, {
                page_title: document.title,
                page_location: window.location.href
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }

    document.addEventListener('error', (event) => {
        const target = event.target;
        if (target && target.tagName === 'IMG') {
            target.style.display = 'none';
        }
    }, true);

    if ('connection' in navigator) {
        const type = navigator.connection.effectiveType;
        if (type === 'slow-2g' || type === '2g') {
            document.body.classList.add('slow-connection');
        }
    }

    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
})();
