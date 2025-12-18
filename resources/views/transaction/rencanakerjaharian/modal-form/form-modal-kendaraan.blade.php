{{--resources\views\input\rencanakerjaharian\modal-kendaraan.blade.php--}}
<div
  x-data="kendaraanModalComponent()"
  x-show="open"
  x-cloak
  @open-kendaraan-modal.window="handleOpen($event.detail)"
  class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
>
  <div
    @click.away="open = false"
    class="bg-white rounded-lg shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden"
  >
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-900">Pilih Kendaraan & Operator</h2>
        </div>
        <button @click="closeModal()" type="button"
          class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <p class="text-sm text-gray-600 mt-1">Pilih kendaraan (dengan operator) untuk assignment</p>
    </div>

    {{-- Activity Selector & Helper Checkbox --}}
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <div class="flex items-center gap-4">
        <!-- Activity Selector -->
        <div class="flex-1">
          <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Aktivitas</label>
          <select
            x-model="selectedActivityCode"
            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
          >
            <option value="">-- Pilih Aktivitas --</option>
            <template x-for="(activityCode, index) in uniqueActivityCodes" :key="`activity-option-${activityCode}-${index}`">
              <option :value="activityCode" x-text="getActivityLabel(activityCode)"></option>
            </template>
          </select>
        </div>

        <!-- Helper Checkbox -->
        <div class="flex items-center pt-5">
          <input
            type="checkbox"
            id="useHelper"
            x-model="useHelper"
            class="h-4 w-4 text-gray-600 focus:ring-gray-500 border-gray-300 rounded"
          >
          <label for="useHelper" class="ml-2 text-sm font-medium text-gray-700">
            Gunakan Helper
          </label>
        </div>
      </div>
    </div>

    {{-- Search Bar --}}
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <div class="grid grid-cols-1 gap-4" :class="useHelper ? 'md:grid-cols-2' : ''">
        <!-- Search Vehicle -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            placeholder="Cari kendaraan (nomor, jenis, operator)..."
            x-model="searchVehicleQuery"
            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
          >
        </div>

        <!-- Search Helper -->
        <div x-show="useHelper" class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            placeholder="Cari helper (nama, ID)..."
            x-model="searchHelperQuery"
            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500"
          >
        </div>
      </div>
    </div>

    {{-- Content Area --}}
    <div class="flex-1 overflow-y-auto max-h-[400px]">
      <div class="grid" :class="useHelper ? 'md:grid-cols-2' : 'grid-cols-1'">

        {{-- Vehicle List (LEFT) --}}
        <div class="border-r border-gray-200">
          <div class="px-4 py-2 bg-green-100 border-b">
            <h3 class="text-sm font-semibold text-green-800">Pilih Kendaraan</h3>
          </div>
          <div class="overflow-y-auto max-h-[350px]">
            <table class="w-full">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Kendaraan</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Operator</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <template x-for="(vehicle, vIndex) in filteredVehicles" :key="`vehicle-${vehicle.nokendaraan}-${vIndex}`">
                  <tr
                    @click="selectVehicle(vehicle)"
                    class="cursor-pointer transition-colors duration-150"
                    :class="{
                      'bg-green-100 hover:bg-green-150': selectedVehicle && selectedVehicle.nokendaraan === vehicle.nokendaraan,
                      'hover:bg-green-50': !selectedVehicle || selectedVehicle.nokendaraan !== vehicle.nokendaraan,
                      'opacity-50 cursor-not-allowed': isVehicleDisabled(vehicle)
                    }"
                  >
                    <td class="px-3 py-3 whitespace-nowrap">
                      <span class="text-sm font-medium text-gray-900" x-text="vehicle.nokendaraan"></span>
                      <template x-if="isVehicleDisabled(vehicle)">
                        <span class="ml-1 text-xs text-red-600">(Sudah dipilih)</span>
                      </template>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <div class="text-xs text-gray-700" x-text="vehicle.vehicle_type || '-'"></div>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <div class="text-sm">
                        <div class="font-medium text-gray-900" x-text="vehicle.operator_name || 'No Operator'"></div>
                        <div class="text-xs text-gray-500" x-text="vehicle.operator_id || '-'"></div>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>

            {{-- Empty State Vehicle --}}
            <template x-if="filteredVehicles.length === 0">
              <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Tidak ada kendaraan ditemukan</p>
              </div>
            </template>
          </div>
        </div>

        {{-- Helper List (RIGHT) --}}
        <div x-show="useHelper">
          <div class="px-4 py-2 bg-gray-200 border-b">
            <h3 class="text-sm font-semibold text-gray-800">Pilih Helper</h3>
          </div>
          <div class="overflow-y-auto max-h-[350px]">
            <table class="w-full">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <template x-for="(helper, hIndex) in filteredHelpers" :key="`helper-${helper.tenagakerjaid}-${hIndex}`">
                  <tr
                    @click="selectHelper(helper)"
                    class="hover:bg-gray-100 cursor-pointer transition-colors duration-150"
                    :class="selectedHelper && selectedHelper.tenagakerjaid === helper.tenagakerjaid ? 'bg-gray-200' : ''"
                  >
                    <td class="px-3 py-3 whitespace-nowrap">
                      <span class="text-sm font-medium text-gray-900" x-text="helper.tenagakerjaid"></span>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <div class="text-sm text-gray-900" x-text="helper.nama"></div>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <span class="text-sm text-gray-700" x-text="helper.nik || '-'"></span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>

            {{-- Empty State Helper --}}
            <template x-if="filteredHelpers.length === 0">
              <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Tidak ada helper ditemukan</p>
              </div>
            </template>
          </div>
        </div>

      </div>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
      <div class="flex justify-between items-center">
        <div class="text-xs text-gray-500">
          <div>
            <span x-text="`${filteredVehicles.length} kendaraan tersedia`"></span>
            <template x-if="useHelper">
              <span> • <span x-text="`${filteredHelpers.length} helper tersedia`"></span></span>
            </template>
          </div>
          <div class="mt-1">
            <span x-show="selectedActivityCode" class="text-green-700 font-medium">
              Aktivitas: <span x-text="selectedActivityCode"></span>
            </span>
            <span x-show="selectedVehicle" class="text-green-600 ml-3">
              Kendaraan: <span x-text="selectedVehicle ? selectedVehicle.nokendaraan : ''"></span>
              <template x-if="selectedVehicle && selectedVehicle.operator_name">
                <span class="text-gray-600"> (Op: <span x-text="selectedVehicle.operator_name"></span>)</span>
              </template>
            </span>
            <template x-if="useHelper && selectedHelper">
              <span class="text-gray-700 ml-3">
                Helper: <span x-text="selectedHelper.nama"></span>
              </span>
            </template>
          </div>
        </div>
        <div class="flex space-x-3">
          <button
            type="button"
            @click="clearSelection()"
            class="px-4 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
          >
            Clear
          </button>
          <button
            type="button"
            @click="confirmSelection()"
            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            :disabled="!canConfirm"
            :class="!canConfirm ? 'opacity-50 cursor-not-allowed' : ''"
          >
            Tambah Kendaraan
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
/**
 * ✅ FIXED: Kendaraan Modal Component with Proper Reactivity
 */
