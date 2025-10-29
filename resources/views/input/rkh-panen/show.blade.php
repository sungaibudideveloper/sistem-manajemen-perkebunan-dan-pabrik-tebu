{{-- resources/views/input/rkh-panen/show.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="bg-white rounded-lg shadow-md p-6">
    
    <!-- Header Section -->
    <div class="flex justify-between items-start mb-6">
      <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Laporan RKH Panen</h2>
        <div class="flex items-center gap-4 text-sm text-gray-600">
          <span class="font-semibold">{{ $rkhPanen->rkhpanenno }}</span>
          <span>•</span>
          <span>{{ \Carbon\Carbon::parse($rkhPanen->rkhdate)->format('d F Y') }}</span>
          <span>•</span>
          <span class="px-3 py-1 rounded-full text-xs font-semibold
                {{ $rkhPanen->status == 'COMPLETED' ? 'bg-green-100 text-green-800' : 
                   ($rkhPanen->status == 'MOBILE_UPLOAD' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
            {{ $rkhPanen->status }}
          </span>
        </div>
      </div>
      
      <div class="flex gap-2">
        <a href="{{ route('input.rkh-panen.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          ← Kembali
        </a>
        
        @if($rkhPanen->status == 'MOBILE_UPLOAD')
        <a href="{{ route('input.rkh-panen.editHasil', $rkhPanen->rkhpanenno) }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          Input Hasil
        </a>
        @endif

        <button onclick="window.print()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
          🖨️ Print
        </button>
      </div>
    </div>

    <!-- Info Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-gray-50 rounded-lg p-4">
      <div>
        <p class="text-xs text-gray-500 mb-1">Mandor Panen</p>
        <p class="text-sm font-semibold text-gray-800">{{ $rkhPanen->mandor->name ?? '-' }}</p>
      </div>
      <div>
        <p class="text-xs text-gray-500 mb-1">Total Kontraktor</p>
        <p class="text-sm font-semibold text-gray-800">{{ $rencana->count() }} Kontraktor</p>
      </div>
      <div>
        <p class="text-xs text-gray-500 mb-1">Keterangan</p>
        <p class="text-sm text-gray-800">{{ $rkhPanen->keterangan ?? '-' }}</p>
      </div>
    </div>

    <!-- Section 1: RENCANA PANEN -->
    <div class="mb-8">
      <div class="bg-blue-600 text-white px-4 py-2 rounded-t-lg">
        <h3 class="font-bold text-sm uppercase">1. Rencana Panen</h3>
      </div>
      
      <div class="overflow-x-auto border border-gray-200 rounded-b-lg">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Kontraktor</th>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Jenis Panen</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">Rencana Netto (Ton)</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">Rencana (Ha)</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Est. YPH</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">T. Tebang</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">T. Muat</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">WL</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Umum</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Mesin</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Grab</th>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Lokasi Plot</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($rencana as $item)
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-2 text-gray-900 font-medium">{{ $item->kontraktorid }}</td>
              <td class="px-3 py-2">
                <span class="px-2 py-1 rounded text-xs font-medium
                      {{ $item->jenispanen == 'MANUAL' ? 'bg-gray-100 text-gray-800' : 
                         ($item->jenispanen == 'SEMI_MEKANIS' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                  {{ str_replace('_', ' ', $item->jenispanen) }}
                </span>
              </td>
              <td class="px-3 py-2 text-right font-semibold text-green-700">{{ number_format($item->rencananetto ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item->rencanaha ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-center text-gray-700">{{ number_format($item->estimasiyph ?? 0, 0) }}</td>
              <td class="px-3 py-2 text-center text-gray-700">{{ $item->tenagatebangjumlah ?? 0 }}</td>
              <td class="px-3 py-2 text-center text-gray-700">{{ $item->tenagamuatjumlah ?? 0 }}</td>
              <td class="px-3 py-2 text-center text-gray-700">{{ $item->armadawl ?? 0 }}</td>
              <td class="px-3 py-2 text-center text-gray-700">{{ $item->armadaumum ?? 0 }}</td>
              <td class="px-3 py-2 text-center">
                <span class="text-{{ $item->mesinpanen ? 'green' : 'gray' }}-600">
                  {{ $item->mesinpanen ? '✓' : '-' }}
                </span>
              </td>
              <td class="px-3 py-2 text-center">
                <span class="text-{{ $item->grabloader ? 'green' : 'gray' }}-600">
                  {{ $item->grabloader ? '✓' : '-' }}
                </span>
              </td>
              <td class="px-3 py-2 text-gray-700">{{ $item->lokasiplot ?? '-' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="12" class="px-3 py-4 text-center text-gray-500">Tidak ada data rencana</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="bg-gray-50 font-bold">
            <tr>
              <td colspan="2" class="px-3 py-2 text-right">TOTAL:</td>
              <td class="px-3 py-2 text-right text-green-700">{{ number_format($totals['rencana_netto'], 2) }}</td>
              <td class="px-3 py-2 text-right text-gray-800">{{ number_format($totals['rencana_ha'], 2) }}</td>
              <td colspan="8"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Section 2: HASIL KEMARIN -->
    <div class="mb-8">
      <div class="bg-green-600 text-white px-4 py-2 rounded-t-lg">
        <h3 class="font-bold text-sm uppercase">2. Hasil Panen Kemarin</h3>
      </div>
      
      <div class="overflow-x-auto border border-gray-200 rounded-b-lg">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Blok</th>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Plot</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">Luas (Ha)</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Status</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Hari Tebang</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">STC (Ton)</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">HC (Ton)</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">BC (Ton)</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">FB (Rit)</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">FB (Ton)</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Premium</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($hasil as $item)
            <tr class="hover:bg-gray-50">
              <td class="px-3 py-2 text-gray-900 font-medium">{{ $item->blok }}</td>
              <td class="px-3 py-2 text-gray-900">{{ $item->plot }}</td>
              <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item->luasplot ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-center">
                <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                  {{ $item->kodestatus }}
                </span>
              </td>
              <td class="px-3 py-2 text-center text-gray-700">{{ $item->haritebang }}</td>
              <td class="px-3 py-2 text-right font-semibold text-orange-700">{{ number_format($item->stc ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-right font-semibold text-green-700">{{ number_format($item->hc ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item->bc ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-right text-gray-700">{{ $item->fbrit ?? 0 }}</td>
              <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item->fbton ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-center">
                <span class="text-{{ $item->ispremium ? 'yellow' : 'gray' }}-600">
                  {{ $item->ispremium ? '⭐' : '-' }}
                </span>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="11" class="px-3 py-4 text-center text-gray-500">Belum ada hasil panen</td>
            </tr>
            @endforelse
          </tbody>
          <tfoot class="bg-gray-50 font-bold">
            <tr>
              <td colspan="5" class="px-3 py-2 text-right">TOTAL:</td>
              <td class="px-3 py-2 text-right text-orange-700">{{ number_format($totals['hasil_stc'], 2) }}</td>
              <td class="px-3 py-2 text-right text-green-700">{{ number_format($totals['hasil_hc'], 2) }}</td>
              <td class="px-3 py-2 text-right text-gray-800">{{ number_format($totals['hasil_bc'], 2) }}</td>
              <td class="px-3 py-2 text-right text-gray-800">{{ number_format($totals['field_balance_rit'], 0) }}</td>
              <td class="px-3 py-2 text-right text-gray-800">{{ number_format($totals['field_balance_ton'], 2) }}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Section 3: PETAK BARU (Hari Tebang = 1) -->
    <div class="mb-8">
      <div class="bg-purple-600 text-white px-4 py-2 rounded-t-lg">
        <h3 class="font-bold text-sm uppercase">3. Petak Baru (Hari Tebang Pertama)</h3>
      </div>
      
      <div class="overflow-x-auto border border-gray-200 rounded-b-lg">
        <table class="min-w-full divide-y divide-gray-200 text-xs">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Blok</th>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Plot</th>
              <th class="px-3 py-2 text-right font-semibold text-gray-700">Luas (Ha)</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Status</th>
              <th class="px-3 py-2 text-center font-semibold text-gray-700">Hari Tebang</th>
              <th class="px-3 py-2 text-left font-semibold text-gray-700">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($petakBaru as $item)
            <tr class="hover:bg-gray-50 bg-purple-50">
              <td class="px-3 py-2 text-gray-900 font-medium">{{ $item->blok }}</td>
              <td class="px-3 py-2 text-gray-900 font-medium">{{ $item->plot }}</td>
              <td class="px-3 py-2 text-right text-gray-700">{{ number_format($item->luasplot ?? 0, 2) }}</td>
              <td class="px-3 py-2 text-center">
                <span class="px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                  {{ $item->kodestatus }}
                </span>
              </td>
              <td class="px-3 py-2 text-center">
                <span class="px-2 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-800">
                  Hari ke-{{ $item->haritebang }}
                </span>
              </td>
              <td class="px-3 py-2 text-gray-700">{{ $item->keterangan ?? 'Petak baru mulai dipanen hari ini' }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="px-3 py-4 text-center text-gray-500">Tidak ada petak baru hari ini</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
      <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4">
        <p class="text-xs text-blue-600 font-semibold mb-1">RENCANA NETTO</p>
        <p class="text-2xl font-bold text-blue-900">{{ number_format($totals['rencana_netto'], 2) }}</p>
        <p class="text-xs text-blue-600">Ton</p>
      </div>
      
      <div class="bg-green-50 border-l-4 border-green-600 rounded-lg p-4">
        <p class="text-xs text-green-600 font-semibold mb-1">HASIL HC</p>
        <p class="text-2xl font-bold text-green-900">{{ number_format($totals['hasil_hc'], 2) }}</p>
        <p class="text-xs text-green-600">Ton</p>
      </div>
      
      <div class="bg-orange-50 border-l-4 border-orange-600 rounded-lg p-4">
        <p class="text-xs text-orange-600 font-semibold mb-1">HASIL STC</p>
        <p class="text-2xl font-bold text-orange-900">{{ number_format($totals['hasil_stc'], 2) }}</p>
        <p class="text-xs text-orange-600">Ton</p>
      </div>
      
      <div class="bg-purple-50 border-l-4 border-purple-600 rounded-lg p-4">
        <p class="text-xs text-purple-600 font-semibold mb-1">FIELD BALANCE</p>
        <p class="text-2xl font-bold text-purple-900">{{ number_format($totals['field_balance_ton'], 2) }}</p>
        <p class="text-xs text-purple-600">Ton ({{ number_format($totals['field_balance_rit'], 0) }} Rit)</p>
      </div>
    </div>

    <!-- Footer Info -->
    <div class="mt-6 pt-4 border-t border-gray-200 text-xs text-gray-500">
      <div class="flex justify-between">
        <div>
          <p>Dibuat oleh: <span class="font-semibold">{{ $rkhPanen->inputby }}</span></p>
          <p>Tanggal: {{ \Carbon\Carbon::parse($rkhPanen->createdat)->format('d/m/Y H:i') }}</p>
        </div>
        @if($rkhPanen->updateby)
        <div class="text-right">
          <p>Diupdate oleh: <span class="font-semibold">{{ $rkhPanen->updateby }}</span></p>
          <p>Tanggal: {{ \Carbon\Carbon::parse($rkhPanen->updatedat)->format('d/m/Y H:i') }}</p>
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