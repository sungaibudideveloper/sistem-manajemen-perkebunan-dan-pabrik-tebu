
<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <style>
    body {
     font-family: 'Roboto', sans-serif;
     margin: 40px;
     background-color: #f7f9fc;
 }
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
 th {
     background-color: #0057e7;
     color: white;
 }
 tr:nth-child(even) {
     background-color: #f2f2f2;
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
    <h1 class="text-2xl font-bold text-center mb-8">Laporan Analisa Timeline Aktivitas Perkebunan Tebu</h1>
    <div x-data="{ modalOpen: false, detail: {} }">
         <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
             <thead class="bg-blue-600 text-white">
                 <tr>
                     <th class="py-3 px-5">No</th>
                     <th class="py-3 px-5">Aktivitas</th>
                     <th class="py-3 px-5">Tgl Mulai (Target)</th>
                     <th class="py-3 px-5">Tgl Selesai (Target)</th>
                     <th class="py-3 px-5">Durasi Target (hari)</th>
                     <th class="py-3 px-5">Tepat Waktu (%)</th>
                 </tr>
             </thead>
             <tbody >
                 <template x-for="activity in [
                     {no:1, nama:'Pembukaan Lahan', targetStart:'01 Jan 2025', targetEnd:'07 Jan 2025', durasiTarget:7, persen:'89'},
                     {no:2, nama:'Penanaman', targetStart:'08 Jan 2025', targetEnd:'15 Jan 2025', durasiTarget:8, persen:'97'},
                     {no:3, nama:'Pemupukan Pertama', targetStart:'20 Jan 2025', targetEnd:'22 Jan 2025', durasiTarget:3, persen:'100'},
                     {no:4, nama:'Pemeliharaan', targetStart:'25 Jan 2025', targetEnd:'30 Apr 2025', durasiTarget:96, persen:'100'},
                     {no:5, nama:'Pemupukan Kedua', targetStart:'10 Mei 2025', targetEnd:'12 Mei 2025', durasiTarget:3, persen:'97'},
                     {no:6, nama:'Panen', targetStart:'15 Jul 2025', targetEnd:'20 Jul 2025', durasiTarget:6, persen:'99'}
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
                     </tr>
                 </template>
             </tbody>
         </table>

         <!-- Modal -->
         <div x-data="{ detailModalOpen: false, detailAktivitas: '', detailData: [] }">
  <div x-show="modalOpen" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center px-4 py-8 z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full h-full max-w-screen-xl max-h-screen overflow-auto">
      <h2 class="text-2xl font-bold mb-4 text-center" x-text="`${detail.nama} - Per Blok`"></h2>
      <table class="min-w-full border-collapse">
        <thead class="bg-gray-200">
          <tr>
            <th class="py-3 px-4">Blok</th>
            <th class="py-3 px-4">Aktifitas</th>
            <th class="py-3 px-4">Tgl Mulai (Target)</th>
            <th class="py-3 px-4">Tgl Selesai (Target)</th>
            <th class="py-3 px-4">Durasi Target (hari)</th>
            <th class="py-3 px-4">Tepat Waktu (%)</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="activity in [
            {nama: 'Perencanaan Kegiatan Pembersihan Lahan', persen:98},
            {nama: 'Penebasan dan Penumpukan', persen:98},
            {nama: 'Penumbangan Tegakan dan Penumpukan', persen:70},
            {nama: 'Pembentukan Lahan Tanam', persen:70},
            {nama: 'Pembentukan Badan Jalan', persen:92},
            {nama: 'Pembuatan Jembatan', persen:95},
            {nama: 'Pembuatan Gorong Gorong', persen:95},
            {nama: 'Pembuatan Parit dan Kanal', persen:99},
            {nama: 'Pembuatan Embung', persen:99}
          ]">
            <tr class="cursor-pointer hover:bg-gray-100" @click="detailAktivitas = activity.nama; detailModalOpen = true; detailData = generateDetailData()">
              <td class="py-2 px-4 text-center">A</td>
              <td class="py-2 px-4 text-left font-semibold" x-text="activity.nama"></td>
              <td class="py-2 px-4 text-center">01 Jan 2025</td>
              <td class="py-2 px-4 text-center">01 Jan 2025</td>
              <td class="py-2 px-4 text-right">1</td>
              <td class="py-2 px-4 font-semibold text-right"
                :class="{
                  'text-green-500': activity.persen >= 98,
                  'text-red-500': activity.persen < 90,
                  'text-blue-500': activity.persen >=90 && activity.persen <=97
                }"
                x-text="`${activity.persen}%`">
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <button @click="modalOpen = false" class="mt-4 bg-blue-600 text-white py-2 px-4 rounded">Tutup</button>
    </div>
  </div>

  <!-- Nested Modal (Detail Aktivitas) -->
  <div x-show="detailModalOpen" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center px-4 py-8 z-50" style="display: none;">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-7xl max-h-screen overflow-auto">
      <h3 class="text-xl font-bold mb-4 text-center" x-text="detailAktivitas"></h3>
      <table class="min-w-full border-collapse">
        <thead class="bg-gray-300">
          <tr>
            <th class="py-3 px-4 text-center">Plot</th>
            <th class="py-3 px-4 text-center">Tgl Mulai (Target)</th>
            <th class="py-3 px-4 text-center">Tgl Selesai (Target)</th>
            <th class="py-3 px-4 text-right">Durasi Target (hari)</th>
            <th class="py-3 px-4 text-center">Tgl Mulai (Realisasi)</th>
            <th class="py-3 px-4 text-center">Tgl Selesai (Realisasi)</th>
            <th class="py-3 px-4 text-right">Durasi Realisasi (hari)</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="item in detailData">
            <tr class="hover:bg-gray-50">
              <td class="py-2 px-4 text-center" x-text="item.plot"></td>
              <td class="py-2 px-4 text-center" x-text="item.targetStart"></td>
              <td class="py-2 px-4 text-center" x-text="item.targetEnd"></td>
              <td class="py-2 px-4 text-right" x-text="item.durasiTarget"></td>
              <td class="py-2 px-4 text-center" x-text="item.realStart"></td>
              <td class="py-2 px-4 text-center" x-text="item.realEnd"></td>
              <td class="py-2 px-4 text-right" x-text="item.durasiReal"></td>
            </tr>
          </template>
        </tbody>
      </table>
      <button @click="detailModalOpen = false" class="mt-4 bg-red-500 text-white py-2 px-4 rounded">Tutup</button>
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
        realEnd: '02 Jan 2025',
        durasiReal: Math.floor(Math.random()*5)+1,
      });
    }
    return data;
  }
</script>
     </div>

</x-layout>
