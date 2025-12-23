{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step2-plots.blade.php --}}

<div class="space-y-4">
  
  {{-- Header - Compact --}}
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-xl font-bold text-gray-800">Assign Plots to Activities</h3>
      <p class="text-sm text-gray-600">Select plots for each activity</p>
    </div>
  </div>

  {{-- Activity Tabs - Compact --}}
  <div>
    <div class="border-b border-gray-200 mb-4">
      <div class="flex overflow-x-auto scrollbar-hide gap-1">
        <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
          <button
            type="button"
            @click="currentActivityForPlots = actCode"
            class="flex-shrink-0 px-3 py-2 text-xs font-medium rounded-t-lg border-b-2 transition-all whitespace-nowrap"
            :class="{
              'border-blue-500 bg-blue-50 text-blue-700': currentActivityForPlots === actCode,
              'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50': currentActivityForPlots !== actCode
            }">
            <div class="flex items-center gap-2">
              <span x-text="actCode"></span>
              <span 
                x-show="plotAssignments[actCode]?.length > 0"
                class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold rounded-full"
                :class="currentActivityForPlots === actCode ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'"
                x-text="plotAssignments[actCode]?.length || 0">
              </span>
              {{-- ✅ FIXED: Badge "B" only --}}
              <span 
                x-show="activity.isblokactivity == 1"
                class="inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold bg-purple-100 text-purple-700 rounded"
                title="Blok Activity">
                B
              </span>
            </div>
          </button>
        </template>
      </div>
    </div>

    {{-- Current Activity Info - Compact --}}
    <div class="bg-gradient-to-r from-blue-50 to-sky-50 rounded-lg p-4 mb-4 border border-blue-200">
      <div class="flex items-start justify-between">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-2">
            <span class="px-2 py-0.5 bg-blue-500 text-white text-xs font-bold rounded" 
                  x-text="currentActivityForPlots"></span>
            <h4 class="text-sm font-semibold text-gray-800" 
                x-text="selectedActivities[currentActivityForPlots]?.name"></h4>
            <span 
              x-show="selectedActivities[currentActivityForPlots]?.isblokactivity == 1"
              class="inline-flex items-center px-2 py-0.5 text-[10px] font-bold bg-purple-100 text-purple-700 rounded">
              Blok Activity
            </span>
          </div>
          
          {{-- Info Text for Blok Activity --}}
          <div x-show="selectedActivities[currentActivityForPlots]?.isblokactivity == 1" class="mt-2">
            <p class="text-xs text-purple-700 font-medium">
              <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              This is a blok activity - select blok only (plot selection disabled)
            </p>
          </div>
          
          {{-- Selected Plots Count (only for non-blok activity) --}}
          <div x-show="selectedActivities[currentActivityForPlots]?.isblokactivity != 1" class="flex items-center gap-4 mt-2">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <span class="text-xs font-medium text-gray-700">
                <span class="text-lg font-bold text-blue-700" x-text="plotAssignments[currentActivityForPlots]?.length || 0"></span>
                plots selected
              </span>
            </div>
            
            <button
              type="button"
              @click="clearPlotsForActivity(currentActivityForPlots)"
              x-show="plotAssignments[currentActivityForPlots]?.length > 0"
              class="text-xs text-red-600 hover:text-red-700 font-medium">
              Clear all
            </button>
          </div>
        </div>

        {{-- Total Area --}}
        <div class="text-right" x-show="selectedActivities[currentActivityForPlots]?.isblokactivity != 1">
          <div class="text-[10px] text-gray-500 mb-1">Total Area</div>
          <div class="text-lg font-bold text-blue-700" x-text="getTotalLuasForActivity(currentActivityForPlots) + ' Ha'"></div>
        </div>
      </div>
    </div>

    {{-- ✅ BLOK ACTIVITY: Show "ALL" option --}}
    <div x-show="selectedActivities[currentActivityForPlots]?.isblokactivity == 1" class="bg-white border border-gray-200 rounded-lg p-6">
      <div class="text-center">
        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
          </svg>
        </div>
        <h4 class="text-lg font-bold text-gray-800 mb-2">Blok Activity Mode</h4>
        <p class="text-sm text-gray-600 mb-6">This activity applies to entire blok areas. Select blok from the list below.</p>
        
        {{-- Blok Selection for Blok Activity --}}
        <div class="max-w-md mx-auto">
          <label class="block text-sm font-semibold text-gray-700 mb-2 text-left">Select Blok:</label>
          
          {{-- "ALL" Option --}}
          <div 
            @click="selectBlokForBlokActivity('ALL')"
            class="mb-2 p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-purple-400"
            :class="getSelectedBlokForActivity(currentActivityForPlots) === 'ALL' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="text-left">
                  <p class="text-sm font-bold text-gray-800">ALL BLOKS</p>
                  <p class="text-xs text-gray-500">Apply to all blok areas</p>
                </div>
              </div>
              {{-- ✅ FIXED: Checkbox visual state --}}
              <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                   :class="getSelectedBlokForActivity(currentActivityForPlots) === 'ALL' ? 'bg-purple-500 border-purple-500' : 'border-gray-300'">
                <svg x-show="getSelectedBlokForActivity(currentActivityForPlots) === 'ALL'" 
                     class="w-3 h-3 text-white" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </div>
          </div>

          {{-- ✅ NEW: Search Box --}}
          <div class="mb-3">
            <div class="relative">
              <input 
                type="text" 
                x-model="blokSearchQuery"
                placeholder="Search blok..."
                class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
              <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>

          {{-- ✅ UPDATED: Individual Bloks with Search Filter --}}
          <div class="space-y-2 max-h-64 overflow-y-auto">
            <template x-for="blok in filteredBloksForActivity()" :key="blok">
              <div 
                @click="selectBlokForBlokActivity(blok)"
                class="p-3 border-2 rounded-lg cursor-pointer transition-all hover:border-blue-400"
                :class="getSelectedBlokForActivity(currentActivityForPlots) === blok ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                      <span class="text-white text-xs font-bold" x-text="blok"></span>
                    </div>
                    <span class="text-sm font-semibold text-gray-800" x-text="'Blok ' + blok"></span>
                  </div>
                  {{-- ✅ FIXED: Checkbox visual state --}}
                  <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                       :class="getSelectedBlokForActivity(currentActivityForPlots) === blok ? 'bg-blue-500 border-blue-500' : 'border-gray-300'">
                    <svg x-show="getSelectedBlokForActivity(currentActivityForPlots) === blok" 
                         class="w-3 h-3 text-white" 
                         fill="none" 
                         stroke="currentColor" 
                         viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
              </div>
            </template>
          </div>

          {{-- Empty State for Search --}}
          <div x-show="filteredBloksForActivity().length === 0" class="text-center py-6">
            <p class="text-sm text-gray-500">No bloks found</p>
          </div>
        </div>
      </div>
    </div>

    {{-- ✅ NORMAL ACTIVITY: Blok & Plot Selection --}}
    <div x-show="selectedActivities[currentActivityForPlots]?.isblokactivity != 1" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
      
      {{-- Blok Tabs - Compact --}}
      <div class="bg-gray-50 border-b border-gray-200 px-3 py-2">
        <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
          <span class="text-xs font-medium text-gray-600 flex-shrink-0">Blok:</span>
          <template x-for="blok in availableBloks()" :key="blok">
            <button
              type="button"
              @click="selectedBlokForPlots = blok"
              class="px-3 py-1.5 text-xs font-medium rounded transition-all flex-shrink-0"
              :class="{
                'bg-blue-500 text-white shadow': selectedBlokForPlots === blok,
                'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300': selectedBlokForPlots !== blok
              }">
              <span x-text="blok"></span>
              <span 
                x-show="getSelectedPlotsInBlok(blok, currentActivityForPlots).length > 0"
                class="ml-1 inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold rounded-full bg-white text-blue-600"
                x-text="getSelectedPlotsInBlok(blok, currentActivityForPlots).length">
              </span>
            </button>
          </template>
        </div>
      </div>

      <div class="p-3 max-h-[400px] overflow-y-auto">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
          <template x-for="plot in getPlotsForBlok(selectedBlokForPlots)" :key="plot.plot">
            <div
              @click="togglePlotForActivity(currentActivityForPlots, plot)"
              class="relative group cursor-pointer bg-white border rounded-lg p-3 transition-all hover:shadow-md"
              :class="{
                'border-blue-500 bg-blue-50 shadow-sm': isPlotSelectedForActivity(currentActivityForPlots, plot),
                'border-gray-200 hover:border-blue-300': !isPlotSelectedForActivity(currentActivityForPlots, plot)
              }">
              
              {{-- Checkbox --}}
              <div class="absolute top-2 right-2">
                <div class="w-4 h-4 rounded border-2 flex items-center justify-center transition-all"
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
              <div class="pr-5">
                <div class="text-sm font-bold text-gray-800 mb-1" x-text="plot.plot"></div>
                
                <div class="text-xs text-gray-600">
                  <span x-text="(parseFloat(plot.luassisa) || 0).toFixed(2)"></span>
                  <span class="text-gray-400"> Ha</span>
                </div>
                
                {{-- ✅ NEW: Lifecycle Status Badge --}}
                <div class="mt-2">
                  <template x-if="plot.lifecyclestatus">
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-[9px] font-bold rounded"
                          :class="{
                            'bg-yellow-100 text-yellow-700': plot.lifecyclestatus === 'PC',
                            'bg-green-100 text-green-700': plot.lifecyclestatus === 'RC1',
                            'bg-blue-100 text-blue-700': plot.lifecyclestatus === 'RC2',
                            'bg-purple-100 text-purple-700': plot.lifecyclestatus === 'RC3'
                          }">
                      <span class="w-1 h-1 rounded-full animate-pulse"
                            :class="{
                              'bg-yellow-500': plot.lifecyclestatus === 'PC',
                              'bg-green-500': plot.lifecyclestatus === 'RC1',
                              'bg-blue-500': plot.lifecyclestatus === 'RC2',
                              'bg-purple-500': plot.lifecyclestatus === 'RC3'
                            }"></span>
                      <span x-text="plot.lifecyclestatus"></span>
                    </span>
                  </template>
                </div>

                {{-- ✅ NEW: Last Activity Info --}}
                <div x-show="plot.last_activitycode" class="mt-2 pt-2 border-t border-gray-200">
                  <div class="text-[9px] text-gray-500 mb-0.5">Last Activity:</div>
                  <div class="text-[10px] font-semibold text-gray-700" x-text="plot.last_activitycode"></div>
                  <div class="text-[9px] text-gray-500" x-text="formatDate(plot.last_activity_date)"></div>
                </div>
              </div>

            </div>
          </template>
        </div>

        {{-- Empty State --}}
        <div x-show="getPlotsForBlok(selectedBlokForPlots).length === 0" class="text-center py-8">
          <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
          <p class="text-gray-500 text-xs">No plots in this blok</p>
        </div>
      </div>

    </div>

    {{-- Selected Plots Summary - Compact --}}
    <div class="mt-4 bg-gray-50 rounded-lg border border-gray-200 p-4">
      <h4 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Summary: All Activities
      </h4>

      <div class="space-y-2">
        <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
          <div 
            x-show="plotAssignments[actCode]?.length > 0 || getSelectedBlokForActivity(actCode)"
            class="bg-white rounded-lg p-3 border border-gray-200 hover:border-blue-300 transition-colors">
            <div class="flex items-start justify-between mb-2">
              <div class="flex-1">
                <span class="text-xs font-bold text-blue-600" x-text="actCode"></span>
                <p class="text-xs font-medium text-gray-800 mt-0.5" x-text="activity.name"></p>
                
                {{-- ✅ Show Blok for Blok Activity --}}
                <template x-if="activity.isblokactivity == 1 && getSelectedBlokForActivity(actCode)">
                  <div class="mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-50 text-purple-700 text-xs font-semibold rounded border border-purple-200">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                      </svg>
                      Blok: <span class="font-bold" x-text="getSelectedBlokForActivity(actCode)"></span>
                    </span>
                  </div>
                </template>
              </div>
              <span x-show="activity.isblokactivity != 1" class="text-sm font-bold text-blue-600" x-text="plotAssignments[actCode]?.length || 0"></span>
            </div>
            
            {{-- Plot Tags - Compact (only for non-blok activity) --}}
            <div x-show="activity.isblokactivity != 1" class="flex flex-wrap gap-1 mt-2">
              <template x-for="plot in (plotAssignments[actCode] || [])" :key="`${actCode}-${plot.blok}-${plot.plot}`">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 text-[10px] font-medium rounded-full border border-blue-200">
                  <span x-text="`${plot.blok}-${plot.plot}`"></span>
                  <button
                    type="button"
                    @click.stop="removePlotFromActivity(actCode, plot)"
                    class="hover:bg-blue-200 rounded-full p-0.5 transition-colors">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <div x-show="Object.values(plotAssignments).every(plots => !plots || plots.length === 0) && !hasAnyBlokActivitySelected()" 
           class="text-center py-6">
        <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <p class="text-gray-500 text-xs">No plots or bloks assigned yet</p>
      </div>
    </div>

  </div>

  {{-- ✅ VALIDATION WARNING --}}
  <div x-show="!canProceedStep2()" class="mt-4">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Selection Required</h3>
          <p class="text-xs text-yellow-700">Please assign plots to all activities (or select blok for blok activities).</p>
        </div>
      </div>
    </div>
  </div>

</div>