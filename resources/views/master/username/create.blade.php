<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <div class="mx-4">
        @error('duplicate')
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400 w-fit">
                {{ $message }}</div>
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

            <div class="mb-4 bg-white rounded-md shadow-md p-4">
                <div class="border-b mb-4">
                    <label class="block text-md mb-3 font-medium">Hak Akses</label>
                </div>
                <div class="grid lg:grid-cols-3 md:grid-cols-2 sm:grid-cols-1 gap-4">
                    <div>
                        @php
                            $permissions = [
                                [
                                    'id' => 'master',
                                    'label' => 'Master',
                                    'submenus' => [
                                        [
                                            'id' => 'company',
                                            'label' => 'Company',
                                            'subitems' => ['Create Company', 'Edit Company'],
                                        ],
                                        [
                                            'id' => 'blok',
                                            'label' => 'Blok',
                                            'subitems' => ['Create Blok', 'Hapus Blok', 'Edit Blok'],
                                        ],
                                        [
                                            'id' => 'plotting',
                                            'label' => 'Plotting',
                                            'subitems' => ['Create Plotting', 'Hapus Plotting', 'Edit Plotting'],
                                        ],
                                        [
                                            'id' => 'mapping',
                                            'label' => 'Mapping',
                                            'subitems' => ['Create Mapping', 'Hapus Mapping', 'Edit Mapping'],
                                        ],
                                        [
                                            'id' => 'kelolaUser',
                                            'label' => 'Kelola User',
                                            'subitems' => ['Create User', 'Hapus User', 'Edit User', 'Hak Akses'],
                                        ],
                                    ],
                                ],
                            ];
                        @endphp

                        @foreach ($permissions as $permission)
                            <label
                                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center mb-2">
                                <input type="checkbox" id="{{ $permission['id'] }}" name="permissions[]"
                                    value="{{ $permission['label'] }}"
                                    {{ in_array($permission['label'], old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-1">{{ $permission['label'] }}</span>
                                <button type="button" id="toggle{{ ucfirst($permission['id']) }}"
                                    class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                    <svg id="{{ $permission['id'] }}ToggleIcon"
                                        class="ml-1 h-4 w-4 transition-transform transform"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </label>

                            <div id="{{ $permission['id'] }}Dropdown" class="ml-6 mt-2 l-line">
                                @foreach ($permission['submenus'] as $submenu)
                                    <div class="flex items-center mb-2 child-line">
                                        <label class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            <input type="checkbox" id="{{ $submenu['id'] }}" name="permissions[]"
                                                value="{{ $submenu['label'] }}"
                                                {{ in_array($submenu['label'], old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 master-child-checkbox">
                                            <span class="ml-1">{{ $submenu['label'] }}</span>
                                            <button type="button" id="toggle{{ ucfirst($submenu['id']) }}"
                                                class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                                <svg id="{{ $submenu['id'] }}ToggleIcon"
                                                    class="ml-1 h-4 w-4 transition-transform transform"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </label>
                                    </div>

                                    <div id="{{ $submenu['id'] }}Dropdown" class="ml-6 mt-2 l-line">
                                        @foreach ($submenu['subitems'] as $subitem)
                                            <div class="flex items-center mb-2 child-line">
                                                <label
                                                    class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ $subitem }}"
                                                        {{ in_array($subitem, old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 master-child-checkbox">
                                                    <span class="ml-1">{{ $subitem }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <label
                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center mb-2">
                            <input type="checkbox" id="inputData" name="permissions[]" value="Input Data"
                                {{ in_array('Input Data', old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-1">Input Data</span>
                            <button type="button" id="toggleInputData"
                                class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                <svg id="inputDataToggleIcon" class="ml-1 h-4 w-4 transition-transform transform"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </label>

                        <div id="inputDataDropdown" class="ml-6 mt-2 l-line mb-4">
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
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                        <input type="checkbox" id="{{ $category['id'] }}" name="permissions[]"
                                            value="{{ $category['name'] }}"
                                            {{ in_array($category['name'], old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 inputData-child-checkbox">
                                        <span class="ml-1">{{ $category['name'] }}</span>
                                        <button type="button" id="toggle{{ ucfirst($category['name']) }}"
                                            class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                            <svg id="{{ $category['id'] }}ToggleIcon"
                                                class="ml-1 h-4 w-4 transition-transform transform"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </label>
                                </div>

                                <div id="{{ $category['id'] }}Dropdown" class="ml-6 mt-2 l-line">
                                    @foreach ($category['submenus'] as $sub)
                                        <div class="flex items-center mb-2 child-line">
                                            <label
                                                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $sub }}"
                                                    {{ in_array($sub, old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 inputData-child-checkbox">
                                                <span class="ml-1">{{ $sub }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <label
                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center mb-2">
                            <input type="checkbox" id="dashboard" name="permissions[]" value="Dashboard"
                                {{ in_array('Dashboard', old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-1">Dashboard</span>
                            <button type="button" id="toggleDashboard"
                                class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                <svg id="dashboardToggleIcon" class="ml-1 h-4 w-4 transition-transform transform"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </label>

                        <div id="dashboardDropdown" class="ml-6 mt-2 l-line mb-4">
                            @php
                                $categories = [
                                    [
                                        'id' => 'dashboardAgronomi',
                                        'name' => 'Agronomi',
                                        'submenus' => ['Pivot Agronomi'],
                                    ],
                                    ['id' => 'dashboardHPT', 'name' => 'HPT', 'submenus' => ['Pivot HPT']],
                                ];
                            @endphp

                            @foreach ($categories as $category)
                                <div class="items-center mb-2 child-line w-fit">
                                    <label
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                        <input type="checkbox" id="{{ $category['id'] }}" name="permissions[]"
                                            value="Dashboard {{ $category['name'] }}"
                                            {{ in_array('Dashboard ' . $category['name'], old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 dashboard-child-checkbox">
                                        <span class="ml-1">{{ $category['name'] }}</span>
                                        <button type="button" id="toggle{{ ucfirst($category['id']) }}"
                                            class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                            <svg id="{{ $category['id'] }}ToggleIcon"
                                                class="ml-1 h-4 w-4 transition-transform transform"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </label>
                                </div>

                                <div id="{{ $category['id'] }}Dropdown" class="ml-6 mt-2 l-line">
                                    @foreach ($category['submenus'] as $sub)
                                        <div class="flex items-center mb-2 child-line">
                                            <label
                                                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $sub }}"
                                                    {{ in_array($sub, old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 dashboard-child-checkbox">
                                                <span class="ml-1">{{ $sub }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <label
                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center mb-2">
                            <input type="checkbox" id="report" name="permissions[]" value="Report"
                                {{ in_array('Report', old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-1">Report</span>
                            <button type="button" id="toggleReport"
                                class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                <svg id="reportToggleIcon" class="ml-1 h-4 w-4 transition-transform transform"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </label>

                        <div id="reportDropdown" class="ml-6 mt-2 l-line">
                            @php
                                $categories = [
                                    ['id' => 'reportAgronomi', 'name' => 'Agronomi', 'submenus' => ['Excel Agronomi']],
                                    ['id' => 'reportHPT', 'name' => 'HPT', 'submenus' => ['Excel HPT']],
                                ];
                            @endphp

                            @foreach ($categories as $category)
                                <div class="items-center mb-2 child-line w-fit">
                                    <label
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                        <input type="checkbox" id="{{ $category['id'] }}" name="permissions[]"
                                            value="Report {{ $category['name'] }}"
                                            {{ in_array('Report ' . $category['name'], old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 report-child-checkbox">
                                        <span class="ml-1">{{ $category['name'] }}</span>
                                        <button type="button" id="toggle{{ ucfirst($category['id']) }}"
                                            class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                            <svg id="{{ $category['id'] }}ToggleIcon"
                                                class="ml-1 h-4 w-4 transition-transform transform"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </label>
                                </div>

                                <div id="{{ $category['id'] }}Dropdown" class="ml-6 mt-2 l-line">
                                    @foreach ($category['submenus'] as $sub)
                                        <div class="flex items-center mb-2 child-line">
                                            <label
                                                class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                                <input type="checkbox" name="permissions[]"
                                                    value="{{ $sub }}"
                                                    {{ in_array($sub, old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 report-child-checkbox">
                                                <span class="ml-1">{{ $sub }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label
                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center mb-2">
                            <input type="checkbox" id="process" name="permissions[]" value="Process"
                                {{ in_array('Process', old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            <span class="ml-1">Process</span>
                            <button type="button" id="toggleProcess"
                                class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                <svg id="processToggleIcon" class="ml-1 h-4 w-4 transition-transform transform"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </label>

                        <div id="processDropdown" class="ml-6 mt-2 l-line">
                            @php
                                $categories = [
                                    ['id' => 'posting', 'name' => 'Posting', 'submenus' => ['Submit Posting']],
                                    ['id' => 'unposting', 'name' => 'Unposting', 'submenus' => ['Batal Posting']],
                                    ['id' => 'uploadGPX', 'name' => 'Upload GPX File'],
                                    ['id' => 'exportKML', 'name' => 'Export KML File'],
                                    ['id' => 'closing', 'name' => 'Closing'],
                                ];
                            @endphp

                            @foreach ($categories as $category)
                                <div class="items-center mb-2 child-line w-fit">
                                    <label
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                        <input type="checkbox" id="{{ $category['id'] }}" name="permissions[]"
                                            value="{{ $category['name'] }}"
                                            {{ in_array($category['name'], old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 process-child-checkbox">
                                        <span class="ml-1">{{ $category['name'] }}</span>
                                        @isset($category['submenus'])
                                            <button type="button" id="toggle{{ ucfirst($category['id']) }}"
                                                class="text-gray-600 hover:text-blue-600 focus:outline-none">
                                                <svg id="{{ $category['id'] }}ToggleIcon"
                                                    class="ml-1 h-4 w-4 transition-transform transform"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        @endisset
                                    </label>
                                </div>

                                <div id="{{ $category['id'] }}Dropdown" class="ml-6 mt-2 l-line">
                                    @isset($category['submenus'])
                                        @foreach ($category['submenus'] as $sub)
                                            <div class="flex items-center mb-2 child-line">
                                                <label
                                                    class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                                    <input type="checkbox" name="permissions[]"
                                                        value="{{ $sub }}"
                                                        {{ in_array($sub, old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 process-child-checkbox">
                                                    <span class="ml-1">{{ $sub }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    @endisset
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            @php
                                $menuu = [
                                    'Create Notifikasi',
                                    'Hapus Notifikasi',
                                    'Edit Notifikasi',
                                    'Kepala Kebun',
                                    'Admin',
                                ];
                            @endphp
                            @foreach ($menuu as $menu)
                                <div class="flex items-center mb-2">
                                    <label
                                        class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300 flex items-center">
                                        <input type="checkbox" name="permissions[]" value="{{ $menu }}"
                                            {{ in_array($menu, old('permissions', json_decode($username->permissions ?? '[]', true))) ? 'checked' : '' }}
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                        <span class="ml-1">{{ $menu }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
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
        const setupMasterCheckbox = (masterSelector, childSelector) => {
            const masterCheckbox = document.getElementById(masterSelector);
            const childCheckboxes = document.querySelectorAll(`.${childSelector}`);

            const updateMasterCheckbox = () => {
                masterCheckbox.checked = Array.from(childCheckboxes).some(cb => cb.checked);
            };

            masterCheckbox.addEventListener('change', function() {
                childCheckboxes.forEach(cb => cb.checked = this.checked);
            });

            childCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateMasterCheckbox);
            });
        };

        setupMasterCheckbox('master', 'master-child-checkbox');
        setupMasterCheckbox('inputData', 'inputData-child-checkbox');
        setupMasterCheckbox('dashboard', 'dashboard-child-checkbox');
        setupMasterCheckbox('report', 'report-child-checkbox');
        setupMasterCheckbox('process', 'process-child-checkbox');
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function setupCheckboxLogic(parentValue, childValues) {
                const parentCheckbox = document.querySelector(`input[value="${parentValue}"]`);
                const childCheckboxes = document.querySelectorAll(
                    childValues.map(value => `input[value="${value}"]`).join(", ")
                );

                childCheckboxes.forEach(childCheckbox => {
                    childCheckbox.addEventListener("change", function() {
                        if (childCheckbox.checked) {
                            parentCheckbox.checked = true;
                        }
                    });
                });

                parentCheckbox.addEventListener("change", function() {
                    if (!parentCheckbox.checked) {
                        childCheckboxes.forEach(childCheckbox => {
                            childCheckbox.checked = false;
                        });
                    }
                });
            }

            setupCheckboxLogic("Company", ["Hapus Company", "Edit Company", "Create Company"]);
            setupCheckboxLogic("blok", ["Hapus Blok", "Edit Blok", "Create Blok"]);
            setupCheckboxLogic("Plotting", ["Hapus Plotting", "Edit Plotting", "Create Plotting"]);
            setupCheckboxLogic("Mapping", ["Hapus Mapping", "Edit Mapping", "Create Mapping"]);
            setupCheckboxLogic("Kelola User", ["Hapus User", "Edit User", "Create User", "Hak Akses"]);
            setupCheckboxLogic("Agronomi", ["Hapus Agronomi", "Edit Agronomi", "Create Agronomi"]);
            setupCheckboxLogic("HPT", ["Hapus HPT", "Edit HPT", "Create HPT"]);
            setupCheckboxLogic("Dashboard HPT", ["Pivot HPT"]);
            setupCheckboxLogic("Dashboard Agronomi", ["Pivot Agronomi"]);
            setupCheckboxLogic("Report Agronomi", ["Excel Agronomi"]);
            setupCheckboxLogic("Report HPT", ["Excel HPT"]);
            setupCheckboxLogic("Process Posting", ["Submit Posting"]);
            setupCheckboxLogic("Process Unposting", ["Batal Posting"]);
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleElements = [{
                    toggle: 'toggleMaster',
                    dropdown: 'masterDropdown',
                    icon: 'masterToggleIcon'
                },
                {
                    toggle: 'toggleInputData',
                    dropdown: 'inputDataDropdown',
                    icon: 'inputDataToggleIcon'
                },
                {
                    toggle: 'toggleDashboard',
                    dropdown: 'dashboardDropdown',
                    icon: 'dashboardToggleIcon'
                },
                {
                    toggle: 'toggleReport',
                    dropdown: 'reportDropdown',
                    icon: 'reportToggleIcon'
                },
                {
                    toggle: 'toggleProcess',
                    dropdown: 'processDropdown',
                    icon: 'processToggleIcon'
                },
                {
                    toggle: 'toggleCompany',
                    dropdown: 'companyDropdown',
                    icon: 'companyToggleIcon'
                },
                {
                    toggle: 'toggleblok',
                    dropdown: 'blokDropdown',
                    icon: 'blockToggleIcon'
                },
                {
                    toggle: 'togglePlotting',
                    dropdown: 'plottingDropdown',
                    icon: 'plottingToggleIcon'
                },
                {
                    toggle: 'toggleMapping',
                    dropdown: 'mappingDropdown',
                    icon: 'mappingToggleIcon'
                },
                {
                    toggle: 'toggleKelolaUser',
                    dropdown: 'kelolaUserDropdown',
                    icon: 'kelolaUserToggleIcon'
                },
                {
                    toggle: 'toggleAgronomi',
                    dropdown: 'agronomiDropdown',
                    icon: 'agronomiToggleIcon'
                },
                {
                    toggle: 'toggleHPT',
                    dropdown: 'hptDropdown',
                    icon: 'hptToggleIcon'
                },
                {
                    toggle: 'toggleDashboardHPT',
                    dropdown: 'dashboardHPTDropdown',
                    icon: 'dashboardHPTToggleIcon'
                },
                {
                    toggle: 'toggleDashboardAgronomi',
                    dropdown: 'dashboardAgronomiDropdown',
                    icon: 'dashboardAgronomiToggleIcon'
                },
                {
                    toggle: 'toggleReportHPT',
                    dropdown: 'reportHPTDropdown',
                    icon: 'reportHPTToggleIcon'
                },
                {
                    toggle: 'toggleReportAgronomi',
                    dropdown: 'reportAgronomiDropdown',
                    icon: 'reportAgronomiToggleIcon'
                },
                {
                    toggle: 'togglePosting',
                    dropdown: 'postingDropdown',
                    icon: 'postingToggleIcon'
                },
                {
                    toggle: 'toggleUnposting',
                    dropdown: 'unpostingDropdown',
                    icon: 'unpostingToggleIcon'
                },
            ];

            toggleElements.forEach(({
                toggle,
                dropdown,
                icon
            }) => {
                const toggleButton = document.getElementById(toggle);
                const dropdownMenu = document.getElementById(dropdown);
                const toggleIcon = document.getElementById(icon);
                let isOpen = false;

                toggleButton.addEventListener('click', function() {
                    dropdownMenu.classList.toggle('hidden');
                    isOpen = !isOpen;

                    if (isOpen) {
                        toggleIcon.classList.add('rotate-180');
                        toggleIcon.classList.remove('rotate-0');
                    } else {
                        toggleIcon.classList.add('rotate-0');
                        toggleIcon.classList.remove('rotate-180');
                    }
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
