{{--resources\views\input\rencanakerjaharian\lkh-report-panen.blade.php--}}
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
                                LAPORAN KEGIATAN HARIAN (LKH) - PANEN
                            </h1>
                            <div class="space-y-1">
                                <p class="text-sm text-gray-600">No. LKH: <span class="font-mono font-semibold">{{ $lkhData->lkhno }}</span></p>
                                <p class="text-sm text-gray-600">No. RKH: <span class="font-mono font-semibold">{{ $lkhData->rkhno }}</span></p>
                                <p class="text-sm text-gray-600">Tanggal: <span class="font-semibold">{{ \Carbon\Carbon::parse($lkhData->lkhdate)->format('l, d F Y') }}</span></p>
                                <p class="text-sm text-gray-600">Aktivitas: <span class="font-semibold">{{ $lkhData->activitycode }} - {{ $lkhData->activityname ?? '' }}</span></p>
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
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total Plot:</span> 
                            <span class="font-bold text-gray-900">{{ $lkhPanenDetails->count() }} plot</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total HC (Hasil):</span> 
                            <span class="font-bold text-green-700">{{ number_format($lkhPanenDetails->sum('hc'), 2) }} Ha</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-700">Total STC (Sisa):</span> 
                            <span class="font-bold text-orange-700">{{ number_format($lkhPanenDetails->sum('stc'), 2) }} Ha</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 1: KONTRAKTOR & SUBKONTRAKTOR --}}
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Kontraktor & Subkontraktor</h3>
            </div>
            
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="min-w-full divide-y divide-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">No</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">ID Kontraktor</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Nama Kontraktor</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Total Subkontraktor</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Total Plot</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">List Plot</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($kontraktorSummary as $index => $kontraktor)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-700 font-medium">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-gray-900 font-mono">{{ $kontraktor->kontraktor_id }}</td>
                            <td class="px-4 py-3 text-gray-900 font-semibold">{{ $kontraktor->kontraktor_nama ?? 'Unknown' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                    {{ $kontraktor->total_subkontraktor }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                    {{ $kontraktor->total_plot }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-mono text-xs">{{ $kontraktor->list_plot }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-medium">Belum ada data kontraktor</p>
                                    <p class="text-xs text-gray-400">Data akan muncul setelah input dari mobile</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Section 2: HASIL PANEN --}}
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Hasil Panen</h3>
            </div>
            
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="min-w-full divide-y divide-gray-300 text-xs">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Blok</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Plot</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">Luas Batch (Ha)</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Hari</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-orange-50">STC (Ha)</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-green-50">HC (Ha)</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-gray-50">BC (Ha)</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-blue-50">FB Rit</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide bg-blue-50">FB Ton</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Subkontraktor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($lkhPanenDetails as $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->blok }}</td>
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->plot }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->batcharea, 2) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 rounded text-xs font-medium 
                                    {{ $item->kodestatus == 'PC' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($item->kodestatus == 'RC1' ? 'bg-green-100 text-green-800' : 
                                    ($item->kodestatus == 'RC2' ? 'bg-blue-100 text-blue-800' : 
                                    ($item->kodestatus == 'RC3' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'))) }}">
                                    {{ $item->kodestatus ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($item->haritebang === '-' || is_null($item->haritebang))
                                    <span class="text-gray-400">-</span>
                                @elseif($item->haritebang == 1)
                                    <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">1</span>
                                @else
                                    <span class="text-gray-700">{{ $item->haritebang }}</span>
                                @endif
                            </td>
                            
                            {{-- ✅ STC SELALU tampil (dari RKH planning) --}}
                            <td class="px-4 py-3 text-right font-semibold text-orange-700 bg-orange-50">
                                {{ number_format($item->stc, 2) }}
                            </td>
                            
                            {{-- ✅ HC, BC, FB hanya tampil jika sudah ada input dari Android --}}
                            @if($item->hc > 0)
                                <td class="px-4 py-3 text-right font-semibold text-green-700 bg-green-50">{{ number_format($item->hc, 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 bg-gray-50">{{ number_format($item->bc, 2) }}</td>
                                <td class="px-4 py-3 text-right text-blue-700 bg-blue-50">{{ $item->fieldbalancerit ? number_format($item->fieldbalancerit, 2) : '-' }}</td>
                                <td class="px-4 py-3 text-right text-blue-700 bg-blue-50">{{ $item->fieldbalanceton ? number_format($item->fieldbalanceton, 2) : '-' }}</td>
                            @else
                                <td class="px-4 py-3 text-center text-gray-400 italic text-xs" colspan="4">Menunggu input hasil</td>
                            @endif
                            
                            {{-- ✅ UPDATED: Kolom Subkontraktor dengan data real --}}
                            <td class="px-4 py-3">
                                @php
                                    $plotData = $subkontraktorDetail->where('plot', $item->plot);
                                @endphp
                                
                                @if($plotData->count() > 0)
                                    @php
                                        $kontraktor = $plotData->first();
                                    @endphp
                                    
                                    {{-- Header Kontraktor dengan format ID - Nama --}}
                                    <div class="mb-2 pb-2 border-b border-gray-200">
                                        <div class="text-xs font-bold text-gray-800">
                                            {{ $kontraktor->kontraktor_id }} - {{ $kontraktor->kontraktor_nama ?? 'Unknown' }}
                                        </div>
                                    </div>
                                    
                                    {{-- List Subkontraktor --}}
                                    <div class="space-y-1">
                                        @foreach($plotData as $sk)
                                        <div class="flex items-start gap-2">
                                            <span class="text-blue-600">•</span>
                                            <div class="flex-1 text-xs">
                                                <span class="font-semibold text-gray-900">
                                                    {{ $sk->subkontraktor_nama ?? $sk->subkontraktor_id }}
                                                </span>
                                                <span class="text-gray-500 ml-1">({{ $sk->jumlah_sj }} SJ)</span>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs">Belum ada SJ</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="px-4 py-6 text-center text-gray-500">Tidak ada plot yang direncanakan</td>
                        </tr>
                        @endforelse
                    </tbody>
                    
                    @if($lkhPanenDetails->count() > 0)
                    <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right text-gray-900 uppercase">Total:</td>
                            <td class="px-4 py-3 text-right text-orange-700 bg-orange-50">{{ number_format($lkhPanenDetails->sum('stc'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-green-700 bg-green-50">{{ number_format($lkhPanenDetails->sum('hc'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 bg-gray-50">{{ number_format($lkhPanenDetails->sum('bc'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-blue-700 bg-blue-50">{{ number_format($lkhPanenDetails->sum('fieldbalancerit'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-blue-700 bg-blue-50">{{ number_format($lkhPanenDetails->sum('fieldbalanceton'), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Section 3: PETAK BARU (Hari Tebang = 1) --}}
        @php
            $petakBaru = $lkhPanenDetails->filter(function($item) {
                return $item->haritebang == 1;
            });
        @endphp

        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Petak Baru Hari Ini</h3>
            </div>
            
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="min-w-full divide-y divide-gray-300 text-xs">
                    <thead class="bg-yellow-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Blok</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Plot</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">Luas (Ha)</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Subkontraktor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($petakBaru as $item)
                        <tr class="hover:bg-yellow-50 transition-colors bg-yellow-50/30">
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->blok }}</td>
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->plot }}</td>
                            <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->batcharea, 2) }}</td>
                            
                            {{-- ✅ UPDATED: Kolom Subkontraktor dengan data real untuk Petak Baru --}}
                            <td class="px-4 py-3">
                                @php
                                    $plotData = $subkontraktorDetail->where('plot', $item->plot);
                                @endphp
                                
                                @if($plotData->count() > 0)
                                    <div class="text-xs">
                                        @foreach($plotData as $sk)
                                            <div class="mb-1">
                                                <span class="font-semibold text-gray-900">{{ $sk->subkontraktor_nama ?? $sk->subkontraktor_id }}</span>
                                                <span class="text-gray-500">({{ $sk->jumlah_sj }} SJ)</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400 italic text-xs">Belum ada SJ</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Tidak ada petak baru hari ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($petakBaru->count() > 0)
                    <tfoot class="bg-yellow-50 font-semibold border-t-2 border-gray-300">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-right text-gray-900 uppercase">Total Petak Baru:</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ number_format($petakBaru->sum('batcharea'), 2) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $petakBaru->count() }} Plot</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- Section 4: TENAGA HARIAN --}}
        <div class="mb-8">
            <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
                <h3 class="font-bold text-sm uppercase tracking-wide">Tenaga Harian</h3>
            </div>
            
            @if($lkhWorkerDetails && $lkhWorkerDetails->count() > 0)
            <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
                <table class="w-full border-collapse bg-white text-xs">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-8">No</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-40">Nama Pekerja</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-32">NIK</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Masuk</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Selesai</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Jam Kerja</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Overtime</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Premi</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Harian</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Upah Lembur</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-24">Total Upah</th>
                            <th class="border border-gray-300 px-2 py-2 text-xs font-medium text-gray-700 w-20">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lkhWorkerDetails as $index => $worker)
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-2 py-2 text-center text-sm bg-gray-50">{{ $index + 1 }}</td>
                            <td class="border border-gray-300 px-2 py-2 text-sm font-medium">
                                {{ $worker->tenagakerja->nama ?? $worker->tenagakerjaid ?? 'N/A' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-sm font-mono">
                                {{ $worker->tenagakerja->nik ?? '-' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">
                                {{ $worker->jammasuk ? \Carbon\Carbon::parse($worker->jammasuk)->format('H:i') : '-' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-center text-sm font-mono">
                                {{ $worker->jamselesai ? \Carbon\Carbon::parse($worker->jamselesai)->format('H:i') : '-' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-center text-sm">
                                {{ ($worker->totaljamkerja ?? 0) > 0 ? number_format($worker->totaljamkerja, 0) . ' jam' : '-' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-center text-sm">
                                {{ ($worker->overtimehours ?? 0) > 0 ? number_format($worker->overtimehours, 0) . ' jam' : '-' }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                Rp {{ number_format($worker->premi ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                Rp {{ number_format($worker->upahharian ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                Rp {{ number_format($worker->upahlembur ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-right text-sm font-semibold bg-green-50">
                                Rp {{ number_format($worker->totalupah ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-sm text-gray-600">
                                {{ $worker->keterangan ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-100 font-semibold">
                        <tr>
                            <td colspan="9" class="border border-gray-300 px-2 py-2 text-center text-sm">TOTAL UPAH</td>
                            <td class="border border-gray-300 px-2 py-2 text-right text-sm">
                                Rp {{ number_format($lkhWorkerDetails->sum('upahlembur'), 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2 text-right text-sm bg-green-100">
                                Rp {{ number_format($lkhWorkerDetails->sum('totalupah'), 0, ',', '.') }}
                            </td>
                            <td class="border border-gray-300 px-2 py-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="border-x border-b border-gray-300 rounded-b-md bg-gray-50 py-6">
                <p class="text-center text-gray-500 text-sm">Tidak menggunakan tenaga harian</p>
            </div>
            @endif
        </div>

        <!-- Keterangan Section -->
        @if($lkhData->keterangan)
        <div class="bg-white rounded-lg p-4 mb-4 border border-gray-200">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Keterangan</label>
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
            @if($lkhData->status != 'APPROVED' && $lkhData->status != 'COMPLETED' && !$lkhData->issubmit)
            <button 
                onclick="alert('Edit LKH Panen akan segera tersedia')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit LKH
            </button>
            @endif
            
            <button 
                onclick="handlePrint()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
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