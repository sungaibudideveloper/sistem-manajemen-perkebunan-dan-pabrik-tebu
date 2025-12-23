{{-- resources/views/transaction/rencanakerjaharian/wizard-steps/step6-manpower.blade.php --}}

<div class="space-y-6">
  
  {{-- Header --}}
  <div class="text-center mb-8">
    <h3 class="text-2xl font-bold text-gray-800 mb-2">Input Manpower</h3>
    <p class="text-gray-600">Specify the number of workers (male/female) for each activity</p>
  </div>

  {{-- Summary Stats --}}
  <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white shadow-lg">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
      </div>
      <p class="text-blue-100 text-xs font-medium mb-1">Male Workers</p>
      <p class="text-3xl font-bold" x-text="getTotalWorkers('laki')"></p>
    </div>

    <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl p-5 text-white shadow-lg">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-8 h-8 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
      </div>
      <p class="text-pink-100 text-xs font-medium mb-1">Female Workers</p>
      <p class="text-3xl font-bold" x-text="getTotalWorkers('perempuan')"></p>
    </div>

    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-5 text-white shadow-lg">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-8 h-8 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
      </div>
      <p class="text-purple-100 text-xs font-medium mb-1">Total Workers</p>
      <p class="text-3xl font-bold" x-text="getTotalWorkers('total')"></p>
    </div>

    <div class="bg-gradient-to-br from-gray-700 to-gray-800 rounded-xl p-5 text-white shadow-lg">
      <div class="flex items-center justify-between mb-2">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
      </div>
      <p class="text-gray-300 text-xs font-medium mb-1">Activities</p>
      <p class="text-3xl font-bold" x-text="Object.keys(selectedActivities).length"></p>
    </div>
  </div>

  {{-- Manpower Input Cards --}}
  <div class="max-w-6xl mx-auto space-y-4">
    
    <template x-for="(activity, actCode) in selectedActivities" :key="actCode">
      <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        
        {{-- Activity Header --}}
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border-b border-gray-200 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 flex-1">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-bold text-indigo-600" x-text="actCode"></span>
                  <span 
                    class="inline-block px-2 py-0.5 text-[9px] font-bold rounded"
                    :class="{
                      'bg-blue-100 text-blue-700': activity.jenistenagakerja == 1,
                      'bg-green-100 text-green-700': activity.jenistenagakerja == 2,
                      'bg-orange-100 text-orange-700': activity.jenistenagakerja == 3,
                      'bg-purple-100 text-purple-700': activity.jenistenagakerja == 4
                    }"
                    x-text="getJenisLabel(activity.jenistenagakerja)">
                  </span>
                </div>
                <p class="text-sm font-semibold text-gray-800" x-text="activity.name"></p>
              </div>
            </div>
            
            {{-- Total Badge --}}
            <div class="text-right">
              <p class="text-xs text-gray-500 mb-1">Total</p>
              <div class="flex items-center gap-2">
                <span 
                  class="text-2xl font-bold"
                  :class="workers[actCode]?.total > 0 ? 'text-indigo-600' : 'text-gray-400'"
                  x-text="workers[actCode]?.total || 0">
                </span>
                <span class="text-sm text-gray-500">workers</span>
              </div>
            </div>
          </div>
        </div>

        {{-- Input Form --}}
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Male Input --}}
            <div class="bg-gradient-to-br from-blue-50 to-sky-50 rounded-xl p-5 border-2 border-blue-200">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-medium text-blue-600">Male Workers</p>
                  <p class="text-sm text-gray-500">Laki-laki</p>
                </div>
              </div>
              <input
                type="number"
                min="0"
                max="999"
                x-model="workers[actCode].laki"
                @input="updateWorkerTotal(actCode)"
                placeholder="0"
                class="w-full px-4 py-3 border-2 border-blue-300 rounded-lg text-2xl font-bold text-blue-700 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
              >
            </div>

            {{-- Female Input --}}
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 rounded-xl p-5 border-2 border-pink-200">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-pink-500 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-medium text-pink-600">Female Workers</p>
                  <p class="text-sm text-gray-500">Perempuan</p>
                </div>
              </div>
              <input
                type="number"
                min="0"
                max="999"
                x-model="workers[actCode].perempuan"
                @input="updateWorkerTotal(actCode)"
                placeholder="0"
                class="w-full px-4 py-3 border-2 border-pink-300 rounded-lg text-2xl font-bold text-pink-700 text-center focus:ring-2 focus:ring-pink-500 focus:border-pink-500 bg-white"
              >
            </div>

            {{-- Total Display --}}
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-5 border-2 border-purple-200">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-xs font-medium text-purple-600">Total Workers</p>
                  <p class="text-sm text-gray-500">Jumlah</p>
                </div>
              </div>
              <div class="w-full px-4 py-3 border-2 border-purple-300 rounded-lg text-2xl font-bold text-purple-700 text-center bg-white">
                <span x-text="workers[actCode]?.total || 0"></span>
              </div>
            </div>

          </div>

          {{-- Quick Suggestions (if available from absen data) --}}
          <div x-show="getAbsenSuggestion(actCode).total > 0" 
               class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-semibold text-yellow-800 mb-2">Today's Attendance Suggestion</p>
                <div class="flex items-center gap-3">
                  <p class="text-xs text-yellow-700">
                    L: <span class="font-bold" x-text="getAbsenSuggestion(actCode).laki"></span> • 
                    P: <span class="font-bold" x-text="getAbsenSuggestion(actCode).perempuan"></span> • 
                    Total: <span class="font-bold" x-text="getAbsenSuggestion(actCode).total"></span>
                  </p>
                  <button
                    type="button"
                    @click="applyAbsenSuggestion(actCode)"
                    class="px-3 py-1 text-xs font-medium bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors">
                    Apply
                  </button>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </template>

  </div>

  {{-- Validation Warning --}}
  <div x-show="!allWorkersCompleted()" class="max-w-5xl mx-auto">
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <div>
          <h3 class="text-sm font-semibold text-yellow-800 mb-1">Incomplete Information</h3>
          <p class="text-sm text-yellow-700">Please fill in the number of workers for all activities. You can input 0 if no workers are needed, but the field cannot be empty.</p>
        </div>
      </div>
    </div>
  </div>

</div>
