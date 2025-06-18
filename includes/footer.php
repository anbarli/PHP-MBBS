		</div>
	</div>
</div>

<footer class="footer mt-auto py-3 bg-body-secondary">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="text-center text-body-secondary border-bottom pb-3 mb-3">
                    Powered By <a href="https://github.com/anbarli/PHP-MBBS" class="text-secondary" target="_blank" rel="noopener">PHP-MBBS</a>
                </p>
                <p class="text-center text-body-secondary">
                    &copy; <?php echo date("Y"); ?> <?php echo SITE_NAME; ?> 
                    <a href="<?php echo BASE_URL; ?>rss" target="_blank" class="text-secondary" title="RSS Feed">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 448 512">
                            <path d="M0 64C0 46.3 14.3 32 32 32c229.8 0 416 186.2 416 416c0 17.7-14.3 32-32 32s-32-14.3-32-32C384 253.6 226.4 96 32 96C14.3 96 0 81.7 0 64zM0 416a64 64 0 1 1 128 0A64 64 0 1 1 0 416zM32 160c159.1 0 288 128.9 288 288c0 17.7-14.3 32-32 32s-32-14.3-32-32c0-123.7-100.3-224-224-224c-17.7 0-32-14.3-32-32s14.3-32 32-32z"/>
                        </svg>
                    </a>
                </p>
            </div>
            <div class="col-md-6">
                <div class="text-center text-body-secondary">
                    <small>
                        <?php 
                        $postCount = getPostCount();
                        $categoryCount = getCategoryCount();
                        ?>
                        <span class="badge bg-secondary me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-file-text me-1" viewBox="0 0 16 16">
                                <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                                <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                            </svg>
                            <?php echo $postCount; ?> Yazı
                        </span>
                        <span class="badge bg-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-folder me-1" viewBox="0 0 16 16">
                                <path d="M.54 3.87L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z"/>
                            </svg>
                            <?php echo $categoryCount; ?> Kategori
                        </span>
                    </small>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        Son güncelleme: <?php echo date('d.m.Y H:i'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Performance optimized JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

<!-- Dark mode functionality with performance optimizations -->
<script>
// Dark mode functionality
(function() {
    'use strict';
    
    console.log('Dark mode script loaded');
    
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('theme') || 'light';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    console.log('Current theme from localStorage:', currentTheme);
    console.log('Prefers dark mode:', prefersDark);
    
    // Set initial theme
    if (currentTheme === 'dark' || (!currentTheme && prefersDark)) {
        console.log('Setting dark mode on page load');
        document.body.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    // Dark mode toggle functionality
    const darkModeToggle = document.getElementById('darkModeToggle');
    console.log('Dark mode toggle button found:', !!darkModeToggle);
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            console.log('Dark mode toggle clicked');
            const isDark = document.body.classList.contains('dark-mode');
            const newTheme = isDark ? 'light' : 'dark';
            
            console.log('Current isDark:', isDark, 'New theme:', newTheme);
            
            if (isDark) {
                document.body.classList.remove('dark-mode');
                console.log('Removed dark-mode class');
            } else {
                document.body.classList.add('dark-mode');
                console.log('Added dark-mode class');
            }
            
            localStorage.setItem('theme', newTheme);
            updateDarkModeIcon(!isDark);
            
            console.log('Theme saved to localStorage:', newTheme);
        });
    }
    
    function updateDarkModeIcon(isDark) {
        const moonIcon = document.getElementById('moonIcon');
        const sunIcon = document.getElementById('sunIcon');
        
        console.log('Updating icons - isDark:', isDark);
        console.log('Moon icon found:', !!moonIcon);
        console.log('Sun icon found:', !!sunIcon);
        
        if (moonIcon && sunIcon) {
            moonIcon.style.display = isDark ? 'none' : 'inline';
            sunIcon.style.display = isDark ? 'inline' : 'none';
            console.log('Icons updated successfully');
        }
    }
    
    // Performance: Intersection Observer for lazy loading
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        // Observe all lazy images
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Performance: Service Worker registration (if available)
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
    
    // SEO: Add structured data for better search engine understanding
    function addStructuredData() {
        const structuredData = {
            "@context": "https://schema.org",
            "@type": "WebSite",
            "name": "<?php echo SITE_NAME; ?>",
            "url": "<?php echo BASE_URL; ?>",
            "description": "<?php echo DEFAULT_DESCRIPTION; ?>",
            "publisher": {
                "@type": "Organization",
                "name": "<?php echo AUTHOR_NAME; ?>"
            },
            "potentialAction": {
                "@type": "SearchAction",
                "target": "<?php echo BASE_URL; ?>search?q={search_term_string}",
                "query-input": "required name=search_term_string"
            }
        };
        
        const script = document.createElement('script');
        script.type = 'application/ld+json';
        script.textContent = JSON.stringify(structuredData);
        document.head.appendChild(script);
    }
    
    // Add structured data when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addStructuredData);
    } else {
        addStructuredData();
    }
    
    // Performance: Preload critical resources
    function preloadCriticalResources() {
        const criticalResources = [
            '<?php echo BASE_URL; ?>rss',
            '<?php echo BASE_URL; ?>sitemap.xml'
        ];
        
        criticalResources.forEach(url => {
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            document.head.appendChild(link);
        });
    }
    
    // Preload resources when page is idle
    if ('requestIdleCallback' in window) {
        requestIdleCallback(preloadCriticalResources);
    } else {
        setTimeout(preloadCriticalResources, 1000);
    }
    
    // Performance: Add loading states
    document.addEventListener('DOMContentLoaded', function() {
        // Remove loading class from body
        document.body.classList.remove('loading');
        
        // Add smooth transitions after page load
        setTimeout(() => {
            document.body.classList.add('loaded');
        }, 100);
    });
    
    // SEO: Track page views for analytics (if enabled)
    function trackPageView() {
        if (typeof gtag !== 'undefined') {
            gtag('config', '<?php echo GA_TRACKING_ID; ?>', {
                page_title: document.title,
                page_location: window.location.href
            });
        }
    }
    
    // Track page view when page loads
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', trackPageView);
    } else {
        trackPageView();
    }
    
    // Performance: Add error handling for images
    document.addEventListener('error', function(e) {
        if (e.target.tagName === 'IMG') {
            e.target.style.display = 'none';
            console.warn('Image failed to load:', e.target.src);
        }
    }, true);
    
    // Performance: Add loading indicator for slow connections
    if ('connection' in navigator) {
        if (navigator.connection.effectiveType === 'slow-2g' || 
            navigator.connection.effectiveType === '2g') {
            document.body.classList.add('slow-connection');
        }
    }
    
    // Performance: Optimize for mobile devices
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
    
    // SEO: Add breadcrumb structured data
    function addBreadcrumbData() {
        const breadcrumbs = document.querySelectorAll('.breadcrumb-item a');
        if (breadcrumbs.length > 0) {
            const breadcrumbData = {
                "@context": "https://schema.org",
                "@type": "BreadcrumbList",
                "itemListElement": []
            };
            
            breadcrumbs.forEach((item, index) => {
                breadcrumbData.itemListElement.push({
                    "@type": "ListItem",
                    "position": index + 1,
                    "name": item.textContent.trim(),
                    "item": item.href
                });
            });
            
            const script = document.createElement('script');
            script.type = 'application/ld+json';
            script.textContent = JSON.stringify(breadcrumbData);
            document.head.appendChild(script);
        }
    }
    
    // Add breadcrumb data when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addBreadcrumbData);
    } else {
        addBreadcrumbData();
    }
    
    console.log('Dark mode script initialization complete');
    
})();
</script>

</body>
</html>
