<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="suratJalanReport()" x-init="loadData()" class="space-y-5">
        
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Main Content -->
        <div x-show="!loading" style="display: none;" x-transition>
            
            <!-- Header Title -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <h2 class="text-lg font-bold text-gray-800">
                    Report Surat Jalan - {{ Session::get('companycode') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Tanggal: <span class="font-semibold" x-text="data.tanggal"></span>
                </p>
            </div>

            <!-- Summary Info -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-xs text-gray-600">Total Surat Jalan</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="data.summary?.total_sj || 0"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Total Plot</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="data.summary?.total_plot || 0"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Langsir</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="data.summary?.langsir_count || 0"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Tebu Sulit</p>
                        <p class="text-2xl font-bold text-gray-800" x-text="data.summary?.tebu_sulit_count || 0"></p>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Filter</h3>
                    <div class="flex gap-2">
                        <button @click="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-xs font-medium">
                            Apply
                        </button>
                        <button @click="resetFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-xs font-medium">
                            Reset
                        </button>
                        <button @click="exportAll()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-xs font-medium">
                            Export/Print
                        </button>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                        <input type="date" x-model="filters.tanggal" class="w-full text-sm border border-gray-300 rounded p-2">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Plot</label>
                        <select x-model="filters.plot" class="w-full text-sm border border-gray-300 rounded p-2">
                            <option value="">Semua Plot</option>
                            <template x-for="plot in data.filterOptions?.plots" :key="plot">
                                <option :value="plot" x-text="plot"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Data Tables (Grouped by Plot) -->
            <template x-for="(group, index) in data.groupedByPlot" :key="group.plot">
                <div class="bg-white rounded-lg shadow p-4 mb-4">
                    <!-- Plot Header -->
                    <div class="mb-3 pb-2 border-b border-gray-300">
                        <h3 class="text-base font-bold text-gray-800">
                            Plot: <span x-text="group.plot"></span>
                        </h3>
                        <p class="text-xs text-gray-600">
                            Total: <span class="font-semibold" x-text="group.total_sj"></span> Surat Jalan
                        </p>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-400 px-2 py-2">No</th>
                                    <th class="border border-gray-400 px-2 py-2">No SJ</th>
                                    <th class="border border-gray-400 px-2 py-2">Jam Angkut</th>
                                    <th class="border border-gray-400 px-2 py-2">Jam Cetak POS</th>
                                    <th class="border border-gray-400 px-2 py-2">Mandor</th>
                                    <th class="border border-gray-400 px-2 py-2">Umur</th>
                                    <th class="border border-gray-400 px-2 py-2">Kategori</th>
                                    <th class="border border-gray-400 px-2 py-2">Varietas</th>
                                    <th class="border border-gray-400 px-2 py-2">Kode Tebang</th>
                                    <th class="border border-gray-400 px-2 py-2">Langsir</th>
                                    <th class="border border-gray-400 px-2 py-2">Tebu Sulit</th>
                                    <th class="border border-gray-400 px-2 py-2">Jenis Kendaraan</th>
                                    <th class="border border-gray-400 px-2 py-2">No Polisi</th>
                                    <th class="border border-gray-400 px-2 py-2">Rit</th>
                                    <th class="border border-gray-400 px-2 py-2">Supir</th>
                                    <th class="border border-gray-400 px-2 py-2">Kontraktor</th>
                                    <th class="border border-gray-400 px-2 py-2">Sub Kontraktor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, idx) in group.details" :key="item.suratjalanno">
                                    <tr>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="idx + 1"></td>
                                        <td class="border border-gray-400 px-2 py-2" x-text="item.suratjalanno"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="formatDateTime(item.tanggalangkut)"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="formatDateTime(item.tanggalcetakpossecurity)"></td>
                                        <td class="border border-gray-400 px-2 py-2" x-text="item.nama_mandor || item.mandorid"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.umur || '-'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.kategori || '-'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.varietas || '-'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.kodetebang || '-'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.langsir === 1 ? 'Ya' : 'Tidak'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.tebusulit === 1 ? 'Ya' : 'Tidak'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.kendaraankontraktor === 0 ? 'WL' : 'Umum'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.nomorpolisi || '-'"></td>
                                        <td class="border border-gray-400 px-2 py-2 text-center" x-text="item.rit"></td>
                                        <td class="border border-gray-400 px-2 py-2" x-text="item.namasupir || '-'"></td>
                                        <td class="border border-gray-400 px-2 py-2" x-text="item.nama_kontraktor_lengkap || '-'"></td>
                                        <td class="border border-gray-400 px-2 py-2" x-text="item.nama_subkontraktor_lengkap || '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>

            <!-- No Data -->
            <template x-if="!data.groupedByPlot || data.groupedByPlot.length === 0">
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <p class="text-gray-500 text-base">Tidak ada data surat jalan</p>
                </div>
            </template>

        </div>
    </div>

    <script>
    function suratJalanReport() {
        return {
            loading: true,
            data: {
                summary: {},
                groupedByPlot: [],
                filterOptions: {},
                tanggal: ''
            },
            filters: {
                tanggal: new Date().toISOString().split('T')[0],
                plot: ''
            },

            async loadData() {
                this.loading = true;
                try {
                    const params = new URLSearchParams(this.filters);
                    const response = await fetch(`{{ route('report.report-surat-jalan.data') }}?${params}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.data = result.data;
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Load data error:', error);
                    alert('Gagal memuat data');
                } finally {
                    this.loading = false;
                }
            },

            applyFilters() {
                this.loadData();
            },

            resetFilters() {
                this.filters = {
                    tanggal: new Date().toISOString().split('T')[0],
                    plot: ''
                };
                this.loadData();
            },

            formatDateTime(datetime) {
                if (!datetime) return '-';
                const date = new Date(datetime);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${day}/${month} ${hours}:${minutes}`;
            },

            getKategoriColor(kategori) {
                // No colors, just return empty for simple display
                return '';
            },

            exportAll() {
                let html = `
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; font-size: 11px; }
                        h2 { font-size: 14px; margin-bottom: 5px; }
                        .header { margin-bottom: 15px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                        th, td { border: 1px solid black; padding: 4px; text-align: left; }
                        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
                        .plot-header { font-weight: bold; font-size: 12px; margin-top: 15px; margin-bottom: 5px; }
                        .text-center { text-align: center; }
                        @media print {
                            @page { size: landscape; margin: 10mm; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h2>Report Surat Jalan - {{ Session::get('companycode') }}</h2>
                        <p>Tanggal: ${this.data.tanggal}</p>
                        <p>Total Surat Jalan: ${this.data.summary.total_sj} | Total Plot: ${this.data.summary.total_plot} | Langsir: ${this.data.summary.langsir_count} | Tebu Sulit: ${this.data.summary.tebu_sulit_count}</p>
                    </div>
                `;
                
                this.data.groupedByPlot.forEach(group => {
                    html += `<div class="plot-header">Plot: ${group.plot} (Total: ${group.total_sj} SJ)</div>`;
                    html += `<table>`;
                    html += `<thead><tr>`;
                    html += `<th>No</th><th>No SJ</th><th>Jam Angkut</th><th>Jam Cetak POS</th>`;
                    html += `<th>Mandor</th><th>Umur</th><th>Kategori</th><th>Varietas</th><th>Kode Tebang</th>`;
                    html += `<th>Langsir</th><th>Tebu Sulit</th><th>Jenis Kendaraan</th><th>No Polisi</th><th>Rit</th>`;
                    html += `<th>Supir</th><th>Kontraktor</th><th>Sub Kontraktor</th>`;
                    html += `</tr></thead><tbody>`;
                    
                    group.details.forEach((item, idx) => {
                        html += `<tr>`;
                        html += `<td class="text-center">${idx + 1}</td>`;
                        html += `<td>${item.suratjalanno}</td>`;
                        html += `<td class="text-center">${this.formatDateTime(item.tanggalangkut)}</td>`;
                        html += `<td class="text-center">${this.formatDateTime(item.tanggalcetakpossecurity)}</td>`;
                        html += `<td>${item.nama_mandor || item.mandorid}</td>`;
                        html += `<td class="text-center">${item.umur || '-'}</td>`;
                        html += `<td class="text-center">${item.kategori || '-'}</td>`;
                        html += `<td class="text-center">${item.varietas || '-'}</td>`;
                        html += `<td class="text-center">${item.kodetebang || '-'}</td>`;
                        html += `<td class="text-center">${item.langsir === 1 ? 'Ya' : 'Tidak'}</td>`;
                        html += `<td class="text-center">${item.tebusulit === 1 ? 'Ya' : 'Tidak'}</td>`;
                        html += `<td class="text-center">${item.kendaraankontraktor === 0 ? 'WL' : 'Umum'}</td>`;
                        html += `<td class="text-center">${item.nomorpolisi || '-'}</td>`;
                        html += `<td class="text-center">${item.rit}</td>`;
                        html += `<td>${item.namasupir || '-'}</td>`;
                        html += `<td>${item.nama_kontraktor_lengkap || '-'}</td>`;
                        html += `<td>${item.nama_subkontraktor_lengkap || '-'}</td>`;
                        html += `</tr>`;
                    });
                    
                    html += `</tbody></table>`;
                });
                
                html += `</body></html>`;
                
                // Open in new window for print
                const printWindow = window.open('', '_blank');
                printWindow.document.write(html);
                printWindow.document.close();
                printWindow.focus();
                
                // Auto print after a short delay
                setTimeout(() => {
                    printWindow.print();
                }, 500);
            }
        }
    }
    </script>
</x-layout>