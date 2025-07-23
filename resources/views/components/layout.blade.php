<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-username" content="{{ Auth::user()->usernm }}">
    <meta name="user-id" content="{{ auth()->user()->userid }}">
    <meta name="user-name" content="{{ auth()->user()->name }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('asset/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">
    <link rel="icon" href="{{ asset('Logo-1.png') }}" type="image/png">
    
    <style>
        /* Prevent Alpine.js flicker */
        [x-cloak] { display: none !important; }
        
        /* CSS variables for sidebar width */
        :root {
            --sidebar-width: 18rem; /* 288px = ml-72 */
            --sidebar-collapsed-width: 4rem; /* 64px = ml-16 */
        }
        
        /* Main content positioning */
        .main-content-area {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }
        
        /* When sidebar is minimized */
        .sidebar-minimized .main-content-area {
            margin-left: var(--sidebar-collapsed-width);
        }
        
        /* Disable transitions on initial load */
        .no-transitions * {
            -webkit-transition: none !important;
            -moz-transition: none !important;
            -ms-transition: none !important;
            -o-transition: none !important;
            transition: none !important;
        }
    </style>
    
    <!-- Set sidebar state before render -->
    <script>
        (function() {
            const isMinimized = localStorage.getItem('sidebar-minimized') === 'true';
            if (isMinimized) {
                document.documentElement.classList.add('sidebar-minimized');
            }
            // Add no-transitions class to prevent animation on load
            document.documentElement.classList.add('no-transitions');
        })();
    </script>
    
    <script defer src="{{ asset('asset/alpinejs.min.js') }}"></script>
    <script src="{{ asset('asset/chart.js') }}"></script>
    <script src="{{ asset('asset/chartjs-plugin-datalabels@2.0.0.js') }}"></script>
    <script src="{{ asset('asset/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('asset/simple-datatables@9.0.3.js') }}"></script>

    <title>{{ $title }}</title>
    <x-slot:head></x-slot>
</head>

<body class="h-full bg-gray-50">
    <div class="flex min-h-screen" x-data="mainLayoutData()" x-init="init()">
        <!-- Sidebar -->
        <x-sidebar></x-sidebar>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 main-content-area"
             x-ref="mainContent">
            
            <!-- Header -->
            <x-header>{{ $title }}
                <x-slot:navhint>
                    <x-nav-hint>
                        {{ $navbar ?? 'Not Defined' }}
                        @isset($nav)
                            <x-slot:secondarySlot>
                                {{ $nav }}
                            </x-slot:secondarySlot>
                        @endisset
                        @isset($navnav)
                            @if (isset($navnav) && isset($routeName))
                                <x-slot:routeName>
                                    {{ $routeName }}
                                </x-slot:routeName>
                                <x-slot:tertiarySlot>
                                    {{ $navnav }}
                                </x-slot:tertiarySlot>
                            @else
                                <x-slot:tertiarySlot>
                                    {{ $navnav }}
                                </x-slot:tertiarySlot>
                            @endif
                        @endisset
                    </x-nav-hint>
                </x-slot:navhint>
            </x-header>

            <!-- Main Content -->
            <main class="bg-gray-200 flex-1 overflow-y-auto">
                {{ $hero ?? null }}
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    @error('duplicateClosing')
                        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400 w-fit">
                            {{ $message }}
                        </div>
                    @enderror

                    @if (session('success1'))
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                alert("{{ session('success1') }}");
                            });
                        </script>
                    @endif

                    @if (session('error'))
                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                alert("{{ session('error') }}");
                            });
                        </script>
                    @endif

                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <x-footer></x-footer>
        </div>

        <!-- Mobile sidebar overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50 lg:hidden"
             @click="sidebarOpen = false">
        </div>
    </div>

    <!-- Global Company Modal - Available on all pages -->
    @if(isset($company) && $company)
        <x-company-modal :companies="$company" />
    @endif

    <script>
        // Enable transitions after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.documentElement.classList.remove('no-transitions');
            }, 50);
        });

        // Main layout Alpine.js component
        function mainLayoutData() {
            return {
                sidebarOpen: false,
                sidebarMinimized: false,
                
                init() {
                    // Get initial state from localStorage
                    this.sidebarMinimized = JSON.parse(localStorage.getItem('sidebar-minimized') || 'false');
                    
                    // Listen untuk sidebar toggle event
                    window.addEventListener('sidebar-toggle', (e) => {
                        this.sidebarMinimized = e.detail.minimized;
                        // Update class on document element
                        if (this.sidebarMinimized) {
                            document.documentElement.classList.add('sidebar-minimized');
                        } else {
                            document.documentElement.classList.remove('sidebar-minimized');
                        }
                    });
                    
                    // Set initial state dari store jika ada
                    this.$nextTick(() => {
                        if (Alpine.store('sidebar')) {
                            this.sidebarMinimized = Alpine.store('sidebar').isMinimized;
                        }
                    });
                }
            }
        }

        // Global Alpine.js store for app-wide state
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                isMinimized: JSON.parse(localStorage.getItem('sidebar-minimized') || 'false'),
                
                toggle() {
                    this.isMinimized = !this.isMinimized;
                    localStorage.setItem('sidebar-minimized', this.isMinimized);
                    
                    // Update class on document element
                    if (this.isMinimized) {
                        document.documentElement.classList.add('sidebar-minimized');
                    } else {
                        document.documentElement.classList.remove('sidebar-minimized');
                    }
                    
                    // Dispatch event for other components to listen
                    window.dispatchEvent(new CustomEvent('sidebar-toggle', {
                        detail: { minimized: this.isMinimized }
                    }));
                }
            });
        });
    </script>

    <x-sprite-svg />
    @stack('scripts')
</body>
<x-script></x-script>
<x-style></x-style>

</html>