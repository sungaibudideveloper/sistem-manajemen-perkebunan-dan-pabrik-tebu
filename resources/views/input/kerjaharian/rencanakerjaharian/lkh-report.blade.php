<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="max-w-full mx-auto bg-white rounded-lg shadow-lg p-8">
        <!-- Header Section -->
        <div class="border-b-2 border-gray-300 pb-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">
                        LAPORAN KEGIATAN HARIAN (LKH)
                        @if($lkhData->jenistenagakerja == 1)
                            - TENAGA HARIAN
                        @else
                            - TENAGA BORONGAN
                        @endif
                    </h1>
                    <p class="text-sm text-gray-600">No. LKH: <span class="font-mono font-semibold">{{ $lkhData->lkhno }}</span></p>
                </div>
                <div class="text-right">
                    @if($lkhData->status == 'COMPLETED')
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">Selesai</span>
                    @elseif($lkhData->status == 'DRAFT')
                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">Draft</span>
                    @else
                        <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm font-medium">{{ $lkhData->status }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Header -->
        <div class="grid grid-cols-2 gap-6 mb-8 bg-gray-50 p-6 rounded-lg">
            <div class="space-y-3">
                <div class="flex">
                    <span class="font-semibold text-gray-700 w-32">Hari/Tanggal:</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('l, d F Y') }}</span>
                </div>
                <div class="flex">
                    <span class="font-semibold text-gray-700 w-32">Company:</span>
                    <span class="text-gray-900">{{ $lkhData->companycode }}</span>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex">
                    <span class="font-semibold text-gray-700 w-32">Aktivitas:</span>
                    <span class="text-gray-900">{{ $lkhData->activitycode }} - {{ $lkhData->activityname ?? '' }}</span>
                </div>
                <div class="flex">
                    <span class="font-semibold text-gray-700 w-32">Mandor:</span>
                    <span class="text-gray-900">{{ $lkhData->mandornama ?? $lkhData->mandorid }}</span>
                </div>
            </div>
        </div>

        <!-- Summary Info -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $lkhData->totalworkers }}</div>
                <div class="text-sm text-blue-800">Total Pekerja</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-green-600">{{ number_format($lkhData->totalhasil, 2) }}</div>
                <div class="text-sm text-green-800">Total Hasil (Ha)</div>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-orange-600">{{ number_format($lkhData->totalsisa, 2) }}</div>
                <div class="text-sm text-orange-800">Total Sisa (Ha)</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg text-center">
                <div class="text-2xl font-bold text-purple-600">Rp {{ number_format($lkhData->totalupahall, 0, ',', '.') }}</div>
                <div class="text-sm text-purple-800">Total Upah</div>
            </div>
        </div>

        <!-- Work Hours (Hanya untuk Tenaga Harian) -->
        @if($lkhData->jenistenagakerja == 1)
        <div class="bg-yellow-50 p-4 rounded-lg mb-6">
            <h3 class="font-semibold text-gray-800 mb-2">Jam Kerja Shift</h3>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="font-medium">Jam Mulai:</span> 
                    <span class="font-mono">{{ $lkhData->jammulaikerja ?? '-' }}</span>
                </div>
                <div>
                    <span class="font-medium">Jam Selesai:</span> 
                    <span class="font-mono">{{ $lkhData->jamselesaikerja ?? '-' }}</span>
                </div>
                <div>
                    <span class="font-medium">Total Overtime:</span> 
                    <span class="font-mono">{{ $lkhData->totalovertimehours ?? 0 }} jam</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Detail Table -->
        <div class="overflow-x-auto mb-8">
            <table class="min-w-full border-collapse border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">No</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Nama</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">No KTP</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Blok</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Plot</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 text-center" colspan="3">Hasil</th>
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Material</th>
                        
                        @if($lkhData->jenistenagakerja == 1)
                            {{-- Tenaga Harian --}}
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 text-center" colspan="3">Jam Kerja</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Premi</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Upah Harian</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Total Upah</th>
                        @else
                            {{-- Tenaga Borongan --}}
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Cost/Ha</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Total Biaya</th>
                        @endif
                        
                        <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Keterangan</th>
                    </tr>
                    
                    {{-- Sub Header untuk kolom yang di-colspan --}}
                    <tr class="bg-gray-50">
                        <th class="border border-gray-300 px-1 py-1" colspan="5"></th>
                        <th class="border border-gray-300 px-2 py-1 text-xs">Luas Plot</th>
                        <th class="border border-gray-300 px-2 py-1 text-xs">Hasil</th>
                        <th class="border border-gray-300 px-2 py-1 text-xs">Sisa</th>
                        <th class="border border-gray-300 px-1 py-1"></th>
                        
                        @if($lkhData->jenistenagakerja == 1)
                            <th class="border border-gray-300 px-2 py-1 text-xs">Masuk</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs">Selesai</th>
                            <th class="border border-gray-300 px-2 py-1 text-xs">Overtime</th>
                            <th class="border border-gray-300 px-1 py-1" colspan="3"></th>
                        @else
                            <th class="border border-gray-300 px-1 py-1" colspan="2"></th>
                        @endif
                        
                        <th class="border border-gray-300 px-1 py-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lkhDetails as $index => $worker)
                    <tr class="hover:bg-gray-50">
                        <td class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $index + 1 }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-sm">{{ $worker->workername }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-sm font-mono">{{ $worker->noktp ?? '-' }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $worker->blok }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $worker->plot }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($worker->luasplot, 2) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($worker->hasil, 2) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($worker->sisa, 2) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-sm">{{ $worker->materialused ?? '-' }}</td>
                        
                        @if($lkhData->jenistenagakerja == 1)
                            {{-- Tenaga Harian --}}
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm font-mono">{{ $worker->jammasuk ?? '-' }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm font-mono">{{ $worker->jamselesai ?? '-' }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $worker->overtimehours ?? 0 }}h</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">Rp {{ number_format($worker->premi ?? 0, 0, ',', '.') }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">Rp {{ number_format($worker->upahharian ?? 0, 0, ',', '.') }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold">Rp {{ number_format($worker->totalupahharian ?? 0, 0, ',', '.') }}</td>
                        @else
                            {{-- Tenaga Borongan --}}
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">Rp {{ number_format($worker->costperha ?? 0, 0, ',', '.') }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold">Rp {{ number_format($worker->totalbiayaborongan ?? 0, 0, ',', '.') }}</td>
                        @endif
                        
                        <td class="border border-gray-300 px-3 py-2 text-sm">-</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $lkhData->jenistenagakerja == 1 ? 15 : 12 }}" class="border border-gray-300 px-3 py-8 text-center text-gray-500">
                            Tidak ada data pekerja
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                
                {{-- Footer Total --}}
                <tfoot class="bg-gray-100 font-semibold">
                    <tr>
                        <td colspan="6" class="border border-gray-300 px-3 py-2 text-center text-sm">TOTAL</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhData->totalhasil, 2) }}</td>
                        <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhData->totalsisa, 2) }}</td>
                        <td class="border border-gray-300 px-3 py-2"></td>
                        
                        @if($lkhData->jenistenagakerja == 1)
                            <td colspan="3" class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $lkhData->totalovertimehours }}h</td>
                            <td colspan="2" class="border border-gray-300 px-3 py-2"></td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">Rp {{ number_format($lkhData->totalupahall, 0, ',', '.') }}</td>
                        @else
                            <td class="border border-gray-300 px-3 py-2"></td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">Rp {{ number_format($lkhData->totalupahall, 0, ',', '.') }}</td>
                        @endif
                        
                        <td class="border border-gray-300 px-3 py-2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Keterangan -->
        @if($lkhData->keterangan)
        <div class="mb-8">
            <h3 class="font-semibold text-gray-800 mb-2">Keterangan:</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-700">{{ $lkhData->keterangan }}</p>
            </div>
        </div>
        @endif

        <!-- Signature Section -->
        <div class="mt-12 grid grid-cols-4 gap-8">
            <div class="text-center">
                <div class="border-b border-gray-300 h-16 mb-2"></div>
                <p class="text-sm font-medium">Jabatan 1</p>
                <p class="text-xs text-gray-600">{{ $approvals->jabatan1name ?? 'Tidak diatur' }}</p>
                @if($lkhData->approval1flag == '1')
                    <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval1date ? \Carbon\Carbon::parse($lkhData->approval1date)->format('d/m/Y') : '' }}</p>
                @endif
            </div>
            <div class="text-center">
                <div class="border-b border-gray-300 h-16 mb-2"></div>
                <p class="text-sm font-medium">Jabatan 2</p>
                <p class="text-xs text-gray-600">{{ $approvals->jabatan2name ?? 'Tidak diatur' }}</p>
                @if($lkhData->approval2flag == '1')
                    <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval2date ? \Carbon\Carbon::parse($lkhData->approval2date)->format('d/m/Y') : '' }}</p>
                @endif
            </div>
            <div class="text-center">
                <div class="border-b border-gray-300 h-16 mb-2"></div>
                <p class="text-sm font-medium">Jabatan 3</p>
                <p class="text-xs text-gray-600">{{ $approvals->jabatan3name ?? 'Tidak diatur' }}</p>
                @if($lkhData->approval3flag == '1')
                    <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval3date ? \Carbon\Carbon::parse($lkhData->approval3date)->format('d/m/Y') : '' }}</p>
                @endif
            </div>
            <div class="text-center">
                <div class="border-b border-gray-300 h-16 mb-2"></div>
                <p class="text-sm font-medium">Jabatan 4</p>
                <p class="text-xs text-gray-600">{{ $approvals->jabatan4name ?? 'Tidak diatur' }}</p>
                @if($lkhData->approval4flag == '1')
                    <p class="text-xs text-green-600 mt-1">✓ Disetujui: {{ $lkhData->approval4date ? \Carbon\Carbon::parse($lkhData->approval4date)->format('d/m/Y') : '' }}</p>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-center space-x-4 no-print">
            @if($lkhData->status != 'APPROVED')
            <button 
                onclick="window.location.href='{{ route('input.kerjaharian.rencanakerjaharian.editLKH', $lkhData->lkhno) }}'"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
            >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit LKH
            </button>
            @endif
            
            <button 
                onclick="window.print()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-medium transition-colors"
            >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2 2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print
            </button>
            
            <button 
                onclick="window.history.back()"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition-colors"
            >
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            
            .action-buttons {
                display: none;
            }
        }
    </style>
</x-layout>