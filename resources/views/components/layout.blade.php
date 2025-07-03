<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-username" content="{{ Auth::user()->usernm }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('asset/inter.css') }}">
    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">
    <link rel="icon" href="{{ asset('Logo-1.png') }}" type="image/png">
    <style>
        [x-cloak] { display: none !important; }
    </style>
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
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300"
             :class="sidebarMinimized ? 'ml-16' : 'ml-72'">
            
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
            <main class="flex-1 overflow-y-auto">
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
        // Main layout Alpine.js component
        function mainLayoutData() {
            return {
                sidebarOpen: false,
                sidebarMinimized: false,
                
                init() {
                    // Listen untuk sidebar toggle event
                    window.addEventListener('sidebar-toggle', (e) => {
                        this.sidebarMinimized = e.detail.minimized;
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
                isMinimized: false,
                
                toggle() {
                    this.isMinimized = !this.isMinimized;
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