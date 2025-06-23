<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <!-- Success Alert -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <!-- Error Alert -->
    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <div class="mx-4 pb-6">
        <form action="{{ route('masterdata.username.setaccess', $user->userid) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Username Field -->
            <div class="bg-white p-3 rounded-md shadow-sm">
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text"
                    name="usernm"
                    value="{{ $user->userid }}"
                    readonly
                    class="w-full border border-gray-300 px-3 py-1.5 rounded bg-gray-100 text-sm text-gray-600" />
            </div>

            <!-- Permissions Section -->
            <div class="bg-white p-4 rounded-md shadow-sm">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Hak Akses</h2>
                <hr class="border-gray-300 mb-4">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($menu as $m)
                    @php 
                        $sub = $submenu->where('menuid', $m->menuid);
                    @endphp
                    <div class="menu-item">
                        <div class="flex items-center justify-between cursor-pointer mb-2">
                            <label class="flex items-center text-sm font-medium text-gray-700">
                                <input type="checkbox"
                                    class="menu-checkbox w-4 h-4 mr-2"
                                    data-menuid="{{ $m->menuid }}"
                                    name="permissions[]"
                                    value="{{ $m->name }}"
                                    {{ in_array($m->name, json_decode($user->permissions ?? '[]', true)) ? 'checked' : '' }}>
                                {{ $m->name }}
                            </label>
                            @if($sub->isNotEmpty())
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 menu-toggle-icon"
                                data-menuid="{{ $m->menuid }}"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            @endif
                        </div>
                        
                        @if($sub->isNotEmpty())
                        <div class="ml-5 submenu-group submenu-menuid-{{ $m->menuid }} hidden space-y-2 border-l-2 border-gray-200 pl-3">
                            @php
                                // Get submenu headers (parentid null) untuk menu ini
                                $submenuHeaders = $sub->whereNull('parentid');
                                // Get direct submenus (yang punya parentid null dan slug)
                                $directSubmenus = $sub->whereNull('parentid')->whereNotNull('slug');
                            @endphp
                            
                            {{-- Render submenu yang punya header dulu --}}
                            @foreach($submenuHeaders->whereNull('slug') as $header)
                                {{-- Header/Kategori --}}
                                <div class="mb-3">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                        {{ $header->name }}
                                    </div>
                                    
                                    {{-- Actual submenus under this header --}}
                                    @php
                                        $actualSubmenus = $sub->where('parentid', $header->submenuid);
                                    @endphp
                                    
                                    <div class="space-y-1">
                                        @foreach($actualSubmenus as $sm)
                                            @php 
                                                $subsub = $subsubmenu->where('submenuid', $sm->submenuid);
                                                
                                                // Special handling for Dashboard submenu
                                                $permissionValue = $sm->name;
                                                if ($m->slug === 'dashboard') {
                                                    if ($sm->slug === 'agronomi-dashboard' || $sm->slug === 'agronomi') {
                                                        $permissionValue = 'Dashboard Agronomi';
                                                    } elseif ($sm->slug === 'hpt-dashboard' || $sm->slug === 'hpt') {
                                                        $permissionValue = 'Dashboard HPT';
                                                    }
                                                }
                                            @endphp
                                            <div class="submenu-item">
                                                <div class="flex items-center justify-between cursor-pointer">
                                                    <label class="flex items-center text-xs text-gray-600">
                                                        <input type="checkbox"
                                                            class="submenu-checkbox w-3.5 h-3.5 mr-2"
                                                            data-submenuid="{{ $sm->submenuid }}"
                                                            name="permissions[]"
                                                            value="{{ $permissionValue }}"
                                                            {{ in_array($permissionValue, json_decode($user->permissions ?? '[]', true)) ? 'checked' : '' }}>
                                                        {{ $sm->name }}
                                                    </label>
                                                    @if($subsub->isNotEmpty())
                                                    <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 submenu-toggle-icon"
                                                        data-submenuid="{{ $sm->submenuid }}"
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                    @endif
                                                </div>
                                                @if($subsub->isNotEmpty())
                                                <div class="ml-5 mt-2 subsubmenu-group subsubmenu-submenuid-{{ $sm->submenuid }} hidden space-y-1 border-l border-gray-100 pl-3">
                                                    @foreach($subsub as $ss)
                                                    <label class="flex items-center text-xs text-gray-500">
                                                        <input type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $ss->name }}"
                                                            class="w-3 h-3 mr-2"
                                                            {{ in_array($ss->name, json_decode($user->permissions ?? '[]', true)) ? 'checked' : '' }}>
                                                        {{ $ss->name }}
                                                    </label>
                                                    @endforeach
                                                </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- Render direct submenus (yang tidak punya header) --}}
                            @foreach($directSubmenus as $sm)
                                @php 
                                    $subsub = $subsubmenu->where('submenuid', $sm->submenuid);
                                    
                                    // Special handling for Dashboard submenu
                                    $permissionValue = $sm->name;
                                    if ($m->slug === 'dashboard') {
                                        if ($sm->slug === 'agronomi-dashboard' || $sm->slug === 'agronomi') {
                                            $permissionValue = 'Dashboard Agronomi';
                                        } elseif ($sm->slug === 'hpt-dashboard' || $sm->slug === 'hpt') {
                                            $permissionValue = 'Dashboard HPT';
                                        }
                                    }
                                @endphp
                                <div class="submenu-item">
                                    <div class="flex items-center justify-between cursor-pointer">
                                        <label class="flex items-center text-xs text-gray-600">
                                            <input type="checkbox"
                                                class="submenu-checkbox w-3.5 h-3.5 mr-2"
                                                data-submenuid="{{ $sm->submenuid }}"
                                                name="permissions[]"
                                                value="{{ $permissionValue }}"
                                                {{ in_array($permissionValue, json_decode($user->permissions ?? '[]', true)) ? 'checked' : '' }}>
                                            {{ $sm->name }}
                                        </label>
                                        @if($subsub->isNotEmpty())
                                        <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200 submenu-toggle-icon"
                                            data-submenuid="{{ $sm->submenuid }}"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        @endif
                                    </div>
                                    @if($subsub->isNotEmpty())
                                    <div class="ml-5 mt-2 subsubmenu-group subsubmenu-submenuid-{{ $sm->submenuid }} hidden space-y-1 border-l border-gray-100 pl-3">
                                        @foreach($subsub as $ss)
                                        <label class="flex items-center text-xs text-gray-500">
                                            <input type="checkbox"
                                                name="permissions[]"
                                                value="{{ $ss->name }}"
                                                class="w-3 h-3 mr-2"
                                                {{ in_array($ss->name, json_decode($user->permissions ?? '[]', true)) ? 'checked' : '' }}>
                                            {{ $ss->name }}
                                        </label>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    @endforeach

                    <!-- Custom Permissions Box -->
                    <div class="menu-item bg-gray-50 border border-gray-200 rounded-md p-3 mt-4">
                        <div class="mb-2 text-sm font-semibold text-gray-700">Akses Khusus</div>
                        @foreach(['Create Notifikasi', 'Hapus Notifikasi', 'Edit Notifikasi', 'Kepala Kebun', 'Admin'] as $custom)
                        <div class="flex items-center mb-2 last:mb-0">
                            <label class="flex items-center text-sm font-medium text-gray-700">
                                <input type="checkbox"
                                    name="permissions[]"
                                    value="{{ $custom }}"
                                    {{ in_array($custom, json_decode($user->permissions ?? '[]', true)) ? 'checked' : '' }}>
                                <span class="ml-2">{{ $custom }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 mt-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 text-sm rounded-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan</span>
                </button>

                <a href="{{ route('masterdata.username.index') }}"
                    class="inline-flex items-center gap-2 bg-gray-300 text-gray-800 px-4 py-2 text-sm rounded-md hover:bg-gray-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Batal</span>
                </a>
            </div>
        </form>
    </div>



    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function toggleSubmenu(icon, targetClass) {
                const submenuGroup = document.querySelector(`.${targetClass}`);
                if (submenuGroup) {
                    submenuGroup.classList.toggle("hidden");
                    icon.classList.toggle("rotate-180");
                }
            }
            document.querySelectorAll(".menu-toggle-icon").forEach(icon => {
                const menuId = icon.dataset.menuid;
                icon.addEventListener("click", () => {
                    toggleSubmenu(icon, `submenu-menuid-${menuId}`);
                });
            });
            document.querySelectorAll(".submenu-toggle-icon").forEach(icon => {
                const submenuId = icon.dataset.submenuid;
                icon.addEventListener("click", () => {
                    toggleSubmenu(icon, `subsubmenu-submenuid-${submenuId}`);
                });
            });
        });
    </script>

    <style>
        .rotate-180 {
            transform: rotate(180deg);
        }

        .menu-item {
            border-left: 3px solid transparent;
            padding-left: 8px;
            transition: border-color 0.2s ease;
        }

        .menu-item:hover {
            border-left-color: #3b82f6;
        }

        .submenu-item {
            padding: 4px 0;
        }

        .submenu-item:hover {
            background-color: #f8fafc;
            border-radius: 4px;
        }
    </style>
</x-layout>