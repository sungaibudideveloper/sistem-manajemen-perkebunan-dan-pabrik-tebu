<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full">
        <!-- Header Form -->
        <div class="px-4 py-3 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 text-center">Berita Acara Panen Tebu Giling</h2>
        </div>

        <!-- Form Berita Acara -->
        <div class="p-6">
            <form method="POST" action="{{ route('report.panen-tebu-report.proses') }}" class="space-y-6">
                @csrf
                
                <!-- Nama Kontraktor -->
                <div class="space-y-2">
                    <label for="nama_kontraktor" class="block text-sm font-medium text-gray-700">
                        Nama Kontraktor <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <!-- Hidden input for form submission -->
                        <input type="hidden" id="nama_kontraktor" name="idkontraktor" value="{{ old('nama_kontraktor') }}" required>
                        
                        <!-- Search input -->
                        <input type="text" 
                               id="kontraktor_search" 
                               autocomplete="off"
                               placeholder="Cari dan pilih kontraktor..."
                               class="block w-full px-3 py-2 pr-10 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white text-gray-900"
                               onclick="toggleDropdown()"
                               oninput="filterOptions()">
                        
                        <!-- Dropdown arrow -->
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        
                        <!-- Dropdown options -->
                        <div id="kontraktor_dropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
                            <div class="py-1">
                                @foreach($kontraktor as $ktk)
                                <div class="option-item px-3 py-2 cursor-pointer hover:bg-gray-100 text-sm text-gray-900" 
                                     data-value="{{$ktk->id}}" 
                                     data-text="{{$ktk->namakontraktor}}"
                                     onclick="selectOption('{{$ktk->id}}', '{{$ktk->namakontraktor}}')">
                                    {{$ktk->namakontraktor}}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('nama_kontraktor')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kode Harga -->
                <div class="space-y-2">
                    <label for="kode_harga" class="block text-sm font-medium text-gray-700">
                        Kode Harga <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <select id="kode_harga" 
                                    name="kode_harga" 
                                    required
                                    onchange="toggleInfoButton()"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white text-gray-900">
                                <option value="">Pilih Kode Harga</option>
                                @foreach($tabel_harga as $harga)
                                <option value="{{$harga->kodeharga}}" {{ old('kode_harga') == $harga->kodeharga ? 'selected' : '' }}>
                                    {{$harga->kodeharga}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" 
                                id="info_detail_btn"
                                onclick="showHargaDetail()" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Info Detail
                        </button>
                    </div>
                    @error('kode_harga')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Range Tanggal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="start_date" 
                               name="start_date" 
                               required
                               value="{{ old('start_date') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('start_date')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="end_date" 
                               name="end_date" 
                               required
                               value="{{ old('end_date') }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400"
                               oninput="this.className = this.value ? 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-black' : 'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-400'">
                        @error('end_date')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-center pt-4">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate Report
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
                        <strong>Informasi:</strong> Pastikan semua field telah terisi dengan benar sebelum generate report. Report akan dibuat berdasarkan range tanggal yang dipilih.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Info Detail Harga -->
    <div id="harga_detail_modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeHargaModal()"></div>
            
            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <!-- Modal header -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg leading-6 font-bold text-gray-900 mb-1">
                                Harga Panen Tebu Giling (<span id="modal_company_code"></span>)
                            </h3>
                            <p class="text-sm text-gray-600 mb-1">
                                Periode Giling Tahun <span id="modal_periode"></span>
                            </p>
                            <p class="text-sm text-gray-600">
                                No Kode : <span id="modal_kode_harga"></span>
                            </p>
                        </div>
                        <button type="button" onclick="closeHargaModal()" class="rounded-md bg-white text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Table content -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Kategori</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">Manual<br>Rp /kg</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-300">GL Kebun<br>Rp /kg</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">GL Kontraktor<br>Rp /kg</th>
                                </tr>
                            </thead>
                            <tbody id="harga_table_body" class="bg-white divide-y divide-gray-200">
                                <!-- Content will be filled by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="closeHargaModal()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data harga untuk modal (akan diisi oleh Blade)
        const hargaData = @json($tabel_harga);

        // Searchable Select Functions
        function toggleDropdown() {
            const dropdown = document.getElementById('kontraktor_dropdown');
            dropdown.classList.toggle('hidden');
        }

        function selectOption(value, text) {
            document.getElementById('nama_kontraktor').value = value;
            document.getElementById('kontraktor_search').value = text;
            document.getElementById('kontraktor_dropdown').classList.add('hidden');
            
            // Remove validation error styling if exists
            const searchInput = document.getElementById('kontraktor_search');
            searchInput.classList.remove('border-red-500');
        }

        function filterOptions() {
            const searchInput = document.getElementById('kontraktor_search');
            const filter = searchInput.value.toLowerCase();
            const dropdown = document.getElementById('kontraktor_dropdown');
            const options = dropdown.getElementsByClassName('option-item');
            
            // Show dropdown when typing
            dropdown.classList.remove('hidden');
            
            // Filter options
            let visibleCount = 0;
            for (let i = 0; i < options.length; i++) {
                const text = options[i].textContent.toLowerCase();
                if (text.includes(filter)) {
                    options[i].style.display = 'block';
                    visibleCount++;
                } else {
                    options[i].style.display = 'none';
                }
            }
            
            // Clear selection if search doesn't match exactly
            if (visibleCount === 0 || !searchInput.value) {
                document.getElementById('nama_kontraktor').value = '';
            }
        }

        // Toggle Info Detail button
        function toggleInfoButton() {
            const select = document.getElementById('kode_harga');
            const button = document.getElementById('info_detail_btn');
            
            if (select.value) {
                button.disabled = false;
            } else {
                button.disabled = true;
            }
        }

        // Show Harga Detail Modal
        function showHargaDetail() {
            const selectedKode = document.getElementById('kode_harga').value;
            if (!selectedKode) return;

            const selectedHarga = hargaData.find(item => item.kodeharga === selectedKode);
            if (!selectedHarga) return;

            // Update modal title
            document.getElementById('modal_kode_harga').textContent = selectedKode;
            document.getElementById('modal_periode').textContent = selectedHarga.periode;
            document.getElementById('modal_company_code').textContent = selectedHarga.companycode;

            // Build table content
            const tableBody = document.getElementById('harga_table_body');
            tableBody.innerHTML = `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Tebang</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_tebang)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_tebang)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_tebang)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Muat</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_muat)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_muat)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_muat)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Angkutan</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_angkutan)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_angkutan)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_angkutan)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Fee Kontraktor</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_feekont)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_feekont)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_feekont)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Non Premi</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_nonpremi)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_nonpremi)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_nonpremi)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">BSM</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_bsm)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_bsm)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_bsm)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Tebu Sulit</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_tebusulit)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_tebusulit)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_tebusulit)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Premi Ton</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.manual_premiton)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500 border-r border-gray-300">Rp ${formatNumber(selectedHarga.glkebun_premiton)}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500">Rp ${formatNumber(selectedHarga.glkontraktor_premiton)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Extra Fooding</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500" colspan="3">Rp ${formatNumber(selectedHarga.extra_fooding)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Tebu Tidak Seset</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500" colspan="3">Rp ${formatNumber(selectedHarga.tebu_tdk_seset)}</td>
                </tr>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-300">Langsir</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-500" colspan="3">Rp ${formatNumber(selectedHarga.langsir)}</td>
                </tr>
            `;

            // Show modal
            document.getElementById('harga_detail_modal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        // Close Harga Detail Modal
        function closeHargaModal() {
            document.getElementById('harga_detail_modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Format number with thousands separator
        function formatNumber(num) {
            return parseInt(num || 0).toLocaleString('id-ID');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('kontraktor_dropdown');
            const searchInput = document.getElementById('kontraktor_search');
            
            if (!dropdown.contains(event.target) && !searchInput.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Initialize selected value on page load (for old input)
        document.addEventListener('DOMContentLoaded', function() {
            const selectedValue = document.getElementById('nama_kontraktor').value;
            const searchInput = document.getElementById('kontraktor_search');
            
            if (selectedValue) {
                const options = document.getElementsByClassName('option-item');
                for (let i = 0; i < options.length; i++) {
                    if (options[i].dataset.value === selectedValue) {
                        searchInput.value = options[i].dataset.text;
                        break;
                    }
                }
            }

            // Initialize info button state
            toggleInfoButton();

            // Existing date validation code
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function validateDateRange() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);

                if (startDate && endDate && startDate > endDate) {
                    endDateInput.setCustomValidity('Tanggal selesai harus setelah tanggal mulai');
                } else {
                    endDateInput.setCustomValidity('');
                }
            }

            startDateInput.addEventListener('change', validateDateRange);
            endDateInput.addEventListener('change', validateDateRange);

            // Set max date to today for both inputs
            const today = new Date().toISOString().split('T')[0];
            startDateInput.setAttribute('max', today);
            endDateInput.setAttribute('max', today);
        });

        // Enhanced form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const namaKontraktor = document.getElementById('nama_kontraktor').value;
            const kodeHarga = document.getElementById('kode_harga').value;
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const searchInput = document.getElementById('kontraktor_search');

            if (!namaKontraktor || !kodeHarga || !startDate || !endDate) {
                e.preventDefault();
                
                // Highlight empty fields
                if (!namaKontraktor) {
                    searchInput.classList.add('border-red-500');
                    searchInput.focus();
                }
                if (!kodeHarga) {
                    document.getElementById('kode_harga').classList.add('border-red-500');
                }
                
                alert('Mohon lengkapi semua field yang wajib diisi');
                return false;
            }

            // Additional validation for date range
            if (new Date(startDate) > new Date(endDate)) {
                e.preventDefault();
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal selesai');
                return false;
            }

            // Show loading state
            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
            `;
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeHargaModal();
            }
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

        /* Searchable select dropdown styles */
        .option-item:hover {
            background-color: #f3f4f6;
        }
        
        .option-item:active {
            background-color: #e5e7eb;
        }
        
        /* Dropdown scrollbar styling */
        #kontraktor_dropdown::-webkit-scrollbar {
            width: 6px;
        }
        
        #kontraktor_dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        #kontraktor_dropdown::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        #kontraktor_dropdown::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Error state for inputs */
        .border-red-500 {
            border-color: #ef4444 !important;
        }

        /* Ensure dropdown appears above other elements */
        .relative {
            position: relative;
        }

        /* Modal animation */
        .fixed {
            backdrop-filter: blur(4px);
        }

        /* Table styling improvements */
        table th {
            position: sticky;
            top: 0;
            background-color: #f9fafb;
        }

        /* Scroll styling for modal table */
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
    </style>

</x-layout>