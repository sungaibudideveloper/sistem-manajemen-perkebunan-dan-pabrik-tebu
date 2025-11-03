{{-- resources/views/input/rkh-panen/index.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="bg-white rounded-lg shadow-md p-6">
    
    <!-- Header with Create Button -->
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Daftar RKH Panen</h2>
      
      <!-- Date Picker Modal Trigger -->
      <button type="button" 
              onclick="document.getElementById('datePickerModal').classList.remove('hidden')"
              class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center gap-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Buat RKH Panen
      </button>
    </div>

    <!-- Filter Section -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
      <form method="GET" action="{{ route('input.rkh-panen.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        
        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cari RKH No</label>
          <input type="text" 
                 name="search" 
                 value="{{ $search }}"
                 placeholder="RKHPN..." 
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Date Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
          <input type="date" 
                 name="filter_date" 
                 value="{{ $filterDate }}"
                 class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select name="filter_status" 
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Semua Status</option>
            <option value="DRAFT" {{ $filterStatus == 'DRAFT' ? 'selected' : '' }}>Draft</option>
            <option value="MOBILE_UPLOAD" {{ $filterStatus == 'MOBILE_UPLOAD' ? 'selected' : '' }}>Mobile Upload</option>
            <option value="COMPLETED" {{ $filterStatus == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
          </select>
        </div>

        <!-- All Date Checkbox -->
        <div class="flex items-end">
          <label class="flex items-center cursor-pointer">
            <input type="checkbox" 
                   name="all_date" 
                   value="1"
                   {{ $allDate ? 'checked' : '' }}
                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700">Semua Tanggal</span>
          </label>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-end gap-2">
          <button type="submit" 
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Filter
          </button>
          <a href="{{ route('input.rkh-panen.index') }}" 
             class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-800 text-white">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">No RKH</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Mandor</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Rencana (Ton/Ha)</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Kontraktor</th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @forelse($rkhPanenData as $rkh)
          <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-4 py-3 text-sm font-medium">
              <a href="{{ route('input.rkh-panen.show', $rkh->rkhpanenno) }}" 
                 class="text-blue-600 hover:text-blue-800 hover:underline font-semibold">
                {{ $rkh->rkhpanenno }}
              </a>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($rkh->rkhdate)->format('d/m/Y') }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">{{ $rkh->mandor_name ?? '-' }}</td>
            <td class="px-4 py-3 text-sm text-gray-600">
              <div class="flex flex-col">
                <span class="font-semibold text-green-700">{{ number_format($rkh->total_netto ?? 0, 2) }} ton</span>
                <span class="text-xs text-gray-500">{{ number_format($rkh->total_ha ?? 0, 2) }} ha</span>
              </div>
            </td>
            <td class="px-4 py-3 text-sm text-gray-600">
              <span class="font-medium">{{ $rkh->kontraktor_count ?? 0 }}</span> kontraktor
            </td>
            <td class="px-4 py-3">
              @if($rkh->status == 'COMPLETED')
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  COMPLETED
                </span>
              @elseif($rkh->status == 'MOBILE_UPLOAD')
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                  MOBILE UPLOAD
                </span>
              @else
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                  {{ $rkh->status }}
                </span>
              @endif
            </td>
            <td class="px-4 py-3 text-center">
              <div class="flex justify-center gap-2">
                
                <!-- Edit Hasil (for MOBILE_UPLOAD status) -->
                @if($rkh->status == 'MOBILE_UPLOAD')
                <a href="{{ route('input.rkh-panen.editHasil', $rkh->rkhpanenno) }}" 
                  class="text-green-600 hover:text-green-800 transition-colors" 
                  title="Input Hasil">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </a>

                <!-- Complete (for MOBILE_UPLOAD status) -->
                <button onclick="confirmComplete('{{ $rkh->rkhpanenno }}')" 
                        class="text-blue-600 hover:text-blue-800 transition-colors" 
                        title="Selesaikan">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </button>
                @endif

                <!-- Delete (only for DRAFT status) -->
                @if($rkh->status == 'DRAFT')
                <button onclick="confirmDelete('{{ $rkh->rkhpanenno }}')" 
                        class="text-red-600 hover:text-red-800 transition-colors" 
                        title="Hapus">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
                @endif

                <!-- No Actions Available -->
                @if($rkh->status == 'COMPLETED')
                <span class="text-gray-400 text-xs">-</span>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
              <div class="flex flex-col items-center">
                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-lg font-medium">Tidak ada data RKH Panen</p>
                <p class="text-sm">Buat RKH Panen baru dengan klik tombol "Buat RKH Panen"</p>
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
      {{ $rkhPanenData->links() }}
    </div>
  </div>

  <!-- Date Picker Modal -->
  <div id="datePickerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-bold text-gray-800">Pilih Tanggal RKH Panen</h3>
        <button onclick="document.getElementById('datePickerModal').classList.add('hidden')" 
                class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <form action="{{ route('input.rkh-panen.create') }}" method="GET">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal RKH</label>
          <input type="date" 
                 name="date" 
                 required
                 class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
        </div>
        
        <div class="flex gap-3">
          <button type="submit" 
                  class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
            Lanjutkan
          </button>
          <button type="button" 
                  onclick="document.getElementById('datePickerModal').classList.add('hidden')"
                  class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
            Batal
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
      <div class="flex items-center mb-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-900">Konfirmasi Hapus</h3>
          <p class="text-sm text-gray-600 mt-1">Apakah Anda yakin ingin menghapus RKH Panen ini?</p>
        </div>
      </div>
      
      <div class="flex gap-3 mt-6">
        <button type="button" 
                onclick="executeDelete()"
                class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
          Ya, Hapus
        </button>
        <button type="button" 
                onclick="document.getElementById('deleteModal').classList.add('hidden')"
                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
          Batal
        </button>
      </div>
    </div>
  </div>

  <!-- Complete Confirmation Modal -->
  <div id="completeModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
      <div class="flex items-center mb-4">
        <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-bold text-gray-900">Selesaikan RKH Panen</h3>
          <p class="text-sm text-gray-600 mt-1">Apakah Anda yakin ingin menyelesaikan RKH Panen ini?</p>
        </div>
      </div>
      
      <div class="flex gap-3 mt-6">
        <button type="button" 
                onclick="executeComplete()"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
          Ya, Selesaikan
        </button>
        <button type="button" 
                onclick="document.getElementById('completeModal').classList.add('hidden')"
                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors">
          Batal
        </button>
      </div>
    </div>
  </div>

  <script>
    let deleteRkhNo = null;
    let completeRkhNo = null;

    function confirmDelete(rkhpanenno) {
      deleteRkhNo = rkhpanenno;
      document.getElementById('deleteModal').classList.remove('hidden');
    }

    function confirmComplete(rkhpanenno) {
      completeRkhNo = rkhpanenno;
      document.getElementById('completeModal').classList.remove('hidden');
    }

    function executeDelete() {
      if (!deleteRkhNo) return;

      const deleteUrl = '{{ route("input.rkh-panen.destroy", ":rkhpanenno") }}'.replace(':rkhpanenno', deleteRkhNo);

      fetch(deleteUrl, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.message || 'Gagal menghapus RKH Panen');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus data');
      })
      .finally(() => {
        document.getElementById('deleteModal').classList.add('hidden');
        deleteRkhNo = null;
      });
    }

    function executeComplete() {
      if (!completeRkhNo) return;

      const completeUrl = '{{ route("input.rkh-panen.complete", ":rkhpanenno") }}'.replace(':rkhpanenno', completeRkhNo);

      fetch(completeUrl, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.message || 'Gagal menyelesaikan RKH Panen');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyelesaikan RKH Panen');
      })
      .finally(() => {
        document.getElementById('completeModal').classList.add('hidden');
        completeRkhNo = null;
      });
    }
  </script>

</x-layout>