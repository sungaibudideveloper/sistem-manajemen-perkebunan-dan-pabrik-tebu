{{-- resources/views/components/global-loader.blade.php --}}

<!-- Global Overlay Loading Indicator for PWA -->
<div id="global-loader" x-data="globalLoader()" x-show="show" x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-gray-900/50 backdrop-blur-sm"
    style="display: none;" @loading-start.window="show = true" @loading-stop.window="show = false">

    <!-- Loading Card Container -->
    <div x-show="show" x-transition:enter="transition ease-out duration-300 delay-100"
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90" class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm mx-4">

        <!-- Loading Animation Container -->
        <div class="flex flex-col items-center space-y-6">

            <!-- Option 1: Spinner with Logo Icon (Recommended for PWA) -->
            <div class="relative">
                <!-- Outer spinning ring -->
                <div class="w-20 h-20 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin"></div>

                <!-- Inner logo/icon -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Loading Text -->
            <div class="text-center space-y-2">
                <h3 class="text-lg font-semibold text-gray-900">Loading</h3>
                <p class="text-sm text-gray-600">Please wait...</p>
            </div>

            <!-- Optional: Progress dots animation -->
            <div class="flex space-x-2">
                <div class="w-2 h-2 bg-emerald-600 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                <div class="w-2 h-2 bg-emerald-600 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                <div class="w-2 h-2 bg-emerald-600 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Prevent body scroll when loading */
    body.loading-active {
        overflow: hidden;
    }

    /* Custom animation for smooth appearance */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* Pulse animation for alternative design */
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    /* Safe area for notched devices */
    @supports (padding: max(0px)) {
        #global-loader {
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
        }
    }

    /* Prevent interaction with overlay */
    #global-loader {
        -webkit-tap-highlight-color: transparent;
        touch-action: none;
        user-select: none;
    }
</style>

<script>
    // ============================================
    // GLOBAL LOADER - PRODUCTION VERSION (FIXED)
    // ============================================

    function globalLoader() {
        return {
            show: false,
            timeoutId: null,

            init() {
                this.$watch('show', (value) => {
                    if (value) {
                        document.body.classList.add('loading-active');

                        // Safety timeout: auto-hide after 30 seconds
                        this.timeoutId = setTimeout(() => {
                            this.show = false;
                        }, 30000);
                    } else {
                        document.body.classList.remove('loading-active');

                        if (this.timeoutId) {
                            clearTimeout(this.timeoutId);
                        }
                    }
                });
            }
        }
    }

    // ============================================
    // ALPINE STORE
    // ============================================

    document.addEventListener('alpine:init', () => {
        Alpine.store('loading', {
            isLoading: false,

            start() {
                // console.log('LOADER START CALLED', new Error().stack); // Trace siapa yang manggil
                this.isLoading = true;
                window.dispatchEvent(new CustomEvent('loading-start'));
            },

            stop() {
                // console.log('LOADER STOP CALLED'); // Trace
                this.isLoading = false;
                window.dispatchEvent(new CustomEvent('loading-stop'));
            }
        });
    });

    // ============================================
    // AUTO-DETECT NAVIGATION
    // ============================================

    (function() {
        let isNavigating = false;
        const MIN_LOADING_TIME = 500; // Minimal tampil 500ms

        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            const url = args[0];

            // Skip loader untuk AJAX endpoints
            if (typeof url === 'string' && (
                    url.includes('/chat/') ||
                    url.includes('/api/') ||
                    url.includes('/notifications/') ||
                    url.includes('/_debugbar/')
                )) {
                return originalFetch.apply(this, args);
            }

            return originalFetch.apply(this, args);
        };

        // Detect link clicks
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');

            if (isNavigating || !link) return;

            // Skip Loader for paginations
            if (link.closest('#pagination-links')) {
                return;
            }

            // Only intercept internal navigation links
            if (link.href &&
                !link.hasAttribute('target') &&
                !link.hasAttribute('download') &&
                !link.href.startsWith('javascript:') &&
                !link.href.startsWith('mailto:') &&
                !link.href.startsWith('tel:') &&
                !link.href.startsWith('#') &&
                link.href.startsWith(window.location.origin)) {

                isNavigating = true;

                // Mark in sessionStorage so new page knows
                sessionStorage.setItem('_navigation_loading', 'true');
                sessionStorage.setItem('_navigation_start', Date.now());

                if (Alpine.store('loading')) {
                    Alpine.store('loading').start();
                }
            }
        }, true);

        // Detect form submissions (GET only)
        document.addEventListener('submit', function(e) {
            const form = e.target;

            if (isNavigating) return;

            // Skip form yang ada Alpine x-data/x-on (Alpine form)
            if (form.closest('[x-data]')) {
                return; // Skip loader untuk Alpine forms
            }

            // Skip form dengan action /chat/ atau /api/
            if (form.action && (
                    form.action.includes('/chat/') ||
                    form.action.includes('/api/')
                )) {
                return;
            }

            if (form.method.toLowerCase() === 'get') {
                isNavigating = true;

                sessionStorage.setItem('_navigation_loading', 'true');
                sessionStorage.setItem('_navigation_start', Date.now());

                if (Alpine.store('loading')) {
                    Alpine.store('loading').start();
                }
            }
        }, true);

        // Handle browser back/forward navigation
        window.addEventListener('popstate', function() {
            if (!isNavigating) {
                isNavigating = true;

                sessionStorage.setItem('_navigation_loading', 'true');
                sessionStorage.setItem('_navigation_start', Date.now());

                if (Alpine.store('loading')) {
                    Alpine.store('loading').start();
                }
            }
        });

        // CRITICAL: Check if we arrived from navigation
        // This runs on NEW page load
        if (sessionStorage.getItem('_navigation_loading') === 'true') {
            const navigationStart = parseInt(sessionStorage.getItem('_navigation_start')) || Date.now();
            const elapsedTime = Date.now() - navigationStart;
            const remainingTime = Math.max(0, MIN_LOADING_TIME - elapsedTime);

            // Show loader immediately if navigation was in progress
            if (typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('loading')) {
                Alpine.store('loading').start();
            } else {
                window.dispatchEvent(new CustomEvent('loading-start'));
            }

            // Wait for page to be fully ready
            function stopWhenReady() {
                if (document.readyState === 'complete') {
                    setTimeout(() => {
                        sessionStorage.removeItem('_navigation_loading');
                        sessionStorage.removeItem('_navigation_start');

                        if (Alpine.store('loading')) {
                            Alpine.store('loading').stop();
                        }
                    }, remainingTime);
                } else {
                    // Not ready yet, wait for load event
                    window.addEventListener('load', function() {
                        setTimeout(() => {
                            sessionStorage.removeItem('_navigation_loading');
                            sessionStorage.removeItem('_navigation_start');

                            if (Alpine.store('loading')) {
                                Alpine.store('loading').stop();
                            }
                        }, remainingTime);
                    }, {
                        once: true
                    });
                }
            }

            // Start checking when Alpine is ready
            if (typeof Alpine !== 'undefined' && Alpine.store('loading')) {
                stopWhenReady();
            } else {
                document.addEventListener('alpine:init', stopWhenReady);
            }
        }

        // Handle bfcache (back button with cached page)
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                // Page from cache, clean up
                sessionStorage.removeItem('_navigation_loading');
                sessionStorage.removeItem('_navigation_start');

                if (Alpine.store('loading')) {
                    Alpine.store('loading').stop();
                }
            }
        });
    })();
</script>
