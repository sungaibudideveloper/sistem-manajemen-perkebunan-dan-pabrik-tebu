{{--resources\views\input\rencanakerjaharian\lkh-report.blade.php--}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="max-w-full mx-auto bg-white rounded-lg shadow-lg p-8">
        <!-- Header Section -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-xl font-bold text-gray-800 mb-2">
                        LAPORAN KEGIATAN HARIAN (LKH)
                        @if($lkhData->jenistenagakerja == 1)
                            - TENAGA HARIAN
                        @else
                            - TENAGA BORONGAN
                        @endif
                    </h1>
                    <div class="space-y-1">
                        <p class="text-sm text-gray-600">No. LKH: <span class="font-mono font-semibold">{{ $lkhData->lkhno }}</span></p>
                        <p class="text-sm text-gray-600">No. RKH: <span class="font-mono font-semibold">{{ $lkhData->rkhno }}</span></p>
                        <p class="text-sm text-gray-600">Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('l, d F Y') }}</span></p>
                        <p class="text-sm text-gray-600">Aktivitas: <span class="font-semibold">{{ $lkhData->activitycode }} - {{ $lkhData->activityname ?? '' }}</span></p>
                        <p class="text-sm text-gray-600">Mandor: <span class="font-semibold">{{ $lkhData->mandornama ?? $lkhData->mandorid }}</span></p>
                    </div>
                </div>
                <div class="text-right">
                    @if($lkhData->status == 'COMPLETED')
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Selesai</span>
                    @elseif($lkhData->status == 'DRAFT')
                        <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">Draft</span>
                    @elseif($lkhData->status == 'APPROVED')
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Approved</span>
                    @else
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">{{ $lkhData->status }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan</h3>
            <div class="grid grid-cols-4 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg text-center border border-gray-200">
                    <div class="text-2xl font-bold text-gray-800">{{ $lkhData->totalworkers }}</div>
                    <div class="text-sm text-gray-600">Total Pekerja</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-center border border-gray-200">
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($lkhData->totalhasil, 2) }}</div>
                    <div class="text-sm text-gray-600">Total Hasil (Ha)</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-center border border-gray-200">
                    <div class="text-2xl font-bold text-gray-800">{{ number_format($lkhData->totalsisa, 2) }}</div>
                    <div class="text-sm text-gray-600">Total Sisa (Ha)</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg text-center border border-gray-200">
                    <div class="text-2xl font-bold text-gray-800">Rp {{ number_format($lkhData->totalupahall, 0, ',', '.') }}</div>
                    <div class="text-sm text-gray-600">Total Upah</div>
                </div>
            </div>
        </div>

        <!-- Plot Details Section -->
        @if($lkhPlotDetails->count() > 0)
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Plot</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-12">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Blok</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-20">Plot</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas RKH</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas Hasil</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Luas Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhPlotDetails as $index => $plot)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $plot->blok }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm">{{ $plot->plot }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($plot->luasrkh, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($plot->luashasil, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($plot->luassisa, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td colspan="4" class="border border-gray-300 px-3 py-2 text-center text-sm">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhPlotDetails->sum('luashasil'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhPlotDetails->sum('luassisa'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <!-- Worker Details Section -->
        @if($lkhWorkerDetails->count() > 0)
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Pekerja</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-8">No</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-40">Nama Pekerja</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-32">NIK</th>
                            @if($lkhData->jenistenagakerja == 1)
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Masuk</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Selesai</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Kerja</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Overtime</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Premi</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Harian</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Lembur</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Total Upah</th>
                            @else
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Borongan</th>
                                <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Total Upah</th>
                            @endif
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhWorkerDetails as $index => $worker)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-sm">{{ $worker->tenagakerja->nama ?? 'N/A' }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-sm font-mono">{{ $worker->tenagakerja->nik ?? '-' }}</td>
                            @if($lkhData->jenistenagakerja == 1)
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">{{ $worker->jammasuk ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">{{ $worker->jamselesai ?? '-' }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm">{{ $worker->totaljamkerja ?? 0 }}h</td>
                                <td class="border border-gray-300 px-2 py-2 text-center text-sm">{{ $worker->overtimehours ?? 0 }}h</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">Rp {{ number_format($worker->premi ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">Rp {{ number_format($worker->upahharian ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">Rp {{ number_format($worker->upahlembur ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-semibold">Rp {{ number_format($worker->totalupah ?? 0, 0, ',', '.') }}</td>
                            @else
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">Rp {{ number_format($worker->upahborongan ?? 0, 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm font-semibold">Rp {{ number_format($worker->totalupah ?? 0, 0, ',', '.') }}</td>
                            @endif
                            <td class="border border-gray-300 px-2 py-2 text-sm">{{ $worker->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            @if($lkhData->jenistenagakerja == 1)
                                <td colspan="9" class="border border-gray-300 px-2 py-2 text-center text-sm">TOTAL</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">Rp {{ number_format($lkhWorkerDetails->sum('upahlembur'), 0, ',', '.') }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">Rp {{ number_format($lkhWorkerDetails->sum('totalupah'), 0, ',', '.') }}</td>
                            @else
                                <td colspan="4" class="border border-gray-300 px-2 py-2 text-center text-sm">TOTAL</td>
                                <td class="border border-gray-300 px-2 py-2 text-right text-sm">Rp {{ number_format($lkhWorkerDetails->sum('totalupah'), 0, ',', '.') }}</td>
                            @endif
                            <td class="border border-gray-300 px-2 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <!-- Material Details Section -->
        @if($lkhMaterialDetails->count() > 0)
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">Detail Material</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-12">No</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-32">Item Code</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Diterima</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Sisa</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 w-24">Qty Digunakan</th>
                            <th class="border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhMaterialDetails as $index => $material)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-3 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $material->itemcode }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($material->qtyditerima, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($material->qtysisa, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm font-semibold">{{ number_format($material->qtydigunakan, 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-sm">{{ $material->keterangan ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td colspan="2" class="border border-gray-300 px-3 py-2 text-center text-sm">TOTAL</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhMaterialDetails->sum('qtyditerima'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhMaterialDetails->sum('qtysisa'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2 text-right text-sm">{{ number_format($lkhMaterialDetails->sum('qtydigunakan'), 2) }}</td>
                            <td class="border border-gray-300 px-3 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <!-- Keterangan Section -->
        @if($lkhData->keterangan)
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-300">
                    <p class="text-sm text-gray-700">{{ $lkhData->keterangan }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Signature Section -->
        <div class="bg-white rounded-lg p-6 mb-6 border border-gray-200">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Persetujuan</h3>
            <div class="grid grid-cols-3 gap-8">
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
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-center space-x-4 no-print">
            @if($lkhData->status != 'APPROVED' && !$lkhData->issubmit)
            <button 
                onclick="window.location.href='{{ route('input.rencanakerjaharian.editLKH', $lkhData->lkhno) }}'"
                class="bg-gray-700 hover:bg-gray-800 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit LKH
            </button>
            @endif
            
            <button 
                onclick="window.print()"
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
            
            .action-buttons {
                display: none;
            }
        }
    </style>
</x-layout>