{{-- resources\views\input\rencanakerjaharian\indexmodal\index-modal-absen.blade.php --}}

{{-- ABSEN MODAL --}}
<div x-show="showAbsenModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showAbsenModal" x-transition.scale
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
            <h2 class="text-lg font-semibold text-gray-900">Data Absen Tenaga Kerja</h2>
            <div class="flex items-center space-x-2">
                <label for="absen_date" class="text-sm font-medium">Tanggal:</label>
                <input type="date" id="absen_date" x-model="absenDate" @change="loadAbsenData(absenDate)"
                       class="text-sm border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"/>
            </div>
            <button @click="showAbsenModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <!-- Filters -->
        <div class="flex items-center justify-between p-4 border-b bg-gray-50">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <label for="mandor_filter" class="text-sm font-medium">Mandor:</label>
                    <select id="mandor_filter" x-model="selectedMandor" @change="loadAbsenData(absenDate, selectedMandor)"
                            class="text-sm border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Mandor</option>
                        <template x-for="mandor in mandorList" :key="mandor.id">
                            <option :value="mandor.id" x-text="mandor.name"></option>
                        </template>
                    </select>
                </div>
            </div>
            
            <div class="text-sm text-gray-600">
                <span class="font-medium">Total:</span> <span x-text="absenList.length"></span> orang
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isAbsenLoading" class="p-6 text-center">
            <div class="loading-spinner mx-auto"></div>
            <p class="mt-2 text-gray-500 loading-dots">Loading absen data</p>
        </div>

        <!-- Body -->
        <div x-show="!isAbsenLoading" class="p-4 overflow-hidden flex-grow">
            <div class="overflow-x-auto">
                <div class="max-h-[400px] overflow-y-auto">
                    <table class="min-w-full table-auto text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-left">ID</th>
                                <th class="px-3 py-2 text-left">Nama</th>
                                <th class="px-3 py-2 text-center">Gender</th>
                                <th class="px-3 py-2 text-left">Jenis TK</th>
                                <th class="px-3 py-2 text-left">Mandor</th>
                                <th class="px-3 py-2 text-center">Jam Absen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="person in absenList" :key="person.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="border px-3 py-2 font-mono text-xs" x-text="person.id"></td>
                                    <td class="border px-3 py-2" x-text="person.nama"></td>
                                    <td class="border px-3 py-2 text-center">
                                        <span class="px-2 py-1 text-xs rounded-full"
                                              :class="person.gender === 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'"
                                              x-text="person.gender === 'L' ? 'L' : 'P'"></span>
                                    </td>
                                    <td class="border px-3 py-2" x-text="person.jenistenagakerja_nama"></td>
                                    <td class="border px-3 py-2" x-text="person.mandor_nama"></td>
                                    <td class="border px-3 py-2 text-center font-mono text-xs" x-text="person.jam_absen"></td>
                                </tr>
                            </template>
                            <tr x-show="absenList.length === 0">
                                <td colspan="6" class="border px-3 py-8 text-center text-gray-500">
                                    Tidak ada data absen yang tersedia untuk tanggal ini
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div x-show="!isAbsenLoading" class="flex justify-between items-center p-4 border-t bg-gray-50">
            <div class="flex items-center space-x-6 text-sm">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 bg-blue-100 rounded-full"></span>
                    <span>Laki-laki: <span class="font-medium" x-text="absenList.filter(p => p.gender==='L').length"></span></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 bg-pink-100 rounded-full"></span>
                    <span>Perempuan: <span class="font-medium" x-text="absenList.filter(p => p.gender==='P').length"></span></span>
                </div>
                <div class="font-medium">
                    Total: <span x-text="absenList.length"></span> orang
                </div>
            </div>
            <button @click="showAbsenModal = false"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded">Close</button>
        </div>
    </div>
</div>