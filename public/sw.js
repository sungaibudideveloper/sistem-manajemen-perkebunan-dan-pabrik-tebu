// public/sw.js - FIXED VERSION
const CACHE_VERSION = 'v7'; // Increment this on every deployment
const CACHE_NAME = `sb-tebu-${CACHE_VERSION}`;
const STATIC_CACHE = `sb-tebu-static-${CACHE_VERSION}`;

// Only cache truly static assets
const STATIC_ASSETS = [
    './manifest.json',
    './img/icon-sb-tebu-circle.png', 
    './img/icon-1024x1024.png',
    './asset/font-awesome-6.5.1-all.min.css',
    './asset/inter.css',
    './offline.html'
];

// Environment detection
const isDev = () => {
    const hostname = self.location.hostname;
    const pathname = self.location.pathname;
    
    return hostname === 'localhost' || 
           hostname === '127.0.0.1' ||
           hostname.includes('192.168.') ||
           hostname.includes('.test') ||
           hostname.includes('.local') ||
           pathname.includes('/public') ||
           (self.location.port !== '' && self.location.port !== '80' && self.location.port !== '443');
};

// Logging helper
function logSW(message, data = null, level = 'info') {
    if (!isDev()) return;
    
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

// Check if request is authentication related
function isAuthRequest(url) {
    const authPatterns = [
        /\/login/,
        /\/logout/,
        /\/register/,
        /\/password/,
        /\/sanctum/,
        /\/csrf/,
        /\/user/,
        /\/auth/,
        /\/session/,
        /\/setSession/
    ];
    
    return authPatterns.some(pattern => pattern.test(url));
}

// Determine if URL should never be cached
function shouldNeverCache(url, method) {
    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        
        // Auth requests must always go to network
        if (isAuthRequest(pathname)) {
            logSW('Auth request, skip cache:', url);
            return true;
        }
        
        // Only GET requests can be cached
        if (method !== 'GET') {
            return true;
        }
        
        // CRITICAL FIX: In development, NEVER cache /build/ assets
        if (isDev()) {
            if (pathname.includes('/build/')) {
                logSW('Dev: Skip build asset (bypass SW):', url);
                return true;
            }
            // Also skip any JS files in development
            if (pathname.endsWith('.js') && !pathname.includes('/asset/')) {
                logSW('Dev: Skip JS file:', url);
                return true;
            }
        }
        
        // Skip URLs with dynamic parameters (except CSS/JS with version hashes)
        if (urlObj.search && !pathname.match(/\.(css|js)$/)) {
            return true;
        }
        
        // Laravel specific patterns to skip
        const skipPatterns = [
            /\/api\//,
            /\/ajax\//,
            /\/livewire\//,
            /\/broadcasting\//,
            /\/storage\//,
            /\/build\/manifest\.json$/,
            /\/@vite\//,
            /\/__vite_ping/,
            /\/hot$/,
            /\/build\/.*\.js$/, // CRITICAL: Skip all build JS files
            /\/build\/.*\.css$/ // Also skip build CSS files in case of issues
        ];
        
        return skipPatterns.some(pattern => pattern.test(pathname));
        
    } catch (error) {
        logSW('Invalid URL:', url, 'warn');
        return true;
    }
}

// Check if resource is a static asset
function isStaticAsset(url) {
    try {
        const urlObj = new URL(url);
        const pathname = urlObj.pathname;
        
        // NEVER treat /build/ assets as static in development
        if (isDev() && pathname.includes('/build/')) {
            return false;
        }
        
        // Static file extensions (but not build assets)
        const staticPatterns = [
            /\.(png|jpg|jpeg|gif|svg|ico|webp)$/,
            /\.(woff|woff2|ttf|eot|otf)$/,
            /\/asset\/.+\.(css|js)$/, // Only /asset/ CSS/JS, not /build/
            /\/img\//,
            /\/fonts\//,
            /manifest\.json$/,
            /favicon\.ico$/,
            /offline\.html$/
        ];
        
        return staticPatterns.some(pattern => pattern.test(pathname));
        
    } catch (error) {
        return false;
    }
}

// Fetch with timeout
function fetchWithTimeout(request, timeout = null) {
    const defaultTimeout = isDev() ? 30000 : 10000;
    const actualTimeout = timeout || defaultTimeout;
    
    return Promise.race([
        fetch(request, {
            credentials: 'same-origin' // Important for Laravel sessions
        }),
        new Promise((_, reject) =>
            setTimeout(() => reject(new Error(`Timeout: ${actualTimeout}ms`)), actualTimeout)
        )
    ]);
}

// Install event
self.addEventListener('install', event => {
    logSW('Installing service worker...');
    
    if (isDev()) {
        // In development, skip waiting immediately and don't cache anything
        logSW('Development mode: skipping cache installation');
        self.skipWaiting();
        return;
    }
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                logSW('Caching static assets...');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                logSW('Installation complete');
                self.skipWaiting();
            })
            .catch(error => {
                logSW('Cache failed:', error.message, 'error');
                self.skipWaiting();
            })
    );
});

