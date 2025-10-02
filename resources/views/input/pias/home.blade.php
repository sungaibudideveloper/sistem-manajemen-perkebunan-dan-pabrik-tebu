<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="w-full">
    {{-- Filter Bar --}}
    <form method="GET" class="mx-auto px-4 pt-4">
      <div class="flex justify-end items-end gap-2 flex-wrap">
        <div class="flex gap-2 items-end">
          {{-- Items per page --}}
          <div>
            <label for="perPage" class="text-sm font-medium text-gray-700">Items per page:</label>
            <input
              type="text"
              name="perPage"
              id="perPage"
              value="{{ $perPage ?? request('perPage', 10) }}"
              min="1"
              class="w-12 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              onchange="this.form.submit()"
            />
          </div>

          {{-- Date Filter Dropdown --}}
          <div class="relative inline-block text-left w-full max-w-xs px-0">
            <button
              type="button"
              class="inline-flex justify-center w-full items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
              id="menu-button"
              aria-expanded="false"
              aria-haspopup="true"
              onclick="toggleDropdown()"
            >
              {{-- icon --}}
              <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-4 w-4 mr-2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
              </svg>
              <span>Date Filter</span>
              <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <div class="absolute right-0 z-10 mt-1 w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden" id="menu-dropdown">
              <div class="py-1 px-4" role="menu" aria-orientation="vertical" aria-labelledby="menu-button">
                <div class="py-2">
                  <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                  <input
                    type="date"
                    id="start_date"
                    name="start_date"
                    value="{{ old('start_date', $startDate ?? request('start_date')) }}"
                    class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm {{ old('start_date', $startDate ?? request('start_date')) ? 'text-black' : 'text-gray-400' }}"
                    oninput="this.className = this.value ? 'mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                </div>
                <div class="py-2">
                  <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                  <input
                    type="date"
                    id="end_date"
                    name="end_date"
                    value="{{ old('end_date', $endDate ?? request('end_date')) }}"
                    class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm {{ old('end_date', $endDate ?? request('end_date')) ? 'text-black' : 'text-gray-400' }}"
                    oninput="this.className = this.value ? 'mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                </div>
                <div class="py-2">
                  <button
                    type="submit"
                    name="filter"
                    class="w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Apply
                  </button>
                </div>
              </div>
            </div>
          </div>

          {{-- Optional tombol reset filter --}}
          @if(request()->hasAny(['start_date','end_date']))
            <a href="{{ url()->current() }}" class="inline-flex items-center px-3 py-2 text-sm border rounded-md text-gray-600 hover:bg-gray-50">Reset</a>
          @endif
        </div>
      </div>
    </form>

    {{-- Tabel --}}
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
                <td class="py-2 px-4">
                  <a href="#" onclick="location.href='{{ url('input/pias/detail?rkhno='.$item->rkhno) }}'" target="_blank" class="text-blue-600 hover:underline">
                    {{ $item->rkhno }}  
                    @if($item->is_generated)
                    <span class="inline-block px-2 py-1 text-xs font-semibold text-white rounded-full bg-green-600">
                      Generated
                    </span> @endif
                  </a>
                </td>
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

    {{-- Pagination --}}
    <div class="px-4 py-2">
      {{-- links() sudah di-appends() dari controller, jadi query filter tetap nempel --}}
      {{ $data->links() }}
    </div>
  </div>

  {{-- Script kecil untuk UX --}}
  <script>
    // Hanya angka untuk perPage
    document.addEventListener("DOMContentLoaded", () => {
      const inputElement = document.getElementById("perPage");
      if (inputElement) {
        inputElement.addEventListener("input", (event) => {
          event.target.value = event.target.value.replace(/[^0-9]/g, '');
        });
      }
    });

    // Toggle dropdown
    function toggleDropdown() {
      const dropdown = document.getElementById('menu-dropdown');
      dropdown.classList.toggle('hidden');
    }

    // Klik di luar dropdown => tutup
    document.addEventListener("click", function(event) {
      const dropdown = document.getElementById("menu-dropdown");
      const button = document.getElementById("menu-button");
      if (!dropdown || !button) return;
      if (!dropdown.contains(event.target) && !button.contains(event.target)) {
        dropdown.classList.add("hidden");
      }
    });
  </script>
</x-layout>
