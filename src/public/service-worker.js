const CACHE_VERSION = 'v1';
const CACHE_NAME = `dispensaries-${CACHE_VERSION}`;

const STATIC_ASSETS = [
  '/',
  '/css/main.css',
  '/js/main.js',
  '/js/map.js',
  'https://cdn.tailwindcss.com',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap'
];

const API_CACHE = 'api-cache-v1';
const IMAGE_CACHE = 'image-cache-v1';

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('Service Worker: Installing...');

  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Service Worker: Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker: Activating...');

  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME && cacheName !== API_CACHE && cacheName !== IMAGE_CACHE) {
              console.log('Service Worker: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => self.clients.claim())
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip cross-origin requests
  if (url.origin !== location.origin) {
    return;
  }

  // API requests - network first, cache fallback
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          const responseClone = response.clone();
          caches.open(API_CACHE).then((cache) => {
            cache.put(request, responseClone);
          });
          return response;
        })
        .catch(() => {
          return caches.match(request);
        })
    );
    return;
  }

  // Images - cache first, network fallback
  if (request.destination === 'image') {
    event.respondWith(
      caches.match(request)
        .then((cached) => {
          if (cached) return cached;

          return fetch(request).then((response) => {
            const responseClone = response.clone();
            caches.open(IMAGE_CACHE).then((cache) => {
              cache.put(request, responseClone);
            });
            return response;
          });
        })
    );
    return;
  }

  // HTML pages - network first, cache fallback
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, responseClone);
          });
          return response;
        })
        .catch(() => {
          return caches.match(request)
            .then((cached) => {
              if (cached) return cached;

              // Return offline page
              return caches.match('/offline.html');
            });
        })
    );
    return;
  }

  // Default - cache first, network fallback
  event.respondWith(
    caches.match(request)
      .then((cached) => {
        if (cached) return cached;

        return fetch(request).then((response) => {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, responseClone);
          });
          return response;
        });
      })
  );
});

// Background sync for offline form submissions
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-reviews') {
    event.waitUntil(syncReviews());
  }
  if (event.tag === 'sync-votes') {
    event.waitUntil(syncVotes());
  }
});

async function syncReviews() {
  // Sync pending reviews from IndexedDB
  console.log('Service Worker: Syncing reviews...');
  // Implementation would use IndexedDB to store offline reviews
}

async function syncVotes() {
  // Sync pending votes from IndexedDB
  console.log('Service Worker: Syncing votes...');
  // Implementation would use IndexedDB to store offline votes
}

// Push notifications (future feature)
self.addEventListener('push', (event) => {
  const data = event.data?.json() || {};

  const options = {
    body: data.body || 'New update available',
    icon: '/icons/icon-192.png',
    badge: '/icons/icon-192.png',
    vibrate: [200, 100, 200],
    data: {
      url: data.url || '/'
    }
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'Top Dispensaries', options)
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(
    clients.openWindow(event.notification.data.url)
  );
});
