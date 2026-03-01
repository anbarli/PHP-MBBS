/**
 * Service Worker for PHP-MBBS
 * Minimal implementation for offline support and caching
 */

const CACHE_NAME = 'php-mbbs-v2';
const SCOPE_PATH = new URL(self.registration.scope).pathname.replace(/\/$/, '');
const appUrl = (path) => `${SCOPE_PATH}${path}`;
const urlsToCache = [
    SCOPE_PATH ? `${SCOPE_PATH}/` : '/',
    appUrl('/includes/style.css'),
    appUrl('/includes/js/theme.js'),
    appUrl('/includes/js/perf.js')
];

// Install event - cache core assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                // Try to cache, but don't fail if it doesn't work
                return cache.addAll(urlsToCache).catch(() => {
                    console.log('Some assets could not be cached');
                });
            })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone the response before caching
                const responseToCache = response.clone();

                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseToCache);
                });

                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request);
            })
    );
});
