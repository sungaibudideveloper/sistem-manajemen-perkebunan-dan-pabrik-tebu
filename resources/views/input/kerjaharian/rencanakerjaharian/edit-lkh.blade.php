<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- VALIDATION ERROR MODAL -->
    <div x-data="{ showValidationModal: false, validationErrors: [] }" 
         x-show="showValidationModal" 
         x-cloak
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
         style="display: none;"
         @validation-error.window="showValidationModal = true; validationErrors = $event.detail.errors">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            <div class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Form Belum Lengkap</h3>
                <p class="text-sm text-gray-600 mb-4">Mohon lengkapi field yang diperlukan:</p>
                
                <div class="text-left bg-red-50 rounded-lg p-3 mb-4 max-h-48 overflow-y-auto">
                    <ul class="text-sm text-red-700 space-y-1">
                        <template x-for="error in validationErrors" :key="error">
                            <li x-text="error"></li>
                        </template>
                    </ul>
                </div>

                <button @click="showValidationModal = false"
                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    OK, Saya Mengerti
                </button>
            </div>
        </div>
    </div>

    <!-- SUCCESS/ERROR MODAL -->
    <div x-data="{ showModal: false, modalType: '', modalMessage: '' }" 
         x-show="showModal" 
         x-cloak
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
         style="display: none;">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
            <!-- Success Modal -->
            <div x-show="modalType === 'success'" class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Berhasil!</h3>
                <p class="text-sm text-gray-600 mb-4" x-html="modalMessage"></p>
                <button @click="window.location.href = '{{ route('input.kerjaharian.rencanakerjaharian.showLKH', $lkhData->lkhno) }}'"
                        class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    OK
                </button>
            </div>

            <!-- Error Modal -->
            <div x-show="modalType === 'error'" class="p-6 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Terjadi Kesalahan</h3>
                <p class="text-sm text-gray-600 mb-4" x-text="modalMessage"></p>
                <button @click="showModal = false"
                        class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <form id="lkh-form" action="{{ route('input.kerjaharian.rencanakerjaharian.updateLKH', $lkhData->lkhno) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Header Section - Read Only -->
        <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-blue-100">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">
                        EDIT LAPORAN KEGIATAN HARIAN (LKH)
                        @if($lkhData->jenistenagakerja == 1)
                            - TENAGA HARIAN
                        @else
                            - TENAGA BORONGAN
                        @endif
                    </h1>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">No. LKH: <span class="font-mono font-semibold">{{ $lkhData->lkhno }}</span></p>
                        <p class="text-sm text-gray-600">No. RKH: <span class="font-mono font-semibold">{{ $lkhData->rkhno }}</span></p>
                        <p class="text-sm text-gray-600">Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('l, d F Y') }}</span></p>
                        <p class="text-sm text-gray-600">Aktivitas: <span class="font-semibold">{{ $lkhData->activitycode }} - {{ $lkhData->activityname ?? '' }}</span></p>
                        <p class="text-sm text-gray-600">Mandor: <span class="font-semibold">{{ $lkhData->mandornama ?? $lkhData->mandorid }}</span></p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">Edit Mode</span>
                </div>
            </div>
        </div>

        <!-- Keterangan Section -->
        <div class="bg-white rounded-lg p-6 mb-8 border border-gray-200 shadow-sm">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                <textarea 
                    name="keterangan" 
                    rows="3" 
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Masukkan keterangan LKH..."
                >{{ old('keterangan', $lkhData->keterangan) }}</textarea>
            </div>
        </div>

        <!-- Detail Workers Table -->
        <div class="bg-white rounded-xl p-6 border border-gray-300 shadow-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-gray-800">Detail Pekerja</h3>
                <button type="button" id="addWorkerBtn" class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Pekerja
                </button>
            </div>

            <div class="overflow-x-auto">
                <table id="workers-table" class="table-fixed w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
                    <colgroup>
                        <col style="width: 32px"><!-- No -->
                        <col style="width: 48px"><!-- Blok -->
                        <col style="width: 48px"><!-- Plot -->
                        <col style="width: 180px"><!-- Nama -->
                        <col style="width: 120px"><!-- NIK -->
                        <col style="width: 60px"><!-- Luas Plot -->
                        <col style="width: 60px"><!-- Hasil -->
                        <col style="width: 60px"><!-- Sisa -->
                        <col style="width: 80px"><!-- Material -->
                        @if($lkhData->jenistenagakerja == 1)
                            <col style="width: 60px"><!-- Jam Masuk -->
                            <col style="width: 60px"><!-- Jam Selesai -->
                            <col style="width: 50px"><!-- Overtime -->
                            <col style="width: 80px"><!-- Premi -->
                            <col style="width: 80px"><!-- Upah Harian -->
                        @else
                            <col style="width: 80px"><!-- Cost/Ha -->
                        @endif
                        <col style="width: 60px"><!-- Aksi -->
                    </colgroup>

                    <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
                        <tr>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">No</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Blok</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Plot</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Nama Pekerja</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">NIK</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Luas Plot</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Hasil</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Sisa</th>
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Material</th>
                            @if($lkhData->jenistenagakerja == 1)
                                <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Jam Masuk</th>
                                <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Jam Selesai</th>
                                <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Overtime (h)</th>
                                <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Premi</th>
                                <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Upah Harian</th>
                            @else
                                <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Cost/Ha</th>
                            @endif
                            <th class="border border-gray-300 px-2 py-3 text-xs font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="workersTableBody" class="divide-y divide-gray-100">
                        @foreach($lkhDetails as $index => $worker)
                        <tr class="worker-row hover:bg-blue-50 transition-colors" data-row="{{ $index }}">
                            <td class="border border-gray-300 px-2 py-3 text-center text-sm font-medium text-gray-600 bg-gray-50">{{ $index + 1 }}</td>
                            
                            <!-- Blok -->
                            <td class="border border-gray-300 px-2 py-3">
                                <div class="relative">
                                    <input type="hidden" name="workers[{{ $index }}][id]" value="{{ $worker->id }}">
                                    <input type="hidden" name="workers[{{ $index }}][blok]" value="{{ $worker->blok }}" class="blok-value">
                                    <button type="button" class="select-blok-btn w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-row="{{ $index }}">
                                        <span class="blok-display">{{ $worker->blok }}</span>
                                    </button>
                                </div>
                            </td>
                            
                            <!-- Plot -->
                            <td class="border border-gray-300 px-2 py-3">
                                <div class="relative">
                                    <input type="hidden" name="workers[{{ $index }}][plot]" value="{{ $worker->plot }}" class="plot-value">
                                    <button type="button" class="select-plot-btn w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-row="{{ $index }}">
                                        <span class="plot-display">{{ $worker->plot }}</span>
                                    </button>
                                </div>
                            </td>
                            
                            <!-- Nama Pekerja -->
                            <td class="border border-gray-300 px-2 py-3">
                                <div class="relative">
                                    <input type="hidden" name="workers[{{ $index }}][idtenagakerja]" value="{{ $worker->idtenagakerja }}" class="worker-id">
                                    <button type="button" class="select-worker-btn w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-left cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-row="{{ $index }}">
                                        <span class="worker-name">{{ $worker->workername ?? 'Pilih Pekerja' }}</span>
                                    </button>
                                </div>
                            </td>
                            
                            <!-- NIK -->
                            <td class="border border-gray-300 px-2 py-3">
                                <input type="text" class="worker-nik w-full px-3 py-2 text-sm bg-gray-100 border-2 border-gray-300 rounded-lg cursor-not-allowed" value="{{ $worker->noktp }}" readonly>
                            </td>
                            
                            <!-- Luas Plot -->
                            <td class="border border-gray-300 px-2 py-3">
                                <input type="number" name="workers[{{ $index }}][luasplot]" value="{{ $worker->luasplot }}" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </td>
                            
                            <!-- Hasil -->
                            <td class="border border-gray-300 px-2 py-3">
                                <input type="number" name="workers[{{ $index }}][hasil]" value="{{ $worker->hasil }}" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hasil-input">
                            </td>
                            
                            <!-- Sisa -->
                            <td class="border border-gray-300 px-2 py-3">
                                <input type="number" name="workers[{{ $index }}][sisa]" value="{{ $worker->sisa }}" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sisa-input">
                            </td>
                            
                            <!-- Material -->
                            <td class="border border-gray-300 px-2 py-3">
                                <input type="text" name="workers[{{ $index }}][materialused]" value="{{ $worker->materialused }}" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </td>
                            
                            @if($lkhData->jenistenagakerja == 1)
                                <!-- Jam Masuk -->
                                <td class="border border-gray-300 px-2 py-3">
                                    <input type="time" name="workers[{{ $index }}][jammasuk]" value="{{ $worker->jammasuk }}" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                
                                <!-- Jam Selesai -->
                                <td class="border border-gray-300 px-2 py-3">
                                    <input type="time" name="workers[{{ $index }}][jamselesai]" value="{{ $worker->jamselesai }}" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                
                                <!-- Overtime -->
                                <td class="border border-gray-300 px-2 py-3">
                                    <input type="number" name="workers[{{ $index }}][overtimehours]" value="{{ $worker->overtimehours }}" step="0.1" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                
                                <!-- Premi -->
                                <td class="border border-gray-300 px-2 py-3">
                                    <input type="number" name="workers[{{ $index }}][premi]" value="{{ $worker->premi }}" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </td>
                                
                                <!-- Upah Harian -->
                                <td class="border border-gray-300 px-2 py-3">
                                    <input type="number" name="workers[{{ $index }}][upahharian]" value="{{ $worker->upahharian }}" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </td>
                            @else
                                <!-- Cost/Ha -->
                                <td class="border border-gray-300 px-2 py-3">
                                    <input type="number" name="workers[{{ $index }}][costperha]" value="{{ $worker->costperha }}" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cost-input">
                                </td>
                            @endif
                            
                            <!-- Aksi -->
                            <td class="border border-gray-300 px-2 py-3 text-center">
                                <button type="button" class="remove-worker-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors" data-row="{{ $index }}">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col items-center space-y-4">
            <!-- Secondary Actions -->
            <div class="flex justify-center space-x-4">
                <button type="button" onclick="window.history.back()" class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Batal
                </button>
            </div>
            
            <!-- Primary Submit Button -->
            <button type="submit" id="submit-btn" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-12 py-4 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="submit-icon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg class="animate-spin w-5 h-5 mr-2 hidden" fill="none" viewBox="0 0 24 24" id="loading-spinner">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span id="submit-text">Update LKH</span>
            </button>
        </div>
    </form>

    <!-- Modal Pilih Pekerja -->
    <div id="workerModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-96 overflow-hidden">
                <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Pilih Pekerja</h3>
                    <button type="button" id="closeWorkerModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <input type="text" id="workerSearch" placeholder="Cari nama pekerja..." class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg mb-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="overflow-y-auto max-h-64" id="workerList">
                        <!-- Worker list will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilih Blok -->
    <div id="blokModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Pilih Blok</h3>
                    <button type="button" id="closeBlokModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <div class="overflow-y-auto max-h-64" id="blokList">
                        <!-- Blok list will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pilih Plot -->
    <div id="plotModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="flex justify-between items-center p-4 border-b bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-800">Pilih Plot</h3>
                    <button type="button" id="closePlotModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <div class="overflow-y-auto max-h-64" id="plotList">
                        <!-- Plot list will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentRow = 0;
        let rowCounter = {{ count($lkhDetails) }};

        // Data dari server
        const tenagaKerjaData = @json($tenagaKerja);
        const bloksData = @json($bloks);
        const plotsData = @json($plots);

        // Modal handlers
        document.addEventListener('DOMContentLoaded', function() {
            // Event listeners
            document.addEventListener('click', function(e) {
                if (e.target.closest('.select-worker-btn')) {
                    currentRow = e.target.closest('.select-worker-btn').dataset.row;
                    openWorkerModal();
                }
                if (e.target.closest('.select-blok-btn')) {
                    currentRow = e.target.closest('.select-blok-btn').dataset.row;
                    openBlokModal();
                }
                if (e.target.closest('.select-plot-btn')) {
                    currentRow = e.target.closest('.select-plot-btn').dataset.row;
                    openPlotModal();
                }
                if (e.target.closest('.remove-worker-btn')) {
                    removeWorker(e.target.closest('.remove-worker-btn').dataset.row);
                }
            });

            // Close modals
            document.getElementById('closeWorkerModal').addEventListener('click', closeWorkerModal);
            document.getElementById('closeBlokModal').addEventListener('click', closeBlokModal);
            document.getElementById('closePlotModal').addEventListener('click', closePlotModal);

            // Add worker button
            document.getElementById('addWorkerBtn').addEventListener('click', addWorker);

            // Auto calculate for borongan
            @if($lkhData->jenistenagakerja == 2)
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('hasil-input') || e.target.classList.contains('cost-input')) {
                    calculateBorongan(e.target.closest('tr'));
                }
            });
            @endif

            // Form submission
            document.getElementById('lkh-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm();
            });
        });

        function openWorkerModal() {
            document.getElementById('workerModal').classList.remove('hidden');
            loadWorkers();
        }

        function closeWorkerModal() {
            document.getElementById('workerModal').classList.add('hidden');
        }

        function openBlokModal() {
            document.getElementById('blokModal').classList.remove('hidden');
            loadBloks();
        }

        function closeBlokModal() {
            document.getElementById('blokModal').classList.add('hidden');
        }

        function openPlotModal() {
            document.getElementById('plotModal').classList.remove('hidden');
            loadPlots();
        }

        function closePlotModal() {
            document.getElementById('plotModal').classList.add('hidden');
        }

        function loadWorkers() {
            const workerList = document.getElementById('workerList');
            workerList.innerHTML = '';
            
            tenagaKerjaData.forEach(worker => {
                const workerDiv = document.createElement('div');
                workerDiv.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-200 transition-colors worker-item';
                workerDiv.innerHTML = `
                    <div class="font-medium text-gray-800">${worker.nama}</div>
                    <div class="text-sm text-gray-600">ID: ${worker.idtenagakerja} | NIK: ${worker.nik || '-'}</div>
                `;
                workerDiv.addEventListener('click', () => selectWorker(worker));
                workerList.appendChild(workerDiv);
            });
        }

        function loadBloks() {
            const blokList = document.getElementById('blokList');
            blokList.innerHTML = '';
            
            bloksData.forEach(blok => {
                const blokDiv = document.createElement('div');
                blokDiv.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-200 transition-colors font-medium text-center';
                blokDiv.textContent = blok.blok;
                blokDiv.addEventListener('click', () => selectBlok(blok.blok));
                blokList.appendChild(blokDiv);
            });
        }

        function loadPlots() {
            const plotList = document.getElementById('plotList');
            plotList.innerHTML = '';
            
            plotsData.forEach(plot => {
                const plotDiv = document.createElement('div');
                plotDiv.className = 'p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-200 transition-colors';
                plotDiv.innerHTML = `
                    <div class="font-medium text-gray-800">${plot.plot}</div>
                `;
                plotDiv.addEventListener('click', () => selectPlot(plot.plot));
                plotList.appendChild(plotDiv);
            });
        }

        function selectWorker(worker) {
            const row = document.querySelector(`[data-row="${currentRow}"]`);
            row.querySelector('.worker-id').value = worker.idtenagakerja;
            row.querySelector('.worker-name').textContent = worker.nama;
            row.querySelector('.worker-nik').value = worker.nik || '';
            closeWorkerModal();
        }

        function selectBlok(blok) {
            const row = document.querySelector(`[data-row="${currentRow}"]`);
            row.querySelector('.blok-value').value = blok;
            row.querySelector('.blok-display').textContent = blok;
            closeBlokModal();
        }

        function selectPlot(plot) {
            const row = document.querySelector(`[data-row="${currentRow}"]`);
            row.querySelector('.plot-value').value = plot;
            row.querySelector('.plot-display').textContent = plot;
            closePlotModal();
        }

        function addWorker() {
            const tbody = document.getElementById('workersTableBody');
            const newRow = createWorkerRow(rowCounter);
            tbody.appendChild(newRow);
            rowCounter++;
            updateRowNumbers();
        }

        function createWorkerRow(index) {
            const tr = document.createElement('tr');
            tr.className = 'worker-row hover:bg-blue-50 transition-colors';
            tr.dataset.row = index;
            
            const jenisHarian = {{ $lkhData->jenistenagakerja == 1 ? 'true' : 'false' }};
            
            tr.innerHTML = `
                <td class="border border-gray-300 px-2 py-3 text-center text-sm font-medium text-gray-600 bg-gray-50">${index + 1}</td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="hidden" name="workers[${index}][blok]" value="" class="blok-value">
                    <button type="button" class="select-blok-btn w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-row="${index}">
                        <span class="blok-display">Pilih Blok</span>
                    </button>
                </td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="hidden" name="workers[${index}][plot]" value="" class="plot-value">
                    <button type="button" class="select-plot-btn w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-row="${index}">
                        <span class="plot-display">Pilih Plot</span>
                    </button>
                </td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="hidden" name="workers[${index}][idtenagakerja]" value="" class="worker-id">
                    <button type="button" class="select-worker-btn w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-left cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" data-row="${index}">
                        <span class="worker-name">Pilih Pekerja</span>
                    </button>
                </td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="text" class="worker-nik w-full px-3 py-2 text-sm bg-gray-100 border-2 border-gray-300 rounded-lg cursor-not-allowed" readonly>
                </td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="number" name="workers[${index}][luasplot]" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="number" name="workers[${index}][hasil]" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hasil-input">
                </td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="number" name="workers[${index}][sisa]" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sisa-input">
                </td>
                <td class="border border-gray-300 px-2 py-3">
                    <input type="text" name="workers[${index}][materialused]" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </td>
                ${jenisHarian ? `
                    <td class="border border-gray-300 px-2 py-3">
                        <input type="time" name="workers[${index}][jammasuk]" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-3">
                        <input type="time" name="workers[${index}][jamselesai]" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-3">
                        <input type="number" name="workers[${index}][overtimehours]" step="0.1" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-3">
                        <input type="number" name="workers[${index}][premi]" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-3">
                        <input type="number" name="workers[${index}][upahharian]" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                ` : `
                    <td class="border border-gray-300 px-2 py-3">
                        <input type="number" name="workers[${index}][costperha]" step="0.01" class="w-full px-3 py-2 text-sm border-2 border-gray-200 rounded-lg text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cost-input">
                    </td>
                `}
                <td class="border border-gray-300 px-2 py-3 text-center">
                    <button type="button" class="remove-worker-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors" data-row="${index}">
                        Hapus
                    </button>
                </td>
            `;
            
            return tr;
        }

        function removeWorker(rowIndex) {
            const row = document.querySelector(`[data-row="${rowIndex}"]`);
            if (row && document.querySelectorAll('.worker-row').length > 1) {
                row.remove();
                updateRowNumbers();
            }
        }

        function updateRowNumbers() {
            const rows = document.querySelectorAll('.worker-row');
            rows.forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
            });
        }

        @if($lkhData->jenistenagakerja == 2)
        function calculateBorongan(row) {
            const hasil = parseFloat(row.querySelector('.hasil-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const total = hasil * cost;
            console.log('Total biaya borongan:', total);
        }
        @endif

        // Worker search functionality
        document.getElementById('workerSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const workerItems = document.querySelectorAll('.worker-item');
            
            workerItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Form submission
        function submitForm() {
            const submitBtn = document.getElementById('submit-btn');
            const submitIcon = document.getElementById('submit-icon');
            const loadingSpinner = document.getElementById('loading-spinner');
            const submitText = document.getElementById('submit-text');

            // Show loading state
            submitBtn.disabled = true;
            submitIcon.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
            submitText.textContent = 'Updating...';

            // Submit form
            document.getElementById('lkh-form').submit();
        }
    </script>
</x-layout>