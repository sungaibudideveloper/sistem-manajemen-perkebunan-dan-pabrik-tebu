{{-- resources\views\input\rencanakerjaharian\lkh-report-operator.blade.php --}}
<x-layout>
    <x-slot:title>Laporan Kegiatan Harian - Operator Alat</x-slot:title>
    <x-slot:navbar>Input</x-slot:navbar>
    <x-slot:nav>Rencana Kerja Harian</x-slot:nav>

    <!-- Print-optimized container -->
    <div class="print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-4">

        <!-- Title -->
        <h1 class="text-xl font-bold text-center text-gray-800 mb-4 uppercase tracking-wider">
            Laporan Kegiatan Harian - Operator Alat
        </h1>

        <!-- Header Information -->
        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Left side - Company & Date -->
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

                <!-- Right side - Operator & Vehicle -->
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-20">Operator:</span>
                        <span id="operator-name" class="text-gray-900">Loading...</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-20">Unit Alat:</span>
                        <span id="vehicle-info" class="text-gray-900">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities Table -->
        <div class="mb-4">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Detail Kegiatan</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300" id="activities-table">
                    <thead class="bg-gray-100">
                        <tr class="text-sm">
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 5%">No</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Jam Mulai</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Jam Selesai</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 12%">Durasi</th>
                            <th class="border border-gray-300 px-2 py-2 text-left" style="width: 25%">Kegiatan</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Plot</th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Luas RKH<br><small>(ha)</small></th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Luas Hasil<br><small>(ha)</small></th>
                            <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Pemakaian BBM<br><small>(Solar)</small></th>
                        </tr>
                    </thead>
                    <tbody id="activities-tbody">
                        <tr>
                            <td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-500">
                                Memuat data kegiatan...
                            </td>
                        </tr>
                    </tbody>
                    <tfoot id="activities-tfoot" class="bg-gray-50 font-semibold">
                        <!-- Total row will be inserted here -->
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Summary Section - REMOVED -->

        <!-- Signatures - SIMPLIFIED -->
        <div class="mt-6 flex justify-end print:mt-8">
            <div class="text-center" style="width: 200px;">
                <div class="font-semibold mb-12 text-sm">Disiapkan Oleh</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">Asisten Lapangan</div>
            </div>
        </div>

        <!-- Action Buttons -->
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
        const reportDate = urlParams.get('date') || new Date().toISOString().split('T')[0];
        const operatorId = urlParams.get('operator_id');

        let globalData = null;

        async function loadOperatorReportData() {
            try {
                if (!operatorId) {
                    showError('Operator ID tidak ditemukan');
                    return;
                }

                const response = await fetch(`{{ route('input.rencanakerjaharian.operator-report-data') }}?date=${reportDate}&operator_id=${operatorId}`);
                const data = await response.json();
                globalData = data;

                if (data.success) {
                    updateHeaderInfo(data);
                    populateActivitiesTable(data.activities || []);
                } else {
                    showError('Gagal memuat data laporan operator: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading operator report data:', error);
                showError('Terjadi kesalahan saat memuat data: ' + error.message);
            }
        }

        function updateHeaderInfo(data) {
            const reportDateEl = document.getElementById('report-date');
            if (reportDateEl) reportDateEl.textContent = data.date_formatted || reportDate;

            const companyInfoEl = document.getElementById('company-info');
            if (companyInfoEl) companyInfoEl.textContent = data.company_info || 'N/A';

            if (data.operator_info) {
                const operatorNameEl = document.getElementById('operator-name');
                if (operatorNameEl) operatorNameEl.textContent = data.operator_info.operator_name || 'N/A';

                const vehicleInfoEl = document.getElementById('vehicle-info');
                if (vehicleInfoEl) {
                    const vehicleText = `${data.operator_info.nokendaraan || 'N/A'} - ${data.operator_info.vehicle_type || 'N/A'}`;
                    vehicleInfoEl.textContent = vehicleText;
                }
            }
        }

        function populateActivitiesTable(activities) {
            const tbody = document.getElementById('activities-tbody');
            const tfoot = document.getElementById('activities-tfoot');
            tbody.innerHTML = '';
            tfoot.innerHTML = '';

            if (!activities || activities.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-400 italic">
                            Tidak ada data kegiatan untuk operator ini pada tanggal yang dipilih
                        </td>
                    </tr>
                `;
                return;
            }

            let totalLuasRencana = 0;
            let totalLuasHasil = 0;
            let totalSolar = 0;
            let totalDurationMinutes = 0;

            activities.forEach((activity, index) => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50';

                // Calculate duration in minutes for total
                const durationParts = activity.durasi_kerja.split(':');
                const activityMinutes = (parseInt(durationParts[0]) * 60) + parseInt(durationParts[1]);
                totalDurationMinutes += activityMinutes;

                // Sum other totals
                totalLuasRencana += parseFloat(activity.luas_rencana_ha.replace(',', ''));
                totalLuasHasil += parseFloat(activity.luas_hasil_ha.replace(',', ''));
                if (activity.solar_liter) {
                    totalSolar += parseFloat(activity.solar_liter);
                }

                row.innerHTML = `
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm">${index + 1}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${activity.jam_mulai}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${activity.jam_selesai}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${formatDurationText(activityMinutes)}</td>
                    <td class="border border-gray-300 px-2 py-2 text-sm">
                        <div class="font-medium">${activity.activityname}</div>
                        <div class="text-xs text-gray-500">${activity.activitycode}</div>
                    </td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${activity.plot_display}</td>
                    <td class="border border-gray-300 px-2 py-2 text-right text-sm">${activity.luas_rencana_ha}</td>
                    <td class="border border-gray-300 px-2 py-2 text-right text-sm">${activity.luas_hasil_ha}</td>
                    <td class="border border-gray-300 px-2 py-2 text-center text-sm ${activity.solar_liter ? 'font-medium' : 'text-gray-400 italic'}">${activity.solar_display}</td>
                `;

                tbody.appendChild(row);
            });

            // Add total row
            const totalRow = document.createElement('tr');
            totalRow.innerHTML = `
                <td colspan="3" class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">TOTAL</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">${formatDurationText(totalDurationMinutes)}</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">-</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">-</td>
                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-bold">${totalLuasRencana.toFixed(2)}</td>
                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-bold">${totalLuasHasil.toFixed(2)}</td>
                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-bold">${totalSolar.toFixed(1)} L</td>
            `;
            tfoot.appendChild(totalRow);
        }

        function formatDurationText(totalMinutes) {
            if (totalMinutes === 0) return '0 menit';

            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;

            if (hours === 0) {
                return `${minutes} menit`;
            } else if (minutes === 0) {
                return `${hours} jam`;
            } else {
                return `${hours} jam ${minutes} menit`;
            }
        }

        function showError(message) {
            const tbody = document.getElementById('activities-tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-red-500">
                            ${message}
                        </td>
                    </tr>
                `;
            }
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadOperatorReportData();
        });
    </script>
</x-layout>
