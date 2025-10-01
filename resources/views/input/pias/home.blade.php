<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
  
    <div class="w-full">
      <div class="mx-auto px-4 py-4">
        <div class="overflow-x-auto rounded-md border border-gray-300">
          <table class="min-w-full bg-white text-sm text-center">
            <thead>
              <tr>
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No. RKH</th>
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal</th>
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Mandor</th>
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Total Luas</th>
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Man Power</th>
              </tr>
            </thead>
            <tbody>
              @forelse($data as $item)
                <tr>
                  <td class="py-2 px-4" ><a href="#" onclick="location.href='{{ url('input/pias/detail?rkhno='.$item->rkhno) }}'" target="_blank" class="text-blue-600 hover:underline">
                    {{$item->rkhno}}</a></td>
                  <td class="py-2 px-4">{{ date('d M Y', strtotime($item->rkhdate)) }}</td>
                  <td class="py-2 px-4">{{ $item->mandor_name ?? 'N/A' }}</td>
                  <td class="py-2 px-4">{{ $item->totalluas ?? 'N/A' }}</td>
                  <td class="py-2 px-4">{{ $item->manpower ?? 'N/A' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="py-4 text-center text-gray-500">
                    Tidak ada RKH yang sudah selesai
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  
    <script>
    function tebarPias(rkhno) {
      // Modal atau form input tebar pias
      console.log('Tebar Pias untuk RKH: ' + rkhno);
      // Tambahkan logika selanjutnya
    }
    </script>
  </x-layout>