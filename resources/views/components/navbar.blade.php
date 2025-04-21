<nav class="bg-gradient-to-tr from-red-950 to-red-800" x-data="{ isOpen: false, isMasterOpen: false }">
    <div class="mx-auto px-4 sm:px-6 lg:px-12">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="{{ route('home') }}">
                        <img class="h-8 w-8" src="{{ asset('img/Logo-1.png') }}" alt="Sungai Budi">
                    </a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-2">
                        @if (auth()->user() && in_array('Master', json_decode(auth()->user()->permissions ?? '[]')))
                            <div class="relative" x-data="{ isMasterOpen: false }">
                                <button @click="isMasterOpen = !isMasterOpen"
                                    :class="{
                                        'bg-red-1000 text-white': {{ request()->is('company') ||
                                            request()->is('blok') ||
                                            request()->is('plotting') ||
                                            request()->is('mapping') ||
                                            request()->is('herbisida') ||
                                            request()->is('herbisida-dosage') ||
                                            request()->is('username') ||
                                            request()->routeIs('master.username.create') ||
                                            request()->routeIs('master.username.access') ||
                                            request()->routeIs('master.username.edit') }},
                                        'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                            !(
                                                {{ request()->is('company') ||
                                                    request()->is('blok') ||
                                                    request()->is('plotting') ||
                                                    request()->is('mapping') ||
                                                    request()->is('herbisida') ||
                                                    request()->is('herbisida-dosage') ||
                                                    request()->is('username') ||
                                                    request()->routeIs('master.username.create') ||
                                                    request()->routeIs('master.username.access') ||
                                                    request()->routeIs('master.username.edit') }}
                                            )
                                    }"
                                    class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                    Master
                                    <!-- Arrow Icon -->
                                    <svg class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                                        :class="{ 'rotate-180': isMasterOpen, 'rotate-0': !isMasterOpen }"

                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <!-- Dropdown Content -->
                                <div x-show="isMasterOpen" @click.away="isMasterOpen = false" style="display: none;"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="absolute z-10 mt-2 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    @if (auth()->user() && in_array('Company', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('master.company.index') }}"
                                            :active="request()->is('company')">Company</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Blok', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('master.blok.index') }}"
                                            :active="request()->is('blok')">Blok</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Plotting', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('master.plotting.index') }}"
                                            :active="request()->is('plotting')">Plotting</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Mapping', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('master.mapping.index') }}"
                                            :active="request()->is('mapping')">Mapping</x-childnav-link>
                                    @endif
                                    
                                    {{-- @if (auth()->user() && in_array('Herbisida', json_decode(auth()->user()->permissions ?? '[]'))) --}}
                                        <x-childnav-link href="{{ route('master.herbisida.index') }}"
                                            :active="request()->is('herbisida')">Herbisida</x-childnav-link>
                                    {{-- @endif --}}

                                    {{-- @if (auth()->user() && in_array('Dosis Herbisida', json_decode(auth()->user()->permissions ?? '[]'))) --}}
                                        <x-childnav-link href="{{ route('masterdata.herbisida-dosage.index') }}"
                                            :active="request()->is('herbisida-dosage')">Dosis Herbisida</x-childnav-link>
                                    {{-- @endif --}}

                                    @if (auth()->user() && in_array('Kelola User', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('master.username.index') }}"
                                            :active="request()->is('username') ||
                                                request()->routeIs('master.username.create') ||
                                                request()->routeIs('master.username.access') ||
                                                request()->routeIs('master.username.edit')">Kelola
                                            User</x-childnav-link>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if (auth()->user() && in_array('Input Data', json_decode(auth()->user()->permissions ?? '[]')))
                            <div class="relative" x-data="{ isInputOpen: false }">
                                <button @click="isInputOpen = !isInputOpen"
                                    :class="{
                                        'bg-red-1000 text-white': {{ request()->is('agronomi') ||
                                            request()->is('hpt') ||
                                            request()->routeIs('input.agronomi.create') ||
                                            request()->routeIs('input.agronomi.edit') ||
                                            request()->routeIs('input.hpt.create') ||
                                            request()->routeIs('input.hpt.edit') }},
                                        'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                            !(
                                                {{ request()->is('agronomi') ||
                                                    request()->is('hpt') ||
                                                    request()->routeIs('input.agronomi.create') ||
                                                    request()->routeIs('input.agronomi.edit') ||
                                                    request()->routeIs('input.hpt.create') ||
                                                    request()->routeIs('input.hpt.edit') }}
                                            )
                                    }"
                                    class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                    Input Data
                                    <!-- Arrow Icon -->
                                    <svg :class="{ 'rotate-180': isInputOpen, 'rotate-0': !isInputOpen }"
                                        class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <!-- Dropdown Content -->
                                <div x-show="isInputOpen" @click.away="isInputOpen = false" style="display: none;"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="absolute z-10 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    @if (auth()->user() && in_array('Agronomi', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('input.agronomi.index') }}"
                                            :active="request()->is('agronomi') ||
                                                request()->routeIs('input.agronomi.create') ||
                                                request()->routeIs('input.agronomi.edit')">Agronomi</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('HPT', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('input.hpt.index') }}"
                                            :active="request()->is('hpt') ||
                                                request()->routeIs('input.hpt.create') ||
                                                request()->routeIs('input.hpt.edit')">HPT</x-childnav-link>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if (auth()->user() && in_array('Report', json_decode(auth()->user()->permissions ?? '[]')))
                            <div class="relative" x-data="{ isReportOpen: false }">
                                <button @click="isReportOpen = !isReportOpen"
                                    :class="{
                                        'bg-red-1000 text-white': {{ request()->is('agronomireport') || request()->is('hptreport') }},
                                        'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                            !(
                                                {{ request()->is('agronomireport') || request()->is('hptreport') }}
                                            )
                                    }"
                                    class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                    Report
                                    <!-- Arrow Icon -->
                                    <svg :class="{ 'rotate-180': isReportOpen, 'rotate-0': !isReportOpen }"
                                        class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <!-- Dropdown Content -->
                                <div x-show="isReportOpen" @click.away="isReportOpen = false" style="display: none;"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="absolute z-10 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    @if (auth()->user() && in_array('Report Agronomi', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('report.agronomi.index') }}"
                                            :active="request()->is('agronomireport')">Agronomi</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Report HPT', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('report.hpt.index') }}"
                                            :active="request()->is('hptreport')">HPT</x-childnav-link>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if (auth()->user() && in_array('Dashboard', json_decode(auth()->user()->permissions ?? '[]')))
                            <div class="relative" x-data="{ isDashboardOpen: false }">
                                <button @click="isDashboardOpen = !isDashboardOpen"
                                    :class="{
                                        'bg-red-1000 text-white': {{ request()->is('agronomidashboard') || request()->is('hptdashboard') }},
                                        'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                            !(
                                                {{ request()->is('agronomidashboard') || request()->is('hptdashboard') }}
                                            )
                                    }"
                                    class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                    Dashboard
                                    <!-- Arrow Icon -->
                                    <svg :class="{ 'rotate-180': isDashboardOpen, 'rotate-0': !isDashboardOpen }"
                                        class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <!-- Dropdown Content -->
                                <div x-show="isDashboardOpen" @click.away="isDashboardOpen = false"
                                    style="display: none;" x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="absolute z-10 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    @if (auth()->user() && in_array('Dashboard Agronomi', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('dashboard.agronomi') }}"
                                            :active="request()->is('agronomidashboard')">Agronomi</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Dashboard HPT', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('dashboard.hpt') }}"
                                            :active="request()->is('hptdashboard')">HPT</x-childnav-link>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if (auth()->user() && in_array('Process', json_decode(auth()->user()->permissions ?? '[]')))
                            <div class="relative" x-data="{ isProcessOpen: false }">
                                <button @click="isProcessOpen = !isProcessOpen"
                                    :class="{
                                        'bg-red-1000 text-white': {{ request()->is('closing') || request()->is('uploadgpx') || request()->is('exportkml') || request()->is('posting') || request()->is('unposting') }},
                                        'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                            !(
                                                {{ request()->is('closing') || request()->is('uploadgpx') || request()->is('exportkml') || request()->is('posting') || request()->is('unposting') }}
                                            )
                                    }"
                                    class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                    Process
                                    <!-- Arrow Icon -->
                                    <svg :class="{ 'rotate-180': isProcessOpen, 'rotate-0': !isProcessOpen }"
                                        class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <!-- Dropdown Content -->
                                <div x-show="isProcessOpen" @click.away="isProcessOpen = false"
                                    style="display: none;" x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 transform scale-95"
                                    x-transition:enter-end="opacity-100 transform scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 transform scale-100"
                                    x-transition:leave-end="opacity-0 transform scale-95"
                                    class="absolute z-10 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                    @if (auth()->user() && in_array('Posting', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('process.posting') }}"
                                            :active="request()->is('posting')">Posting</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Unposting', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('process.unposting') }}"
                                            :active="request()->is('unposting')">Unposting</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Upload GPX File', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('upload.gpx.view') }}"
                                            :active="request()->is('uploadgpx')">Upload
                                            GPX File</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Export KML File', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('export.kml.view') }}"
                                            :active="request()->is('exportkml')">Export
                                            KML File</x-childnav-link>
                                    @endif
                                    @if (auth()->user() && in_array('Closing', json_decode(auth()->user()->permissions ?? '[]')))
                                        <x-childnav-link href="{{ route('closing') }}" :active="request()->is('closing')"
                                            onclick="return confirm('Yakin closing periode sekarang?')">Closing</x-childnav-link>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    @php
                        $compName = DB::table('company')
                            ->where('companycode', '=', session('companycode'))
                            ->value('name');
                    @endphp
                    <div class="mr-3 text-right">
                        <div class="text-sm font-medium leading-none text-white opacity-70">
                            {{ session('companycode') }}
                        </div>
                        <div class="text-sm font-medium leading-none text-white opacity-50">{{ $compName }}</div>
                    </div>
                    <a href="{{ route('notifications.index') }}"
                        class="relative rounded-full p-1 text-red-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">View notifications</span>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span id="notification-dot"
                            class="absolute top-0.5 right-0.5 w-3 h-3 bg-red-500 rounded-full hidden text-[8px] text-white text-center"></span>
                    </a>
                    <!-- Profile dropdown -->
                    <div class="relative ml-3">
                        <div class="flex items-center text-red-200 hover:text-white hover:underline">
                            <img class="h-8 w-8 rounded-full"
                                src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                                alt="">
                            <div class="ml-3">
                                <span class="text-sm font-medium">
                                    {{ Auth::user()->name }}
                                </span>

                            </div>
                            <button type="button" @click="isOpen = !isOpen" class="..."
                                class="relative flex max-w-xs items-center rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                                id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="absolute -inset-1.5"></span>
                                <span class="sr-only">Open user menu</span>

                            </button>
                        </div>

                        <div x-show="isOpen" @click.away="isOpen = false" style="display: none;"
                            x-transition:enter="transition ease-out duration-100 transform"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75 transform"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                            role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button"
                            tabindex="-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                                    role="menuitem" tabindex="-1" id="user-menu-item-2">
                                    <svg class="w-4 h-4 dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                                    </svg>
                                    <span>Sign out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="-mr-2 flex md:hidden items-center gap-3">
                <a href="{{ route('notifications.index') }}"
                    class="relative ml-auto shrink-0 rounded-full p-1 text-red-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                    <span class="absolute -inset-1.5"></span>
                    <span class="sr-only">View notifications</span>
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                    <span id="notification-dot-mobile"
                        class="absolute top-0.5 right-0.5 w-3 h-3 bg-red-500 rounded-full hidden text-[8px] text-white text-center"></span>
                </a>
                <!-- Mobile menu button -->
                <button type="button" @click="isOpen = !isOpen"
                    class="relative inline-flex items-center justify-center rounded-md p-2 text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                    aria-controls="mobile-menu" aria-expanded="false">
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">Open main menu</span>
                    <!-- Menu open: "hidden", Menu closed: "block" -->
                    <svg :class="{ 'hidden': isOpen, blok: !isOpen }" class="block h-6 w-6" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                        data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <!-- Menu open: "block", Menu closed: "hidden" -->
                    <svg :class="{ blok: isOpen, 'hidden': !isOpen }" class="hidden h-6 w-6" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                        data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div x-show="isOpen" class="md:hidden" id="mobile-menu">
        <div class="flex px-2 pb-3 pt-2 flex-wrap">

            @if (auth()->user() && in_array('Master', json_decode(auth()->user()->permissions ?? '[]')))
                <div class="relative" x-data="{ isMasterOpen: false }">
                    <button @click="isMasterOpen = !isMasterOpen; isMasterActive = !isMasterActive"
                        :class="{
                            'bg-red-1000 text-white': {{ request()->is('company') ||
                                request()->is('blok') ||
                                request()->is('plotting') ||
                                request()->is('mapping') ||
                                request()->is('herbisida') ||
                                request()->is('herbisida-dosage') ||
                                request()->is('username') ||
                                request()->routeIs('master.username.create') ||
                                request()->routeIs('master.username.access') ||
                                request()->routeIs('master.username.edit') }},
                            'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                !(
                                    {{ request()->is('company') ||
                                        request()->is('blok') ||
                                        request()->is('plotting') ||
                                        request()->is('mapping') ||
                                        request()->is('herbisida') ||
                                        request()->is('herbisida-dosage') ||
                                        request()->is('username') ||
                                        request()->routeIs('master.username.create') ||
                                        request()->routeIs('master.username.access') ||
                                        request()->routeIs('master.username.edit') }}
                                )
                        }"
                        class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                        Master
                        <!-- Arrow Icon -->
                        <svg :class="{ 'rotate-180': isMasterOpen, 'rotate-0': !isMasterOpen }"
                            class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <!-- Dropdown Content -->
                    <div x-show="isMasterOpen" @click.away="isMasterOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute z-10 mt-2 w-36 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        @if (auth()->user() && in_array('Company', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('master.company.index') }}"
                                :active="request()->is('company')">Company</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Blok', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('master.blok.index') }}"
                                :active="request()->is('blok')">Blok</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Plotting', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('master.plotting.index') }}"
                                :active="request()->is('plotting')">Plotting</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Mapping', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('master.mapping.index') }}"
                                :active="request()->is('mapping')">Mapping</x-childnav-link>
                        @endif

                        <x-childnav-link href="{{ route('master.herbisida.index') }}"
                                :active="request()->is('herbisida')">Herbisida</x-childnav-link>

                        <x-childnav-link href="{{ route('masterdata.herbisida-dosage.index') }}"
                                :active="request()->is('herbisida-dosage')">Dosis Herbisida</x-childnav-link>

                        @if (auth()->user() && in_array('Kelola User', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('master.username.index') }}" :active="request()->is('username') ||
                                request()->routeIs('master.username.create') ||
                                request()->routeIs('master.username.access') ||
                                request()->routeIs('master.username.edit')">Kelola
                                User</x-childnav-link>
                        @endif
                    </div>
                </div>
            @endif
            @if (auth()->user() && in_array('Input Data', json_decode(auth()->user()->permissions ?? '[]')))
                <div class="relative" x-data="{ isInputDataOpen: false }">
                    <button @click="isInputDataOpen = !isInputDataOpen; isInputDataActive = !isInputDataActive"
                        :class="{
                            'bg-red-1000 text-white': {{ request()->is('agronomi') ||
                                request()->is('hpt') ||
                                request()->routeIs('input.agronomi.create') ||
                                request()->routeIs('input.agronomi.edit') ||
                                request()->routeIs('input.hpt.create') ||
                                request()->routeIs('input.hpt.edit') }},
                            'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                !(
                                    {{ request()->is('agronomi') ||
                                        request()->is('hpt') ||
                                        request()->routeIs('input.agronomi.create') ||
                                        request()->routeIs('input.agronomi.edit') ||
                                        request()->routeIs('input.hpt.create') ||
                                        request()->routeIs('input.hpt.edit') }}
                                )
                        }"
                        class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                        Input Data
                        <!-- Arrow Icon -->
                        <svg :class="{ 'rotate-180': isInputDataOpen, 'rotate-0': !isInputDataOpen }"
                            class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <!-- Dropdown Content -->
                    <div x-show="isInputDataOpen" @click.away="isInputDataOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute z-10 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        @if (auth()->user() && in_array('Agronomi', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('input.agronomi.index') }}"
                                :active="request()->is('agronomi') ||
                                    request()->routeIs('input.agronomi.create') ||
                                    request()->routeIs('input.agronomi.edit')">Agronomi</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('HPT', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('input.hpt.index') }}"
                                :active="request()->is('hpt') ||
                                    request()->routeIs('input.hpt.create') ||
                                    request()->routeIs('input.hpt.edit')">HPT</x-childnav-link>
                        @endif
                    </div>
                </div>
            @endif
            @if (auth()->user() && in_array('Report', json_decode(auth()->user()->permissions ?? '[]')))
                <div class="relative" x-data="{ isReportOpen: false }">
                    <button @click="isReportOpen = !isReportOpen"
                        :class="{
                            'bg-red-1000 text-white': {{ request()->is('agronomireport') || request()->is('hptreport') }},
                            'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                !(
                                    {{ request()->is('agronomireport') || request()->is('hptreport') }}
                                )
                        }"
                        class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                        Report
                        <!-- Arrow Icon -->
                        <svg :class="{ 'rotate-180': isReportOpen, 'rotate-0': !isReportOpen }"
                            class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <!-- Dropdown Content -->
                    <div x-show="isReportOpen" @click.away="isReportOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute z-10 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        @if (auth()->user() && in_array('Report Agronomi', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('report.agronomi.index') }}"
                                :active="request()->is('agronomireport')">Agronomi</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Report HPT', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('report.hpt.index') }}"
                                :active="request()->is('hptreport')">HPT</x-childnav-link>
                        @endif
                    </div>
                </div>
            @endif

            @if (auth()->user() && in_array('Dashboard', json_decode(auth()->user()->permissions ?? '[]')))
                <div class="relative" x-data="{ isDashboardOpen: false }">
                    <button @click="isDashboardOpen = !isDashboardOpen"
                        :class="{
                            'bg-red-1000 text-white': {{ request()->is('agronomidashboard') || request()->is('hptdashboard') }},
                            'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                !(
                                    {{ request()->is('agronomidashboard') || request()->is('hptdashboard') }}
                                )
                        }"
                        class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                        Dashboard
                        <!-- Arrow Icon -->
                        <svg :class="{ 'rotate-180': isDashboardOpen, 'rotate-0': !isDashboardOpen }"
                            class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <!-- Dropdown Content -->
                    <div x-show="isDashboardOpen" @click.away="isDashboardOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute z-10 mt-2 w-32 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        @if (auth()->user() && in_array('Dashboard Agronomi', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('dashboard.agronomi') }}"
                                :active="request()->is('agronomidashboard')">Agronomi</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Dashboard HPT', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('dashboard.hpt') }}"
                                :active="request()->is('hptdashboard')">HPT</x-childnav-link>
                        @endif
                    </div>
                </div>
            @endif
            @if (auth()->user() && in_array('Process', json_decode(auth()->user()->permissions ?? '[]')))
                <div class="relative" x-data="{ isProcessOpen: false }">
                    <button @click="isProcessOpen = !isProcessOpen"
                        :class="{
                            'bg-red-1000 text-white': {{ request()->is('closing') || request()->is('uploadgpx') || request()->is('exportkml') || request()->is('posting') || request()->is('unposting') }},
                            'text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white':
                                !(
                                    {{ request()->is('closing') || request()->is('uploadgpx') || request()->is('exportkml') || request()->is('posting') || request()->is('unposting') }}
                                )
                        }"
                        class="text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white px-3 py-2 rounded-md text-sm font-medium flex items-center">
                        Process
                        <!-- Arrow Icon -->
                        <svg :class="{ 'rotate-180': isProcessOpen, 'rotate-0': !isProcessOpen }"
                            class="ml-1 h-4 w-4 -mr-1 transition-transform transform"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <!-- Dropdown Content -->
                    <div x-show="isProcessOpen" @click.away="isProcessOpen = false" style="display: none;"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95"
                        class="absolute right-0 z-10 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        @if (auth()->user() && in_array('Posting', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('process.posting') }}"
                                :active="request()->is('posting')">Posting</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Unposting', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('process.unposting') }}"
                                :active="request()->is('unposting')">Unposting</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Upload GPX File', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('upload.gpx.view') }}" :active="request()->is('uploadgpx')">Upload
                                GPX File</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Export KML File', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('export.kml.view') }}" :active="request()->is('exportkml')">Export
                                KML File</x-childnav-link>
                        @endif
                        @if (auth()->user() && in_array('Closing', json_decode(auth()->user()->permissions ?? '[]')))
                            <x-childnav-link href="{{ route('closing') }}" :active="request()->is('closing')"
                                onclick="return confirm('Yakin closing periode sekarang?')">Closing</x-childnav-link>
                        @endif

                    </div>
                </div>
            @endif

        </div>
        <div class="border-t border-red-900 pb-3 pt-4">
            <div class="flex items-center px-5">
                <div class="flex-shrink-0">
                    <img class="h-10 w-10 rounded-full"
                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                        alt="">
                </div>
                <div class="ml-3 space-y-2">
                    <div class="text-base font-medium leading-none text-white">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium leading-none text-white opacity-70">
                        {{ session('companycode') }}
                        <span class="text-white opacity-50">
                            ( {{ $compName }} )
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1 px-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left flex items-center gap-2 rounded-md px-3 py-2 text-base font-medium text-red-200 hover:from-red-900 hover:bg-gradient-to-b hover:to-red-800 hover:text-white"
                        role="menuitem" tabindex="-1" id="user-menu-item-2">
                        <svg class="w-4 h-4 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="3"
                                d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                        </svg>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
