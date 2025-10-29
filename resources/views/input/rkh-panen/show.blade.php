{{-- resources/views/input/rkh-panen/show.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="bg-white rounded-lg shadow-md p-6">
    
    <!-- Header Section -->
    <div class="flex justify-between items-start mb-6 pb-4 border-b-2 border-gray-200">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Laporan RKH Panen</h2>
        <div class="flex items-center gap-4 text-sm">
          <span class="font-semibold text-gray-900">{{ $rkhPanen->rkhpanenno }}</span>
          <span class="text-gray-400">•</span>
          <span class="text-gray-600">{{ \Carbon\Carbon::parse($rkhPanen->rkhdate)->format('d F Y') }}</span>
          <span class="text-gray-400">•</span>
          <span class="px-3 py-1 rounded-md text-xs font-semibold
                {{ $rkhPanen->status == 'COMPLETED' ? 'bg-green-600 text-white' : 
                   ($rkhPanen->status == 'MOBILE_UPLOAD' ? 'bg-blue-600 text-white' : 'bg-gray-600 text-white') }}">
            {{ $rkhPanen->status }}
          </span>
        </div>
      </div>
      
      <div class="flex gap-2">
        <a href="{{ route('input.rkh-panen.index') }}" 
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-medium transition-colors border border-gray-300">
          ← Kembali
        </a>
        
        @if($rkhPanen->status == 'MOBILE_UPLOAD')
        <a href="{{ route('input.rkh-panen.editHasil', $rkhPanen->rkhpanenno) }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
          Input Hasil
        </a>
        @endif

        <button onclick="window.print()" 
                class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
          🖨️ Print
        </button>
      </div>
    </div>

    <!-- Info Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 p-4 bg-gray-50 rounded-md border border-gray-200">
      <div>
        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Mandor Panen</p>
        <p class="text-sm font-semibold text-gray-900">{{ $rkhPanen->mandor->name ?? '-' }}</p>
      </div>
      <div>
        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Kontraktor</p>
        <p class="text-sm font-semibold text-gray-900">{{ $rencana->count() }} Kontraktor</p>
      </div>
      <div>
        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Keterangan</p>
        <p class="text-sm text-gray-700">{{ $rkhPanen->keterangan ?? '-' }}</p>
      </div>
    </div>

    <!-- Section 1: RENCANA PANEN -->
    <div class="mb-8">
      <div class="bg-gray-800 text-white px-4 py-3 rounded-t-md">
        <h3 class="font-bold text-sm uppercase tracking-wide">Rencana Panen</h3>
      </div>
      
      <div class="overflow-x-auto border-x border-b border-gray-300 rounded-b-md">
        <table class="min-w-full divide-y divide-gray-300 text-xs">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide" rowspan="2">Kontraktor</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide" rowspan="2">Jenis Panen</th>
              <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-l-2 border-gray-300" colspan="3">Rencana Panen</th>
              <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-l-2 border-gray-300" colspan="2">Tenaga</th>
              <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-l-2 border-gray-300" colspan="2">Armada</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-l-2 border-gray-300" rowspan="2">Lokasi Plot</th>
            </tr>
            <tr>
              <th class="px-4 py-2 text-right font-medium text-gray-600 text-xs border-l-2 border-gray-300">Netto (Ton)</th>
              <th class="px-4 py-2 text-right font-medium text-gray-600 text-xs">Luas (Ha)</th>
              <th class="px-4 py-2 text-center font-medium text-gray-600 text-xs">YPH</th>
              <th class="px-4 py-2 text-center font-medium text-gray-600 text-xs border-l-2 border-gray-300">Tebang</th>
              <th class="px-4 py-2 text-center font-medium text-gray-600 text-xs">Muat</th>
              <th class="px-4 py-2 text-center font-medium text-gray-600 text-xs border-l-2 border-gray-300">WL</th>
              <th class="px-4 py-2 text-center font-medium text-gray-600 text-xs">Umum</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($rencana as $item)
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->kontraktorid }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 rounded text-xs font-medium border
                      {{ $item->jenispanen == 'MANUAL' ? 'bg-gray-50 text-gray-700 border-gray-300' : 
                         ($item->jenispanen == 'SEMI_MEKANIS' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-green-50 text-green-700 border-green-200') }}">
                  {{ str_replace('_', ' ', $item->jenispanen) }}
                </span>
              </td>
              <td class="px-4 py-3 text-right font-semibold text-gray-900 border-l-2 border-gray-200">{{ number_format($item->rencananetto ?? 0, 2) }}</td>
              <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->rencanaha ?? 0, 2) }}</td>
              <td class="px-4 py-3 text-center text-gray-700">{{ number_format($item->estimasiyph ?? 0, 0) }}</td>
              <td class="px-4 py-3 text-center text-gray-700 border-l-2 border-gray-200">{{ $item->tenagatebangjumlah ?? 0 }}</td>
              <td class="px-4 py-3 text-center text-gray-700">{{ $item->tenagamuatjumlah ?? 0 }}</td>
              <td class="px-4 py-3 text-center text-gray-700 border-l-2 border-gray-200">{{ $item->armadawl ?? 0 }}</td>
              <td class="px-4 py-3 text-center text-gray-700">{{ $item->armadaumum ?? 0 }}</td>
              <td class="px-4 py-3 text-gray-700 text-xs border-l-2 border-gray-200">{{ $item->lokasiplot ?? '-' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="10" class="px-4 py-6 text-center text-gray-500">Tidak ada data rencana</td>
            </tr>
            @endforelse
          </tbody>
          @if($rencana->count() > 0)
          <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
            <tr>
              <td colspan="2" class="px-4 py-3 text-right text-gray-900 uppercase">Total:</td>
              <td class="px-4 py-3 text-right text-gray-900 border-l-2 border-gray-200">{{ number_format($totals['rencana_netto'], 2) }}</td>
              <td class="px-4 py-3 text-right text-gray-900">{{ number_format($totals['rencana_ha'], 2) }}</td>
              <td class="px-4 py-3 text-center text-gray-500">-</td>
              <td class="px-4 py-3 text-center text-gray-900 border-l-2 border-gray-200">{{ $rencana->sum('tenagatebangjumlah') }}</td>
              <td class="px-4 py-3 text-center text-gray-900">{{ $rencana->sum('tenagamuatjumlah') }}</td>
              <td class="px-4 py-3 text-center text-gray-900 border-l-2 border-gray-200">{{ $rencana->sum('armadawl') }}</td>
              <td class="px-4 py-3 text-center text-gray-900">{{ $rencana->sum('armadaumum') }}</td>
              <td class="border-l-2 border-gray-200"></td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>

    <!-- Section 2: HASIL PANEN (Always show all plots) -->
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
              <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">Luas (Ha)</th>
              <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Status</th>
              <th class="px-4 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide">Hari</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">STC (Ha)</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">HC (Ha)</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">BC (Ha)</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">FB Rit</th>
              <th class="px-4 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide">FB Ton</th>
              <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            @php
              // Get ALL plots from rkhpanenresult, regardless of HC value
              $allPlots = DB::table('rkhpanenresult')
                  ->where('companycode', Session::get('companycode'))
                  ->where('rkhpanenno', $rkhPanen->rkhpanenno)
                  ->orderBy('blok')
                  ->orderBy('plot')
                  ->get();
            @endphp

            @forelse($allPlots as $item)
            <tr class="hover:bg-gray-50 transition-colors {{ is_null($item->hc) || $item->hc == 0 ? 'bg-gray-50' : '' }}">
              <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->blok }}</td>
              <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->plot }}</td>
              <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->luasplot ?? 0, 2) }}</td>
              <td class="px-4 py-3 text-center">
                <span class="px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-300">
                  {{ $item->kodestatus }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                @if($item->haritebang == 1)
                  <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                    1
                  </span>
                @else
                  <span class="text-gray-700">{{ $item->haritebang }}</span>
                @endif
              </td>
              <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($item->stc ?? 0, 2) }}</td>
              
              @if(is_null($item->hc) || $item->hc == 0)
                <td class="px-4 py-3 text-center text-gray-400 italic text-xs" colspan="5">Menunggu input hasil</td>
              @else
                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ number_format($item->hc ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->bc ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-right text-gray-700">{{ $item->fbrit ?? 0 }}</td>
                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->fbton ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-gray-700 text-xs">{{ $item->keterangan ?? '-' }}</td>
              @endif
            </tr>
            @empty
            <tr>
              <td colspan="11" class="px-4 py-6 text-center text-gray-500">Tidak ada plot yang direncanakan</td>
            </tr>
            @endforelse
          </tbody>
          
          @if($allPlots->count() > 0)
          <tfoot class="bg-gray-100 font-bold border-t-2 border-gray-300">
            <tr>
              <td colspan="5" class="px-4 py-3 text-right text-gray-900 uppercase">Total:</td>
              <td class="px-4 py-3 text-right text-gray-900">{{ number_format($totals['hasil_stc'], 2) }}</td>
              <td class="px-4 py-3 text-right text-gray-900">{{ number_format($totals['hasil_hc'], 2) }}</td>
              <td class="px-4 py-3 text-right text-gray-900">{{ number_format($totals['hasil_bc'], 2) }}</td>
              <td class="px-4 py-3 text-right text-gray-900">{{ number_format($totals['field_balance_rit'], 0) }}</td>
              <td class="px-4 py-3 text-right text-gray-900">{{ number_format($totals['field_balance_ton'], 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>

    <!-- Section 3: PETAK BARU (Hari Tebang = 1) -->
    @php
      $petakBaru = DB::table('rkhpanenresult as r')
          ->leftJoin('rkhpanenlst as l', function($join) {
              $join->on('r.rkhpanenno', '=', 'l.rkhpanenno')
                   ->on('r.companycode', '=', 'l.companycode')
                   ->whereRaw("FIND_IN_SET(r.plot, l.lokasiplot) > 0");
          })
          ->leftJoin('kontraktor as k', 'l.kontraktorid', '=', 'k.id')
          ->where('r.companycode', Session::get('companycode'))
          ->where('r.rkhpanenno', $rkhPanen->rkhpanenno)
          ->where('r.haritebang', 1)
          ->select('r.blok', 'r.plot', 'r.luasplot', 'k.namakontraktor')
          ->orderBy('r.blok')
          ->orderBy('r.plot')
          ->get();
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
              <th class="px-4 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide">Kontraktor</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($petakBaru as $item)
            <tr class="hover:bg-yellow-50 transition-colors bg-yellow-50/30">
              <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->blok }}</td>
              <td class="px-4 py-3 text-gray-900 font-medium">{{ $item->plot }}</td>
              <td class="px-4 py-3 text-right text-gray-700">{{ number_format($item->luasplot ?? 0, 2) }}</td>
              <td class="px-4 py-3 text-gray-700">{{ $item->namakontraktor ?? '-' }}</td>
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
              <td class="px-4 py-3 text-right text-gray-900">{{ number_format($petakBaru->sum('luasplot'), 2) }}</td>
              <td class="px-4 py-3 text-gray-700">{{ $petakBaru->count() }} Plot</td>
            </tr>
          </tfoot>
          @endif
        </table>
      </div>
    </div>

    <!-- Footer Info -->
    <div class="mt-8 pt-4 border-t border-gray-200 text-xs text-gray-500">
      <div class="flex justify-between">
        <div>
          <p>Dibuat oleh: <span class="font-semibold text-gray-700">{{ $rkhPanen->inputby }}</span></p>
          <p>Tanggal: <span class="text-gray-700">{{ \Carbon\Carbon::parse($rkhPanen->createdat)->format('d/m/Y H:i') }}</span></p>
        </div>
        @if($rkhPanen->updateby)
        <div class="text-right">
          <p>Diupdate oleh: <span class="font-semibold text-gray-700">{{ $rkhPanen->updateby }}</span></p>
          <p>Tanggal: <span class="text-gray-700">{{ \Carbon\Carbon::parse($rkhPanen->updatedat)->format('d/m/Y H:i') }}</span></p>
        </div>
        @endif
      </div>
    </div>

  </div>

  <!-- Print Styles -->
  <style>
    @media print {
      body * {
        visibility: hidden;
      }
      .bg-white, .bg-white * {
        visibility: visible;
      }
      .bg-white {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      button, .no-print {
        display: none !important;
      }
    }
  </style>

</x-layout>