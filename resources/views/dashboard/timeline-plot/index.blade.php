
<x-layout>
    <x-slot:title>{{ $title }} yo</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <style>
 h1 {
     text-align: center;
     color: #333;
     margin-bottom: 40px;
 }
 table {
     width: 100%;
     border-collapse: collapse;
     box-shadow: 0 4px 8px rgba(0,0,0,0.1);
 }
 th, td {
     border: 1px solid #ddd;
     padding: 12px;
 }
 tr:hover {
     background-color: #e9f4ff;
 }
 .status-ontime {
     color: #28a745;
     font-weight: bold;
 }
 .status-late {
     color: #dc3545;
     font-weight: bold;
 }
 .status-early {
     color: #17a2b8;
     font-weight: bold;
 }
       </style>
    <div class="mx-auto px-6">
    <div x-data="{ modalOpen: false, detail: {} }">
         <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
             <thead class="bg-blue-600 text-white">
                 <tr>
                     <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No</th>
                     <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Aktivitas</th>
                     <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tgl Mulai (Target)</th>
                     <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tgl Selesai (Target)</th>
                     <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Durasi Target (hari)</th>
                     <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tepat Waktu (%)</th>
                     <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Biaya Langsung (Rp.)</th>
                 </tr>
             </thead>
             <tbody >
                 <template x-for="activity in [
                     {no:1, nama:'Pembukaan Lahan', targetStart:'01 Jan 2025', targetEnd:'07 Jan 2025', durasiTarget:7, persen:'89', biaya:'20.000.000'},
                     {no:2, nama:'Penanaman', targetStart:'08 Jan 2025', targetEnd:'15 Jan 2025', durasiTarget:8, persen:'97', biaya:'25.000.000'},
                     {no:3, nama:'Pemupukan Pertama', targetStart:'20 Jan 2025', targetEnd:'22 Jan 2025', durasiTarget:3, persen:'100', biaya:'30.000.000'},
                     {no:4, nama:'Pemeliharaan', targetStart:'25 Jan 2025', targetEnd:'30 Apr 2025', durasiTarget:96, persen:'100', biaya:'40.000.000'},
                     {no:5, nama:'Pemupukan Kedua', targetStart:'10 Mei 2025', targetEnd:'12 Mei 2025', durasiTarget:3, persen:'97', biaya:'10.000.000'},
                     {no:6, nama:'Panen', targetStart:'15 Jul 2025', targetEnd:'20 Jul 2025', durasiTarget:6, persen:'99', biaya:'10.000.000'}
                 ]">
                     <tr @click="detail = activity; modalOpen = true" class="hover:bg-blue-100 cursor-pointer">
                         <td class="py-2 px-4 text-right" x-text="activity.no"></td>
                         <td class="py-2 px-4" x-text="activity.nama"></td>
                         <td class="py-2 px-4 text-center pr-3" x-text="activity.targetStart"></td>
                         <td class="py-2 px-4 text-center pr-3" x-text="activity.targetEnd"></td>
                         <td class="py-2 px-4 text-right pr-3" x-text="activity.durasiTarget"></td>
                         <td class="py-2 px-4 font-semibold text-right pr-3" :class="{
                                'text-green-500': activity.persen >= 98,
                                'text-red-500': activity.persen < 90,
                                'text-blue-500': activity.persen >= 90 && activity.persen <= 97
                            }" x-text="`${activity.persen} %`"></td>
                          <td class="py-2 px-4 text-right pr-3" x-text="activity.biaya"></td>
                     </tr>
                 </template>
             </tbody>
         </table>

         <!-- Modal -->
         <div x-data="{ detailModalOpen: false, detailAktivitas: '', detailData: [] }">
  <div x-show="modalOpen" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center px-4 py-8 z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full h-full max-w-screen-xl max-h-screen overflow-auto">
      <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold" x-text="`${detail.nama} - Per Blok`"></h2>
          <button @click="modalOpen = false" class="p-2 hover:bg-gray-200 rounded-md">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                  viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18 17.94 6M18 18 6.06 6" />
              </svg>
          </button>
      </div>
      <table class="min-w-full border-collapse">
        <tr class="cursor-pointer hover:bg-gray-100">
        </tr>
        <thead class="bg-gray-200">
          <tr>
            <th colspan="3">Blok A</th>
          </tr>
          <tr>
            <th align="center">Tgl Mulai : 01 Jan 2025</th>
            <th align="center">Tgl Selesai : 07 Jan 2025</th>
            <th colspan="2" align="center">Durasi Target : 7</th>
          </tr>
          <tr>
            <th class="py-3 px-4">Aktifitas</th>
            <th class="py-3 px-4">Tepat Waktu (%)</th>
            <th class="py-3 px-4">Biaya Langsung (Rp.)</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="activity in [
            {nama: 'Perencanaan Kegiatan Pembersihan Lahan', persen:98, biaya:'0'},
            {nama: 'Penebasan dan Penumpukan', persen:98, biaya:'2.000.000'},
            {nama: 'Penumbangan Tegakan dan Penumpukan', persen:70, biaya:'2.000.000'},
            {nama: 'Pembentukan Lahan Tanam', persen:70, biaya:'2.000.000'},
            {nama: 'Pembentukan Badan Jalan', persen:92, biaya:'2.000.000'},
            {nama: 'Pembuatan Jembatan', persen:95, biaya:'2.000.000'},
            {nama: 'Pembuatan Gorong Gorong', persen:95, biaya:'2.000.000'},
            {nama: 'Pembuatan Parit dan Kanal', persen:99, biaya:'2.000.000'},
            {nama: 'Pembuatan Embung', persen:99, biaya:'2.000.000'}
          ]">
            <tr class="cursor-pointer hover:bg-gray-100" @click="detailAktivitas = activity.nama; detailModalOpen = true; detailData = generateDetailData()">
              <td class="py-2 px-4 text-left font-semibold" x-text="activity.nama"></td>
              <td class="py-2 px-4 font-semibold text-right"
                :class="{
                  'text-green-500': activity.persen >= 98,
                  'text-red-500': activity.persen < 90,
                  'text-blue-500': activity.persen >=90 && activity.persen <=97
                }"
                x-text="`${activity.persen}%`">
              </td>
              <td class="py-2 px-4 text-right" x-text="activity.biaya"></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Nested Modal (Detail Aktivitas) -->
  <div x-show="detailModalOpen" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center px-4 py-8 z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-7xl max-h-screen overflow-auto">
      <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-bold" x-text="detailAktivitas"></h2>
          <button @click="detailModalOpen = false" class="p-2 hover:bg-gray-200 rounded-md">
              <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                  viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18 17.94 6M18 18 6.06 6" />
              </svg>
          </button>
      </div>
      <table class="min-w-full border-collapse">
        <thead class="bg-gray-300">
          <tr>
            <th align="center">Blok : A</th>
            <th align="center">Tgl Mulai : 01 Jan 2025</th>
            <th align="center">Tgl Selesai : 07 Jan 2025</th>
            <th colspan="2" align="center">Durasi Target : 7</th>
          </tr>
          <tr>
            <th class="py-3 px-4 text-center">Plot</th>
            <th class="py-3 px-4 text-center">Tgl Mulai (Realisasi)</th>
            <th class="py-3 px-4 text-center">Tgl Selesai (Realisasi)</th>
            <th class="py-3 px-4 text-right">Durasi Realisasi (hari)</th>
            <th class="py-3 px-4 text-right">Biaya Langsung (Rp.)</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="item in detailData">
            <tr class="hover:bg-gray-50">
              <td class="py-2 px-4 text-center" x-text="item.plot"></td>
              <td class="py-2 px-4 text-center" x-text="item.realStart"></td>
              <td class="py-2 px-4 text-center" x-text="item.realEnd"></td>
              <td class="py-2 px-4 text-right" x-text="item.durasiReal"></td>
              <td class="py-2 px-4 text-right" x-text="item.biayaLangsung"></td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  function generateDetailData() {
    const data = [];
    for (let i = 1; i <= 30; i++) {
      data.push({
        plot: `A${('00'+i).slice(-3)}`,
        targetStart: '01 Jan 2025',
        targetEnd: Math.floor(Math.random()*5)+1+' Jan 2025',
        durasiTarget: Math.floor(Math.random()*5)+1,
        realStart: '01 Jan 2025',
        realEnd: '07 Jan 2025',
        durasiReal: 7,
        biayaLangsung: Math.floor(Math.random()*50000)+1,
      });
    }
    return data;
  }
</script>
     </div>

</x-layout>
