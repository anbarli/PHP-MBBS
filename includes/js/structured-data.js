(() => {
    'use strict';

    const addBreadcrumbData = () => {
        const breadcrumbLinks = document.querySelectorAll('.breadcrumb-item a');
        if (!breadcrumbLinks.length) {
            return;
        }

        const breadcrumbData = {
            '@context': 'https://schema.org',
            '@type': 'BreadcrumbList',
            itemListElement: []
        };

        breadcrumbLinks.forEach((link, index) => {
            breadcrumbData.itemListElement.push({
                '@type': 'ListItem',
                position: index + 1,
                name: (link.textContent || '').trim(),
                item: link.href
            });
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
