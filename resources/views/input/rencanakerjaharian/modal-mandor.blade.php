{{--resources\views\input\rencanakerjaharian\modal-mandor.blade.php--}}
<div
  x-show="open"
  x-cloak
  class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
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
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-900">Pilih Mandor</h2>
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
          placeholder="Cari nama atau ID mandor..."
          x-model="searchQuery"
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
        >
      </div>
    </div>

    {{-- Mandor List --}}
    <div class="flex-1 overflow-y-auto max-h-[400px]">
      <table class="w-full">
        <thead class="bg-gray-100 sticky top-0">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mandor</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template x-for="mandor in filteredMandors" :key="mandor.companycode + mandor.userid">
            <tr @click="selectMandor(mandor)"
              class="hover:bg-blue-50 cursor-pointer transition-colors duration-150 group">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-medium text-gray-900" x-text="mandor.userid"></span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900 font-medium" x-text="mandor.name"></div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      {{-- Empty State --}}
      <template x-if="filteredMandors.length === 0">
        <div class="text-center py-12">
          <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada mandor ditemukan</h3>
          <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci pencarian Anda.</p>
        </div>
      </template>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
      <div class="flex justify-between items-center text-xs text-gray-500">
        <span x-text="${filteredMandors.length} mandor tersedia"></span>
<button
type="button"
@click.stop="clear()"
class="text-red-500 hover:text-red-700 hover:underline text-sm font-medium"
>
Clear Selected Mandor
</button>
</div>
</div>
  </div>
</div>
@push('scripts')
<script>
function mandorPicker() {
  return {
    open: false,
    searchQuery: '',
    mandors: @json($mandors ?? []),
    selected: { companycode: '', userid: '', name: '' },
    isMandorUser: false, // ✅ NEW: Track if current user is Mandor

    // ✅ NEW: Check if logged-in user is a Mandor
    checkIfUserIsMandor() {
      // idjabatan = 5 means Mandor
      if (window.currentUser.idjabatan === 5) {
        this.isMandorUser = true;
        
        // Auto-select current user as mandor
        const currentUserAsMandor = this.mandors.find(m => m.userid === window.currentUser.userid);
        
        if (currentUserAsMandor) {
          this.selected = {
            companycode: currentUserAsMandor.companycode,
            userid: currentUserAsMandor.userid,
            name: currentUserAsMandor.name
          };
          
          // Update absen summary immediately
          updateAbsenSummary(currentUserAsMandor.userid, currentUserAsMandor.userid, currentUserAsMandor.name);
        } else {
          // Fallback: create from current user data
          this.selected = {
            companycode: '',
            userid: window.currentUser.userid,
            name: window.currentUser.name
          };
          
          updateAbsenSummary(window.currentUser.userid, window.currentUser.userid, window.currentUser.name);
        }
      }
    },

    get filteredMandors() {
      if (!this.searchQuery) return this.mandors;
      const q = this.searchQuery.toString().toUpperCase();
      return this.mandors.filter(m =>
        m.name.toUpperCase().includes(q) ||
        m.userid.toString().toUpperCase().includes(q)
      );
    },

    selectMandor(mandor) {
      // ✅ Prevent selection if user is Mandor (should not happen, but safety check)
      if (this.isMandorUser) {
        return;
      }

      this.selected = {
        companycode: mandor.companycode,
        userid: mandor.userid,
        name: mandor.name
      };
      this.open = false;
      updateAbsenSummary(mandor.userid, mandor.userid, mandor.name);
    },

    clear() {
      // ✅ Prevent clearing if user is Mandor
      if (this.isMandorUser) {
        return;
      }

      this.selected = { companycode: '', userid: '', name: '' };
      this.searchQuery = '';
      updateAbsenSummary(null);
      this.open = false;
    }
  }
}
</script>
@endpush