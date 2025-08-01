// public/sw.js - ENHANCED VERSION dengan International Best Practices
const CACHE_NAME = 'sb-tebu-v2'; // Increment version untuk force update
const STATIC_CACHE = 'sb-tebu-static-v2';

// Static assets yang AMAN untuk di-cache
const STATIC_ASSETS = [
    './manifest.json',
    './img/icon-sb-tebu-circle.png', 
    './img/icon-1024x1024.png',
    './asset/font-awesome-6.5.1-all.min.css',
    './asset/inter.css',
    './offline.html'
];

// Pattern-based detection - ENHANCED dengan Cross-Origin & Edge Cases
function shouldNeverCache(url, method) {
    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        
        // 1. Cross-origin requests - NEVER cache (ChatGPT suggestion ✅)
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
            
            // Form submissions (CRUD operations) - dengan trailing slash handling
            /\/store\/?$/,           // create operations
            /\/update\/?$/,          // update operations  
            /\/destroy\/?$/,         // delete operations
            /\/delete\/\d+\/?$/,     // delete with ID
            /\/edit\/\d+\/?$/,       // edit forms
            
            // Laravel Resource Controller methods
            /\/create\/?$/,
            /\/show\/\d+\/?$/,
            
            // Input routes (form heavy)
            /\/input\//,
            
            // Masterdata CRUD
            /\/masterdata\//,
            
            // Notifications & real-time
            /\/notifications\//,
            /\/chat\//,
            
            // Session & CSRF
            /\/setSession/,
            /\/_token/,
            /\/csrf/,
            
            // Reports & downloads (large files)
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
            
            // File uploads & media
            /\/upload/,
            /\/file\//,
            /\/media\//,
            
            // Livewire & AJAX
            /\/livewire\//,
            /\/ajax\//,
            
            // Search dengan dynamic results
            /\/search\?/
        ];
        
        // Check if URL matches any never-cache pattern
        return neverCachePatterns.some(pattern => pattern.test(pathname));
        
    } catch (error) {
        // Invalid URL - don't cache untuk safety
        console.warn('SW: Invalid URL detected, skipping cache:', url);
        return true;
    }
}

function shouldCacheAsset(url) {
    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        
        // Cross-origin static assets (CDN) - cache dengan hati-hati
        if (urlObj.origin !== location.origin) {
            // Only cache well-known CDN static assets
            const trustedCDNs = [
                'cdn.jsdelivr.net',
                'cdnjs.cloudflare.com',
                'fonts.googleapis.com',
                'fonts.gstatic.com'
            ];
            
            const isTrustedCDN = trustedCDNs.some(cdn => urlObj.hostname.includes(cdn));
            if (!isTrustedCDN) return false;
        }
        
        // Cache static assets only
        const cacheablePatterns = [
            // Standard web assets
            /\.(css|js|png|jpg|jpeg|gif|svg|ico|webp|avif)$/,
            /\.(woff|woff2|ttf|eot|otf)$/,  // Fonts (tambah .otf per ChatGPT)
            
            // Directory-based assets
            /\/asset\//,
            /\/img\//,
            /\/images\//,
            /\/fonts\//,
            /\/css\//,
            /\/js\//,
            /\/build\//,    // Vite build directory
            /\/dist\//,     // Distribution directory
            
            // PWA files
            /manifest\.json$/,
            /favicon\.ico$/,
            /offline\.html$/
        ];
        
        return cacheablePatterns.some(pattern => pattern.test(pathname));
        
    } catch (error) {
        console.warn('SW: Error checking cacheable asset:', url);
        return false;
    }
}

// Enhanced logging untuk debugging (production-safe)
function logSW(message, data = null, level = 'info') {
    if (self.location.hostname === 'localhost' || self.location.hostname.includes('127.0.0.1')) {
        const prefix = '[SW]';
        switch(level) {
            case 'error':
                console.error(prefix, message, data);
                break;
            case 'warn':
                console.warn(prefix, message, data);
                break;
            default:
                console.log(prefix, message, data);
        }
    }
}
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .catch(error => {
                // Silent fail for production
            })
    );
    self.skipWaiting();
});

