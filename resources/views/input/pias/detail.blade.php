<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  @once
  <style>
    .toast-center{
      position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);
      background:#fff9c4; border:1px solid #f6e27c; /* success: soft yellow */
      border-radius:12px; padding:32px 40px;
      box-shadow:0 12px 32px rgba(0,0,0,.28);
      z-index:9999; text-align:center; color:#3b3b3b;
      font:600 16px/1.35 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
    }
    .toast-error{ background:#ffe4e6; border-color:#fda4af; } /* soft red */
    @media print { .toast-center{ display:none !important; }   .print-only { display:block;}    }
    @media screen { .print-only { display:none; } }
  </style>
@endonce

@if (session('success'))
  <div class="toast-center" role="alert" aria-live="assertive" onclick="this.remove()">
    {{ session('success') }}
  </div>
  <script>setTimeout(()=>document.querySelector('.toast-center')?.remove(),3000)</script>
@endif

{{-- Opsional: versi error (pakai if ($errors->any())) --}}
@if ($errors->any())
  <div class="toast-center toast-error" role="alert" aria-live="assertive" onclick="this.remove()">
    Terjadi kesalahan:
    <ul style="text-align:left;margin:6px 0 0;padding-left:18px;">
      @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
  <script>setTimeout(()=>document.querySelector('.toast-error')?.remove(),6000)</script>
@endif





  <!-- Form -->
  <form action="{{ route('input.pias.submit', ['rkhno' => $data[0]->rkhno]) }}" method="POST">
    @csrf
    <input type="hidden" name="rkhno" value="{{ $data[0]->rkhno }}"> {{-- ADDED: agar validasi rkhno di submit lolos --}}

  <div class="w-full p-4">
    <!-- Container full width tanpa center alignment -->
    <div class="w-full mb-4">
      <div class="grid grid-cols-4 gap-3">
        <!-- Card Input -->
        <div class="col-span-1 border rounded-md p-3 bg-white shadow-sm no-print">
          <h3 class="text-base font-bold mb-3">Input TJ & TC</h3>
          <div class="space-y-3">
            <div>
              <label class="block mb-1 text-sm">Total TJ (stok opsional)</label>
              <input type="number" name="inputTJ" id="inputTJ" class="w-full border rounded-md p-1 bg-gray-50 text-sm" placeholder="Masukkan Total TJ" 
              value="{{ old('inputTJ', optional($hdr)->tj)*1 }}" onchange="render()">
            </div>
            <div>
              <label class="block mb-1 text-sm">Total TC (stok opsional)</label>
              <input type="number" name="inputTC" id="inputTC" class="w-full border rounded-md p-1 bg-gray-50 text-sm" placeholder="Masukkan Total TC" 
              value="{{ old('inputTC', optional($hdr)->tc)*1 }}" onchange="render()">
            </div>
          </div>
        </div>
  
        <!-- Card Summary - lebih lebar -->
        <div id="summaryCard" class="col-span-2 border rounded-md p-1 bg-white shadow-sm">
        <h3 class="text-base font-bold mb-1 text-center">Ringkasan Kebutuhan vs Stok</h3>
        <div class="space-y-2">
          <div class="border rounded p-1 bg-blue-50">
            <div class="text-xs text-gray-600 mb-1 text-center">Total Kebutuhan</div>
            <div class="flex items-center justify-center gap-1">
              <div class="text-base font-bold">TJ <span id="totalTJ">0</span></div>
              <div class="text-base font-bold">TC <span id="totalTC">0</span></div>
            </div>
          </div>
          <div class="border rounded p-1 bg-gray-50">
            <div class="text-xs text-gray-600 mb-1 text-center">Stok & Sisa</div>
            <div class="grid grid-cols-2 gap-1">
              <div class="text-center">
                <div class="text-xs">Stok TJ</div>
                <div class="text-sm font-semibold" id="stokTJ">0</div>
                <div class="text-xs">Sisa: <span id="sisaTJ">0</span></div>
              </div>
              <div class="text-center">
                <div class="text-xs">Stok TC</div>
                <div class="text-sm font-semibold" id="stokTC">0</div>
                <div class="text-xs">Sisa: <span id="sisaTC">0</span></div>
              </div>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-1">
            <div id="statusTJ" class="text-center text-xs font-medium rounded py-1"></div>
            <div id="statusTC" class="text-center text-xs font-medium rounded py-1"></div>
          </div>
        </div>
      </div>

  
        <!-- Card RKH Detail -->
        <div class="col-span-1 border rounded-md p-3 bg-white shadow-sm no-print">
          <h3 class="text-base font-bold mb-3">RKH Detail</h3>
          <div class="space-y-1 text-sm">
            <p><strong>RKH No:</strong> {{ $data[0]->rkhno }}</p>
            <p><strong>Mandor:</strong> {{ $data[0]->mandor_name }}</p>
            <p><strong>Total Luas:</strong> {{ $data[0]->totalluas }} Ha</p>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Tabel plot tetap sama -->
    <div class="border rounded-md bg-white shadow-sm">
      <div class="p-4 bg-gray-100">
        <h3 class="text-lg font-bold">Detail Plot</h3>
        <h6 class="text-sm print-only">RKH {{ $data[0]->rkhno }} — Mandor {{ $data[0]->mandor_name }} — Total Luas {{ $data[0]->totalluas }} Ha</h6>
      </div>

      <div class="overflow-x-auto">

        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="p-3 border-b">Blok</th>
              <th class="p-3 border-b">Plot</th>
              <th class="p-3 border-b">Umur</th>
              <th class="p-3 border-b">Kategory</th>
              <th class="p-3 border-b">Varietas</th>
              <th class="p-3 border-b">Luas (Ha)</th>
              <th class="p-3 border-b bg-blue-50 font-semibold">TJ</th>
              <th class="p-3 border-b bg-green-50 font-semibold">TC</th>
              <th class="p-3 border-b no-print">Rumus</th>
            </tr>
          </thead>
          <tbody id="plotTable">
            @foreach($data as $item)
              @php
                $hari = (int) floor(abs($item->rkhdate->diffInRealDays($item->tanggalulangtahun)));
                $bulan = ceil($hari / 30);
                // cari data existing utk baris ini
                $exist = $lst->where('blok', $item->blok)->where('plot', $item->plot)->first();
                // ganti nama kolom sesuai tabel piaslst kamu
                $existTJ = optional($exist)->tj_alloc ?? optional($exist)->tj ?? null;
                $existTC = optional($exist)->tc_alloc ?? optional($exist)->tc ?? null;
                
              @endphp
              <tr class="hover:bg-gray-50"
                  data-luas="{{ $item->luasrkh }}" data-umur="{{ $hari }}" 
                  data-tj="{{ $existTJ ?? '' }}" data-tc="{{ $existTC ?? '' }}">
                <td class="p-3 border-b">{{ $item->blok }}</td>
                <td class="p-3 border-b">{{ $item->plot }}</td>
                <td class="p-3 border-b">
                  <span class="inline-flex gap-1">
                    <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold">Bulan ke {{ $bulan }}</span>
                    <span class="text-gray-700 text-sm">({{ $hari }} hari sejak tanam)</span>
                  </span>
                </td> 
                <td class="p-3 border-b text-center">{{ $item->kodestatus }}</td>
                <td class="p-3 border-b">{{ $item->kodevarietas }}</td>
                <td class="p-3 border-b text-right">{{ $item->luasrkh }}</td>
                <td class="p-3 border-b tj-result bg-blue-50 font-semibold text-right">
                  {{ isset($existTJ) ? number_format($existTJ, 2, ',', '.') : '' }}
                </td>
                <td class="p-3 border-b tc-result bg-green-50 font-semibold text-right">
                  {{ isset($existTC) ? number_format($existTC, 2, ',', '.') : '' }}
                </td>
                <td class="p-3 border-b pias-formula text-left text-sm no-print"></td>
              </tr>
            @endforeach
          </tbody>
        </table>

        @if($hdr)
        <div class="flex justify-center mt-2 text-sm">
          Sudah generated ({{ \Carbon\Carbon::parse($hdr->generateddate)->format('d M Y H:i') }})
        </div>
        @endif

        <div class="flex justify-center mt-2 mb-4">
          <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition"
          >
            {{ $hdr ? 'Edit Data' : 'Generate' }}
          </button>
        </div>

        <div class="flex justify-center mt-2 mb-6"> {{-- ADDED: tombol Print --}}
          <button
            type="button"
            onclick="window.print()"
            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded shadow transition"
          >
            Print
          </button>
        </div>

      </div>
    </div>
  </div>
</form>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const inputTJ = document.getElementById('inputTJ');
    const inputTC = document.getElementById('inputTC');
    const plotTable = document.getElementById('plotTable');

    const totalTJEl = document.getElementById('totalTJ');
    const totalTCEl = document.getElementById('totalTC');
    const stokTJEl  = document.getElementById('stokTJ');
    const stokTCEl  = document.getElementById('stokTC');
    const sisaTJEl  = document.getElementById('sisaTJ');
    const sisaTCEl  = document.getElementById('sisaTC');
    const summary   = document.getElementById('summaryCard');
    const statusTJ  = document.getElementById('statusTJ');
    const statusTC  = document.getElementById('statusTC');

    const pcts = [
      {tj:0.7,tc:0.3},{tj:0.7,tc:0.3},{tj:0.69,tc:0.4},
      {tj:0.5,tc:0.5},{tj:0.4,tc:0.6},{tj:0.3,tc:0.7},
      {tj:0.3,tc:0.7},{tj:0.3,tc:0.7},{tj:0.3,tc:0.7},{tj:0.3,tc:0.7}
    ];

    const hasBoth = () => {
      const tj = parseFloat(inputTJ.value), tc = parseFloat(inputTC.value);
      return tj > 0 && Number.isFinite(tj) && tc > 0 && Number.isFinite(tc);
    };

    const fmt0 = (x) => {
      const v = Math.round(x || 0);
      return v.toLocaleString('id-ID', {maximumFractionDigits: 0});
    };

    // ====== PRECOMPUTE SEKALI: baris, needs, elemen DOM ======
    const rows = Array.from(plotTable.rows); // <tr> di tbody
    const meta = [];            // { tjEl, tcEl, fEl, total, bulan, needTJ, needTC }
    let needsTJ=[], needsTC=[];
    for (const r of rows) {
      const luas = parseFloat(r.dataset.luas) || 0;
      const umur = parseInt(r.dataset.umur) || 0;
      const bulan = Math.max(1, Math.ceil(umur/30));
      const p = pcts[Math.min(bulan,10)-1] || {tj:0.5,tc:0.5};
      const total = luas * 25;
      const needTJ = total * p.tj;
      const needTC = total * p.tc;

      const tjEl = r.querySelector('.tj-result');
      const tcEl = r.querySelector('.tc-result');
      const fEl  = r.querySelector('.pias-formula');

      // Bangun bagian statis RUMUS SEKALI
      if (fEl) {
        fEl.innerHTML =
          `Pembagian: ` +
          `<span class="inline-block rounded px-2 py-0.5 bg-blue-100 font-semibold">TJ ${Math.round(p.tj*100)}%</span> / ` +
          `<span class="inline-block rounded px-2 py-0.5 bg-green-100 font-semibold">TC ${Math.round(p.tc*100)}%</span> dari ${fmt0(total)} lembar. <br>`+
          `Kebutuhan: ` +
          `<span class="inline-block rounded px-2 py-0.5 bg-blue-100 font-semibold">TJ ${needTJ.toFixed(2)}</span>, ` +
          `<span class="inline-block rounded px-2 py-0.5 bg-green-100 font-semibold">TC ${needTC.toFixed(2)}</span>`
          ;
      }

      meta.push({
        tjEl, tcEl, fEl,
        total, bulan, needTJ, needTC
      });
      needsTJ.push(needTJ);
      needsTC.push(needTC);
    }

    // Sum kebutuhan (konstan, tidak perlu dihitung tiap render)
    const sumNeedTJConst = needsTJ.reduce((a,b)=>a+b,0);
    const sumNeedTCConst = needsTC.reduce((a,b)=>a+b,0);

    // ====== allocator (Hamilton) tetap ======
    function allocateIntHamilton(needs, stock) {
      const n = needs.length;
      if (n === 0) return [];
      const sumNeed = needs.reduce((a,b)=>a+b,0);
      if (stock <= 0 || sumNeed <= 0) return Array(n).fill(0);

      const target = Math.min(Math.floor(stock), Math.ceil(sumNeed));
      if (target <= 0) return Array(n).fill(0);

      const quotas = needs.map(v => v / sumNeed * target);
      const alloc  = quotas.map(q => Math.floor(q));
      let remain   = target - alloc.reduce((a,b)=>a+b,0);
      if (remain > 0) {
        const order = quotas.map((q,i)=>({i, frac: q - Math.floor(q)}))
                            .sort((a,b)=> b.frac === a.frac ? a.i - b.i : b.frac - a.frac);
        for (let k=0; k<order.length && remain>0; k++, remain--) {
          alloc[order[k].i] += 1;
        }
      }
      return alloc;
    }

    // Cache state input terakhir untuk skip render yang sama
    let lastTJ = null, lastTC = null;

    function render(){
      if (!hasBoth()) { resetUI(); return; }

      const stokTJ = parseFloat(inputTJ.value) || 0;
      const stokTC = parseFloat(inputTC.value) || 0;
      if (stokTJ === lastTJ && stokTC === lastTC) return; // tidak berubah → skip
      lastTJ = stokTJ; lastTC = stokTC;

      const allocTJ = allocateIntHamilton(needsTJ, stokTJ);
      const allocTC = allocateIntHamilton(needsTC, stokTC);

      let sumAllocTJ = 0, sumAllocTC = 0;

      // Hanya update angka (tanpa rebuild innerHTML)
      for (let i=0;i<meta.length;i++){
        const m = meta[i];
        const aTJ = allocTJ[i]|0, aTC = allocTC[i]|0;
        sumAllocTJ += aTJ; sumAllocTC += aTC;

        if (m.tjEl) m.tjEl.textContent = fmt0(aTJ);
        if (m.tcEl) m.tcEl.textContent = fmt0(aTC);
      }

      // Ringkasan (kebutuhan konstan)
      const needTJInt = Math.ceil(sumNeedTJConst);
      const needTCInt = Math.ceil(sumNeedTCConst);

      totalTJEl.textContent = needTJInt.toLocaleString('id-ID');
      totalTCEl.textContent = needTCInt.toLocaleString('id-ID');

      stokTJEl.textContent  = fmt0(stokTJ);
      stokTCEl.textContent  = fmt0(stokTC);
      sisaTJEl.textContent  = fmt0(stokTJ - sumAllocTJ);
      sisaTCEl.textContent  = fmt0(stokTC - sumAllocTC);

      const okTJ = sumAllocTJ >= needTJInt;
      const okTC = sumAllocTC >= needTCInt;
      statusTJ.textContent = okTJ ? 'TJ CUKUP' : 'TJ KURANG';
      statusTC.textContent = okTC ? 'TC CUKUP' : 'TC KURANG';
      statusTJ.className = `text-center text-sm font-medium rounded-md py-1 ${okTJ ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
      statusTC.className = `text-center text-sm font-medium rounded-md py-1 ${okTC ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;

      summary.classList.remove('hidden');
    }

    function resetUI(){
      // Kosongkan angka saja, jangan rebuild DOM
      for (const m of meta) {
        if (m.tjEl) m.tjEl.textContent = '';
        if (m.tcEl) m.tcEl.textContent = '';
      }
    }

    // Debounce ringan
    let timer; const IDLE=500;
    function schedule(){
      clearTimeout(timer);
      if (hasBoth()) timer=setTimeout(render, IDLE);
      else resetUI();
    }
    inputTJ.addEventListener('input', schedule);
    inputTC.addEventListener('input', schedule);
    inputTJ.addEventListener('change', schedule);
    inputTC.addEventListener('change', schedule);

    // Jangan render otomatis saat load; render setelah user ubah nilai
    // Jika mau auto-render, aktifkan baris di bawah (non-blocking):
    // setTimeout(()=>{ if (hasBoth()) render(); }, 0);
    requestAnimationFrame(() => { if (hasBoth()) render(); });
    // Cetak: render sekali sebelum print saja
    window.onbeforeprint = function(){ if (hasBoth()) render(); };
    if (window.matchMedia) {
      const mq = window.matchMedia('print');
      mq.addEventListener?.('change', e => { if (e.matches && hasBoth()) render(); });
    }
  });
</script>


</x-layout>
