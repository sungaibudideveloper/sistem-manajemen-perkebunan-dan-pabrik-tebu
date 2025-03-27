<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        <div class="flex items-end mx-4 gap-2 flex-wrap justify-between">
            <form action="{{ route('postSession') }}" method="POST">
                @csrf
                <div class="text-center">
                    <label for="posting" class="font-medium text-sm text-gray-700">Silahkan Pilih Pengamatan yang akan ditampilkan
                        :</label>
                    <select name="posting" class="w-fit border border-gray-300 rounded p-2 text-sm" required
                        onchange="this.form.submit()">
                        <option value="" disabled {{ old('posting', session('posting')) == null ? 'selected' : '' }}>
                            --Pilih Pengamatan--</option>
                        <option value="Agronomi" {{ old('posting', session('posting')) == 'Agronomi' ? 'selected' : '' }}>
                            Agronomi</option>
                        <option value="HPT" {{ old('posting', session('posting')) == 'HPT' ? 'selected' : '' }}>HPT
                        </option>
                    </select>
                </div>
            </form>
            @if (session('posting') != null)
                <div class="flex justify-center items-end gap-2 flex-wrap">
                    <form method="POST" action="{{ route('process.posting') }}">
                        @csrf
                        <div class="flex gap-2 items-end">
                            <div>
                                <label for="perPage" class="text-sm font-medium text-gray-700">Items per page:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    min="1" onchange="this.form.submit()"
                                    class="w-10 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

                            </div>
                            <div>
                                <div class="relative inline-block text-left w-full">
                                    <div>
                                        <button type="button"
                                            class="inline-flex justify-center w-full items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                            id="menu-button" aria-expanded="false" aria-haspopup="true"
                                            onclick="toggleDropdown()">
                                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                                class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span>Date Filter</span>
                                            <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="absolute z-10 mt-1 w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden"
                                        id="menu-dropdown">
                                        <div class="py-1 px-4" role="menu" aria-orientation="vertical"
                                            aria-labelledby="menu-button">
                                            <div class="py-2">
                                                <label for="start_date"
                                                    class="block text-sm font-medium text-gray-700">Start Date</label>
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

                                            <div class="py-2">
                                                <button type="submit" name="filter"
                                                    class="w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>
                    <div>
                        <form action="{{ route('process.posting.submit') }}" method="POST">
                            @csrf
                            <input type="hidden" name="selected_items" id="selected_items">
                            <button type="submit"
                                class="flex items-center border text-green-600 border-green-600 py-2 px-4 hover:bg-green-600 hover:text-white font-medium rounded-md text-sm gap-2">
                                <svg class="w-5 h-5 dark:text-white" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                    viewBox="0 0 24 24">
                                    <path fill-rule="evenodd"
                                        d="M18 14a1 1 0 1 0-2 0v2h-2a1 1 0 1 0 0 2h2v2a1 1 0 1 0 2 0v-2h2a1 1 0 1 0 0-2h-2v-2Z"
                                        clip-rule="evenodd" />
                                    <path fill-rule="evenodd"
                                        d="M15.026 21.534A9.994 9.994 0 0 1 12 22C6.477 22 2 17.523 2 12S6.477 2 12 2c2.51 0 4.802.924 6.558 2.45l-7.635 7.636L7.707 8.87a1 1 0 0 0-1.414 1.414l3.923 3.923a1 1 0 0 0 1.414 0l8.3-8.3A9.956 9.956 0 0 1 22 12a9.994 9.994 0 0 1-.466 3.026A2.49 2.49 0 0 0 20 14.5h-.5V14a2.5 2.5 0 0 0-5 0v.5H14a2.5 2.5 0 0 0 0 5h.5v.5c0 .578.196 1.11.526 1.534Z"
                                        clip-rule="evenodd" />
                                </svg>

                                <span>
                                    Posting
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        @if (session('posting') != null)
            <div class="mx-auto px-4 py-4">
                <div class="mb-1 -mt-2">
                    <span class="text-xs text-gray-400">*(check the checkbox for the data that will be unposted)</span>
                </div>
                <div class="overflow-x-auto rounded-md border-gray-300 border">
                    <table class="min-w-full bg-white text-sm text-center">
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
                                @if (session('posting') === 'Agronomi')
                                    <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                        Kategori
                                    </th>
                                @endif
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Tanggal Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Tanggal Pengamatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $item)
                                <tr>
                                    <td class="w-1 py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        <input type="checkbox" class="rowCheckbox rounded" name="selected_items[]"
                                            value="{{ $item->no_sample }},{{ $item->kd_comp }},{{ $item->tgltanam }}">
                                    </td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->no }}.</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->no_sample }}</td>
                                    </td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->kd_plotsample }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->varietas }}</td>
                                    @if (session('posting') === 'Agronomi')
                                        <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                            {{ $item->kat }}</td>
                                    @endif
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->tgltanam }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->tglamat }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mx-4 my-1">
                @if ($posts->hasPages())
                    {{ $posts->appends(['perPage' => $posts->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $posts->count() }}</span> of <span
                                class="font-medium">{{ $posts->total() }}</span> results
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputElement = document.getElementById("perPage");
            inputElement.addEventListener("input", (event) => {
                event.target.value = event.target.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
    <script>
        function toggleCheckboxes(source) {
            const checkboxes = document.querySelectorAll('.rowCheckbox');
            checkboxes.forEach(checkbox => checkbox.checked = source.checked);
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('menu-dropdown');
            dropdown.classList.toggle('hidden');
        }
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("menu-dropdown");
            const button = document.getElementById("menu-button");
            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });
    </script>
    <script>
        document.querySelector("form[action='{{ route('process.posting.submit') }}']").addEventListener("submit", function(
            event) {
            event.preventDefault();
            const selectedItems = [];
            document.querySelectorAll(".rowCheckbox:checked").forEach(checkbox => {
                selectedItems.push(checkbox.value);
            });
            if (selectedItems.length === 0) {
                alert("Silakan pilih setidaknya satu data untuk diposting.");
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

        select:invalid {
            color: gray;
        }

        option[disabled] {
            color: gray;
        }

        select:disabled {
            color: gray;
        }

        option:disabled {
            color: gray;
        }

        option:not([disabled]) {
            color: black;
        }
    </style>

</x-layout>
