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
    @media print { .toast-center{ display:none !important; }   .print-only { display:block;}    
      input[type="number"] { 
        border: none !important; 
        background: transparent !important;
        padding: 0 !important;
      }
    }
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
  <form action="{{ route('transaction.pias.submit', ['rkhno' => $data[0]->rkhno]) }}" method="POST">
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
            <div style="text-align:right"> 
              <label class="text-sm font-medium">Dosis / Ha</label>
              <select name="dosage" id="dosage" class="border rounded p-2">
                @for($i=10;$i<=25;$i++)
                  <option value="{{ $i }}" {{ old('dosage', $hdr->dosage ?? 25)==$i ? 'selected':'' }}>{{ $i }}</option>
                @endfor
              </select>
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
                $exist = $lst->where('blok', $item->blok)->where('plot', $item->plot)->first();
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
                    {{-- <span class="text-gray-700 text-sm">({{ $hari }} hari sejak tanam)</span> --}}
                  </span>
                </td> 
                <td class="p-3 border-b text-center">{{ $item->kodestatus }}</td>
                <td class="p-3 border-b">{{ $item->kodevarietas }}</td>
                <td class="p-3 border-b text-right">{{ $item->luasrkh }}</td>
                
                {{-- ✅ INPUT TJ dengan hidden blok & plot --}}
                <td class="p-3 border-b bg-blue-50 font-semibold text-right">
                  <input type="hidden" name="rows[{{ $loop->index }}][blok]" value="{{ $item->blok }}">
                  <input type="hidden" name="rows[{{ $loop->index }}][plot]" value="{{ $item->plot }}">
                  <input type="hidden" name="rows[{{ $loop->index }}][lkhno]" value="{{ $item->lkhno }}">
                  <input
                    type="number" step="1" min="0"
                    class="tj-result w-24 text-right border rounded px-2 py-1 bg-white"
                    value="{{ old("rows.$loop->index.tj", isset($existTJ) ? (int)$existTJ : '') }}"
                    name="rows[{{ $loop->index }}][tj]"
                  >
                </td>
                
                {{-- ✅ INPUT TC --}}
                <td class="p-3 border-b bg-green-50 font-semibold text-right">
                  <input
                    type="number" step="1" min="0"
                    class="tc-result w-24 text-right border rounded px-2 py-1 bg-white"
                    value="{{ old("rows.$loop->index.tc", isset($existTC) ? (int)$existTC : '') }}"
                    name="rows[{{ $loop->index }}][tc]"
                  >
                </td>
                
                {{-- ✅ FORMULA (tanpa hidden input lagi) --}}
                <td class="p-3 border-b pias-formula text-left text-sm no-print">
                  {{-- Konten formula akan di-generate oleh JavaScript --}}
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr class="bg-gray-100 font-bold">
              <td class="p-3 border-t" colspan="6" style="text-align:right">TOTAL</td>
              <td class="p-3 border-t bg-blue-50 text-right">
                <span id="sumTJCell">0</span>
              </td>
              <td class="p-3 border-t bg-green-50 text-right">
                <span id="sumTCCell">0</span>
              </td>
              <td class="p-3 border-t"></td>
            </tr>
          </tfoot>
        </table>

        @if($hdr)
        <div class="flex justify-center mt-2 text-sm">
          Sudah generated ({{ \Carbon\Carbon::parse($hdr->generateddate)->format('d M Y H:i') }})
        </div>
        @endif

        <div class="flex justify-center mt-2 mb-4">
          <a href="{{ route('transaction.pias.index') }}" 
          class="bg-white inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 font-semibold py-2 px-4 rounded shadow transition no-print">
           ← Kembali
          </a> &nbsp;
          <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition no-print"
          >
            {{ $hdr ? 'Edit Data' : 'Generate' }}
          </button>
        </div>
        @if($hdr)
        <div class="flex justify-center mt-2 mb-6"> {{-- ADDED: tombol Print --}}
          <button
            type="button"
            onclick="window.print()"
            class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-6 rounded shadow transition no-print"
          >
            Print
          </button>
        </div>
        @endif

      </div>
    </div>
  </div>
  <input type="hidden" name="totalNeedTJ" id="totalNeedTJ_hidden" value="0">
  <input type="hidden" name="totalNeedTC" id="totalNeedTC_hidden" value="0">
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
    const sumTJCell = document.getElementById('sumTJCell');
    const sumTCCell = document.getElementById('sumTCCell');

    const pcts = [
      {tj:0.7,tc:0.3},{tj:0.7,tc:0.3},{tj:0.6,tc:0.4},
      {tj:0.5,tc:0.5},{tj:0.4,tc:0.6},{tj:0.3,tc:0.7},
      {tj:0.3,tc:0.7},{tj:0.3,tc:0.7},{tj:0.3,tc:0.7},{tj:0.3,tc:0.7}
    ];

    const hasBoth = () => {
      const tj = parseFloat(inputTJ.value), tc = parseFloat(inputTC.value);
      return tj > 0 && Number.isFinite(tj) && tc > 0 && Number.isFinite(tc);
    };

    const NF0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
    const fmt0 = (x) => NF0.format(Math.round(x || 0));
    const dosageEl = document.getElementById('dosage');
    function getDosage(){
      return parseFloat(dosageEl?.value || '25');
    }

    // ====== PRECOMPUTE SEKALI: baris, needs, elemen DOM ======
    const rows = Array.from(plotTable.rows); // <tr> di tbody
    const meta = [];            // { tjEl, tcEl, fEl, total, bulan, needTJ, needTC }
    let needsTJ=[], needsTC=[];
    for (const r of rows) {
      const luas = parseFloat(r.dataset.luas) || 0;
      const umur = parseInt(r.dataset.umur) || 0;
      const bulan = Math.max(1, Math.ceil(umur/30));
      const p = pcts[Math.min(bulan,10)-1] || {tj:0.5,tc:0.5};
      const total = luas * getDosage();
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
          `<span class="inline-block rounded px-2 py-0.5 bg-green-100 font-semibold">TC ${needTC.toFixed(2)}</span>`;
      }

      meta.push({ tjEl, tcEl, fEl, total, bulan, needTJ, needTC });
      needsTJ.push(needTJ);
      needsTC.push(needTC);
    }

    let initTJ = 0, initTC = 0;
    for (const r of rows) {
      initTJ += parseFloat(r.dataset.tj) || 0;
      initTC += parseFloat(r.dataset.tc) || 0;
    }
    if (sumTJCell) sumTJCell.textContent = fmt0(initTJ);
    if (sumTCCell) sumTCCell.textContent = fmt0(initTC);

    const stokTJ0 = parseFloat(inputTJ.value) || 0;
    const stokTC0 = parseFloat(inputTC.value) || 0;
    if (sisaTJEl) sisaTJEl.textContent = fmt0(Math.floor(stokTJ0) - initTJ);
    if (sisaTCEl) sisaTCEl.textContent = fmt0(Math.floor(stokTC0) - initTC);

    // Sum kebutuhan INTEGER (supaya sinkron dengan piaslst/controller)
    const needTJIntArr = needsTJ.map(v => Math.round(v));
    const needTCIntArr = needsTC.map(v => Math.round(v));
    let sumNeedTJIntConst = needTJIntArr.reduce((a,b)=>a+b,0);
    let sumNeedTCIntConst = needTCIntArr.reduce((a,b)=>a+b,0);

    // ✅ INIT: Tampilkan total kebutuhan dan stok saat page load
    if (totalTJEl) totalTJEl.textContent = sumNeedTJIntConst.toLocaleString('id-ID');
    if (totalTCEl) totalTCEl.textContent = sumNeedTCIntConst.toLocaleString('id-ID');
    if (stokTJEl) stokTJEl.textContent = fmt0(stokTJ0);
    if (stokTCEl) stokTCEl.textContent = fmt0(stokTC0);

    document.getElementById('totalNeedTJ_hidden').value = sumNeedTJIntConst;
    document.getElementById('totalNeedTC_hidden').value = sumNeedTCIntConst;

    // ================= CRC32 (match PHP) =================
    const CRC_TABLE = (() => {
      const t = new Uint32Array(256);
      for (let n=0;n<256;n++){
        let c = n;
        for (let k=0;k<8;k++) c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
        t[n] = c >>> 0;
      }
      return t;
    })();
    function crc32(str){
      let crc = 0 ^ (-1);
      for (let i=0;i<str.length;i++){
        crc = (crc >>> 8) ^ CRC_TABLE[(crc ^ str.charCodeAt(i)) & 0xFF];
      }
      return (crc ^ (-1)) >>> 0;
    }

    // =================== Allocator: Equal-first + Group-fair (CRC32) ===================
    function allocateInt(needs, stock) {
      const n = needs.length;
      if (n === 0) return [];
      const sumNeedInt = needs.reduce((a,b)=>a + Math.round(b), 0);                // <— pakai SUM(round)
      const target     = Math.min(Math.floor(stock || 0), sumNeedInt);             // <— target sinkron
      if (target <= 0 || sumNeedInt <= 0) return Array(n).fill(0);

      // seed sama dengan controller: crc32(rkhno)
      const rkhInput = document.querySelector('input[name="rkhno"]');
      const seed = crc32(rkhInput?.value || '');

      // ids stabil: "Blok|Plot" dari data-id kalau ada; fallback teks kolom 0 & 1
      const trs = Array.from(document.getElementById('plotTable').rows);
      const ids = trs.map(tr => tr.getAttribute('data-id')?.trim()
        || `${(tr.cells?.[0]?.textContent||'').trim()}|${(tr.cells?.[1]?.textContent||'').trim()}`);

      // cap = round(need) (selaras controller & DB)
      const cap    = needs.map(v => Math.round(v));

      // kuota proporsional (prioritas sekunder untuk tie-break)
      const sumFloat = needs.reduce((a,b)=>a+b, 0);
      const quotas = needs.map(v => (sumFloat>0 ? v/sumFloat*target : 0));
      const fracs  = quotas.map(q => q - Math.floor(q));

      // 1) equal-first baseline (clamp cap)
      const base  = Math.floor(target / n);
      const alloc = Array(n).fill(0).map((_,i)=> Math.min(base, cap[i]));
      let remain  = target - alloc.reduce((a,b)=>a+b,0);
      if (remain <= 0) return alloc;

      // 2) group by rounded need (desc)
      const needInt = needs.map(v => Math.round(v));
      const groups  = new Map();
      for (let i=0;i<n;i++){ (groups.get(needInt[i]) ?? groups.set(needInt[i],[]).get(needInt[i])).push(i); }
      const groupKeys = Array.from(groups.keys()).sort((a,b)=>b-a);

      // urut anggota grup: (crc32(id)^seed) asc, tie frac desc, tie index asc
      const orderGroup = idxs => idxs.slice().sort((a,b)=>{
        const ha = (crc32(ids[a]||'') ^ seed) >>> 0;
        const hb = (crc32(ids[b]||'') ^ seed) >>> 0;
        if (ha === hb){
          if (fracs[a] === fracs[b]) return a - b;
          return fracs[b] - fracs[a];
        }
        return ha - hb;
      });

      // 3) bagi sisa per GRUP need: meratakan dulu; selisih dalam grup ≤ 1
      while (remain > 0){
        let progressed = false;

        for (const k of groupKeys){
          if (remain <= 0) break;

          const all = groups.get(k);
          const idxs = all.filter(i => alloc[i] < cap[i]);
          if (idxs.length === 0) continue;

          const ord = orderGroup(idxs);

          if (remain >= ord.length){
            for (const i of ord) alloc[i] += 1;
            remain -= ord.length;
            progressed = true;
            continue;
          }

          for (let t=0; t<remain; t++) alloc[ord[t]] += 1;
          remain = 0;
          progressed = true;
          break;
        }

        if (!progressed) break;
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

      const allocTJ = allocateInt(needsTJ, stokTJ);
      const allocTC = allocateInt(needsTC, stokTC);

      let sumAllocTJ = 0, sumAllocTC = 0;

      // Hanya update angka (tanpa rebuild innerHTML)
      for (let i=0;i<meta.length;i++){
        const m = meta[i];
        const aTJ = allocTJ[i]|0, aTC = allocTC[i]|0;
        sumAllocTJ += aTJ; sumAllocTC += aTC;

        // if (m.tjEl) m.tjEl.textContent = fmt0(aTJ);
        // if (m.tcEl) m.tcEl.textContent = fmt0(aTC);

      if (m.tjEl) (m.tjEl.tagName === 'INPUT') ? m.tjEl.value = String(aTJ) : m.tjEl.textContent = fmt0(aTJ);
      if (m.tcEl) (m.tcEl.tagName === 'INPUT') ? m.tcEl.value = String(aTC) : m.tcEl.textContent = fmt0(aTC);

      }

      if (sumTJCell)  sumTJCell.textContent  = fmt0(sumAllocTJ);
      if (sumTCCell)  sumTCCell.textContent  = fmt0(sumAllocTC);

      // ===== Ringkasan pakai kebutuhan integer & target kebutuhan =====
      const needTJInt = sumNeedTJIntConst;
      const needTCInt = sumNeedTCIntConst;

      totalTJEl.textContent = needTJInt.toLocaleString('id-ID');
      totalTCEl.textContent = needTCInt.toLocaleString('id-ID');

      // target = min(floor(stok), sumNeedInt)
      const targetTJ = Math.min(Math.floor(stokTJ), needTJInt);
      const targetTC = Math.min(Math.floor(stokTC), needTCInt);

      stokTJEl.textContent  = fmt0(stokTJ);
      stokTCEl.textContent  = fmt0(stokTC);
      sisaTJEl.textContent  = fmt0(Math.floor(stokTJ) - sumAllocTJ);
      sisaTCEl.textContent  = fmt0(Math.floor(stokTC) - sumAllocTC);

      const okTJ = sumAllocTJ >= needTJInt;
      const okTC = sumAllocTC >= needTCInt;
      statusTJ.textContent = okTJ ? 'TJ CUKUP' : 'TJ KURANG';
      statusTC.textContent = okTC ? 'TC CUKUP' : 'TC KURANG';
      statusTJ.className = `text-center text-sm font-medium rounded-md py-1 ${okTJ ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
      statusTC.className = `text-center text-sm font-medium rounded-md py-1 ${okTC ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;

      summary.classList.remove('hidden');
    }

    function resetUI(){
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

    // Render awal (non-blocking)
// Render awal (non-blocking) - HANYA jika user mengetik stok TJ/TC
function scheduleFirstRender(){
  // ✅ JANGAN auto-render saat page load
  // Biarkan user yang trigger render dengan mengetik stok TJ/TC
  
  // Tapi tetap hitung total dan sisa dari data existing
  if (hasBoth()) {
    recalcTotalsFromInputs();
  }
}
scheduleFirstRender();

    // Cetak: render sebelum print
    // window.onbeforeprint = function(){ if (hasBoth()) render(); };
    // if (window.matchMedia) {
    //   const mq = window.matchMedia('print');
    //   mq.addEventListener?.('change', e => { if (e.matches && hasBoth()) render(); });
    // }

    // ===== FUNGSI RECALC (PINDAHKAN KE DALAM SCOPE) =====
    function recalcTotalsFromInputs(){
      const stokTJ = parseFloat(inputTJ.value)||0;
      const stokTC = parseFloat(inputTC.value)||0;
      const sumTJ  = sumInputs('.tj-result');
      const sumTC  = sumInputs('.tc-result');

      if (sumTJCell)  sumTJCell.textContent  = fmt0(sumTJ);
      if (sumTCCell)  sumTCCell.textContent  = fmt0(sumTC);
      
      // Update stok
      if (stokTJEl) stokTJEl.textContent = fmt0(stokTJ);
      if (stokTCEl) stokTCEl.textContent = fmt0(stokTC);
      
      // Update sisa
      sisaTJEl.textContent = fmt0(Math.floor(stokTJ) - sumTJ);
      sisaTCEl.textContent = fmt0(Math.floor(stokTC) - sumTC);

      // Update total kebutuhan
      if (totalTJEl) totalTJEl.textContent = sumNeedTJIntConst.toLocaleString('id-ID');
      if (totalTCEl) totalTCEl.textContent = sumNeedTCIntConst.toLocaleString('id-ID');

      document.getElementById('totalNeedTJ_hidden').value = sumNeedTJIntConst;
      document.getElementById('totalNeedTC_hidden').value = sumNeedTCIntConst;

      const okTJ = sumTJ >= sumNeedTJIntConst;
      const okTC = sumTC >= sumNeedTCIntConst;
      statusTJ.textContent = okTJ ? 'TJ CUKUP' : 'TJ KURANG';
      statusTC.textContent = okTC ? 'TC CUKUP' : 'TC KURANG';
      statusTJ.className = `text-center text-sm font-medium rounded-md py-1 ${okTJ ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
      statusTC.className = `text-center text-sm font-medium rounded-md py-1 ${okTC ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
    }

    // Helper function
    function sumInputs(sel){ 
      return [...document.querySelectorAll(sel)].reduce((a,el)=> a + (parseFloat(el.value)||0), 0); 
    }

    // ✅ Real-time update saat user mengedit TJ/TC di plot
    plotTable.addEventListener('input', (e)=>{
      if (e.target.matches('.tj-result, .tc-result')) recalcTotalsFromInputs();
    });

    plotTable.addEventListener('change', (e)=>{
      if (e.target.matches('.tj-result, .tc-result')) recalcTotalsFromInputs();
    });

    // Event listener untuk input stok TJ/TC
    inputTJ.addEventListener('change', recalcTotalsFromInputs);
    inputTC.addEventListener('change', recalcTotalsFromInputs);
    inputTJ.addEventListener('input', recalcTotalsFromInputs);
    inputTC.addEventListener('input', recalcTotalsFromInputs);

    dosageEl.addEventListener('change', function(){
  // Recalc all needs dengan basis baru
  needsTJ = [];
  needsTC = [];
  const dosage = getDosage();

  for (let i = 0; i < rows.length; i++) {
    const r = rows[i];
    const luas = parseFloat(r.dataset.luas) || 0;
    const umur = parseInt(r.dataset.umur) || 0;
    const bulan = Math.max(1, Math.ceil(umur/30));
    const p = pcts[Math.min(bulan,10)-1] || {tj:0.5,tc:0.5};
    
    const total = luas * dosage;
    const needTJ = total * p.tj;
    const needTC = total * p.tc;

    needsTJ.push(needTJ);
    needsTC.push(needTC);

    if (meta[i] && meta[i].fEl) {
      meta[i].fEl.innerHTML =
        `Pembagian: ` +
        `<span class="inline-block rounded px-2 py-0.5 bg-blue-100 font-semibold">TJ ${Math.round(p.tj*100)}%</span> / ` +
        `<span class="inline-block rounded px-2 py-0.5 bg-green-100 font-semibold">TC ${Math.round(p.tc*100)}%</span> dari ${fmt0(total)} lembar. <br>`+
        `Kebutuhan: ` +
        `<span class="inline-block rounded px-2 py-0.5 bg-blue-100 font-semibold">TJ ${needTJ.toFixed(2)}</span>, ` +
        `<span class="inline-block rounded px-2 py-0.5 bg-green-100 font-semibold">TC ${needTC.toFixed(2)}</span>`;
    }
  }

  const needTJIntArr = needsTJ.map(v => Math.round(v));
  const needTCIntArr = needsTC.map(v => Math.round(v));
  sumNeedTJIntConst = needTJIntArr.reduce((a,b)=>a+b,0);
  sumNeedTCIntConst = needTCIntArr.reduce((a,b)=>a+b,0);

  if (totalTJEl) totalTJEl.textContent = sumNeedTJIntConst.toLocaleString('id-ID');
  if (totalTCEl) totalTCEl.textContent = sumNeedTCIntConst.toLocaleString('id-ID');
  
  document.getElementById('totalNeedTJ_hidden').value = sumNeedTJIntConst;
  document.getElementById('totalNeedTC_hidden').value = sumNeedTCIntConst;

  lastTJ = null; lastTC = null;
  if (hasBoth()) render();
  else recalcTotalsFromInputs();
});


    // Initial calculation
    setTimeout(recalcTotalsFromInputs, 250);

  });


</script>



</x-layout>
