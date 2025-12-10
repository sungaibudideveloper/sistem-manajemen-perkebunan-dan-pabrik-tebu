{{-- resources/views/components/sidebar.blade.php --}}
@props(['navigationMenus' => null, 'companyName' => null])

@php
// Fallback jika composer belum inject data
$navigationMenus = $navigationMenus ?? collect([]);
$compName = $companyName ?? session('companyname', 'Default Company');

/**
 * Helper: Check if current route is active
 */
$isActive = function($routeName) {
    if (!$routeName) return false;
    
    try {
        $currentRoute = request()->route()->getName();
        
        // Exact match
        if ($currentRoute === $routeName) {
            return true;
        }
        
        // Prefix match (e.g., 'masterdata.company' matches 'masterdata.company.index')
        if (str_starts_with($currentRoute, $routeName . '.')) {
            return true;
        }
        
        return false;
    } catch (\Exception $e) {
        return false;
    }
};

/**
 * Helper: Check if menu has any active child
 */
$hasActiveChild = function($children) use ($isActive, &$hasActiveChild) {
    if (!isset($children) || !is_array($children)) {
        return false;
    }
    
    foreach ($children as $child) {
        // Check if child route is active
        if (isset($child['route']) && $isActive($child['route'])) {
            return true;
        }
        
        // Recursive check for nested children
        if (isset($child['children']) && $hasActiveChild($child['children'])) {
            return true;
        }
    }
    
    return false;
};

/**
 * Helper: Get icon SVG path
 */
$getIcon = function($iconName) {
    $icons = [
        'layout-dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>',
        'database' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>',
        'file-edit' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>',
        'file-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
        'settings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>',
    ];
    
    return $icons[$iconName] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
};
@endphp

