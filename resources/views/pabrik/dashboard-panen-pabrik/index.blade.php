<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="dashboardPabrik()" x-init="loadData()" class="space-y-6">
        
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-700"></div>
        </div>

        <!-- No Data State -->
        <div x-show="!loading && !data.has_data" class="bg-gray-50 border border-gray-300 p-6 rounded-lg" style="display: none;">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Belum Ada Data Panen</h3>
                    <p class="text-gray-600 mt-1" x-text="data.message"></p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div x-show="!loading && data.has_data" style="display: none;" x-transition>
            
            <!-- Header with Year Filter -->
            <div class="bg-white border-b-4 border-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Dashboard Panen Pabrik</h2>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium">Periode:</span>
                                <span class="font-semibold text-gray-900" x-text="data.periode ? `${data.periode.start} - ${data.periode.end}` : '-'"></span>
                            </div>
                            <div class="text-xs bg-gray-100 px-3 py-1 rounded font-medium text-gray-700">
                                6 Bulan Musim Giling
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700">Tahun:</label>
                        <select x-model="filters.tahun" @change="loadData()" class="bg-white border-2 border-gray-300 text-gray-900 rounded-lg px-4 py-2 font-semibold focus:ring-2 focus:ring-gray-500 focus:border-gray-500">
                            <option value="2024">2024</option>
                            <option value="2025" selected>2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Overall Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
                <!-- Progress Overall -->
                <div class="bg-white rounded-lg shadow border-l-4 border-gray-600 p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Progress Overall</p>
                            <p class="text-4xl font-bold text-gray-600" x-text="data.summary?.overall_percentage + '%'"></p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-2">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold text-gray-900" x-text="formatNumber(data.summary?.total_progress)"></span> ha / 
                        <span x-text="formatNumber(data.summary?.total_target)"></span> ha
                    </div>
                </div>

                <!-- Total Rit -->
                <div class="bg-white rounded-lg shadow border-l-4 border-green-600 p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Rit</p>
                            <p class="text-4xl font-bold text-gray-600" x-text="formatNumber(data.summary?.total_rit)"></p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600">Total perjalanan</div>
                </div>

                <!-- Total Tonase -->
                <div class="bg-white rounded-lg shadow border-l-4 border-purple-600 p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Tonase</p>
                            <p class="text-4xl font-bold text-gray-600" x-text="formatTon(data.summary?.total_tonase)"></p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-2">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600">Ton</div>
                </div>

                <!-- Average Trash -->
                <div class="bg-white rounded-lg shadow border-l-4 border-orange-600 p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Avg Trash</p>
                            <p class="text-4xl font-bold text-gray-600" x-text="data.trash?.avg_trash_percentage + '%'"></p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-2">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600">
                        <span x-text="data.trash?.total_sampling"></span> sampling
                    </div>
                </div>
            </div>

            <!-- Progress per Company Table -->
            <div class="bg-white rounded-lg shadow border border-gray-200 p-6 mb-6">
                <h3 class="text-base font-bold text-gray-900 mb-4 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Progress Panen Per Company
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border-r border-gray-200 px-4 py-3 text-left font-semibold text-gray-700">Company</th>
                                <th class="border-r border-gray-200 px-4 py-3 text-right font-semibold text-gray-700">Luas Terpanen<br><span class="text-xs font-normal text-gray-500">(ha)</span></th>
                                <th class="border-r border-gray-200 px-4 py-3 text-right font-semibold text-gray-700">Total Luas Area<br><span class="text-xs font-normal text-gray-500">(ha)</span></th>
                                <th class="border-r border-gray-200 px-4 py-3 text-center font-semibold text-gray-700" style="min-width: 280px;">Progress Percentage</th>
                                <th class="border-r border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">Total<br>Rit</th>
                                <th class="border-r border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">Sudah<br>Timbang</th>
                                <th class="border-r border-gray-200 px-4 py-3 text-center font-semibold text-gray-700">Pending</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700">Total<br>Tonase</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="company in data.companies" :key="company.companycode">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="border-r border-gray-200 px-4 py-3 font-bold text-gray-900" x-text="company.companyname"></td>
                                    <td class="border-r border-gray-200 px-4 py-3 text-right font-semibold text-blue-600" x-text="formatNumber(company.progress)"></td>
                                    <td class="border-r border-gray-200 px-4 py-3 text-right font-semibold text-gray-700" x-text="formatNumber(company.target)"></td>
                                    <td class="border-r border-gray-200 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-1 bg-gray-200 rounded-full h-7 overflow-hidden relative">
                                                <div class="h-full transition-all duration-500 bg-blue-600"
                                                     :style="`width: ${company.percentage}%`"></div>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900 min-w-[45px] text-right" x-text="company.percentage + '%'"></span>
                                        </div>
                                    </td>
                                    <td class="border-r border-gray-200 px-4 py-3 text-center text-gray-900" x-text="formatNumber(company.total_rit)"></td>
                                    <td class="border-r border-gray-200 px-4 py-3 text-center text-green-600" x-text="formatNumber(company.sudah_timbang)"></td>
                                    <td class="border-r border-gray-200 px-4 py-3 text-center text-orange-600" x-text="formatNumber(company.pending_timbang)"></td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900" x-text="formatTon(company.total_tonase)"></td>
                                </tr>
                            </template>
                            <template x-if="!data.companies || data.companies.length === 0">
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 font-medium">Tidak ada data</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Charts Row 1: Trend & Daily Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Monthly Trend -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wide">Trend Bulanan Tonase</h3>
                    <canvas id="monthlyTrendChart" height="200"></canvas>
                </div>

                <!-- Daily Performance (Last 7 Days) -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wide">Performance 7 Hari Terakhir</h3>
                    <canvas id="dailyPerformanceChart" height="200"></canvas>
                </div>
            </div>

            <!-- Trash Analysis -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Trash Summary -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wide">Analisa Trash</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm text-gray-600 font-medium">Total Sampling:</span>
                            <span class="text-lg font-bold text-gray-900" x-text="data.trash?.total_sampling"></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm text-gray-600 font-medium">Avg Trash %:</span>
                            <span class="text-lg font-bold text-gray-900" x-text="data.trash?.avg_trash_percentage + '%'"></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="text-sm text-gray-600 font-medium">Above Tolerance:</span>
                            <span class="text-lg font-bold text-gray-900" x-text="data.trash?.above_tolerance_pct + '%'"></span>
                        </div>
                    </div>
                </div>

                <!-- Trash by Company -->
                <div class="bg-white rounded-lg shadow border border-gray-200 p-6 lg:col-span-2">
                    <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wide">Trash per Company</h3>
                    <canvas id="trashCompanyChart" height="150"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script>
    function dashboardPabrik() {
        return {
            loading: true,
            data: {
                has_data: false,
                companies: [],
                summary: {},
                trash: {},
                trend: {},
                daily_performance: []
            },
            filters: {
                tahun: new Date().getFullYear()
            },
            charts: {
                monthlyTrend: null,
                dailyPerformance: null,
                trashCompany: null
            },

            async loadData() {
                this.loading = true;
                try {
                    const params = new URLSearchParams(this.filters);
                    const response = await fetch(`{{ route('pabrik.panen-pabrik.data') }}?${params}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.data = result.data;
                        
                        if (this.data.has_data) {
                            await this.$nextTick();
                            this.renderCharts();
                        }
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
                this.renderMonthlyTrendChart();
                this.renderDailyPerformanceChart();
                this.renderTrashCompanyChart();
            },

            renderMonthlyTrendChart() {
                const ctx = document.getElementById('monthlyTrendChart');
                if (!ctx) return;

                if (this.charts.monthlyTrend) {
                    this.charts.monthlyTrend.destroy();
                }

                const datasets = this.data.trend.monthly.map((company, index) => {
                    const colors = ['#2563eb', '#059669', '#7c3aed', '#dc2626', '#ea580c'];
                    return {
                        label: company.company,
                        data: company.data.map(d => d.tonase),
                        borderColor: colors[index % colors.length],
                        backgroundColor: colors[index % colors.length],
                        tension: 0.3,
                        borderWidth: 2
                    };
                });

                const labels = this.data.trend.monthly[0]?.data.map(d => d.month) || [];

                this.charts.monthlyTrend = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { 
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
                                    font: { size: 11 }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('id-ID') + ' ton';
                                    }
                                }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            },

            renderDailyPerformanceChart() {
                const ctx = document.getElementById('dailyPerformanceChart');
                if (!ctx) return;

                if (this.charts.dailyPerformance) {
                    this.charts.dailyPerformance.destroy();
                }

                this.charts.dailyPerformance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.daily_performance.map(d => d.date),
                        datasets: [
                            {
                                label: 'Rit',
                                data: this.data.daily_performance.map(d => d.rit),
                                backgroundColor: '#10b981',
                                yAxisID: 'y',
                                borderRadius: 4
                            },
                            {
                                label: 'Tonase (ton)',
                                data: this.data.daily_performance.map(d => d.tonase),
                                backgroundColor: '#8b5cf6',
                                yAxisID: 'y1',
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { 
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
                                    font: { size: 11 }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                grid: { color: '#f3f4f6' },
                                title: { display: true, text: 'Rit', font: { size: 11 } }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: { display: true, text: 'Ton', font: { size: 11 } },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
            },

            renderTrashCompanyChart() {
                const ctx = document.getElementById('trashCompanyChart');
                if (!ctx) return;

                if (this.charts.trashCompany) {
                    this.charts.trashCompany.destroy();
                }

                this.charts.trashCompany = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.trash.by_company.map(c => c.company),
                        datasets: [{
                            label: 'Average Trash %',
                            data: this.data.trash.by_company.map(c => c.avg_trash),
                            backgroundColor: '#ea580c',
                            borderRadius: 4
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
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    callback: function(value) {
                                        return value + '%';
                                    }
                                }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            },

            formatNumber(num) {
                if (!num) return '0';
                return parseFloat(num).toLocaleString('id-ID', { maximumFractionDigits: 2 });
            },
            
            formatTon(kg) {
                if (!kg) return '0.00';
                const ton = parseFloat(kg) / 1000;
                return ton.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }
    }
    </script>
</x-layout>