function kendaraanModalComponent() {
  return {
    open: false,
    availableActivityCodes: [], // ← Keep original array
    selectedActivityCode: '',
    searchVehicleQuery: '',
    searchHelperQuery: '',
    selectedVehicle: null,
    selectedHelper: null,
    useHelper: false,
    
    _vehiclesCache: null,
    _helpersCache: null,
    _kendaraanCardCache: null,

    init() {
      this._vehiclesCache = window.vehiclesData || [];
      this._helpersCache = window.helpersData || [];
      
      
      this.$watch('selectedActivityCode', (newVal) => {
        if (newVal) {
          console.log('📋 Activity changed to:', newVal);
          this.refreshVehicleStates();
        }
      });
    },

    // ✅ NEW: Get unique activity codes
    get uniqueActivityCodes() {
      if (!this.availableActivityCodes || this.availableActivityCodes.length === 0) {
        return [];
      }
      
      // Remove duplicates using Set
      const unique = [...new Set(this.availableActivityCodes)];
      console.log('📋 Unique activities:', unique);
      return unique;
    },

    // ✅ NEW: Get activity label with count
    getActivityLabel(activityCode) {
      const count = this.availableActivityCodes.filter(code => code === activityCode).length;
      
      // Get activity name from global data
      const activity = window.activitiesData?.find(a => a.activitycode === activityCode);
      const activityName = activity ? activity.activityname : '';
      
      if (count > 1) {
        return `${activityCode} - ${activityName} (${count} plot)`;
      }
      
      return `${activityCode} - ${activityName}`;
    },

    refreshVehicleStates() {
      this.$nextTick(() => {
        const kendaraanCardElement = document.querySelector('[x-data*="kendaraanInfoCard"]');
        if (kendaraanCardElement && kendaraanCardElement._x_dataStack && kendaraanCardElement._x_dataStack[0]) {
          this._kendaraanCardCache = kendaraanCardElement._x_dataStack[0];
        } else {
          console.warn('⚠️ Kendaraan card not found');
        }
      });
    },

    get canConfirm() {
      return this.selectedActivityCode && 
             this.selectedVehicle && 
             !this.isVehicleDisabled(this.selectedVehicle);
    },

    get availableVehicles() {
      return this._vehiclesCache || [];
    },

    get availableHelpers() {
      return this._helpersCache || [];
    },

    get filteredVehicles() {
      const vehicles = this.availableVehicles;
      
      if (!this.searchVehicleQuery) {
        return vehicles;
      }
      
      const q = this.searchVehicleQuery.toString().toUpperCase();
      return vehicles.filter(v => {
        const nokendaraan = (v.nokendaraan || '').toString().toUpperCase();
        const vehicleType = (v.vehicle_type || '').toString().toUpperCase();
        const operatorName = (v.operator_name || '').toString().toUpperCase();
        const operatorId = (v.operator_id || '').toString().toUpperCase();
        
        return nokendaraan.includes(q) ||
               vehicleType.includes(q) ||
               operatorName.includes(q) ||
               operatorId.includes(q);
      });
    },

    get filteredHelpers() {
      const helpers = this.availableHelpers;
      
      if (!this.searchHelperQuery) {
        return helpers;
      }
      
      const q = this.searchHelperQuery.toString().toUpperCase();
      return helpers.filter(helper => {
        const nama = (helper.nama || '').toString().toUpperCase();
        const id = (helper.tenagakerjaid || '').toString().toUpperCase();
        const nik = (helper.nik || '').toString().toUpperCase();
        
        return nama.includes(q) || id.includes(q) || nik.includes(q);
      });
    },

    isVehicleDisabled(vehicle) {
      if (!this.selectedActivityCode || !vehicle) return false;
      
      let kendaraanCard = this._kendaraanCardCache;
      
      if (!kendaraanCard) {
        const kendaraanCardElement = document.querySelector('[x-data*="kendaraanInfoCard"]');
        if (kendaraanCardElement && kendaraanCardElement._x_dataStack && kendaraanCardElement._x_dataStack[0]) {
          kendaraanCard = kendaraanCardElement._x_dataStack[0];
          this._kendaraanCardCache = kendaraanCard;
        }
      }
      
      if (!kendaraanCard) return false;

      const activityKendaraan = kendaraanCard.kendaraan[this.selectedActivityCode];
      if (!activityKendaraan) return false;

      return Object.values(activityKendaraan).some(
        item => item.nokendaraan === vehicle.nokendaraan
      );
    },

    handleOpen(detail) {
      
      if (!detail || !detail.activityCodes || detail.activityCodes.length === 0) {
        showToast('Tidak ada aktivitas yang menggunakan kendaraan', 'warning', 3000);
        return;
      }

      this._vehiclesCache = window.vehiclesData || [];
      this._helpersCache = window.helpersData || [];
      this._kendaraanCardCache = null;

      // ✅ Store original array (dengan duplicates untuk counting)
      this.availableActivityCodes = detail.activityCodes;
      
      // ✅ Set first unique activity as selected
      const uniqueCodes = [...new Set(detail.activityCodes)];
      this.selectedActivityCode = uniqueCodes[0];
      
      this.$nextTick(() => {
        this.refreshVehicleStates();
        this.open = true;
      });
    },

    selectVehicle(vehicle) {
      if (this.isVehicleDisabled(vehicle)) {
        showToast(`Kendaraan ${vehicle.nokendaraan} sudah dipilih untuk aktivitas ini`, 'warning', 3000);
        return;
      }

      this.selectedVehicle = {
        nokendaraan: vehicle.nokendaraan,
        vehicle_type: vehicle.vehicle_type,
        operator_id: vehicle.operator_id,
        operator_name: vehicle.operator_name,
        operator_nik: vehicle.operator_nik
      };
    },

    selectHelper(helper) {
      this.selectedHelper = {
        tenagakerjaid: helper.tenagakerjaid,
        nama: helper.nama,
        nik: helper.nik
      };
    },

    confirmSelection() {
      if (!this.canConfirm) {
        showToast('Pilih aktivitas dan kendaraan terlebih dahulu', 'warning', 2000);
        return;
      }
      
      const kendaraanCardElement = document.querySelector('[x-data*="kendaraanInfoCard"]');
      if (!kendaraanCardElement || !kendaraanCardElement._x_dataStack || !kendaraanCardElement._x_dataStack[0]) {
        showToast('Error: Kendaraan card tidak ditemukan', 'error', 3000);
        console.error('❌ Kendaraan card element not found');
        return;
      }

      const kendaraanCard = kendaraanCardElement._x_dataStack[0];
      
      const success = kendaraanCard.addKendaraan(
        this.selectedActivityCode,
        this.selectedVehicle,
        this.useHelper ? this.selectedHelper : null
      );

      if (success) {
        showToast('Kendaraan berhasil ditambahkan', 'success', 2000);
        console.log('✅ Vehicle added successfully');
        
        this.clearSelection();
        this.$nextTick(() => {
          this.refreshVehicleStates();
        });
      } else {
        console.error('❌ Failed to add vehicle');
      }
    },

    clearSelection() {
      this.selectedVehicle = null;
      this.selectedHelper = null;
      this.useHelper = false;
      this.searchVehicleQuery = '';
      this.searchHelperQuery = '';
    },

    closeModal() {
      this.open = false;
      this.clearSelection();
      this.selectedActivityCode = '';
      this.availableActivityCodes = [];
      this._kendaraanCardCache = null;
    }
  };
}

