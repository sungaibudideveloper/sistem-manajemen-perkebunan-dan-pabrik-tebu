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

        <!-- Section 1: LKH Pengolahan -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                1. LKH Pengolahan (Activity II, III, IV)
            </h2>
            <div id="pengolahan-section" class="text-center py-3 text-gray-500">
                Memuat data pengolahan...
            </div>
        </div>

        <!-- Section 2: LKH Perawatan Manual dan Mekanis - UPDATED -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                2. LKH Perawatan Manual dan Mekanis (Activity V)
            </h2>
            
            <h3 class="text-base font-semibold text-gray-700 mb-1">
                PC (Plant Cane)
            </h3>
            <div id="perawatan-pc-section" class="text-center py-3 text-gray-500 mb-2">
                Memuat data perawatan PC...
            </div>

            <h3 class="text-base font-semibold text-gray-700 mb-1">
                RC (Ratoon Cane)
            </h3>
            <div id="perawatan-rc-section" class="text-center py-3 text-gray-500">
                Memuat data perawatan RC...
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

        let globalData = null;

        async function loadLKHRekapData() {
            try {
                const response = await fetch(`{{ route('input.rencanakerjaharian.lkh-rekap-data') }}?date=${reportDate}`);
                const data = await response.json();
                globalData = data;
                
                if (data.success) {
                    updateHeaderInfo(data);
                    populatePengolahanSection(data.pengolahan || {});
                    populatePerawatanSection(data.perawatan || {}); // UPDATED: Single perawatan section
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

            if (data.pengolahan) {
                Object.values(data.pengolahan).forEach(activities => {
                    if (Array.isArray(activities)) {
                        activities.forEach(item => {
                            totalLkh++; totalHasil += parseFloat(item.totalhasil || 0);
                            totalWorkers += parseInt(item.totalworkers || 0);
                        });
                    }
                });
            }

            // UPDATED: Single perawatan section with PC/RC subsections
            if (data.perawatan) {
                ['pc', 'rc'].forEach(type => {
                    if (data.perawatan[type]) {
                        Object.values(data.perawatan[type]).forEach(activities => {
                            if (Array.isArray(activities)) {
                                activities.forEach(item => {
                                    totalLkh++; totalHasil += parseFloat(item.totalhasil || 0);
                                    totalWorkers += parseInt(item.totalworkers || 0);
                                });
                            }
                        });
                    }
                });
            }

            const lkhEl = document.getElementById('stat-total-lkh');
            const hasilEl = document.getElementById('stat-total-hasil');
            const workersEl = document.getElementById('stat-total-workers');
            
            if (lkhEl) lkhEl.textContent = totalLkh;
            if (hasilEl) hasilEl.textContent = totalHasil.toFixed(2);
            if (workersEl) workersEl.textContent = totalWorkers;
        }

        function populatePengolahanSection(data) {
            const section = document.getElementById('pengolahan-section');
            section.innerHTML = '';
            if (!data || Object.keys(data).length === 0) {
                section.innerHTML = '<div class="text-center py-6 text-gray-400 italic">Tidak ada data pengolahan untuk tanggal yang dipilih</div>';
                return;
            }
            createActivityGrid(section, data, 'pengolahan');
        }

        // UPDATED: Single function for perawatan section
        function populatePerawatanSection(data) {
            populatePerawatanSubsection(data?.pc, 'perawatan-pc-section', 'PC');
            populatePerawatanSubsection(data?.rc, 'perawatan-rc-section', 'RC');
        }

        function populatePerawatanSubsection(data, sectionId, type) {
            const section = document.getElementById(sectionId);
            section.innerHTML = '';
            if (!data || Object.keys(data).length === 0) {
                section.innerHTML = `<div class="text-center py-6 text-gray-400 italic">Tidak ada data perawatan ${type} untuk tanggal yang dipilih</div>`;
                return;
            }
            createActivityGrid(section, data, 'perawatan');
        }

        // JavaScript untuk LKH Rekap
        function createActivityGrid(section, data, type) {
            const tablesGrid = document.createElement('div');
            tablesGrid.className = 'grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 mb-3';

            Object.keys(data).forEach(activityCode => {
                const activities = data[activityCode];
                if (!Array.isArray(activities) || activities.length === 0) return;

                const activityTable = document.createElement('div');
                activityTable.className = 'bg-white border border-gray-200 rounded-lg overflow-hidden';

                const activityTitle = document.createElement('div');
                activityTitle.className = 'bg-gray-100 px-2 py-1 text-sm font-semibold text-gray-700 border-b';
                const activityName = activities[0]?.activityname || 'Unknown Activity';
                activityTitle.textContent = `${activityCode} - ${activityName}`;
                activityTable.appendChild(activityTitle);

                const table = document.createElement('table');
                table.className = 'w-full text-xs';
                
                // Table headers based on type
                const headers = type === 'pengolahan' 
                    ? `
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-1 py-0.5 text-left border-b text-gray-600 w-8">No.</th>
                                <th class="px-1 py-0.5 text-center border-b text-gray-600">Mandor</th>
                                <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Plot</th>
                                <th class="px-1 py-0.5 text-right border-b text-gray-600 w-12">Hasil</th>
                            </tr>
                        </thead>
                    `
                    : `
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-1 py-0.5 text-left border-b text-gray-600 w-8">No.</th>
                                <th class="px-1 py-0.5 text-center border-b text-gray-600">Operator</th>
                                <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Plot</th>
                                <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Luas Plot</th>
                                <th class="px-1 py-0.5 text-right border-b text-gray-600 w-12">Hasil</th>
                            </tr>
                        </thead>
                    `;

                table.innerHTML = headers + '<tbody></tbody>';

                const tbody = table.querySelector('tbody');
                let totalHasil = 0;

                activities.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    
                    const hasil = parseFloat(item.totalhasil || 0);
                    totalHasil += hasil;

                    // FIXED: Plot display - consistent for both types (no blok prefix)
                    if (type === 'pengolahan') {
                        const mandorName = item.mandor_nama || '-';
                        const plotDisplay = item.plot || '-'; // FIXED: Just plot, no blok

                        row.innerHTML = `
                            <td class="px-1 py-0.5 border-b text-center">${index + 1}</td>
                            <td class="px-1 py-0.5 border-b font-medium text-xs">${mandorName}</td>
                            <td class="px-1 py-0.5 border-b text-center">${plotDisplay}</td>
                            <td class="px-1 py-0.5 border-b text-right">${hasil.toFixed(2)}</td>
                        `;
                    } else {
                        // Perawatan - show operator and plot area
                        const operatorName = item.operator_nama || '-';
                        const plotDisplay = item.plot || '-'; // FIXED: Just plot, consistent
                        const luasArea = item.luasarea ? parseFloat(item.luasarea).toFixed(2) : '0.00';

                        row.innerHTML = `
                            <td class="px-1 py-0.5 border-b text-center">${index + 1}</td>
                            <td class="px-1 py-0.5 border-b font-medium text-xs">${operatorName}</td>
                            <td class="px-1 py-0.5 border-b text-center">${plotDisplay}</td>
                            <td class="px-1 py-0.5 border-b text-center">${luasArea}</td>
                            <td class="px-1 py-0.5 border-b text-right">${hasil.toFixed(2)}</td>
                        `;
                    }
                    
                    tbody.appendChild(row);
                });

                // Total row based on type
                const totalRow = document.createElement('tr');
                totalRow.className = 'bg-gray-100 font-semibold';
                
                if (type === 'pengolahan') {
                    totalRow.innerHTML = `
                        <td colspan="3" class="px-1 py-0.5 text-center border-t-2 border-gray-400">TOTAL</td>
                        <td class="px-1 py-0.5 text-right border-t-2 border-gray-400">${totalHasil.toFixed(2)}</td>
                    `;
                } else {
                    totalRow.innerHTML = `
                        <td colspan="4" class="px-1 py-0.5 text-center border-t-2 border-gray-400">TOTAL</td>
                        <td class="px-1 py-0.5 text-right border-t-2 border-gray-400">${totalHasil.toFixed(2)}</td>
                    `;
                }
                
                tbody.appendChild(totalRow);

                activityTable.appendChild(table);
                tablesGrid.appendChild(activityTable);
            });

            section.appendChild(tablesGrid);
        }

        function showError(message) {
            ['pengolahan-section', 'perawatan-pc-section', 'perawatan-rc-section'].forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) section.innerHTML = `<div class="text-center py-6 text-red-500">${message}</div>`;
            });
        }

        document.addEventListener('DOMContentLoaded', function() { loadLKHRekapData(); });
    </script>
</x-layout>