// Activate Service Worker  
self.addEventListener('activate', event => {
    logSW('Activating service worker...');
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            const deletePromises = cacheNames.map(cacheName => {
                if (cacheName !== CACHE_NAME && cacheName !== STATIC_CACHE) {
                    logSW('Deleting old cache:', cacheName);
                    return caches.delete(cacheName);
                }
            }).filter(Boolean);
            
            logSW(`Cleaned up ${deletePromises.length} old caches`);
            return Promise.all(deletePromises);
        })
    );
    
    self.clients.claim();
    logSW('Service worker activated and controlling clients');
});

// Enhanced Fetch Strategy dengan Network Timeout
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = request.url;
    
    // RULE 1: Skip caching untuk dynamic/form routes
    if (shouldNeverCache(url, request.method)) {
        // CRITICAL: Pakai event.respondWith untuk bypass cache (fix ChatGPT issue)
        event.respondWith(
            fetchWithTimeout(request, 10000).catch(error => {
                logSW('Network request failed:', url, 'warn');
                throw error;
            })
        );
        return;
    }
    
    // RULE 2: Cache-First strategy untuk static assets
    if (shouldCacheAsset(url)) {
        event.respondWith(
            caches.match(request).then(cachedResponse => {
                if (cachedResponse) {
                    logSW('Serving from cache:', url);
                    
                    // Background update untuk static assets (silent update)
                    fetchWithTimeout(request, 5000)
                        .then(networkResponse => {
                            if (networkResponse && networkResponse.status >= 200 && networkResponse.status < 300) {
                                const responseToCache = networkResponse.clone();
                                caches.open(STATIC_CACHE)
                                    .then(cache => cache.put(request, responseToCache))
                                    .catch(error => logSW('Background cache update failed:', error, 'warn'));
                            }
                        })
                        .catch(() => {
                            // Silent fail untuk background update
                        });
                    
                    return cachedResponse;
                }
                
                // Not in cache, fetch from network
                return fetchWithTimeout(request, 8000).then(response => {
                    // Only cache successful responses
                    if (response && response.status >= 200 && response.status < 300) {
                        const responseToCache = response.clone();
                        caches.open(STATIC_CACHE)
                            .then(cache => {
                                cache.put(request, responseToCache);
                                logSW('Cached new asset:', url);
                            })
                            .catch(error => logSW('Cache storage failed:', error, 'warn'));
                    }
                    return response;
                }).catch(error => {
                    logSW('Failed to fetch asset:', url, 'error');
                    throw error;
                });
            })
        );
        return;
    }
    
    // RULE 3: Network-First untuk HTML pages dengan timeout (ChatGPT suggestion ✅)
    event.respondWith(
        fetchWithTimeout(request, 5000)
            .then(response => {
                logSW('Network response for HTML:', url);
                return response;
            })
            .catch(error => {
                logSW('Network failed for HTML, trying offline fallback:', url, 'warn');
                
                // Only show offline page for navigation requests
                if (request.destination === 'document' || 
                    request.mode === 'navigate' ||
                    request.headers.get('accept')?.includes('text/html')) {
                    return caches.match('./offline.html').then(offlineResponse => {
                        if (offlineResponse) {
                            logSW('Serving offline page');
                            return offlineResponse;
                        }
                        // Fallback response jika offline.html tidak ada
                        return new Response(
                            '<h1>Offline</h1><p>Please check your internet connection.</p>',
                            { 
                                headers: { 'Content-Type': 'text/html' },
                                status: 503,
                                statusText: 'Service Unavailable'
                            }
                        );
                    });
                }
                throw error;
            })
    );
});

// Network timeout helper function (ChatGPT suggestion ✅)
function fetchWithTimeout(request, timeout = 8000) {
    return Promise.race([
        fetch(request),
        new Promise((_, reject) =>
            setTimeout(() => reject(new Error('Network timeout')), timeout)
        )
    ]);
}