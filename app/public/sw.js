// Service Worker for caching external images
const CACHE_NAME = 'tvwatchlist-images-v2';
const IMAGE_CACHE_TIME = 7 * 24 * 60 * 60 * 1000; // 7 days

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Only cache images from thetvdb.com
    if (url.hostname === 'artworks.thetvdb.com' && 
        (request.destination === 'image' || url.pathname.match(/\.(jpg|jpeg|png|gif|webp)$/i))) {
        
        event.respondWith(
            caches.open(CACHE_NAME).then((cache) => {
                return cache.match(request).then((cachedResponse) => {
                    // Always return cached immediately if available (stale-while-revalidate)
                    const fetchPromise = fetch(request).then((networkResponse) => {
                        // Update cache in background
                        if (networkResponse && networkResponse.status === 200) {
                            cache.put(request, networkResponse.clone());
                        }
                        return networkResponse;
                    }).catch(() => cachedResponse); // Fallback to cache on network error
                    
                    // Return cached immediately, or network if no cache
                    return cachedResponse || fetchPromise;
                });
            })
        );
    }
});
