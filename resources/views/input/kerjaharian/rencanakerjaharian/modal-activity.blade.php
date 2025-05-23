{{-- resources/views/input/kerjaharian/rencanakerjaharian/modal-aktivitas.blade.php --}}
                {{-- MODAL AKTIVITAS --}}
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
        class="bg-white rounded-lg shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden"
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
        <div class="px-6 py-2 bg-gray-50 border-b border-gray-200" x-show="selectedGroup">
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
        </div>
        
        <!-- Content Area -->
        <div class="flex-1 overflow-hidden">
          <div class="overflow-y-auto" style="max-height: 400px;">
            
            <!-- Activity Groups View -->
            <div x-show="!selectedGroup">
              <table class="w-full">
                <thead class="bg-gray-100 sticky top-0">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Grup</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Grup</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Aktivitas</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
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

                    <td class="px-6 py-4 text-base"
    x-text="group.groupName">
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


<!-- View B: Sub‐Groups -->
<div x-show="selectedGroup && !selectedSubGroup">
  <table class="w-full">
    <thead class="bg-gray-100 sticky top-0">
      <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Sub-Grup</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Sub-Grup</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah</th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
      <!-- PERBAIKAN: Gunakan filteredSubGroups yang konsisten -->
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
      <!-- HAPUS template empty state dari dalam tbody -->
    </tbody>
  </table>
  
  <!-- Empty State for Sub-Groups - PINDAH KE LUAR TABLE -->
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Aktivitas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Aktivitas</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
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
              </table>
              
            
            </div>
            
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
          <div class="flex justify-between items-center text-xs text-gray-500">
            <span x-show="!selectedGroup" x-text="`${filteredGroups.length} grup tersedia`"></span>
            <span x-show="selectedGroup" x-text="`${filteredActivities.length} aktivitas tersedia`"></span>
            <span>Klik untuk memilih</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>

@push('scripts')
<script>
  function activityPicker() {
    return {
      open: false,
      searchQuery: '',
      selectedGroup: '',
      selectedSubGroup: '',
      activities: @json($activities ?? []),
      selected: { activitycode: '', activityname: '' },

      get activityGroups() {
        const groups = {}

        this.activities.forEach(activity => {
          const code = activity.activitygroup || 'Uncategorized'
          if (!groups[code]) {
            groups[code] = {
              code,
              groupName: activity.group?.groupname || code,
              activities: []
            }
          }
          groups[code].activities.push(activity)
        })

        // Convert object ke array, plus hitung count-nya di sini
        return Object.values(groups).map(g => ({
          code:      g.code,
          groupName: g.groupName,
          activities:g.activities,
          count:     g.activities.length   // <-- tambahkan ini
        }))
      },

      get subGroups() {
  if (!this.selectedGroup) return [];

  // ambil semua aktivitas di grup terpilih
  const list = this.activities.filter(a => a.activitygroup === this.selectedGroup);

  // cari semua prefix 3‐level unik
  const prefixes = [...new Set(
    list.map(a => a.activitycode.split('.').slice(0,3).join('.'))
  )];

  // bangun array sub‐grup
  const subs = prefixes.map(code => {
    // exact match atau direct child (kode + dot)
    const subList = list.filter(a =>
      a.activitycode === code ||
      a.activitycode.startsWith(code + '.')
    );

    const childOnlyCount = subList.filter(a =>
  a.activitycode.startsWith(code + '.')
).length;

    return {
      code,
      activityname: subList[0].activityname,
      count: childOnlyCount
    };
  });

  // (opsional) urutkan numerik
  subs.sort((a, b) => {
    const [, majA, minA] = a.code.split('.');
    const [, majB, minB] = b.code.split('.');
    if (+majA !== +majB) return +majA - +majB;
    return +minA - +minB;
  });

  return subs;
},


      get filteredGroups() {
        const q = this.searchQuery.toUpperCase();
        return this.searchQuery
          ? this.activityGroups.filter(g => g.code.toUpperCase().includes(q)|| (g.groupName && g.groupName.toUpperCase().includes(q)))
          : this.activityGroups;
      },

      get filteredSubGroups() {
  const q = this.searchQuery.toUpperCase();
  // kalau belum ada search, tampilkan semua subGroups
  if (!this.searchQuery) return this.subGroups;
  return this.subGroups.filter(sg =>
    sg.code.toUpperCase().includes(q) ||
    (sg.activityname && sg.activityname.toUpperCase().includes(q))
  );
},

      get filteredActivities() {
        if (!this.selectedSubGroup) return [];

        const list = this.activities
          .filter(a => a.activitycode.startsWith(this.selectedSubGroup+'.'))
          .filter(a => {
            const q = this.searchQuery.toUpperCase();
            return !q
              || a.activitycode.toUpperCase().includes(q)
              || a.activityname.toUpperCase().includes(q);
          });

        // sort string secara “numeric aware”
        list.sort((a, b) =>
          a.activitycode.localeCompare(b.activitycode, undefined, { numeric: true })
        );

        return list;
      },

      selectGroup(code) {
        this.selectedGroup = code;
        this.searchQuery = '';
      },
      
      selectSubGroup(code) {
        // cari semua aktivitas yang kodenya diawali prefix ini
        const matched = this.activities.filter(a => a.activitycode.startsWith(code));

        // jika cuma ada satu, dan itu exact match (leaf), langsung pilih
        if (
          matched.length === 1 &&
          matched[0].activitycode === code
        ) {
          this.selectActivity(matched[0]);
        } else {
          // kalau bukan leaf, lanjut ke view sub-grup
          this.selectedSubGroup = code;
          this.searchQuery = '';
        }
      },
      
      backToGroups() {
        this.selectedGroup = '';
        this.selectedSubGroup = '';
        this.searchQuery = '';
      },
      
      backToSubGroups() {
        this.selectedSubGroup = '';
        this.searchQuery = '';
      },

      selectActivity(activity) {
        this.selected = activity;
        this.closeModal();
      },

      closeModal() {
        this.open = false;
        this.selectedGroup = '';
        this.selectedSubGroup = '';
        this.searchQuery = '';
      }
    }
  }
</script>
@endpush