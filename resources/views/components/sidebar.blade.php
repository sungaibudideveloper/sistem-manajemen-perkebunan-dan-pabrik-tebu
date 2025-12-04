{{--resources\views\components\sidebar.blade.php--}}
@props(['navigationMenus' => null, 'allSubmenus' => null, 'userPermissions' => null, 'companyName' => null])

@php
// Pastikan data tersedia, jika tidak gunakan default kosong
$navigationMenus = $navigationMenus ?? collect([]);
$allSubmenus = $allSubmenus ?? collect([]);
$userPermissions = $userPermissions ?? [];
$compName = $companyName ?? session('company_name', 'Default Company');

// Initialize NavigationComposer instance for permission checking
$navComposer = app(\App\View\Composers\NavigationComposer::class);

/**
* ? NEW: Convention-Based Route Generator
*
* CONVENTION:
* Route Name Pattern: {menu_slug}.{submenu_slug}.index
*
* Examples:
* - masterdata + company ? masterdata.company.index
* - usermanagement + support-ticket ? usermanagement.support-ticket.index
* - dashboard + timeline ? dashboard.timeline.index
*
* SPECIAL CASES yang tidak ikut pattern disimpan di $routeOverrides
*/
$getRoute = function($menuSlug, $submenuSlug) {
// Clean slug - remove extra spaces and convert to lowercase
$submenuSlug = trim(str_replace(' ', '-', strtolower($submenuSlug)));

// ============================================
// ROUTE OVERRIDES
// Hanya untuk route yang TIDAK BISA ikut convention
// ============================================
$routeOverrides = [
// Process - special single-word routes (legacy)
'process.closing' => 'closing',
'process.upload-gpx-file' => 'upload.gpx.view',
'process.export-kml-file' => 'export.kml.view',



// Dashboard - remove '-dashboard' suffix
'dashboard.agronomi-dashboard' => 'dashboard.agronomi',
'dashboard.hpt-dashboard' => 'dashboard.hpt',

// Report - remove '-report' suffix
'report.agronomi-report' => 'report.agronomi.index',
'report.hpt-report' => 'report.hpt.index',
];

// ============================================
// LOGIC: Check Override ? Convention ? Fallback
// ============================================

// 1. Check: Ada override untuk kombinasi menu+submenu ini?
$overrideKey = "{$menuSlug}.{$submenuSlug}";
if (isset($routeOverrides[$overrideKey])) {
try {
return route($routeOverrides[$overrideKey]);
} catch (\Exception $e) {
\Log::warning("Override route not found: {$routeOverrides[$overrideKey]}", [
'menu' => $menuSlug,
'submenu' => $submenuSlug
]);
return '#';
}
}

// 2. Convention: Generate route name berdasarkan pattern
// Pattern: {menu_slug}.{submenu_slug}.index
$conventionRouteName = "{$menuSlug}.{$submenuSlug}.index";

try {
return route($conventionRouteName);
} catch (\Exception $e) {
// 3. Fallback: Try without .index suffix (untuk route khusus)
try {
$fallbackRouteName = "{$menuSlug}.{$submenuSlug}";
return route($fallbackRouteName);
} catch (\Exception $e2) {
// Log untuk debugging - route tidak ditemukan
\Log::debug("Route not found for menu item", [
'menu_slug' => $menuSlug,
'submenu_slug' => $submenuSlug,
'tried_routes' => [$conventionRouteName, $fallbackRouteName],
'suggestion' => "Create route: Route::get('{$menuSlug}/{$submenuSlug}', ...)->name('{$conventionRouteName}');"
]);
return '#';
}
}
};



// Check active route
$isActive = function($slug) {
return request()->is($slug . '*') ||
request()->is('*/' . $slug . '*') ||
request()->routeIs('*.' . $slug . '*');
};

// Check if user has permission for menu/submenu
$hasPermission = function($menuSlug, $submenuSlug = null) use ($navComposer) {
$permission = $navComposer->getPermissionName($menuSlug, $submenuSlug);
return $navComposer->hasPermission($permission);
};

// Helper function untuk render submenu items
$renderSubmenuItem = function($item, $menu, $getRoute, $isActive, $hasPermission, $level = 1) {
// Check permission untuk item ini
if (!$hasPermission($menu->slug, $item->slug)) {
return '';
}

$baseClasses = "group flex items-center w-full rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-100 hover:text-gray-900";
$paddingClass = $level === 1 ? "pl-8 pr-4 py-2.5" : "pl-12 pr-4 py-2.5";
$textColor = $isActive($item->slug) ? "text-gray-900 bg-gray-100 font-semibold" : "text-gray-600";

$classes = "$baseClasses $paddingClass $textColor";

return sprintf(
'<a href="%s" class="%s">
    <div class="w-1.5 h-1.5 rounded-full bg-current opacity-60 mr-3 group-hover:opacity-100"></div>
    <span>%s</span>
</a>',
$getRoute($menu->slug, $item->slug),
$classes,
$item->name
);
};
@endphp

