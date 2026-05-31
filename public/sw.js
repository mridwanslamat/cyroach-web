const CACHE = 'cyroach-v2';
const STATIC = [
    '/',
    '/missions',
    '/about',
    '/manifest.json',
];

// Install: cache static assets
self.addEventListener('install', e => {
    self.skipWaiting();
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll(STATIC))
    );
});

// Activate: hapus cache lama
self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

// Fetch: network first, fallback ke cache
self.addEventListener('fetch', e => {
    // Skip non-GET dan API calls
    if (e.request.method !== 'GET') return;
    if (e.request.url.includes('/api/')) return;

    e.respondWith(
        fetch(e.request)
            .then(res => {
                // Cache response baru untuk halaman HTML dan static assets
                if (res.ok && (e.request.destination === 'document' || e.request.destination === 'style' || e.request.destination === 'script')) {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return res;
            })
            .catch(() => caches.match(e.request))
    );
});