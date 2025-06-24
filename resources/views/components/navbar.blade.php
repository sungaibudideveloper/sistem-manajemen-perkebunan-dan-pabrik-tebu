@props(['navigationMenus', 'allSubmenus', 'userPermissions', 'companyName'])

@php
    $compName = $companyName ?? 'Default Company';
    
    // Generate route otomatis berdasarkan pattern
    $getRoute = function($menuSlug, $submenuSlug) {
    // Log untuk debug
    \Log::info("Generating route for menu: {$menuSlug}, submenu: {$submenuSlug}");
    
    // Special routes yang tidak ikut pattern  
    $specialRoutes = [
        'closing' => 'closing',
        'upload-gpx' => 'upload.gpx.view',
        'export-kml' => 'export.kml.view',
        'kerja-harian' => 'input.kerjaharian.rencanakerjaharian.index',
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
        // Special case mapping dulu
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
        
        // Default pattern untuk yang lain (timeline, maps, dll)
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
        // Special case mapping untuk report
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
        
        // Default pattern untuk report lain
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
        \Log::warning("Route not found: {$menuSlug}.{$submenuSlug}.index");
    }
};
    
    // Check active
    $isActive = function($slug) {
        return request()->is($slug . '*') || 
               request()->is('*/' . $slug . '*') ||
               request()->routeIs('*.' . $slug . '*');
    };
@endphp

