{{-- resources/views/transaction/kerjaharian/rencanakerjaharian/modal-aktivitas.blade.php --}}

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
    class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    >
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-900">Pilih Aktivitas</h2>
        </div>
        <button 
          @click="closeModal()" 
          type="button" 
          class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200"
          >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        <input
          type="text"
          placeholder="Cari aktivitas atau grup..."
          x-model="searchQuery"
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
        >
      </div>
    </div>

    <!-- Breadcrumb -->
    <div
      x-show="selectedGroup"
      class="px-6 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between"
      >
      <nav class="flex items-center space-x-2 text-sm">
        <!-- Activity Groups -->
        <button 
          type="button"
          @click="backToGroups()"
          class="text-green-600 hover:text-green-800 hover:underline transition-colors duration-200"
          >
          Activity Groups
        </button>
        
        <!-- Arrow after Activity Groups -->
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
        
        <!-- Selected Group (clickable if we have subgroup selected) -->
        <template x-if="selectedSubGroup">
          <button 
            type="button" 
            @click="backToSubGroups()" 
            class="text-green-600 hover:text-green-800 hover:underline transition-colors duration-200"
            x-text="selectedGroup"
          ></button>
        </template>
        
        <!-- Selected Group (non-clickable if no subgroup selected) -->
        <template x-if="!selectedSubGroup">
          <span class="text-gray-700 font-medium" x-text="selectedGroup"></span>
        </template>
        
        <!-- Arrow and SubGroup (only show if subgroup is selected) -->
        <template x-if="selectedSubGroup">
          <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <span class="text-gray-700 font-medium" x-text="selectedSubGroup"></span>
          </div>
        </template>
      </nav>
    
      <!-- Back Button -->
      <div class="flex items-center space-x-2">
        <!-- Back to Sub Groups (when in activities view) -->
        <button 
          x-show="selectedSubGroup"
          type="button"
          @click="backToSubGroups()"
          class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
        >
          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
          Back to Sub Groups
        </button>
  
      <!-- Back to Groups (when in subgroups view) -->
      <button 
        x-show="selectedGroup && !selectedSubGroup"
        type="button"
        @click="backToGroups()"
        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
        >
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to Groups
      </button>
    </div>
  </div>
    
  <!-- Content Area -->
  <div class="flex-1 overflow-y-auto" style="max-height: 400px;">
      
    <!-- Activity Groups View -->
    <div x-show="!selectedGroup">
      <table class="w-full">
        <thead class="bg-gray-100 sticky top-0">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Kode Grup
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Nama Grup
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Jumlah Aktivitas
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template x-for="group in filteredGroups" :key="group.code">
            <tr class="hover:bg-green-50 transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-900" x-text="group.code"></span>
                </div>
              </td>
              <td class="px-6 py-4 text-base" x-text="group.groupName">
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="group.count + ' aktivitas'"></span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <button
                  type="button"
                  @click="selectGroup(group.code)"
                  class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                >
                  Lihat Aktivitas
                  <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      
      <!-- Empty State for Groups -->
      <template x-if="filteredGroups.length === 0">
        <div class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada grup aktivitas ditemukan</h3>
          <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci pencarian Anda.</p>
        </div>
      </template>
    </div>

    <!-- View B: Sub-Groups -->
    <div x-show="selectedGroup && !selectedSubGroup">
      <table class="w-full">
        <thead class="bg-gray-100 sticky top-0">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Kode Sub-Grup
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Nama Sub-Grup
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Jumlah
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template x-for="sg in filteredSubGroups" :key="sg.code">
            <tr @click="selectSubGroup(sg.code)" class="hover:bg-green-50 cursor-pointer transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-900" x-text="sg.code"></span>
                </div>
              </td>
              <td class="px-6 py-4 text-base" x-text="sg.activityname"></td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" x-text="sg.count + ' aktivitas'"></span>
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <!-- Empty State for Sub-Groups -->
      <template x-if="filteredSubGroups.length === 0">
        <div class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada sub-grup ditemukan</h3>
          <p class="mt-1 text-sm text-gray-500">
            <span x-show="searchQuery">Coba ubah kata kunci pencarian Anda.</span>
            <span x-show="!searchQuery">Tidak ada sub-grup tersedia untuk grup ini.</span>
          </p>
        </div>
      </template>
    </div>

    <!-- Activities View -->
    <div x-show="selectedSubGroup">
      <table class="w-full">
        <thead class="bg-gray-100 sticky top-0">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Kode Aktivitas
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Nama Aktivitas
            </th>
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template x-for="activity in filteredActivities" :key="activity.activitycode">
            <tr class="hover:bg-green-50 transition-colors duration-150">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-900" x-text="activity.activitycode"></span>
                </div>
              </td>
              <td class="px-6 py-4 text-base">
                <div class="text-sm text-gray-900" x-text="activity.activityname"></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-center">
                <button
                  type="button"
                  @click="selectActivity(activity)"
                  class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                >
                  Pilih
                  <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

  </div>

  <!-- Footer -->
  <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
    <div class="flex justify-between items-center text-xs text-gray-500">

      <!-- 1. Grup view -->
      <span
        x-show="!selectedGroup"
        x-text="`${filteredGroups.length} grup tersedia`"
      ></span>

      <!-- 2. Sub-group view -->
      <span
        x-show="selectedGroup && !selectedSubGroup"
        x-text="`${filteredSubGroups.length} sub-grup tersedia`"
      ></span>

      <!-- 3. Aktivitas view -->
      <span
        x-show="selectedSubGroup"
        x-text="`${filteredActivities.length} aktivitas tersedia`"
      ></span>

      <button
        type="button"
        @click.stop="clear()"
        class="text-red-500 hover:text-red-700 hover:underline text-sm font-medium"
      >
        Clear Selected Activity
      </button>
    </div>
  </div>
