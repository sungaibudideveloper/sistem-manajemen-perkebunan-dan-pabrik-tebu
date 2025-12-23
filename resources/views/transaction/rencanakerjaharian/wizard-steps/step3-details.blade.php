{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step3-details.blade.php --}}

<div class="space-y-4">
  
  {{-- Header - Compact --}}
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-xl font-bold text-gray-800">Confirm Plot Details</h3>
      <p class="text-sm text-gray-600">Review and adjust work area for each plot</p>
    </div>
  </div>

  {{-- Summary Stats - Compact --}}
  <div class="grid grid-cols-3 gap-3 mb-4">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-3 text-white">
      <p class="text-blue-100 text-xs font-medium mb-1">Total Plots</p>
      <p class="text-2xl font-bold" x-text="getTotalPlotsCount()"></p>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-3 text-white">
      <p class="text-green-100 text-xs font-medium mb-1">Total Area</p>
      <p class="text-2xl font-bold" x-text="getTotalLuasAll() + ' Ha'"></p>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg p-3 text-white">
      <p class="text-purple-100 text-xs font-medium mb-1">Activities</p>
      <p class="text-2xl font-bold" x-text="Object.keys(plotAssignments).length"></p>
    </div>
  </div>

  {{-- Plot Details by Activity - Compact --}}
  <div class="space-y-4">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div x-show="plotAssignments[actCode]?.length > 0" class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        
        {{-- Activity Header - Compact --}}
        <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 bg-blue-500 text-white text-xs font-bold rounded" x-text="actCode"></span>
              <span class="text-sm font-semibold text-gray-800" x-text="activity.name"></span>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-500">Total Area</p>
              <p class="text-lg font-bold text-blue-600" x-text="getTotalLuasForActivityConfirmed(actCode) + ' Ha'"></p>
            </div>
          </div>
        </div>

        {{-- Plot Cards - Compact --}}
        <div class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            
            <template x-for="(plot, plotIndex) in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
              <div 
                x-data="plotDetailCard(actCode, plot)"
                x-init="init()"
                class="bg-gray-50 rounded-lg border border-gray-200 p-3 hover:border-blue-300 transition-colors">
                
                {{-- Plot Header - Compact --}}
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-gray-600 rounded-lg flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-bold text-gray-800" x-text="`${plot.blok}-${plot.plot}`"></p>
                      <p class="text-[10px] text-gray-500">Plot Code</p>
                    </div>
                  </div>

                  {{-- Status Badge --}}
                  <span 
                    x-show="plotInfo.lifecyclestatus"
                    class="inline-block px-2 py-0.5 text-[10px] font-bold rounded"
                    :class="{
                      'bg-green-100 text-green-800': plotInfo.lifecyclestatus === 'PC',
                      'bg-blue-100 text-blue-800': plotInfo.lifecyclestatus === 'RC1',
                      'bg-yellow-100 text-yellow-800': plotInfo.lifecyclestatus === 'RC2',
                      'bg-purple-100 text-purple-800': plotInfo.lifecyclestatus === 'RC3'
                    }"
                    x-text="plotInfo.lifecyclestatus">
                  </span>
                </div>

                {{-- ✅ Plot Info from AJAX - Compact --}}
                <div x-show="isLoading" class="text-center py-3">
                  <svg class="animate-spin h-5 w-5 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>

                <div x-show="!isLoading && plotInfo.luasplot" class="space-y-2 mb-3">
                  {{-- Luas Plot Info --}}
                  <div class="grid grid-cols-2 gap-2">
                    <div class="bg-white rounded p-2 border border-gray-200">
                      <p class="text-[10px] text-gray-500">Luas Plot</p>
                      <p class="text-sm font-bold text-gray-800" x-text="parseFloat(plotInfo.luasplot).toFixed(2) + ' Ha'"></p>
                    </div>
                    <div class="bg-white rounded p-2 border border-gray-200">
                      <p class="text-[10px] text-gray-500">Luas Sisa</p>
                      <p class="text-sm font-bold text-green-600" x-text="parseFloat(plotInfo.luassisa).toFixed(2) + ' Ha'"></p>
                    </div>
                  </div>

                  {{-- ✅ Batch Info (if PANEN activity) --}}
                  <div x-show="isPanenActivity && plotInfo.batchno" class="bg-yellow-50 border border-yellow-200 rounded p-2">
                    <div class="flex items-center gap-1 mb-1">
                      <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <span class="text-[10px] font-semibold text-yellow-800">Sedang Panen</span>
                    </div>
                    <p class="text-[10px] text-yellow-700">
                      Batch: <span class="font-mono font-bold" x-text="plotInfo.batchno"></span>
                    </p>
                    <p x-show="plotInfo.tanggal" class="text-[10px] text-yellow-700 mt-1">
                      Tgl Panen: <span x-text="plotInfo.tanggal"></span>
                    </p>
                  </div>

                  {{-- ✅ Last Activity Info (if NOT panen) --}}
                  <div x-show="!isPanenActivity && plotInfo.tanggal" class="bg-blue-50 border border-blue-200 rounded p-2">
                    <p class="text-[10px] text-blue-700">
                      <span class="font-semibold">Last Activity:</span> <span x-text="plotInfo.tanggal"></span>
                    </p>
                  </div>

                  {{-- ✅ ZPK Info (if exists) --}}
                  <div x-show="plotInfo.zpk_date" class="bg-purple-50 border border-purple-200 rounded p-2">
                    <div class="flex items-center justify-between">
                      <span class="text-[10px] font-semibold text-purple-700">ZPK Date:</span>
                      <span class="text-[10px] text-purple-700" x-text="plotInfo.zpk_date"></span>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                      <span class="text-[10px] text-purple-600">Days Gap:</span>
                      <span 
                        class="text-[10px] font-bold"
                        :class="{
                          'text-green-600': plotInfo.zpk_status === 'ideal',
                          'text-red-600': plotInfo.zpk_status === 'too_early' || plotInfo.zpk_status === 'too_late'
                        }"
                        x-text="plotInfo.zpk_days_gap + ' days'">
                      </span>
                    </div>
                  </div>
                </div>

                {{-- Luas Input - Compact --}}
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-1">
                    Luas Kerja (Ha) <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      :max="parseFloat(plotInfo.luassisa || plot.luassisa)"
                      x-model="luasKerja"
                      @input="validateLuas($event)"
                      class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold text-sm text-gray-800"
                      placeholder="0.00"
                    >
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-xs">
                      Ha
                    </div>
                  </div>
                  <p class="text-[10px] text-gray-500 mt-1">
                    Max: <span class="font-semibold text-gray-700" x-text="parseFloat(plotInfo.luassisa || plot.luassisa).toFixed(2)"></span> Ha
                  </p>
                </div>

              </div>
            </template>

          </div>
        </div>

      </div>
    </template>

  </div>

  {{-- Validation Warning --}}
  <div x-show="!allLuasConfirmed()">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Information</h3>
          <p class="text-xs text-yellow-700">Please confirm work area (luas) for all plots before proceeding.</p>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
