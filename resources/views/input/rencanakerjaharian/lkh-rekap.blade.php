{{-- resources\views\input\rencanakerjaharian\lkh-rekap.blade.php --}}
<x-layout>
    <x-slot:title>Rekap Laporan Kegiatan Harian (LKH)</x-slot:title>
    <x-slot:navbar>Input</x-slot:navbar>
    <x-slot:nav>Rencana Kerja Harian</x-slot:nav>

    <!-- Print-optimized container -->
    <div class="print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-2">

        <!-- Title -->
        <h1 class="text-xl font-bold text-center text-gray-800 mb-3 uppercase tracking-wider">
            Rekap Laporan Kegiatan Harian (LKH)
        </h1>

        <!-- Info Box -->
        <div class="flex justify-between items-start mb-3 p-2 bg-gray-50 rounded-lg">
            <div class="text-sm space-y-0.5">
                <div>Total LKH: <strong id="stat-total-lkh">0</strong></div>
                <div>Total Hasil: <strong id="stat-total-hasil">0.00</strong> ha</div>
                <div>Total Workers: <strong id="stat-total-workers">0</strong></div>
            </div>
            <div class="text-right text-sm text-gray-600">
                <div class="font-semibold text-gray-800">Tanggal: <span id="report-date"></span></div>
                <div>Generated: <span id="print-timestamp"></span></div>
                <div class="font-semibold text-gray-800">Divisi: <span id="company-info">Loading...</span></div>
            </div>
        </div>

        <!-- Section 1: Pengolahan Lahan (Activity I, II) -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                1. LKH Pengolahan Lahan (Activity I, II)
            </h2>
            <div id="pengolahan-section" class="text-center py-3 text-gray-500">
                Memuat data pengolahan...
            </div>
        </div>

        <!-- Section 2: Perawatan (Activity III) -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                2. LKH Perawatan Manual dan Mekanis (Activity III)
            </h2>
            
            <h3 class="text-base font-semibold text-gray-700 mb-1">
                PC (Plant Cane - 3.1.x)
            </h3>
            <div id="perawatan-pc-section" class="text-center py-3 text-gray-500 mb-2">
                Memuat data perawatan PC...
            </div>

            <h3 class="text-base font-semibold text-gray-700 mb-1">
                RC (Ratoon Cane - 3.2.x)
            </h3>
            <div id="perawatan-rc-section" class="text-center py-3 text-gray-500">
                Memuat data perawatan RC...
            </div>
        </div>

        <!-- Section 3: Panen (Activity IV) -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                3. LKH Panen (Activity IV)
            </h2>
            <div id="panen-section" class="text-center py-3 text-gray-500">
                Memuat data panen...
            </div>
        </div>

        <!-- Section 4: Pias/Hama (Activity V) -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                4. LKH Pias/Hama (Activity V)
            </h2>
            <div id="pias-section" class="text-center py-3 text-gray-500">
                Memuat data pias/hama...
            </div>
        </div>

        <!-- Section 5: Lain-lain (Activity VI, VII, VIII) -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                5. LKH Lain-lain (Activity VI, VII, VIII)
            </h2>
            <div id="lainlain-section" class="text-center py-3 text-gray-500">
                Memuat data lain-lain...
            </div>
        </div>

        <!-- Signatures -->
        <div class="mt-6 grid grid-cols-3 gap-6 text-center print:mt-8">
            <div class="p-2">
                <div class="font-semibold mb-8 text-sm">Disetujui</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">Estate Manager</div>
            </div>
            <div class="p-2">
                <div class="font-semibold mb-8 text-sm">Diperiksa</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">Asisten Kepala</div>
            </div>
            <div class="p-2">
                <div class="font-semibold mb-8 text-sm">Disiapkan</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">Asisten Lapangan</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4 flex justify-center space-x-4 no-print">
            <button 
                onclick="window.print()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print
            </button>
            
            <button 
                onclick="window.history.back()"
                class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors hover:bg-gray-50 flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </button>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const reportDate = urlParams.get('date') || new Date().toISOString().split('T')[0];
        
        document.getElementById('report-date').textContent = new Date(reportDate).toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

        async function loadLKHRekapData() {
            try {
                const response = await fetch(`{{ route('input.rencanakerjaharian.lkh-rekap-data') }}?date=${reportDate}`);
                const data = await response.json();
                
                if (data.success) {
                    updateHeaderInfo(data);
                    populateSection(data.pengolahan || {}, 'pengolahan-section', 'pengolahan');
                    populatePerawatanSection(data.perawatan || {});
                    populateSection(data.panen || {}, 'panen-section', 'panen');
                    populateSection(data.pias || {}, 'pias-section', 'pias');
                    populateSection(data.lainlain || {}, 'lainlain-section', 'lainlain');
                } else {
                    showError('Gagal memuat data LKH Rekap: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading LKH Rekap data:', error);
                showError('Terjadi kesalahan saat memuat data: ' + error.message);
            }
        }

        function updateHeaderInfo(data) {
            const printTimestamp = document.getElementById('print-timestamp');
            if (printTimestamp) printTimestamp.textContent = data.generated_at || new Date().toLocaleString('id-ID');
            
            const companyInfo = document.getElementById('company-info');
            if (companyInfo) companyInfo.textContent = data.company_info || 'N/A';
            
            updateStatistics(data);
        }

        function updateStatistics(data) {
            let totalLkh = 0, totalHasil = 0, totalWorkers = 0;

            // Count all sections
            ['pengolahan', 'panen', 'pias', 'lainlain'].forEach(section => {
                if (data[section]) {
                    Object.values(data[section]).forEach(activities => {
                        if (Array.isArray(activities)) {
                            activities.forEach(item => {
                                totalLkh++;
                                totalHasil += parseFloat(item.totalhasil || 0);
                                totalWorkers += parseInt(item.totalworkers || 0);
                            });
                        }
                    });
                }
            });

            // Perawatan has PC/RC structure
            if (data.perawatan) {
                ['pc', 'rc'].forEach(type => {
                    if (data.perawatan[type]) {
                        Object.values(data.perawatan[type]).forEach(activities => {
                            if (Array.isArray(activities)) {
                                activities.forEach(item => {
                                    totalLkh++;
                                    totalHasil += parseFloat(item.totalhasil || 0);
                                    totalWorkers += parseInt(item.totalworkers || 0);
                                });
                            }
                        });
                    }
                });
            }

            document.getElementById('stat-total-lkh').textContent = totalLkh;
            document.getElementById('stat-total-hasil').textContent = totalHasil.toFixed(2);
            document.getElementById('stat-total-workers').textContent = totalWorkers;
        }

        function populateSection(data, sectionId, type) {
            const section = document.getElementById(sectionId);
            section.innerHTML = '';
            
            if (!data || Object.keys(data).length === 0) {
                section.innerHTML = '<div class="text-center py-6 text-gray-400 italic">Tidak ada data untuk tanggal yang dipilih</div>';
                return;
            }
            
            createActivityGrid(section, data, type);
        }

        function populatePerawatanSection(data) {
            populatePerawatanSubsection(data?.pc, 'perawatan-pc-section');
            populatePerawatanSubsection(data?.rc, 'perawatan-rc-section');
        }

        function populatePerawatanSubsection(data, sectionId) {
            const section = document.getElementById(sectionId);
            section.innerHTML = '';
            
            if (!data || Object.keys(data).length === 0) {
                section.innerHTML = '<div class="text-center py-6 text-gray-400 italic">Tidak ada data untuk tanggal yang dipilih</div>';
                return;
            }
            
            createActivityGrid(section, data, 'perawatan');
        }

        function createActivityGrid(section, data, type) {
            const tablesGrid = document.createElement('div');
            tablesGrid.className = 'grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 mb-3';

            Object.keys(data).forEach(activityCode => {
                const activities = data[activityCode];
                if (!Array.isArray(activities) || activities.length === 0) return;

                const activityTable = document.createElement('div');
                activityTable.className = 'bg-white border border-gray-200 rounded-lg overflow-hidden';

                // Title
                const activityTitle = document.createElement('div');
                activityTitle.className = 'bg-gray-100 px-2 py-1 text-sm font-semibold text-gray-700 border-b';
                const activityName = activities[0]?.activityname || 'Unknown Activity';
                activityTitle.textContent = `${activityCode} - ${activityName}`;
                activityTable.appendChild(activityTitle);

                // Table
                const table = document.createElement('table');
                table.className = 'w-full text-xs';
                
                // Headers based on type
                const headers = (type === 'pengolahan') 
                    ? `<thead class="bg-gray-50">
                        <tr>
                            <th class="px-1 py-0.5 text-left border-b text-gray-600 w-8">No.</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600">Mandor</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Plot</th>
                            <th class="px-1 py-0.5 text-right border-b text-gray-600 w-12">Hasil</th>
                        </tr>
                    </thead>`
                    : (type === 'perawatan')
                    ? `<thead class="bg-gray-50">
                        <tr>
                            <th class="px-1 py-0.5 text-left border-b text-gray-600 w-8">No.</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600">Operator</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Plot</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Luas</th>
                            <th class="px-1 py-0.5 text-right border-b text-gray-600 w-12">Hasil</th>
                        </tr>
                    </thead>`
                    : `<thead class="bg-gray-50">
                        <tr>
                            <th class="px-1 py-0.5 text-left border-b text-gray-600 w-8">No.</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600">Info</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Plot</th>
                            <th class="px-1 py-0.5 text-right border-b text-gray-600 w-12">Hasil</th>
                        </tr>
                    </thead>`;

                table.innerHTML = headers + '<tbody></tbody>';
                const tbody = table.querySelector('tbody');
                let totalHasil = 0;

                // Rows
                activities.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    
                    const hasil = parseFloat(item.totalhasil || 0);
                    totalHasil += hasil;
                    const plotDisplay = item.plot || '-';

                    if (type === 'pengolahan') {
                        const mandorName = item.mandor_nama || '-';
                        row.innerHTML = `
                            <td class="px-1 py-0.5 border-b text-center">${index + 1}</td>
                            <td class="px-1 py-0.5 border-b font-medium text-xs">${mandorName}</td>
                            <td class="px-1 py-0.5 border-b text-center">${plotDisplay}</td>
                            <td class="px-1 py-0.5 border-b text-right">${hasil.toFixed(2)}</td>
                        `;
                    } else if (type === 'perawatan') {
                        const operatorName = item.operator_nama || '-';
                        const luasArea = item.luasarea ? parseFloat(item.luasarea).toFixed(2) : '0.00';
                        row.innerHTML = `
                            <td class="px-1 py-0.5 border-b text-center">${index + 1}</td>
                            <td class="px-1 py-0.5 border-b font-medium text-xs">${operatorName}</td>
                            <td class="px-1 py-0.5 border-b text-center">${plotDisplay}</td>
                            <td class="px-1 py-0.5 border-b text-center">${luasArea}</td>
                            <td class="px-1 py-0.5 border-b text-right">${hasil.toFixed(2)}</td>
                        `;
                    } else {
                        // Panen, Pias, Lainlain - generic format
                        const info = item.mandor_nama || item.operator_nama || '-';
                        row.innerHTML = `
                            <td class="px-1 py-0.5 border-b text-center">${index + 1}</td>
                            <td class="px-1 py-0.5 border-b font-medium text-xs">${info}</td>
                            <td class="px-1 py-0.5 border-b text-center">${plotDisplay}</td>
                            <td class="px-1 py-0.5 border-b text-right">${hasil.toFixed(2)}</td>
                        `;
                    }
                    
                    tbody.appendChild(row);
                });

                // Total row
                const totalRow = document.createElement('tr');
                totalRow.className = 'bg-gray-100 font-semibold';
                const colspanCount = (type === 'perawatan') ? 4 : 3;
                totalRow.innerHTML = `
                    <td colspan="${colspanCount}" class="px-1 py-0.5 text-center border-t-2 border-gray-400">TOTAL</td>
                    <td class="px-1 py-0.5 text-right border-t-2 border-gray-400">${totalHasil.toFixed(2)}</td>
                `;
                tbody.appendChild(totalRow);

                activityTable.appendChild(table);
                tablesGrid.appendChild(activityTable);
            });

            section.appendChild(tablesGrid);
        }

        function showError(message) {
            ['pengolahan-section', 'perawatan-pc-section', 'perawatan-rc-section', 'panen-section', 'pias-section', 'lainlain-section'].forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) section.innerHTML = `<div class="text-center py-6 text-red-500">${message}</div>`;
            });
        }

        document.addEventListener('DOMContentLoaded', function() { loadLKHRekapData(); });
    </script>
</x-layout>