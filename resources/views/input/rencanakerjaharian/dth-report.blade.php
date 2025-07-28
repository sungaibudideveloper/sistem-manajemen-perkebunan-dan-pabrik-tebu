<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan DTH - Distribusi Tenaga Harian, Borongan dan Alat</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #fff;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
        }

        /* Header Section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .header-left {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #f9fafb;
            color: #374151;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .btn-primary {
            background: #6b7280;
            color: white;
            border-color: #6b7280;
        }

        .btn-primary:hover {
            background: #4b5563;
        }

        .header-right {
            text-align: right;
            font-size: 11px;
            color: #6b7280;
        }

        .header-right .date {
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        /* Title */
        .main-title {
            text-align: center;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin: 25px 0 15px;
            padding: 6px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Modern Table Styles */
        .table-container {
            margin-bottom: 30px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 8px 6px;
            text-align: center;
            font-weight: 600;
            font-size: 10px;
            color: #374151;
            border: 1px solid #e5e7eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 8px 6px;
            text-align: center;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #f3f4f6;
        }

        /* Column Widths - Tenaga Harian & Borongan */
        .col-no { width: 4%; }
        .col-mandor { width: 14%; text-align: left; }
        .col-kegiatan { width: 18%; text-align: left; }
        .col-blok { width: 6%; }
        .col-plot { width: 8%; }
        .col-luas { width: 8%; }
        .col-tenaga { width: 6%; }

        /* Column Widths - Alat */
        .col-operator { width: 12%; text-align: left; }
        .col-helper { width: 12%; text-align: left; }
        .col-nokendaraan { width: 10%; }
        .col-jenis { width: 12%; text-align: left; }

        /* Footer/Total Row */
        tfoot {
            background: #374151;
            color: white;
        }

        tfoot td {
            font-weight: 600;
            padding: 10px 6px;
            border-color: #4b5563;
        }

        /* Signatures */
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            text-align: center;
        }

        .signature-box {
            padding: 15px 8px;
        }

        .signature-title {
            font-weight: 600;
            margin-bottom: 50px;
            font-size: 11px;
        }

        .signature-line {
            border-top: 1px solid #374151;
            padding-top: 6px;
            font-size: 10px;
            color: #6b7280;
        }

        /* Print Styles */
        @media print {
            .header-left {
                display: none;
            }
            
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            /* Hide page info during print to avoid confusion */
            #page-info {
                display: none;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #6b7280;
            font-style: italic;
        }

        .loading {
            text-align: center;
            padding: 30px 20px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="btn" onclick="history.back()">
                    Kembali
                </button>
                <button class="btn btn-primary" onclick="window.print()">
                    Cetak
                </button>
                <button class="btn" onclick="exportToPDF()">
                    Export PDF
                </button>
                <button class="btn" onclick="exportToExcel()">
                    Export Excel
                </button>
            </div>
        </div>

        <!-- Title -->
        <h1 class="main-title">Distribusi Tenaga Harian, Borongan dan Alat</h1>

        <!-- RKH List & Statistics -->
        <div style="margin-bottom: 20px; font-size: 11px; color: #6b7280; display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div id="rkh-list" style="margin-bottom: 8px;">RKH: Loading...</div>
                <div id="statistics" style="display: flex; gap: 25px; font-size: 10px;">
                    <span>Total Luas: <strong id="stat-total-luas">0</strong> ha</span>
                    <span>Total Tenaga Kerja: <strong id="stat-total-tenaga">0</strong> orang</span>
                    <span>Total Kendaraan: <strong id="stat-total-kendaraan">0</strong> unit</span>
                </div>
            </div>
            <div style="text-align: right; font-size: 12px; color: #6b7280;">
                <div class="date" style="font-weight: 600; color: #111827">Tanggal: <span id="report-date"></span></div>
                <div id="printed-at">Printed at: <span id="print-timestamp"></span></div>
                <div id="divisi-info"style="font-weight: 600; color: #111827" >Divisi: <span id="company-info">Loading...</span></div>
            </div>
        </div>

        <!-- Tenaga Harian Section -->
        <h2 class="section-title">Distribusi Tenaga Harian</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" class="col-no">No.</th>
                        <th rowspan="2" class="col-mandor">Mandor</th>
                        <th rowspan="2" class="col-kegiatan">Kegiatan</th>
                        <th rowspan="2" class="col-blok">Blok</th>
                        <th rowspan="2" class="col-plot">Plot</th>
                        <th rowspan="2" class="col-luas">Luas (ha)</th>
                        <th colspan="3" class="col-tenaga">Jumlah Tenaga</th>
                    </tr>
                    <tr>
                        <th class="col-tenaga">L</th>
                        <th class="col-tenaga">P</th>
                        <th class="col-tenaga">Total</th>
                    </tr>
                </thead>
                <tbody id="harian-tbody">
                    <tr>
                        <td colspan="9" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: center; font-weight: bold;">TOTAL TENAGA HARIAN</td>
                        <td id="sum-luas-harian">0</td>
                        <td id="sum-laki-harian">0</td>
                        <td id="sum-perempuan-harian">0</td>
                        <td id="sum-total-harian">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tenaga Borongan Section -->
        <h2 class="section-title">Distribusi Tenaga Borongan</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" class="col-no">No.</th>
                        <th rowspan="2" class="col-mandor">Mandor</th>
                        <th rowspan="2" class="col-kegiatan">Kegiatan</th>
                        <th rowspan="2" class="col-blok">Blok</th>
                        <th rowspan="2" class="col-plot">Plot</th>
                        <th rowspan="2" class="col-luas">Luas (ha)</th>
                        <th colspan="3" class="col-tenaga">Jumlah Tenaga</th>
                    </tr>
                    <tr>
                        <th class="col-tenaga">L</th>
                        <th class="col-tenaga">P</th>
                        <th class="col-tenaga">Total</th>
                    </tr>
                </thead>
                <tbody id="borongan-tbody">
                    <tr>
                        <td colspan="9" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" style="text-align: center; font-weight: bold;">TOTAL TENAGA BORONGAN</td>
                        <td id="sum-luas-borongan">0</td>
                        <td id="sum-laki-borongan">0</td>
                        <td id="sum-perempuan-borongan">0</td>
                        <td id="sum-total-borongan">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Distribusi Alat Section -->
        <h2 class="section-title">Distribusi Alat</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th class="col-no">No.</th>
                        <th class="col-operator">Nama Operator</th>
                        <th class="col-helper">Helper</th>
                        <th class="col-kegiatan">Kegiatan</th>
                        <th class="col-blok">Blok</th>
                        <th class="col-plot">Plot</th>
                        <th class="col-luas">Luas (ha)</th>
                        <th class="col-nokendaraan">No Kendaraan</th>
                        <th class="col-jenis">Jenis</th>
                    </tr>
                </thead>
                <tbody id="alat-tbody">
                    <tr>
                        <td colspan="9" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Diketahui</div>
                <div class="signature-line">General Manager</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Disetujui</div>
                <div class="signature-line">Estate Manager</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Diperiksa</div>
                <div class="signature-line">Asisten Kepala</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Disiapkan</div>
                <div class="signature-line">Asisten Lapangan</div>
            </div>
        </div>
    </div>

    <script>
        // Set report date from URL parameter or current date
        const urlParams = new URLSearchParams(window.location.search);
        const reportDate = urlParams.get('date') || new Date().toISOString().split('T')[0];
        
        document.getElementById('report-date').textContent = new Date(reportDate).toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Load DTH data
        async function loadDTHData() {
            try {
                const response = await fetch(`{{ route('input.rencanakerjaharian.dth-data') }}?date=${reportDate}`);
                const data = await response.json();
                
                if (data.success) {
                    // Update RKH list dan timestamp
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
            // Update RKH list
            const rkhListElement = document.getElementById('rkh-list');
            if (rkhListElement) {
                if (data.rkh_numbers && data.rkh_numbers.length > 0) {
                    rkhListElement.textContent = `RKH: ${data.rkh_numbers.join(', ')}`;
                } else {
                    rkhListElement.textContent = 'RKH: Tidak ada data';
                }
            }
            
            // Update print timestamp
            const printTimestamp = document.getElementById('print-timestamp');
            if (printTimestamp) {
                printTimestamp.textContent = data.generated_at || new Date().toLocaleString('id-ID');
            }
            
            // Update company info
            const companyInfo = document.getElementById('company-info');
            if (companyInfo) {
                if (data.company_info) {
                    companyInfo.textContent = data.company_info;
                } else {
                    companyInfo.textContent = 'N/A';
                }
            }
            
            // Update statistics
            updateStatistics(data);
        }

        function updateStatistics(data) {
            let totalLuas = 0;
            let totalTenaga = 0;
            let totalKendaraan = 0;

            // Calculate from harian data
            if (data.harian) {
                data.harian.forEach(item => {
                    totalLuas += parseFloat(item.luasarea || 0);
                    totalTenaga += parseInt(item.jumlahlaki || 0) + parseInt(item.jumlahperempuan || 0);
                });
            }

            // Calculate from borongan data
            if (data.borongan) {
                data.borongan.forEach(item => {
                    totalLuas += parseFloat(item.luasarea || 0);
                    totalTenaga += parseInt(item.jumlahlaki || 0) + parseInt(item.jumlahperempuan || 0);
                });
            }

            // Calculate kendaraan from alat data
            if (data.alat) {
                totalKendaraan = data.alat.length;
            }

            // Update DOM with null checks
            const luasEl = document.getElementById('stat-total-luas');
            const tenagaEl = document.getElementById('stat-total-tenaga');
            const kendaraanEl = document.getElementById('stat-total-kendaraan');
            
            if (luasEl) luasEl.textContent = totalLuas.toFixed(1);
            if (tenagaEl) tenagaEl.textContent = totalTenaga;
            if (kendaraanEl) kendaraanEl.textContent = totalKendaraan;
        }

        function populateHarianTable(data) {
            const tbody = document.getElementById('harian-tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            Tidak ada data tenaga harian untuk tanggal yang dipilih
                        </td>
                    </tr>
                `;
                return;
            }

            let totalL = 0, totalP = 0, totalLuas = 0;

            data.forEach((item, index) => {
                const row = document.createElement('tr');
                const total = item.jumlahlaki + item.jumlahperempuan;
                
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td style="text-align: left;">${item.mandor_nama || '-'}</td>
                    <td style="text-align: left;">${item.activityname || '-'}</td>
                    <td>${item.blok}</td>
                    <td>${item.plot}</td>
                    <td>${parseFloat(item.luasarea).toFixed(1)}</td>
                    <td>${item.jumlahlaki}</td>
                    <td>${item.jumlahperempuan}</td>
                    <td>${total}</td>
                `;
                tbody.appendChild(row);
                
                totalL += item.jumlahlaki;
                totalP += item.jumlahperempuan;
                totalLuas += parseFloat(item.luasarea);
            });

            // Update totals
            document.getElementById('sum-luas-harian').textContent = totalLuas.toFixed(1);
            document.getElementById('sum-laki-harian').textContent = totalL;
            document.getElementById('sum-perempuan-harian').textContent = totalP;
            document.getElementById('sum-total-harian').textContent = totalL + totalP;
        }

        function populateBoronganTable(data) {
            const tbody = document.getElementById('borongan-tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            Tidak ada data tenaga borongan untuk tanggal yang dipilih
                        </td>
                    </tr>
                `;
                return;
            }

            let totalL = 0, totalP = 0, totalLuas = 0;

            data.forEach((item, index) => {
                const row = document.createElement('tr');
                const total = item.jumlahlaki + item.jumlahperempuan;
                
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td style="text-align: left;">${item.mandor_nama || '-'}</td>
                    <td style="text-align: left;">${item.activityname || '-'}</td>
                    <td>${item.blok}</td>
                    <td>${item.plot}</td>
                    <td>${parseFloat(item.luasarea).toFixed(1)}</td>
                    <td>${item.jumlahlaki}</td>
                    <td>${item.jumlahperempuan}</td>
                    <td>${total}</td>
                `;
                tbody.appendChild(row);
                
                totalL += item.jumlahlaki;
                totalP += item.jumlahperempuan;
                totalLuas += parseFloat(item.luasarea);
            });

            // Update totals
            document.getElementById('sum-luas-borongan').textContent = totalLuas.toFixed(1);
            document.getElementById('sum-laki-borongan').textContent = totalL;
            document.getElementById('sum-perempuan-borongan').textContent = totalP;
            document.getElementById('sum-total-borongan').textContent = totalL + totalP;
        }

        function populateAlatTable(data) {
            const tbody = document.getElementById('alat-tbody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            Tidak ada data alat untuk tanggal yang dipilih
                        </td>
                    </tr>
                `;
                return;
            }

            data.forEach((item, index) => {
                const row = document.createElement('tr');
                
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td style="text-align: left;">${item.operator_nama || '-'}</td>
                    <td style="text-align: left;">${item.helper_nama || '-'}</td>
                    <td style="text-align: left;">${item.activityname || '-'}</td>
                    <td>${item.blok}</td>
                    <td>${item.plot}</td>
                    <td>${parseFloat(item.luasarea).toFixed(1)}</td>
                    <td>${item.nokendaraan || '-'}</td>
                    <td style="text-align: left;">${item.jenis || '-'}</td>
                `;
                tbody.appendChild(row);
            });
        }

        function showError(message) {
            document.getElementById('harian-tbody').innerHTML = `
                <tr><td colspan="9" class="empty-state">${message}</td></tr>
            `;
            document.getElementById('borongan-tbody').innerHTML = `
                <tr><td colspan="9" class="empty-state">${message}</td></tr>
            `;
            document.getElementById('alat-tbody').innerHTML = `
                <tr><td colspan="9" class="empty-state">${message}</td></tr>
            `;
        }

        function exportToPDF() {
            window.print();
        }

        function exportToExcel() {
            alert('Fitur export Excel belum diimplementasikan');
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadDTHData();
        });
    </script>
</body>
</html>