// ✅ Plot Detail Card Component with AJAX
function plotDetailCard(actCode, plot) {
  return {
    actCode: actCode,
    plot: plot,
    isLoading: false,
    isPanenActivity: false,
    plotInfo: {
      luasplot: plot.luasplot || 0,
      luassisa: plot.luassisa || plot.luasplot || 0,
      batchno: '',
      lifecyclestatus: plot.lifecyclestatus || '',
      tanggal: '',
      zpk_date: '',
      zpk_days_gap: 0,
      zpk_status: ''
    },
    luasKerja: '',

    init() {
      // Check if this is a panen activity
      this.isPanenActivity = window.PANEN_ACTIVITIES.includes(this.actCode);
      
      // Fetch plot info via AJAX
      this.fetchPlotInfo();
    },

    async fetchPlotInfo() {
      this.isLoading = true;
      
      try {
        const url = `${window.PLOT_INFO_BASE_URL}/${this.plot.plot}/${this.actCode}`;
        const response = await fetch(url);
        const data = await response.json();

        if (data.success) {
          this.plotInfo = {
            luasplot: parseFloat(data.luasplot || 0).toFixed(2),
            luassisa: parseFloat(data.luassisa || 0).toFixed(2),
            batchno: data.batchinfo?.batchno || '',
            lifecyclestatus: data.batchinfo?.lifecyclestatus || this.plot.lifecyclestatus || '',
            tanggal: data.tanggal || '',
            zpk_date: data.batchinfo?.zpk_date || '',
            zpk_days_gap: data.batchinfo?.zpk_days_gap || 0,
            zpk_status: data.batchinfo?.zpk_status || ''
          };
          
          // Auto-fill luas sisa
          this.luasKerja = this.plotInfo.luassisa;
          
          // Update parent component
          const wizardApp = Alpine.$data(document.querySelector('[x-data*="rkhWizardApp"]'));
          if (wizardApp) {
            const key = `${this.actCode}_${this.plot.blok}_${this.plot.plot}`;
            wizardApp.luasConfirmed[key] = this.plotInfo.luassisa;
          }
        }
      } catch (error) {
        console.error('Error fetching plot info:', error);
      } finally {
        this.isLoading = false;
      }
    },

    validateLuas(event) {
      const value = parseFloat(event.target.value) || 0;
      const maxLuas = parseFloat(this.plotInfo.luassisa);
      
      if (value > maxLuas) {
        this.luasKerja = maxLuas.toFixed(2);
        showToast(`Luas tidak boleh melebihi ${maxLuas.toFixed(2)} Ha`, 'warning', 3000);
        
        event.target.classList.add('border-red-500', 'bg-red-50');
        setTimeout(() => {
          event.target.classList.remove('border-red-500', 'bg-red-50');
        }, 2000);
      } else {
        this.luasKerja = value.toFixed(2);
      }
      
      // Update parent component
      const wizardApp = Alpine.$data(document.querySelector('[x-data*="rkhWizardApp"]'));
      if (wizardApp) {
        const key = `${this.actCode}_${this.plot.blok}_${this.plot.plot}`;
        wizardApp.luasConfirmed[key] = this.luasKerja;
      }
    }
  };
}
</script>