{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step3-details.blade.php --}}

<div class="space-y-6">
  
  {{-- Header --}}
  <div class="text-center mb-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-2">Confirm Plot Details</h3>
    <p class="text-gray-600">Review and adjust area (luas) for each plot assignment</p>
  </div>

  {{-- Summary Stats --}}
  <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-blue-100 text-sm font-medium mb-1">Total Plots</p>
          <p class="text-4xl font-bold" x-text="getTotalPlotsCount()"></p>
        </div>
        <svg class="w-12 h-12 text-blue-200 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
      </div>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-green-100 text-sm font-medium mb-1">Total Area</p>
          <p class="text-4xl font-bold" x-text="getTotalLuasAll() + ' Ha'"></p>
        </div>
        <svg class="w-12 h-12 text-green-200 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
        </svg>
      </div>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-purple-100 text-sm font-medium mb-1">Activities</p>
          <p class="text-4xl font-bold" x-text="Object.keys(plotAssignments).length"></p>
        </div>
        <svg class="w-12 h-12 text-purple-200 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
      </div>
    </div>
  </div>

  {{-- Plot Details by Activity --}}
  <div class="max-w-6xl mx-auto space-y-6">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div x-show="plotAssignments[actCode]?.length > 0" class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        
        {{-- Activity Header --}}
        <div class="bg-gradient-to-r from-blue-50 to-sky-50 border-b border-gray-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm" x-text="actCode.split('.')[0]"></span>
              </div>
              <div>
                <span class="text-xs font-bold text-blue-600" x-text="actCode"></span>
                <p class="text-sm font-semibold text-gray-800" x-text="activity.name"></p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-500">Total Area</p>
              <p class="text-xl font-bold text-blue-600" x-text="getTotalLuasForActivityConfirmed(actCode) + ' Ha'"></p>
            </div>
          </div>
        </div>

        {{-- Plot Cards --}}
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <template x-for="(plot, plotIndex) in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
              <div class="bg-gray-50 rounded-lg border border-gray-200 p-4 hover:border-blue-300 transition-colors">
                
                {{-- Plot Header --}}
                <div class="flex items-start justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-lg font-bold text-gray-800" x-text="`${plot.blok}-${plot.plot}`"></p>
                      <p class="text-xs text-gray-500">Plot Code</p>
                    </div>
                  </div>

                  {{-- Status Badges --}}
                  <div class="flex flex-col gap-1 items-end">
                    <span 
                      x-show="plot.lifecyclestatus"
                      class="inline-block px-2 py-0.5 text-[10px] font-bold rounded"
                      :class="{
                        'bg-green-100 text-green-800': plot.lifecyclestatus === 'PC',
                        'bg-blue-100 text-blue-800': plot.lifecyclestatus === 'RC1',
                        'bg-yellow-100 text-yellow-800': plot.lifecyclestatus === 'RC2',
                        'bg-purple-100 text-purple-800': plot.lifecyclestatus === 'RC3'
                      }"
                      x-text="plot.lifecyclestatus">
                    </span>
                  </div>
                </div>

                {{-- Plot Info Grid --}}
                <div class="grid grid-cols-2 gap-3 mb-4">
                  <div class="bg-white rounded-lg p-3 border border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Luas Plot</p>
                    <p class="text-lg font-bold text-gray-800" x-text="parseFloat(plot.luasplot).toFixed(2) + ' Ha'"></p>
                  </div>
                  <div class="bg-white rounded-lg p-3 border border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Luas Sisa</p>
                    <p class="text-lg font-bold text-green-600" x-text="parseFloat(plot.luassisa).toFixed(2) + ' Ha'"></p>
                  </div>
                </div>

                {{-- Batch Info (if panen) --}}
                <div x-show="plot.batchno" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                  <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs font-semibold text-yellow-800">Sedang Panen</span>
                  </div>
                  <p class="text-xs text-yellow-700">
                    Batch: <span class="font-mono font-bold" x-text="plot.batchno"></span>
                  </p>
                </div>

                {{-- Luas Input --}}
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Luas Kerja (Ha) <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      :max="parseFloat(plot.luassisa)"
                      x-model="luasConfirmed[`${actCode}_${plot.blok}_${plot.plot}`]"
                      @input="validateLuasInput($event, actCode, plot)"
                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold text-lg text-gray-800 transition-all"
                      placeholder="0.00"
                    >
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">
                      Ha
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">
                    Max: <span class="font-semibold text-gray-700" x-text="parseFloat(plot.luassisa).toFixed(2)"></span> Ha
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
  <div x-show="!allLuasConfirmed()" class="max-w-5xl mx-auto">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Information</h3>
          <p class="text-sm text-yellow-700">Please confirm luas (area) for all plots before proceeding.</p>
        </div>
      </div>
    </div>
  </div>

</div>
