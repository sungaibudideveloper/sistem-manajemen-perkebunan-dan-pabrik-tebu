<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full">
        <form method="POST" action="{{ route('report.rekap-upah-mingguan.index') }}">
            @csrf
            <div class="flex mx-4 items-center gap-2 flex-wrap lg:justify-between justify-center">
                <div class="flex gap-2 text-sm">
                    <div>
                        <label for="tenagakerjarum" class="font-medium text-sm text-gray-700 block mb-1">Jenis Tenaga
                            Kerja:</label>
                        <select name="tenagakerjarum" id="tenagakerjarum"
                            onchange="Alpine.store('loading').start(); this.form.submit()"
                            class="w-fit border border-gray-300 rounded-md p-2 text-sm" required>
                            <option value="" disabled
                                {{ old('tenagakerjarum', session('tenagakerjarum')) == null ? 'selected' : '' }}>
                                -- Tenaga Kerja --</option>
                            <option value="Harian" class="text-black"
                                {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Harian' ? 'selected' : '' }}>
                                Harian</option>
                            <option value="Borongan" class="text-black"
                                {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Borongan' ? 'selected' : '' }}>
                                Borongan
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-end gap-2 flex-wrap justify-center">
                    <div class="block">
                        <span class="block mb-1 text-sm text-gray-700 font-medium">Range tanggal : </span>
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
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="ajax-data" data-url="{{ route('report.rekap-upah-mingguan.index') }}">
                        <div class="flex items-center gap-2 w-full">
                            <div>
                                <label for="perPage" class="text-sm font-medium text-gray-700">Show:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    min="1" autocomplete="off"
                                    class="w-10 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-blue-500 focus:border-blue-500" />
                                <span class="text-gray-700 text-sm font-medium"> entries</span>
                            </div>
                        </div>
                    </div>
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
                            placeholder="Search Kegiatan..." />
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="button" onclick="openPreviewReport()"
                            class="bg-gradient-to-r from-blue-600 to-sky-600 hover:bg-gradient-to-r hover:from-blue-700 hover:to-sky-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm.5 5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm0 5c.47 0 .917-.092 1.326-.26l1.967 1.967a1 1 0 0 0 1.414-1.414l-1.817-1.818A3.5 3.5 0 1 0 11.5 17Z"
                                    clip-rule="evenodd" />
                            </svg>
                            Preview Report
                        </button>
                        <button type="button" onclick="printBp()"
                            class="bg-gradient-to-r from-red-600 to-rose-500 hover:bg-gradient-to-r hover:from-red-700 hover:to-rose-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                                    d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                            </svg>
                            Print BP
                        </button>
                        <button type="button" onclick="exportToExcel()"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:bg-gradient-to-r hover:from-green-700 hover:to-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm2-2a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm0 3a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm-6 4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-6Zm8 1v1h-2v-1h2Zm0 3h-2v1h2v-1Zm-4-3v1H9v-1h2Zm0 3H9v1h2v-1Z"
                                    clip-rule="evenodd" />
                            </svg>
                            Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm text-center" id="tables">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-1">No.</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">LKH No.</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kegiatan</th>

                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Plot</th>

                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal
                                Kegiatan
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Total Biaya
                                (Rp)
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                @if (session()->has('tenagakerjarum'))
                                    {{ session('tenagakerjarum') == 'Harian' ? 'TKH' : 'TKB' }}
                                @else
                                    TK
                                @endif
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (session('tenagakerjarum') != null && $startDate && $endDate)
                            @foreach ($rum as $item)
                                <tr>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-1">
                                        {{ $item->no }}.</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-1">
                                        {{ $item->lkhno }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->activityname }}</td>

                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->plots }}</td>

                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->lkhdate }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->totalupahall }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                        {{ $item->totalworkers ?? '' }}</td>
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-40">
                                        <div class="flex items-center justify-center">
                                            <button class="group flex items-center"
                                                onclick="showList('{{ $item->lkhno }}')"><svg
                                                    class="w-6 h-6 text-gray-500 dark:text-white group-hover:hidden"
                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-width="2"
                                                        d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                                    <path stroke="currentColor" stroke-width="2"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                                <svg class="w-6 h-6 text-gray-500 dark:text-white hidden group-hover:block"
                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" fill="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path fill-rule="evenodd"
                                                        d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="w-2"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="px-2 py-4 text-center text-gray-500">Jenis Tenaga
                                    Kerja atau Range Tanggal Belum Dipilih</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mx-4 mt-1" id="pagination-links">
            @if ($rum->hasPages())
                {{ $rum->appends(['perPage' => $rum->perPage()])->links() }}
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $rum->count() }}</span> of <span
                            class="font-medium">{{ $rum->total() }}</span> results
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal dengan tinggi maksimal responsif -->
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0 transform scale-95 p-4"
        style="opacity: 0; transform: scale(0.95);">
        <div
            class="bg-white w-11/12 max-w-7xl max-h-[90vh] flex flex-col rounded shadow-lg transition-transform duration-300 ease-out transform">
            <!-- Header Modal - Fixed -->
            <div class="flex items-center justify-between p-4 border-b border-gray-300 flex-shrink-0">
                <h2 class="text-lg font-bold">Daftar List</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-200 rounded-md">
                    <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18 17.94 6M18 18 6.06 6" />
                    </svg>
                </button>
            </div>

            <!-- Content Modal - Scrollable -->
            <div class="overflow-auto flex-1 p-4">
                <div class="rounded border border-gray-300">
                    <table class="min-w-full bg-white text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">No.</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Kegiatan</th>
                                @if (session('tenagakerjarum') == 'Harian')
                                    <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Tenaga Kerja</th>
                                @endif

                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Plot</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Luasan (Ha)</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Status Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Hasil (Ha)</th>

                                @if (session('tenagakerjarum') == 'Harian')
                                    <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Cost/Unit</th>
                                    <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Biaya (Rp)</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="listTableBody">
                            <tr>
                                <td colspan="10" class="text-center py-4">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modal styling */
        .invisible {
            visibility: hidden;
            pointer-events: none;
        }

        .visible {
            visibility: visible;
            pointer-events: auto;
        }

        /* Table styling */
        th,
        td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Custom scrollbar untuk modal */
        .overflow-auto::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

    <script>
        function showList(lkhno) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            tableBody.innerHTML = '<tr><td colspan="10" class="text-center py-4">Loading...</td></tr>';

            const url = `{{ route('report.rekap-upah-mingguan.show', ['lkhno' => '__lkhno__']) }}`.replace('__lkhno__',
                lkhno);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(response => {
                    tableBody.innerHTML = '';

                    if (response.error) {
                        tableBody.innerHTML =
                            `<tr><td colspan="10" class="text-center py-4 text-red-600">${response.error}</td></tr>`;
                        return;
                    }

                    const data = response.data || response;

                    if (!data || data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="10" class="text-center py-4">Tidak ada data</td></tr>';
                        return;
                    }

                    // Variabel untuk menghitung total biaya
                    let totalBiaya = 0;

                    data.forEach(item => {
                        @if (session('tenagakerjarum') == 'Harian')
                            const biaya = item.total || '0';
                            const biayaNumeric = parseFloat(biaya.toString().replace(/[^0-9,-]/g, '').replace(
                                ',', '.')) || 0;
                            totalBiaya += biayaNumeric;
                        @endif

                        const row = `
                    <tr class="text-center">
                        <td class="py-2 px-4 border-b border-gray-300">${item.no}.</td>
                        <td class="py-2 px-4 border-b border-gray-300">${item.activityname || ''}</td>
                        @if (session('tenagakerjarum') == 'Harian')
                            <td class="py-2 px-4 border-b border-gray-300">${item.namatenagakerja}</td>
                        @endif
                        
                        <td class="py-2 px-4 border-b border-gray-300">${item.plot || ''}</td>
                        <td class="py-2 px-4 border-b border-gray-300">${item.luasrkh || ''}</td>
                        <td class="py-2 px-4 border-b border-gray-300">${item.batchdate || ''}/${item.lifecyclestatus}</td>
                        <td class="py-2 px-4 border-b border-gray-300">${item.luashasil || ''}</td>
                        
                        @if (session('tenagakerjarum') == 'Harian')
                            <td class="py-2 px-4 border-b border-gray-300">${item.upah || '-'}</td>
                            <td class="py-2 px-4 border-b border-gray-300">${item.total || '-'}</td>
                        @endif
                    </tr>
                `;
                        tableBody.innerHTML += row;
                    });

                    // Tambahkan baris total jika tenaga kerja adalah Harian
                    @if (session('tenagakerjarum') == 'Harian')
                        // Format total biaya ke format Rupiah dengan ,00 di belakang
                        const totalFormatted = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(totalBiaya);

                        const totalRow = `
                    <tr class="text-center font-bold bg-gray-50">
                        <td colspan="8" class="py-2 px-4 border-t-2 border-gray-400 text-right">Total Biaya:</td>
                        <td class="py-2 px-4 border-t-2 border-gray-400">${totalFormatted}</td>
                    </tr>
                `;
                        tableBody.innerHTML += totalRow;
                    @endif

                    modal.classList.remove('invisible');
                    modal.classList.add('visible');
                    setTimeout(() => {
                        modal.style.opacity = "1";
                        modal.style.transform = "scale(1)";
                    }, 50);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    tableBody.innerHTML =
                        `<tr><td colspan="10" class="text-center py-4 text-red-600">Gagal memuat data: ${error.message}</td></tr>`;
                });
        }

        function closeModal() {
            const modal = document.getElementById('listModal');

            modal.style.opacity = "0";
            modal.style.transform = "scale(0.95)";

            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
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

        // Fungsi untuk membuka halaman preview dengan parameter tanggal
        function openPreviewReport() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const tenagaKerja = document.getElementById('tenagakerjarum').value;

            if (!tenagaKerja) {
                alert('Harap pilih tenaga kerja terlebih dahulu');
                return;
            }

            if (!startDate || !endDate) {
                alert('Harap pilih range tanggal terlebih dahulu');
                return;
            }

            const baseUrl = "{{ route('report.rekap-upah-mingguan.preview') }}";
            const url = `${baseUrl}?start_date=${startDate}&end_date=${endDate}`;

            window.open(url, '_blank');
        }

        function printBp() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const tenagaKerja = document.getElementById('tenagakerjarum').value;

            if (!tenagaKerja) {
                alert('Harap pilih tenaga kerja terlebih dahulu');
                return;
            }

            if (!startDate || !endDate) {
                alert('Harap pilih range tanggal terlebih dahulu');
                return;
            }

            const baseUrl = "{{ route('report.rekap-upah-mingguan.print-bp') }}";
            const url = `${baseUrl}?start_date=${startDate}&end_date=${endDate}&tenagakerja=${tenagaKerja}`;

            window.open(url, '_blank');
        }

        // Fungsi untuk export Excel
        function exportToExcel() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Harap pilih range tanggal terlebih dahulu');
                return;
            }

            // Buat URL untuk export Excel
            const baseUrl = "{{ route('report.rekap-upah-mingguan.export-excel') }}";
            const url = `${baseUrl}?start_date=${startDate}&end_date=${endDate}`;

            // Download langsung
            window.location.href = url;
        }
    </script>
</x-layout>
