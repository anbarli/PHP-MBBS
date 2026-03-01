(() => {
    'use strict';

    const getFallbackNameFromUrl = (url) => {
        try {
            const parsed = new URL(url, window.location.origin);
            const segments = parsed.pathname.split('/').filter(Boolean);

            if (!segments.length) {
                return 'Ana Sayfa';
            }

            return decodeURIComponent(segments[segments.length - 1])
                .replace(/[-_]+/g, ' ')
                .trim();
        } catch (error) {
            return '';
        }
    };

    const pickName = (...candidates) => {
        for (const candidate of candidates) {
            const value = (candidate || '').trim();
            if (value.length > 0) {
                return value;
            }
        }

        return '';
    };

    const addBreadcrumbData = () => {
        const breadcrumbItems = document.querySelectorAll('.breadcrumb-item');
        if (!breadcrumbItems.length) {
            return;
        }

        const breadcrumbData = {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: []
        };

        breadcrumbItems.forEach((item, index) => {
            const link = item.querySelector('a');
            const isActive = item.classList.contains('active');

            if (link) {
                const linkName = pickName(
                    link.textContent,
                    link.getAttribute('aria-label'),
                    link.getAttribute('title'),
                    getFallbackNameFromUrl(link.href)
                );

                if (!linkName) {
                    return;
                }

                breadcrumbData.itemListElement.push({
                    '@type': 'ListItem',
                    position: index + 1,
                    name: linkName,
                    item: {
                        '@id': link.href,
                        name: linkName
                    }
                });
            } else if (isActive) {
                const activeName = pickName(
                    item.textContent,
                    item.getAttribute('aria-label'),
                    document.title.replace(/\s*[-|].*$/, '')
                );

                if (!activeName) {
                    return;
                }

                breadcrumbData.itemListElement.push({
                    '@type': 'ListItem',
                    position: index + 1,
                    name: activeName
                });
            }
        });

        if (!breadcrumbData.itemListElement.length) {
            return;
        }

        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(breadcrumbData);
        document.head.appendChild(script);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addBreadcrumbData);
    } else {
        addBreadcrumbData();
    }
})();
