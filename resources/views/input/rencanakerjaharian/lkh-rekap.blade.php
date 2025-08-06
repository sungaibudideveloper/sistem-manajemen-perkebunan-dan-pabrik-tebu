{{--resources\views\input\rencanakerjaharian\lkh-rekap.blade.php--}}
<x-layout>
    <x-slot:title>Rekap Laporan Kegiatan Harian (LKH)</x-slot:title>
    <x-slot:navbar>Input</x-slot:navbar>
    <x-slot:nav>Rencana Kerja Harian</x-slot:nav>

    <!-- Print-optimized container -->
    <div class="print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-2">
        
        <!-- Debug button (top right, small) -->
        <div class="absolute top-2 right-2 z-50 no-print">
            <button onclick="toggleDebugInfo()" class="bg-yellow-400 text-yellow-900 px-1 py-0.5 rounded text-xs font-medium hover:bg-yellow-500">
                Debug
            </button>
        </div>

        <!-- Title -->
        <h1 class="text-xl font-bold text-center text-gray-800 mb-3 uppercase tracking-wider">
            Rekap Laporan Kegiatan Harian (LKH)
        </h1>

        <!-- Debug Info -->
        <div id="debug-info" class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-2 rounded mb-2 text-xs hidden">
            <strong>Debug Information:</strong>
            <div id="debug-content">Loading debug info...</div>
        </div>

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

        <!-- Section 2: LKH Perawatan Manual -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                2. LKH Perawatan Manual (Activity V)
            </h2>
            
            <h3 class="text-base font-semibold text-gray-700 mb-1">
                PC (Plant Cane)
            </h3>
            <div id="perawatan-manual-pc-section" class="text-center py-3 text-gray-500 mb-2">
                Memuat data perawatan manual PC...
            </div>

            <h3 class="text-base font-semibold text-gray-700 mb-1">
                RC (Ratoon Cane)
            </h3>
            <div id="perawatan-manual-rc-section" class="text-center py-3 text-gray-500">
                Memuat data perawatan manual RC...
            </div>
        </div>

        <!-- Section 3: LKH Perawatan Mekanis -->
        <div class="mb-3">
            <h2 class="text-lg font-bold text-gray-800 mb-2 pb-1 border-b border-gray-300">
                3. LKH Perawatan Mekanis
            </h2>
            
            <h3 class="text-base font-semibold text-gray-700 mb-1">
                PC (Plant Cane)
            </h3>
            <div class="text-center py-3 text-gray-400 italic mb-2">
                Fitur perawatan mekanis akan ditambahkan pada update selanjutnya
            </div>

            <h3 class="text-base font-semibold text-gray-700 mb-1">
                RC (Ratoon Cane)
            </h3>
            <div class="text-center py-3 text-gray-400 italic">
                Fitur perawatan mekanis akan ditambahkan pada update selanjutnya
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
                    populatePerawatanManualSection(data.perawatan_manual || {});
                    updateDebugInfo(data);
                } else {
                    showError('Gagal memuat data LKH Rekap: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading LKH Rekap data:', error);
                showError('Terjadi kesalahan saat memuat data: ' + error.message);
                updateDebugInfo({error: error.message, stack: error.stack});
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

            if (data.perawatan_manual) {
                ['pc', 'rc'].forEach(type => {
                    if (data.perawatan_manual[type]) {
                        Object.values(data.perawatan_manual[type]).forEach(activities => {
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
            createActivityGrid(section, data);
        }

        function populatePerawatanManualSection(data) {
            populatePerawatanSubsection(data?.pc, 'perawatan-manual-pc-section', 'PC');
            populatePerawatanSubsection(data?.rc, 'perawatan-manual-rc-section', 'RC');
        }

        function populatePerawatanSubsection(data, sectionId, type) {
            const section = document.getElementById(sectionId);
            section.innerHTML = '';
            if (!data || Object.keys(data).length === 0) {
                section.innerHTML = `<div class="text-center py-6 text-gray-400 italic">Tidak ada data perawatan manual ${type} untuk tanggal yang dipilih</div>`;
                return;
            }
            createActivityGrid(section, data);
        }

        function createActivityGrid(section, data) {
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
                table.innerHTML = `
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-1 py-0.5 text-left border-b text-gray-600 w-8">No.</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600">Mandor</th>
                            <th class="px-1 py-0.5 text-center border-b text-gray-600 w-16">Plot</th>
                            <th class="px-1 py-0.5 text-right border-b text-gray-600 w-12">Hasil</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                `;

                const tbody = table.querySelector('tbody');
                let totalHasil = 0;

                activities.forEach((item, index) => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    
                    const hasil = parseFloat(item.totalhasil || 0);
                    totalHasil += hasil;

                    const mandorName = item.mandor_nama || '-';
                    const plotDisplay = `${item.blok}-${item.plot}` || '-';

                    row.innerHTML = `
                        <td class="px-1 py-0.5 border-b text-center">${index + 1}</td>
                        <td class="px-1 py-0.5 border-b font-medium text-xs">${mandorName}</td>
                        <td class="px-1 py-0.5 border-b text-center">${plotDisplay}</td>
                        <td class="px-1 py-0.5 border-b text-right">${hasil.toFixed(2)}</td>
                    `;
                    tbody.appendChild(row);
                });

                const totalRow = document.createElement('tr');
                totalRow.className = 'bg-gray-100 font-semibold';
                totalRow.innerHTML = `
                    <td colspan="3" class="px-1 py-0.5 text-center border-t-2 border-gray-400">TOTAL</td>
                    <td class="px-1 py-0.5 text-right border-t-2 border-gray-400">${totalHasil.toFixed(2)}</td>
                `;
                tbody.appendChild(totalRow);

                activityTable.appendChild(table);
                tablesGrid.appendChild(activityTable);
            });

            section.appendChild(tablesGrid);
        }

        function updateDebugInfo(data) {
            const debugContent = document.getElementById('debug-content');
            if (!debugContent) return;

            let debugHtml = '';
            
            if (data.error) {
                debugHtml += `<div><strong>Error:</strong> ${data.error}</div>`;
                if (data.stack) debugHtml += `<div><strong>Stack:</strong> <pre class="text-xs whitespace-pre-wrap mt-2">${data.stack}</pre></div>`;
            } else {
                debugHtml += `<div><strong>Data loaded successfully</strong></div>`;
                if (data.debug) {
                    debugHtml += '<div class="mt-2"><strong>Counts:</strong></div>';
                    Object.keys(data.debug).forEach(key => {
                        debugHtml += `<div>- ${key}: ${data.debug[key]}</div>`;
                    });
                }
                
                if (data.pengolahan) debugHtml += `<div class="mt-2"><strong>Pengolahan activities:</strong> ${Object.keys(data.pengolahan).join(', ')}</div>`;
                
                if (data.perawatan_manual) {
                    if (data.perawatan_manual.pc) debugHtml += `<div><strong>PC activities:</strong> ${Object.keys(data.perawatan_manual.pc).join(', ')}</div>`;
                    if (data.perawatan_manual.rc) debugHtml += `<div><strong>RC activities:</strong> ${Object.keys(data.perawatan_manual.rc).join(', ')}</div>`;
                }
            }
            
            debugContent.innerHTML = debugHtml;
        }

        function toggleDebugInfo() {
            const debugDiv = document.getElementById('debug-info');
            debugDiv.classList.toggle('hidden');
        }

        function showError(message) {
            ['pengolahan-section', 'perawatan-manual-pc-section', 'perawatan-manual-rc-section'].forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) section.innerHTML = `<div class="text-center py-6 text-red-500">${message}</div>`;
            });
        }

        document.addEventListener('DOMContentLoaded', function() { loadLKHRekapData(); });
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault(); toggleDebugInfo();
            }
        });
    </script>
</x-layout>