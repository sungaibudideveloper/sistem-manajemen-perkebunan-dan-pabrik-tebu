{{-- resources/views/partials/modals/modal-blok.blade.php --}}
<div
  x-show="open"
  x-cloak
  class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
  style="display: none;"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0"
  x-transition:enter-end="opacity-100"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="opacity-100"
  x-transition:leave-end="opacity-0"
>
  <div
    @click.away="open = false"
    class="bg-white rounded-lg shadow-2xl w-full max-w-md max-h-[85vh] flex flex-col overflow-hidden"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
  >
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50 text-center">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h1l1 2h10l1-2h1M6 10v10h2V10M16 10v10h2V10" />
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-900">Pilih Blok</h2>
        </div>
        <button @click="open = false" type="button"
          class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    {{-- Search Bar --}}
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input
          type="text"
          placeholder="Cari nama blok..."
          x-model="searchQuery"
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors duration-200"
        >
      </div>
    </div>

    {{-- Daftar Blok --}}
    <div class="flex-1 overflow-hidden">
      <div class="overflow-y-auto" style="max-height: 400px;">
        <table class="w-full">
          <thead class="bg-gray-100 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Blok</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template x-for="blok in filteredBloks" :key="blok.companycode + blok.blok">
              <tr @click="selectBlok(blok)"
                class="hover:bg-emerald-50 cursor-pointer transition-colors duration-150 group">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-center" x-text="blok.blok"></td>
              </tr>
            </template>
          </tbody>
        </table>

        {{-- Empty State --}}
        <template x-if="filteredBloks.length === 0">
          <div class="text-center py-12">
            <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada blok ditemukan</h3>
            <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci pencarian Anda.</p>
          </div>
        </template>
      </div>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
      <div class="flex justify-between items-center text-xs text-gray-500">
        <span x-text="`${filteredBloks.length} blok tersedia`"></span>
        <span>Klik untuk memilih</span>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function blokPicker() {
    return {
      open: false,
      searchQuery: '',
      bloks: @json($bloks ?? []),
      selected: { blok: '' },

      get filteredBloks() {
        if (!this.searchQuery) return this.bloks;
        const q = this.searchQuery.toUpperCase();
        return this.bloks.filter(b =>
          b.blok && b.blok.toUpperCase().includes(q)
        );
      },

      selectBlok(blok) {
        this.selected = blok;
        this.open = false;
      },
    };
  }
</script>
@endpush
