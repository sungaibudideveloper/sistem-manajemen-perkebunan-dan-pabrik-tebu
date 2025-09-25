<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- Form -->
  <form action="{{ route('input.pias.submit', ['rkhno' => $data[0]->rkhno]) }}" method="POST">
    @csrf
  <div class="w-full p-4">
    <!-- Container full width tanpa center alignment -->
    <div class="w-full mb-4">
      <div class="flex gap-3">
        <!-- Card Input -->
        <div class="flex-1 border rounded-md p-3 bg-white shadow-sm">
          <h3 class="text-base font-bold mb-3">Input TJ & TC</h3>
          <div class="space-y-3">
            <div>
              <label class="block mb-1 text-sm">Total TJ (stok opsional)</label>
              <input type="number" name="inputTJ" id="inputTJ" class="w-full border rounded-md p-2 bg-gray-50 text-sm" placeholder="Masukkan Total TJ" onchange="render()">
            </div>
            <div>
              <label class="block mb-1 text-sm">Total TC (stok opsional)</label>
              <input type="number" name="inputTC" id="inputTC" class="w-full border rounded-md p-2 bg-gray-50 text-sm" placeholder="Masukkan Total TC" onchange="render()">
            </div>
          </div>
        </div>
  
        <!-- Card Summary - lebih lebar -->
        <div id="summaryCard" class="flex-[2] border rounded-md p-3 bg-white shadow-sm">
          <h3 class="text-base font-bold mb-3 text-center">Ringkasan Kebutuhan vs Stok</h3>
          <div class="space-y-3">
            <div class="border rounded-lg p-3 bg-blue-50">
              <div class="text-xs text-gray-600 mb-2 text-center">Total Kebutuhan</div>
              <div class="flex items-center justify-center gap-4">
                <div class="text-lg font-bold text-blue-800">TJ <span id="totalTJ">0</span></div>
                <div class="text-lg font-bold text-green-800">TC <span id="totalTC">0</span></div>
              </div>
            </div>
            <div class="border rounded-lg p-3 bg-gray-50">
              <div class="text-xs text-gray-600 mb-2 text-center">Stok & Sisa</div>
              <div class="grid grid-cols-2 gap-3">
                <div class="text-center">
                  <div class="text-xs text-gray-500">Stok TJ</div>
                  <div class="text-base font-semibold text-blue-800" id="stokTJ">0</div>
                  <div class="text-xs">Sisa: <span id="sisaTJ">0</span></div>
                </div>
                <div class="text-center">
                  <div class="text-xs text-gray-500">Stok TC</div>
                  <div class="text-base font-semibold text-green-800" id="stokTC">0</div>
                  <div class="text-xs">Sisa: <span id="sisaTC">0</span></div>
                </div>
              </div>
            </div>
            <div class="space-y-1">
              <div id="statusTJ" class="text-center text-xs font-medium rounded-md py-1"></div>
              <div id="statusTC" class="text-center text-xs font-medium rounded-md py-1"></div>
            </div>
          </div>
        </div>
  
        <!-- Card RKH Detail -->
        <div class="flex-1 border rounded-md p-3 bg-white shadow-sm">
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
      <div class="p-4 border-b bg-gray-100">
        <h3 class="text-lg font-bold">Detail Plot</h3>
      </div>
      <div class="overflow-x-auto">

        <table class="w-full text-center">
          <thead>
            <tr class="bg-gray-50">
              <th class="p-3 border-b">Blok</th>
              <th class="p-3 border-b">Plot</th>
              <th class="p-3 border-b">Umur</th>
              <th class="p-3 border-b">Kategory</th>
              <th class="p-3 border-b">Varietas</th>
              <th class="p-3 border-b">Luas (Ha)</th>
              <th class="p-3 border-b bg-blue-50 text-blue-800 font-semibold">TJ</th>
              <th class="p-3 border-b bg-green-50 text-green-800 font-semibold">TC</th>
              <th class="p-3 border-b">Rumus</th>
            </tr>
          </thead>
          <tbody id="plotTable">
            @foreach($data as $item)
              @php
                $hari = abs($item->rkhdate->diffInDays($item->tanggalulangtahun));
                $bulan = ceil($hari / 30);
              @endphp
              <tr class="hover:bg-gray-50"
                  data-luas="{{ $item->luasrkh }}"
                  data-umur="{{ $hari }}">
                <td class="p-3 border-b">{{ $item->blok }}</td>
                <td class="p-3 border-b">{{ $item->plot }}</td>
                <td class="p-3 border-b">
                  <span class="inline-flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-800 text-xs font-semibold">Bulan ke {{ $bulan }}</span>
                    <span class="text-gray-700 text-sm">({{ $hari }} hari sejak tanam)</span>
                  </span>
                </td> 
                <td class="p-3 border-b">{{ $item->kodestatus }}</td>
                <td class="p-3 border-b">{{ $item->kodevarietas }}</td>
                <td class="p-3 border-b">{{ $item->luasrkh }}</td>
                <td class="p-3 border-b tj-result bg-blue-50 font-semibold text-blue-800"></td>
                <td class="p-3 border-b tc-result bg-green-50 font-semibold text-green-800"></td>
                <td class="p-3 border-b pias-formula text-left text-sm"></td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="flex justify-center mt-4 mb-4">
          <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition"
          >
              Generate
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
        return tj > 0 && !Number.isNaN(tj) && tc > 0 && !Number.isNaN(tc);
      };
    
      // format tampilan: truncate ke 3 desimal (tanpa pengaruh ke nilai asli)
      function fmt3(x) {
        if (!Number.isFinite(x)) return '0';
        const t = Math.trunc(x * 1000) / 1000;
        return t.toLocaleString('id-ID', {maximumFractionDigits: 3, minimumFractionDigits: 0});
      }
    
      function kebutuhan(luas, umur){
        const bulan = Math.max(1, Math.ceil(umur/30));
        const p = pcts[Math.min(bulan,10)-1] || {tj:0.5,tc:0.5};
        const total = luas * 25;
        return {
          bulan,
          total,
          needTJ: total * p.tj,
          needTC: total * p.tc,
          persenTJ: (p.tj*100).toFixed(0),
          persenTC: (p.tc*100).toFixed(0)
        };
      }
    
      // alokasi sama-rata tanpa pembulatan (continuous water-filling)
      function allocateEqualNoRound(needs, stock){
        const n = needs.length;
        const alloc = new Array(n).fill(0);
        const idx = needs.map((v,i)=> v>0 ? i : null).filter(i=>i!==null);
        let remain = stock;
        if (remain <= 0 || idx.length === 0) return alloc;
    
        const totalNeed = needs.reduce((a,b)=>a+b,0);
        if (remain >= totalNeed) return needs.slice(); // penuhi semua
    
        let active = idx.slice();
        while (active.length > 0 && remain > 0){
          const share = remain / active.length;
          const still = [];
          for (const i of active){
            const gap = needs[i] - alloc[i];
            if (gap <= share + 1e-12){
              alloc[i] += gap;
              remain   -= gap;
            } else {
              alloc[i] += share;
              remain   -= share;
              still.push(i);
            }
          }
          if (still.length === active.length) break; // semua dapat share sama; stok habis
          active = still;
        }
        return alloc;
      }
    
      function resetUI(){
        [...plotTable.querySelectorAll('tr')].forEach(r=>{
          const h=r.querySelector('.pias-results');
          const f=r.querySelector('.pias-formula');
          if(h) h.textContent='';
          if(f) f.textContent='';
        });
        summary.classList.add('hidden');
      }
    
      function render(){
        if(!hasBoth()){ resetUI(); return; }
    
        const stokTJ = parseFloat(inputTJ.value) || 0;
        const stokTC = parseFloat(inputTC.value) || 0;
    
        const rows = [...plotTable.querySelectorAll('tr')];
        const needsTJ=[], needsTC=[], meta=[];
    
        rows.forEach(r=>{
          const luas = parseFloat(r.dataset.luas) || 0;
          const umur = parseInt(r.dataset.umur) || 0;
          const k = kebutuhan(luas, umur);
          needsTJ.push(k.needTJ);
          needsTC.push(k.needTC);
          meta.push({row:r,res:k});
        });
    
        const allocTJ = allocateEqualNoRound(needsTJ, stokTJ);
        const allocTC = allocateEqualNoRound(needsTC, stokTC);
    
        let sumNeedTJ=0, sumNeedTC=0, sumAllocTJ=0, sumAllocTC=0;
    
        meta.forEach((m,i)=>{
          const h = m.row.querySelector('.pias-results');
          const f = m.row.querySelector('.pias-formula');
          sumNeedTJ  += needsTJ[i];
          sumNeedTC  += needsTC[i];
          sumAllocTJ += allocTJ[i];
          sumAllocTC += allocTC[i];
    
          if (h) h.textContent = `B${m.res.bulan}: TJ ${fmt3(allocTJ[i])}, TC ${fmt3(allocTC[i])}`;
          if (f) f.innerHTML =
            `Total=${fmt3(m.res.total)} lbr<br>`+
            `Persen B${m.res.bulan}: TJ ${m.res.persenTJ}% / TC ${m.res.persenTC}%<br>`+
            `Kebutuhan: TJ ${fmt3(needsTJ[i])}, TC ${fmt3(needsTC[i])}<br>`+
            `Alokasi: TJ ${fmt3(allocTJ[i])}, TC ${fmt3(allocTC[i])}`;
        });
    
        totalTJEl.textContent = fmt3(sumNeedTJ);
        totalTCEl.textContent = fmt3(sumNeedTC);
        stokTJEl.textContent  = fmt3(stokTJ);
        stokTCEl.textContent  = fmt3(stokTC);
        sisaTJEl.textContent  = fmt3(stokTJ - sumAllocTJ);
        sisaTCEl.textContent  = fmt3(stokTC - sumAllocTC);
    
        const okTJ = sumAllocTJ + 1e-9 >= sumNeedTJ;
        const okTC = sumAllocTC + 1e-9 >= sumNeedTC;
        statusTJ.textContent = okTJ ? 'TJ CUKUP' : 'TJ KURANG';
        statusTC.textContent = okTC ? 'TC CUKUP' : 'TC KURANG';
        statusTJ.className = `text-center text-sm font-medium rounded-md py-2 mb-2 ${okTJ ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
        statusTC.className = `text-center text-sm font-medium rounded-md py-2 ${okTC ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
    
        summary.classList.remove('hidden');
      }
    
      let timer; const IDLE=800;
      function schedule(){
        clearTimeout(timer);
        if(hasBoth()) timer=setTimeout(render,IDLE);
        else resetUI();
      }
      ['input','change','blur'].forEach(evt=>{
        inputTJ.addEventListener(evt, schedule);
        inputTC.addEventListener(evt, schedule);
      });
      [inputTJ,inputTC].forEach(el=>{
        el.addEventListener('keydown', e=>{
          if(e.key==='Enter'){ clearTimeout(timer); hasBoth()?render():resetUI(); }
        });
      });
    
      resetUI();
    });
    </script>
      
    
</x-layout>