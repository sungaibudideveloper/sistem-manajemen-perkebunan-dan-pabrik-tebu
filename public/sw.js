// Deteksi environment secara dinamis
const isProduction = location.hostname !== 'localhost' && location.hostname !== '127.0.0.1';
const BASE_PATH = isProduction ? '' : '/tebu/public';

const CACHE_NAME = 'sb-tebu-v1';
const urlsToCache = [
  `${BASE_PATH}/`,
  `${BASE_PATH}/img/icon-1024x1024.png`,
  `${BASE_PATH}/img/icon-sb-tebu-circle.png`,
  `${BASE_PATH}/asset/font-awesome-6.5.1-all.min.css`,
  `${BASE_PATH}/asset/inter.css`
];

// Install Service Worker
self.addEventListener('install', event => {
  console.log('SW installing with BASE_PATH:', BASE_PATH);
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Caching URLs:', urlsToCache);
        return cache.addAll(urlsToCache);
      })
      .catch(error => {
        console.error('Cache addAll failed:', error);
      })
  );
});

// Activate Service Worker
self.addEventListener('activate', event => {
  console.log('SW activating');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Helper function untuk check apakah URL dalam scope aplikasi
function isAppScope(url) {
  const pathname = new URL(url).pathname;
  if (isProduction) {
    return pathname.startsWith('/');
  } else {
    return pathname.startsWith('/tebu/public/');
  }
}

// Fetch Strategy: Network First for HTML, Cache First for assets
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Skip caching untuk request di luar scope aplikasi
  if (!isAppScope(event.request.url)) {
    return;
  }
  
  // Skip caching for POST requests and session-related routes
  if (event.request.method !== 'GET' || 
      url.pathname.includes('/setSession') ||
      url.pathname.includes('/login') ||
      url.pathname.includes('/logout') ||
      url.pathname.includes('/api/')) {
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
              .catch(error => console.log('Cache put failed:', error));
          }
          return response;
        })
        .catch(() => {
          // Fallback to cache if network fails
          console.log('Network failed, trying cache for:', event.request.url);
          return caches.match(event.request);
        })
    );
  } else {
    // Cache First for static assets (CSS, JS, images)
    event.respondWith(
      caches.match(event.request)
        .then(response => {
          if (response) {
            console.log('Cache hit for:', event.request.url);
            return response;
          }
          
          console.log('Cache miss, fetching:', event.request.url);
          return fetch(event.request).then(response => {
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }
            
            const responseToCache = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              })
              .catch(error => console.log('Cache put failed:', error));
            
            return response;
          });
        })
    );
  }
});