</div>

@push('scripts')
<script>
// ===== HANYA FUNCTION COMPONENT - TIDAK ADA DUPLIKASI =====
function activityPicker(rowIndex) {
  return {
    open: false,
    searchQuery: '',
    selectedGroup: '',
    selectedSubGroup: '',
    activities: window.activitiesData || [],
    selected: { 
      activitycode: '', 
      activityname: '',
      usingvehicle: null,
      jenistenagakerja: null 
    },
    rowIndex: rowIndex || 0,

    get activityGroups() {
      const groups = {}
      this.activities.forEach(a => {
        const code = a.activitygroup || 'Uncategorized'
        if (!groups[code]) {
          groups[code] = {
            code,
            groupName: a.groupname || code,
            activities: []
          }
        }
        groups[code].activities.push(a)
      })
      return Object.values(groups).map(g => ({
        code: g.code,
        groupName: g.groupName,
        activities: g.activities,
        count: g.activities.length
      }))
    },

    get subGroups() {
      if (!this.selectedGroup) return []
      const list = this.activities.filter(a => a.activitygroup === this.selectedGroup)
      const prefixes = [...new Set(list.map(a => a.activitycode.split('.').slice(0,3).join('.')))]
      const subs = prefixes.map(code => {
        const subList = list.filter(a =>
          a.activitycode === code ||
          a.activitycode.startsWith(code + '.')
        )
        const childCount = subList.filter(a => a.activitycode.startsWith(code + '.')).length
        return {
          code,
          activityname: subList[0].activityname,
          count: childCount
        }
      })
      subs.sort((a,b) => a.code.localeCompare(b.code, undefined, {numeric:true}))
      return subs
    },

    get filteredGroups() {
      const q = this.searchQuery.toUpperCase()
      return q
        ? this.activityGroups.filter(g =>
            g.code.toUpperCase().includes(q) ||
            g.groupName.toUpperCase().includes(q)
          )
        : this.activityGroups
    },

    get filteredSubGroups() {
      const q = this.searchQuery.toUpperCase()
      return this.subGroups.filter(sg =>
        sg.code.toUpperCase().includes(q) ||
        sg.activityname.toUpperCase().includes(q)
      )
    },

    get filteredActivities() {
      if (!this.selectedSubGroup) return []
      const q = this.searchQuery.toUpperCase()
      return this.activities
        .filter(a => a.activitycode.startsWith(this.selectedSubGroup + '.'))
        .filter(a =>
          !q ||
          a.activitycode.toUpperCase().includes(q) ||
          a.activityname.toUpperCase().includes(q)
        )
        .sort((a,b) => a.activitycode.localeCompare(b.activitycode, undefined, {numeric: true}))
    },

    selectGroup(code) {
      this.selectedGroup = code
      this.searchQuery = ''
    },

    selectSubGroup(code) {
      const matched = this.activities.filter(a => a.activitycode.startsWith(code))
      
      const children = this.activities.filter(a => a.activitycode.startsWith(code + '.'))
      
      if (children.length === 0 && matched.length > 0) {
        const exactMatch = matched.find(a => a.activitycode === code)
        return this.selectActivity(exactMatch || matched[0])
      }
      
      this.selectedSubGroup = code
      this.searchQuery = ''
    },

    backToGroups() {
      this.selectedGroup = ''
      this.selectedSubGroup = ''
      this.searchQuery = ''
    },

    backToSubGroups() {
      this.selectedSubGroup = ''
      this.searchQuery = ''
    },

    selectActivity(activity) {
      this.selected = {
        activitycode: activity.activitycode,
        activityname: activity.activityname,
        usingvehicle: activity.usingvehicle,
        jenistenagakerja: activity.jenistenagakerja,
        isblokactivity: activity.isblokactivity || 0  // ✅ TAMBAH INI
      };
      
      Alpine.store('activityPerRow').setActivity(this.rowIndex, this.selected);
      
      // Auto disable plot & set null kalau blok activity
      if (this.selected.isblokactivity === 1) {
        const plotPicker = document.querySelector(`[x-data*="plotPicker"][x-init*="rowIndex = ${this.rowIndex}"]`);
        if (plotPicker && plotPicker._x_dataStack && plotPicker._x_dataStack[0]) {
          plotPicker._x_dataStack[0].selected.plot = '';
          plotPicker._x_dataStack[0].isBlokActivity = true;
        }
        
        // Set hidden input plot = null
        const plotInput = document.querySelector(`input[name="rows[${this.rowIndex}][plot]"]`);
        if (plotInput) {
          plotInput.value = '';
          plotInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }
      
      this.updateJenisField();
      this.closeModal();
    },

    getActivityName(code) {
      const activity = this.activities.find(a => a.activitycode === code);
      return activity ? activity.activityname : '';
    },

    updateJenisField() {
      const jenisField = document.getElementById(`jenistenagakerja-${this.rowIndex}`);
      
      if (jenisField) {
        let jenisId = null;
        
        if (this.selected.jenistenagakerja) {
          if (typeof this.selected.jenistenagakerja === 'object' && this.selected.jenistenagakerja.idjenistenagakerja) {
            jenisId = this.selected.jenistenagakerja.idjenistenagakerja;
          } else if (typeof this.selected.jenistenagakerja === 'number') {
            jenisId = this.selected.jenistenagakerja;
          }
        }
        
        if (jenisId === 1) {
          jenisField.value = 'Harian';  
        } else if (jenisId === 2) {
          jenisField.value = 'Borongan';  
        } else if (jenisId === 3) {
          jenisField.value = 'Operator'; 
        } else if (jenisId === 4) {
          jenisField.value = 'Helper';
        } else {
          jenisField.value = '-';
        }
      }
    },

    closeModal() {
      this.open = false
      this.selectedGroup = ''
      this.selectedSubGroup = ''
      this.searchQuery = ''
    },

    clear() {
      console.log('🔴 Clearing activity and all related fields for row', this.rowIndex);
      
      // ✅ 1. Hapus dari worker card
      if (this.selected.activitycode) {
        const workerCardElement = document.querySelector('[x-data*="workerInfoCard"]');
        if (workerCardElement && workerCardElement._x_dataStack && workerCardElement._x_dataStack[0]) {
          const workerCard = workerCardElement._x_dataStack[0];
          
          if (workerCard.workers[this.selected.activitycode]) {
            delete workerCard.workers[this.selected.activitycode];
            console.log('✅ Removed worker card for activity:', this.selected.activitycode);
          }
        }

        // ✅ 2. Hapus dari kendaraan card (FIXED: kendaraan bukan kendaraans)
        const kendaraanCardElement = document.querySelector('[x-data*="kendaraanInfoCard"]');
        if (kendaraanCardElement && kendaraanCardElement._x_dataStack && kendaraanCardElement._x_dataStack[0]) {
          const kendaraanCard = kendaraanCardElement._x_dataStack[0];
          
          if (kendaraanCard.kendaraan[this.selected.activitycode]) {
            delete kendaraanCard.kendaraan[this.selected.activitycode];
            console.log('✅ Removed kendaraan card for activity:', this.selected.activitycode);
          }
        }
        
        // Hapus dari Alpine store
        Alpine.store('activityPerRow').setActivity(this.rowIndex, null);
      }
      
      // ✅ 3. Clear activity data (internal state)
      this.selected = { 
        activitycode: '', 
        activityname: '', 
        usingvehicle: null, 
        jenistenagakerja: null 
      };
      
      // ✅ 4. Clear hidden input field nama (activitycode) - INI YANG PENTING!
      const activityInput = document.querySelector(`input[name="rows[${this.rowIndex}][nama]"]`);
      if (activityInput) {
        activityInput.value = '';
        // Trigger Alpine reactivity
        activityInput.dispatchEvent(new Event('input', { bubbles: true }));
      }
      
      // ✅ 5. Clear BLOK
      const blokComponent = this.$el.closest('tr').querySelector('[x-data*="blokPicker"]');
      if (blokComponent && blokComponent._x_dataStack && blokComponent._x_dataStack[0]) {
        blokComponent._x_dataStack[0].selected = { blok: '', id: '' };
        Alpine.store('blokPerRow').setBlok(this.rowIndex, '');
      }
      
      // ✅ 6. Clear PLOT
      const plotComponent = this.$el.closest('tr').querySelector('[x-data*="plotPicker"]');
      if (plotComponent && plotComponent._x_dataStack && plotComponent._x_dataStack[0]) {
        plotComponent._x_dataStack[0].selected = { plot: '' };
      }
      
      // ✅ 7. Clear LUAS
      const luasInput = document.querySelector(`input[name="rows[${this.rowIndex}][luas]"]`);
      if (luasInput) luasInput.value = '';
      
      // ✅ 8. Clear MATERIAL
      const materialComponent = this.$el.closest('tr').querySelector('[x-data*="materialPicker"]');
      if (materialComponent && materialComponent._x_dataStack && materialComponent._x_dataStack[0]) {
        const materialData = materialComponent._x_dataStack[0];
        materialData.currentActivityCode = '';
        materialData.selectedGroup = null;
        materialData.updateHiddenInputs();
      }
      
      // ✅ 9. Clear PANEN INFO (if any)
      const panenComponent = this.$el.closest('tr').querySelector('[x-data*="panenInfoPicker"]');
      if (panenComponent && panenComponent._x_dataStack && panenComponent._x_dataStack[0]) {
        panenComponent._x_dataStack[0].resetPanenInfo();
      }
      
      // ✅ 10. Clear unique combination store
      Alpine.store('uniqueCombinations').setCombination(this.rowIndex, '', '', '');
      
      console.log('✅ All fields cleared for row', this.rowIndex);
      
      // Show toast notification
      showToast('Activity dan semua data terkait berhasil dihapus', 'success', 2000);
      
      this.closeModal();
    }
  }
}

// ===== INITIALIZATION FOR EDIT MODE =====
document.addEventListener('DOMContentLoaded', function() {
  const activityInputs = document.querySelectorAll('input[name*="[nama]"]');
  activityInputs.forEach((input, index) => {
    if (input.value && window.activitiesData) {
      const activity = window.activitiesData.find(a => a.activitycode === input.value);
      if (activity) {
        const jenisInput = document.getElementById(`jenistenagakerja-${index}`);
        if (jenisInput) {
          if (activity.jenistenagakerja === 1) {
            jenisInput.value = 'Harian';  
          } else if (activity.jenistenagakerja === 2) {
            jenisInput.value = 'Borongan';  
          } else if (activity.jenistenagakerja === 3) {
            jenisInput.value = 'Operator'; 
          } else if (activity.jenistenagakerja === 4) {
            jenisInput.value = 'Helper';
          }
        }
      }
    }
  });
});
</script>
@endpush