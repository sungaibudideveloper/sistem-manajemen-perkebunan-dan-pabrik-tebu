<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="suratJalanDetail()" x-init="loadData()" class="space-y-5">
        
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>

        <!-- Main Content -->
        <div x-show="!loading" style="display: none;" x-transition>
            
            <!-- Header with Back Button -->
            <div class="bg-white rounded-xl shadow-md p-4 mb-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('report.report-surat-jalan-timbangan.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900" x-text="'Detail Surat Jalan: ' + (data.suratjalanno || '')"></h1>
                            <p class="text-sm text-gray-600 mt-1">Informasi lengkap perjalanan surat jalan</p>
                        </div>
                    </div>
                    <div>
                        <span x-show="data.status === 'Sudah Timbang'" class="px-3 py-1.5 bg-green-100 text-green-800 rounded-full text-xs font-bold">✓ Timbang</span>
                        <span x-show="data.status === 'Pending'" class="px-3 py-1.5 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold">⏳ Pending</span>
                    </div>
                </div>
            </div>

            <!-- Timeline, Duration, and Timbangan Cards (3:3:2 ratio) -->
            <div class="grid grid-cols-1 md:grid-cols-8 gap-5 mb-5">
                <!-- Timeline Card (3/8 width) -->
                <div class="md:col-span-3 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-6">Timeline Perjalanan</h2>
                    <div class="relative">
                        <!-- Timeline Line -->
                        <div class="absolute left-7 top-7 bottom-7 w-0.5 bg-gradient-to-b from-blue-200 via-purple-200 to-red-200"></div>
                        
                        <!-- Timeline Events -->
                        <div class="space-y-5">
                            <!-- Tebang -->
                            <div class="relative flex items-center gap-4">
                                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center z-10 shadow-lg transform hover:scale-105 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-800 text-base">Tebang</h3>
                                    <p class="text-sm text-gray-500 mt-1" x-text="formatDate(data.tanggaltebang)"></p>
                                </div>
                            </div>

                            <!-- Angkut -->
                            <div class="relative flex items-center gap-4">
                                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center z-10 shadow-lg transform hover:scale-105 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-800 text-base">Angkut</h3>
                                    <p class="text-sm text-gray-500 mt-1" x-text="formatDateTime(data.tanggalangkut)"></p>
                                </div>
                            </div>

                            <!-- Cetak POS -->
                            <div class="relative flex items-center gap-4">
                                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center z-10 shadow-lg transform hover:scale-105 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-800 text-base">Cetak POS Security</h3>
                                    <p class="text-sm text-gray-500 mt-1" x-text="formatDateTime(data.tanggalcetakpossecurity)"></p>
                                </div>
                            </div>

                            <!-- Masuk Timbangan -->
                            <div x-show="data.jam1" class="relative flex items-center gap-4">
                                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center z-10 shadow-lg transform hover:scale-105 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-800 text-base">Masuk Timbangan</h3>
                                    <p class="text-sm text-gray-500 mt-1" x-text="formatDateTime2(data.tgl1, data.jam1)"></p>
                                </div>
                            </div>

                            <!-- Keluar Timbangan -->
                            <div x-show="data.jam2" class="relative flex items-center gap-4">
                                <div class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center z-10 shadow-lg transform hover:scale-105 transition-transform">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                </div>
                                <div class="flex-grow">
                                    <h3 class="font-bold text-gray-800 text-base">Keluar Timbangan</h3>
                                    <p class="text-sm text-gray-500 mt-1" x-text="formatDateTime2(data.tgl2, data.jam2)"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Duration Card (3/8 width) -->
                <div class="md:col-span-3 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-6">Ringkasan Durasi</h2>
                    <div class="space-y-4">
                        <!-- Total Durasi Perjalanan -->
                        <div class="pb-4 border-b border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-gray-600 font-medium">Total Durasi Perjalanan</div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900" x-text="formatDuration(data.total_durasi)"></div>
                            <div class="text-xs text-gray-500 mt-1">Dari angkut sampai selesai timbang</div>
                        </div>

                        <!-- Durasi POS - Timbangan -->
                        <div class="pb-4 border-b border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-gray-600 font-medium">Durasi POS - Timbangan</div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900" x-text="formatDuration(data.durasi_pos_timbangan)"></div>
                            <div class="text-xs text-gray-500 mt-1">Waktu tempuh ke timbangan</div>
                        </div>

                        <!-- Durasi Deload -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm text-gray-600 font-medium">Durasi Deload</div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="text-3xl font-bold text-gray-900" x-text="formatDuration(data.durasi_deload)"></div>
                            <div class="text-xs text-gray-500 mt-1">Waktu bongkar di timbangan</div>
                        </div>
                    </div>
                </div>

                <!-- Hasil Timbangan Card (2/8 width) -->
                <div class="md:col-span-2 bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                        Hasil Timbangan
                    </h3>
                    
                    <div x-show="data.bruto || data.netto">
                        <div class="space-y-3 text-sm">
                            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-3 text-white shadow-md">
                                <div class="text-xs opacity-90 mb-1">Bruto</div>
                                <div class="text-xl font-bold" x-text="formatTon(data.bruto) + ' ton'"></div>
                                <div class="text-xs opacity-75 mt-1" x-text="formatNumber(data.bruto) + ' kg'"></div>
                            </div>
                            <div x-show="data.bruto && data.netto" class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg p-3 text-white shadow-md">
                                <div class="text-xs opacity-90 mb-1">Berat Mobil</div>
                                <div class="text-xl font-bold" x-text="formatTon(data.bruto - data.netto) + ' ton'"></div>
                                <div class="text-xs opacity-75 mt-1" x-text="formatNumber(data.bruto - data.netto) + ' kg'"></div>
                            </div>
                            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-3 text-white shadow-md">
                                <div class="text-xs opacity-90 mb-1">Netto</div>
                                <div class="text-2xl font-bold" x-text="formatTon(data.netto) + ' ton'"></div>
                                <div class="text-xs opacity-75 mt-1" x-text="formatNumber(data.netto) + ' kg'"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div x-show="!data.bruto && !data.netto" class="text-center py-6 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path>
                        </svg>
                        <p class="text-xs font-medium">Belum Masuk Timbangan</p>
                        <p class="text-xs text-gray-400 mt-1">Menunggu proses timbang</p>
                    </div>
                </div>
            </div>

            <!-- Information Cards and BSM - 3 columns -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <!-- Card 1: Informasi Umum -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Informasi Umum
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">No Surat Jalan:</span>
                            <span class="font-bold text-gray-900" x-text="data.suratjalanno"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Mandor:</span>
                            <span class="font-semibold text-gray-900" x-text="data.nama_mandor || data.mandorid"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Plot:</span>
                            <span class="font-bold text-blue-600" x-text="data.plot"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Umur:</span>
                            <span class="font-semibold text-gray-900" x-text="data.umur ? data.umur + ' bulan' : '-'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Kategori:</span>
                            <span class="px-2 py-1 rounded text-xs font-semibold" :class="getKategoriColor(data.kategori)" x-text="data.kategori || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Varietas:</span>
                            <span class="font-semibold text-gray-900" x-text="data.varietas || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Kode Tebang:</span>
                            <span :class="data.kodetebang === 'Premium' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 rounded text-xs font-semibold" x-text="data.kodetebang || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Langsir:</span>
                            <span x-show="data.langsir === 1" class="text-green-600 font-bold">✓ Ya</span>
                            <span x-show="data.langsir === 0" class="text-gray-400">Tidak</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Tebu Sulit:</span>
                            <span x-show="data.tebusulit === 1" class="text-red-600 font-bold">✓ Ya</span>
                            <span x-show="data.tebusulit === 0" class="text-gray-400">Tidak</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Informasi Kendaraan -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Kendaraan & Pengangkut
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Jenis Kendaraan:</span>
                            <span :class="data.kendaraankontraktor === 0 ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'" class="px-2 py-1 rounded text-xs font-semibold" x-text="data.kendaraankontraktor === 0 ? 'WL (Sendiri)' : 'Umum (Kontraktor)'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">No Polisi:</span>
                            <span class="font-bold text-gray-900" x-text="data.nomorpolisi || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Nama Supir:</span>
                            <span class="font-semibold text-gray-900" x-text="data.namasupir || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Kontraktor:</span>
                            <span class="font-semibold text-gray-900 text-right" x-text="data.nama_kontraktor_lengkap || '-'"></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Sub Kontraktor:</span>
                            <span class="font-semibold text-gray-900 text-right" x-text="data.nama_subkontraktor_lengkap || '-'"></span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: BSM Card -->
                <div class="bg-white rounded-xl shadow-md p-5">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Cek BSM
                    </h3>
                    
                    <div x-show="bsmData" style="display: none;">
                        <!-- Grade (Paling Atas) -->
                        <div class="text-center mb-6 pb-6 border-b-2 border-gray-200">
                            <div class="text-xs text-gray-500 uppercase font-semibold mb-2">Grade Kualitas</div>
                            <div class="inline-block">
                                <div class="text-8xl font-black mb-2" 
                                     :class="{
                                         'text-green-600': bsmData?.grade === 'A',
                                         'text-yellow-500': bsmData?.grade === 'B',
                                         'text-red-500': bsmData?.grade === 'C',
                                         'text-gray-400': !bsmData?.grade
                                     }"
                                     x-text="bsmData?.grade || '-'"></div>
                                <div class="text-sm font-semibold"
                                     :class="{
                                         'text-green-600': bsmData?.grade === 'A',
                                         'text-yellow-600': bsmData?.grade === 'B',
                                         'text-red-600': bsmData?.grade === 'C',
                                         'text-gray-500': !bsmData?.grade
                                     }">
                                    <span x-show="bsmData?.grade === 'A'">Excellent Quality</span>
                                    <span x-show="bsmData?.grade === 'B'">Good Quality</span>
                                    <span x-show="bsmData?.grade === 'C'">Fair Quality</span>
                                    <span x-show="!bsmData?.grade">No Grade</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Average Score -->
                        <div class="text-center mb-6 pb-4 border-b border-gray-200">
                            <div class="text-xs text-gray-500 uppercase font-semibold mb-1">Rata-rata BSM</div>
                            <div class="text-4xl font-bold text-purple-600" x-text="bsmData?.averagescore || '-'"></div>
                        </div>
                        
                        <!-- Detail Nilai BSM -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Bersih (B):</span>
                                <span class="text-base font-semibold text-blue-600" x-text="bsmData?.nilaibersih || '-'"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Segar (S):</span>
                                <span class="text-base font-semibold text-green-600" x-text="bsmData?.nilaisegar || '-'"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <span class="text-sm text-gray-600">Manis (M):</span>
                                <span class="text-base font-semibold text-yellow-600" x-text="bsmData?.nilaimanis || '-'"></span>
                            </div>
                        </div>
                        
                        <!-- Keterangan -->
                        <div x-show="bsmData?.keterangan" class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-xs font-semibold text-gray-700 mb-1">Keterangan:</div>
                            <div class="text-xs text-gray-600" x-text="bsmData?.keterangan"></div>
                        </div>
                        
                        <!-- Timestamp -->
                        <div class="mt-3 text-xs text-gray-400 text-center" x-show="bsmData?.createdat">
                            <span x-text="formatDateTime(bsmData?.createdat)"></span>
                        </div>
                    </div>
                    
                    <div x-show="!bsmData" class="text-center py-6 text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-xs font-medium">Belum ada BSM</p>
                        <p class="text-xs text-gray-400 mt-1">Menunggu cek kualitas</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
    function suratJalanDetail() {
        return {
            loading: true,
            data: {},
            bsmData: null,

            async loadData() {
                this.loading = true;
                try {
                    const response = await fetch(`{{ url('report/surat-jalan-timbangan') }}/{{ $suratjalanno }}/detail`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.data = result.data;
                        this.bsmData = result.bsm;
                        console.log('BSM Data:', this.bsmData);
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

            formatNumber(num) {
                if (!num) return '0';
                return parseFloat(num).toLocaleString('id-ID');
            },
            
            formatTon(kg) {
                if (!kg) return '0.00';
                const ton = parseFloat(kg) / 1000;
                return ton.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatDate(date) {
                if (!date) return '-';
                return new Date(date).toLocaleDateString('id-ID', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric' 
                });
            },

            formatDateTime(datetime) {
                if (!datetime) return '-';
                const date = new Date(datetime);
                return date.toLocaleDateString('id-ID', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            formatDateTime2(date, time) {
                if (!date || !time) return '-';
                const d = new Date(date);
                const dateStr = d.toLocaleDateString('id-ID', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric'
                });
                const timeStr = time.substring(0, 5);
                return `${dateStr} ${timeStr}`;
            },

            formatDuration(minutes) {
                if (!minutes) return '-';
                const mins = parseFloat(minutes);
                if (mins < 60) {
                    return Math.round(mins) + ' menit';
                }
                const hours = Math.floor(mins / 60);
                const remainingMins = Math.round(mins % 60);
                return `${hours} jam ${remainingMins} menit`;
            },

            getKategoriColor(kategori) {
                const colors = {
                    'PC': 'bg-emerald-100 text-emerald-800',
                    'RC1': 'bg-blue-100 text-blue-800',
                    'RC2': 'bg-amber-100 text-amber-800',
                    'RC3': 'bg-rose-100 text-rose-800'
                };
                return colors[kategori] || 'bg-gray-100 text-gray-800';
            }
        }
    }
    </script>
</x-layout>