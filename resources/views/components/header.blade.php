<header class="bg-green-700 shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex h-14 items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Left: Desktop toggle button + Mobile menu button + Page title -->
        <div class="flex items-center space-x-4">
            <!-- Desktop Sidebar Toggle Button -->
            <button @click="toggleSidebar()" 
                    class="hidden lg:flex p-2 rounded-lg bg-gray-100 text-gray-900 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                <span class="sr-only">Toggle sidebar</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Mobile menu button -->
            <button @click="sidebarOpen = !sidebarOpen" 
                    class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Page Title -->
            <h1 class="text-lg font-semibold text-white truncate">{{ $slot }}</h1>
        </div>

        <!-- Center: Company Info (hidden on mobile) - Clickable -->
        <div class="hidden md:flex items-center space-x-2 text-sm text-gray-600">
            <button @click="$dispatch('open-company-modal')" 
                    class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors group">
                <div class="flex items-center space-x-2">
                    <div class="h-2 w-2 bg-green-500 rounded-full"></div>
                    <span class="font-medium">{{ session('companycode') }}</span>
                    <span class="text-gray-400">•</span>
                    <span>{{ session('companyname') }}</span>
                </div>
                <svg class="h-4 w-4 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                </svg>
            </button>
        </div>

        <!-- Right: Notifications + User Profile -->
        <div class="flex items-center space-x-3">
            <!-- Notifications -->
            <a href="{{ route('notifications.index') }}" 
               class="relative p-2 rounded-full text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition-colors">
                <span class="sr-only">View notifications</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <span id="notification-dot" class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full hidden animate-pulse"></span>
            </a>

            <!-- User Profile Dropdown -->
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <button @click="open = !open" 
                        class="flex items-center space-x-2 p-1 rounded-full text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition-colors">
                    <img class="h-8 w-8 rounded-full ring-2 ring-gray-200" 
                         src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                         alt="{{ Auth::user()->name }}">
                    <div class="hidden md:block text-left">
                        <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open"
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    
                    <!-- User Info -->
                    <div class="px-4 py-2 border-b border-gray-100">
                        <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                    </div>

                    <!-- Company Info (Mobile) - Clickable -->
                    <div class="md:hidden border-b border-gray-100">
                        <button @click="$dispatch('open-company-modal')" 
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 transition-colors">
                            <div class="text-xs text-gray-600">
                                <div class="font-medium">{{ session('companycode') }}</div>
                                <div class="text-gray-500">{{ session('companyname') }}</div>
                            </div>
                        </button>
                    </div>

                    <!-- Change Company Option -->
                    <button @click="$dispatch('open-company-modal')" 
                            class="flex w-full items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span>Change Company</span>
                    </button>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="flex w-full items-center space-x-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Sign out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Breadcrumb/Navigation hint -->
    @if(isset($navhint))
        <div class="border-t border-gray-200 bg-green-50 px-4 py-2 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    {{ $navhint }}
                </div>
                
                <!-- Monitoring Period di breadcrumb row -->
                @if(isset($period) && $period)
                    <div class="text-right">
                        <div class="flex justify-end items-center space-x-2 text-sm text-gray-700">
                            <span class="text-xs text-gray-500">Monitoring Period:</span>
                            <span class="font-medium">{{ $period }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</header>

<script>
// Function untuk toggle sidebar dari header
function toggleSidebar() {
    if (Alpine.store('sidebar')) {
        Alpine.store('sidebar').toggle();
    }
}

// Pastikan function tersedia di window scope
window.toggleSidebar = toggleSidebar;
</script>