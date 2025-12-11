{{-- resources\views\input\rencanakerjaharian\indexmodal\index-modal-dth.blade.php --}}

{{-- GENERATE DTH MODAL --}}
<div x-show="showGenerateDTHModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showGenerateDTHModal" x-transition.scale
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/3">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-gray-50 to-blue-50">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Generate DTH</h2>
            </div>
            <button @click="showGenerateDTHModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <div>
                <label for="dth_date" class="block text-sm font-medium text-gray-700 mb-2">Pilih Tanggal:</label>
                <input type="date" id="dth_date" x-model="dthDate"
                       class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"/>
                <p class="text-xs text-gray-500 mt-1">Pilih tanggal untuk generate Daftar Tenaga Harian (DTH)</p>
            </div>
            
            <div class="bg-blue-50 p-3 rounded-lg">
                <div class="flex items-start space-x-2">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-xs text-blue-700">
                        <p class="font-medium">Informasi DTH:</p>
                        <p>DTH akan berisi daftar tenaga kerja harian beserta detail absensi dan upah per tanggal yang dipilih.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
            <button @click="showGenerateDTHModal = false"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors">Cancel</button>
            <button @click="generateDTH()" :disabled="!dthDate"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 text-sm rounded-lg transition-colors">Generate</button>
        </div>
    </div>
</div>