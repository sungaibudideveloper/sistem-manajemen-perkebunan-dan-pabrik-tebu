<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <div class="mx-4 pb-6">
        <form action="{{ route('master.username.setaccess', $user->userid) }}" method="POST" class="space-y-4">
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
                        @php $sub = $submenu->where('menuid', $m->menuid); @endphp
                        <div class="menu-item">
                            <!-- Main Menu -->
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

                            <!-- Submenu -->
                            @if($sub->isNotEmpty())
                                <div class="ml-5 submenu-group submenu-menuid-{{ $m->menuid }} hidden space-y-2 border-l-2 border-gray-200 pl-3">
                                    @foreach($sub as $sm)
                                        @php $subsub = $subsubmenu->where('submenuid', $sm->submenuid); @endphp
                                        <div class="submenu-item">
                                            <div class="flex items-center justify-between cursor-pointer">
                                                <label class="flex items-center text-xs text-gray-600">
                                                    <input type="checkbox" 
                                                           class="submenu-checkbox w-3.5 h-3.5 mr-2" 
                                                           data-submenuid="{{ $sm->submenuid }}"
                                                           name="permissions[]" 
                                                           value="{{ $sm->name }}"
                                                           {{ in_array($sm->name, json_decode($user->permissions ?? '[]', true)) ? 'checked' : '' }}>
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

                                            <!-- Sub-submenu -->
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
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 text-sm rounded-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan</span>
                </button>
                
                <a href="{{ route('master.username.index') }}"
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
        document.addEventListener("DOMContentLoaded", function () {
            // Toggle submenu visibility
            function toggleSubmenu(icon, targetClass) {
                const submenuGroup = document.querySelector(`.${targetClass}`);
                if (submenuGroup) {
                    submenuGroup.classList.toggle("hidden");
                    icon.classList.toggle("rotate-180");
                }
            }

            // Menu toggle functionality
            document.querySelectorAll(".menu-toggle-icon").forEach(icon => {
                const menuId = icon.dataset.menuid;
                icon.addEventListener("click", () => {
                    toggleSubmenu(icon, `submenu-menuid-${menuId}`);
                });
            });

            // Submenu toggle functionality
            document.querySelectorAll(".submenu-toggle-icon").forEach(icon => {
                const submenuId = icon.dataset.submenuid;
                icon.addEventListener("click", () => {
                    toggleSubmenu(icon, `subsubmenu-submenuid-${submenuId}`);
                });
            });

            // Optional: Auto-check parent when all children are checked
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Add your parent-child checkbox logic here if needed
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