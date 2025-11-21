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
            
            <!-- Summary Cards (3 cards only) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
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
                    <div class="text-xs text-gray-600 font-medium">
                        Surat jalan yang dicetak
                        <span x-show="data.dateRange" class="block text-blue-600 font-semibold mt-1" x-text="data.dateRange ? `(${data.dateRange.start} - ${data.dateRange.end})` : ''"></span>
                    </div>
                </div>

                <!-- Total Netto -->
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 border-green-500 transform hover:scale-105 transition-transform hover:shadow-xl">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Total Netto</p>
                            <p class="text-4xl font-bold text-gray-900" x-text="formatTon(data.summary?.total_netto || 0)"></p>
                        </div>
                        <div class="bg-green-100 rounded-lg px-3 py-1.5">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="text-xs text-gray-600 font-medium">
                        Ton
                        <span x-show="data.dateRange" class="block text-green-600 font-semibold mt-1" x-text="data.dateRange ? `(${data.dateRange.start} - ${data.dateRange.end})` : ''"></span>
                    </div>
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
                    <div class="text-xs text-gray-600 font-medium">
                        Dalam perjalanan
                        <span x-show="data.dateRange" class="block text-amber-600 font-semibold mt-1" x-text="data.dateRange ? `(${data.dateRange.start} - ${data.dateRange.end})` : ''"></span>
                    </div>
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
                        <input type="date" x-model="filters.start_date" :disabled="dateRangeLocked" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                    </div>
                    <div class="flex-1 min-w-[140px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Tanggal Akhir</label>
                        <input type="date" x-model="filters.end_date" :disabled="dateRangeLocked" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed">
                    </div>
                    <div class="flex items-end gap-2">
                        <button @click="setToday()" :class="activeRange === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">Hari Ini</button>
                        <button @click="setYesterday()" :class="activeRange === 'yesterday' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">Kemarin</button>
                        <button @click="setLast7Days()" :class="activeRange === '7days' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">7 Hari</button>
                        <button @click="setLast30Days()" :class="activeRange === '30days' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-2 rounded-lg text-xs font-medium transition-colors">30 Hari</button>
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
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Sub Kontraktor</label>
                        <select x-model="filters.subkontraktor" class="w-full text-xs border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Sub Kontraktor</option>
                            <template x-for="subkontraktor in data.filterOptions?.subkontraktors" :key="subkontraktor.id">
                                <option :value="subkontraktor.id" x-text="subkontraktor.name"></option>
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

            <!-- Charts Row 1: SJ & Tonase per Tanggal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <!-- SJ per Tanggal -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Jumlah Surat Jalan</h3>
                        <div class="flex gap-2">
                            <button @click="setSJPeriod('daily')" :class="sjPeriod === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors">Harian</button>
                            <button @click="setSJPeriod('monthly')" :class="sjPeriod === 'monthly' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors">Bulanan</button>
                        </div>
                    </div>
                    <canvas id="sjPerTanggalChart" height="220"></canvas>
                </div>

                <!-- Tonase per Tanggal -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Hasil Tonase</h3>
                        <div class="flex gap-2">
                            <button @click="setTonasePeriod('daily')" :class="tonasePeriod === 'daily' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors">Harian</button>
                            <button @click="setTonasePeriod('monthly')" :class="tonasePeriod === 'monthly' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="px-3 py-1 rounded text-xs font-semibold transition-colors">Bulanan</button>
                        </div>
                    </div>
                    <canvas id="tonasePerTanggalChart" height="220"></canvas>
                </div>
            </div>

            <!-- Charts Row 2: Hourly (if single day) - REDUCED HEIGHT -->
            <div x-show="data.isSingleDay" class="mb-5">
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Trend Netto Per Jam (Akumulasi)</h3>
                    <canvas id="hourlyChart" height="90"></canvas>
                </div>
            </div>

            <!-- Charts Row 3: Status, Vehicle, Durasi (3 kolom) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <!-- Status Breakdown -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Status Timbangan</h3>
                    <canvas id="statusChart" height="200"></canvas>
                </div>

                <!-- Vehicle Performance List (Scrollable) -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Performance Kendaraan</h3>
                    <div class="overflow-y-auto max-h-80">
                        <table class="min-w-full text-xs">
                            <thead class="sticky top-0 bg-gray-100 z-10">
                                <tr>
                                    <th class="border border-gray-300 px-2 py-1.5 text-left">No Polisi</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Trip</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-right">Total Netto</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Avg Deload</th>
                                    <th class="border border-gray-300 px-2 py-1.5 text-center">Avg POS-Timbang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(vehicle, index) in data.vehiclePerformance" :key="vehicle.nopol">
                                    <tr class="hover:bg-blue-50">
                                        <td class="border border-gray-300 px-2 py-1.5 font-semibold" x-text="vehicle.nopol"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center font-bold text-blue-600" x-text="vehicle.trip_count"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-right" x-text="formatNumber(vehicle.total_netto)"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="formatDuration(vehicle.avg_durasi_deload)"></td>
                                        <td class="border border-gray-300 px-2 py-1.5 text-center" x-text="formatDuration(vehicle.avg_durasi_pos_timbang)"></td>
                                    </tr>
                                </template>
                                <template x-if="!data.vehiclePerformance || data.vehiclePerformance.length === 0">
                                    <tr>
                                        <td colspan="5" class="border border-gray-300 px-2 py-4 text-center text-gray-500">Tidak ada data</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Durasi Perjalanan Chart -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Distribusi Durasi Perjalanan</h3>
                    <p class="text-xs text-gray-500 mb-3">Waktu dari Cetak POS ke Timbangan</p>
                    <canvas id="durasiChart" height="200"></canvas>
                </div>
            </div>

            <!-- Charts Row 4: Compact Stats Cards (4 in 1 row) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <!-- Langsir -->
                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Langsir</h3>
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-4xl font-bold text-green-600 mb-1" x-text="data.langsirPercentage + '%'"></div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold" x-text="data.langsirCount"></span> langsir, 
                        <span class="font-semibold" x-text="data.nonLangsirCount"></span> non-langsir
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>

                <!-- Tebu Sulit -->
                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-red-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Tebu Sulit</h3>
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="text-4xl font-bold text-red-600 mb-1" x-text="data.tebuSulitPercentage + '%'"></div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold" x-text="data.tebuSulitCount"></span> sulit, 
                        <span class="font-semibold" x-text="data.tebuNormalCount"></span> normal
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>

                <!-- Kode Tebang -->
                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-purple-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Kode Tebang</h3>
                        <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-2 mb-1">
                        <span class="text-2xl font-bold text-purple-600" x-text="data.premiumCount"></span>
                        <span class="text-sm text-gray-500">-</span>
                        <span class="text-2xl font-bold text-gray-600" x-text="data.nonPremiumCount"></span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold">Premium</span> - 
                        <span class="font-semibold">Non-Premium</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>

                <!-- Jenis Kendaraan -->
                <div class="bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-gray-700 uppercase">Kendaraan</h3>
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                        </svg>
                    </div>
                    <div class="flex items-baseline gap-2 mb-1">
                        <span class="text-2xl font-bold text-blue-600" x-text="data.kendaraanWL"></span>
                        <span class="text-sm text-gray-500">-</span>
                        <span class="text-2xl font-bold text-orange-600" x-text="data.kendaraanUmum"></span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <span class="font-semibold">WL</span> - 
                        <span class="font-semibold">Umum</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">dari <span x-text="data.summary?.total_sj"></span> total</div>
                </div>
            </div>

            <!-- Charts Row 5: Kontraktor & Subkontraktor (2 horizontal bars) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <!-- Kontraktor by Tonase -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Kontraktor by Tonase</h3>
                    <canvas id="kontraktorChart" height="220"></canvas>
                </div>

                <!-- Subkontraktor by Tonase -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">Sub Kontraktor by Tonase</h3>
                    <canvas id="subkontraktorChart" height="220"></canvas>
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
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">No</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">No SJ</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Tgl SJ</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Mandor</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Plot</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Umur<br>(bulan)</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Kategori</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Varietas</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Kode<br>Tebang</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Langsir</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Tebu<br>Sulit</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Jenis<br>Kendaraan</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">No Polisi</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Supir</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Kontraktor</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Sub Kontraktor</th>
                                <th colspan="4" class="border border-gray-300 px-2 py-1 text-center bg-blue-50">Waktu</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Bruto</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Netto</th>
                                <th colspan="2" class="border border-gray-300 px-2 py-1 text-center bg-purple-50">Durasi</th>
                                <th rowspan="2" class="border border-gray-300 px-2 py-2">Status</th>
                            </tr>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-2 py-1 text-xs bg-blue-50">Jam<br>Angkut</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs bg-blue-50">Jam<br>Cetak POS</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs bg-blue-50">Jam<br>Masuk</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs bg-blue-50">Jam<br>Keluar</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs bg-purple-50">POS ke<br>Timbang<br>(min)</th>
                                <th class="border border-gray-300 px-2 py-1 text-xs bg-purple-50">Durasi<br>Deload<br>(min)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, index) in data.details" :key="item.suratjalanno">
                                <tr :class="item.status === 'Sudah Timbang' ? 'bg-green-50 hover:bg-green-100' : 'bg-yellow-50 hover:bg-yellow-100'" class="transition-colors">
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="index + 1"></td>
                                    <td class="border border-gray-300 px-2 py-2">
                                        <a :href="`{{ route('report.report-surat-jalan-timbangan.index') }}/${item.suratjalanno}`" 
                                           class="font-semibold text-blue-600 hover:text-blue-800 hover:underline"
                                           x-text="item.suratjalanno"></a>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="formatDate(item.tanggalcetakpossecurity)"></td>
                                    <td class="border border-gray-300 px-2 py-2" x-text="item.nama_mandor || item.mandorid"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center font-bold" x-text="item.plot"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.umur || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span class="px-2 py-1 rounded text-xs font-semibold" :class="getKategoriColor(item.kategori)" x-text="item.kategori || '-'"></span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center" x-text="item.varietas || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span :class="item.kodetebang === 'Premium' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 rounded text-xs font-semibold" x-text="item.kodetebang || '-'"></span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span x-show="item.langsir === 1" class="text-green-600">✓</span>
                                        <span x-show="item.langsir === 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span x-show="item.tebusulit === 1" class="text-red-600">✓</span>
                                        <span x-show="item.tebusulit === 0" class="text-gray-400">-</span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span :class="item.kendaraankontraktor === 0 ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'" class="px-2 py-1 rounded text-xs font-semibold" x-text="item.kendaraankontraktor === 0 ? 'WL' : 'Umum'"></span>
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-center font-medium" x-text="item.nomorpolisi || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2" x-text="item.namasupir || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-xs" x-text="item.nama_kontraktor_lengkap || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-xs" x-text="item.nama_subkontraktor_lengkap || '-'"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50" x-text="formatTime24(item.tanggalangkut)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50" x-text="formatTime24(item.tanggalcetakpossecurity)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50" x-text="formatTime24FromJam(item.jam1)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center bg-blue-50" x-text="formatTime24FromJam(item.jam2)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-right" x-text="formatNumber(item.bruto)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-right font-bold" x-text="formatNumber(item.netto)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center bg-purple-50" x-text="formatDuration(item.durasi_pos_timbangan)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center bg-purple-50" x-text="formatDuration(item.durasi_deload)"></td>
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        <span :class="item.status === 'Sudah Timbang' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="px-2 py-1 rounded-full text-xs font-bold" x-text="item.status"></span>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="!data.details || data.details.length === 0">
                                <tr>
                                    <td colspan="24" class="border border-gray-300 px-3 py-8 text-center text-gray-500 font-medium">Tidak ada data</td>
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
            dateRangeLocked: false,
            activeRange: '',
            sjPeriod: 'daily',
            tonasePeriod: 'daily',
            data: {
                summary: {},
                details: [],
                filterOptions: {},
                hourlyTrend: [],
                statusBreakdown: [],
                vehiclePerformance: [],
                sjDaily: [],
                sjMonthly: [],
                tonaseDaily: [],
                tonaseMonthly: [],
                durasiPerjalanan: [],
                langsirCount: 0,
                langsirPercentage: 0,
                nonLangsirCount: 0,
                tebuSulitCount: 0,
                tebuSulitPercentage: 0,
                tebuNormalCount: 0,
                premiumCount: 0,
                nonPremiumCount: 0,
                kendaraanWL: 0,
                kendaraanUmum: 0,
                kontraktorTonase: [],
                subkontraktorTonase: [],
                isSingleDay: true
            },
            filters: {
                start_date: new Date().toISOString().split('T')[0],
                end_date: new Date().toISOString().split('T')[0],
                mandor: '',
                plot: '',
                kontraktor: '',
                subkontraktor: '',
                nopol: '',
                status: ''
            },
            charts: {
                hourly: null,
                status: null,
                sjPerTanggal: null,
                tonasePerTanggal: null,
                durasi: null,
                kontraktor: null,
                subkontraktor: null
            },
            chartTimers: {
                sj: null,
                tonase: null
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
                this.renderSJPerTanggalChart();
                this.renderTonasePerTanggalChart();
                if (this.data.isSingleDay) {
                    this.renderHourlyChart();
                }
                this.renderStatusChart();
                this.renderDurasiChart();
                this.renderKontraktorChart();
                this.renderSubkontraktorChart();
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
                            legend: { 
                                position: 'bottom',
                                labels: { font: { size: 10 } }
                            }
                        }
                    }
                });
            },

            setSJPeriod(period) {
                if (this.sjPeriod === period) return;
                this.sjPeriod = period;
                
                // Clear previous timer
                if (this.chartTimers.sj) {
                    clearTimeout(this.chartTimers.sj);
                }
                
                // Debounce with 300ms delay
                this.chartTimers.sj = setTimeout(() => {
                    this.renderSJPerTanggalChart();
                }, 300);
            },

            setTonasePeriod(period) {
                if (this.tonasePeriod === period) return;
                this.tonasePeriod = period;
                
                // Clear previous timer
                if (this.chartTimers.tonase) {
                    clearTimeout(this.chartTimers.tonase);
                }
                
                // Debounce with 300ms delay
                this.chartTimers.tonase = setTimeout(() => {
                    this.renderTonasePerTanggalChart();
                }, 300);
            },

            renderSJPerTanggalChart() {
                const ctx = document.getElementById('sjPerTanggalChart');
                if (!ctx) return;

                // Destroy existing chart safely
                if (this.charts.sjPerTanggal) {
                    try {
                        this.charts.sjPerTanggal.destroy();
                    } catch (e) {
                        console.warn('Chart destroy error:', e);
                    }
                    this.charts.sjPerTanggal = null;
                }

                // Wait for DOM to be ready
                requestAnimationFrame(() => {
                    const chartData = this.sjPeriod === 'monthly' ? this.data.sjMonthly : this.data.sjDaily;

                    try {
                        this.charts.sjPerTanggal = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.map(d => d.label),
                                datasets: [{
                                    label: 'Jumlah SJ',
                                    data: chartData.map(d => d.value),
                                    backgroundColor: '#3b82f6',
                                    borderRadius: 6
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
                    } catch (e) {
                        console.error('Chart render error:', e);
                    }
                });
            },

            renderTonasePerTanggalChart() {
                const ctx = document.getElementById('tonasePerTanggalChart');
                if (!ctx) return;

                // Destroy existing chart safely
                if (this.charts.tonasePerTanggal) {
                    try {
                        this.charts.tonasePerTanggal.destroy();
                    } catch (e) {
                        console.warn('Chart destroy error:', e);
                    }
                    this.charts.tonasePerTanggal = null;
                }

                // Wait for DOM to be ready
                requestAnimationFrame(() => {
                    const chartData = this.tonasePeriod === 'monthly' ? this.data.tonaseMonthly : this.data.tonaseDaily;

                    try {
                        this.charts.tonasePerTanggal = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: chartData.map(d => d.label),
                                datasets: [{
                                    label: 'Tonase (kg)',
                                    data: chartData.map(d => d.value),
                                    backgroundColor: '#10b981',
                                    borderRadius: 6
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
                    } catch (e) {
                        console.error('Chart render error:', e);
                    }
                });
            },

            renderDurasiChart() {
                const ctx = document.getElementById('durasiChart');
                if (!ctx || !this.data.durasiPerjalanan || this.data.durasiPerjalanan.length === 0) return;

                if (this.charts.durasi) {
                    this.charts.durasi.destroy();
                }

                this.charts.durasi = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.durasiPerjalanan.map(d => d.label),
                        datasets: [{
                            label: 'Jumlah SJ',
                            data: this.data.durasiPerjalanan.map(d => d.value),
                            backgroundColor: '#8b5cf6',
                            borderRadius: 6
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
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            },

            renderKontraktorChart() {
                const ctx = document.getElementById('kontraktorChart');
                if (!ctx || !this.data.kontraktorTonase || this.data.kontraktorTonase.length === 0) return;

                if (this.charts.kontraktor) {
                    this.charts.kontraktor.destroy();
                }

                this.charts.kontraktor = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.kontraktorTonase.map(d => d.name),
                        datasets: [{
                            label: 'Tonase (ton)',
                            data: this.data.kontraktorTonase.map(d => d.value),
                            backgroundColor: '#10b981',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.x.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('id-ID') + ' ton';
                                    }
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
                            }
                        }
                    }
                });
            },

            renderSubkontraktorChart() {
                const ctx = document.getElementById('subkontraktorChart');
                if (!ctx || !this.data.subkontraktorTonase || this.data.subkontraktorTonase.length === 0) return;

                if (this.charts.subkontraktor) {
                    this.charts.subkontraktor.destroy();
                }

                this.charts.subkontraktor = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: this.data.subkontraktorTonase.map(d => d.name),
                        datasets: [{
                            label: 'Tonase (ton)',
                            data: this.data.subkontraktorTonase.map(d => d.value),
                            backgroundColor: '#f59e0b',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.parsed.x.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ton';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('id-ID') + ' ton';
                                    }
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
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
                    subkontraktor: '',
                    nopol: '',
                    status: ''
                };
                this.activeRange = '';
                this.dateRangeLocked = false;
                this.sjPeriod = 'daily';
                this.tonasePeriod = 'daily';
                this.loadData();
            },

            setToday() {
                const today = new Date().toISOString().split('T')[0];
                this.filters.start_date = today;
                this.filters.end_date = today;
                this.activeRange = 'today';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            setYesterday() {
                const yesterday = new Date();
                yesterday.setDate(yesterday.getDate() - 1);
                const dateStr = yesterday.toISOString().split('T')[0];
                this.filters.start_date = dateStr;
                this.filters.end_date = dateStr;
                this.activeRange = 'yesterday';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            setLast7Days() {
                const end = new Date();
                const start = new Date();
                start.setDate(start.getDate() - 6);
                this.filters.start_date = start.toISOString().split('T')[0];
                this.filters.end_date = end.toISOString().split('T')[0];
                this.activeRange = '7days';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            setLast30Days() {
                const end = new Date();
                const start = new Date();
                start.setDate(start.getDate() - 29);
                this.filters.start_date = start.toISOString().split('T')[0];
                this.filters.end_date = end.toISOString().split('T')[0];
                this.activeRange = '30days';
                this.dateRangeLocked = true;
                this.applyFilters();
            },

            formatNumber(num) {
                if (!num) return '0';
                return parseFloat(num).toLocaleString('id-ID');
            },
            
            formatTon(kg) {
                if (!kg) return '0.00';
                const ton = parseFloat(kg) / 1000;
                return ton.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatDuration(minutes) {
                if (!minutes) return '-';
                const mins = parseFloat(minutes);
                if (mins < 60) {
                    return mins.toFixed(2) + ' menit';
                }
                const hours = Math.floor(mins / 60);
                const remainingMins = Math.round(mins % 60);
                return `${hours} jam ${remainingMins} menit`;
            },

            formatDate(date) {
                if (!date) return '-';
                return new Date(date).toLocaleDateString('id-ID');
            },

            formatTime24(datetime) {
                if (!datetime) return '-';
                const date = new Date(datetime);
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                return `${hours}:${minutes}`;
            },

            formatTime24FromJam(jam) {
                if (!jam) return '-';
                // jam format: "04:18:20" atau "04:18"
                const parts = jam.split(':');
                if (parts.length >= 2) {
                    return `${parts[0]}:${parts[1]}`;
                }
                return jam;
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
                const mandorSummary = {};
                
                this.data.details.forEach(item => {
                    const mandor = item.nama_mandor || item.mandorid;
                    if (!mandorSummary[mandor]) {
                        mandorSummary[mandor] = { count: 0, netto: 0 };
                    }
                    mandorSummary[mandor].count++;
                    if (item.netto) {
                        mandorSummary[mandor].netto += parseFloat(item.netto);
                    }
                });
                
                Object.keys(mandorSummary).forEach(mandor => {
                    html += `<tr><td>${mandor}</td><td>${mandorSummary[mandor].count}</td><td>${mandorSummary[mandor].netto}</td></tr>`;
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