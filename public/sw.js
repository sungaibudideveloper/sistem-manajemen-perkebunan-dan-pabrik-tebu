const CACHE_NAME = 'sb-tebu-v1';
const urlsToCache = [
  './',
  './manifest.json',
  './img/icon-sb-tebu-circle.png',
  './img/icon-1024x1024.png',
  './asset/font-awesome-6.5.1-all.min.css',
  './asset/inter.css',
  './offline.html'
];

// Install Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
      .catch(error => {
        // Silent fail - no console.log in production
      })
  );
});

// Activate Service Worker
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch Strategy: Network First for HTML, Cache First for assets
self.addEventListener('fetch', event => {
  // Skip caching for POST requests and session-related routes
  if (event.request.method !== 'GET' || 
      event.request.url.includes('/setSession') ||
      event.request.url.includes('/login') ||
      event.request.url.includes('/logout') ||
      event.request.url.includes('/api/')) {
    return fetch(event.request);
  }
  
  // Network First for HTML pages (to get fresh session data)
  if (event.request.headers.get('accept') && 
      event.request.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Cache the response if successful
          if (response.status === 200) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => cache.put(event.request, responseToCache))
              .catch(() => {
                // Silent fail
              });
          }
          return response;
        })
        .catch(() => {
          // Fallback to cache or offline page
          return caches.match(event.request).then(cachedResponse => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // Show offline page for navigation requests
            if (event.request.destination === 'document') {
              return caches.match('./offline.html');
            }
          });
        })
    );
  } else {
    // Cache First for static assets (CSS, JS, images)
    event.respondWith(
      caches.match(event.request)
        .then(response => {
          if (response) {
            return response;
          }
          
          return fetch(event.request).then(response => {
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }
            
            const responseToCache = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => cache.put(event.request, responseToCache))
              .catch(() => {
                // Silent fail
              });
            
            return response;
          });
        })
    );
  }
});