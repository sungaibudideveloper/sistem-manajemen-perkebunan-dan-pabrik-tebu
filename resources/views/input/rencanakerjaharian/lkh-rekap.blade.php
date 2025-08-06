{{--resources\views\input\rencanakerjaharian\lkh-rekap.blade.php--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap LKH - Laporan Kegiatan Harian</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.3;
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
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .header-left {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #f9fafb;
            color: #374151;
            text-decoration: none;
            font-size: 10px;
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
            font-size: 10px;
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
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            margin: 20px 0 10px;
            padding: 4px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .subsection-title {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            margin: 15px 0 8px;
            padding: 3px 6px;
            background: #f8fafc;
            border-left: 3px solid #6b7280;
        }

        /* Grid Layout for Tables */
        .tables-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .activity-table {
            flex: 1;
            min-width: 180px;
            max-width: calc(20% - 12px); /* 5 tables per row */
        }

        .activity-title {
            font-size: 10px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 5px;
            padding: 2px 4px;
            background: #f1f5f9;
            border-radius: 3px;
            text-align: left;
        }

        /* Compact Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            font-size: 9px;
        }

        th {
            padding: 4px 3px;
            text-align: center;
            font-weight: 600;
            font-size: 8px;
            color: #374151;
            border: 1px solid #d1d5db;
            background: #f8fafc;
        }

        td {
            padding: 4px 3px;
            text-align: center;
            border: 1px solid #d1d5db;
            font-size: 8px;
        }

        /* Column Alignments */
        .col-left { text-align: left; }
        .col-right { text-align: right; }
        .col-center { text-align: center; }

        /* Total Row */
        .total-row {
            background: #f3f4f6;
            font-weight: 600;
        }

        .total-row td {
            border-top: 2px solid #9ca3af;
        }

        /* Signatures */
        .signatures {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            text-align: center;
        }

        .signature-box {
            padding: 15px 8px;
        }

        .signature-title {
            font-weight: 600;
            margin-bottom: 45px;
            font-size: 10px;
        }

        .signature-line {
            border-top: 1px solid #374151;
            padding-top: 5px;
            font-size: 9px;
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
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
            font-size: 10px;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 10px;
        }

        /* Info Box */
        .info-box {
            margin-bottom: 15px;
            font-size: 10px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        /* Alert box for debugging */
        .debug-info {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 9px;
            display: none; /* Hidden by default, can be toggled for debugging */
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
                <button class="btn" onclick="toggleDebugInfo()" style="background: #fbbf24; color: #92400e;">
                    Debug Info
                </button>
            </div>
        </div>

        <!-- Title -->
        <h1 class="main-title">Rekap Laporan Kegiatan Harian (LKH)</h1>

        <!-- Debug Info -->
        <div id="debug-info" class="debug-info">
            <strong>Debug Information:</strong>
            <div id="debug-content">Loading debug info...</div>
        </div>

        <!-- Info Box -->
        <div class="info-box">
            <div>
                <div id="statistics" style="display: flex; gap: 20px; font-size: 9px;">
                    <span>Total LKH: <strong id="stat-total-lkh">0</strong></span>
                    <span>Total Hasil: <strong id="stat-total-hasil">0.00</strong> ha</span>
                    <span>Total Upah: <strong id="stat-total-upah">Rp 0</strong></span>
                    <span>Total Workers: <strong id="stat-total-workers">0</strong></span>
                </div>
            </div>
            <div style="text-align: right; font-size: 11px; color: #6b7280;">
                <div class="date" style="font-weight: 600; color: #111827">Tanggal: <span id="report-date"></span></div>
                <div id="printed-at">Generated: <span id="print-timestamp"></span></div>
                <div id="divisi-info" style="font-weight: 600; color: #111827">Divisi: <span id="company-info">Loading...</span></div>
            </div>
        </div>

        <!-- Section 1: LKH Pengolahan (Activity II, III, IV) -->
        <h2 class="section-title">1. LKH Pengolahan (Activity II, III, IV)</h2>
        <div id="pengolahan-section">
            <div class="loading">Memuat data pengolahan...</div>
        </div>

        <!-- Section 2: LKH Perawatan Manual -->
        <h2 class="section-title">2. LKH Perawatan Manual (Activity V)</h2>
        
        <!-- PC Subsection -->
        <h3 class="subsection-title">PC (Plant Cane)</h3>
        <div id="perawatan-manual-pc-section">
            <div class="loading">Memuat data perawatan manual PC...</div>
        </div>

        <!-- RC Subsection -->
        <h3 class="subsection-title">RC (Ratoon Cane)</h3>
        <div id="perawatan-manual-rc-section">
            <div class="loading">Memuat data perawatan manual RC...</div>
        </div>

        <!-- Section 3: LKH Perawatan Mekanis -->
        <h2 class="section-title">3. LKH Perawatan Mekanis</h2>
        
        <!-- PC Subsection -->
        <h3 class="subsection-title">PC (Plant Cane)</h3>
        <div id="perawatan-mekanis-pc-section">
            <div class="empty-state">Fitur perawatan mekanis akan ditambahkan pada update selanjutnya</div>
        </div>

        <!-- RC Subsection -->
        <h3 class="subsection-title">RC (Ratoon Cane)</h3>
        <div id="perawatan-mekanis-rc-section">
            <div class="empty-state">Fitur perawatan mekanis akan ditambahkan pada update selanjutnya</div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
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

        let globalData = null; // Store loaded data for debugging

        // Load LKH Rekap data - UPDATED to handle new DB structure
        async function loadLKHRekapData() {
            try {
                const response = await fetch(`{{ route('input.rencanakerjaharian.lkh-rekap-data') }}?date=${reportDate}`);
                const data = await response.json();
                
                globalData = data; // Store for debugging
                
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
            let totalLkh = 0;
            let totalHasil = 0;
            let totalWorkers = 0;
            let totalUpah = 0;

            // Calculate from all sections - UPDATED for new structure
            if (data.pengolahan) {
                Object.values(data.pengolahan).forEach(activities => {
                    if (Array.isArray(activities)) {
                        activities.forEach(item => {
                            totalLkh++;
                            totalHasil += parseFloat(item.totalhasil || 0);
                            totalWorkers += parseInt(item.totalworkers || 0);
                            totalUpah += parseFloat(item.totalupahall || 0);
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
                                    totalLkh++;
                                    totalHasil += parseFloat(item.totalhasil || 0);
                                    totalWorkers += parseInt(item.totalworkers || 0);
                                    totalUpah += parseFloat(item.totalupahall || 0);
                                });
                            }
                        });
                    }
                });
            }

            // Update DOM
            const lkhEl = document.getElementById('stat-total-lkh');
            const hasilEl = document.getElementById('stat-total-hasil');
            const workersEl = document.getElementById('stat-total-workers');
            const upahEl = document.getElementById('stat-total-upah');
            
            if (lkhEl) lkhEl.textContent = totalLkh;
            if (hasilEl) hasilEl.textContent = totalHasil.toFixed(2);
            if (workersEl) workersEl.textContent = totalWorkers;
            if (upahEl) upahEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalUpah);
        }

        function populatePengolahanSection(data) {
            const section = document.getElementById('pengolahan-section');
            section.innerHTML = '';

            if (!data || Object.keys(data).length === 0) {
                section.innerHTML = '<div class="empty-state">Tidak ada data pengolahan untuk tanggal yang dipilih</div>';
                return;
            }

            createActivityGrid(section, data, true); // true = show operator column
        }

        function populatePerawatanManualSection(data) {
            populatePerawatanSubsection(data?.pc, 'perawatan-manual-pc-section', 'PC');
            populatePerawatanSubsection(data?.rc, 'perawatan-manual-rc-section', 'RC');
        }

        function populatePerawatanSubsection(data, sectionId, type) {
            const section = document.getElementById(sectionId);
            section.innerHTML = '';

            if (!data || Object.keys(data).length === 0) {
                section.innerHTML = `<div class="empty-state">Tidak ada data perawatan manual ${type} untuk tanggal yang dipilih</div>`;
                return;
            }

            createActivityGrid(section, data, true); // true = show operator column
        }

        function createActivityGrid(section, data, showOperator = true) {
            const tablesGrid = document.createElement('div');
            tablesGrid.className = 'tables-grid';

            Object.keys(data).forEach(activityCode => {
                const activities = data[activityCode];
                if (!Array.isArray(activities) || activities.length === 0) return;

                // Create activity table container
                const activityTable = document.createElement('div');
                activityTable.className = 'activity-table';

                // Activity title with name
                const activityTitle = document.createElement('div');
                activityTitle.className = 'activity-title';
                const activityName = activities[0]?.activityname || 'Unknown Activity';
                activityTitle.textContent = `${activityCode} - ${activityName}`;
                activityTable.appendChild(activityTitle);

                // Create table
                const table = document.createElement('table');
                const headerCols = showOperator ? 
                    '<th>No.</th><th>Operator/Mandor</th><th>Plot</th><th>Workers</th><th>Luas Hasil</th><th>Upah</th>' :
                    '<th>No.</th><th>Mandor</th><th>Plot</th><th>Workers</th><th>Luas Hasil</th><th>Upah</th>';

                table.innerHTML = `
                    <thead>
                        <tr>${headerCols}</tr>
                    </thead>
                    <tbody></tbody>
                `;

                const tbody = table.querySelector('tbody');
                let totalHasil = 0;
                let totalWorkers = 0;
                let totalUpah = 0;

                activities.forEach((item, index) => {
                    const row = document.createElement('tr');
                    const hasil = parseFloat(item.totalhasil || 0);
                    const workers = parseInt(item.totalworkers || 0);
                    const upah = parseFloat(item.totalupahall || 0);
                    
                    totalHasil += hasil;
                    totalWorkers += workers;
                    totalUpah += upah;

                    // UPDATED: Get operator/mandor name and plot info from new structure
                    const operatorName = item.operator || item.mandor_nama || '-';
                    const plotDisplay = `${item.blok}-${item.plot}` || '-';

                    row.innerHTML = `
                        <td>${index + 1}</td>
                        <td class="col-left">${operatorName}</td>
                        <td>${plotDisplay}</td>
                        <td class="col-center">${workers}</td>
                        <td class="col-right">${hasil.toFixed(2)}</td>
                        <td class="col-right">${new Intl.NumberFormat('id-ID').format(upah)}</td>
                    `;
                    tbody.appendChild(row);
                });

                // Add total row
                const totalRow = document.createElement('tr');
                totalRow.className = 'total-row';
                totalRow.innerHTML = `
                    <td colspan="3" class="col-center"><strong>TOTAL</strong></td>
                    <td class="col-center"><strong>${totalWorkers}</strong></td>
                    <td class="col-right"><strong>${totalHasil.toFixed(2)}</strong></td>
                    <td class="col-right"><strong>${new Intl.NumberFormat('id-ID').format(totalUpah)}</strong></td>
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
                if (data.stack) {
                    debugHtml += `<div><strong>Stack:</strong> <pre style="font-size: 8px; white-space: pre-wrap;">${data.stack}</pre></div>`;
                }
            } else {
                debugHtml += `<div><strong>Data loaded successfully</strong></div>`;
                if (data.debug) {
                    debugHtml += '<div><strong>Counts:</strong></div>';
                    Object.keys(data.debug).forEach(key => {
                        debugHtml += `<div>- ${key}: ${data.debug[key]}</div>`;
                    });
                }
                
                if (data.pengolahan) {
                    debugHtml += `<div><strong>Pengolahan activities:</strong> ${Object.keys(data.pengolahan).join(', ')}</div>`;
                }
                
                if (data.perawatan_manual) {
                    if (data.perawatan_manual.pc) {
                        debugHtml += `<div><strong>PC activities:</strong> ${Object.keys(data.perawatan_manual.pc).join(', ')}</div>`;
                    }
                    if (data.perawatan_manual.rc) {
                        debugHtml += `<div><strong>RC activities:</strong> ${Object.keys(data.perawatan_manual.rc).join(', ')}</div>`;
                    }
                }
            }
            
            debugContent.innerHTML = debugHtml;
        }

        function toggleDebugInfo() {
            const debugDiv = document.getElementById('debug-info');
            if (debugDiv.style.display === 'none' || !debugDiv.style.display) {
                debugDiv.style.display = 'block';
            } else {
                debugDiv.style.display = 'none';
            }
        }

        function showError(message) {
            const sections = [
                'pengolahan-section',
                'perawatan-manual-pc-section', 
                'perawatan-manual-rc-section'
            ];
            
            sections.forEach(sectionId => {
                const section = document.getElementById(sectionId);
                if (section) {
                    section.innerHTML = `<div class="empty-state">${message}</div>`;
                }
            });
        }

        function exportToPDF() {
            window.print();
        }

        function exportToExcel() {
            alert('Fitur export Excel belum diimplementasikan');
        }

        // Load data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadLKHRekapData();
        });

        // Add keyboard shortcut for debug toggle
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                toggleDebugInfo();
            }
        });
    </script>
</body>
</html>