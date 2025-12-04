<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full">
        <!-- Header Form -->
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 text-center">Mapping BSM</h2>
        </div>

        <!-- Form Filter -->
        <div class="p-6">
            <form method="POST" action="{{ route('input.mapping-bsm.index') }}" class="space-y-6">
                @csrf
                
                <!-- Range Tanggal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="tanggalawal" class="block text-sm font-medium text-gray-700">
                            Tanggal Awal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="tanggalawal" 
                               name="tanggalawal" 
                               required
                               value="{{ request('tanggalawal') ?? old('tanggalawal') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm {{ (request('tanggalawal') || old('tanggalawal')) ? 'text-black' : 'text-gray-400' }}"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('tanggalawal')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="tanggalakhir" class="block text-sm font-medium text-gray-700">
                            Tanggal Akhir <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="tanggalakhir" 
                               name="tanggalakhir" 
                               required
                               value="{{ request('tanggalakhir') ?? old('tanggalakhir') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm {{ (request('tanggalakhir') || old('tanggalakhir')) ? 'text-black' : 'text-gray-400' }}"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('tanggalakhir')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Search Button -->
                <div class="flex justify-center pt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Cari Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Information Card -->
        <div class="mx-4 mb-4 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Informasi:</strong> Menampilkan ringkasan data RKH beserta jumlah LKH dan BSM yang terkait. Pilih rentang tanggal untuk melihat data ringkasan mapping BSM.
                    </p>
                </div>
            </div>
        </div>

        <!-- DataTable Container -->
        <div class="p-6 pt-0">
            @if(request('tanggalawal') && request('tanggalakhir') && isset($data))
            <div id="table-container">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Ringkasan Data RKH</h3>
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            Periode: <span class="font-medium">{{ \Carbon\Carbon::parse(request('tanggalawal'))->format('d F Y') }} - {{ \Carbon\Carbon::parse(request('tanggalakhir'))->format('d F Y') }}</span>
                            | Total RKH: <span class="font-medium text-indigo-600">{{ count($data) }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" 
                                    id="export-excel-btn"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Excel
                            </button>
                            <button type="button" 
                                    id="print-btn"
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print
                            </button>
                        </div>
                    </div>
                </div>

                @if(count($data) > 0)
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-blue-600">Total RKH</dt>
                                <dd class="text-2xl font-bold text-blue-900">{{ count($data) }}</dd>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V7a2 2 0 00-2-2h-2m-2 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-green-600">Total LKH</dt>
                                <dd class="text-2xl font-bold text-green-900">{{ $data->sum('jumlah_lkh') }}</dd>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-sm font-medium text-yellow-600">Total BSM</dt>
                                <dd class="text-2xl font-bold text-yellow-900">{{ $data->sum('jumlah_bsm') }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <div class="overflow-x-auto">
                        <table id="mapping-bsm-table" class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No RKH</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal RKH</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah LKH</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah BSM</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($data as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $item->rkhno }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($item->rkhdate)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->jumlah_lkh > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $item->jumlah_lkh }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->jumlah_bsm > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $item->jumlah_bsm }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button type="button" 
                                                onclick="showDetailModal('{{ $item->rkhno }}')"
                                                class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <!-- No Data Found -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Data tidak ditemukan</h3>
                    <p class="mt-1 text-sm text-gray-500">Tidak ada data RKH untuk periode tanggal yang dipilih.</p>
                </div>
                @endif
            </div>
            @else
            <!-- No Data State -->
            <div id="no-data-state" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada data</h3>
                <p class="mt-1 text-sm text-gray-500">Silakan pilih rentang tanggal dan klik "Cari Data" untuk menampilkan ringkasan data RKH.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Detail BSM Modal -->
    <div id="detail_modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDetailModal()"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                <!-- Modal header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-1">
                                Detail BSM per Surat Jalan
                            </h3>
                            <p class="text-sm text-gray-600" id="modal_detail_subtitle">
                                <!-- Detail subtitle will be filled by JavaScript -->
                            </p>
                        </div>
                        <button type="button" onclick="closeDetailModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Loading indicator -->
                    <div id="modal_loading" class="flex justify-center items-center py-8 hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-gray-600">Memuat data...</span>
                    </div>

                    <!-- Detail content -->
                    <div class="overflow-x-auto" id="modal_detail_content">
                        <!-- Content will be filled by JavaScript -->
                    </div>

                    <!-- Error message -->
                    <div id="modal_error" class="text-center py-8 hidden">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-red-900">Terjadi kesalahan</h3>
                        <p class="mt-1 text-sm text-red-600" id="error_message">Gagal memuat data BSM.</p>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeDetailModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Include DataTables CSS and JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.tailwind.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize date inputs
            initializeDateInputs();

            // Initialize DataTable if data exists
            @if(request('tanggalawal') && request('tanggalakhir') && isset($data) && count($data) > 0)
            initializeDataTable();
            @endif

            // Export buttons handlers
            $('#export-excel-btn').on('click', function() {
                if ($.fn.DataTable.isDataTable('#mapping-bsm-table')) {
                    $('#mapping-bsm-table').DataTable().button('.buttons-excel').trigger();
                }
            });

            $('#print-btn').on('click', function() {
                if ($.fn.DataTable.isDataTable('#mapping-bsm-table')) {
                    $('#mapping-bsm-table').DataTable().button('.buttons-print').trigger();
                }
            });
        });

        function initializeDateInputs() {
            const today = new Date().toISOString().split('T')[0];
            const tanggalAwal = document.getElementById('tanggalawal');
            const tanggalAkhir = document.getElementById('tanggalakhir');

            // Set max date to today
            tanggalAwal.setAttribute('max', today);
            tanggalAkhir.setAttribute('max', today);

            // Date validation
            function validateDateRange() {
                const startDate = new Date(tanggalAwal.value);
                const endDate = new Date(tanggalAkhir.value);

                if (startDate && endDate && startDate > endDate) {
                    tanggalAkhir.setCustomValidity('Tanggal akhir harus setelah tanggal awal');
                } else {
                    tanggalAkhir.setCustomValidity('');
                }
            }

            tanggalAwal.addEventListener('change', validateDateRange);
            tanggalAkhir.addEventListener('change', validateDateRange);
        }

        function initializeDataTable() {
            // Initialize DataTable
            $('#mapping-bsm-table').DataTable({
                responsive: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export Excel',
                        title: 'Ringkasan RKH - {{ request("tanggalawal") ?? "" }} s/d {{ request("tanggalakhir") ?? "" }}',
                        className: 'hidden'
                    },
                    {
                        extend: 'print',
                        text: 'Print',
                        title: 'Ringkasan Data RKH',
                        messageTop: 'Periode: {{ request("tanggalawal") ?? "" }} s/d {{ request("tanggalakhir") ?? "" }}',
                        className: 'hidden'
                    }
                ],
                columnDefs: [
                    {
                        targets: [0, 3, 4, 5], // No, Jumlah LKH, Jumlah BSM, Aksi
                        className: 'text-center'
                    },
                    {
                        targets: [5], // Aksi column
                        orderable: false
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                drawCallback: function() {
                    // Custom styling after each draw
                    $('.dataTables_wrapper .dataTables_paginate .paginate_button').addClass('px-3 py-2 ml-0 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700');
                }
            });
        }

        function showDetailModal(rkhno) {
            // Show modal
            document.getElementById('detail_modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            
            // Update modal subtitle
            document.getElementById('modal_detail_subtitle').textContent = `RKH No: ${rkhno}`;
            
            // Show loading state
            document.getElementById('modal_loading').classList.remove('hidden');
            document.getElementById('modal_detail_content').innerHTML = '';
            document.getElementById('modal_error').classList.add('hidden');
            
            // Fetch BSM detail data via AJAX
            fetch(`{{ route('input.mapping-bsm.get-bsm-detail') }}?rkhno=${encodeURIComponent(rkhno)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Hide loading
                document.getElementById('modal_loading').classList.add('hidden');
                
                if (data.success && data.data && data.data.length > 0) {
                    // Build table content
                    const tableHTML = `
                        <div class="mb-4">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-600">
                                    Total: <span class="font-medium text-indigo-600">${data.total}</span> surat jalan ditemukan
                                </div>
                                <div class="text-sm text-gray-500">
                                    Data BSM untuk RKH: <span class="font-medium">${rkhno}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Surat Jalan</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plot</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Bersih</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Segar</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Manis</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Average Score</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    ${data.data.map((item, index) => `
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">${index + 1}</td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                                ${item.suratjalanno || '-'}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                ${item.plot || '-'}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ${parseFloat(item.nilaibersih || 0).toFixed(2)}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ${parseFloat(item.nilaisegar || 0).toFixed(2)}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ${parseFloat(item.nilaimanis || 0).toFixed(2)}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getScoreColor(item.averagescore)}">
                                                    ${parseFloat(item.averagescore || 0).toFixed(2)}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeColor(item.grade)}">
                                                    ${item.grade || '-'}
                                                </span>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        
                        ${data.data.length > 0 ? `
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                    <div class="text-center">
                                        <div class="font-medium text-gray-500">Rata-rata Nilai Bersih</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            ${(data.data.reduce((sum, item) => sum + parseFloat(item.nilaibersih || 0), 0) / data.data.length).toFixed(2)}
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-medium text-gray-500">Rata-rata Nilai Segar</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            ${(data.data.reduce((sum, item) => sum + parseFloat(item.nilaisegar || 0), 0) / data.data.length).toFixed(2)}
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-medium text-gray-500">Rata-rata Nilai Manis</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            ${(data.data.reduce((sum, item) => sum + parseFloat(item.nilaimanis || 0), 0) / data.data.length).toFixed(2)}
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <div class="font-medium text-gray-500">Rata-rata Score</div>
                                        <div class="text-lg font-bold text-gray-900">
                                            ${(data.data.reduce((sum, item) => sum + parseFloat(item.averagescore || 0), 0) / data.data.length).toFixed(2)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                    `;
                    
                    document.getElementById('modal_detail_content').innerHTML = tableHTML;
                } else {
                    // Show no data message
                    document.getElementById('modal_detail_content').innerHTML = `
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v10z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data BSM</h3>
                            <p class="mt-1 text-sm text-gray-500">Tidak ditemukan data BSM untuk RKH: ${rkhno}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                // Hide loading
                document.getElementById('modal_loading').classList.add('hidden');
                
                // Show error
                document.getElementById('modal_error').classList.remove('hidden');
                document.getElementById('error_message').textContent = error.message || 'Terjadi kesalahan saat memuat data BSM';
                
                console.error('Error fetching BSM detail:', error);
            });
        }

        function getGradeColor(grade) {
            if (!grade) return 'bg-gray-100 text-gray-800';
            
            switch(grade.toUpperCase()) {
                case 'A':
                    return 'bg-green-100 text-green-800';
                case 'B':
                    return 'bg-yellow-100 text-yellow-800';
                case 'C':
                    return 'bg-red-100 text-red-800';
                default:
                    return 'bg-gray-100 text-gray-800';
            }
        }

        function getScoreColor(score) {
            const numScore = parseFloat(score || 0);
            if (numScore >= 80) {
                return 'bg-green-100 text-green-800';
            } else if (numScore >= 60) {
                return 'bg-yellow-100 text-yellow-800';
            } else if (numScore > 0) {
                return 'bg-red-100 text-red-800';
            } else {
                return 'bg-gray-100 text-gray-800';
            }
        }

        function closeDetailModal() {
            document.getElementById('detail_modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            
            // Clear modal content
            document.getElementById('modal_detail_content').innerHTML = '';
            document.getElementById('modal_loading').classList.add('hidden');
            document.getElementById('modal_error').classList.add('hidden');
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDetailModal();
            }
        });

        // Form validation before submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const tanggalAwal = document.getElementById('tanggalawal').value;
            const tanggalAkhir = document.getElementById('tanggalakhir').value;

            if (!tanggalAwal || !tanggalAkhir) {
                e.preventDefault();
                alert('Mohon lengkapi tanggal awal dan tanggal akhir');
                return false;
            }

            if (new Date(tanggalAwal) > new Date(tanggalAkhir)) {
                e.preventDefault();
                alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
                return false;
            }

            // Show loading state on submit button
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Mencari...
            `;

            // Reset button after some time if form doesn't submit
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }, 10000);
        });
    </script>

    <style>
        /* Custom styles to match the original design */
        .transition {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
        }
        
        input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
        }
        
        /* Focus states for inputs */
        input:focus, select:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Button hover effects */
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        /* Required field indicator */
        .text-red-500 {
            color: #ef4444;
        }
        
        /* Form spacing */
        .space-y-6 > * + * {
            margin-top: 1.5rem;
        }
        
        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }

        /* DataTables custom styling */
        .dataTables_wrapper {
            font-family: inherit;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate {
            margin-top: 1rem;
        }

        /* Table styling improvements */
        #mapping-bsm-table thead th {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
            z-index: 10;
        }

        /* Modal animation */
        .fixed {
            backdrop-filter: blur(4px);
        }

        /* Scroll styling for modal */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Loading animation */
        .animate-spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        /* Modal content responsive */
        @media (max-width: 768px) {
            .modal table {
                font-size: 0.8rem;
            }
            
            .modal th, .modal td {
                padding: 0.5rem 0.25rem;
            }
        }
    </style>

</x-layout>