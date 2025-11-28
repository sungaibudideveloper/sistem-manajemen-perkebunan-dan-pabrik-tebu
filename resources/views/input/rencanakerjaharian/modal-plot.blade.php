{{-- Modal Plot --}}
<div x-show="open" x-cloak class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4">
  <div @click.away="open = false" class="bg-white rounded-lg shadow-2xl w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden">
    
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-sky-50">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-900">Pilih Plot</h2>
        </div>
        <button @click="open = false" type="button"
          class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 space-y-3">
      <!-- Search Input -->
      <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <input type="text" placeholder="Cari nama plot..." x-model="searchQuery"
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
      </div>

      <!-- ✅ Checkbox Filter Panen -->
      <label class="flex items-center space-x-2 cursor-pointer">
        <input type="checkbox" x-model="showOnPanenOnly"
          class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
        <span class="text-sm text-gray-700 font-medium">
          Plot yang sedang panen
        </span>
      </label>
    </div>

    <!-- Plot List -->
    <div class="flex-1 overflow-y-auto">
      <table class="w-full">
        <thead class="bg-gray-100 sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plot</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template x-for="item in filteredPlots" :key="item.companycode + item.plot + item.blok">
            <tr @click="selectPlot(item)" 
                class="hover:bg-sky-50 cursor-pointer transition-colors group">
              <!-- Plot Name -->
              <td class="px-4 py-3">
                <div class="flex items-center space-x-2">
                  <span class="text-sm font-semibold text-gray-900" x-text="item.plot"></span>
                  <span x-show="item.is_on_panen == 1" 
                        class="inline-block w-2 h-2 bg-yellow-400 rounded-full animate-pulse"
                        title="Sedang panen"></span>
                </div>
              </td>
              
              <!-- Lifecycle Status Badge -->
              <td class="px-4 py-3">
                <span x-show="item.lifecyclestatus"
                      class="inline-block px-2 py-0.5 text-xs font-semibold rounded"
                      :class="{
                        'bg-green-100 text-green-800': item.lifecyclestatus === 'PC',
                        'bg-blue-100 text-blue-800': item.lifecyclestatus === 'RC1',
                        'bg-yellow-100 text-yellow-800': item.lifecyclestatus === 'RC2',
                        'bg-purple-100 text-purple-800': item.lifecyclestatus === 'RC3',
                        'bg-gray-100 text-gray-600': !item.lifecyclestatus
                      }"
                      x-text="item.lifecyclestatus || '-'">
                </span>
              </td>
              
              <!-- Last Activity -->
              <td class="px-4 py-3">
                <div x-show="item.last_activitycode" class="text-xs">
                  <div class="font-medium text-gray-900" x-text="item.last_activitycode"></div>
                  <div class="text-gray-500 truncate max-w-[150px]" 
                       x-text="item.last_activityname"
                       :title="item.last_activityname"></div>
                </div>
                <span x-show="!item.last_activitycode" class="text-xs text-gray-400">Belum ada aktivitas</span>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- Empty State -->
      <template x-if="filteredPlots.length === 0">
        <div class="text-center py-12">
          <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <h3 class="text-sm font-medium text-gray-900">Tidak ada plot ditemukan</h3>
          <p class="mt-1 text-xs text-gray-500">Coba ubah filter atau blok yang dipilih</p>
        </div>
      </template>
    </div>

    <!-- Footer Info -->
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
      <div class="flex justify-between items-center text-xs text-gray-500">
        <span>
          <span class="font-semibold" x-text="filteredPlots.length"></span> plot tersedia
          <span x-show="showOnPanenOnly" class="text-yellow-600">(sedang panen)</span>
        </span>
        <span>Klik untuk memilih</span>
      </div>
    </div>
  </div>
</div>

@once
@push('scripts')
<script>
function plotPicker(rowIndex) {
  return {
    open: false,
    searchQuery: '',
    showOnPanenOnly: false,
    isBlokActivity: false,
    masterlist: window.masterlistData || [],
    selected: { plot: '' },
    rowIndex: rowIndex,

    get isBlokSelected() {
      return Alpine.store('blokPerRow').hasBlok(this.rowIndex);
    },

    get selectedBlok() {
      return Alpine.store('blokPerRow').getBlok(this.rowIndex);
    },

    get filteredPlots() {
      const blok = this.selectedBlok;
      const q = this.searchQuery.toUpperCase();
      
      if (!blok) return [];
      
      let filtered = this.masterlist.filter(item => item.blok === blok);
      
      if (this.showOnPanenOnly) {
        filtered = filtered.filter(item => item.is_on_panen == 1);
      }
      
      if (q) {
        filtered = filtered.filter(item => item.plot.toUpperCase().includes(q));
      }
      
      return filtered;
    },

    selectPlot(item) {
      if (!this.isBlokSelected) return;
      
      // ✅ FIX: Check duplicate SEBELUM select
      const currentActivity = Alpine.store('activityPerRow').getActivity(this.rowIndex);
      const currentBlok = this.selectedBlok;
      
      if (currentActivity && currentActivity.activitycode) {
        const isDuplicate = Alpine.store('uniqueCombinations').isDuplicate(
          this.rowIndex, 
          currentBlok, 
          item.plot, 
          currentActivity.activitycode
        );
        
        if (isDuplicate) {
          showToast('Kombinasi Blok + Plot + Activity sudah digunakan di baris lain. Silakan pilih plot lain.', 'error', 4000);
          return; // ❌ BATALKAN selection
        }
      }
      
      // ✅ Kalau tidak duplikat, baru select
      this.selected = item;
      this.open = false;
      
      window.dispatchEvent(new CustomEvent('plot-changed', {
        detail: {
          plotCode: item.plot,
          rowIndex: this.rowIndex
        }
      }));
    },

    init() {
      this.$watch('selectedBlok', (newBlok, oldBlok) => {
        if (newBlok !== oldBlok) {
          this.selected = { plot: '' };
          this.showOnPanenOnly = false;
        }
      });
      
      this.$watch(() => {
        const activityStore = Alpine.store('activityPerRow');
        const activity = activityStore.getActivity(this.rowIndex);
        return activity?.isblokactivity;
      }, (isBlok) => {
        this.isBlokActivity = isBlok === 1;
        if (this.isBlokActivity) {
          this.selected = { plot: '' };
        }
      });
    }
  }
}
</script>
@endpush
@endonce