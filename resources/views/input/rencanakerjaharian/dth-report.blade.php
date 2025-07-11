<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan DTH - Distribusi Tenaga Harian</title>
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
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 25px 0 15px;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .summary-card {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #6b7280;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .summary-card h3 {
            font-size: 11px;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .summary-card .number {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
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
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            font-size: 11px;
            color: #374151;
            border: 1px solid #e5e7eb;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #f3f4f6;
        }

        /* Column Widths */
        .col-no { width: 4%; }
        .col-mandor { width: 12%; text-align: left; }
        .col-kegiatan { width: 18%; text-align: left; }
        .col-blok { width: 6%; }
        .col-plot { width: 8%; }
        .col-rkh { width: 8%; }
        .col-tenaga { width: 6%; }
        .col-keterangan { width: 12%; text-align: left; }

        /* Footer/Total Row */
        tfoot {
            background: #374151;
            color: white;
        }

        tfoot td {
            font-weight: 600;
            padding: 12px 8px;
            border-color: #4b5563;
        }

        /* Signatures */
        .signatures {
            margin-top: 50px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            text-align: center;
        }

        .signature-box {
            padding: 20px 10px;
        }

        .signature-title {
            font-weight: 600;
            margin-bottom: 60px;
            font-size: 12px;
        }

        .signature-line {
            border-top: 1px solid #374151;
            padding-top: 8px;
            font-size: 11px;
            color: #6b7280;
        }

        /* Print Styles */
        @media print {
            .header-left {
                display: none;
            }
            
            .summary-cards {
                display: none;
            }
            
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            font-style: italic;
        }

        .loading {
            text-align: center;
            padding: 40px 20px;
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
            <div class="header-right">
                <div class="date">Tanggal: <span id="report-date"></span></div>
                <div>Revisi: 1</div>
                <div>Halaman: 1 dari 1</div>
            </div>
        </div>

        <!-- Title -->
        <h1 class="main-title">Distribusi Tenaga Harian (DTH)</h1>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h3>Total Tenaga Harian</h3>
                <div class="number" id="total-harian">-</div>
            </div>
            <div class="summary-card">
                <h3>Total Tenaga Borongan</h3>
                <div class="number" id="total-borongan">-</div>
            </div>
            <div class="summary-card">
                <h3>Grand Total</h3>
                <div class="number" id="grand-total">-</div>
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
                        <th rowspan="2" class="col-rkh">Luas (ha)</th>
                        <th colspan="3" class="col-tenaga">Jumlah Tenaga</th>
                        <th rowspan="2" class="col-keterangan">Keterangan</th>
                    </tr>
                    <tr>
                        <th class="col-tenaga">L</th>
                        <th class="col-tenaga">P</th>
                        <th class="col-tenaga">Total</th>
                    </tr>
                </thead>
                <tbody id="harian-tbody">
                    <tr>
                        <td colspan="10" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: center; font-weight: bold;">TOTAL TENAGA HARIAN</td>
                        <td id="sum-laki-harian">0</td>
                        <td id="sum-perempuan-harian">0</td>
                        <td id="sum-total-harian">0</td>
                        <td></td>
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
                        <th rowspan="2" class="col-rkh">Luas (ha)</th>
                        <th colspan="3" class="col-tenaga">Jumlah Tenaga</th>
                        <th rowspan="2" class="col-keterangan">Keterangan</th>
                    </tr>
                    <tr>
                        <th class="col-tenaga">L</th>
                        <th class="col-tenaga">P</th>
                        <th class="col-tenaga">Total</th>
                    </tr>
                </thead>
                <tbody id="borongan-tbody">
                    <tr>
                        <td colspan="10" class="loading">Memuat data...</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" style="text-align: center; font-weight: bold;">TOTAL TENAGA BORONGAN</td>
                        <td id="sum-laki-borongan">0</td>
                        <td id="sum-perempuan-borongan">0</td>
                        <td id="sum-total-borongan">0</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-title">Mengetahui</div>
                <div class="signature-line">Asisten Lapangan</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Diperiksa</div>
                <div class="signature-line">Asisten Kepala</div>
            </div>
            <div class="signature-box">
                <div class="signature-title">Disiapkan</div>
                <div class="signature-line">PPC</div>
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
                    populateTable(data.harian, 'harian-tbody', 'harian');
                    populateTable(data.borongan, 'borongan-tbody', 'borongan');
                    updateSummary(data.harian, data.borongan);
                } else {
                    showError('Gagal memuat data DTH: ' + data.message);
                }
            } catch (error) {
                console.error('Error loading DTH data:', error);
                showError('Terjadi kesalahan saat memuat data');
            }
        }

        function populateTable(data, tbodyId, category) {
            const tbody = document.getElementById(tbodyId);
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="empty-state">
                            Tidak ada data ${category} untuk tanggal yang dipilih
                        </td>
                    </tr>
                `;
                return { totalL: 0, totalP: 0, totalCount: 0 };
            }

            let totalL = 0, totalP = 0;

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
                    <td style="text-align: left;">${item.description || '-'}</td>
                `;
                tbody.appendChild(row);
                totalL += item.jumlahlaki;
                totalP += item.jumlahperempuan;
            });

            // Update totals for this section
            const prefix = category === 'harian' ? 'harian' : 'borongan';
            document.getElementById(`sum-laki-${prefix}`).textContent = totalL;
            document.getElementById(`sum-perempuan-${prefix}`).textContent = totalP;
            document.getElementById(`sum-total-${prefix}`).textContent = totalL + totalP;

            return { totalL, totalP, totalCount: totalL + totalP };
        }

        function updateSummary(harianData, boronganData) {
            const harianTotal = harianData ? harianData.reduce((sum, item) => sum + item.jumlahlaki + item.jumlahperempuan, 0) : 0;
            const boronganTotal = boronganData ? boronganData.reduce((sum, item) => sum + item.jumlahlaki + item.jumlahperempuan, 0) : 0;
            const grandTotal = harianTotal + boronganTotal;

            document.getElementById('total-harian').textContent = harianTotal;
            document.getElementById('total-borongan').textContent = boronganTotal;
            document.getElementById('grand-total').textContent = grandTotal;
        }

        function showError(message) {
            document.getElementById('harian-tbody').innerHTML = `
                <tr><td colspan="10" class="empty-state">${message}</td></tr>
            `;
            document.getElementById('borongan-tbody').innerHTML = `
                <tr><td colspan="10" class="empty-state">${message}</td></tr>
            `;
        }

        function exportToPDF() {
            window.print();
        }

        function exportToExcel() {
            // Implementasi export Excel jika diperlukan
            alert('Fitur export Excel belum diimplementasikan');
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadDTHData();
        });
    </script>
</body>
</html>