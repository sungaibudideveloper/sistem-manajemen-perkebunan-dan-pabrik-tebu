{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step7-review.blade.php --}}

<div class="space-y-6">
  
  {{-- Header --}}
  <div class="text-center mb-8">
    <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
      <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
    </div>
    <h3 class="text-3xl font-bold text-gray-800 mb-2">Review Your RKH</h3>
    <p class="text-gray-600">Please review all information before submitting</p>
  </div>

  {{-- Quick Stats Cards --}}
  <div class="max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white shadow-lg">
      <p class="text-blue-100 text-xs font-medium mb-1">Activities</p>
      <p class="text-3xl font-bold" x-text="Object.keys(selectedActivities).length"></p>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white shadow-lg">
      <p class="text-green-100 text-xs font-medium mb-1">Total Plots</p>
      <p class="text-3xl font-bold" x-text="getTotalPlotsCount()"></p>
    </div>
    
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white shadow-lg">
      <p class="text-purple-100 text-xs font-medium mb-1">Total Area</p>
      <p class="text-3xl font-bold" x-text="getTotalLuasAll()"></p>
      <p class="text-purple-100 text-xs">Ha</p>
    </div>
    
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-4 text-white shadow-lg">
      <p class="text-orange-100 text-xs font-medium mb-1">Vehicles</p>
      <p class="text-3xl font-bold" x-text="getTotalVehiclesAssigned()"></p>
    </div>
    
    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl p-4 text-white shadow-lg">
      <p class="text-pink-100 text-xs font-medium mb-1">Workers</p>
      <p class="text-3xl font-bold" x-text="getTotalWorkers('total')"></p>
    </div>
  </div>

  {{-- Detailed Review by Activity --}}
  <div class="max-w-7xl mx-auto space-y-6">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div class="bg-white rounded-xl shadow-lg border-2 border-gray-200 overflow-hidden">
        
        {{-- Activity Header --}}
        <div class="bg-gradient-to-r from-slate-700 to-slate-800 text-white px-6 py-5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <span class="text-2xl font-bold" x-text="actCode.split('.')[0]"></span>
              </div>
              <div>
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-sm font-bold text-blue-300" x-text="actCode"></span>
                  <span 
                    class="inline-block px-2 py-0.5 text-[9px] font-bold rounded"
                    :class="{
                      'bg-blue-400 text-blue-900': activity.jenistenagakerja == 1,
                      'bg-green-400 text-green-900': activity.jenistenagakerja == 2,
                      'bg-orange-400 text-orange-900': activity.jenistenagakerja == 3,
                      'bg-purple-400 text-purple-900': activity.jenistenagakerja == 4
                    }"
                    x-text="getJenisLabel(activity.jenistenagakerja)">
                  </span>
                </div>
                <p class="text-lg font-semibold" x-text="activity.name"></p>
              </div>
            </div>
            
            {{-- Edit Button --}}
            <button
              type="button"
              @click="goToStep(2)"
              class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg text-sm font-medium transition-colors backdrop-blur-sm flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Edit
            </button>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- LEFT: Plot Details --}}
            <div class="lg:col-span-2">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  </svg>
                  Plot Assignments
                </h4>
                <span class="text-sm font-semibold text-gray-600">
                  <span class="text-xl text-green-600" x-text="(plotAssignments[actCode] || []).length"></span> plots
                </span>
              </div>

              {{-- Plot Cards Grid --}}
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                <template x-for="(plot, pIndex) in (plotAssignments[actCode] || [])" :key="`review-${actCode}-${plot.blok}-${plot.plot}`">
                  <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-lg border-2 border-gray-200 p-4">
                    
                    {{-- Plot Header --}}
                    <div class="flex items-center justify-between mb-3">
                      <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg flex items-center justify-center">
                          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                          </svg>
                        </div>
                        <div>
                          <p class="text-base font-bold text-gray-800" x-text="`${plot.blok}-${plot.plot}`"></p>
                          <p class="text-xs text-gray-500">Plot Code</p>
                        </div>
                      </div>
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

                    {{-- Plot Info --}}
                    <div class="space-y-2 text-xs">
                      <div class="flex justify-between">
                        <span class="text-gray-600">Luas Kerja:</span>
                        <span class="font-bold text-gray-800" x-text="`${luasConfirmed[`${actCode}_${plot.blok}_${plot.plot}`] || 0} Ha`"></span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Luas Plot:</span>
                        <span class="font-semibold text-gray-700" x-text="`${parseFloat(plot.luasplot).toFixed(2)} Ha`"></span>
                      </div>
                      
                      {{-- Material Info (if applicable) --}}
                      <div 
                        x-show="activity.usingmaterial == 1 && materials[`${actCode}_${plot.blok}_${plot.plot}`]"
                        class="pt-2 border-t border-gray-300">
                        <div class="flex items-center gap-1 mb-1">
                          <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                          </svg>
                          <span class="text-gray-600">Material:</span>
                        </div>
                        <p class="text-xs font-semibold text-green-700 ml-4" x-text="materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupname"></p>
                      </div>

                      {{-- Batch Info (if panen) --}}
                      <div x-show="plot.batchno" class="pt-2 border-t border-gray-300">
                        <div class="flex items-center gap-1 text-yellow-700">
                          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                          </svg>
                          <span class="text-xs">Batch: <span class="font-mono font-bold" x-text="plot.batchno"></span></span>
                        </div>
                      </div>
                    </div>

                  </div>
                </template>
              </div>

              {{-- Total Luas for Activity --}}
              <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-semibold text-gray-700">Total Area for this Activity:</span>
                  <span class="text-2xl font-bold text-green-700" x-text="`${getTotalLuasForActivityConfirmed(actCode)} Ha`"></span>
                </div>
              </div>
            </div>

            {{-- RIGHT: Resources (Vehicles & Workers) --}}
            <div class="space-y-4">
              
              {{-- Vehicles Section --}}
              <div x-show="activity.usingvehicle == 1">
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                  <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                    </svg>
                    <h5 class="font-bold text-gray-800">Vehicles</h5>
                    <span class="ml-auto text-sm font-semibold text-orange-600" x-text="`${(vehicles[actCode] || []).length} units`"></span>
                  </div>
                  
                  <div class="space-y-2">
                    <template x-for="(vehicle, vIdx) in (vehicles[actCode] || [])" :key="`review-vehicle-${actCode}-${vIdx}`">
                      <div class="bg-white rounded-lg p-3 border border-orange-200">
                        <div class="flex items-center gap-2 mb-2">
                          <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                          </svg>
                          <span class="font-bold text-gray-800 text-sm" x-text="vehicle.nokendaraan"></span>
                        </div>
                        <div class="text-xs space-y-1 ml-6">
                          <p class="text-gray-600">Op: <span class="font-semibold text-gray-800" x-text="vehicle.operator_name"></span></p>
                          <p x-show="vehicle.helperid" class="text-gray-600">Helper: <span class="font-semibold text-gray-800" x-text="vehicle.helper_name"></span></p>
                        </div>
                      </div>
                    </template>
                  </div>

                  <div x-show="!(vehicles[actCode] || []).length" class="text-center py-4 text-orange-600 text-sm font-medium">
                    No vehicles assigned
                  </div>
                </div>
              </div>

              {{-- Workers Section --}}
              <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-3">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <h5 class="font-bold text-gray-800">Manpower</h5>
                </div>

                <div class="grid grid-cols-3 gap-2">
                  <div class="bg-white rounded-lg p-3 border border-blue-200 text-center">
                    <p class="text-xs text-gray-600 mb-1">Male</p>
                    <p class="text-xl font-bold text-blue-700" x-text="workers[actCode]?.laki || 0"></p>
                  </div>
                  <div class="bg-white rounded-lg p-3 border border-pink-200 text-center">
                    <p class="text-xs text-gray-600 mb-1">Female</p>
                    <p class="text-xl font-bold text-pink-700" x-text="workers[actCode]?.perempuan || 0"></p>
                  </div>
                  <div class="bg-white rounded-lg p-3 border border-indigo-200 text-center">
                    <p class="text-xs text-gray-600 mb-1">Total</p>
                    <p class="text-xl font-bold text-indigo-700" x-text="workers[actCode]?.total || 0"></p>
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>

      </div>
    </template>

  </div>

  {{-- Final Summary Card --}}
  <div class="max-w-7xl mx-auto">
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl p-6 shadow-lg">
      <div class="flex items-start gap-4">
        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="flex-1">
          <h4 class="text-lg font-bold text-gray-800 mb-2">Ready to Submit?</h4>
          <p class="text-sm text-gray-600 mb-4">
            You have configured <span class="font-bold text-green-700" x-text="Object.keys(selectedActivities).length"></span> activities 
            across <span class="font-bold text-green-700" x-text="getTotalPlotsCount()"></span> plots 
            with <span class="font-bold text-green-700" x-text="getTotalWorkers('total')"></span> workers 
            and <span class="font-bold text-green-700" x-text="getTotalVehiclesAssigned()"></span> vehicles.
          </p>
          
          <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 text-xs text-gray-600">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              All required fields completed
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-600">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              Data validated
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Action Buttons --}}
  <div class="max-w-7xl mx-auto flex justify-center gap-4 pt-6">
    <button
      type="button"
      @click="currentStep = 1"
      class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
      </svg>
      Edit from Start
    </button>
  </div>

</div>
