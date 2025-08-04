{{-- resources\views\input\rencanakerjaharian\indexmodal\index-modal-create-rkh.blade.php --}}

{{-- DATE SELECTION MODAL FOR CREATE RKH --}}
<div x-show="showDateModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showDateModal" x-transition.scale class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/3">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-green-50 to-emerald-50">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Create RKH Baru</h2>
            </div>
            <button @click="showDateModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <div>
                <label for="create_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal RKH:</label>
                <input type="date" id="create_date" x-model="createDate" :min="today" :max="maxDate"
                       class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"/>
                <p class="text-xs text-gray-500 mt-1">Pilih tanggal untuk membuat RKH (maksimal 7 hari ke depan)</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
            <button @click="showDateModal = false" 
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors">Cancel</button>
            <button @click="proceedToCreate()" :disabled="!createDate"
                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 text-sm rounded-lg transition-colors">Lanjutkan</button>
        </div>
    </div>
</div>