// Activate event
self.addEventListener('activate', event => {
    logSW('Activating service worker...');
    
    event.waitUntil(
        Promise.all([
            // Clean old caches
            caches.keys().then(names => {
                return Promise.all(
                    names
                        .filter(name => name.startsWith('sb-tebu-') && name !== STATIC_CACHE)
                        .map(name => {
                            logSW('Deleting old cache:', name);
                            return caches.delete(name);
                        })
                );
            }),
            // Take control
            self.clients.claim()
        ])
    );
});

// Fetch event - MORE Conservative approach for development
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = request.url;
    const method = request.method;
    const destination = request.destination;
    
    // Skip non-HTTP
    if (!url.startsWith('http')) {
        return;
    }
    
    // CRITICAL: In development, bypass SW for build assets entirely
    if (isDev() && url.includes('/build/')) {
        logSW('Dev: Bypassing SW completely for build asset:', url);
        return; // Let browser handle normally
    }
    
    // Auth requests - NEVER cache
    if (isAuthRequest(url)) {
        event.respondWith(
            fetchWithTimeout(request)
                .catch(error => {
                    logSW('Auth request failed:', error.message, 'error');
                    throw error;
                })
        );
        return;
    }
    
    // Skip dynamic content
    if (shouldNeverCache(url, method)) {
        // Don't even intercept, let browser handle
        if (isDev()) {
            logSW('Dev: Not intercepting:', url);
            return;
        }
        event.respondWith(fetchWithTimeout(request));
        return;
    }
    
    // Development - VERY minimal caching, mostly bypass
    if (isDev()) {
        // Only cache very specific static assets
        if (isStaticAsset(url) && !url.includes('/build/')) {
            event.respondWith(
                fetchWithTimeout(request)
                    .catch(() => caches.match(request))
                    .catch(() => {
                        logSW('Dev: Failed to fetch static asset:', url, 'warn');
                        throw new Error('Network failed');
                    })
            );
        } else {
            // For everything else in dev, just fetch normally
            event.respondWith(
                fetchWithTimeout(request).catch(() => {
                    if (destination === 'document') {
                        return caches.match('./offline.html');
                    }
                    throw new Error('Network failed');
                })
            );
        }
        return;
    }
    
    // Production - static assets cache first
    if (isStaticAsset(url)) {
        event.respondWith(
            caches.match(request).then(cached => {
                const networkFetch = fetchWithTimeout(request, 5000)
                    .then(response => {
                        if (response && response.ok) {
                            caches.open(STATIC_CACHE).then(cache => {
                                cache.put(request, response.clone());
                            });
                        }
                        return response;
                    });
                
                return cached || networkFetch;
            })
        );
        return;
    }
    
    // HTML pages - network first
    if (destination === 'document') {
        event.respondWith(
            fetchWithTimeout(request)
                .catch(() => {
                    return caches.match('./offline.html').then(response => {
                        return response || new Response(
                            `<!DOCTYPE html>
                            <html>
                            <head>
                                <title>Offline</title>
                                <meta charset="UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                <style>
                                    body {
                                        font-family: system-ui, -apple-system, sans-serif;
                                        display: flex;
                                        justify-content: center;
                                        align-items: center;
                                        min-height: 100vh;
                                        margin: 0;
                                        background: #f3f4f6;
                                    }
                                    .container {
                                        text-align: center;
                                        padding: 2rem;
                                        background: white;
                                        border-radius: 12px;
                                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                                    }
                                    button {
                                        margin-top: 1rem;
                                        padding: 10px 24px;
                                        background: #3b82f6;
                                        color: white;
                                        border: none;
                                        border-radius: 6px;
                                        cursor: pointer;
                                    }
                                </style>
                            </head>
                            <body>
                                <div class="container">
                                    <h2>Koneksi Terputus</h2>
                                    <p>Tidak dapat terhubung ke server.</p>
                                    <button onclick="location.reload()">Coba Lagi</button>
                                </div>
                            </body>
                            </html>`,
                            { headers: { 'Content-Type': 'text/html' }, status: 503 }
                        );
                    });
                })
        );
        return;
    }
    
    // Default - network first
    event.respondWith(
        fetchWithTimeout(request).catch(() => {
            return caches.match(request).then(cached => {
                return cached || new Response('Network error', { status: 503 });
            });
        })
    );
});

// Message handler
self.addEventListener('message', event => {
    const { data } = event;
    
    if (data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (data.type === 'CLEAR_CACHE') {
        caches.keys().then(names => {
            Promise.all(names.map(name => caches.delete(name))).then(() => {
                logSW('All caches cleared');
                if (event.ports && event.ports[0]) {
                    event.ports[0].postMessage({ success: true });
                }
            });
        });
    }
});