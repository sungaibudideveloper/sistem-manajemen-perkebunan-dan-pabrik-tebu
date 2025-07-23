{{--resources\views\components\sidebar.blade.php--}}
@props(['navigationMenus' => null, 'allSubmenus' => null, 'userPermissions' => null, 'companyName' => null])

@php
    // Pastikan data tersedia, jika tidak gunakan default kosong
    $navigationMenus = $navigationMenus ?? collect([]);
    $allSubmenus = $allSubmenus ?? collect([]);
    $userPermissions = $userPermissions ?? [];
    $compName = $companyName ?? session('company_name', 'Default Company');
    
    // Generate route otomatis berdasarkan pattern (sama seperti navbar)
    $getRoute = function($menuSlug, $submenuSlug) {
        // Clean slug - remove spaces and convert to lowercase
        $submenuSlug = str_replace(' ', '-', strtolower($submenuSlug));
        
        // Special routes yang tidak ikut pattern  
        $specialRoutes = [
            'closing' => 'closing',
            'upload-gpx' => 'upload.gpx.view',
            'upload-gpx-file' => 'upload.gpx.view',
            'export-kml' => 'export.kml.view',
            'export-kml-file' => 'export.kml.view',
            'kerja-harian' => 'input.rencanakerjaharian.index',
        ];
        
        if (isset($specialRoutes[$submenuSlug])) {
            try {
                return route($specialRoutes[$submenuSlug]);
            } catch (\Exception $e) {
                return '#';
            }
        }
        
        // Dashboard routes
        if ($menuSlug === 'dashboard') {
            $specialDashboard = [
                'agronomi-dashboard' => 'dashboard.agronomi',
                'hpt-dashboard' => 'dashboard.hpt',
            ];
            
            if (isset($specialDashboard[$submenuSlug])) {
                try {
                    return route($specialDashboard[$submenuSlug]);
                } catch (\Exception $e) {
                    return '#';
                }
            }
            
            try {
                return route("dashboard.{$submenuSlug}");
            } catch (\Exception $e) {
                return '#';
            }
        }
        
        // Process routes
        if ($menuSlug === 'process') {
            try {
                return route("process.{$submenuSlug}");
            } catch (\Exception $e) {
                return '#';
            }
        }
        
        // Master routes
        if ($menuSlug === 'master') {
            // Aplikasi submenu
            if (in_array($submenuSlug, ['menu', 'submenu', 'subsubmenu'])) {
                try {
                    return route("aplikasi.{$submenuSlug}.index");
                } catch (\Exception $e) {
                    return '#';
                }
            }
            
            // Default master routes
            try {
                return route("masterdata.{$submenuSlug}.index");
            } catch (\Exception $e) {
                return '#';
            }
        }
        
        // Input routes
        if ($menuSlug === 'input-data') {
            try {
                return route("input.{$submenuSlug}.index");
            } catch (\Exception $e) {
                return '#';
            }
        }
        
        // Report routes
        if ($menuSlug === 'report') {
            $specialReport = [
                'agronomi-report' => 'report.agronomi.index',
                'hpt-report' => 'report.hpt.index',
            ];
            
            if (isset($specialReport[$submenuSlug])) {
                try {
                    return route($specialReport[$submenuSlug]);
                } catch (\Exception $e) {
                    return '#';
                }
            }
            
            try {
                return route("report.{$submenuSlug}.index");
            } catch (\Exception $e) {
                return '#';
            }
        }
        
        // Default fallback
        try {
            return route("{$menuSlug}.{$submenuSlug}.index");
        } catch (\Exception $e) {
            return '#';
        }
    };
    
    // Check active
    $isActive = function($slug) {
        return request()->is($slug . '*') || 
               request()->is('*/' . $slug . '*') ||
               request()->routeIs('*.' . $slug . '*');
    };
    
    // Helper function untuk render submenu items
    $renderSubmenuItem = function($item, $menu, $getRoute, $isActive, $level = 1) {
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
<div class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200 transition-all duration-300" 
     x-data="sidebarData()" 
     x-init="init()"
     :class="$store.sidebar.isMinimized ? 'w-16' : 'w-72'">
    
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
    </div>

    <!-- Navigation -->
    <nav class="bg-green-50 flex-1 py-6 space-y-1 overflow-y-auto" 
         :class="$store.sidebar.isMinimized ? 'px-2' : 'px-4'">
        @foreach ($navigationMenus as $menu)
            @php
                // Get submenus for this menu
                $menuSubmenus = $allSubmenus->where('menuid', $menu->menuid);
                $headers = $menuSubmenus->whereNull('parentid')->whereNull('slug');                 
                $directItems = $menuSubmenus->whereNull('parentid')->whereNotNull('slug')->whereIn('name', $userPermissions);
                
                // Check if menu has any visible items
                $hasVisibleItems = false;
                
                // Check headers
                foreach ($headers as $header) {
                    $children = $menuSubmenus->where('parentid', $header->submenuid)->whereIn('name', $userPermissions);
                    if ($children->isNotEmpty()) {
                        $hasVisibleItems = true;
                        break;
                    }
                }
                
                // Check direct items
                if (!$hasVisibleItems && $directItems->isNotEmpty()) {
                    $hasVisibleItems = true;
                }

                // Get menu icon - Updated dengan icon yang lebih sesuai
                $menuIcons = [
                    'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>',
                    'masterdata' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>',
                    'input-data' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>',
                    'process' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>',
                    'report' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>'
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
                                $children = $menuSubmenus->where('parentid', $header->submenuid)->whereIn('name', $userPermissions);
                                $isDuplicate = $children->contains(function($child) use ($header) {
                                    return strtolower($child->name) === strtolower($header->name);
                                });
                            @endphp
                            
                            @if ($children->isNotEmpty())
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
                                            @foreach ($children as $child)
                                                {!! $renderSubmenuItem($child, $menu, $getRoute, $isActive, 2) !!}
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    {{-- Duplicate case - just show children without header --}}
                                    @foreach ($children as $child)
                                        {!! $renderSubmenuItem($child, $menu, $getRoute, $isActive, 1) !!}
                                    @endforeach
                                @endif
                                
                                @if (!$loop->last || $directItems->isNotEmpty())
                                    <div class="my-2 h-px bg-gray-200"></div>
                                @endif
                            @endif
                        @endforeach
                        
                        {{-- Render direct items --}}
                        @foreach ($directItems as $item)
                            @php
                                $itemChildren = $menuSubmenus->where('parentid', $item->submenuid)->whereIn('name', $userPermissions);
                                $hasChildren = $itemChildren->isNotEmpty();
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
                                        @foreach ($itemChildren as $child)
                                            {!! $renderSubmenuItem($child, $menu, $getRoute, $isActive, 2) !!}
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                {{-- Item without children --}}
                                {!! $renderSubmenuItem($item, $menu, $getRoute, $isActive, 1) !!}
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>
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
        },
        
        toggleSidebar() {
            this.$store.sidebar.toggle();
        }
    }
}
</script>