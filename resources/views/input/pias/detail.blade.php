<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="w-full p-4">
    <!-- Header inputs -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
      <div class="border rounded-md p-4 bg-white shadow-sm">
        <h3 class="text-lg font-bold mb-4">Input TJ & TC</h3>
        <div class="space-y-4">
          <div>
            <label class="block mb-2">Total TJ (stok opsional)</label>
            <input type="number" id="inputTJ" class="w-full border rounded-md p-2 bg-gray-50" placeholder="Masukkan Total TJ" onchange="this.value = parseFloat(this.value || 0).toFixed(3); render()">
          </div>
          <div>
            <label class="block mb-2">Total TC (stok opsional)</label>
            <input type="number" id="inputTC" class="w-full border rounded-md p-2 bg-gray-50" placeholder="Masukkan Total TC" onchange="this.value = parseFloat(this.value || 0).toFixed(3); render()">
          </div>
        </div>
      </div>

      <div class="border rounded-md p-4 bg-white shadow-sm">
        <h3 class="text-lg font-bold mb-4">RKH Detail</h3>
        <div class="space-y-2">
          <p>RKH No: {{ $data[0]->rkhno }}</p>
          <p>Mandor: {{ $data[0]->mandor_name }}</p>
          <p>Total Luas: {{ $data[0]->totalluas }} Ha</p>
        </div>
      </div>
    </div>

    <!-- Tabel plot -->
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

                <!-- BADGE KUNING -->
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
      </div>

      <!-- Summary card -->
      <div id="summaryCard" class="max-w-3xl mx-auto my-6 mt-2 mb-2 p-5 bg-white border rounded-xl shadow-sm hidden">
        <h4 class="text-lg font-semibold text-center mb-4">Ringkasan Kebutuhan vs Stok</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="border rounded-lg p-4 bg-gray-50">
            <div class="text-sm text-gray-600 mb-2">Total Kebutuhan</div>
            <div class="flex items-center justify-between">
              <div class="text-2xl font-bold text-blue-800">TJ <span id="totalTJ">0</span></div>
              <div class="text-2xl font-bold text-green-800">TC <span id="totalTC">0</span></div>
            </div>
          </div>
          <div class="border rounded-lg p-4 bg-gray-50">
            <div class="text-sm text-gray-600 mb-2">Stok & Sisa</div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <div class="text-xs text-gray-500">Stok TJ</div>
                <div class="text-xl font-semibold text-blue-800" id="stokTJ">0</div>
                <div class="text-xs" id="sisaTJWrap">Sisa: <span id="sisaTJ">0</span></div>
              </div>
              <div>
                <div class="text-xs text-gray-500">Stok TC</div>
                <div class="text-xl font-semibold text-green-800" id="stokTC">0</div>
                <div class="text-xs" id="sisaTCWrap">Sisa: <span id="sisaTC">0</span></div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <div id="statusTJ" class="text-center text-sm font-medium rounded-md py-2 mb-2"></div>
          <div id="statusTC" class="text-center text-sm font-medium rounded-md py-2"></div>
        </div>
      </div>
    </div>
  </div>

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

      const persentases = [
        {tj: 0.7,  tc: 0.3},   // Bulan 1
        {tj: 0.7,  tc: 0.3},   // Bulan 2
        {tj: 0.69, tc: 0.4},   // Bulan 3
        {tj: 0.5,  tc: 0.5},   // Bulan 4
        {tj: 0.4,  tc: 0.6},   // Bulan 5
        {tj: 0.3,  tc: 0.7},   // Bulan 6
        {tj: 0.3,  tc: 0.7},   // Bulan 7
        {tj: 0.3,  tc: 0.7},   // Bulan 8
        {tj: 0.3,  tc: 0.7},   // Bulan 9
        {tj: 0.3,  tc: 0.7}    // Bulan 10+
      ];

      // Hitung kebutuhan per plot
      function hitungPias(luas, umur) {
        const bulan = Math.max(1, Math.ceil(umur / 30));
        const idx = Math.min(bulan, 10) - 1;
        const p = persentases[idx] || {tj: 0.5, tc: 0.5};
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

// Ganti fungsi allocateSmartly yang ada di script dengan ini:
function allocateSmartly(needs, stock) {
  const plotCount = needs.length;
  if (plotCount === 0 || stock === 0) return needs.map(() => 0);
  
  // Limit stock dan totalNeed maksimal 6 angka belakang koma
  const stockLimited = stock; // biarkan asli
  const totalNeed = Math.ceil(needs.reduce((a, b) => a + b, 0) * 10) / 10;
  
  console.log('Stock limited:', stockLimited, 'Total need:', totalNeed, 'Cukup?', stockLimited >= totalNeed);
  
  // JIKA STOK CUKUP: berikan sesuai kebutuhan masing-masing
  if (stockLimited >= totalNeed) {
    console.log('STOK CUKUP - beri sesuai kebutuhan');
    return needs.map(need => Math.round(need * 100) / 100);
  }
  
  // JIKA STOK KURANG: bagi rata SEMUA DAPAT SAMA
  console.log('STOK KURANG - bagi rata');
  
  // Hitung pembagian rata dengan precision 2 decimal
  const evenShare = Math.floor((stockLimited / plotCount) * 100) / 100;
  
  // Semua plot dapat jumlah yang sama
  const allocations = new Array(plotCount).fill(evenShare);
  
  console.log('Even share per plot:', evenShare);
  
  return allocations;
}

      function kosongkanTabel() {
        plotTable.querySelectorAll('tr').forEach(r => {
          const tjCell = r.querySelector('.tj-result');
          const tcCell = r.querySelector('.tc-result');
          const rumus  = r.querySelector('.pias-formula');
          if (tjCell) tjCell.textContent = '';
          if (tcCell) tcCell.textContent = '';
          if (rumus) rumus.textContent = '';
        });
      }

      function render() {
        const stokTJ = parseFloat(inputTJ.value) || 0;
        const stokTC = parseFloat(inputTC.value) || 0;

        // Wajib isi kedua input
        if (stokTJ === 0 || stokTC === 0) {
          kosongkanTabel();
          summary.classList.add('hidden');
          return;
        }

        const rows = Array.from(plotTable.querySelectorAll('tr'));
        const needsTJ = [];
        const needsTC = [];
        const meta    = [];

        // Kumpulkan kebutuhan per plot
        rows.forEach(row => {
          const luas = parseFloat(row.dataset.luas) || 0;
          const umur = parseInt(row.dataset.umur) || 0;
          const res = hitungPias(luas, umur);
          needsTJ.push(res.needTJ);
          needsTC.push(res.needTC);
          meta.push({row, res});
        });

        const totalNeedTJ = Math.round(needsTJ.reduce((a,b)=>a+b, 0) * 100) / 100;
        const totalNeedTC = Math.round(needsTC.reduce((a,b)=>a+b, 0) * 100) / 100;
        console.log('Total Need TJ:', totalNeedTJ, 'Stock TJ:', stokTJ);
console.log('Total Need TC:', totalNeedTC, 'Stock TC:', stokTC);
console.log('Stok cukup TJ?', stokTJ >= totalNeedTJ);
console.log('Stok cukup TC?', stokTC >= totalNeedTC);
        // Alokasi proporsional berdasarkan stok
        const allocTJ = allocateSmartly(needsTJ, stokTJ);
        const allocTC = allocateSmartly(needsTC, stokTC);

        // Render per plot
        meta.forEach((m, i) => {
          const tjCell = m.row.querySelector('.tj-result');
          const tcCell = m.row.querySelector('.tc-result');
          const rumusCell = m.row.querySelector('.pias-formula');
          const needTJi = Math.round(needsTJ[i]);
          const needTCi = Math.round(needsTC[i]);

          // Kolom TJ dan TC - angka saja
          if (tjCell) tjCell.textContent = allocTJ[i];
          if (tcCell) tcCell.textContent = allocTC[i];
          
          // Rumus detail
          if (rumusCell) {
            rumusCell.innerHTML =
              `<strong>Bulan ke ${m.res.bulan}</strong><br>`+
              `Total = ${m.res.total} lbr (25 × luas)<br>`+
              `Persen: TJ ${m.res.persenTJ}% / TC ${m.res.persenTC}%<br>`+
              `Kebutuhan: TJ <b>${needTJi}</b>, TC <b>${needTCi}</b><br>`+
              `Alokasi: TJ <b>${allocTJ[i]}</b>, TC <b>${allocTC[i]}</b>`;
          }
        });

        // Summary
        const sumAllocTJ = allocTJ.reduce((a,b)=>a+b,0);
        const sumAllocTC = allocTC.reduce((a,b)=>a+b,0);

        totalTJEl.textContent = totalNeedTJ;
        totalTCEl.textContent = totalNeedTC;
        stokTJEl.textContent  = stokTJ;
        stokTCEl.textContent  = stokTC;
        sisaTJEl.textContent  = Math.max(0, stokTJ - sumAllocTJ);
        sisaTCEl.textContent  = Math.max(0, stokTC - sumAllocTC);

        const okTJ = sumAllocTJ >= totalNeedTJ;
        const okTC = sumAllocTC >= totalNeedTC;

        statusTJ.textContent = okTJ ? 'Stok TJ CUKUP (alokasi memenuhi kebutuhan)' : 'Stok TJ KURANG (alokasi proporsional)';
        statusTC.textContent = okTC ? 'Stok TC CUKUP (alokasi memenuhi kebutuhan)' : 'Stok TC KURANG (alokasi proporsional)';

        statusTJ.className = `text-center text-sm font-medium rounded-md py-2 mb-2 ${okTJ ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;
        statusTC.className = `text-center text-sm font-medium rounded-md py-2 ${okTC ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-800'}`;

        summary.classList.remove('hidden');
      }

      // Event listeners
      inputTJ.addEventListener('input', render);
      inputTC.addEventListener('input', render);

      // Initial state
      kosongkanTabel();
      summary.classList.add('hidden');
    });
  </script>
</x-layout>