window.debugKendaraanModal = function() {
  console.log('=== KENDARAAN MODAL DEBUG ===');
  console.log('vehiclesData:', window.vehiclesData?.length || 0, window.vehiclesData);
  console.log('helpersData:', window.helpersData?.length || 0, window.helpersData);
  
  const kendaraanCardElement = document.querySelector('[x-data*="kendaraanInfoCard"]');
  if (kendaraanCardElement && kendaraanCardElement._x_dataStack && kendaraanCardElement._x_dataStack[0]) {
    console.log('Kendaraan Card State:', kendaraanCardElement._x_dataStack[0].kendaraan);
  } else {
    console.warn('Kendaraan card not found');
  }
  
  const modalElement = document.querySelector('[x-data*="kendaraanModalComponent"]');
  if (modalElement && modalElement._x_dataStack && modalElement._x_dataStack[0]) {
    console.log('Modal State:', {
      open: modalElement._x_dataStack[0].open,
      selectedActivityCode: modalElement._x_dataStack[0].selectedActivityCode,
      availableActivityCodes: modalElement._x_dataStack[0].availableActivityCodes,
      uniqueActivityCodes: modalElement._x_dataStack[0].uniqueActivityCodes
    });
  } else {
    console.warn('Modal component not found');
  }
  console.log('============================');
};
</script>