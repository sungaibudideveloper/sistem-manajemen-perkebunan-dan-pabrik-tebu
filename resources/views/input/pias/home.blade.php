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
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Blok</th>
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Luas</th>
                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($data as $item)
                <tr>
                  <td class="py-2 px-4">{{ $item->rkhno }}</td>
                  <td class="py-2 px-4">{{ date('d M Y', strtotime($item->created_at)) }}</td>
                  <td class="py-2 px-4">{{ $item->blok ?? 'N/A' }}</td>
                  <td class="py-2 px-4">{{ $item->luas ?? 'N/A' }}</td>
                  <td class="py-2 px-4">
                    <button class="btn btn-primary btn-sm" onclick="tebarPias('{{ $item->rkhno }}')">
                      Tebar Pias
                    </button>
                  </td>
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