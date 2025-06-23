<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
        <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
        <x-slot:nav>{{ $nav }}</x-slot:nav>
        <x-slot:navnav>{{ $title }}</x-slot:navnav>

        <div class="mx-4">
            @error('duplicate')
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400 w-fit">
                {{ $message }}
            </div>
            @enderror

            <form action="{{ route('masterdata.username.handle') }}" method="POST">
                @csrf
                <div class="bg-white p-4 rounded-md shadow-md mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="mb-4">
                            <label class="block text-md">Username</label>
                            <input type="text" name="usernm" autocomplete="off" maxlength="50" value="{{ old('usernm') }}"
                                class="rounded-md p-2 w-full
                            {{ $errors->has('usernm') ? 'border-red-600 focus:ring-red-600 focus:border-red-600' : 'border-gray-300 focus:ring-blue-600 focus:border-blue-600' }}"
                                required>
                            @error('usernm')
                            <div class="text-red-600">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-md">Nama</label>
                            <input type="text" name="name" value="{{ old('name') }}" autocomplete="off" maxlength="15"
                                class="border rounded-md border-gray-300 p-2 w-full" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-md">Password</label>
                            <input type="text" name="password" class="border rounded-md border-gray-300 p-2 w-full"
                                autocomplete="off" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-md">Kode Company</label>
                            <div class="relative">
                                <button type="button"
                                    class="dropdown-button border rounded-md border-gray-300 bg-white p-2 w-full text-left flex justify-between focus:outline focus:outline-1 focus:outline-blue-600 focus:border-blue-600">
                                    <span class="text-gray-500">--Pilih Company--</span>
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="m19 9-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    class="mt-1 dropdown-menu absolute hidden border border-gray-300 bg-white rounded-md w-full max-h-60 overflow-auto z-10 shadow-sm">
                                    @php
                                    $oldKdComp = is_array(old('companycode'))
                                    ? old('companycode')
                                    : explode(',', old('companycode', ''));
                                    @endphp
                                    @foreach ($company as $comp)
                                    <label class="flex items-center gap-x-2 px-4 py-2 cursor-pointer hover:bg-gray-200">
                                        <input type="checkbox" name="companycode[]" value="{{ $comp->companycode }}"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                            {{ in_array($comp->companycode, $oldKdComp) ? 'checked' : '' }}>
                                        {{ $comp->companycode }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
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
                                        {{ in_array($m->name, old('permissions', [])) ? 'checked' : '' }}>
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
                                $submenuHeaders = $sub->whereNull('parentid');
                                $directSubmenus = $sub->whereNull('parentid')->whereNotNull('slug');
                                @endphp

                                @foreach($submenuHeaders->whereNull('slug') as $header)
                                <div class="mb-3">
                                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                        {{ $header->name }}
                                    </div>

                                    @php
                                    $actualSubmenus = $sub->where('parentid', $header->submenuid);
                                    @endphp

                                    <div class="space-y-1">
                                        @foreach($actualSubmenus as $sm)
                                        @php
                                        $subsub = $subsubmenu->where('submenuid', $sm->submenuid);

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
                                                        {{ in_array($permissionValue, old('permissions', [])) ? 'checked' : '' }}>
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
                                                        {{ in_array($ss->name, old('permissions', [])) ? 'checked' : '' }}>
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

                                @foreach($directSubmenus as $sm)
                                @php
                                $subsub = $subsubmenu->where('submenuid', $sm->submenuid);

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
                                                {{ in_array($permissionValue, old('permissions', [])) ? 'checked' : '' }}>
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
                                                {{ in_array($ss->name, old('permissions', [])) ? 'checked' : '' }}>
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

                        <div class="menu-item bg-gray-50 border border-gray-200 rounded-md p-3 mt-4">
                            <div class="mb-2 text-sm font-semibold text-gray-700">Akses Khusus</div>
                            @foreach(['Create Notifikasi', 'Hapus Notifikasi', 'Edit Notifikasi', 'Kepala Kebun', 'Admin'] as $custom)
                            <div class="flex items-center mb-2 last:mb-0">
                                <label class="flex items-center text-sm font-medium text-gray-700">
                                    <input type="checkbox"
                                        name="permissions[]"
                                        value="{{ $custom }}"
                                        {{ in_array($custom, old('permissions', [])) ? 'checked' : '' }}>
                                    <span class="ml-2">{{ $custom }}</span>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button type="submit"
                        class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-green-600">
                        <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 11.917 9.724 16.5 19 7.5" />
                        </svg>
                        <span>Save</span>
                    </button>
                    <a href="{{ route('masterdata.username.index') }}"
                        class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-red-600">
                        <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>

        <style>
            select:invalid {
                color: gray;
            }
        </style>

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
            .l-line {
                position: relative;
                padding-left: 8px;
            }

            .l-line::before {
                content: '';
                position: absolute;
                top: 0;
                left: -10px;
                width: 2px;
                height: 100%;
                background-color: #e2e2e2;
            }

            .child-line {
                position: relative;
                padding-left: 0px;
            }

            .child-line::before {
                content: '';
                position: absolute;
                top: 50%;
                left: -18px;
                width: 18px;
                height: 2px;
                background-color: #e2e2e2;
            }

            .dropdown-menu {
                display: none;
            }

            .dropdown-menu.visible {
                display: block;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const dropdownButton = document.querySelector('.dropdown-button');
                const dropdownMenu = document.querySelector('.dropdown-menu');
                const checkboxes = document.querySelectorAll('.dropdown-menu input[type="checkbox"]');

                dropdownButton.addEventListener('click', function() {
                    dropdownMenu.classList.toggle('visible');
                });

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const selected = Array.from(checkboxes)
                            .filter(cb => cb.checked)
                            .map(cb => cb.parentNode.textContent.trim());

                        dropdownButton.textContent = selected.length > 0 ?
                            selected.join(', ') :
                            '--Pilih Company--';
                    });
                });

                document.addEventListener('click', function(e) {
                    if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.remove('visible');
                    }
                });
            });
        </script>

</x-layout>