<!-- Sidebar Container -->
<div class="fixed lg:fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200 transition-all duration-300"
    x-data="sidebarData()"
    x-init="init()"
    :class="[
        $store.sidebar.isMinimized ? 'w-16' : 'w-72',
        $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
     ]">

    <!-- Header -->
    <div class="flex h-[94px] shrink-0 items-center justify-between bg-green-50 border-b border-gray-200"
        :class="$store.sidebar.isMinimized ? 'px-3' : 'px-6'">
        <a href="{{ route('home') }}" class="flex items-center space-x-3">
            <!-- Logo -->
            <div class="flex items-center justify-center w-10 h-10 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
                <img class="h-6 w-6 object-contain" src="{{ asset('img/Logo-1.png') }}" alt="Logo">
            </div>

            <!-- Brand Text -->
            <div x-show="!$store.sidebar.isMinimized"
                x-transition:enter="transition ease-out duration-200 delay-100"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">
                <div class="text-gray-900 font-bold text-base leading-tight">Sungai Budi Group</div>
                <div class="text-gray-500 text-xs font-medium">Sugarcane Management System</div>
            </div>
        </a>

        <!-- Mobile Close Button -->
        <button @click="$store.sidebar.closeMobile()"
            class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="bg-green-50 flex-1 py-6 space-y-1 overflow-y-auto"
        :class="$store.sidebar.isMinimized ? 'px-2' : 'px-4'">
        
        @foreach ($navigationMenus as $menu)
            @php
                $menuHasActiveChild = $hasActiveChild($menu['children'] ?? []);
            @endphp
            
            <div x-data="{ open: {{ $menuHasActiveChild ? 'true' : 'false' }} }" class="space-y-1">
                <!-- Main Menu Button -->
                <button @click="$store.sidebar.isMinimized ? null : (open = !open)"
                    class="group flex items-center w-full rounded-lg font-medium transition-all duration-200"
                    :class="[
                        $store.sidebar.isMinimized ? 'px-3 py-3 hover:bg-gray-100 justify-center' : 'px-4 py-3 text-gray-700 hover:text-gray-900 hover:bg-gray-100',
                        open && !$store.sidebar.isMinimized ? 'bg-gray-100 text-gray-900' : ''
                    ]">
                    
                    <div class="flex items-center" :class="$store.sidebar.isMinimized ? 'justify-center' : ''">
                        <!-- Icon -->
                        <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700 transition-colors duration-200 flex-shrink-0"
                            :class="$store.sidebar.isMinimized ? '' : 'mr-3'"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $getIcon($menu['icon'] ?? 'menu') !!}
                        </svg>
                        
                        <!-- Menu Name -->
                        <span x-show="!$store.sidebar.isMinimized"
                            x-transition:enter="transition ease-out duration-200 delay-75"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            class="text-sm font-medium">
                            {{ $menu['name'] }}
                        </span>
                    </div>
                    
                    <!-- Dropdown Arrow -->
                    <svg x-show="!$store.sidebar.isMinimized && !open"
                        x-transition
                        class="w-4 h-4 transition-transform duration-200 flex-shrink-0 ml-auto text-gray-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    <svg x-show="!$store.sidebar.isMinimized && open"
                        x-transition
                        class="w-4 h-4 transition-transform duration-200 rotate-180 flex-shrink-0 ml-auto text-gray-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Submenu Container -->
                @if(isset($menu['children']) && count($menu['children']) > 0)
                    <div x-show="open && !$store.sidebar.isMinimized"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="space-y-1 bg-gray-50 rounded-lg p-2 ml-2">

                        @foreach($menu['children'] as $child)
                            @php
                                $isChildGroup = !isset($child['route']) && isset($child['children']);
                                $childIsActive = isset($child['route']) && $isActive($child['route']);
                                $childHasActiveChild = $hasActiveChild($child['children'] ?? []);
                            @endphp

                            @if($isChildGroup)
                                {{-- Group/Header with nested children --}}
                                <div x-data="{ subOpen: {{ $childHasActiveChild ? 'true' : 'false' }} }" class="space-y-1">
                                    <button @click="subOpen = !subOpen"
                                        class="group flex items-center justify-between w-full px-4 py-2.5 text-left text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200">
                                        <span>{{ $child['name'] }}</span>
                                        <svg class="w-3 h-3 transition-transform duration-200 text-gray-400" 
                                            :class="{ 'rotate-180': subOpen }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="subOpen" x-transition class="space-y-0.5 ml-2">
                                        @foreach($child['children'] as $grandchild)
                                            @if(isset($grandchild['route']))
                                                @php
                                                    $grandchildIsActive = $isActive($grandchild['route']);
                                                @endphp
                                                <a href="{{ route($grandchild['route']) }}"
                                                    class="group flex items-center w-full pl-8 pr-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 {{ $grandchildIsActive ? 'text-gray-900 bg-gray-100 font-semibold' : 'text-gray-600' }}">
                                                    <div class="w-1.5 h-1.5 rounded-full bg-current opacity-60 mr-3 group-hover:opacity-100"></div>
                                                    <span>{{ $grandchild['name'] }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Direct menu item with route --}}
                                @if(isset($child['route']))
                                    <a href="{{ route($child['route']) }}"
                                        class="group flex items-center w-full pl-8 pr-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-100 hover:text-gray-900 {{ $childIsActive ? 'text-gray-900 bg-gray-100 font-semibold' : 'text-gray-600' }}">
                                        <div class="w-1.5 h-1.5 rounded-full bg-current opacity-60 mr-3 group-hover:opacity-100"></div>
                                        <span>{{ $child['name'] }}</span>
                                    </a>
                                @endif
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </nav>

    <!-- Bottom Collapse Button (Desktop Only) -->
    <div class="hidden lg:block bg-green-50 border-t border-gray-200 p-3">
        <button @click="toggleSidebar()"
            class="flex items-center justify-center w-full p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-white/60 transition-all duration-200 group"
            :class="$store.sidebar.isMinimized ? 'px-1.5' : 'px-3'">

            <!-- Collapse Icon -->
            <div x-show="!$store.sidebar.isMinimized"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="flex items-center space-x-2">
                <svg class="w-4 h-4 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
                <span class="text-xs font-medium">Collapse</span>
            </div>

            <!-- Expand Icon -->
            <div x-show="$store.sidebar.isMinimized"
                x-transition:enter="transition ease-out duration-150 delay-50"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <svg class="w-4 h-4 transition-colors duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
            </div>
        </button>
    </div>
</div>

<script>
    // Sidebar component data
    function sidebarData() {
        return {
            init() {
                // Auto-close submenus when sidebar is minimized
                this.$watch('$store.sidebar.isMinimized', (value) => {
                    if (value) {
                        this.$nextTick(() => {
                            const openElements = this.$el.querySelectorAll('[x-data*="open"]');
                            openElements.forEach(el => {
                                const component = Alpine.$data(el);
                                if (component.open !== undefined) {
                                    component.open = false;
                                }
                                if (component.subOpen !== undefined) {
                                    component.subOpen = false;
                                }
                            });
                        });
                    }
                });

                // Close mobile sidebar when clicking menu item
                this.$el.addEventListener('click', (e) => {
                    if (e.target.tagName === 'A' && window.innerWidth < 1024) {
                        this.$store.sidebar.closeMobile();
                    }
                });
            },

            toggleSidebar() {
                this.$store.sidebar.toggle();
            }
        }
    }
</script>