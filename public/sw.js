// public/sw.js - OPTIMIZED VERSION dengan Best Practices untuk SPA
const CACHE_NAME = 'sb-tebu-v3'; // Increment version untuk force update
const STATIC_CACHE = 'sb-tebu-static-v3';
const API_CACHE = 'sb-tebu-api-v3';

// Static assets yang AMAN untuk di-cache
const STATIC_ASSETS = [
    './manifest.json',
    './img/icon-sb-tebu-circle.png', 
    './img/icon-1024x1024.png',
    './asset/font-awesome-6.5.1-all.min.css',
    './asset/inter.css',
    './offline.html',
    './index.html' // Application Shell
];

// Development detection - Enhanced untuk production safety
const isDev = self.location.hostname === 'localhostoff' || 
              self.location.hostname.includes('127.0.0.1') ||
              self.location.hostname.includes('192.168.') ||
              self.location.hostname.includes('.test') ||
              self.location.hostname.includes('.local') ||
              self.location.port !== '';

// Pattern-based detection - ENHANCED dengan Cross-Origin & Edge Cases
function shouldNeverCache(url, method) {
    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        
        // 1. Cross-origin requests - NEVER cache
        if (urlObj.origin !== location.origin) {
            return true;
        }
        
        // 2. NON-GET requests - NEVER cache
        if (method !== 'GET') {
            return true;
        }
        
        // 3. URLs with authentication tokens atau sensitive params
        if (urlObj.searchParams.has('token') || 
            urlObj.searchParams.has('_token') ||
            urlObj.searchParams.has('api_key') ||
            urlObj.search.includes('password')) {
            return true;
        }
        
        // 4. Pattern matching untuk Laravel routes
        const neverCachePatterns = [
            // Authentication & Session
            /\/login/,
            /\/logout/,
            /\/register/,
            /\/password/,
            
            // API endpoints  
            /\/api\//,
            
            // Form submissions (CRUD operations)
            /\/store\/?$/,
            /\/update\/?$/,
            /\/destroy\/?$/,
            /\/delete\/\d+\/?$/,
            /\/edit\/\d+\/?$/,
            
            // Laravel Resource Controller methods
            /\/create\/?$/,
            /\/show\/\d+\/?$/,
            
            // Input routes (form heavy)
            /\/input\//,
            
            // Masterdata CRUD
            /\/masterdata\//,
            
            // Real-time features
            /\/notifications\//,
            /\/chat\//,
            /\/pusher\//,
            /\/broadcasting\//,
            
            // Session & CSRF
            /\/setSession/,
            /\/_token/,
            /\/csrf/,
            /\/sanctum\//,
            
            // Reports & downloads
            /\/report\//,
            /\/export\//,
            /\/download\//,
            /\.pdf$/,
            /\.xlsx?$/,
            /\.csv$/,
            
            // Dashboard dengan data real-time
            /\/dashboard\/.*api/,
            
            // Approval processes
            /approval/,
            /approve/,
            /reject/,
            /submit/,
            
            // Admin routes
            /\/admin\//,
            
            // Development tools
            /\/_ignition/,
            /\/telescope/,
            /\/horizon/,
            /\/debugbar/,
            
            // File uploads & media
            /\/upload/,
            /\/file\//,
            /\/media\//,
            /\/storage\//,
            
            // Laravel specific
            /\/livewire\//,
            /\/ajax\//,
            /\/vendor\//,
            
            // Search dengan dynamic results
            /\/search\?/,
            
            // Laravel Mix/Vite HMR
            /\/hot$/,
            /\/__vite_ping/,
            /\/@vite\//
        ];
        
        return neverCachePatterns.some(pattern => pattern.test(pathname));
        
    } catch (error) {
        logSW('Invalid URL detected, skipping cache:', url, 'warn');
        return true;
    }
}

function shouldCacheAsset(url) {
    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        
        // Cross-origin static assets (CDN)
        if (urlObj.origin !== location.origin) {
            const trustedCDNs = [
                'cdn.jsdelivr.net',
                'cdnjs.cloudflare.com',
                'fonts.googleapis.com',
                'fonts.gstatic.com',
                'unpkg.com'
            ];
            
            const isTrustedCDN = trustedCDNs.some(cdn => urlObj.hostname.includes(cdn));
            if (!isTrustedCDN) return false;
        }
        
        // Cache static assets only
        const cacheablePatterns = [
            // Standard web assets
            /\.(css|js|png|jpg|jpeg|gif|svg|ico|webp|avif)$/,
            /\.(woff|woff2|ttf|eot|otf)$/,
            
            // Directory-based assets
            /\/asset\//,
            /\/img\//,
            /\/images\//,
            /\/fonts\//,
            /\/css\//,
            /\/js\//,
            /\/build\//,
            /\/dist\//,
            /\/public\//,
            
            // PWA files
            /manifest\.json$/,
            /favicon\.ico$/,
            /offline\.html$/,
            /sw\.js$/,
            
            // Laravel Mix/Vite assets
            /\/mix-manifest\.json$/,
            /\/@vite\/client$/
        ];
        
        return cacheablePatterns.some(pattern => pattern.test(pathname));
        
    } catch (error) {
        logSW('Error checking cacheable asset:', url, 'warn');
        return false;
    }
}

