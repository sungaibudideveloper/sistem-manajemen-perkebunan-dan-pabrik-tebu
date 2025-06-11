<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <div class="mx-4 pb-4">
        <form action="{{ route('master.username.setaccess', $user->userid) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="bg-white p-4 rounded-md shadow-md mb-4">
                <label class="block text-md">Username</label>
                <input
                    type="text"
                    name="usernm"
                    value="{{ $user->userid }}"
                    class="border rounded-md border-gray-300 p-2 w-auto text-sm focus:ring-0 focus:border-gray-300 bg-gray-100 text-gray-600"
                    readonly
                >
            </div>

            {{-- =========================================== --}}
            {{-- BAGIAN “MASTER” (DINAMIS DENGAN SAFETY-FALLBACK) --}}
            {{-- =========================================== --}}
            @php
                // Pastikan $submenu dan $subsubmenu adalah Collection, jika bukan, ubah menjadi Collection kosong
                $safeSubmenu    = $submenu instanceof \Illuminate\Support\Collection ? $submenu : collect();
                $safeSubsubmenu = $subsubmenu instanceof \Illuminate\Support\Collection ? $subsubmenu : collect();
            @endphp

            <div class="mb-4 bg-white p-4 rounded-md shadow-md">
                <div class="border-b mb-4">
                    <label class="block text-md mb-3 font-medium">Hak Akses (Master)</label>
                </div>

                <div class="grid lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1 gap-4">
                    <div>
                        {{-- Checkbox induk “Master” --}}
                        <label
                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center mb-2"
                        >
                            <input
                                type="checkbox"
                                id="master"
                                name="permissions[]"
                                value="Master"
                                {{ in_array('Master', old('permissions', json_decode($user->permissions ?? '[]', true))) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 master-child-checkbox"
                            >
                            <span class="ml-1">Master</span>
                            <button type="button" id="toggleMaster" class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                <svg
                                    id="masterToggleIcon"
                                    class="ml-1 h-4 w-4 transition-transform transform rotate-0"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </label>

                        {{-- Dropdown container untuk daftar Menu ? Submenu ? Subsubmenu --}}
                        <div id="masterDropdown" class="ml-6 mt-2 l-line hidden">
                            @foreach($menu ?? collect() as $m)
                                <div class="flex items-center mb-2 child-line">
                                    <label
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            id="menu{{ $m->menuid }}"
                                            name="permissions[]"
                                            value="{{ $m->name }}"
                                            {{ in_array($m->name, old('permissions', json_decode($user->permissions ?? '[]', true))) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 master-child-checkbox"
                                        >
                                        <span class="ml-1">{{ $m->name }}</span>
                                        <button type="button" id="toggleMenu{{ $m->menuid }}" class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                            <svg
                                                id="menu{{ $m->menuid }}ToggleIcon"
                                                class="ml-1 h-4 w-4 transition-transform transform rotate-0"
                                                xmlns="http://www.w3.org/2000/svg"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </label>

                                    {{-- Container Submenu --}}
                                    <div id="menu{{ $m->menuid }}Dropdown" class="ml-6 mt-2 l-line hidden">
                                        @php
                                            $filteredSub = $safeSubmenu->where('menuid', $m->menuid);
                                        @endphp

                                        @foreach($filteredSub ?? collect() as $sm)
                                            <div class="flex items-center mb-2 child-line">
                                                <label
                                                    class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        id="submenu{{ $sm->submenuid }}"
                                                        name="permissions[]"
                                                        value="{{ $sm->name }}"
                                                        {{ in_array($sm->name, old('permissions', json_decode($user->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 master-child-checkbox"
                                                    >
                                                    <span class="ml-1">{{ $sm->name }}</span>
                                                    <button type="button" id="toggleSubmenu{{ $sm->submenuid }}" class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                                        <svg
                                                            id="submenu{{ $sm->submenuid }}ToggleIcon"
                                                            class="ml-1 h-4 w-4 transition-transform transform rotate-0"
                                                            xmlns="http://www.w3.org/2000/svg"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor"
                                                        >
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                </label>

                                                {{-- Container Sub-Submenu --}}
                                                <div id="submenu{{ $sm->submenuid }}Dropdown" class="ml-6 mt-2 l-line hidden">
                                                    @php
                                                        $filteredSubsub = $safeSubsubmenu->where('submenuid', $sm->submenuid);
                                                    @endphp

                                                    @foreach($filteredSubsub ?? collect() as $ss)
                                                        <div class="flex items-center mb-2 child-line">
                                                            <label
                                                                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center"
                                                            >
                                                                <input
                                                                    type="checkbox"
                                                                    name="permissions[]"
                                                                    value="{{ $ss->name }}"
                                                                    {{ in_array($ss->name, old('permissions', json_decode($user->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 master-child-checkbox"
                                                                >
                                                                <span class="ml-1">{{ $ss->name }}</span>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                  <div>
                {{-- Input Data --}}
                <label class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center mb-2">
                    <input
                        type="checkbox"
                        id="inputData"
                        name="permissions[]"
                        value="Input Data"
                        {{ in_array('Input Data', old('permissions', json_decode($user->permissions ?? '[]', true))) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    >
                    <span class="ml-1">Input Data</span>
                    <button type="button" id="toggleInputData" class="text-gray-600 hover:text-blue-600 focus:outline-none">
                        <svg
                            id="inputDataToggleIcon"
                            class="ml-1 h-4 w-4 transition-transform transform"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </label>

                <div id="inputDataDropdown" class="ml-6 mt-2 l-line mb-4 hidden">
                    @php
                        $categories = [
                            [
                                'id' => 'agronomi',
                                'name' => 'Agronomi',
                                'submenus' => ['Create Agronomi', 'Hapus Agronomi', 'Edit Agronomi'],
                            ],
                            [
                                'id' => 'hpt',
                                'name' => 'HPT',
                                'submenus' => ['Create HPT', 'Hapus HPT', 'Edit HPT'],
                            ],
                        ];
                    @endphp

                    @foreach ($categories as $category)
                        <div class="items-center mb-2 child-line w-fit">
                            <label
                                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center"
                            >
                                <input
                                    type="checkbox"
                                    id="{{ $category['id'] }}"
                                    name="permissions[]"
                                    value="{{ $category['name'] }}"
                                    {{ in_array($category['name'], old('permissions', json_decode($user->permissions ?? '[]', true))) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 inputData-child-checkbox"
                                >
                                <span class="ml-1">{{ $category['name'] }}</span>
                                <button type="button" id="toggle{{ ucfirst($category['name']) }}" class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                    <svg
                                        id="{{ $category['id'] }}ToggleIcon"
                                        class="ml-1 h-4 w-4 transition-transform transform"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </label>
                        </div>

                        <div id="{{ $category['id'] }}Dropdown" class="ml-6 mt-2 l-line hidden">
                            @foreach ($category['submenus'] as $sub)
                                <div class="flex items-center mb-2 child-line">
                                    <label
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center"
                                    >
                                        <input
                                            type="checkbox"
                                            name="permissions[]"
                                            value="{{ $sub }}"
                                            {{ in_array($sub, old('permissions', json_decode($user->permissions ?? '[]', true))) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 inputData-child-checkbox"
                                        >
                                        <span class="ml-1">{{ $sub }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
            </div>
            {{-- =========================================== --}}
            {{-- AKHIR BAGIAN “MASTER” --}}
            {{-- =========================================== --}}

            {{-- =========================================== --}}
            {{-- BAGIAN TAMBAHAN (Input Data, Dashboard, Report, Process, dan menu terakhir) --}}
            {{-- =========================================== --}}
          

            <div class="mt-6 flex gap-2">
                <button type="submit"
                    class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-green-600"
                >
                    <svg class="w-6 h-6 text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 11.917 9.724 16.5 19 7.5" />
                    </svg>
                    <span>Save</span>
                </button>
                <a href="{{ route('master.username.index') }}"
                    class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-red-600"
                >
                    <svg class="w-6 h-6 text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18 17.94 6M18 18 6.06 6" />
                    </svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>

    {{-- =========================================== --}}
    {{-- SCRIPT: Sinkronisasi Master Checkbox dengan Semua Child nya --}}
    {{-- =========================================== --}}
    <script>
        const setupMasterCheckbox = (masterId, childClass) => {
            const master = document.getElementById(masterId);
            const children = document.querySelectorAll(`.${childClass}`);

            const updateMasterState = () => {
                master.checked = Array.from(children).some(cb => cb.checked);
            };

            // Jika master dicentang, centang semua child
            master.addEventListener('change', function() {
                children.forEach(cb => cb.checked = this.checked);
            });

            // Jika salah satu child dicentang, master otomatis tercentang
            children.forEach(cb => {
                cb.addEventListener('change', updateMasterState);
            });
        };

        setupMasterCheckbox('master', 'master-child-checkbox');
    </script>

    {{-- =========================================== --}}
    {{-- SCRIPT: Toggle Expand/Collapse untuk setiap Dropdown --}}
    {{-- =========================================== --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleItems = [
                // MASTER
                {
                    buttonId: 'toggleMaster',
                    dropdownId: 'masterDropdown',
                    iconId: 'masterToggleIcon'
                },
                // MENU ? SUBMENU ? SUBSUBMENU (otomatis di-generate oleh Blade)
                @foreach($menu ?? collect() as $m)
                    {
                        buttonId: 'toggleMenu{{ $m->menuid }}',
                        dropdownId: 'menu{{ $m->menuid }}Dropdown',
                        iconId: 'menu{{ $m->menuid }}ToggleIcon'
                    },
                    @foreach($safeSubmenu->where('menuid', $m->menuid) as $sm)
                        {
                            buttonId: 'toggleSubmenu{{ $sm->submenuid }}',
                            dropdownId: 'submenu{{ $sm->submenuid }}Dropdown',
                            iconId: 'submenu{{ $sm->submenuid }}ToggleIcon'
                        },
                    @endforeach
                @endforeach,

                // Input Data
                {
                    buttonId: 'toggleInputData',
                    dropdownId: 'inputDataDropdown',
                    iconId: 'inputDataToggleIcon'
                },
                @php
                    $categoriesInput = [
                        ['id' => 'agronomi'],
                        ['id' => 'hpt'],
                    ];
                @endphp
                @foreach($categoriesInput as $cat)
                    {
                        buttonId: 'toggle{{ ucfirst($cat['id']) }}',
                        dropdownId: '{{ $cat["id"] }}Dropdown',
                        iconId: '{{ $cat["id"] }}ToggleIcon'
                    },
                @endforeach

                // Dashboard
                , {
                    buttonId: 'toggleDashboard',
                    dropdownId: 'dashboardDropdown',
                    iconId: 'dashboardToggleIcon'
                },
                @php
                    $categoriesDashboard = [
                        ['id' => 'dashboardAgronomi'],
                        ['id' => 'dashboardHPT'],
                    ];
                @endphp
                @foreach($categoriesDashboard as $cat)
                    {
                        buttonId: 'toggle{{ ucfirst($cat['id']) }}',
                        dropdownId: '{{ $cat["id"] }}Dropdown',
                        iconId: '{{ $cat["id"] }}ToggleIcon'
                    },
                @endforeach

                // Report
                , {
                    buttonId: 'toggleReport',
                    dropdownId: 'reportDropdown',
                    iconId: 'reportToggleIcon'
                },
                @php
                    $categoriesReport = [
                        ['id' => 'reportAgronomi'],
                        ['id' => 'reportHPT'],
                    ];
                @endphp
                @foreach($categoriesReport as $cat)
                    {
                        buttonId: 'toggle{{ ucfirst($cat['id']) }}',
                        dropdownId: '{{ $cat["id"] }}Dropdown',
                        iconId: '{{ $cat["id"] }}ToggleIcon'
                    },
                @endforeach

                // Process
                , {
                    buttonId: 'toggleProcess',
                    dropdownId: 'processDropdown',
                    iconId: 'processToggleIcon'
                },
                @php
                    $categoriesProcess = [
                        ['id' => 'posting'],
                        ['id' => 'unposting'],
                    ];
                @endphp
                @foreach($categoriesProcess as $cat)
                    {
                        buttonId: 'toggle{{ ucfirst($cat['id']) }}',
                        dropdownId: '{{ $cat["id"] }}Dropdown',
                        iconId: '{{ $cat["id"] }}ToggleIcon'
                    },
                @endforeach
            ];

            toggleItems.forEach(({ buttonId, dropdownId, iconId }) => {
                const btn = document.getElementById(buttonId);
                const dropdownEl = document.getElementById(dropdownId);
                const iconEl = document.getElementById(iconId);
                let isOpen = false;

                if (btn && dropdownEl && iconEl) {
                    btn.addEventListener('click', function() {
                        dropdownEl.classList.toggle('hidden');
                        isOpen = !isOpen;

                        if (isOpen) {
                            iconEl.classList.add('rotate-180');
                            iconEl.classList.remove('rotate-0');
                        } else {
                            iconEl.classList.add('rotate-0');
                            iconEl.classList.remove('rotate-180');
                        }
                    });
                }
            });
        });
    </script>

</x-layout>
