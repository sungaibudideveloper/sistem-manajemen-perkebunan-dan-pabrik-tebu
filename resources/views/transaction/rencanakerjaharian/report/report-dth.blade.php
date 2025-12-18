{{-- resources\views\transaction\rencanakerjaharian\report\report-dth.blade.php --}}
<x-layout>
    <x-slot:title>Laporan DTH - Distribusi Tenaga Harian, Borongan dan Alat</x-slot:title>
    <x-slot:navbar>Input</x-slot:navbar>
    <x-slot:nav>Rencana Kerja Harian</x-slot:nav>

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
            Distribusi Tenaga Harian, Borongan dan Alat
        </h1>

        <!-- Header Info -->
        <div class="mb-4 p-3 bg-gray-50 rounded-lg print:bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-16">RKH:</span>
                        <span id="rkh-list" class="text-gray-900">Loading...</span>
                    </div>
                    <div class="flex items-center text-sm">
                        <span class="font-semibold text-gray-700 w-16">Status:</span>
                        <span id="rkh-approval-status" class="text-gray-900">Loading...</span>
                    </div>
                </div>

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
            </div>
        </div>

        <!-- Tenaga Harian -->
        <h2 class="text-lg font-semibold text-gray-800 mb-3 mt-6">Distribusi Tenaga Harian</h2>
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full border border-gray-300">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 4%">No</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-left" style="width: 14%">Mandor</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-left" style="width: 18%">Kegiatan</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Blok</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 15%">Plot(s)</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Total Luas<br><small>(ha)</small></th>
                        <th colspan="3" class="border border-gray-300 px-2 py-2 text-center">Jumlah Tenaga</th>
                    </tr>
                    <tr class="text-sm">
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">L</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">P</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Total</th>
                    </tr>
                </thead>
                <tbody id="harian-tbody">
                    <tr>
                        <td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-500">Memuat data...</td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="5" class="border border-gray-300 px-2 py-2 text-center">TOTAL TENAGA HARIAN</td>
                        <td id="sum-luas-harian" class="border border-gray-300 px-2 py-2 text-center">0</td>
                        <td id="sum-laki-harian" class="border border-gray-300 px-2 py-2 text-center">0</td>
                        <td id="sum-perempuan-harian" class="border border-gray-300 px-2 py-2 text-center">0</td>
                        <td id="sum-total-harian" class="border border-gray-300 px-2 py-2 text-center">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tenaga Borongan -->
        <h2 class="text-lg font-semibold text-gray-800 mb-3 mt-6">Distribusi Tenaga Borongan</h2>
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full border border-gray-300">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 4%">No</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-left" style="width: 14%">Mandor</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-left" style="width: 18%">Kegiatan</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Blok</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 15%">Plot(s)</th>
                        <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center" style="width: 8%">Total Luas<br><small>(ha)</small></th>
                        <th colspan="3" class="border border-gray-300 px-2 py-2 text-center">Jumlah Tenaga</th>
                    </tr>
                    <tr class="text-sm">
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">L</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">P</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Total</th>
                    </tr>
                </thead>
                <tbody id="borongan-tbody">
                    <tr>
                        <td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-500">Memuat data...</td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50 font-semibold">
                    <tr>
                        <td colspan="5" class="border border-gray-300 px-2 py-2 text-center">TOTAL TENAGA BORONGAN</td>
                        <td id="sum-luas-borongan" class="border border-gray-300 px-2 py-2 text-center">0</td>
                        <td id="sum-laki-borongan" class="border border-gray-300 px-2 py-2 text-center">0</td>
                        <td id="sum-perempuan-borongan" class="border border-gray-300 px-2 py-2 text-center">0</td>
                        <td id="sum-total-borongan" class="border border-gray-300 px-2 py-2 text-center">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Distribusi Alat -->
        <h2 class="text-lg font-semibold text-gray-800 mb-3 mt-6">Distribusi Alat</h2>
        <div class="overflow-x-auto mb-6">
            <table class="min-w-full border border-gray-300">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 4%">No</th>
                        <th class="border border-gray-300 px-2 py-2 text-left" style="width: 15%">Nama Operator</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">No Unit Alat</th>
                        <th class="border border-gray-300 px-2 py-2 text-left" style="width: 10%">Jenis</th>
                        <th class="border border-gray-300 px-2 py-2 text-left" style="width: 12%">Helper</th>
                        <th class="border border-gray-300 px-2 py-2 text-left" style="width: 18%">Kegiatan</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 6%">Blok</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 15%">Plot(s)</th>
                        <th class="border border-gray-300 px-2 py-2 text-center" style="width: 10%">Total Luas<br><small>(ha)</small></th>
                    </tr>
                </thead>
                <tbody id="alat-tbody">
                    <tr>
                        <td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-500">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="mt-8 grid grid-cols-4 gap-6 print:mt-12">
            <div class="text-center">
                <div class="font-semibold mb-16 text-sm">Diketahui</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">General Manager</div>
            </div>
            <div class="text-center">
                <div class="font-semibold mb-16 text-sm">Disetujui</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">Estate Manager</div>
            </div>
            <div class="text-center">
                <div class="font-semibold mb-16 text-sm">Diperiksa</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">Asisten Kepala</div>
            </div>
            <div class="text-center">
                <div class="font-semibold mb-16 text-sm">Disiapkan</div>
                <div class="border-t border-gray-400 pt-1 text-xs text-gray-600">Asisten Lapangan</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-center space-x-4 no-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                Print
            </button>
            <button onclick="window.history.back()" class="bg-white border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium">
                Kembali
            </button>
        </div>
    </div>

    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const reportDate = urlParams.get('date') || new Date().toISOString().split('T')[0];

        async function loadDTHData() {
            try {
                const response = await fetch(`{{ route('transaction.rencanakerjaharian.dth-data') }}?date=${reportDate}`);
                const data = await response.json();

                if (data.success) {
                    updateHeaderInfo(data);
                    populateHarianTable(data.harian);
                    populateBoronganTable(data.borongan);
                    populateAlatTable(data.alat);
                } else {
                    showError('Gagal memuat data DTH: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading DTH data:', error);
                showError('Terjadi kesalahan saat memuat data');
            }
        }

        function updateHeaderInfo(data) {
            document.getElementById('report-date').textContent = new Date(reportDate).toLocaleDateString('id-ID', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
            
            document.getElementById('company-info').textContent = data.company_info || 'N/A';
            
            const rkhList = data.rkh_numbers && data.rkh_numbers.length > 0 
                ? data.rkh_numbers.join(', ') 
                : 'Tidak ada data';
            document.getElementById('rkh-list').textContent = rkhList;

            if (data.rkh_approval) {
                const statusText = `${data.rkh_approval.approved}/${data.rkh_approval.total} RKH Approved (${data.rkh_approval.percentage}%)`;
                const statusEl = document.getElementById('rkh-approval-status');
                statusEl.textContent = statusText;
                
                if (data.rkh_approval.percentage === 100) {
                    statusEl.className = 'text-green-600 font-semibold';
                } else if (data.rkh_approval.percentage >= 50) {
                    statusEl.className = 'text-orange-600 font-semibold';
                } else {
                    statusEl.className = 'text-red-600 font-semibold';
                }
            }
        }

        function populateHarianTable(data) {
            const tbody = document.getElementById('harian-tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-400 italic">Tidak ada data tenaga harian</td></tr>';
                return;
            }

            const grouped = {};
            data.forEach(item => {
                const key = `${item.mandor_nama}|${item.activityname}`;
                if (!grouped[key]) {
                    grouped[key] = {
                        mandor_nama: item.mandor_nama,
                        activityname: item.activityname,
                        plots: [],
                        bloks: [],
                        totalLuas: 0,
                        jumlahlaki: item.jumlahlaki,
                        jumlahperempuan: item.jumlahperempuan
                    };
                }
                if (item.plot?.trim() && !grouped[key].plots.includes(item.plot)) {
                    grouped[key].plots.push(item.plot);
                }
                if (!grouped[key].bloks.includes(item.blok)) {
                    grouped[key].bloks.push(item.blok);
                }
                grouped[key].totalLuas += parseFloat(item.luasarea);
            });

            let totalL = 0, totalP = 0, totalLuas = 0, index = 1;

            Object.values(grouped).forEach(item => {
                const total = item.jumlahlaki + item.jumlahperempuan;
                const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${index++}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.mandor_nama || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.activityname || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.bloks.join(', ')}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.plots.length > 0 ? item.plots.join(', ') : '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.totalLuas.toFixed(1)}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.jumlahlaki}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.jumlahperempuan}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm font-semibold">${total}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
                totalL += item.jumlahlaki;
                totalP += item.jumlahperempuan;
                totalLuas += item.totalLuas;
            });

            document.getElementById('sum-luas-harian').textContent = totalLuas.toFixed(1);
            document.getElementById('sum-laki-harian').textContent = totalL;
            document.getElementById('sum-perempuan-harian').textContent = totalP;
            document.getElementById('sum-total-harian').textContent = totalL + totalP;
        }

        function populateBoronganTable(data) {
            const tbody = document.getElementById('borongan-tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-400 italic">Tidak ada data tenaga borongan</td></tr>';
                return;
            }

            const grouped = {};
            data.forEach(item => {
                const key = `${item.mandor_nama}|${item.activityname}`;
                if (!grouped[key]) {
                    grouped[key] = {
                        mandor_nama: item.mandor_nama,
                        activityname: item.activityname,
                        plots: [],
                        bloks: [],
                        totalLuas: 0,
                        jumlahlaki: item.jumlahlaki,
                        jumlahperempuan: item.jumlahperempuan
                    };
                }
                if (item.plot?.trim() && !grouped[key].plots.includes(item.plot)) {
                    grouped[key].plots.push(item.plot);
                }
                if (!grouped[key].bloks.includes(item.blok)) {
                    grouped[key].bloks.push(item.blok);
                }
                grouped[key].totalLuas += parseFloat(item.luasarea);
            });

            let totalL = 0, totalP = 0, totalLuas = 0, index = 1;

            Object.values(grouped).forEach(item => {
                const total = item.jumlahlaki + item.jumlahperempuan;
                const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${index++}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.mandor_nama || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.activityname || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.bloks.join(', ')}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.plots.length > 0 ? item.plots.join(', ') : '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.totalLuas.toFixed(1)}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.jumlahlaki}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.jumlahperempuan}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm font-semibold">${total}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
                totalL += item.jumlahlaki;
                totalP += item.jumlahperempuan;
                totalLuas += item.totalLuas;
            });

            document.getElementById('sum-luas-borongan').textContent = totalLuas.toFixed(1);
            document.getElementById('sum-laki-borongan').textContent = totalL;
            document.getElementById('sum-perempuan-borongan').textContent = totalP;
            document.getElementById('sum-total-borongan').textContent = totalL + totalP;
        }

        function populateAlatTable(data) {
            const tbody = document.getElementById('alat-tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-gray-400 italic">Tidak ada data alat</td></tr>';
                return;
            }

            const grouped = {};
            data.forEach(item => {
                const key = `${item.nokendaraan}|${item.operator_nama}|${item.activityname}`;
                if (!grouped[key]) {
                    grouped[key] = {
                        operator_nama: item.operator_nama,
                        nokendaraan: item.nokendaraan,
                        jenis: item.jenis,
                        helper_nama: item.helper_nama,
                        activityname: item.activityname,
                        plots: [],
                        bloks: [],
                        totalLuas: 0
                    };
                }
                if (item.plot?.trim() && !grouped[key].plots.includes(item.plot)) {
                    grouped[key].plots.push(item.plot);
                }
                if (!grouped[key].bloks.includes(item.blok)) {
                    grouped[key].bloks.push(item.blok);
                }
                grouped[key].totalLuas += parseFloat(item.luasarea);
            });

            let index = 1;
            Object.values(grouped).forEach(item => {
                const row = `
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${index++}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.operator_nama || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">${item.nokendaraan || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.jenis || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.helper_nama || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-sm">${item.activityname || '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.bloks.join(', ')}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.plots.length > 0 ? item.plots.join(', ') : '-'}</td>
                        <td class="border border-gray-300 px-2 py-2 text-center text-sm">${item.totalLuas.toFixed(1)}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        function showError(message) {
            ['harian-tbody', 'borongan-tbody', 'alat-tbody'].forEach(id => {
                document.getElementById(id).innerHTML = `<tr><td colspan="9" class="border border-gray-300 px-2 py-6 text-center text-red-500">${message}</td></tr>`;
            });
        }

        document.addEventListener('DOMContentLoaded', loadDTHData);
    </script>
</x-layout>