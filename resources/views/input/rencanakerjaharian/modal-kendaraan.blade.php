{{--resources\views\input\rencanakerjaharian\modal-kendaraan.blade.php--}}
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
    class="bg-white rounded-lg shadow-2xl w-full max-w-5xl max-h-[85vh] flex flex-col overflow-hidden"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
  >
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <h2 class="text-lg font-semibold text-gray-900">Pilih Operator & Unit Alat</h2>
        </div>
        <button @click="open = false" type="button"
          class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <p class="text-sm text-gray-600 mt-1">Pilih operator untuk aktivitas yang memerlukan unit alat</p>
    </div>

    {{-- Helper Checkbox Section --}}
    <div class="px-6 py-4 bg-purple-50 border-b border-purple-200">
      <div class="flex items-center space-x-4">
        <div class="flex items-center">
          <input
            type="checkbox"
            id="useHelper"
            x-model="useHelper"
            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
          >
          <label for="useHelper" class="ml-2 text-sm font-medium text-gray-700">
            Gunakan Helper
          </label>
        </div>
        <div class="text-xs text-gray-500">
          (Opsional: Pilih helper untuk membantu operator)
        </div>
      </div>
    </div>

    {{-- Search Bar --}}
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
      <div class="grid grid-cols-1 gap-4" :class="useHelper ? 'md:grid-cols-2' : ''">
        <!-- Search Operator -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            placeholder="Cari operator (nama, ID, nomor kendaraan)..."
            x-model="searchOperatorQuery"
            class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
          >
        </div>

        <!-- Search Helper (jika useHelper aktif) -->
        <div x-show="useHelper" class="relative">
          <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            type="text"
            placeholder="Cari helper (nama, ID)..."
            x-model="searchHelperQuery"
            class="w-full pl-10 pr-4 py-2.5 text-sm border border-purple-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors duration-200"
          >
        </div>
      </div>
    </div>

    {{-- Content Area --}}
    <div class="flex-1 overflow-y-auto max-h-[400px]">
      <div class="grid" :class="useHelper ? 'md:grid-cols-2' : 'grid-cols-1'">

        {{-- Operator List --}}
        <div class="border-r border-gray-200">
          <div class="px-4 py-2 bg-green-100 border-b">
            <h3 class="text-sm font-semibold text-green-800">Pilih Operator & Kendaraan</h3>
          </div>
          <div class="overflow-y-auto max-h-[350px]">
            <table class="w-full">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kendaraan</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <template x-for="operator in filteredOperators" :key="operator.tenagakerjaid">
                  <tr
                    @click="selectOperator(operator)"
                    class="hover:bg-green-50 cursor-pointer transition-colors duration-150"
                    :class="selectedOperator && selectedOperator.tenagakerjaid === operator.tenagakerjaid ? 'bg-green-100' : ''"
                  >
                    <td class="px-3 py-3 whitespace-nowrap">
                      <span class="text-sm font-medium text-gray-900" x-text="operator.tenagakerjaid"></span>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <div class="text-sm text-gray-900" x-text="operator.nama"></div>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <div class="text-xs">
                        <div class="font-medium" x-text="operator.nokendaraan"></div>
                        <div class="text-gray-500" x-text="operator.jenis"></div>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>

            {{-- Empty State Operator --}}
            <template x-if="filteredOperators.length === 0">
              <div class="text-center py-8">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <p class="mt-2 text-sm text-gray-500">Tidak ada operator ditemukan</p>
              </div>
            </template>
          </div>
        </div>

        {{-- Helper List (jika useHelper aktif) --}}
        <div x-show="useHelper">
          <div class="px-4 py-2 bg-purple-100 border-b">
            <h3 class="text-sm font-semibold text-purple-800">Pilih Helper</h3>
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
                <template x-for="helper in filteredHelpers" :key="helper.tenagakerjaid">
                  <tr
                    @click="selectHelper(helper)"
                    class="hover:bg-purple-50 cursor-pointer transition-colors duration-150"
                    :class="selectedHelper && selectedHelper.tenagakerjaid === helper.tenagakerjaid ? 'bg-purple-100' : ''"
                  >
                    <td class="px-3 py-3 whitespace-nowrap">
                      <span class="text-sm font-medium text-gray-900" x-text="helper.tenagakerjaid"></span>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <div class="text-sm text-gray-900" x-text="helper.nama"></div>
                    </td>
                    <td class="px-3 py-3 whitespace-nowrap">
                      <span class="text-sm text-gray-700" x-text="helper.nik"></span>
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
            <span x-text="`${filteredOperators.length} operator tersedia`"></span>
            <template x-if="useHelper">
              <span> • <span x-text="`${filteredHelpers.length} helper tersedia`"></span></span>
            </template>
          </div>
          <div class="mt-1">
            <span x-show="selectedOperator" class="text-green-600">
              Operator: <span x-text="selectedOperator ? selectedOperator.nama : ''"></span>
            </span>
            <template x-if="useHelper && selectedHelper">
              <span class="text-purple-600 ml-3">
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
            :disabled="!selectedOperator"
            :class="!selectedOperator ? 'opacity-50 cursor-not-allowed' : ''"
          >
            Konfirmasi Pilihan
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// KENDARAAN PICKER dengan HELPER di dalamnya
function kendaraanPicker(rowIndex) {
  return {
    open: false,
    rowIndex: rowIndex,
    searchOperatorQuery: '',
    searchHelperQuery: '',
    currentActivityCode: '',
    selectedOperator: null,
    selectedHelper: null,
    useHelper: false,

    get hasVehicle() {
      return this.currentActivityCode && this.canUseVehicle;
    },

    get canUseVehicle() {
      if (!this.currentActivityCode || !window.activitiesData) return false;

      const activity = window.activitiesData.find(act => act.activitycode === this.currentActivityCode);
      return activity && activity.usingvehicle === 1;
    },

    get availableOperators() {
      if (!window.operatorsData) return [];
      return window.operatorsData.filter(op => op.hasVehicle === 1 || op.hasVehicle === true);
    },

    get availableHelpers() {
      if (!window.helpersData) return [];
      return window.helpersData;
    },

    get filteredOperators() {
      if (!this.searchOperatorQuery) return this.availableOperators;
      const q = this.searchOperatorQuery.toString().toUpperCase();
      return this.availableOperators.filter(op =>
        op.nama.toUpperCase().includes(q) ||
        op.tenagakerjaid.toString().toUpperCase().includes(q) ||
        (op.nokendaraan && op.nokendaraan.toUpperCase().includes(q)) ||
        (op.jenis && op.jenis.toUpperCase().includes(q))
      );
    },

    get filteredHelpers() {
      if (!this.searchHelperQuery) return this.availableHelpers;
      const q = this.searchHelperQuery.toString().toUpperCase();
      return this.availableHelpers.filter(helper =>
        helper.nama.toUpperCase().includes(q) ||
        helper.tenagakerjaid.toString().toUpperCase().includes(q) ||
        (helper.nik && helper.nik.toString().toUpperCase().includes(q))
      );
    },

    checkVehicle() {
      if (this.hasVehicle) {
        this.open = true;
      } else {
        this.selectedOperator = null;
        this.selectedHelper = null;
        this.useHelper = false;
        this.updateHiddenInputs();
      }
    },

    selectOperator(operator) {
      this.selectedOperator = {
        tenagakerjaid: operator.tenagakerjaid,
        nama: operator.nama,
        nokendaraan: operator.nokendaraan,
        jenis: operator.jenis
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
      if (this.selectedOperator) {
        this.updateHiddenInputs();
        this.open = false;
      }
    },

    clearSelection() {
      this.selectedOperator = null;
      this.selectedHelper = null;
      this.useHelper = false;
      this.updateHiddenInputs();
    },

    updateHiddenInputs() {
      this.ensureHiddenInputsExist();

      // Operator inputs
      const operatorIdInput = document.querySelector(`input[name="rows[${this.rowIndex}][operatorid]"]`);
      const operatorNameInput = document.querySelector(`input[name="rows[${this.rowIndex}][operator_name]"]`);
      const vehicleNoInput = document.querySelector(`input[name="rows[${this.rowIndex}][vehicle_no]"]`);
      const usingVehicleInput = document.querySelector(`input[name="rows[${this.rowIndex}][usingvehicle]"]`);

      // Helper inputs
      const helperIdInput = document.querySelector(`input[name="rows[${this.rowIndex}][helperid]"]`);
      const usingHelperInput = document.querySelector(`input[name="rows[${this.rowIndex}][usinghelper]"]`);

      // Update operator data
      if (operatorIdInput) {
        operatorIdInput.value = this.selectedOperator ? this.selectedOperator.tenagakerjaid : '';
      }
      if (operatorNameInput) {
        operatorNameInput.value = this.selectedOperator ? this.selectedOperator.nama : '';
      }
      if (vehicleNoInput) {
        vehicleNoInput.value = this.selectedOperator ? this.selectedOperator.nokendaraan : '';
      }
      if (usingVehicleInput) {
        usingVehicleInput.value = this.canUseVehicle ? '1' : '0';
      }

      // Update helper data
      if (helperIdInput) {
        helperIdInput.value = (this.useHelper && this.selectedHelper) ? this.selectedHelper.tenagakerjaid : '';
      }
      if (usingHelperInput) {
        usingHelperInput.value = this.useHelper ? '1' : '0';
      }
    },

    ensureHiddenInputsExist() {
      const vehicleCell = document.querySelector(`tr:nth-child(${this.rowIndex + 1}) td:nth-child(11)`);
      if (!vehicleCell) return;

      // Operator hidden inputs
      ['operatorid', 'operator_name', 'vehicle_no', 'usingvehicle'].forEach(fieldName => {
        if (!document.querySelector(`input[name="rows[${this.rowIndex}][${fieldName}]"]`)) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = `rows[${this.rowIndex}][${fieldName}]`;
          input.value = fieldName === 'usingvehicle' ? '0' : '';
          vehicleCell.appendChild(input);
        }
      });

      // Helper hidden inputs
      ['helperid', 'usinghelper'].forEach(fieldName => {
        if (!document.querySelector(`input[name="rows[${this.rowIndex}][${fieldName}]"]`)) {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = `rows[${this.rowIndex}][${fieldName}]`;
          input.value = fieldName === 'usinghelper' ? '0' : '';
          vehicleCell.appendChild(input);
        }
      });
    },

    init() {
      this.ensureHiddenInputsExist();

      // Observer activity input changes
      const activityInput = document.querySelector(`input[name="rows[${this.rowIndex}][nama]"]`);
      if (activityInput) {
        const observer = new MutationObserver(() => {
          const newActivity = activityInput.value || '';
          if (this.currentActivityCode !== newActivity) {
            this.currentActivityCode = newActivity;
            this.selectedOperator = null;
            this.selectedHelper = null;
            this.useHelper = false;
            this.updateHiddenInputs();
          }
        });

        observer.observe(activityInput, {
          attributes: true,
          attributeFilter: ['value']
        });

        activityInput.addEventListener('input', () => {
          const newActivity = activityInput.value || '';
          if (this.currentActivityCode !== newActivity) {
            this.currentActivityCode = newActivity;
            this.selectedOperator = null;
            this.selectedHelper = null;
            this.useHelper = false;
            this.updateHiddenInputs();
          }
        });

        this.currentActivityCode = activityInput.value || '';
        this.updateHiddenInputs();
      }
    }
  }
}
</script>
