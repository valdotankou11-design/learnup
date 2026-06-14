const CACHE_NAME = 'learnup-v1';
const ASSETS = [
  '/',
  '/assets/css/style.css',
  '/assets/js/app.js',
  '/manifest.json'
];

// Installation — mise en cache des assets
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
  );
  self.skipWaiting();
});

// Activation — suppression des anciens caches
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Fetch — stratégie Network First pour les pages PHP, Cache First pour les assets
self.addEventListener('fetch', e => {
  const url = new URL(e.request.url);

  // Ne pas mettre en cache les requêtes API
  if (url.pathname.startsWith('/api/')) return;

  // Assets statiques — Cache First
  if (url.pathname.startsWith('/assets/')) {
    e.respondWith(
      caches.match(e.request).then(cached => cached || fetch(e.request))
    );
    return;
  }

  // Pages PHP — Network First
  e.respondWith(
    fetch(e.request)
      .then(res => {
        const clone = res.clone();
        caches.open(CACHE_NAME).then(cache => cache.put(e.request, clone));
        return res;
      })
      .catch(() => caches.match(e.request))
  );
});
