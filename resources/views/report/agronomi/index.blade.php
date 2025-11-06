<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md {{ $startDate && $endDate ? 'w-full' : 'w-fit' }}">
        @if (!$startDate && !$endDate)
            <div class="px-4 py-2 text-gray-500 font-medium text-sm text-center">*(silahkan pilih range tanggal
                pengamatan untuk menampilkan data)</div>
        @endif
        <div
            class="flex mx-4 items-center gap-2 flex-wrap {{ $startDate && $endDate ? 'lg:justify-between justify-center' : 'justify-center' }}">
            @if ($startDate && $endDate)
                <div class="flex gap-2 text-sm">
                    @if (hasPermission('Excel Agronomi'))
                        <button data-export="agronomi"
                            class="bg-green-500 text-white px-4 py-2 rounded-md text-sm border border-transparent shadow-sm font-medium hover:bg-green-600 flex items-center space-x-2">
                            <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export to Excel</span>
                        </button>
                    @endif
                    @if (hasPermission('Pivot Agronomi'))
                        <button
                            class="bg-blue-700 text-white px-4 py-2 border border-transparent shadow-sm rounded-md font-medium hover:bg-blue-800 flex items-center space-x-2"
                            onclick="window.location.href='{{ route('pivotTableAgronomi', ['start_date' => old('start_date', request()->start_date), 'end_date' => old('end_date', request()->end_date)]) }}'">
                            <svg class="w-5 h-5 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm-1 9a1 1 0 1 0-2 0v2a1 1 0 1 0 2 0v-2Zm2-5a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1Zm4 4a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0v-3Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export to Pivot</span>
                        </button>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('report.agronomi.index') }}">
                @csrf
                <div class="flex items-center gap-2 flex-wrap justify-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700 font-medium">Range tanggal pengamatan : </span>
                        <div class="relative inline-block w-fit">
                            <button type="button"
                                class="inline-flex justify-center w-fit items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                id="menu-button" aria-expanded="false" aria-haspopup="true" onclick="toggleDropdown()">
                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                    class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Date Filter</span>
                                <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div class="absolute left-0 z-10 mt-[1px] w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden"
                                id="menu-dropdown">
                                <div class="py-1 px-4" role="menu" aria-orientation="vertical"
                                    aria-labelledby="menu-button">
                                    <div class="py-2">
                                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start
                                            Date</label>
                                        <input type="date" id="start_date" name="start_date" required
                                            value="{{ old('start_date', $startDate ?? '') }}"
                                            class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400"
                                            oninput="this.className = this.value ? 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                                    </div>

                                    <div class="py-2">
                                        <label for="end_date" class="block text-sm font-medium text-gray-700">End
                                            Date</label>
                                        <input type="date" id="end_date" name="end_date" required
                                            value="{{ old('end_date', $endDate ?? '') }}"
                                            class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400"
                                            oninput="this.className = this.value ? 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                                    </div>

                                    @if (!$startDate && !$endDate)
                                        <div class="py-2">
                                            <button type="submit" name="filter"
                                                class="w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Apply
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ajax-data" data-url="{{ route('report.agronomi.index') }}">
                        <div class="flex items-center gap-2 w-full">
                            <div>
                                <label for="perPage" class="text-sm font-medium text-gray-700">Items per
                                    page:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    min="1" autocomplete="off"
                                    class="w-10 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        </div>
                    </div>
                    @if ($startDate && $endDate)
                        <div class="relative">
                            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>
                            <input type="text" id="search" autocomplete="off" name="search"
                                value="{{ old('search', $search) }}"
                                class="block w-[350px] p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Search Sample, Plot, Variety, or Category..." />
                        </div>
                    @endif
                </div>
            </form>
        </div>
        @if ($startDate && $endDate)
            <div class="mx-auto px-4 py-4">
                <div class="overflow-x-auto rounded-md border border-gray-300">
                    <table class="min-w-full bg-white text-sm text-center" id="tables">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.
                                    Sample</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Blok
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Plot
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Luas
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Varietas
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kategori
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal
                                    Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Umur
                                    Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Jarak
                                    Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal
                                    Pengamatan</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Bulan
                                    Pengamatan</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No. Urut
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Jumlah
                                    Batang</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Panjang
                                    GAP</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">%GAP
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    %Germinasi</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">pH Tanah
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Populasi
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kotak
                                    Gulma</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    %Penutupan Gulma</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tinggi
                                    Primer</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tinggi
                                    Sekunder</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tinggi
                                    Tersier</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tinggi
                                    Kuarter</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Diameter
                                    Primer</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Diameter
                                    Sekunder</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Diameter
                                    Tersier</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Diameter
                                    Kuarter</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($agronomi as $item)
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->no }}.</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->nosample }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->blokName ?? '' }}
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->plotName ?? '' }}
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">
                                        {{ $item->luasarea ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->varietas ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->kat ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->tanggaltanam ?? '' }}
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ round($item->umur_tanam) }}
                                        Bulan
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">
                                        {{ $item->jaraktanam ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">
                                        {{ $item->tanggalpengamatan ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->bulanPengamatan }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->nourut }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->jumlahbatang }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->pan_gap }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->per_gap * 100 }}%</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->per_germinasi * 100 }}%
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->ph_tanah }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->populasi }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->ktk_gulma }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->per_gulma * 100 }}%</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->t_primer }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->t_sekunder }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->t_tersier }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->t_kuarter }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->d_primer }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->d_sekunder }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->d_tersier }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->d_kuarter }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>


            <div class="mx-4 mt-1" id="pagination-links">
                @if ($agronomi->hasPages())
                    {{ $agronomi->appends(['perPage' => $agronomi->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing <span class="font-medium">{{ $agronomi->count() }}</span> of <span
                                class="font-medium">{{ $agronomi->total() }}</span> results
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <script>
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
