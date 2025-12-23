{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step2-plots.blade.php --}}

<div class="space-y-6">
  
  {{-- Header --}}
  <div class="text-center mb-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-2">Assign Plots to Activities</h3>
    <p class="text-gray-600">Select multiple plots for each activity - bulk selection made easy!</p>
  </div>

  {{-- Activity Tabs --}}
  <div class="max-w-6xl mx-auto">
    <div class="border-b border-gray-200 mb-6">
      <div class="flex overflow-x-auto scrollbar-hide gap-2 pb-2">
        <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
          <button
            type="button"
            @click="currentActivityForPlots = actCode"
            class="flex-shrink-0 px-4 py-3 font-medium text-sm rounded-t-lg border-b-2 transition-all whitespace-nowrap"
            :class="{
              'border-blue-500 bg-blue-50 text-blue-700': currentActivityForPlots === actCode,
              'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50': currentActivityForPlots !== actCode
            }">
            <div class="flex items-center gap-2">
              <span x-text="actCode"></span>
              <span 
                x-show="plotAssignments[actCode]?.length > 0"
                class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold rounded-full"
                :class="currentActivityForPlots === actCode ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'"
                x-text="plotAssignments[actCode]?.length || 0">
              </span>
            </div>
          </button>
        </template>
      </div>
    </div>

    {{-- Current Activity Info --}}
    <div class="bg-gradient-to-r from-blue-50 to-sky-50 rounded-xl p-6 mb-6 border border-blue-200">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <span class="px-3 py-1 bg-blue-500 text-white text-sm font-bold rounded-lg" 
                  x-text="currentActivityForPlots"></span>
            <h4 class="text-lg font-semibold text-gray-800" 
                x-text="selectedActivities[currentActivityForPlots]?.name"></h4>
          </div>
          
          {{-- Selected Plots Count --}}
          <div class="flex items-center gap-4 mt-3">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <span class="text-sm font-medium text-gray-700">
                <span class="text-xl font-bold text-blue-700" x-text="plotAssignments[currentActivityForPlots]?.length || 0"></span>
                plots selected
              </span>
            </div>
            
            <button
              type="button"
              @click="clearPlotsForActivity(currentActivityForPlots)"
              x-show="plotAssignments[currentActivityForPlots]?.length > 0"
              class="text-sm text-red-600 hover:text-red-700 font-medium underline">
              Clear all plots
            </button>
          </div>
        </div>

        {{-- Quick Stats --}}
        <div class="text-right">
          <div class="text-xs text-gray-500 mb-1">Total Area</div>
          <div class="text-2xl font-bold text-blue-700" x-text="getTotalLuasForActivity(currentActivityForPlots) + ' Ha'"></div>
        </div>
      </div>
    </div>

    {{-- Blok Selection Mode --}}
    <div class="bg-white border-2 border-gray-200 rounded-xl overflow-hidden">
      
      {{-- Blok Tabs --}}
      <div class="bg-gray-50 border-b border-gray-200 px-4 py-3">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
          <span class="text-sm font-medium text-gray-600 flex-shrink-0">Select Blok:</span>
          <template x-for="blok in availableBloks()" :key="blok">
            <button
              type="button"
              @click="selectedBlokForPlots = blok"
              class="px-4 py-2 text-sm font-medium rounded-lg transition-all flex-shrink-0"
              :class="{
                'bg-blue-500 text-white shadow-md': selectedBlokForPlots === blok,
                'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300': selectedBlokForPlots !== blok
              }">
              Blok <span x-text="blok"></span>
              <span 
                x-show="getSelectedPlotsInBlok(blok, currentActivityForPlots).length > 0"
                class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs font-bold rounded-full bg-white text-blue-600"
                x-text="getSelectedPlotsInBlok(blok, currentActivityForPlots).length">
              </span>
            </button>
          </template>
        </div>
      </div>

      {{-- Bulk Actions --}}
      <div class="bg-yellow-50 border-b border-yellow-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
          </svg>
          <span class="text-sm font-medium text-yellow-800">Bulk Actions</span>
        </div>
        <div class="flex gap-2">
          <button
            type="button"
            @click="selectAllPlotsInBlok(selectedBlokForPlots)"
            class="px-3 py-1.5 text-xs font-medium bg-white hover:bg-gray-100 text-gray-700 rounded-lg border border-gray-300 transition-colors">
            Select All in Blok
          </button>
          <button
            type="button"
            @click="deselectAllPlotsInBlok(selectedBlokForPlots)"
            x-show="getSelectedPlotsInBlok(selectedBlokForPlots, currentActivityForPlots).length > 0"
            class="px-3 py-1.5 text-xs font-medium bg-red-50 hover:bg-red-100 text-red-700 rounded-lg border border-red-300 transition-colors">
            Deselect All
          </button>
        </div>
      </div>

      {{-- Plots Grid --}}
      <div class="p-4 max-h-[500px] overflow-y-auto">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
          <template x-for="plot in getPlotsForBlok(selectedBlokForPlots)" :key="plot.plot">
            <div
              @click="togglePlotForActivity(currentActivityForPlots, plot)"
              class="relative group cursor-pointer bg-white border-2 rounded-lg p-4 transition-all duration-200 hover:shadow-md"
              :class="{
                'border-blue-500 bg-blue-50 shadow-sm': isPlotSelectedForActivity(currentActivityForPlots, plot),
                'border-gray-200 hover:border-blue-300': !isPlotSelectedForActivity(currentActivityForPlots, plot)
              }">
              
              {{-- Checkbox --}}
              <div class="absolute top-2 right-2">
                <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-all"
                     :class="isPlotSelectedForActivity(currentActivityForPlots, plot) ? 'bg-blue-500 border-blue-500' : 'border-gray-300 group-hover:border-blue-400'">
                  <svg x-show="isPlotSelectedForActivity(currentActivityForPlots, plot)" 
                       class="w-3 h-3 text-white" 
                       fill="none" 
                       stroke="currentColor" 
                       viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
              </div>

              {{-- Plot Info --}}
              <div class="pr-6">
                <div class="text-lg font-bold text-gray-800 mb-1" x-text="plot.plot"></div>
                <div class="text-xs text-gray-600" x-text="plot.luasplot + ' Ha'"></div>
                
                {{-- Status Badge --}}
                <div class="mt-2" x-show="plot.is_on_panen == 1">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[9px] font-bold bg-yellow-100 text-yellow-700 rounded">
                    <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full animate-pulse"></span>
                    PANEN
                  </span>
                </div>
              </div>

            </div>
          </template>
        </div>

        {{-- Empty State --}}
        <div x-show="getPlotsForBlok(selectedBlokForPlots).length === 0" class="text-center py-12">
          <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <p class="text-gray-500 text-sm">No plots in this blok</p>
        </div>
      </div>

    </div>

    {{-- Selected Plots Summary (All Activities) --}}
    <div class="mt-6 bg-gray-50 rounded-xl border border-gray-200 p-6">
      <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Summary: All Activities
      </h4>

      <div class="space-y-3">
        <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
          <div 
            x-show="plotAssignments[actCode]?.length > 0"
            class="bg-white rounded-lg p-4 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1">
                <span class="text-xs font-bold text-blue-600" x-text="actCode"></span>
                <p class="text-sm font-medium text-gray-800 mt-1" x-text="activity.name"></p>
              </div>
              <span class="text-lg font-bold text-blue-600" x-text="plotAssignments[actCode]?.length || 0"></span>
            </div>
            
            {{-- Plot Tags --}}
            <div class="flex flex-wrap gap-2 mt-3">
              <template x-for="plot in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-200">
                  <span x-text="`${plot.blok}-${plot.plot}`"></span>
                  <button
                    type="button"
                    @click.stop="removePlotFromActivity(actCode, plot)"
                    class="hover:bg-blue-200 rounded-full p-0.5 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </span>
              </template>
            </div>
          </div>
        </template>
      </div>

      {{-- Empty State --}}
      <div x-show="Object.values(plotAssignments).every(plots => !plots || plots.length === 0)" 
           class="text-center py-8">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-gray-500 text-sm">No plots assigned yet</p>
      </div>
    </div>

  </div>

</div>
