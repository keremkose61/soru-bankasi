/**
 * BrainBoost Soru Bankası — Service Worker
 * PWA Offline Caching & Background Support
 */

const CACHE_NAME = 'brainboost-cache-v1';
const OFFLINE_URL = './offline.html';

const PRECACHE_ASSETS = [
    './',
    './offline.html',
    './manifest.json',
    './public/css/style.css',
    './public/images/logo.png',
    './public/images/icons/icon-192x192.png',
    './public/images/icons/icon-512x512.png',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// 1. Kurulum (Install) & Önbelleğe Alma
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('Bazı harici kaynaklar önbelleğe alınamadı:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

// 2. Etkinleştirme (Activate) & Eski Önbellekleri Temizleme
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// 3. İstek Yakalama (Fetch Strategy)
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Sadece GET isteklerini önbellekle
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    // HTML / Sayfa Gezinme İstekleri (Network First, Cache Fallback -> Offline Page)
    if (request.mode === 'navigate' || (request.headers.get('accept') && request.headers.get('accept').includes('text/html'))) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    // Sayfa başarılı yüklendiyse önbelleğe kaydet (böylece çevrimdışıyken de açılır)
                    if (networkResponse && networkResponse.status === 200) {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                })
                .catch(async () => {
                    // Ağ yoksa önce önbellekte var mı bak
                    const cachedResponse = await caches.match(request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Önbellekte de yoksa çevrimdışı sayfasını göster
                    return caches.match(OFFLINE_URL);
                })
        );
        return;
    }

    // Statik Varlıklar (CSS, JS, Resimler, Fontlar): Stale-While-Revalidate
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            const fetchPromise = fetch(request)
                .then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
                        const responseToCache = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseToCache);
                        });
                    }
                    return networkResponse;
                })
                .catch(() => {
                    // Statik kaynak ağı koptuğunda sessizce yutulabilir
                });

            return cachedResponse || fetchPromise;
        })
    );
});
