{{-- resources/views/transaction/rencanakerjaharian/report/report-operator-rekap.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <style>
        @media print {
            body * { visibility: hidden; }
            .print-container, .print-container * { visibility: visible; }
            .print-container { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>

    <div class="print-container print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-4">

        <h1 class="text-xl font-bold text-center text-gray-800 mb-4 uppercase tracking-wider">
            Rekap Laporan Operator Unit Alat
        </h1>

        <div class="mb-4 p-3 bg-gray-50 rounded-lg print:bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-16">Divisi:</span>
                        <span id="company-info" class="text-gray-900">Loading...</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-16">Tanggal:</span>
                        <span id="report-date" class="text-gray-900">Loading...</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-28">Total Operator:</span>
                        <span id="total-operators" class="text-gray-900 font-semibold">Loading...</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-28">Total Aktivitas:</span>
                        <span id="total-activities" class="text-gray-900 font-semibold">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Detail Kegiatan Semua Operator</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300" id="activities-table">
                    <thead class="bg-gray-100">
                        <tr class="text-sm">
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 4%">No</th>
                            <th class="border border-gray-300 px-2 py-2 text-left" style="width: 15%">Operator</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Unit Alat</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Jam Mulai</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Jam Selesai</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Durasi</th>
                            <th class="border border-gray-300 px-2 py-2 text-left" style="width: 17%">Kegiatan</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Plot(s)</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Total Luas RKH<br><small>(ha)</small></th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Total Luas Hasil<br><small>(ha)</small></th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Pemakaian BBM<br><small>(Solar)</small></th>
                        </tr>
                    </thead>
                    <tbody id="activities-tbody">
                        <tr>
                            <td colspan="11" class="border border-gray-300 px-2 py-6 text-center text-gray-500">
                                Memuat data kegiatan...
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="activities-tfoot" class="bg-gray-50 font-semibold">
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-6 text-xs text-gray-500 text-center print:mt-8">
            <p>Dicetak pada: <span id="print-timestamp">Loading...</span></p>
        </div>

        <div class="mt-4 flex justify-center space-x-4 no-print">
            <button
                onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
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
        const reportDate = urlParams.get('date') || '{{ $date }}';

        /**
         * Main function to load report data
         */
        async function loadOperatorRekapReportData() {
            try {
                const response = await fetch(`{{ route('transaction.rencanakerjaharian.operator-rekap-report-data') }}?date=${reportDate}`);
                const data = await response.json();

                if (data.success) {
                    updateHeaderInfo(data);
                    displayAllActivities(data.all_activities || [], data.grand_totals);
                } else {
                    showError('Gagal memuat data rekap laporan operator: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading operator rekap report data:', error);
                showError('Terjadi kesalahan saat memuat data: ' + error.message);
            }
        }

        /**
         * Update header information
         */
        function updateHeaderInfo(data) {
            document.getElementById('report-date').textContent = data.date_formatted || reportDate;
            document.getElementById('company-info').textContent = data.company_info || 'N/A';
            document.getElementById('total-operators').textContent = data.grand_totals?.total_operators || 0;
            document.getElementById('total-activities').textContent = data.grand_totals?.total_activities || 0;
            document.getElementById('print-timestamp').textContent = data.generated_at || new Date().toLocaleString('id-ID');
        }

        /**
         * Display all activities in table
         * ✅ TIDAK ADA PERHITUNGAN LAGI - Semua sudah dari backend
         */
        function displayAllActivities(activities, grandTotals) {
            const tbody = document.getElementById('activities-tbody');
            
            if (!activities || activities.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="border border-gray-300 px-2 py-6 text-center text-gray-400 italic">
                            Tidak ada data aktivitas untuk operator pada tanggal yang dipilih
                        </td>
                    </tr>
                `;
                
                const tfoot = document.getElementById('activities-tfoot');
                tfoot.innerHTML = '';
                return;
            }

            tbody.innerHTML = '';
            let activityCounter = 1;

            // ✅ Render rows - TIDAK ADA PERHITUNGAN
            activities.forEach((activity) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                const jamMulai = activity.jam_mulai 
                    ? activity.jam_mulai 
                    : '<span class="text-gray-400 italic">Belum diinput</span>';
                
                const jamSelesai = activity.jam_selesai 
                    ? activity.jam_selesai 
                    : '<span class="text-gray-400 italic">Belum diinput</span>';
                
                const durasiDisplay = activity.durasi_kerja 
                    ? activity.durasi_kerja 
                    : '<span class="text-gray-400 italic">Belum diinput</span>';
                
                const luasHasilDisplay = activity.luas_hasil_ha 
                    ? activity.luas_hasil_ha 
                    : '<span class="text-gray-400 italic">Belum diinput</span>';
                
                const solarDisplay = activity.solar_liter 
                    ? activity.solar_liter + ' L' 
                    : '<span class="text-gray-400 italic">Belum diinput</span>';

                row.innerHTML = `
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">${activityCounter++}</td>
                    <td class="border border-gray-300 px-2 py-2 text-sm">
                        <div class="font-medium">${activity.operator_name}</div>
                    </td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">
                        <div class="font-medium">${activity.nokendaraan}</div>
                        <div class="text-xs text-gray-500">${activity.vehicle_type}</div>
                    </td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${jamMulai}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${jamSelesai}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${durasiDisplay}</td>
                    <td class="border border-gray-300 px-2 py-2 text-sm">
                        <div class="font-medium">${activity.activityname}</div>
                        <div class="text-xs text-gray-500">${activity.activitycode}</div>
                    </td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${activity.plots_display}</td>
                    <td class="border border-gray-300 px-2 py-2 text-right text-sm">${activity.luas_rencana_ha}</td>
                    <td class="border border-gray-300 px-2 py-2 text-right text-sm">${luasHasilDisplay}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">${solarDisplay}</td>
                `;

                tbody.appendChild(row);
            });

            // ✅ Render grand total - LANGSUNG dari backend (NO CALCULATION)
            renderGrandTotalRow(grandTotals);
        }

        /**
         * Render grand total row
         * ✅ Data sudah jadi dari backend
         */
        function renderGrandTotalRow(grandTotals) {
            const tfoot = document.getElementById('activities-tfoot');
            tfoot.innerHTML = '';
            
            const totalRow = document.createElement('tr');
            
            // Format duration text
            const durationText = grandTotals.total_duration_minutes > 0
                ? formatDurationText(grandTotals.total_duration_hours, grandTotals.total_duration_minutes_remainder)
                : '<span class="text-gray-400 italic">Belum diinput</span>';
            
            const luasHasilDisplay = grandTotals.total_luas_hasil_formatted 
                ? grandTotals.total_luas_hasil_formatted 
                : '<span class="text-gray-400 italic">Belum diinput</span>';
            
            const solarDisplay = grandTotals.total_solar_formatted 
                ? grandTotals.total_solar_formatted + ' L' 
                : '<span class="text-gray-400 italic">Belum diinput</span>';
            
            totalRow.innerHTML = `
                <td colspan="5" class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">GRAND TOTAL</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">${durationText}</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">-</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">-</td>
                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-bold">${grandTotals.total_luas_rencana_formatted}</td>
                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-bold">${luasHasilDisplay}</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">${solarDisplay}</td>
            `;
            
            tfoot.appendChild(totalRow);
        }

        /**
         * Format duration (helper function - simple formatting only)
         */
        function formatDurationText(hours, minutes) {
            if (hours === 0 && minutes === 0) {
                return '<span class="text-gray-400 italic">Belum diinput</span>';
            }
            
            if (hours === 0) {
                return `${minutes} menit`;
            } else if (minutes === 0) {
                return `${hours} jam`;
            } else {
                return `${hours} jam ${minutes} menit`;
            }
        }

        /**
         * Show error message
         */
        function showError(message) {
            const tbody = document.getElementById('activities-tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="border border-gray-300 px-2 py-6 text-center text-red-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="font-semibold">${message}</div>
                        </td>
                    </tr>
                `;
            }
            
            const tfoot = document.getElementById('activities-tfoot');
            if (tfoot) {
                tfoot.innerHTML = '';
            }
            
            document.getElementById('company-info').textContent = 'Error';
            document.getElementById('report-date').textContent = 'Error';
            document.getElementById('total-operators').textContent = '0';
            document.getElementById('total-activities').textContent = '0';
            document.getElementById('print-timestamp').textContent = new Date().toLocaleString('id-ID');
        }

        /**
         * Initialize on page load
         */
        document.addEventListener('DOMContentLoaded', function() {
            loadOperatorRekapReportData();
        });
    </script>
</x-layout>