// Enhanced logging untuk debugging - PRODUCTION SAFE
function logSW(message, data = null, level = 'info') {
    // PRODUCTION: Console logging completely disabled
    if (!isDev) return;
    
    // DEVELOPMENT: Full logging with timestamps
    const prefix = '[SW]';
    const timestamp = new Date().toLocaleTimeString();
    
    switch(level) {
        case 'error':
            console.error(`${prefix} ${timestamp}`, message, data);
            break;
        case 'warn':
            console.warn(`${prefix} ${timestamp}`, message, data);
            break;
        default:
            console.log(`${prefix} ${timestamp}`, message, data);
    }
}

// Network timeout dengan environment-aware timeout
function fetchWithTimeout(request, customTimeout = null) {
    // Development: timeout lebih lama karena Laravel bisa lambat
    const timeout = customTimeout || (isDev ? 15000 : 8000);
    
    return Promise.race([
        fetch(request),
        new Promise((_, reject) =>
            setTimeout(() => reject(new Error(`Network timeout after ${timeout}ms`)), timeout)
        )
    ]);
}

// Application Shell handler untuk SPA routing
async function handleNavigation(request, preloadPromise = null) {
    const url = request.url;
    
    try {
        // Try navigation preload first (if available)
        if (preloadPromise) {
            try {
                const preloadResponse = await preloadPromise;
                if (preloadResponse && preloadResponse.ok) {
                    logSW('Using navigation preload for:', url);
                    return preloadResponse;
                }
            } catch (error) {
                logSW('Navigation preload failed:', error.message, 'warn');
            }
        }
        
        // Try network first untuk fresh content
        const networkResponse = await fetchWithTimeout(request, isDev ? 20000 : 10000);
        if (networkResponse && networkResponse.ok) {
            logSW('Network response for navigation:', url);
            return networkResponse;
        }
        
    } catch (error) {
        logSW('Network failed for navigation:', error.message, 'warn');
    }
    
    // Fallback ke cached app shell (index.html)
    const cachedShell = await caches.match('./index.html');
    if (cachedShell) {
        logSW('Serving app shell for:', url);
        return cachedShell;
    }
    
    // Ultimate fallback ke offline page
    const offlinePage = await caches.match('./offline.html');
    if (offlinePage) {
        logSW('Serving offline page for:', url);
        return offlinePage;
    }
    
    // Final fallback response
    return new Response(
        `<!DOCTYPE html>
        <html>
        <head><title>Offline - SB Tebu</title></head>
        <body>
            <h1>Aplikasi Sedang Offline</h1>
            <p>Silakan periksa koneksi internet Anda.</p>
            <button onclick="window.location.reload()">Coba Lagi</button>
        </body>
        </html>`,
        { 
            headers: { 'Content-Type': 'text/html; charset=utf-8' },
            status: 503,
            statusText: 'Service Unavailable'
        }
    );
}

// Install Service Worker
self.addEventListener('install', event => {
    logSW('Installing service worker...');
    
    event.waitUntil(
        Promise.all([
            // Cache static assets
            caches.open(STATIC_CACHE)
                .then(cache => {
                    logSW('Caching static assets...');
                    return cache.addAll(STATIC_ASSETS);
                })
                .catch(error => {
                    logSW('Failed to cache static assets:', error.message, 'error');
                }),
            
            // Create API cache
            caches.open(API_CACHE)
        ]).then(() => {
            logSW('Service worker installed successfully');
        })
    );
    
    // Force activation
    self.skipWaiting();
});

