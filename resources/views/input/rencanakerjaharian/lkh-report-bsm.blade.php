{{--resources\views\input\rencanakerjaharian\lkh-report-bsm.blade.php--}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Print-optimized container -->
    <div class="print:p-0 print:m-0 max-w-full mx-auto bg-white rounded-lg shadow-lg p-4">
        
        <!-- Header and Summary Section -->
        <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
            <div class="flex gap-4">
                <!-- Header Section -->
                <div class="flex-1">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h1 class="text-xl font-bold text-gray-800 mb-2">
                                LAPORAN KEGIATAN HARIAN (LKH) - CEK BSM
                            </h1>
                            <div class="space-y-1">
                                <p class="text-sm text-gray-600">No. LKH: <span class="font-mono font-semibold">{{ $lkhData->lkhno }}</span></p>
                                <p class="text-sm text-gray-600">No. RKH: <span class="font-mono font-semibold">{{ $lkhData->rkhno }}</span></p>
                                <p class="text-sm text-gray-600">Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('l, d F Y') }}</span></p>
                                <p class="text-sm text-gray-600">Aktivitas: <span class="font-semibold">{{ $lkhData->activitycode }} - {{ $lkhData->activityname ?? 'Cek BSM' }}</span></p>
                                <p class="text-sm text-gray-600">Mandor: <span class="font-semibold">{{ $lkhData->mandornama ?? $lkhData->mandorid }}</span></p>
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            @if($lkhData->status == 'COMPLETED')
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Selesai</span>
                            @elseif($lkhData->status == 'DRAFT')
                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">Draft</span>
                            @elseif($lkhData->status == 'APPROVED')
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Approved</span>
                            @elseif($lkhData->status == 'SUBMITTED')
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">Submitted</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Summary Section -->
                <div class="w-64 bg-white rounded-lg p-4 border border-gray-300">
                    <h3 class="text-sm font-bold text-gray-800 mb-3">Ringkasan</h3>
                    @php
                        $totalPlots = $lkhBsmDetails->count();
                        $completedPlots = $lkhBsmDetails->where('status', 'COMPLETED')->count();
                        $pendingPlots = $totalPlots - $completedPlots;
                        $percentage = $totalPlots > 0 ? round(($completedPlots / $totalPlots) * 100, 1) : 0;
                    @endphp
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total Plot:</span> 
                            <span class="font-bold text-gray-900">{{ $totalPlots }} plot</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Completed:</span> 
                            <span class="font-bold text-green-700">{{ $completedPlots }} plot</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Pending:</span> 
                            <span class="font-bold text-orange-700">{{ $pendingPlots }} plot</span>
                        </div>
                        <div class="border-t pt-2 mt-2">
                            <div class="flex justify-between items-center">
                                <span class="font-medium text-gray-700">Progress:</span> 
                                <span class="font-bold text-lg {{ $percentage == 100 ? 'text-green-700' : 'text-orange-700' }}">
                                    {{ $percentage }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Card (Only show if there's completed data) --}}
        @php
            $completedData = $lkhBsmDetails->where('status', 'COMPLETED');
            $gradeA = $completedData->where('grade', 'A')->count();
            $gradeB = $completedData->where('grade', 'B')->count();
            $gradeC = $completedData->where('grade', 'C')->count();
            $avgScore = $completedData->whereNotNull('averagescore')->avg(function($item) {
                return (float)str_replace(',', '', $item->averagescore);
            });
        @endphp

        @if($completedData->count() > 0)
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Statistik BSM</h3>
            </div>
            <div class="border-x border-b border-gray-300 rounded-b-md bg-gray-50 p-4">
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-white rounded-lg p-4 text-center border border-gray-200">
                        <div class="text-3xl font-bold text-gray-800">{{ number_format($avgScore, 2) }}</div>
                        <div class="text-xs text-gray-600 mt-1 uppercase tracking-wide">Rata-rata Score</div>
                    </div>
                    <div class="bg-green-100 rounded-lg p-4 text-center border border-green-300">
                        <div class="text-3xl font-bold text-green-800">{{ $gradeA }}</div>
                        <div class="text-xs text-green-700 mt-1 uppercase tracking-wide">Grade A (≥80)</div>
                    </div>
                    <div class="bg-yellow-100 rounded-lg p-4 text-center border border-yellow-300">
                        <div class="text-3xl font-bold text-yellow-800">{{ $gradeB }}</div>
                        <div class="text-xs text-yellow-700 mt-1 uppercase tracking-wide">Grade B (60-79)</div>
                    </div>
                    <div class="bg-red-100 rounded-lg p-4 text-center border border-red-300">
                        <div class="text-3xl font-bold text-red-800">{{ $gradeC }}</div>
                        <div class="text-xs text-red-700 mt-1 uppercase tracking-wide">Grade C (<60)</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Section: Detail BSM Per Plot --}}
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Detail Hasil Cek BSM Per Plot</h3>
            </div>
            
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="min-w-full divide-y divide-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th rowspan="2" class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-r">No</th>
                            <th rowspan="2" class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-r">Plot</th>
                            <th rowspan="2" class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-r">Batch</th>
                            <th rowspan="2" class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-r">Status Input</th>
                            <th colspan="3" class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide bg-green-50 border-r">Nilai BSM</th>
                            <th rowspan="2" class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide bg-blue-50 border-r">Average</th>
                            <th rowspan="2" class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide bg-yellow-50 border-r">Grade</th>
                            <th rowspan="2" class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Keterangan</th>
                        </tr>
                        <tr class="bg-green-50">
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 border-r">Bersih</th>
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 border-r">Segar</th>
                            <th class="px-4 py-2 text-center font-semibold text-gray-700 border-r">Manis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($lkhBsmDetails as $index => $detail)
                        <tr class="hover:bg-gray-50 transition-colors {{ $detail->status == 'COMPLETED' ? 'bg-green-50/30' : '' }}">
                            <td class="px-4 py-3 text-gray-700 border-r">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 border-r">
                                <div class="font-semibold text-gray-900">{{ $detail->plot }}</div>
                                <div class="text-xs text-gray-500">{{ $detail->kodestatus }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-xs border-r">{{ $detail->batchno }}</td>
                            <td class="px-4 py-3 text-center border-r">
                                @if($detail->status == 'COMPLETED')
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                        ✓ COMPLETED
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            
                            {{-- Nilai BSM --}}
                            <td class="px-4 py-3 text-center border-r bg-green-50/30">
                                @if($detail->nilaibersih)
                                    <strong class="text-gray-900">{{ $detail->nilaibersih }}</strong>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center border-r bg-green-50/30">
                                @if($detail->nilaisegar)
                                    <strong class="text-gray-900">{{ $detail->nilaisegar }}</strong>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center border-r bg-green-50/30">
                                @if($detail->nilaimanis)
                                    <strong class="text-gray-900">{{ $detail->nilaimanis }}</strong>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            
                            {{-- Average Score --}}
                            <td class="px-4 py-3 text-center border-r bg-blue-50/50">
                                @if($detail->averagescore)
                                    <strong class="text-lg text-blue-700">{{ $detail->averagescore }}</strong>
                                @else
                                    <span class="text-gray-400 italic">-</span>
                                @endif
                            </td>
                            
                            {{-- Grade --}}
                            <td class="px-4 py-3 text-center border-r bg-yellow-50/50">
                                @if($detail->grade == 'A')
                                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">A</span>
                                @elseif($detail->grade == 'B')
                                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">B</span>
                                @elseif($detail->grade == 'C')
                                    <span class="px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">C</span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            
                            {{-- Keterangan --}}
                            <td class="px-4 py-3 text-gray-700">
                                <div class="text-xs">{{ $detail->keterangan }}</div>
                                @if($detail->updatedat != '-')
                                    <div class="text-xs text-gray-500 mt-1">Update: {{ $detail->updatedat }}</div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="px-4 py-6 text-center text-gray-500">
                                Tidak ada data BSM
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Keterangan Section -->
        @if($lkhData->keterangan)
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan LKH</label>
                <div class="bg-gray-50 p-3 rounded-lg border border-gray-300">
                    <p class="text-sm text-gray-700">{{ $lkhData->keterangan }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Signature Section -->
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-3">Persetujuan</h3>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="border-b border-gray-300 h-16 mb-2"></div>
                    <p class="text-sm font-medium">Jabatan 1</p>
                    <p class="text-xs text-gray-600">{{ $approvals->jabatan1name ?? 'Tidak diatur' }}</p>
                    @if($lkhData->approval1flag == '1')
                        <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval1date ? \Carbon\Carbon::parse($lkhData->approval1date)->format('d/m/Y H:i') : '' }}</p>
                    @elseif($lkhData->approval1flag == '0')
                        <p class="text-xs text-red-600 mt-1">✗ Ditolak: {{ $lkhData->approval1date ? \Carbon\Carbon::parse($lkhData->approval1date)->format('d/m/Y H:i') : '' }}</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                    @endif
                </div>
                <div class="text-center">
                    <div class="border-b border-gray-300 h-16 mb-2"></div>
                    <p class="text-sm font-medium">Jabatan 2</p>
                    <p class="text-xs text-gray-600">{{ $approvals->jabatan2name ?? 'Tidak diatur' }}</p>
                    @if($lkhData->approval2flag == '1')
                        <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval2date ? \Carbon\Carbon::parse($lkhData->approval2date)->format('d/m/Y H:i') : '' }}</p>
                    @elseif($lkhData->approval2flag == '0')
                        <p class="text-xs text-red-600 mt-1">✗ Ditolak: {{ $lkhData->approval2date ? \Carbon\Carbon::parse($lkhData->approval2date)->format('d/m/Y H:i') : '' }}</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                    @endif
                </div>
                <div class="text-center">
                    <div class="border-b border-gray-300 h-16 mb-2"></div>
                    <p class="text-sm font-medium">Jabatan 3</p>
                    <p class="text-xs text-gray-600">{{ $approvals->jabatan3name ?? 'Tidak diatur' }}</p>
                    @if($lkhData->approval3flag == '1')
                        <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval3date ? \Carbon\Carbon::parse($lkhData->approval3date)->format('d/m/Y H:i') : '' }}</p>
                    @elseif($lkhData->approval3flag == '0')
                        <p class="text-xs text-red-600 mt-1">✗ Ditolak: {{ $lkhData->approval3date ? \Carbon\Carbon::parse($lkhData->approval3date)->format('d/m/Y H:i') : '' }}</p>
                    @else
                        <p class="text-xs text-gray-500 mt-1">Menunggu persetujuan</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex justify-center space-x-4 no-print">
            <button 
                onclick="handlePrint()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
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

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                font-size: 12px;
            }
            
            table {
                font-size: 10px;
            }
        }
    </style>

    <script>
        function handlePrint() {
            const elementsToHide = [
                '.sidebar', '.navbar', '.header', '.nav', '.breadcrumb', 
                '.footer', 'nav', '#sidebar', '#header', '#navbar'
            ];
            
            elementsToHide.forEach(selector => {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    el.style.display = 'none';
                });
            });
            
            window.print();
            
            setTimeout(() => {
                elementsToHide.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(el => {
                        el.style.display = '';
                    });
                });
            }, 1000);
        }
    </script>
</x-layout>