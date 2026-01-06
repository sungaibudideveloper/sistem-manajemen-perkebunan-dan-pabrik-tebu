<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
  
    <style>
      @media print {
        .no-print { display: none !important; }
  
        /* satu item = section; mulai halaman baru */
        .item-section { 
          page-break-after: always; 
          break-after: page;
          /* ✅ paksa "enter" di setiap halaman */
          padding-top: 14mm;
        }
        .item-section:last-child { page-break-after: auto; break-after: auto; }
  
        tr { page-break-inside: avoid; break-inside: avoid; }
  
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
  
        .item-header { position: static !important; }
      }
  
      @media screen {
        .item-header {
          position: sticky;
          top: 0;
          z-index: 10;
        }
      }
    </style>
  
    <div class="p-4 max-w-6xl mx-auto">
  
      {{-- Top info --}}
      
      <div class="flex items-start justify-between mb-4 no-print">
        <div class="text-sm text-gray-700 leading-6">
          <div>Company: <b>{{ session('companycode') }}</b></div>
          @if($search) <div>Filter: <b>{{ $search }}</b></div> @endif
        </div>
  
        <div class="flex gap-2">
          <a href="{{ route('transaction.gudang.index', request()->only(['search','start_date','end_date'])) }}"
             class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">← Back</a>
          <button onclick="window.print()"
                  class="px-4 py-2 bg-white border border-gray-300 rounded hover:bg-gray-50 text-sm">🖨️ Print</button>
        </div>
      </div>
  
      @forelse($report as $block)
        @php
          $totalMasuk  = collect($block->rows)->sum(fn($r) => (float)($r->masuk ?? 0));
          $totalKeluar = collect($block->rows)->sum(fn($r) => (float)($r->keluar ?? 0));
          $saldo = $totalMasuk - $totalKeluar;
        @endphp
  
        {{-- ✅ Border atas dihilangkan: no outer border, no header border --}}
        <div class="item-section bg-white rounded shadow-sm mb-6 overflow-x-auto">
  
          {{-- Header item: tanpa border dan tanpa background berat --}}
          <div class="item-header px-4 py-3">
            <div class="flex items-start justify-between gap-4">
              <div class="text-sm text-gray-900 min-w-0">
                <b>{{ $block->itemcode }}</b> — {{ $block->itemname }} ({{ $block->unit }})
              </div>
          
              <div class="text-xs text-gray-600 text-right whitespace-nowrap">
                Periode: {{ $startDate }} s/d {{ $endDate }}
                @if($search)
                  <span class="text-gray-400"> • </span>
                  Filter: {{ $search }}
                @endif
              </div>
            </div>
          </div>
          
  
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-2 px-3 border border-gray-300">Tanggal</th>
                <th class="py-2 px-3 border border-gray-300">Ket</th>
                <th class="py-2 px-3 border border-gray-300 text-right">Masuk</th>
                <th class="py-2 px-3 border border-gray-300 text-right">Keluar</th>
              </tr>
            </thead>
  
            <tbody>
              @foreach($block->rows as $r)
                <tr class="hover:bg-gray-50">
                  <td class="py-2 px-3 border border-gray-300 text-center">
                    {{ !empty($r->tgl) ? date('d M Y', strtotime($r->tgl)) : '' }}
                  </td>
  
                  <td class="py-2 px-3 border border-gray-300">
                    {{ $r->ket ?? '' }}
                  </td>
  
                  <td class="py-2 px-3 border border-gray-300 text-right font-medium text-green-700">
                    {{ is_null($r->masuk) ? '' : number_format($r->masuk, 2) }}
                  </td>
  
                  <td class="py-2 px-3 border border-gray-300 text-right font-medium text-red-700">
                    {{ is_null($r->keluar) ? '' : number_format($r->keluar, 2) }}
                  </td>
                </tr>
              @endforeach
            </tbody>
  
            <tfoot>
              <tr class="bg-gray-100 font-semibold">
                <td class="py-2 px-3 border border-gray-300 text-right" colspan="2">TOTAL</td>
                <td class="py-2 px-3 border border-gray-300 text-right text-green-800">
                  {{ number_format($totalMasuk, 2) }}
                </td>
                <td class="py-2 px-3 border border-gray-300 text-right text-red-800">
                  {{ number_format($totalKeluar, 2) }}
                </td>
              </tr>
  
              <tr class="bg-white font-semibold">
                <td class="py-2 px-3 border border-gray-300 text-right" colspan="3">SALDO</td>
                <td class="py-2 px-3 border border-gray-300 text-right text-gray-900">
                  {{ number_format($saldo, 2) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
  
      @empty
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-sm text-yellow-800">
          Tidak ada transaksi pada periode ini.
        </div>
      @endforelse
    </div>
  </x-layout>
  