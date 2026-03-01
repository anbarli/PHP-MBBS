(() => {
    'use strict';

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
                // Link varsa (aktif değilse)
                breadcrumbData.itemListElement.push({
                    '@type': 'ListItem',
                    position: index + 1,
                    name: (link.textContent || '').trim(),
                    item: link.href
                });
            } else if (isActive) {
                // Aktif item (link yok, sadece text)
                breadcrumbData.itemListElement.push({
                    '@type': 'ListItem',
                    position: index + 1,
                    name: (item.textContent || '').trim()
                });
            }
        });

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