<nav class="bg-gradient-to-tr from-red-950 to-red-800" x-data="{ isOpen: false }">
    <div class="mx-auto px-4 sm:px-6 lg:px-12">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}">
                        <img class="h-8 w-8" src="{{ asset('img/Logo-1.png') }}" alt="Logo">
                    </a>
                </div>
                
                {{-- DESKTOP MENU --}}
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-2">
                        @foreach ($navigationMenus as $menu)
                            @php
                                // Get submenus for this menu
                                $menuSubmenus = $allSubmenus->where('menuid', $menu->menuid);
                                
                                // Headers (parentid null, no slug)
                                $headers = $menuSubmenus->whereNull('parentid')->whereNull('slug');
                                
                                // Direct items (parentid null, has slug)
                                $directItems = $menuSubmenus->whereNull('parentid')
                                    ->whereNotNull('slug')
                                    ->whereIn('name', $userPermissions);
                            @endphp
                            
                            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                <button @click="open = !open" 
                                    class="text-red-200 hover:bg-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center transition-all duration-200"
                                    :class="{ 'bg-red-900 text-white': open }">
                                    {{ $menu->name }}
                                    <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" 
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-transition 
                                    class="absolute z-50 mt-2 w-64 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5">
                                    <div class="py-1">
                                        {{-- Render headers with children --}}
                                        @foreach ($headers as $header)
                                            @php
                                                $children = $menuSubmenus->where('parentid', $header->submenuid)
                                                    ->whereIn('name', $userPermissions);
                                            @endphp
                                            
                                            @if ($children->isNotEmpty())
                                                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                                    {{ $header->name }}
                                                </div>
                                                
                                                @foreach ($children as $child)
                                                    <a href="{{ $getRoute($menu->slug, $child->slug) }}" 
                                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 ml-2
                                                              {{ $isActive($child->slug) ? 'bg-red-50 text-red-700 font-medium' : '' }}">
                                                        {{ $child->name }}
                                                    </a>
                                                @endforeach
                                                
                                                @if (!$loop->last || $directItems->isNotEmpty())
                                                    <div class="my-1 h-px bg-gray-200"></div>
                                                @endif
                                            @endif
                                        @endforeach
                                        
                                        {{-- Render direct items with nested dropdown support --}}
                                        @foreach ($directItems as $item)
                                            @php
                                                // Check if this item has children
                                                $itemChildren = $menuSubmenus->where('parentid', $item->submenuid)
                                                    ->whereIn('name', $userPermissions);
                                                $hasChildren = $itemChildren->isNotEmpty();
                                            @endphp
                                            
                                            @if ($hasChildren)
                                                {{-- Item with dropdown --}}
                                                <div x-data="{ subOpen: false }" class="relative">
                                                    <button @click="subOpen = !subOpen" 
                                                        class="w-full flex items-center justify-between px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 rounded-md
                                                               {{ $isActive($item->slug) ? 'bg-red-50 text-red-700 font-medium' : '' }}">
                                                        <span>{{ $item->name }}</span>
                                                        <svg class="h-4 w-4 transform transition-transform" :class="{'rotate-90': subOpen}" 
                                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                                        </svg>
                                                    </button>
                                                    
                                                    <div x-show="subOpen" x-transition 
                                                        class="absolute top-0 left-full w-56 mt-0 ml-1 bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5" 
                                                        style="display: none;">
                                                        <div class="py-1">
                                                            {{-- Parent link --}}
                                                            <a href="{{ $getRoute($menu->slug, $item->slug) }}" 
                                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md
                                                                      {{ $isActive($item->slug) ? 'bg-red-50 text-red-700 font-medium' : '' }}">
                                                                {{ $item->name }}
                                                            </a>
                                                            <div class="my-1 h-px bg-gray-200"></div>
                                                            
                                                            {{-- Children --}}
                                                            @foreach ($itemChildren as $child)
                                                                <a href="{{ $getRoute($menu->slug, $child->slug) }}" 
                                                                   class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md
                                                                          {{ $isActive($child->slug) ? 'bg-red-50 text-red-700 font-medium' : '' }}">
                                                                    {{ $child->name }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Item without children --}}
                                                <a href="{{ $getRoute($menu->slug, $item->slug) }}" 
                                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md
                                                          {{ $isActive($item->slug) ? 'bg-red-50 text-red-700 font-medium' : '' }}">
                                                    {{ $item->name }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right side: Profile & Notifications --}}
            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <div class="mr-3 text-right">
                        <div class="text-sm font-medium leading-none text-white">{{ session('companycode') }}</div>
                        <div class="text-xs font-medium leading-none text-red-100 mt-1">{{ $compName }}</div>
                    </div>
                    
                    <a href="{{ route('notifications.index') }}" class="relative rounded-full p-1 text-red-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-red-800 transition-colors duration-200">
                        <span class="sr-only">View notifications</span>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span id="notification-dot" class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full hidden animate-pulse"></span>
                    </a>
                    
                    <div x-data="{ open: false }" @click.away="open = false" class="relative ml-3">
                        <button @click="open = !open" type="button" class="flex items-center text-red-200 hover:text-white transition-colors duration-200 group">
                            <img class="h-8 w-8 rounded-full ring-2 ring-red-900 group-hover:ring-white transition-all duration-200" 
                                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                                alt="{{ Auth::user()->name }}">
                            <div class="ml-3"><span class="text-sm font-medium">{{ Auth::user()->name }}</span></div>
                            <svg class="ml-2 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" 
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div x-show="open" x-transition 
                            class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-xl ring-1 ring-black ring-opacity-5" 
                            style="display: none;">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->email ?? Auth::user()->name }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3 transition-colors duration-150">
                                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                                    </svg>
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile menu button --}}
            <div class="-mr-2 flex md:hidden items-center gap-3">
                <a href="{{ route('notifications.index') }}" class="relative ml-auto shrink-0 rounded-full p-1 text-red-200 hover:text-white">
                    <span class="sr-only">View notifications</span>
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" 
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <span id="notification-dot-mobile" class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full hidden animate-pulse"></span>
                </a>
                <button type="button" @click="isOpen = !isOpen" 
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-red-200 hover:bg-red-800 hover:text-white">
                    <span class="sr-only">Open main menu</span>
                    <svg :class="{ 'hidden': isOpen, 'block': !isOpen }" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg :class="{ 'block': isOpen, 'hidden': !isOpen }" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="isOpen" class="md:hidden" id="mobile-menu" style="display: none;">
        <div class="px-2 pb-3 pt-2 space-y-1">
            @foreach ($navigationMenus as $menu)
                @php
                    $menuSubmenus = $allSubmenus->where('menuid', $menu->menuid);
                    $headers = $menuSubmenus->whereNull('parentid')->whereNull('slug');
                    $directItems = $menuSubmenus->whereNull('parentid')
                        ->whereNotNull('slug')
                        ->whereIn('name', $userPermissions);
                @endphp
                
                <div x-data="{ open: false }">
                    <button @click="open = !open" 
                        class="w-full text-left text-red-200 hover:bg-red-800 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center justify-between">
                        {{ $menu->name }}
                        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': open }" 
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <div x-show="open" x-transition class="mt-2 space-y-1 bg-red-950 rounded-md mx-2">
                        {{-- Headers with children --}}
                        @foreach ($headers as $header)
                            @php
                                $children = $menuSubmenus->where('parentid', $header->submenuid)
                                    ->whereIn('name', $userPermissions);
                            @endphp
                            
                            @if ($children->isNotEmpty())
                                <div class="px-4 py-2 text-xs font-semibold text-red-300 uppercase tracking-wider">
                                    {{ $header->name }}
                                </div>
                                
                                @foreach ($children as $child)
                                    <a href="{{ $getRoute($menu->slug, $child->slug) }}" 
                                       class="block pl-8 pr-3 py-2 text-sm text-red-100 hover:bg-red-800 hover:text-white rounded-md">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            @endif
                        @endforeach
                        
                        {{-- Direct items --}}
                        @foreach ($directItems as $item)
                            @php
                                $itemChildren = $menuSubmenus->where('parentid', $item->submenuid)
                                    ->whereIn('name', $userPermissions);
                                $hasChildren = $itemChildren->isNotEmpty();
                            @endphp
                            
                            @if ($hasChildren)
                                <div x-data="{ subOpen: false }" class="relative">
                                    <button @click="subOpen = !subOpen" 
                                        class="w-full flex items-center justify-between pl-6 pr-3 py-2 text-sm text-red-100 hover:bg-red-800 hover:text-white rounded-md">
                                        {{ $item->name }}
                                        <svg class="h-4 w-4 transform transition-transform" :class="{'rotate-180': subOpen}" 
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    
                                    <div x-show="subOpen" x-transition class="mt-1 space-y-1">
                                        <a href="{{ $getRoute($menu->slug, $item->slug) }}" 
                                           class="block pl-10 pr-3 py-1 text-xs text-red-200 hover:bg-red-800 hover:text-white rounded-md">
                                            {{ $item->name }}
                                        </a>
                                        @foreach ($itemChildren as $child)
                                            <a href="{{ $getRoute($menu->slug, $child->slug) }}" 
                                               class="block pl-12 pr-3 py-1 text-xs text-red-200 hover:bg-red-800 hover:text-white rounded-md">
                                                {{ $child->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $getRoute($menu->slug, $item->slug) }}" 
                                   class="block pl-6 pr-3 py-2 text-sm text-red-100 hover:bg-red-800 hover:text-white rounded-md">
                                    {{ $item->name }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Profile Section untuk Mobile --}}
        <div class="border-t border-red-800 pb-3 pt-4">
            <div class="flex items-center px-5">
                <div class="flex-shrink-0">
                    <img class="h-10 w-10 rounded-full ring-2 ring-red-800" 
                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" 
                        alt="{{ Auth::user()->name }}">
                </div>
                <div class="ml-3 space-y-1">
                    <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-red-200">{{ session('companycode') }}<span class="text-red-300"> - {{ $compName }}</span></div>
                </div>
            </div>
            <div class="mt-3 px-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" 
                        class="w-full text-left flex items-center gap-3 rounded-md px-3 py-2 text-base font-medium text-red-200 hover:bg-red-800 hover:text-white">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                        </svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>