// Activate Service Worker dengan Navigation Preload
self.addEventListener('activate', event => {
    logSW('Activating service worker...');
    
    event.waitUntil(
        Promise.all([
            // Clean up old caches
            caches.keys().then(cacheNames => {
                const validCaches = [CACHE_NAME, STATIC_CACHE, API_CACHE];
                const deletePromises = cacheNames
                    .filter(cacheName => !validCaches.includes(cacheName))
                    .map(cacheName => {
                        logSW('Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    });
                
                logSW(`Cleaned up ${deletePromises.length} old caches`);
                return Promise.all(deletePromises);
            }),
            
            // Enable navigation preload jika tersedia
            self.registration.navigationPreload ? 
                self.registration.navigationPreload.enable().then(() => {
                    logSW('Navigation preload enabled');
                }).catch(error => {
                    logSW('Navigation preload not supported:', error.message, 'warn');
                }) : 
                Promise.resolve()
        ])
    );
    
    // Take control of all clients
    self.clients.claim();
    logSW('Service worker activated and controlling clients');
});

// Enhanced Fetch Handler dengan Request Destination Routing
self.addEventListener('fetch', event => {
    const { request } = event;
    const { url, method, destination, mode } = request;
    
    // Skip non-http requests
    if (!url.startsWith('http')) {
        return;
    }
    
    // Request destination based routing
    switch (destination) {
        case 'document':
            // Navigation requests - Application Shell Pattern
            if (mode === 'navigate' && !shouldNeverCache(url, method)) {
                event.respondWith(
                    handleNavigation(request, event.preloadResponse)
                );
                return;
            }
            break;
            
        case 'script':
        case 'style':
        case 'image':
        case 'font':
            // Static assets - Cache-First with background update
            if (shouldCacheAsset(url)) {
                event.respondWith(handleStaticAsset(request));
                return;
            }
            break;
            
        case '': // XHR/Fetch requests (API calls)
            // Handle API requests
            if (shouldNeverCache(url, method)) {
                event.respondWith(handleApiRequest(request));
                return;
            }
            break;
    }
    
    // Default handling untuk requests lainnya
    if (shouldNeverCache(url, method)) {
        // Network-only untuk dynamic content
        event.respondWith(
            fetchWithTimeout(request, isDev ? 15000 : 10000).catch(error => {
                logSW('Network request failed:', url, 'warn');
                throw error;
            })
        );
        return;
    }
    
    // Cache-first untuk static content lainnya
    if (shouldCacheAsset(url)) {
        event.respondWith(handleStaticAsset(request));
        return;
    }
    
    // Network-first untuk HTML pages
    event.respondWith(
        fetchWithTimeout(request, isDev ? 20000 : 8000)
            .then(response => {
                logSW('Network response for:', url);
                return response;
            })
            .catch(error => {
                logSW('Network failed, trying cache for:', url, 'warn');
                return caches.match(request).then(cachedResponse => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    throw error;
                });
            })
    );
});

// Handle static assets dengan Cache-First + Background Update
async function handleStaticAsset(request) {
    const url = request.url;
    
    try {
        // Check cache first
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            logSW('Serving from cache:', url);
            
            // Background update untuk freshness
            fetchWithTimeout(request, 8000)
                .then(networkResponse => {
                    if (networkResponse && networkResponse.ok) {
                        caches.open(STATIC_CACHE)
                            .then(cache => cache.put(request, networkResponse.clone()))
                            .catch(error => logSW('Background cache update failed:', error.message, 'warn'));
                    }
                })
                .catch(() => {
                    // Silent fail untuk background update
                });
            
            return cachedResponse;
        }
        
        // Not in cache, fetch from network
        const networkResponse = await fetchWithTimeout(request, isDev ? 15000 : 10000);
        
        if (networkResponse && networkResponse.ok) {
            // Cache successful responses
            const responseToCache = networkResponse.clone();
            caches.open(STATIC_CACHE)
                .then(cache => {
                    cache.put(request, responseToCache);
                    logSW('Cached new asset:', url);
                })
                .catch(error => logSW('Cache storage failed:', error.message, 'warn'));
        }
        
        return networkResponse;
        
    } catch (error) {
        logSW('Failed to fetch static asset:', url, 'error');
        throw error;
    }
}

// Handle API requests dengan Network-First + Short Cache
async function handleApiRequest(request) {
    const url = request.url;
    
    try {
        // Network first untuk data freshness
        const networkResponse = await fetchWithTimeout(request, isDev ? 15000 : 10000);
        
        if (networkResponse && networkResponse.ok && request.method === 'GET') {
            // Cache GET API responses dengan short TTL
            const responseToCache = networkResponse.clone();
            caches.open(API_CACHE)
                .then(cache => {
                    cache.put(request, responseToCache);
                    logSW('Cached API response:', url);
                })
                .catch(error => logSW('API cache failed:', error.message, 'warn'));
        }
        
        return networkResponse;
        
    } catch (error) {
        logSW('API request failed, trying cache:', url, 'warn');
        
        // Fallback ke cache untuk GET requests
        if (request.method === 'GET') {
            const cachedResponse = await caches.match(request);
            if (cachedResponse) {
                logSW('Serving stale API data from cache:', url);
                return cachedResponse;
            }
        }
        
        throw error;
    }
}

// Error handling - Production safe logging
self.addEventListener('error', (event) => {
    if (isDev) {
        logSW('Service Worker error:', event.error, 'error');
    }
    // Production: Silent fail, optionally send to analytics
    // navigator.sendBeacon('/api/sw-errors', JSON.stringify({error: event.error.message}));
});

self.addEventListener('unhandledrejection', (event) => {
    if (isDev) {
        logSW('Unhandled promise rejection:', event.reason, 'error');
    }
    event.preventDefault();
    // Production: Silent fail, optionally send to analytics
    // navigator.sendBeacon('/api/sw-errors', JSON.stringify({rejection: event.reason}));
});

// Message handling untuk manual cache updates
self.addEventListener('message', (event) => {
    const { data } = event;
    
    switch (data.type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;
            
        case 'CLEAR_CACHE':
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => caches.delete(cacheName))
                );
            }).then(() => {
                logSW('All caches cleared');
                event.ports[0]?.postMessage({ success: true });
            });
            break;
            
        case 'GET_VERSION':
            event.ports[0]?.postMessage({ 
                version: CACHE_NAME,
                isDev: isDev
            });
            break;
    }
});