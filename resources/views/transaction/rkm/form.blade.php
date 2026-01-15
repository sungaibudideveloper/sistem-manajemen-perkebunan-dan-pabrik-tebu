<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    @php
        $isEdit = isset($header);
    @endphp

    <style>
        /* Custom Searchable Dropdown */
        .custom-dropdown {
            position: relative;
        }

        .custom-dropdown-trigger {
            cursor: pointer;
        }

        .custom-dropdown-menu {
            position: absolute;
            top: 100% !important;
            bottom: auto !important;
            left: 0;
            right: 0;
            z-index: 50;
            margin-top: 0.25rem;
            max-height: 300px;
            overflow-y: auto;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            display: none;
        }

        .custom-dropdown-menu.active {
            display: block;
        }

        .custom-dropdown-search {
            position: sticky;
            top: 0;
            background: white;
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            z-index: 1;
        }

        .custom-dropdown-item {
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: background-color 0.15s;
            border-bottom: 1px solid #f3f4f6;
        }

        .custom-dropdown-item:last-child {
            border-bottom: none;
        }

        .custom-dropdown-item:hover {
            background-color: #f3f4f6;
        }

        .custom-dropdown-item-code {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.875rem;
        }

        .custom-dropdown-item-name {
            color: #6b7280;
            font-size: 0.813rem;
            margin-top: 0.125rem;
        }

        .custom-dropdown-no-results {
            padding: 2rem 1rem;
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
        }

        /* Scrollbar styling for dropdown */
        .custom-dropdown-menu::-webkit-scrollbar {
            width: 8px;
        }

        .custom-dropdown-menu::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 0 0.5rem 0.5rem 0;
        }

        .custom-dropdown-menu::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-dropdown-menu::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    @error('duplicate')
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400 w-full md:w-fit">
            {{ $message }}
        </div>
    @enderror

    <form action="{{ $url }}" method="POST">
        @csrf
        @method($method)

        {{-- ======== HEADER FORM ======== --}}
        <div class="mx-2 md:mx-4 p-4 md:p-6 bg-white rounded-lg shadow-lg border border-gray-100">
            <!-- Action Buttons - Mobile: Stack, Desktop: Right aligned -->
            <div class="mb-6 flex flex-col-reverse md:flex-row md:justify-between gap-4">
                <!-- Form Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4 flex-1">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">No. RKM</label>
                        <input type="text" name="rkmno"
                            class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2.5 w-full text-sm bg-gray-50"
                            autocomplete="off" maxlength="4"
                            value="{{ old('rkmno', $rkmno ?? ($header->rkmno ?? '')) }}" readonly>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-gray-700">Tanggal RKM</label>
                        <input type="date" name="rkmdate"
                            class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2.5 w-full text-sm bg-gray-50"
                            autocomplete="off" value="{{ old('rkmdate', $selectedDate ?? ($header->rkmdate ?? '')) }}"
                            readonly>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex flex-row gap-2 md:items-start">
                    <a href="{{ route('transaction.rencana-kerja-mingguan.index') }}"
                        class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-lg shadow-md hover:bg-red-700 transition-all duration-200 font-medium text-sm">
                        <svg class="w-4 h-4 md:w-5 md:h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span>Cancel</span>
                    </a>
                    <button type="submit"
                        class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-green-600 text-white px-4 py-2.5 rounded-lg shadow-md hover:bg-green-700 transition-all duration-200 font-medium text-sm">
                        <svg class="w-4 h-4 md:w-5 md:h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 11.917 9.724 16.5 19 7.5" />
                        </svg>
                        <span>{{ $buttonSubmit }}</span>
                    </button>
                </div>
            </div>

            <!-- Main Form Fields -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Tanggal Mulai <span
                            class="text-red-600">*</span></label>
                    <input type="date" name="startdate" id="startdate"
                        value="{{ old('startdate', $header->startdate ?? '') }}"
                        class="border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">Tanggal Selesai</label>
                    <input type="date" name="enddate" id="enddate"
                        value="{{ old('enddate', $header->enddate ?? '') }}"
                        class="border rounded-lg cursor-not-allowed focus:ring-0 focus:border-gray-300 border-gray-300 p-2.5 w-full text-sm bg-gray-50"
                        readonly>
                </div>
                <div>
                    <label class="block mb-2 text-sm font-semibold text-gray-700">
                        Aktivitas <span class="text-red-600">*</span>
                    </label>
                    <div class="custom-dropdown">
                        <input type="hidden" name="activitycode" id="activitycode"
                            value="{{ old('activitycode', $header->activitycode ?? '') }}" required>
                        <div
                            class="custom-dropdown-trigger border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between">
                            <span id="selected-activity" class="text-gray-400">-- Pilih Aktivitas --</span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform duration-200" id="dropdown-icon"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                        <div class="custom-dropdown-menu">
                            <div class="custom-dropdown-search">
                                <div class="relative">
                                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <input type="text" id="activity-search" placeholder="Cari aktivitas..."
                                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            <div id="activity-list">
                                @foreach ($activity as $act)
                                    <div class="custom-dropdown-item" data-code="{{ $act->activitycode }}"
                                        data-name="{{ $act->activityname }}">
                                        <div class="custom-dropdown-item-code">{{ $act->activitycode }}</div>
                                        <div class="custom-dropdown-item-name">{{ $act->activityname }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div id="no-results" class="custom-dropdown-no-results" style="display: none;">
                                Tidak ada hasil yang ditemukan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======== DETAIL LIST ======== --}}
        <div class="mx-2 md:mx-4 p-4 md:p-6 bg-white rounded-lg shadow-lg border border-gray-100 mt-4">
            <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-gray-200">
                <h2 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    List Aktivitas
                </h2>
                <button type="button" id="addRow"
                    class="flex items-center gap-2 bg-blue-600 text-white px-3 py-2 rounded-lg shadow-md hover:bg-blue-700 transition-all duration-200 text-sm font-medium">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4.243a1 1 0 1 0-2 0V11H7.757a1 1 0 1 0 0 2H11v3.243a1 1 0 1 0 2 0V13h3.243a1 1 0 1 0 0-2H13V7.757Z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="hidden sm:inline">Tambah</span>
                </button>
            </div>

            @php
                $lists = old('lists', $isEdit ? $header->lists : [[]]);
            @endphp

            <div id="input-container" class="space-y-4">
                @foreach ($lists as $index => $list)
                    <div
                        class="input-row bg-gray-50 border-2 border-gray-200 rounded-lg p-3 md:p-4 hover:border-blue-300 transition-all duration-200">

                        <div class="md:flex space-y-3 md:space-y-0 items-center gap-3">
                            <div class="md:border-none border-b border-gray-300">
                                <div class="pb-3 md:pb-0">
                                    <div
                                        class="flex items-center justify-center md:w-12 md:h-12 w-8 h-8 bg-blue-600 md:text-base text-xs text-white md:rounded-xl rounded-full font-bold number-count">
                                        {{ $index + 1 }}</div>
                                </div>
                            </div>

                            <div class="flex-1">
                                <label class="block mb-1.5 text-xs font-semibold text-gray-700">Blok</label>
                                <select name="lists[{{ $index }}][blok]"
                                    class="blok-select border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                    <option value="" disabled selected>-- Pilih Blok --</option>
                                    @foreach ($bloks as $blok)
                                        <option class="text-black" value="{{ $blok->blok }}"
                                            @selected(old("lists.$index.blok", $list->blok ?? '') == $blok->blok)>
                                            {{ $blok->blok }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-1">
                                <label class="block mb-1.5 text-xs font-semibold text-gray-700">Plot</label>
                                <div class="custom-dropdown plot-dropdown">
                                    <input type="hidden" name="lists[{{ $index }}][plot]"
                                        class="plot-hidden" value="{{ old("lists.$index.plot", $list->plot ?? '') }}"
                                        required>
                                    <div
                                        class="custom-dropdown-trigger border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between">
                                        <span class="selected-plot text-gray-400">-- Pilih Plot --</span>
                                        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 dropdown-icon"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                    <div class="custom-dropdown-menu plot-menu">
                                        <div class="custom-dropdown-search">
                                            <div class="relative">
                                                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <input type="text"
                                                    class="plot-search w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    placeholder="Cari plot...">
                                            </div>
                                        </div>
                                        <div class="plot-list"></div>
                                        <div class="custom-dropdown-no-results plot-no-results" style="display:none;">
                                            Tidak ada hasil yang ditemukan
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="w-full">
                                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">Luas (Ha)</label>
                                    <input type="number" min="0" max="999.99" step="0.01"
                                        name="lists[{{ $index }}][totalluasactual]"
                                        value="{{ old("lists.$index.totalluasactual", $list->totalluasactual ?? '') }}"
                                        class="border rounded-lg bg-gray-100 border-gray-300 p-2.5 w-full text-sm cursor-not-allowed focus:ring-0 focus:border-gray-300"
                                        readonly />
                                </div>

                                <div class="md:w-36 w-full">
                                    <label class="block mb-1.5 text-xs font-semibold text-gray-700">Estimasi
                                        (Ha)</label>
                                    <input type="number" min="0" max="999.99" step="0.01"
                                        name="lists[{{ $index }}][totalestimasi]"
                                        value="{{ old("lists.$index.totalestimasi", $list->totalestimasi ?? '') }}"
                                        class="border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required />
                                </div>
                            </div>


                            @if ($index > 0)
                                <div class="flex items-end pb-0.5">
                                    <button type="button"
                                        class="remove-row flex items-center gap-2 bg-white border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white px-3 py-2.5 rounded-lg shadow-sm transition-all duration-200 font-medium text-sm">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd"
                                                d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>

    {{-- ======== SCRIPT SECTION ======== --}}
    <script>
        // Custom Dropdown untuk Aktivitas
        (function() {
            const dropdown = document.querySelector('.custom-dropdown');
            const trigger = dropdown.querySelector('.custom-dropdown-trigger');
            const menu = dropdown.querySelector('.custom-dropdown-menu');
            const searchInput = document.getElementById('activity-search');
            const selectedDisplay = document.getElementById('selected-activity');
            const hiddenInput = document.getElementById('activitycode');
            const activityList = document.getElementById('activity-list');
            const noResults = document.getElementById('no-results');
            const dropdownIcon = document.getElementById('dropdown-icon');
            const items = activityList.querySelectorAll('.custom-dropdown-item');

            // Toggle dropdown
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('active');
                dropdownIcon.style.transform = menu.classList.contains('active') ? 'rotate(180deg)' :
                    'rotate(0deg)';
                if (menu.classList.contains('active')) {
                    searchInput.focus();
                }
            });

            // Select item
            items.forEach(item => {
                item.addEventListener('click', function() {
                    const code = this.dataset.code;
                    const name = this.dataset.name;
                    hiddenInput.value = code;
                    selectedDisplay.textContent = `${code} - ${name}`;
                    selectedDisplay.classList.remove('text-gray-400');
                    selectedDisplay.classList.add('text-gray-900');
                    menu.classList.remove('active');
                    dropdownIcon.style.transform = 'rotate(0deg)';
                    searchInput.value = '';
                    filterItems('');
                });
            });

            // Search functionality
            searchInput.addEventListener('input', function() {
                filterItems(this.value.toLowerCase());
            });

            function filterItems(searchTerm) {
                let hasResults = false;
                items.forEach(item => {
                    const code = item.dataset.code.toLowerCase();
                    const name = item.dataset.name.toLowerCase();
                    if (code.includes(searchTerm) || name.includes(searchTerm)) {
                        item.style.display = 'block';
                        hasResults = true;
                    } else {
                        item.style.display = 'none';
                    }
                });
                noResults.style.display = hasResults ? 'none' : 'block';
                activityList.style.display = hasResults ? 'block' : 'none';
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    menu.classList.remove('active');
                    dropdownIcon.style.transform = 'rotate(0deg)';
                }
            });

            // Set initial value if exists
            const initialValue = hiddenInput.value;
            if (initialValue) {
                const selectedItem = activityList.querySelector(`[data-code="${initialValue}"]`);
                if (selectedItem) {
                    const code = selectedItem.dataset.code;
                    const name = selectedItem.dataset.name;
                    selectedDisplay.textContent = `${code} - ${name}`;
                    selectedDisplay.classList.remove('text-gray-400');
                    selectedDisplay.classList.add('text-gray-900');
                }
            }
        })();

        // Custom Dropdown untuk Plot (Dynamic per Blok)
        function initPlotDropdown(row) {
            const dropdown = row.querySelector('.plot-dropdown');
            const trigger = dropdown.querySelector('.custom-dropdown-trigger');
            const menu = dropdown.querySelector('.plot-menu');
            const searchInput = dropdown.querySelector('.plot-search');
            const selectedDisplay = dropdown.querySelector('.selected-plot');
            const hiddenInput = dropdown.querySelector('.plot-hidden');
            const listContainer = dropdown.querySelector('.plot-list');
            const noResults = dropdown.querySelector('.plot-no-results');
            const dropdownIcon = dropdown.querySelector('.dropdown-icon');
            const blokSelect = row.querySelector('.blok-select');

            // Fetch plot list when blok changes
            blokSelect.addEventListener('change', function() {
                const blok = this.value;
                const getPlotUrl = "{{ route('transaction.rkm.getPlot', ':blok') }}".replace(':blok', blok);

                fetch(getPlotUrl)
                    .then(response => response.json())
                    .then(data => {
                        listContainer.innerHTML = '';
                        if (!data.length) {
                            listContainer.innerHTML =
                                '<div class="custom-dropdown-no-results">Tidak ada plot tersedia</div>';
                            return;
                        }
                        data.forEach(plot => {
                            const item = document.createElement('div');
                            item.classList.add('custom-dropdown-item');
                            item.dataset.code = plot;
                            item.innerHTML = `<div class="custom-dropdown-item-code">${plot}</div>`;
                            listContainer.appendChild(item);
                        });

                        attachPlotEvents();
                    });
            });

            // Toggle dropdown open/close
            trigger.addEventListener('click', e => {
                e.stopPropagation();
                menu.classList.toggle('active');
                dropdownIcon.style.transform = menu.classList.contains('active') ? 'rotate(180deg)' :
                    'rotate(0deg)';
                if (menu.classList.contains('active')) searchInput.focus();
            });

            // Filter search
            searchInput.addEventListener('input', () => {
                const term = searchInput.value.toLowerCase();
                let visible = 0;
                listContainer.querySelectorAll('.custom-dropdown-item').forEach(item => {
                    const code = item.dataset.code.toLowerCase();
                    if (code.includes(term)) {
                        item.style.display = 'block';
                        visible++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                noResults.style.display = visible ? 'none' : 'block';
            });

            // Attach click events for plot selection
            function attachPlotEvents() {
                listContainer.querySelectorAll('.custom-dropdown-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const code = this.dataset.code;
                        hiddenInput.value = code;
                        selectedDisplay.textContent = code;
                        selectedDisplay.classList.remove('text-gray-400');
                        selectedDisplay.classList.add('text-gray-900');
                        menu.classList.remove('active');
                        dropdownIcon.style.transform = 'rotate(0deg)';

                        // trigger luas update (AJAX)
                        fetch("{{ route('transaction.rkm.getData') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                        .content
                                },
                                body: JSON.stringify({
                                    plot: code
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                const luasInput = row.querySelector('input[name$="[totalluasactual]"]');
                                if (luasInput) luasInput.value = data.luasarea;
                            });
                    });
                });
            }

            // Close when click outside
            document.addEventListener('click', e => {
                if (!dropdown.contains(e.target)) {
                    menu.classList.remove('active');
                    dropdownIcon.style.transform = 'rotate(0deg)';
                }
            });
        }

        // Inisialisasi untuk setiap row yang sudah ada
        document.querySelectorAll('.input-row').forEach(row => {
            initPlotDropdown(row);
        });

        // Jika user menambah baris baru (addRow), panggil juga initPlotDropdown
        document.addEventListener('click', function(e) {
            if (e.target.closest('#addRow')) {
                setTimeout(() => {
                    const newRow = document.querySelector('#input-container .input-row:last-child');
                    initPlotDropdown(newRow);
                }, 100);
            }
        });

        // Generate new row
        document.getElementById('addRow').addEventListener('click', function() {
            const container = document.getElementById('input-container');
            const rowCount = container.querySelectorAll('.input-row').length;
            const newRow = document.createElement('div');
            newRow.classList.add('input-row', 'bg-gray-50', 'border-2', 'border-gray-200', 'rounded-lg', 'p-3',
                'md:p-4', 'hover:border-blue-300', 'transition-all', 'duration-200');
            newRow.innerHTML = `
                <!-- Desktop Layout -->
                <div class="md:flex space-y-3 md:space-y-0 items-center gap-3">
                    <div class="md:border-none border-b border-gray-300">
                        <div class="pb-3 md:pb-0 flex items-center justify-between">
                            <div class="flex items-center justify-center md:w-12 md:h-12 w-8 h-8 bg-blue-600 md:text-base text-xs text-white md:rounded-xl rounded-full font-bold number-count">${rowCount + 1}</div>
                            <button type="button" class="remove-row text-red-600 hover:bg-red-50 p-2 rounded-lg transition-all md:hidden">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex-1">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Blok</label>
                        <select name="lists[${rowCount}][blok]" class="blok-select border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="" disabled selected>-- Pilih Blok --</option>
                            @foreach ($bloks as $blok)
                                <option value="{{ $blok->blok }}" class="text-black">{{ $blok->blok }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block mb-1.5 text-xs font-semibold text-gray-700">Plot</label>
                        <div class="custom-dropdown plot-dropdown">
                            <input type="hidden" name="lists[${rowCount}][plot]"
                                class="plot-hidden"
                                required>
                            <div
                                class="custom-dropdown-trigger border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white flex items-center justify-between">
                                <span class="selected-plot text-gray-400">-- Pilih Plot --</span>
                                <svg class="w-4 h-4 text-gray-500 transition-transform duration-200 dropdown-icon"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                            <div class="custom-dropdown-menu plot-menu">
                                <div class="custom-dropdown-search">
                                    <div class="relative">
                                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <input type="text"
                                            class="plot-search w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Cari plot...">
                                    </div>
                                </div>
                                <div class="plot-list"></div>
                                <div class="custom-dropdown-no-results plot-no-results" style="display:none;">
                                    Tidak ada hasil yang ditemukan
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="w-full">
                            <label class="block mb-1.5 text-xs font-semibold text-gray-700">Luas (Ha)</label>
                            <input type="number" min="0" max="999.99" step="0.01" name="lists[${rowCount}][totalluasactual]" class="border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required />
                        </div>
                        <div class="md:w-36 w-full">
                            <label class="block mb-1.5 text-xs font-semibold text-gray-700">Estimasi (Ha)</label>
                            <input type="number" min="0" max="999.99" step="0.01" name="lists[${rowCount}][totalestimasi]" class="border rounded-lg border-gray-300 p-2.5 w-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required />
                        </div>
                    </div>
                    <div class="items-end pb-0.5 hidden md:flex">
                        <button type="button" class="remove-row flex items-center gap-2 bg-white border-2 border-red-600 text-red-600 hover:bg-red-600 hover:text-white px-3 py-2.5 rounded-lg shadow-sm transition-all duration-200 font-medium text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z" clip-rule="evenodd"/>
                            </svg>
                            <span>Hapus</span>
                        </button>
                    </div>
                </div>`;
            container.appendChild(newRow);
        });

        document.addEventListener('click', function(event) {
            const removeButton = event.target.closest('.remove-row');
            if (removeButton) {
                const row = removeButton.closest('.input-row');
                if (row) {
                    row.remove();
                    updateRowNumbers();
                }
            }
        });

        function updateRowNumbers() {
            const rows = document.querySelectorAll('.input-row');
            rows.forEach((row, index) => {
                const numberDivs = row.querySelectorAll('.number-count');
                numberDivs.forEach(div => {
                    div.textContent = index + 1;
                });

                const inputs = row.querySelectorAll('select, input');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) {
                        input.setAttribute('name', name.replace(/lists\[\d+\]/, `lists[${index}]`));
                    }
                });
            });
        }

        // function loadPlotOptions(blok, plotSelect, selectedPlot = null) {
        //     const getPlotUrl = "{{ route('transaction.rkm.getPlot', ':blok') }}".replace(':blok', blok);
        //     fetch(getPlotUrl)
        //         .then(response => response.json())
        //         .then(data => {
        //             plotSelect.innerHTML = '<option value="" disabled selected>-- Pilih Plot --</option>';
        //             data.forEach(plot => {
        //                 const option = document.createElement('option');
        //                 option.value = plot;
        //                 option.classList = 'text-black'
        //                 option.textContent = plot;
        //                 if (plot === selectedPlot) option.selected = true;
        //                 plotSelect.appendChild(option);
        //             });
        //         })
        //         .catch(() => {
        //             plotSelect.innerHTML = '<option value="">Error loading</option>';
        //         });
        // }

        // document.querySelectorAll('.input-row').forEach(row => {
        //     const blokSelect = row.querySelector('.blok-select');
        //     const plotSelect = row.querySelector('.plot-select');
        //     const selectedPlot = plotSelect.dataset.selected;
        //     if (blokSelect.value) {
        //         loadPlotOptions(blokSelect.value, plotSelect, selectedPlot);
        //     }
        // });

        // document.addEventListener('change', function(e) {
        //     if (e.target.classList.contains('blok-select')) {
        //         const plotSelect = e.target.closest('.input-row').querySelectorAll('.plot-select');
        //         plotSelect.forEach(select => loadPlotOptions(e.target.value, select));
        //     }
        // });


        $(document).on('change', '.plot-select', function() {
            const row = $(this).closest('.input-row');
            const plot = $(this).val();
            const luasInput = row.find('input[name$="[totalluasactual]"]');

            if (plot) {
                $.ajax({
                    url: "{{ route('transaction.rkm.getData') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        plot: plot
                    },
                    success: function(response) {
                        luasInput.val(response.luasarea);
                    },
                    error: function() {
                        alert('Gagal memuat data luas area.');
                    }
                });
            } else {
                luasInput.val('');
            }
        });

        document.getElementById('startdate').addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (!isNaN(startDate)) {
                const endDate = new Date(startDate);
                endDate.setDate(startDate.getDate() + 7);
                const formatted = endDate.toISOString().split('T')[0];
                document.getElementById('enddate').value = formatted;
            }
        });
    </script>
</x-layout>