<!-- Sidebar Container -->
<!-- 
    ? MOBILE RESPONSIVE CHANGES:
    - lg:fixed = Fixed on desktop, absolute on mobile
    - -translate-x-full lg:translate-x-0 = Hidden off-screen on mobile by default
    - $store.sidebar.mobileOpen controls mobile visibility
-->
<div class="fixed lg:fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200 transition-all duration-300"
    x-data="sidebarData()"
    x-init="init()"
    :class="[
        $store.sidebar.isMinimized ? 'w-16' : 'w-72',
        // Mobile: slide in/out from left
        $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
     ]">

    <!-- Header -->
    <div class="flex h-[94px] shrink-0 items-center justify-between bg-green-50 border-b border-gray-200"
        :class="$store.sidebar.isMinimized ? 'px-3' : 'px-6'">
        <a href="{{ route('home') }}" class="flex items-center space-x-3">
            <!-- Logo with subtle shadow -->
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
        // DEBUG: Tambahkan console.log di sini (INSIDE LOOP)
        if ($menu->slug === 'masterdata') {
            $permissionName = $navComposer->getPermissionName('masterdata', 'tenagakerja');
            echo "<script>console.log('Permission needed for tenagakerja: " . $permissionName . "');</script>";
            
            $hasAccess = $navComposer->hasPermission($permissionName);
            echo "<script>console.log('User has permission: " . ($hasAccess ? 'YES' : 'NO') . "');</script>";
        }

        // Check permission untuk menu utama
        if (!$hasPermission($menu->slug)) {
        continue; // Skip menu jika user tidak ada permission
        }

        // Get submenus for this menu
        $menuSubmenus = $allSubmenus->where('menuid', $menu->menuid);
        $headers = $menuSubmenus->whereNull('parentid')->whereNull('slug');
        $directItems = $menuSubmenus->whereNull('parentid')->whereNotNull('slug');

        // Check if menu has any visible items (WITH PERMISSION CHECK)
        $hasVisibleItems = false;

        // Check headers
        foreach ($headers as $header) {
        $children = $menuSubmenus->where('parentid', $header->submenuid);
        foreach ($children as $child) {
        if ($hasPermission($menu->slug, $child->slug)) {
        $hasVisibleItems = true;
        break 2; // Break both loops
        }
        }
        }

        // Check direct items
        if (!$hasVisibleItems) {
        foreach ($directItems as $item) {
        if ($hasPermission($menu->slug, $item->slug)) {

        $hasVisibleItems = true;
        break;
        }
        }
        }

        // Get menu icon
        $menuIcons = [
        'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
        'masterdata' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>',
        'input' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>',
        'process' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>',
        'report' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>',
        'usermanagement' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0v1H4v-1z" />',
        'pabrik' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2h-4l-3 3H6a2 2 0 00-2 2v7a2 2 0 002 2h12a2 2 0 002-2zM16 17h4m0 0v4m0-4l-4 4" />'
        ];
        $icon = $menuIcons[$menu->slug] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
        @endphp

        @if ($hasVisibleItems)
        <div x-data="{ open: false }" class="space-y-1">
            <!-- Menu Button -->
            <button @click="$store.sidebar.isMinimized ? null : (open = !open)"
                class="group flex items-center w-full rounded-lg font-medium transition-all duration-200"
                :class="[
                            $store.sidebar.isMinimized ? 'px-3 py-3 hover:bg-gray-100 justify-center' : 'px-4 py-3 text-gray-700 hover:text-gray-900 hover:bg-gray-100',
                            open && !$store.sidebar.isMinimized ? 'bg-gray-100 text-gray-900' : ''
                        ]">
                <div class="flex items-center" :class="$store.sidebar.isMinimized ? 'justify-center' : ''">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700 transition-colors duration-200 flex-shrink-0"
                        :class="$store.sidebar.isMinimized ? '' : 'mr-3'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $icon !!}
                    </svg>
                    <span x-show="!$store.sidebar.isMinimized"
                        x-transition:enter="transition ease-out duration-200 delay-75"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="text-sm font-medium">
                        {{ $menu->name }}
                    </span>
                </div>
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

            <!-- Submenu -->
            <div x-show="open && !$store.sidebar.isMinimized"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="space-y-1 bg-gray-50 rounded-lg p-2 ml-2">

                {{-- Render headers with children --}}
                @foreach ($headers as $header)
                @php
                $children = $menuSubmenus->where('parentid', $header->submenuid);
                // Filter children based on permission
                $visibleChildren = $children->filter(function($child) use ($hasPermission, $menu) {
                return $hasPermission($menu->slug, $child->slug);
                });

                $isDuplicate = $visibleChildren->contains(function($child) use ($header) {
                return strtolower($child->name) === strtolower($header->name);
                });
                @endphp

                @if ($visibleChildren->isNotEmpty())
                @if (!$isDuplicate)
                {{-- Header with children --}}
                <div x-data="{ subOpen: false }" class="space-y-1">
                    <button @click="subOpen = !subOpen"
                        class="group flex items-center justify-between w-full px-4 py-2.5 text-left text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200">
                        <span>{{ $header->name }}</span>
                        <svg class="w-3 h-3 transition-transform duration-200 text-gray-400" :class="{ 'rotate-180': subOpen }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="subOpen" x-transition class="space-y-0.5 ml-2">
                        @foreach ($visibleChildren as $child)
                        {!! $renderSubmenuItem($child, $menu, $getRoute, $isActive, $hasPermission, 2) !!}
                        @endforeach
                    </div>
                </div>
                @else
                {{-- Duplicate case - just show children without header --}}
                @foreach ($visibleChildren as $child)
                {!! $renderSubmenuItem($child, $menu, $getRoute, $isActive, $hasPermission, 1) !!}
                @endforeach
                @endif

                @if (!$loop->last || $directItems->filter(function($item) use ($hasPermission, $menu) { return $hasPermission($menu->slug, $item->slug); })->isNotEmpty())
                <div class="my-2 h-px bg-gray-200"></div>
                @endif
                @endif
                @endforeach

                {{-- Render direct items --}}
                @foreach ($directItems as $item)
                @php
                // Check permission untuk direct item
                if (!$hasPermission($menu->slug, $item->slug)) {
                continue;
                }

                $itemChildren = $menuSubmenus->where('parentid', $item->submenuid);
                // Filter children based on permission
                $visibleItemChildren = $itemChildren->filter(function($child) use ($hasPermission, $menu) {
                return $hasPermission($menu->slug, $child->slug);
                });
                $hasChildren = $visibleItemChildren->isNotEmpty();
                @endphp

                @if ($hasChildren)
                {{-- Item with dropdown --}}
                <div x-data="{ subOpen: false }" class="space-y-1">
                    <button @click="subOpen = !subOpen"
                        class="group flex items-center justify-between w-full px-4 py-2.5 text-left text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200
                                               {{ $isActive($item->slug) ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
                        <div class="flex items-center">
                            <div class="w-1.5 h-1.5 rounded-full bg-current opacity-60 mr-3 group-hover:opacity-100"></div>
                            <span>{{ $item->name }}</span>
                        </div>
                        <svg class="w-3 h-3 transition-transform duration-200 text-gray-400" :class="{ 'rotate-180': subOpen }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="subOpen" x-transition class="space-y-0.5 ml-2 bg-white rounded-lg p-2">
                        {{-- Parent link --}}
                        <a href="{{ $getRoute($menu->slug, $item->slug) }}"
                            class="group flex items-center w-full px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-100 hover:text-gray-900
                                                  {{ $isActive($item->slug) ? 'text-gray-900 bg-gray-100 font-semibold' : 'text-gray-600' }}">
                            <div class="w-1.5 h-1.5 rounded-full bg-current opacity-60 mr-3 group-hover:opacity-100"></div>
                            <span>{{ $item->name }}</span>
                        </a>
                        <div class="my-1 h-px bg-gray-200"></div>

                        {{-- Children --}}
                        @foreach ($visibleItemChildren as $child)
                        {!! $renderSubmenuItem($child, $menu, $getRoute, $isActive, $hasPermission, 2) !!}
                        @endforeach
                    </div>
                </div>
                @else
                {{-- Item without children --}}
                {!! $renderSubmenuItem($item, $menu, $getRoute, $isActive, $hasPermission, 1) !!}
                @endif
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </nav>

    <!-- Bottom Collapse Button (Desktop Only) -->
    <div class="hidden lg:block bg-green-50 border-t border-gray-200 p-3">
        <button @click="toggleSidebar()"
            class="flex items-center justify-center w-full p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-white/60 transition-all duration-200 group"
            :class="$store.sidebar.isMinimized ? 'px-1.5' : 'px-3'">

            <!-- Collapse Icon (when expanded) -->
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

            <!-- Expand Icon (when minimized) -->
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
                // Listen untuk event dari komponen lain
                this.$watch('$store.sidebar.isMinimized', (value) => {
                    // Pastikan semua submenu tertutup saat minimize
                    if (value) {
                        // Close all open submenus
                        this.$nextTick(() => {
                            const openElements = this.$el.querySelectorAll('[x-data*="open"]');
                            openElements.forEach(el => {
                                const component = Alpine.evaluate(el, 'this');
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