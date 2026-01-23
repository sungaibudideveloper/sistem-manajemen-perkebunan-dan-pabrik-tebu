<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4">
        <!-- Header Card dengan Gradient -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg mb-6 p-6">
            <h1 class="text-2xl font-bold text-white mb-2">Data Agronomi</h1>
            <p class="text-blue-100">Kelola dan monitor data pengamatan agronomi</p>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-lg">
            <!-- Toolbar Section -->
            <div class="p-6 bg-gradient-to-br from-gray-50 to-white border-b border-gray-200 rounded-t-xl">
                <div class="flex lg:justify-between items-center gap-4 flex-wrap">
                    <!-- Action Buttons -->
                    <div class="flex gap-3 flex-wrap">
                        @can('transaction.agronomi.create')
                            <a href="{{ route('transaction.agronomi.create') }}"
                                class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 text-sm font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Tambah Data</span>
                            </a>
                        @endcan

                        @can('transaction.agronomi.export')
                            <button data-export="agronomi"
                                class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2.5 text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd"
                                        d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Export Excel</span>
                            </button>
                        @endcan
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="flex items-center gap-3 flex-wrap flex-1 justify-end">
                        <!-- Items per page -->
                        <div id="ajax-data" data-url="{{ route('transaction.agronomi.handle') }}"
                            class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-gray-300 shadow-sm">
                            <label for="perPage" class="text-sm font-medium text-gray-700 whitespace-nowrap">Per
                                Halaman:</label>
                            <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                min="1" autocomplete="off"
                                class="w-14 p-1.5 border border-gray-300 rounded-md text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>

                        <!-- Search Box -->
                        <div class="relative flex-1 min-w-[280px] max-w-md">
                            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" id="search" autocomplete="off" name="search"
                                value="{{ old('search', $search) }}"
                                class="w-full pl-10 pr-4 py-2.5 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                placeholder="Cari Sample, Plot, Varietas, atau Kategori..." />
                        </div>

                        <!-- Date Filter Dropdown -->
                        <div class="relative">
                            <button type="button"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 shadow-sm transition-all"
                                id="menu-button" onclick="toggleDropdown()">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Filter Tanggal</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div class="absolute right-0 z-10 mt-2 w-72 rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                id="menu-dropdown">
                                <div class="p-4 space-y-4">
                                    <div>
                                        <label for="start_date"
                                            class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                        <input type="date" id="start_date" name="start_date"
                                            value="{{ old('start_date', $startDate ?? '') }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            oninput="this.className = this.value ? 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black' : 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400'">
                                    </div>
                                    <div>
                                        <label for="end_date"
                                            class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                                        <input type="date" id="end_date" name="end_date"
                                            value="{{ old('end_date', $endDate ?? '') }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            oninput="this.className = this.value ? 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black' : 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400'">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="tables">
                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                No. Sample</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Plot</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Varietas</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Kategori</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Tgl Tanam</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Tgl Pengamatan</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($agronomi as $item)
                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $item->nosample }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->plot }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->varietas }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $item->kat }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $item->tanggaltanam }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $item->tanggalpengamatan }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $item->status === 'Posted' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- View Button -->
                                        <button
                                            onclick="showList('{{ $item->nosample }}', '{{ $item->companycode }}', '{{ $item->tanggalpengamatan }}')"
                                            class="p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200 group"
                                            title="Lihat Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        @can('transaction.agronomi.edit')
                                            @if ($item->status === 'Unposted')
                                                <a href="{{ route('transaction.agronomi.edit', ['nosample' => $item->nosample, 'companycode' => $item->companycode, 'tanggalpengamatan' => $item->tanggalpengamatan]) }}"
                                                    class="p-2 text-green-600 hover:bg-green-100 rounded-lg transition-all duration-200"
                                                    title="Edit">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                            @endif
                                        @endcan

                                        @can('transaction.agronomi.delete')
                                            @if ($item->status === 'Unposted')
                                                <form
                                                    action="{{ route('transaction.agronomi.destroy', ['nosample' => $item->nosample, 'companycode' => $item->companycode, 'tanggalpengamatan' => $item->tanggalpengamatan]) }}"
                                                    method="POST" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        onclick="return confirm('Yakin ingin menghapus data ini?')"
                                                        class="p-2 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                                        title="Hapus">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl" id="pagination-links">
                @if ($agronomi->hasPages())
                    {{ $agronomi->appends(['perPage' => $agronomi->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-700">
                            Menampilkan <span class="font-semibold">{{ $agronomi->count() }}</span> dari <span
                                class="font-semibold">{{ $agronomi->total() }}</span> hasil
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0"
        style="opacity: 0;">
        <div
            class="bg-white w-11/12 max-h-[90vh] rounded-xl shadow-2xl transition-transform duration-300 ease-out transform scale-95 flex flex-col">
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-xl">
                <h2 class="text-xl font-bold text-white">Detail Data Agronomi</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-blue-800 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="overflow-auto p-6 flex-1">
                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full bg-white text-sm">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    No.</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    No. Sample</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Kebun</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Blok</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Plot</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Luas</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Varietas</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Kategori</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Tgl Tanam</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Umur Tanam</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Jarak Tanam</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Tgl Pengamatan</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Bulan Pengamatan</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">ni
                                </th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Jumlah Batang</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Panjang GAP</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    %GAP</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    %Germinasi</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">pH
                                    Tanah</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Populasi</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Kotak Gulma</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    %Penutupan Gulma</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Tinggi Primer</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Tinggi Sekunder</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Tinggi Tersier</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Tinggi Kuarter</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Diameter Primer</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Diameter Sekunder</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Diameter Tersier</th>
                                <th class="py-3 px-4 border-b border-gray-200 font-semibold text-gray-700 text-left">
                                    Diameter Kuarter</th>
                            </tr>
                        </thead>
                        <tbody id="listTableBody" class="divide-y divide-gray-200">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .invisible {
            visibility: hidden;
            pointer-events: none;
        }

        .visible {
            visibility: visible;
            pointer-events: auto;
        }

        th,
        td {
            white-space: nowrap;
        }

        #listModal.visible {
            opacity: 1 !important;
        }

        #listModal .scale-95 {
            transform: scale(0.95);
        }
    </style>

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

        function showList(nosample, companycode, tanggalpengamatan) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            tableBody.innerHTML =
                '<tr><td colspan="30" class="py-8 text-center"><div class="flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span class="text-gray-600">Memuat data...</span></div></td></tr>';

            const url =
                `{{ route('transaction.agronomi.show', ['nosample' => '__nosample__', 'companycode' => '__companycode__', 'tanggalpengamatan' => '__tanggalpengamatan__']) }}`
                .replace('__nosample__', nosample)
                .replace('__companycode__', companycode)
                .replace('__tanggalpengamatan__', tanggalpengamatan);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    tableBody.innerHTML = '';
                    data.forEach(item => {
                        const dateInput = new Date(item.tanggalpengamatan);
                        const month = dateInput.toLocaleString('en-US', {
                            month: 'long'
                        });

                        const row = `
                            <tr class="hover:bg-blue-50 transition-colors">
                                <td class="py-3 px-4 text-gray-700">${item.no}.</td>
                                <td class="py-3 px-4 text-gray-700">${item.nosample}</td>
                                <td class="py-3 px-4 text-gray-700">${item.compName}</td>
                                <td class="py-3 px-4 text-gray-700">${item.blokName}</td>
                                <td class="py-3 px-4 text-gray-700">${item.plotName}</td>
                                <td class="py-3 px-4 text-gray-700">${item.luasarea}</td>
                                <td class="py-3 px-4 text-gray-700">${item.varietas}</td>
                                <td class="py-3 px-4 text-gray-700">${item.kat}</td>
                                <td class="py-3 px-4 text-gray-700">${item.tanggaltanam}</td>
                                <td class="py-3 px-4 text-gray-700">${item.umur_tanam} Bulan</td>
                                <td class="py-3 px-4 text-gray-700">${item.jaraktanam}</td>
                                <td class="py-3 px-4 text-gray-700">${item.tanggalpengamatan}</td>
                                <td class="py-3 px-4 text-gray-700">${month}</td>
                                <td class="py-3 px-4 text-gray-700">${item.nourut}</td>
                                <td class="py-3 px-4 text-gray-700">${item.jumlahbatang}</td>
                                <td class="py-3 px-4 text-gray-700">${item.pan_gap}</td>
                                <td class="py-3 px-4 text-gray-700">${(item.per_gap*100).toFixed(2)}%</td>
                                <td class="py-3 px-4 text-gray-700">${(item.per_germinasi*100).toFixed(2)}%</td>
                                <td class="py-3 px-4 text-gray-700">${item.ph_tanah}</td>
                                <td class="py-3 px-4 text-gray-700">${item.populasi}</td>
                                <td class="py-3 px-4 text-gray-700">${item.ktk_gulma}</td>
                                <td class="py-3 px-4 text-gray-700">${(item.per_gulma*100).toFixed(2)}%</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_primer}</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_sekunder}</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_tersier}</td>
                                <td class="py-3 px-4 text-gray-700">${item.t_kuarter}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_primer}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_sekunder}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_tersier}</td>
                                <td class="py-3 px-4 text-gray-700">${item.d_kuarter}</td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });

                    modal.classList.remove('invisible');
                    modal.classList.add('visible');
                    setTimeout(() => {
                        modal.style.opacity = "1";
                        modal.querySelector('.bg-white').style.transform = "scale(1)";
                    }, 50);
                })
                .catch(error => {
                    console.error('Error:', error);
                    tableBody.innerHTML =
                        '<tr><td colspan="30" class="py-8 text-center text-red-600">Gagal memuat data. Silakan coba lagi.</td></tr>';
                });
        }

        function closeModal() {
            const modal = document.getElementById('listModal');
            modal.style.opacity = "0";
            modal.querySelector('.bg-white').style.transform = "scale(0.95)";
            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
        }
    </script>
</x-layout>
