{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step1-activities.blade.php --}}

<div class="space-y-6">
  
  {{-- Header --}}
  <div class="text-center mb-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-2">Select Activities</h3>
    <p class="text-gray-600">Choose which activities you want to plan for this RKH</p>
  </div>

  {{-- Search Bar --}}
  <div class="max-w-2xl mx-auto mb-6">
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
      <input 
        type="text" 
        x-model="activitySearch"
        placeholder="Search activities by code or name..."
        class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
      >
    </div>
  </div>

  {{-- Selected Count --}}
  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 max-w-2xl mx-auto">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </div>
        <div>
          <p class="text-sm font-medium text-gray-600">Selected Activities</p>
          <p class="text-2xl font-bold text-blue-700" x-text="Object.keys(selectedActivities).length"></p>
        </div>
      </div>
      <button 
        type="button"
        @click="selectedActivities = {}"
        x-show="Object.keys(selectedActivities).length > 0"
        class="px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
        Clear All
      </button>
    </div>
  </div>

  {{-- Activities Grid --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-w-7xl mx-auto">
    <template x-for="activity in filteredActivities()" :key="activity.activitycode">
      <div 
        @click="toggleActivity(activity)"
        class="relative group cursor-pointer bg-white border-2 rounded-xl p-5 transition-all duration-200 hover:shadow-lg"
        :class="{
          'border-blue-500 bg-blue-50 shadow-md': selectedActivities[activity.activitycode],
          'border-gray-200 hover:border-blue-300': !selectedActivities[activity.activitycode]
        }">
        
        {{-- Checkbox --}}
        <div class="absolute top-4 right-4">
          <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all"
               :class="selectedActivities[activity.activitycode] ? 'bg-blue-500 border-blue-500' : 'border-gray-300 group-hover:border-blue-400'">
            <svg x-show="selectedActivities[activity.activitycode]" 
                 class="w-4 h-4 text-white" 
                 fill="none" 
                 stroke="currentColor" 
                 viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
        </div>

        {{-- Activity Code --}}
        <div class="mb-3">
          <span class="inline-block px-3 py-1 text-xs font-bold rounded-full"
                :class="selectedActivities[activity.activitycode] ? 'bg-blue-200 text-blue-800' : 'bg-gray-100 text-gray-700'"
                x-text="activity.activitycode">
          </span>
        </div>

        {{-- Activity Name --}}
        <h4 class="text-sm font-semibold text-gray-800 mb-3 pr-8 line-clamp-2 min-h-[40px]" 
            x-text="activity.activityname">
        </h4>

        {{-- Badges --}}
        <div class="flex flex-wrap gap-2">
          <template x-if="activity.usingmaterial == 1">
            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium bg-green-100 text-green-700 rounded">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
              </svg>
              Material
            </span>
          </template>
          
          <template x-if="activity.usingvehicle == 1">
            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium bg-orange-100 text-orange-700 rounded">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
              </svg>
              Vehicle
            </span>
          </template>

          <template x-if="activity.isblokactivity == 1">
            <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] font-medium bg-purple-100 text-purple-700 rounded">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
              </svg>
              Blok Activity
            </span>
          </template>
        </div>

      </div>
    </template>
  </div>

  {{-- Empty State --}}
  <div x-show="filteredActivities().length === 0" class="text-center py-12">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <p class="text-gray-500 text-lg font-medium">No activities found</p>
    <p class="text-gray-400 text-sm mt-1">Try adjusting your search</p>
  </div>

</div>