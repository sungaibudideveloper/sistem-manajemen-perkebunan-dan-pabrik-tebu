{{-- resources\views\input\rencanakerjaharian\modal-index\index-modal-rekap.blade.php --}}

{{-- GENERATE REKAP LKH MODAL - UPDATED WITH 3 OPTIONS --}}
<div x-show="showGenerateRekapLKHModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showGenerateRekapLKHModal" x-transition.scale
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Generate Daily Reports</h2>
            <button @click="showGenerateRekapLKHModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6">
            <!-- Date Selection -->
            <div>
                <label for="report_date" class="block text-sm font-medium text-gray-700 mb-2">Pilih Tanggal:</label>
                <input type="date" id="report_date" x-model="rekapLkhDate"
                       @change="loadOperatorsForDate()"
                       class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-gray-500 focus:border-gray-500"/>
                <p class="text-xs text-gray-500 mt-1">Pilih tanggal untuk generate laporan</p>
            </div>

            <!-- Report Type Selection -->
            <div class="space-y-4">
                <h3 class="text-sm font-medium text-gray-700">Pilih Jenis Laporan:</h3>

                <!-- 1. Rekap LKH Option -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-400 transition-colors">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="reportType" value="rekap" x-model="selectedReportType"
                               class="mt-1 text-gray-600 focus:ring-gray-500">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-900">Rekap LKH Harian</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Ringkasan semua kegiatan LKH per tanggal termasuk pengolahan dan perawatan manual</p>
                        </div>
                    </label>
                </div>

                <!-- 2. LKH Operator Rekap Option (NEW) -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-400 transition-colors">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="reportType" value="operator_rekap" x-model="selectedReportType"
                               class="mt-1 text-gray-600 focus:ring-gray-500">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-900">Rekap Operator Unit Alat</span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-green-100 text-green-700 rounded-full">NEW</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Ringkasan semua operator alat dalam 1 tanggal dengan total jam kerja, luas, dan BBM</p>
                        </div>
                    </label>
                </div>

                <!-- 3. LKH Operator (Per Operator) Option -->
                <div class="border border-gray-200 rounded-lg p-4 hover:border-gray-400 transition-colors">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="reportType" value="operator" x-model="selectedReportType"
                               class="mt-1 text-gray-600 focus:ring-gray-500">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-gray-900">LKH Operator Unit Alat (Per Operator)</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Detail aktivitas operator alat tertentu termasuk jam kerja, plot, dan pemakaian BBM</p>
                        </div>
                    </label>

                    <!-- Operator Selection (shown when operator type is selected) -->
                    <div x-show="selectedReportType === 'operator'" x-transition class="mt-4 ml-8">
                        <label for="operator_select" class="block text-sm font-medium text-gray-700 mb-2">Pilih Operator:</label>
                        <div class="relative">
                            <select id="operator_select" x-model="selectedOperatorId"
                                    :disabled="isLoadingOperators || !rekapLkhDate"
                                    class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100">
                                <option value="">
                                    <span x-text="isLoadingOperators ? 'Loading operators...' : (rekapLkhDate ? 'Pilih operator...' : 'Pilih tanggal terlebih dahulu')"></span>
                                </option>
                                <template x-for="operator in availableOperators" :key="operator.tenagakerjaid">
                                    <option :value="operator.tenagakerjaid" x-text="`${operator.nama} - ${operator.nokendaraan} (${operator.jenis})`"></option>
                                </template>
                            </select>

                            <!-- Loading spinner -->
                            <div x-show="isLoadingOperators" class="absolute right-3 top-3">
                                <svg class="animate-spin h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Hanya menampilkan operator yang bekerja pada tanggal yang dipilih</p>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div x-show="selectedReportType" x-transition class="bg-gray-50 rounded-lg p-3">
                <div class="flex items-start space-x-2">
                    <div class="text-xs text-gray-700">
                        <template x-if="selectedReportType === 'rekap'">
                            <div>
                                <p class="font-medium">Informasi Rekap LKH:</p>
                                <p>Laporan berisi ringkasan semua LKH per tanggal termasuk aktivitas pengolahan dan perawatan manual (PC/RC).</p>
                            </div>
                        </template>
                        <template x-if="selectedReportType === 'operator_rekap'">
                            <div>
                                <p class="font-medium">Informasi Rekap Operator:</p>
                                <p>Laporan ringkasan semua operator dalam 1 hari dengan total aktivitas, jam kerja, luas area hasil, dan konsumsi BBM. Klik detail untuk melihat breakdown per operator.</p>
                            </div>
                        </template>
                        <template x-if="selectedReportType === 'operator'">
                            <div>
                                <p class="font-medium">Informasi LKH Operator:</p>
                                <p>Laporan detail aktivitas operator unit alat tertentu termasuk jam kerja, kegiatan per plot, luas area, dan konsumsi BBM.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
            <button @click="showGenerateRekapLKHModal = false; resetReportModal()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors">Cancel</button>
            <button @click="generateSelectedReport()"
                    :disabled="!canGenerateReport"
                    class="px-6 py-2 text-sm rounded-lg transition-colors"
                    :class="canGenerateReport ? 'bg-gray-800 hover:bg-gray-900 text-white' : 'bg-gray-400 text-gray-200 cursor-not-allowed'">
                Generate Report
            </button>
        </div>
    </div>
</div>