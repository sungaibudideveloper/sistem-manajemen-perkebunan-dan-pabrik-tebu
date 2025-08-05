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
                <button @click="window.location.href = '{{ route('input.rencanakerjaharian.showLKH', $lkhData->lkhno) }}'"
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

    <form id="lkh-form" action="{{ route('input.rencanakerjaharian.updateLKH', $lkhData->lkhno) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Header Section - Read Only -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-xl font-bold text-gray-800 mb-2">
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
                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Edit Mode</span>
                </div>
            </div>
        </div>

        <!-- Keterangan Section -->
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                <textarea 
                    name="keterangan" 
                    rows="3" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                    placeholder="Masukkan keterangan LKH..."
                >{{ old('keterangan', $lkhData->keterangan) }}</textarea>
            </div>
        </div>

        <!-- Plot Details Section - NEW -->
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Plot</h3>
                <button type="button" id="addPlotBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Plot
                </button>
            </div>

            <div class="overflow-x-auto">
                <table id="plots-table" class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-12">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Blok</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Plot</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas RKH</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas Hasil</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas Sisa</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="plotsTableBody">
                        @foreach($lkhPlotDetails as $index => $plot)
                        <tr class="plot-row">
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="text" name="plots[{{ $index }}][blok]" value="{{ $plot->blok }}" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="text" name="plots[{{ $index }}][plot]" value="{{ $plot->plot }}" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="number" name="plots[{{ $index }}][luasrkh]" value="{{ $plot->luasrkh }}" step="0.01" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500" required>
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="number" name="plots[{{ $index }}][luashasil]" value="{{ $plot->luashasil }}" step="0.01" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="number" name="plots[{{ $index }}][luassisa]" value="{{ $plot->luassisa }}" step="0.01" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <button type="button" class="remove-plot-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Worker Details Section - UPDATED -->
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Pekerja</h3>
                <button type="button" id="addWorkerBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Pekerja
                </button>
            </div>

            <div class="overflow-x-auto">
                <table id="workers-table" class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-8">No</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-40">Nama Pekerja</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-32">NIK</th>
                            @if($lkhData->jenistenagakerja == 1)
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Masuk</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Selesai</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Kerja</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Overtime</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Premi</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Harian</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Lembur</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Total Upah</th>
                            @else
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Borongan</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Total Upah</th>
                            @endif
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Keterangan</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="workersTableBody">
                        @foreach($lkhWorkerDetails as $index => $worker)
                        <tr class="worker-row">
                            <td class="border border-gray-300 px-2 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-2 py-2">
                                <select name="workers[{{ $index }}][tenagakerjaid]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                                    <option value="">Pilih Pekerja</option>
                                    @foreach($tenagaKerja as $tk)
                                        <option value="{{ $tk->tenagakerjaid }}" {{ $worker->tenagakerjaid == $tk->tenagakerjaid ? 'selected' : '' }} data-nik="{{ $tk->nik }}">
                                            {{ $tk->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="border border-gray-300 px-2 py-2">
                                <input type="text" class="worker-nik w-full px-2 py-1 text-sm bg-gray-100 border border-gray-300 rounded cursor-not-allowed" value="{{ $worker->tenagakerja->nik ?? '' }}" readonly>
                            </td>
                            @if($lkhData->jenistenagakerja == 1)
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="time" name="workers[{{ $index }}][jammasuk]" value="{{ $worker->jammasuk }}" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="time" name="workers[{{ $index }}][jamselesai]" value="{{ $worker->jamselesai }}" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][totaljamkerja]" value="{{ $worker->totaljamkerja }}" step="0.1" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][overtimehours]" value="{{ $worker->overtimehours }}" step="0.1" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][premi]" value="{{ $worker->premi }}" step="0.01" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][upahharian]" value="{{ $worker->upahharian }}" step="0.01" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][upahlembur]" value="{{ $worker->upahlembur }}" step="0.01" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][totalupah]" value="{{ $worker->totalupah }}" step="0.01" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                            @else
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][upahborongan]" value="{{ $worker->upahborongan }}" step="0.01" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                                <td class="border border-gray-300 px-2 py-2">
                                    <input type="number" name="workers[{{ $index }}][totalupah]" value="{{ $worker->totalupah }}" step="0.01" 
                                           class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                                </td>
                            @endif
                            <td class="border border-gray-300 px-2 py-2">
                                <input type="text" name="workers[{{ $index }}][keterangan]" value="{{ $worker->keterangan }}" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-center">
                                <button type="button" class="remove-worker-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                    Hapus
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Material Details Section - NEW -->
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Material</h3>
                <button type="button" id="addMaterialBtn" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Material
                </button>
            </div>

            <div class="overflow-x-auto">
                <table id="materials-table" class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-12">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-32">Item Code</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Diterima</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Sisa</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Digunakan</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Keterangan</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="materialsTableBody">
                        @foreach($lkhMaterialDetails as $index => $material)
                        <tr class="material-row">
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="text" name="materials[{{ $index }}][itemcode]" value="{{ $material->itemcode }}" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="number" name="materials[{{ $index }}][qtyditerima]" value="{{ $material->qtyditerima }}" step="0.01" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500 qty-diterima">
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="number" name="materials[{{ $index }}][qtysisa]" value="{{ $material->qtysisa }}" step="0.01" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500 qty-sisa">
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="number" value="{{ $material->qtydigunakan }}" step="0.01" readonly
                                       class="w-full px-2 py-1 text-sm bg-gray-100 border border-gray-300 rounded text-right cursor-not-allowed qty-digunakan">
                            </td>
                            <td class="border border-gray-300 px-3 py-2">
                                <input type="text" name="materials[{{ $index }}][keterangan]" value="{{ $material->keterangan }}" 
                                       class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                            </td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <button type="button" class="remove-material-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
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
        <div class="mt-8 flex justify-center space-x-4">
            <button type="button" onclick="window.history.back()" class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors hover:bg-gray-50">
                Batal
            </button>
            
            <button type="submit" id="submit-btn" class="bg-gray-700 hover:bg-gray-800 text-white px-8 py-3 rounded-lg font-medium transition-colors flex items-center disabled:opacity-50">
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

    <script>
        let plotCounter = {{ count($lkhPlotDetails) }};
        let workerCounter = {{ count($lkhWorkerDetails) }};
        let materialCounter = {{ count($lkhMaterialDetails) }};

        document.addEventListener('DOMContentLoaded', function() {
            // Add event listeners
            document.getElementById('addPlotBtn').addEventListener('click', addPlot);
            document.getElementById('addWorkerBtn').addEventListener('click', addWorker);
            document.getElementById('addMaterialBtn').addEventListener('click', addMaterial);

            // Remove buttons
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-plot-btn')) {
                    e.target.closest('tr').remove();
                    updateRowNumbers('plot');
                }
                if (e.target.classList.contains('remove-worker-btn')) {
                    e.target.closest('tr').remove();
                    updateRowNumbers('worker');
                }
                if (e.target.classList.contains('remove-material-btn')) {
                    e.target.closest('tr').remove();
                    updateRowNumbers('material');
                }
            });

            // Auto-calculate material usage
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('qty-diterima') || e.target.classList.contains('qty-sisa')) {
                    calculateMaterialUsage(e.target.closest('tr'));
                }
                // Update NIK when worker selected
                if (e.target.name && e.target.name.includes('tenagakerjaid')) {
                    const selectedOption = e.target.selectedOptions[0];
                    const nikInput = e.target.closest('tr').querySelector('.worker-nik');
                    nikInput.value = selectedOption.dataset.nik || '';
                }
            });

            // Form submission
            document.getElementById('lkh-form').addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm();
            });
        });

        function addPlot() {
            const tbody = document.getElementById('plotsTableBody');
            const newRow = createPlotRow(plotCounter);
            tbody.appendChild(newRow);
            plotCounter++;
            updateRowNumbers('plot');
        }

        function addWorker() {
            const tbody = document.getElementById('workersTableBody');
            const newRow = createWorkerRow(workerCounter);
            tbody.appendChild(newRow);
            workerCounter++;
            updateRowNumbers('worker');
        }

        function addMaterial() {
            const tbody = document.getElementById('materialsTableBody');
            const newRow = createMaterialRow(materialCounter);
            tbody.appendChild(newRow);
            materialCounter++;
            updateRowNumbers('material');
        }

        function createPlotRow(index) {
            const tr = document.createElement('tr');
            tr.className = 'plot-row';
            tr.innerHTML = `
                <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">${index + 1}</td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="text" name="plots[${index}][blok]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="text" name="plots[${index}][plot]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="number" name="plots[${index}][luasrkh]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500" required>
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="number" name="plots[${index}][luashasil]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="number" name="plots[${index}][luassisa]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                </td>
                <td class="border border-gray-300 px-3 py-2 text-center">
                    <button type="button" class="remove-plot-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Hapus</button>
                </td>
            `;
            return tr;
        }

        function createWorkerRow(index) {
            const tr = document.createElement('tr');
            tr.className = 'worker-row';
            
            const jenisHarian = {{ $lkhData->jenistenagakerja == 1 ? 'true' : 'false' }};
            const tenagaKerjaOptions = @json($tenagaKerja->map(function($tk) {
                return ['tenagakerjaid' => $tk->tenagakerjaid, 'nama' => $tk->nama, 'nik' => $tk->nik];
            }));
            
            let optionsHtml = '<option value="">Pilih Pekerja</option>';
            tenagaKerjaOptions.forEach(tk => {
                optionsHtml += `<option value="${tk.tenagakerjaid}" data-nik="${tk.nik || ''}">${tk.nama}</option>`;
            });
            
            tr.innerHTML = `
                <td class="border border-gray-300 px-2 py-2 text-center text-sm bg-gray-50">${index + 1}</td>
                <td class="border border-gray-300 px-2 py-2">
                    <select name="workers[${index}][tenagakerjaid]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td class="border border-gray-300 px-2 py-2">
                    <input type="text" class="worker-nik w-full px-2 py-1 text-sm bg-gray-100 border border-gray-300 rounded cursor-not-allowed" readonly>
                </td>
                ${jenisHarian ? `
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="time" name="workers[${index}][jammasuk]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="time" name="workers[${index}][jamselesai]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][totaljamkerja]" step="0.1" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][overtimehours]" step="0.1" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][premi]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][upahharian]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][upahlembur]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][totalupah]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                ` : `
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][upahborongan]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                    <td class="border border-gray-300 px-2 py-2">
                        <input type="number" name="workers[${index}][totalupah]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500">
                    </td>
                `}
                <td class="border border-gray-300 px-2 py-2">
                    <input type="text" name="workers[${index}][keterangan]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                </td>
                <td class="border border-gray-300 px-2 py-2 text-center">
                    <button type="button" class="remove-worker-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Hapus</button>
                </td>
            `;
            return tr;
        }

        function createMaterialRow(index) {
            const tr = document.createElement('tr');
            tr.className = 'material-row';
            tr.innerHTML = `
                <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">${index + 1}</td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="text" name="materials[${index}][itemcode]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500" required>
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="number" name="materials[${index}][qtyditerima]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500 qty-diterima">
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="number" name="materials[${index}][qtysisa]" step="0.01" class="w-full px-2 py-1 text-sm border border-gray-300 rounded text-right focus:ring-1 focus:ring-gray-500 qty-sisa">
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="number" step="0.01" readonly class="w-full px-2 py-1 text-sm bg-gray-100 border border-gray-300 rounded text-right cursor-not-allowed qty-digunakan">
                </td>
                <td class="border border-gray-300 px-3 py-2">
                    <input type="text" name="materials[${index}][keterangan]" class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-gray-500">
                </td>
                <td class="border border-gray-300 px-3 py-2 text-center">
                    <button type="button" class="remove-material-btn bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Hapus</button>
                </td>
            `;
            return tr;
        }

        function updateRowNumbers(type) {
            const rows = document.querySelectorAll(`.${type}-row`);
            rows.forEach((row, index) => {
                row.querySelector('td:first-child').textContent = index + 1;
            });
        }

        function calculateMaterialUsage(row) {
            const diterima = parseFloat(row.querySelector('.qty-diterima').value) || 0;
            const sisa = parseFloat(row.querySelector('.qty-sisa').value) || 0;
            const digunakan = Math.max(0, diterima - sisa);
            row.querySelector('.qty-digunakan').value = digunakan.toFixed(2);
        }

        function submitForm() {
            const submitBtn = document.getElementById('submit-btn');
            const submitIcon = document.getElementById('submit-icon');
            const loadingSpinner = document.getElementById('loading-spinner');
            const submitText = document.getElementById('submit-text');

            submitBtn.disabled = true;
            submitIcon.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
            submitText.textContent = 'Updating...';

            document.getElementById('lkh-form').submit();
        }
    </script>
</x-layout>