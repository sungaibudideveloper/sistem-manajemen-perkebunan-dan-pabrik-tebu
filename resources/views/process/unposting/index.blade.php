<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md {{ $startDate && $endDate ? 'w-full' : 'w-fit' }}">
        @if (!$startDate && !$endDate)
            <div class="px-4 py-2 text-gray-500 font-medium text-xs text-center">*(silahkan atur pengamatan dan range
                tanggal untuk
                menampilkan data)</div>
        @endif
        <div class="flex items-start mx-4 gap-2 lg:justify-between justify-center flex-wrap">
            <div class="flex items-end gap-2 flex-wrap">
                <div class="flex gap-2 justify-between">
                    <form method="POST" action="{{ route('process.unposting') }}">
                        @csrf
                        <div class="flex gap-2 items-end flex-wrap justify-center">
                            <div>
                                <label for="unposting" class="font-medium text-sm text-gray-700">Pilih
                                    Pengamatan:</label>
                                <select name="unposting" id="unposting" onchange="this.form.submit()"
                                    class="w-fit border border-gray-300 rounded-md p-2 text-sm" required>
                                    <option value="" disabled
                                        {{ old('unposting', session('unposting')) == null ? 'selected' : '' }}>
                                        --Pilih Pengamatan--</option>
                                    <option value="Agronomi" class="text-black"
                                        {{ old('unposting', session('unposting')) == 'Agronomi' ? 'selected' : '' }}>
                                        Agronomi</option>
                                    <option value="HPT" class="text-black"
                                        {{ old('unposting', session('unposting')) == 'HPT' ? 'selected' : '' }}>
                                        HPT
                                    </option>
                                </select>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap justify-center">
                                    <div class="flex items-center gap-2 flex-wrap justify-center">
                                        <span class="text-sm text-gray-700 font-medium">Range tanggal pengamatan:
                                        </span>
                                        <div class="relative inline-block w-fit">
                                            <button type="button"
                                                class="inline-flex justify-between text-left w-full items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                id="menu-button" aria-expanded="false" aria-haspopup="true"
                                                onclick="toggleDropdown()">
                                                <div class="flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                                        class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20"
                                                        fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Date Filter</span>
                                                </div>
                                                <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div class="absolute z-10 mt-[0.5px] w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden pb-2"
                                                id="menu-dropdown">
                                                <div class="py-1 px-4" role="menu" aria-orientation="vertical"
                                                    aria-labelledby="menu-button">
                                                    <div class="py-2">
                                                        <label for="start_date"
                                                            class="block text-sm font-medium text-gray-700">Start
                                                            Date</label>
                                                        <input type="date" id="start_date" name="start_date"
                                                            value="{{ old('start_date', $startDate ?? '') }}"
                                                            class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400"
                                                            oninput="this.className = this.value ? 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                                                    </div>

                                                    <div class="py-2">
                                                        <label for="end_date"
                                                            class="block text-sm font-medium text-gray-700">End
                                                            Date</label>
                                                        <input type="date" id="end_date" name="end_date"
                                                            value="{{ old('end_date', $endDate ?? '') }}"
                                                            class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400"
                                                            oninput="this.className = this.value ? 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="ajax-data" data-url="{{ route('process.unposting') }}">
                                            <div class="flex items-center gap-2 w-full">
                                                <div>
                                                    <label for="perPage"
                                                        class="text-sm font-medium text-gray-700">Items per
                                                        page:</label>
                                                    <input type="text" name="perPage" id="perPage"
                                                        value="{{ $perPage }}" min="1" autocomplete="off"
                                                        class="w-10 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-blue-500 focus:border-blue-500" />
                                                </div>
                                            </div>
                                        </div>
                                        @if ($startDate && $endDate)
                                            <div class="relative">
                                                <div
                                                    class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                                    <svg class="w-4 h-4 text-gray-500 dark:text-gray-400"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" viewBox="0 0 20 20">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                                    </svg>
                                                </div>
                                                <input type="text" id="search" autocomplete="off" name="search"
                                                    value="{{ old('search', $search) }}"
                                                    class="block w-80 p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                    placeholder="Search Sample, Variety, or Category..." />
                                            </div>
                                        @endif
                                    </div>
                                    @if (!$startDate && !$endDate)
                                        <div class="justify-center flex">
                                            <button type="submit" name="filter"
                                                class="flex items-center gap-2 w-auto py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    fill="currentColor" viewBox="0 0 24 24">
                                                    <path fill-rule="evenodd"
                                                        d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm13.707-1.293a1 1 0 0 0-1.414-1.414L11 12.586l-1.793-1.793a1 1 0 0 0-1.414 1.414l2.5 2.5a1 1 0 0 0 1.414 0l4-4Z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span>Apply</span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @if ($startDate && $endDate)
                @if (hasPermission('Batal Posting'))
                    <div>
                        <form action="{{ route('process.unposting.submit') }}" method="POST" id="unpost">
                            @csrf
                            <input type="hidden" name="selected_items" id="selected_items">
                            <button type="submit"
                                class="flex items-center border text-red-600 border-red-600 py-2 px-4 hover:bg-red-600 hover:text-white font-medium rounded-md text-sm gap-2">
                                <svg class="w-5 h-5 dark:text-white" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd"
                                        d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm7.707-3.707a1 1 0 0 0-1.414 1.414L10.586 12l-2.293 2.293a1 1 0 1 0 1.414 1.414L12 13.414l2.293 2.293a1 1 0 0 0 1.414-1.414L13.414 12l2.293-2.293a1 1 0 0 0-1.414-1.414L12 10.586 9.707 8.293Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>
                                    Unposting
                                </span>
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>

        @if ($startDate && $endDate)
            <div class="mx-auto px-4 py-4">
                <div class="mb-1 -mt-2">
                    <span class="text-xs text-gray-400">*(check the checkbox for the data that will be posted.)</span>
                </div>
                <div class="overflow-x-auto rounded-md border-gray-300 border">
                    <table class="min-w-full bg-white text-sm text-center" id="tables">
                        <thead>
                            <tr>
                                <th class="w-1 py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    <input type="checkbox" id="selectAll" onclick="toggleCheckboxes(this)"
                                        class="rounded">
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.
                                    Sample
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Plot
                                    Sample</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Varietas
                                </th>
                                {{-- @if (session('unposting') === 'Agronomi') --}}
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Kategori
                                </th>
                                {{-- @endif --}}
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Tanggal
                                    Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Tanggal
                                    Pengamatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unposts as $item)
                                <tr>
                                    <td class="w-1 py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        <input type="checkbox" class="rowCheckbox rounded" name="selected_items[]"
                                            value="{{ $item->nosample }},{{ $item->companycode }},{{ $item->tanggalpengamatan }}">
                                    </td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->no }}.</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->nosample }}</td>
                                    </td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->idblokplot }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->varietas }}</td>
                                    {{-- @if (session('unposting') === 'Agronomi') --}}
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->kat }}</td>
                                    {{-- @endif --}}
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->tanggaltanam }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->tanggalpengamatan }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mx-4 my-1" id="pagination-links">
                @if ($unposts->hasPages())
                    {{ $unposts->appends(['perPage' => $unposts->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $unposts->count() }}</span> of <span
                                class="font-medium">{{ $unposts->total() }}</span> results
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script>
        function toggleCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.rowCheckbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('menu-dropdown');
            dropdown.classList.toggle('hidden');
        }
    </script>
    <script>
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("menu-dropdown");
            const button = document.getElementById("menu-button");

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });
    </script>
    <script>
        document.querySelector("#unpost").addEventListener("submit", function(event) {
            event.preventDefault();
            const selectedItems = [];
            document.querySelectorAll(".rowCheckbox:checked").forEach(checkbox => {
                selectedItems.push(checkbox.value);
            });
            if (selectedItems.length === 0) {
                alert("Silakan pilih setidaknya satu data untuk di unposting.");
                return;
            }
            const selectedSamples = selectedItems.map(item => item.split(',')[0]);
            const confirmText =
                `Yakin ingin unposting data untuk nomor sample berikut?\n\n${selectedSamples.join(', ')}`;
            if (!confirm(confirmText)) {
                return;
            }
            document.getElementById("selected_items").value = JSON.stringify(selectedItems);
            this.submit();
        });
    </script>

    <style>
        th,
        td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .max-h-96 {
            max-height: 24rem;
            overflow-x: auto;
            overflow-y: hidden;
        }
    </style>

</x-layout>
