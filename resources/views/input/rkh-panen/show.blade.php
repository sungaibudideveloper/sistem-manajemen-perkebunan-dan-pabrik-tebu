{{-- resources/views/input/rkh-panen/show.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="bg-white rounded-lg shadow-md p-6" id="report-container">
    
    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6 print:hidden">
      <div>
        <h2 class="text-2xl font-bold text-gray-800">Laporan RKH Panen</h2>
        <p class="text-sm text-gray-600 mt-1">{{ $rkhPanen->rkhpanenno }} - {{ \Carbon\Carbon::parse($rkhPanen->rkhdate)->format('d F Y') }}</p>
      </div>
      
      <div class="flex gap-2">
        <button onclick="window.print()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
          </svg>
          Print
        </button>
        
        @if($rkhPanen->status != 'COMPLETED')
        <a href="{{ route('input.rkh-panen.editHasil', $rkhPanen->rkhpanenno) }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Input Hasil
        </a>
        @endif
        
        <a href="{{ route('input.rkh-panen.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Kembali
        </a>
      </div>
    </div>

    <!-- Print Header (Only visible when printing) -->
    <div class="hidden print:block mb-6 pb-4 border-b-2 border-gray-300">
      <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900">LAPORAN RENCANA KERJA HARIAN PANEN</h1>
        <p class="text-lg font-semibold text-gray-700 mt-2">{{ session('companyname', 'PT. Company Name') }}</p>
      </div>
      <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
        <div>
          <p><span class="font-semibold">No RKH:</span> {{ $rkhPanen->rkhpanenno }}</p>
          <p><span class="font-semibold">Tanggal:</span> {{ \Carbon\Carbon::parse($rkhPanen->rkhdate)->format('d F Y') }}</p>
        </div>
        <div>
          <p><span class="font-semibold">Mandor Panen:</span> {{ $rkhPanen->mandor->name ?? '-' }}</p>
          <p><span class="font-semibold">Target:</span> {{ number_format($rkhPanen->targettoday ?? 0, 2) }} ton / {{ number_format($rkhPanen->targetha ?? 0, 2) }} ha</p>
        </div>
      </div>
    </div>

    <!-- RKH Info Card (Screen only) -->
    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 mb-6 print:hidden">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <p class="text-xs text-gray-600 mb-1">No RKH</p>
          <p class="text-sm font-bold text-gray-900">{{ $rkhPanen->rkhpanenno }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-600 mb-1">Mandor Panen</p>
          <p class="text-sm font-semibold text-gray-900">{{ $rkhPanen->mandor->name ?? '-' }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-600 mb-1">Target Hari Ini</p>
          <p class="text-sm font-semibold text-gray-900">{{ number_format($rkhPanen->targettoday ?? 0, 2) }} ton / {{ number_format($rkhPanen->targetha ?? 0, 2) }} ha</p>
        </div>
        <div>
          <p class="text-xs text-gray-600 mb-1">Status</p>
          <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                {{ $rkhPanen->status == 'COMPLETED' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
            {{ $rkhPanen->status }}
          </span>
        </div>
      </div>
    </div>

    <!-- SECTION 1: Rencana Panen -->
    <div class="mb-8">
      <div class="bg-gradient-to-r from-green-700 to-green-600 text-white px-4 py-3 rounded-t-lg">
        <h3 class="text-lg font-bold">Section 1: Laporan Rencana Panen</h3>
      </div>
      
      <div class="border border-gray-300 rounded-b-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Kontraktor</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Jenis Panen</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Rencana Netto (ton)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Rencana (ha)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">YPH</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Tenaga (Tebang/Muat)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Armada (WL/Umum)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Alat</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Lokasi Plot</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($rencana as $index => $r)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $r->kontraktor->nama ?? $r->kontraktorid }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $r->jenispanen == 'MANUAL' ? 'bg-blue-100 text-blue-800' : 
                           ($r->jenispanen == 'SEMI_MEKANIS' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                    {{ str_replace('_', '-', $r->jenispanen) }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($r->rencananetto ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($r->rencanaha ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($r->estimasiyph ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">
                  @if($r->jenispanen == 'MEKANIS')
                    <span class="text-gray-500">-</span>
                  @else
                    {{ $r->tenagatebangjumlah ?? 0 }} / {{ $r->jenispanen == 'MANUAL' ? ($r->tenagamuatjumlah ?? 0) : '-' }}
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $r->armadawl ?? 0 }} / {{ $r->armadaumum ?? 0 }}</td>
                <td class="px-4 py-3 text-sm">
                  @if($r->mesinpanen)
                    <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded mr-1">Mesin Panen</span>
                  @endif
                  @if($r->grabloader)
                    <span class="inline-block px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Grab Loader</span>
                  @endif
                  @if(!$r->mesinpanen && !$r->grabloader)
                    <span class="text-gray-500">Manual</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $r->lokasiplot ?? '-' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="px-4 py-6 text-center text-gray-500">Tidak ada data rencana panen</td>
              </tr>
              @endforelse
              
              @if($rencana->isNotEmpty())
              <tr class="bg-gray-100 font-semibold">
                <td colspan="3" class="px-4 py-3 text-sm text-gray-900">TOTAL</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($totals['rencana_netto'] ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($totals['rencana_ha'] ?? 0, 2) }}</td>
                <td colspan="5" class="px-4 py-3"></td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SECTION 2: Hasil Kemarin -->
    <div class="mb-8">
      <div class="bg-gradient-to-r from-orange-700 to-orange-600 text-white px-4 py-3 rounded-t-lg">
        <h3 class="text-lg font-bold">Section 2: Hasil Panen Kemarin</h3>
      </div>
      
      <div class="border border-gray-300 rounded-b-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Blok</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Plot</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Luas (ha)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">KTG</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Hari Tebang</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">STC (ha)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">HC (ha)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">BC (ha)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">FB (RIT/TON)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Ket</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($hasil as $h)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $h->blok }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $h->plot }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($h->luasplot, 2) }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                    {{ $h->kodestatus }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-center text-gray-900">{{ $h->haritebang }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($h->stc ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 font-semibold">{{ number_format($h->hc ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($h->bc ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $h->fbrit ?? 0 }} / {{ number_format($h->fbton ?? 0, 2) }}</td>
                <td class="px-4 py-3 text-sm">
                  @if($h->ispremium)
                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Premium</span>
                  @else
                    <span class="text-gray-500">Non-Premium</span>
                  @endif
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="11" class="px-4 py-6 text-center text-gray-500">Belum ada data hasil panen</td>
              </tr>
              @endforelse
              
              @if($hasil->isNotEmpty())
              <tr class="bg-gray-100 font-semibold">
                <td colspan="7" class="px-4 py-3 text-sm text-gray-900">TOTAL</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($totals['hasil_hc'] ?? 0, 2) }}</td>
                <td colspan="3" class="px-4 py-3"></td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SECTION 3: Petak Baru -->
    <div class="mb-8">
      <div class="bg-gradient-to-r from-purple-700 to-purple-600 text-white px-4 py-3 rounded-t-lg">
        <h3 class="text-lg font-bold">Section 3: Petak Baru (Hari Tebang ke-1)</h3>
      </div>
      
      <div class="border border-gray-300 rounded-b-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Blok - Plot</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Luas (ha)</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">KTG</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($petakBaru as $p)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $p->blok }} - {{ $p->plot }}</td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($p->luasplot, 2) }}</td>
                <td class="px-4 py-3 text-sm">
                  <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                    {{ $p->kodestatus }}
                  </span>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-500">Tidak ada petak baru hari ini</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Keterangan (if any) -->
    @if($rkhPanen->keterangan)
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
      <p class="text-sm font-semibold text-gray-700 mb-2">Keterangan:</p>
      <p class="text-sm text-gray-600">{{ $rkhPanen->keterangan }}</p>
    </div>
    @endif

    <!-- Footer Info -->
    <div class="mt-8 pt-4 border-t border-gray-300 text-sm text-gray-600">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p>Dibuat oleh: <span class="font-semibold">{{ $rkhPanen->inputby ?? '-' }}</span></p>
          <p>Tanggal dibuat: <span class="font-semibold">{{ $rkhPanen->createdat ? \Carbon\Carbon::parse($rkhPanen->createdat)->format('d/m/Y H:i') : '-' }}</span></p>
        </div>
        @if($rkhPanen->updateby)
        <div class="text-right">
          <p>Diupdate oleh: <span class="font-semibold">{{ $rkhPanen->updateby }}</span></p>
          <p>Tanggal update: <span class="font-semibold">{{ $rkhPanen->updatedat ? \Carbon\Carbon::parse($rkhPanen->updatedat)->format('d/m/Y H:i') : '-' }}</span></p>
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
      #report-container, #report-container * {
        visibility: visible;
      }
      #report-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
      .print\:hidden {
        display: none !important;
      }
      .print\:block {
        display: block !important;
      }
      table {
        page-break-inside: auto;
      }
      tr {
        page-break-inside: avoid;
        page-break-after: auto;
      }
    }
  </style>

</x-layout>