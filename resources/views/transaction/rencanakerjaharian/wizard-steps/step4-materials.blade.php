{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step4-materials.blade.php --}}

<div class="space-y-6">
  
  {{-- Header --}}
  <div class="text-center mb-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-2">Select Materials</h3>
    <p class="text-gray-600">Choose herbicide group for each plot (different plots can use different materials)</p>
  </div>

  {{-- Progress Info --}}
  <div class="max-w-5xl mx-auto">
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-14 h-14 bg-green-500 rounded-xl flex items-center justify-center">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600 mb-1">Material Selection Progress</p>
            <p class="text-3xl font-bold text-green-700">
              <span x-text="getCompletedMaterialCount()"></span>
              <span class="text-lg text-gray-400">/</span>
              <span x-text="getRequiredMaterialCount()"></span>
            </p>
          </div>
        </div>
        
        {{-- Progress Bar --}}
        <div class="w-48">
          <div class="relative pt-1">
            <div class="flex mb-2 items-center justify-between">
              <div>
                <span class="text-xs font-semibold inline-block text-green-600" 
                      x-text="`${getMaterialProgress()}%`">
                </span>
              </div>
            </div>
            <div class="overflow-hidden h-3 mb-4 text-xs flex rounded-full bg-green-100">
              <div 
                class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500"
                :style="`width: ${getMaterialProgress()}%`">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Materials by Activity --}}
  <div class="max-w-6xl mx-auto space-y-6">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div x-show="activity.usingmaterial == 1 && plotAssignments[actCode]?.length > 0" 
           class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        
        {{-- Activity Header --}}
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                </svg>
              </div>
              <div>
                <span class="text-xs font-bold text-green-600" x-text="actCode"></span>
                <p class="text-sm font-semibold text-gray-800" x-text="activity.name"></p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-500">Plots Selected</p>
              <p class="text-xl font-bold text-green-600" x-text="getMaterialCountForActivity(actCode)"></p>
            </div>
          </div>
        </div>

        {{-- Plot Material Cards --}}
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <template x-for="plot in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
              <div 
                class="bg-gray-50 rounded-lg border-2 p-5 transition-all hover:shadow-md"
                :class="materials[`${actCode}_${plot.blok}_${plot.plot}`] ? 'border-green-400 bg-green-50' : 'border-gray-200'">
                
                {{-- Plot Header --}}
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-gray-600 to-gray-700 rounded-lg flex items-center justify-center">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-base font-bold text-gray-800" x-text="`${plot.blok}-${plot.plot}`"></p>
                      <p class="text-xs text-gray-500" x-text="`${luasConfirmed[`${actCode}_${plot.blok}_${plot.plot}`] || 0} Ha`"></p>
                    </div>
                  </div>
                  
                  {{-- Status Badge --}}
                  <div>
                    <span 
                      x-show="materials[`${actCode}_${plot.blok}_${plot.plot}`]"
                      class="inline-flex items-center gap-1 px-2 py-1 text-xs font-bold bg-green-500 text-white rounded-full">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                      </svg>
                      Selected
                    </span>
                  </div>
                </div>

                {{-- Material Selection --}}
                <div>
                  <label class="block text-xs font-semibold text-gray-700 mb-2">
                    Herbicide Group <span class="text-red-500">*</span>
                  </label>
                  
                  <div class="space-y-2">
                    <template x-for="group in getAvailableMaterialGroups(actCode)" :key="group.herbisidagroupid">
                      <label 
                        class="flex items-start p-3 border-2 rounded-lg cursor-pointer transition-all hover:bg-white hover:shadow-sm"
                        :class="{
                          'border-green-500 bg-white shadow-sm': materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupid === group.herbisidagroupid,
                          'border-gray-200': materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupid !== group.herbisidagroupid
                        }">
                        <input 
                          type="radio" 
                          :name="`material_${actCode}_${plot.blok}_${plot.plot}`"
                          :value="group.herbisidagroupid"
                          @change="selectMaterial(actCode, plot, group)"
                          :checked="materials[`${actCode}_${plot.blok}_${plot.plot}`]?.groupid === group.herbisidagroupid"
                          class="mt-1 w-4 h-4 text-green-600 focus:ring-green-500">
                        <div class="ml-3 flex-1">
                          <p class="text-sm font-semibold text-gray-800" x-text="group.herbisidagroupname"></p>
                          <p class="text-xs text-gray-500 mt-1" x-text="group.description"></p>
                          
                          {{-- Material Items Preview --}}
                          <div class="mt-2 flex flex-wrap gap-1">
                            <template x-for="item in group.items.slice(0, 3)" :key="item.itemcode">
                              <span class="inline-block px-2 py-0.5 text-[9px] font-medium bg-gray-100 text-gray-600 rounded">
                                <span x-text="item.itemcode"></span>
                              </span>
                            </template>
                            <span 
                              x-show="group.items.length > 3"
                              class="inline-block px-2 py-0.5 text-[9px] font-medium bg-gray-200 text-gray-600 rounded">
                              +<span x-text="group.items.length - 3"></span> more
                            </span>
                          </div>
                        </div>
                      </label>
                    </template>
                  </div>
                  
                  {{-- No Materials Available --}}
                  <div x-show="getAvailableMaterialGroups(actCode).length === 0" 
                       class="text-center py-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <svg class="w-8 h-8 mx-auto text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-xs text-yellow-700 font-medium">No material groups available</p>
                  </div>

                </div>

              </div>
            </template>

          </div>
        </div>

      </div>
    </template>

  </div>

  {{-- No Materials Needed Info --}}
  <div x-show="getRequiredMaterialCount() === 0" class="max-w-5xl mx-auto">
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-8 text-center">
      <svg class="w-16 h-16 mx-auto text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <h3 class="text-xl font-bold text-gray-800 mb-2">No Materials Required</h3>
      <p class="text-gray-600">None of your selected activities require materials. You can proceed to the next step.</p>
    </div>
  </div>

  {{-- Validation Warning --}}
  <div x-show="getRequiredMaterialCount() > 0 && !allMaterialsSelected()" class="max-w-5xl mx-auto">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Selection</h3>
          <p class="text-sm text-yellow-700">Please select herbicide group for all plots before proceeding.</p>
        </div>
      </div>
    </div>
  </div>

</div>
