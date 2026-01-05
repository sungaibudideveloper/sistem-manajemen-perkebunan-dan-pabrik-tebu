<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
  
    <div class="p-4 max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-4">
        <div>
          <div class="text-sm text-gray-600">Company: <b>{{ session('companycode') }}</b></div>
          <div class="text-sm text-gray-600">Tgl: <b>{{ $startDate }}</b> s/d <b>{{ $endDate }}</b></div>
          @if($search) <div class="text-sm text-gray-600">Filter: <b>{{ $search }}</b></div> @endif
        </div>
        <div class="flex gap-2">
          <a href="{{ route('transaction.gudang.index', request()->only(['search','start_date','end_date'])) }}"
             class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm">← Back</a>
          <button onclick="window.print()" class="px-4 py-2 bg-white border rounded hover:bg-gray-50 text-sm">🖨️ Print</button>
        </div>
      </div>
  
      @foreach($report as $block)
        <div class="bg-white border rounded shadow-sm mb-6 overflow-x-auto">
          <div class="px-4 py-2 bg-gray-50 border-b">
            <div class="text-sm">
              <b>{{ $block->itemcode }}</b> — {{ $block->itemname }} ({{ $block->unit }})
            </div>
          </div>
  
          <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="py-2 px-3 border">No</th>
                <th class="py-2 px-3 border">Ket</th>
                <th class="py-2 px-3 border text-right">Masuk</th>
                <th class="py-2 px-3 border text-right">Keluar</th>
                <th class="py-2 px-3 border text-right">Saldo</th>
              </tr>
            </thead>
            <tbody>
              @foreach($block->rows as $r)
                <tr class="hover:bg-gray-50">
                  <td class="py-2 px-3 border">{{ $r->no ?? '' }}</td>
                  <td class="py-2 px-3 border">
                    {{ $r->ket ?? '' }}
                  </td>
                  <td class="py-2 px-3 border text-right">{{ is_null($r->masuk) ? '' : number_format($r->masuk, 3) }}</td>
                  <td class="py-2 px-3 border text-right">{{ is_null($r->keluar) ? '' : number_format($r->keluar, 3) }}</td>
                  <td class="py-2 px-3 border text-right font-semibold">{{ number_format($r->saldo, 3) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endforeach
  
      @if(empty($report))
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded text-sm text-yellow-800">
          Tidak ada transaksi pada periode ini.
        </div>
      @endif
    </div>
  </x-layout>
  