@php
$compName = $companyName ?? '';
$navComponent = $__env->getContainer()->make(\App\View\Components\Navbar::class);
@endphp

<nav class="bg-gradient-to-tr from-red-950 to-red-800" x-data="{ isOpen: false }">
    <div class="mx-auto px-4 sm:px-6 lg:px-12">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}">
                        <img class="h-8 w-8" src="{{ asset('img/Logo-1.png') }}" alt="Sungai Budi">
                    </a>
                </div>
                {{-- DESKTOP MENU --}}



                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-2">

                     



                        @foreach (($navigationMenus ?? []) as $menu)
                        @if ($hasPermission($getPermission($menu)))
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open" class="text-red-200 hover:bg-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center transition-all duration-200" :class="{ 'bg-red-1000 text-white': open || $isActive($menu) }">
                                {{ $menu->name }}
                                <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-transition class="absolute z-50 mt-2 w-56 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5" style="display: none;">
                                <div class="py-1">
                                    @foreach (($menu->submenus ?? []) as $topItem)
                                    <x-submenu-item :item="$topItem" :mainmenu="$menu" :component="$navComponent" />
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Bagian Profile & Notifikasi --}}
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
                            <img class="h-8 w-8 rounded-full ring-2 ring-red-900 group-hover:ring-white transition-all duration-200" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="{{ Auth::user()->name }}">
                            <div class="ml-3"><span class="text-sm font-medium">{{ Auth::user()->name }}</span></div>
                            <svg class="ml-2 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-xl ring-1 ring-black ring-opacity-5" style="display: none;">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Signed in as</p>
                                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->email ?? Auth::user()->name }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3 transition-colors duration-150">
                                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                                    </svg>
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Hamburger untuk Mobile --}}
            <div class="-mr-2 flex md:hidden items-center gap-3">
                <a href="{{ route('notifications.index') }}" class="relative ml-auto shrink-0 rounded-full p-1 text-red-200 hover:text-white"><span class="sr-only">View notifications</span><svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg><span id="notification-dot-mobile" class="absolute top-0 right-0 w-2.5 h-2.5 bg-red-500 rounded-full hidden animate-pulse"></span></a>
                <button type="button" @click="isOpen = !isOpen" class="relative inline-flex items-center justify-center rounded-md p-2 text-red-200 hover:bg-red-800 hover:text-white">
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

    {{-- ==================== MOBILE MENU SECTION ==================== --}}
    <div x-show="isOpen" class="md:hidden" id="mobile-menu" style="display: none;">
        {{-- Daftar Menu Dinamis Mobile --}}
        <div class="px-2 pb-3 pt-2 space-y-1">
            @foreach (($navigationMenus ?? []) as $menu)
            @if ($hasPermission($getPermission($menu)))
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full text-left text-red-200 hover:bg-red-800 hover:text-white px-3 py-2 rounded-md text-base font-medium flex items-center justify-between">
                    {{ $menu->name }}
                    <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-2 space-y-1 bg-red-950 rounded-md mx-2">
                    @foreach (($menu->submenus ?? []) as $topItem)
                    <x-mobile-submenu-item :item="$topItem" :mainmenu="$menu" :component="$navComponent" />
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
        </div>

        {{-- Profile Section untuk Mobile --}}
        <div class="border-t border-red-800 pb-3 pt-4">
            <div class="flex items-center px-5">
                <div class="flex-shrink-0"><img class="h-10 w-10 rounded-full ring-2 ring-red-800" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="{{ Auth::user()->name }}"></div>
                <div class="ml-3 space-y-1">
                    <div class="text-base font-medium text-white">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-red-200">{{ session('companycode') }}<span class="text-red-300"> - {{ $compName }}</span></div>
                </div>
            </div>
            <div class="mt-3 px-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-3 rounded-md px-3 py-2 text-base font-medium text-red-200 hover:bg-red-800 hover:text-white">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                        </svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</nav>