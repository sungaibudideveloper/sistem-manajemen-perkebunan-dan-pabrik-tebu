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
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <!-- Total SJ -->
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-blue-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Surat Jalan</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="data.summary?.total_sj || 0"></p>
                        </div>
                        <div class="bg-blue-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">Surat jalan yang dicetak</div>
                </div>

                <!-- Total Netto -->
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-green-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Netto</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="formatNumber(data.summary?.total_netto || 0)"></p>
                        </div>
                        <div class="bg-green-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">Kilogram</div>
                </div>

                <!-- Pending Timbangan -->
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-amber-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Pending Timbangan</p>
                            <p class="text-4xl font-bold text-amber-600" x-text="data.summary?.pending_timbangan || 0"></p>
                        </div>
                        <div class="bg-amber-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">Dalam perjalanan</div>
                </div>

                <!-- Avg Waktu Tunggu -->
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-purple-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Avg Waktu Tunggu</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="Math.round(data.summary?.avg_waktu_tunggu || 0)"></p>
                        </div>
                        <div class="bg-purple-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">Menit (Masuk - Keluar)</div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Filters & Date Range</h3>
                    <div class="flex gap-2">
                        <button @click="applyFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-1.5 rounded-lg text-xs font-medium transition-colors">
                            Apply Filters
                        </button>
                        <button @click="resetFilters()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-1.5 rounded-lg text-xs font-medium transition-colors">
                            Reset
                        </button>
                    </div>
                </div>
                
                <!-- Date Range Row -->
                <div class="flex flex-wrap gap-3 mb-3 pb-3 border-b border-gray-200">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Mulai</label>
                        <input type="date" x-model="filters.start_date" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Akhir</label>
                        <input type="date" x-model="filters.end_date" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex items-end gap-2">
                        <button @click="setToday()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors">Hari Ini</button>
                        <button @click="setYesterday()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors">Kemarin</button>
                        <button @click="setLast7Days()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors">7 Hari</button>
                        <button @click="setLast30Days()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium transition-colors">30 Hari</button>
                    </div>
                </div>

                <!-- Other Filters Row -->
                <div class="flex flex-wrap gap-3">
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Mandor</label>
                        <select x-model="filters.mandor" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Mandor</option>
                            <template x-for="mandor in data.filterOptions?.mandors" :key="mandor.id">
                                <option :value="mandor.id" x-text="mandor.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Plot</label>
                        <select x-model="filters.plot" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Plot</option>
                            <template x-for="plot in data.filterOptions?.plots" :key="plot">
                                <option :value="plot" x-text="plot"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Kontraktor</label>
                        <select x-model="filters.kontraktor" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Kontraktor</option>
                            <template x-for="kontraktor in data.filterOptions?.kontraktors" :key="kontraktor">
                                <option :value="kontraktor" x-text="kontraktor"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">No Polisi</label>
                        <select x-model="filters.nopol" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Kendaraan</option>
                            <template x-for="nopol in data.filterOptions?.nopols" :key="nopol">
                                <option :value="nopol" x-text="nopol"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Status</label>
                        <select x-model="filters.status" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="sudah">Sudah Timbang</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <!-- Hourly Trend -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Trend Netto Per Jam (Akumulasi)</h3>
                    <canvas id="hourlyChart" height="220"></canvas>
                </div>

                <!-- Status Breakdown -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Status Timbangan</h3>
                    <canvas id="statusChart" height="220"></canvas>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <!-- Mandor Performance -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Top 10 Mandor by Netto</h3>
                    <canvas id="mandorChart" height="220"></canvas>
                </div>

                <!-- Vehicle Performance Table -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Top 10 Kendaraan</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 px-2 py-1.5 text-left">No Polisi</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Trip</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-right">Total Netto</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Avg Tunggu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(vehicle, index) in data.vehiclePerformance" :key="vehicle.nopol">
                                    <tr class="hover:bg-blue-50">
                                        <td class="border border-gray-300 px-2 py-1.5 font-semibold" x-text="vehicle.nopol"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center font-bold text-blue-600" x-text="vehicle.trip_count"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-right" x-text="formatNumber(vehicle.total_netto)"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="vehicle.avg_waktu_tunggu ? vehicle.avg_waktu_tunggu + ' min' : '-'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Detail Table -->
            <div class="bg-white rounded-xl shadow-md p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Detail Surat Jalan</h3>
                    <div class="flex gap-2">
                        <button @click="exportSummary()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                            📊 Export Summary
                        </button>
                        <button @click="exportDetail()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                            📋 Export Detail
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-2 py-2">No</th>
                                <th class="border border-gray-300 px-2 py-2">No SJ</th>
                                <th class="border border-gray-300 px-2 py-2">Mandor</th>
                                <th class="border border-gray-300 px-2 py-2">Plot</th>
                                <th class="border border-gray-300 px-2 py-2">Varietas</th>
                                <th class="border border-gray-300 px-2 py-2">Kategori</th>
                                <th class="border border-gray-300 px-2 py-2">No Polisi</th>
                                <th class="border border-gray-300 px-2 py-2">Supir</th>
                                <th class="border border-gray-300 px-2 py-2">Kontraktor</th>
                                <th class="border border-gray-300 px-2 py-2">Tgl Tebang</th>
                                <th class="border border-gray-300 px-2 py-2">Tgl Angkut</th>
                                <th class="border border-gray-300 px-2 py-2">Jam Masuk</th>
                                <th class="border border-gray-300 px-2 py-2">Jam Keluar</th>
                                <th class="border border-gray-300 px-2 py-2">Bruto</th>
                                <th class="border border-gray-300 px-2 py-2">Netto</th>
                                <th class="border border-gray-300 px-2 py-2">Tunggu (min)</th>
                                <th class="border border-gray-300 px-2 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in data.details" :key="item.suratjalanno">
                                <tr :class="item.status === 'Sudah Timbang' ? 'bg-green-50 hover:bg-green-100' : 'bg-yellow-50 hover:bg-yellow-100'" class="transition-colors">
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="index + 1"></td>
                                    <td class="border border-gray-300 px-2 py-2 font-semibold text-blue-600" x-text="item.suratjalanno"></td>
                                    <td class="border border-gray-300 px-2 py-2" x-text="item.nama_mandor || item.mandorid"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center font-bold" x-text="item.plot"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.varietas || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold" :class="getKategoriColor(item.kategori)" x-text="item.kategori || '-'"></span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center font-medium" x-text="item.nomorpolisi || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2" x-text="item.namasupir || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-xs" x-text="item.namakontraktor || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatDate(item.tanggaltebang)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatDate(item.tanggalangkut)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.jam1 || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.jam2 || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-right" x-text="formatNumber(item.bruto)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-right font-bold" x-text="formatNumber(item.netto)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.waktu_tunggu || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span :class="item.status === 'Sudah Timbang' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-bold" x-text="item.status"></span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="!data.details || data.details.length === 0">
                                <tr>
                                    <td colspan="17" class="border border-gray-300 px-3 py-8 text-center text-gray-500 font-medium">Tidak ada data</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
    function suratJalanReport() {
        return {
            loading: true,
            data: {
                summary: {},
                details: [],
                filterOptions: {},
                hourlyTrend: [],
                statusBreakdown: [],
                mandorPerformance: [],
                vehiclePerformance: []
            },
            filters: {
                start_date: new Date().toISOString().split('T')[0],
                end_date: new Date().toISOString().split('T')[0],
                mandor: '',
                plot: '',
                kontraktor: '',
                nopol: '',
                status: ''
            },
            charts: {
                hourly: null,
                status: null,
                mandor: null
            },

            async loadData() {
                this.loading = true;
                try {
                    const params = new URLSearchParams(this.filters);
                    const response = await fetch(`{{ route('report.report-surat-jalan-timbangan.data') }}?${params}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.data = result.data;
                        await this.$nextTick();
                        this.renderCharts();
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

            renderCharts() {
                this.renderHourlyChart();
                this.renderStatusChart();
                this.renderMandorChart();
            },

            renderHourlyChart() {
                const ctx = document.getElementById('hourlyChart');
                if (!ctx) return;

                if (this.charts.hourly) {
                    this.charts.hourly.destroy();
                }

                this.charts.hourly = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: this.data.hourlyTrend.map(d => d.hour),
                        datasets: [{
                            label: 'Netto Kumulatif (kg)',
                            data: this.data.hourlyTrend.map(d => d.netto),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
                            }
                        }
                    }
                });
            },

            renderStatusChart() {
                const ctx = document.getElementById('statusChart');
                if (!ctx) return;

                if (this.charts.status) {
                    this.charts.status.destroy();
                }

                this.charts.status = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: this.data.statusBreakdown.map(d => d.name),
                        datasets: [{
                            data: this.data.statusBreakdown.map(d => d.value),
                            backgroundColor: ['#10b981', '#f59e0b']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            },

            renderMandorChart() {
                const ctx = document.getElementById('mandorChart');
                if (!ctx) return;

                if (this.charts.mandor) {
                    this.charts.mandor.destroy();
                }

                this.charts.mandor = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.mandorPerformance.map(d => d.nama_mandor),
                        datasets: [{
                            label: 'Total Netto (kg)',
                            data: this.data.mandorPerformance.map(d => d.total_netto),
                            backgroundColor: '#3b82f6',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            },

            applyFilters() {
                this.loadData();
            },

            resetFilters() {
                this.filters = {
                    start_date: new Date().toISOString().split('T')[0],
                    end_date: new Date().toISOString().split('T')[0],
                    mandor: '',
                    plot: '',
                    kontraktor: '',
                    nopol: '',
                    status: ''
                };
                this.loadData();
            },

            setToday() {
                const today = new Date().toISOString().split('T')[0];
                this.filters.start_date = today;
                this.filters.end_date = today;
            },

            setYesterday() {
                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                const dateStr = yesterday.toISOString().split('T')[0];
                this.filters.start_date = dateStr;
                this.filters.end_date = dateStr;
            },

            setLast7Days() {
                const end = new Date();
                const start = new Date();
                start.setDate(start.getDate() - 6);
                this.filters.start_date = start.toISOString().split('T')[0];
                this.filters.end_date = end.toISOString().split('T')[0];
            },

            setLast30Days() {
                const end = new Date();
                const start = new Date();
                start.setDate(start.getDate() - 29);
                this.filters.start_date = start.toISOString().split('T')[0];
                this.filters.end_date = end.toISOString().split('T')[0];
            },

            formatNumber(num) {
                if (!num) return '0';
                return parseFloat(num).toLocaleString('id-ID');
            },

            formatDate(date) {
                if (!date) return '-';
                return new Date(date).toLocaleDateString('id-ID');
            },

            getKategoriColor(kategori) {
                const colors = {
                    'PC': 'bg-emerald-100 text-emerald-800',
                    'RC1': 'bg-blue-100 text-blue-800',
                    'RC2': 'bg-amber-100 text-amber-800',
                    'RC3': 'bg-rose-100 text-rose-800'
                };
                return colors[kategori] || 'bg-gray-100 text-gray-800';
            },

            exportSummary() {
                // Export summary by mandor
                let html = '<table border="1"><thead><tr><th>Mandor</th><th>Total SJ</th><th>Total Netto (kg)</th></tr></thead><tbody>';
                this.data.mandorPerformance.forEach(item => {
                    html += `<tr><td>${item.nama_mandor}</td><td>${item.total_sj}</td><td>${item.total_netto}</td></tr>`;
                });
                html += '</tbody></table>';
                
                const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Summary_SJ_${this.filters.start_date}_${this.filters.end_date}.xls`;
                a.click();
            },

            exportDetail() {
                const table = document.querySelector('table').outerHTML;
                const blob = new Blob([table], { type: 'application/vnd.ms-excel' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Detail_SJ_${this.filters.start_date}_${this.filters.end_date}.xls`;
                a.click();
            }
        }
    }
    </script>
</x-layout>