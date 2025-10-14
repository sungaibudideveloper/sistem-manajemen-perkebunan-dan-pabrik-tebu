{{--resources\views\components\header.blade.php--}}
<header class="bg-green-700 shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="flex h-14 items-center justify-between px-4 sm:px-6 lg:px-8">
        <!-- Left: Desktop toggle button + Mobile menu button + Page title -->
        <div class="flex items-center space-x-4">
            <!-- Mobile Sidebar Toggle Button (Hamburger) -->
            <button @click="$store.sidebar.toggleMobile()" 
                    class="lg:hidden flex p-2 rounded-lg bg-gray-100 text-gray-900 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <!-- Desktop Sidebar Toggle Button (Collapse/Expand) -->
            <button @click="$store.sidebar.toggle()" 
                    class="hidden lg:flex p-2 rounded-lg bg-gray-100 text-gray-900 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                <span class="sr-only">Toggle sidebar</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            
            <!-- ? NOTIFICATION DROPDOWN - UPDATED -->
            <div x-data="notificationDropdown()" @click.away="open = false" class="relative">
                <button @click="toggleDropdown()" 
                   class="relative p-2 rounded-full text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition-colors">
                    <span class="sr-only">View notifications</span>
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <!-- Unread badge -->
                    <span x-show="unreadCount > 0" 
                          x-text="unreadCount > 99 ? '99+' : unreadCount"
                          class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 animate-pulse"></span>
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
                     class="absolute right-0 z-50 mt-2 w-80 sm:w-96 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                    
                    <!-- Header -->
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50 rounded-t-lg">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                            <span x-show="unreadCount > 0" 
                                  class="px-2 py-0.5 bg-red-100 text-red-800 text-xs font-medium rounded-full"
                                  x-text="unreadCount + ' unread'"></span>
                        </div>
                        <button @click="markAllAsRead()" 
                                x-show="unreadCount > 0"
                                class="text-xs text-blue-600 hover:text-blue-800 hover:underline">
                            Mark all read
                        </button>
                    </div>

                    <!-- Notification List -->
                    <div class="max-h-96 overflow-y-auto">
                        <template x-if="loading">
                            <div class="py-8 text-center">
                                <svg class="animate-spin h-6 w-6 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-sm text-gray-500 mt-2">Loading...</p>
                            </div>
                        </template>

                        <template x-if="!loading && notifications.length === 0">
                            <div class="py-8 text-center">
                                <svg class="h-12 w-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-sm text-gray-500 mt-2">No notifications</p>
                            </div>
                        </template>

                        <template x-if="!loading && notifications.length > 0">
                            <div>
                                <template x-for="notif in notifications" :key="notif.notification_id">
                                    <div @click="handleNotificationClick(notif)"
                                         class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 transition-colors"
                                         :class="{ 'bg-blue-50': !notif.is_read }">
                                        <div class="flex items-start space-x-3">
                                            <!-- Icon -->
                                            <div class="flex-shrink-0">
                                                <template x-if="notif.icon === 'ticket'">
                                                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                                        </svg>
                                                    </div>
                                                </template>
                                                <template x-if="notif.icon === 'bell'">
                                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between">
                                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="notif.title"></p>
                                                    <span x-show="!notif.is_read" class="ml-2 w-2 h-2 bg-blue-600 rounded-full flex-shrink-0"></span>
                                                </div>
                                                <p class="text-xs text-gray-600 mt-1 line-clamp-2" x-text="notif.body"></p>
                                                <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notif.createdat)"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                        <a href="{{ route('notifications.index') }}" 
                           class="text-sm text-blue-600 hover:text-blue-800 hover:underline font-medium flex items-center justify-center">
                            View all notifications
                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Profile Dropdown -->
            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                <!-- Ngambil Initial Nama untuk Avatar -->
                @php
                    $name = Auth::user()->name;
                    $words = array_filter(explode(' ', trim($name)));
                    
                    if (count($words) === 0) {
                        $initials = '?';
                    } elseif (count($words) === 1) {
                        $initials = strtoupper(substr($words[0], 0, 1));
                    } else {
                        $initials = strtoupper(substr($words[0], 0, 1)) . 
                                    strtoupper(substr($words[1], 0, 1));
                    }
                    
                    $colors = [
                        'bg-slate-600',
                        'bg-gray-600',
                        'bg-zinc-600',
                        'bg-stone-600',
                        'bg-neutral-600',
                        'bg-slate-700',
                        'bg-gray-700',
                        'bg-zinc-700',
                    ];
                    $colorIndex = abs(crc32($name)) % count($colors);
                    $bgColor = $colors[$colorIndex];
                @endphp
                <button @click="open = !open" 
                        class="flex items-center space-x-2 p-1 rounded-full text-gray-500 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 transition-colors">
                    <div class="h-8 w-8 rounded-full ring-2 ring-gray-200 {{ $bgColor }} flex items-center justify-center">
                        <span class="text-white text-sm font-semibold">{{ $initials }}</span>
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                    </div>
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" x-cloak
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

                    <!-- Company Info (Mobile) -->
                    <div class="md:hidden border-b border-gray-100">
                        <button @click="$dispatch('open-company-modal')" 
                                class="w-full text-left px-4 py-2 hover:bg-gray-50 transition-colors">
                            <div class="text-xs text-gray-600">
                                <div class="font-medium">{{ session('companycode') }}</div>
                                <div class="text-gray-500">{{ session('companyname') }}</div>
                            </div>
                        </button>
                    </div>

                    <!-- Change Company -->
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
// ? NOTIFICATION DROPDOWN COMPONENT
function notificationDropdown() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: 0,
        refreshInterval: null,

        init() {
            // Initial load
            this.loadNotifications();
            
            // Auto-refresh every 30 seconds
            this.refreshInterval = setInterval(() => {
                this.loadUnreadCount();
            }, 30000);
        },

        toggleDropdown() {
            this.open = !this.open;
            if (this.open) {
                this.loadNotifications();
            }
        },

        async loadNotifications() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("notifications.dropdown-data") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
            } catch (error) {
                console.error('Failed to load notifications:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadUnreadCount() {
            try {
                const response = await fetch('{{ route("notifications.unread-count") }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                this.unreadCount = data.unread_count;
            } catch (error) {
                console.error('Failed to load unread count:', error);
            }
        },

        async handleNotificationClick(notif) {
            // Mark as read
            if (!notif.is_read) {
                await this.markAsRead(notif.notification_id);
            }

            // Close dropdown
            this.open = false;

            // Redirect if action_url exists
            if (notif.action_url) {
                window.location.href = notif.action_url;
            } else {
                // Default: go to notifications page
                window.location.href = '{{ route("notifications.index") }}';
            }
        },

        async markAsRead(notificationId) {
            try {
                const response = await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    // Update local state
                    const notif = this.notifications.find(n => n.notification_id === notificationId);
                    if (notif) {
                        notif.is_read = true;
                    }
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                const response = await fetch('{{ route("notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    // Update local state
                    this.notifications.forEach(n => n.is_read = true);
                    this.unreadCount = 0;
                }
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            const hours = Math.floor(diff / 3600000);
            const days = Math.floor(diff / 86400000);

            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (hours < 24) return `${hours}h ago`;
            if (days < 7) return `${days}d ago`;
            
            return date.toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'short',
                year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
            });
        },

        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    }
}
</script>