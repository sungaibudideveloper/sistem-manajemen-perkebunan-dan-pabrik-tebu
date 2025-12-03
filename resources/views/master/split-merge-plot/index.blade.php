{{-- resources/views/master/split-merge-plot/index.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="splitMergeData()"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    <!-- Header Actions -->
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <div class="flex gap-2">
        <button @click="openSplitWizard()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 12h12m-12 5h12M3 7h.01M3 12h.01M3 17h.01"/>
          </svg>
          Split Plot
        </button>
      
        <button @click="openMergeWizard()"
                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
          </svg>
          Merge Plot
        </button>
      </div>

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
        <input
          type="text"
          name="search"
          id="search"
          value="{{ request('search') }}"
          class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          onkeydown="if(event.key==='Enter') this.form.submit()"
        />
      </form>

      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select 
          name="perPage" id="perPage"
          onchange="this.form.submit()"
          class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
            <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>
    </div>

    <!-- Transaction History Table -->
    <div class="mx-auto px-4 py-2">
    <h3 class="text-lg font-semibold mb-3">Riwayat Transaksi Split & Merge</h3>
    <div class="overflow-x-auto border border-gray-300 rounded-md">
        <table class="min-w-full bg-white text-sm">
        <thead>
          <tr class="bg-gray-100 text-gray-700">
              <th class="py-2 px-3 border-b text-center" style="width: 3%;">No.</th>
              <th class="py-2 px-3 border-b text-center" style="width: 8%;">Transaction No</th>
              <th class="py-2 px-3 border-b text-center" style="width: 6%;">Tanggal</th>
              <th class="py-2 px-3 border-b text-center" style="width: 5%;">Tipe</th>
              <th class="py-2 px-3 border-b text-center" style="width: 8%;">Status Approval</th>
              <th class="py-2 px-3 border-b text-left" style="width: 12%;">Plot Asal</th>
              <th class="py-2 px-3 border-b text-left" style="width: 12%;">Plot Hasil (Area)</th>
              <th class="py-2 px-3 border-b text-left" style="width: 12%;">Batch Asal</th>
              <th class="py-2 px-3 border-b text-left" style="width: 12%;">Batch Hasil</th>
              <th class="py-2 px-3 border-b text-left" style="width: 8%;">Dominant</th>
              <th class="py-2 px-3 border-b text-left" style="width: 10%;">Alasan</th>
              <th class="py-2 px-3 border-b text-left" style="width: 6%;">Input By</th>
          </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $trx)
            <tr class="hover:bg-gray-50">
                <td class="py-2 px-3 border-b text-center">{{ $transactions->firstItem() + $index }}</td>
                
                <!-- Transaction Number -->
                <td class="py-2 px-3 border-b text-center">
                    <span class="font-mono text-sm font-semibold text-gray-700">{{ $trx->transactionnumber }}</span>
                </td>
                
                <td class="py-2 px-3 border-b text-center">{{ $trx->formatted_date }}</td>
                
                <!-- Tipe -->
                <td class="py-2 px-3 border-b text-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $trx->transactiontype === 'SPLIT' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                        {{ $trx->transactiontype }}
                    </span>
                </td>
                
                <!-- Status Approval - SIMPLIFIED -->
                <td class="py-2 px-3 border-b text-center">
                    @if($trx->approvalstatus === null)
                        @php
                            $total = $trx->jumlahapproval ?? 0;
                            $completed = 0;
                            if($trx->approval1flag == '1') $completed++;
                            if($trx->approval2flag == '1') $completed++;
                            if($trx->approval3flag == '1') $completed++;
                            $waitingText = $total == 0 ? "Pending" : "Pending ({$completed}/{$total})";
                        @endphp
                        <button @click="showApprovalDetailModal = true; selectedApprovalNo = '{{ $trx->approvalno }}'; loadApprovalDetail('{{ $trx->approvalno }}')"
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 hover:bg-yellow-200 cursor-pointer">
                            {{ $waitingText }}
                        </button>
                    @elseif($trx->approvalstatus === '1')
                        <button @click="showApprovalDetailModal = true; selectedApprovalNo = '{{ $trx->approvalno }}'; loadApprovalDetail('{{ $trx->approvalno }}')"
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer">
                            Approved
                        </button>
                    @elseif($trx->approvalstatus === '0')
                        <button @click="showApprovalDetailModal = true; selectedApprovalNo = '{{ $trx->approvalno }}'; loadApprovalDetail('{{ $trx->approvalno }}')"
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 hover:bg-red-200 cursor-pointer">
                            Declined
                        </button>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                            No Approval
                        </span>
                    @endif
                    
                    {{-- Approval Number as subtext --}}
                    @if($trx->approvalno)
                    <div class="text-xs text-gray-400 mt-1 font-mono">
                        {{ $trx->approvalno }}
                    </div>
                    @endif
                </td>
                
                <!-- Plot Asal dengan Area -->
                <td class="py-2 px-3 border-b text-left">
                    <ul class="text-xs space-y-0.5">
                        @foreach($trx->sourceplots_array as $plot)
                        @php
                            $plotArea = $trx->areamap_array[$plot] ?? null;
                        @endphp
                        <li>
                            • {{ $plot }}
                            @if($plotArea)
                            <span class="text-gray-500">({{ number_format($plotArea, 2) }} Ha)</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </td>
                
                <!-- Plot Hasil -->
                <td class="py-2 px-3 border-b text-left">
                  <ul class="text-xs space-y-0.5">
                      @foreach($trx->resultplots_array as $plot)
                      @php
                          $plotArea = $trx->areamap_array[$plot] ?? null;
                      @endphp
                      <li>
                          • <span class="font-medium">{{ $plot }}</span>
                          @if($plotArea)
                          <span class="text-gray-600">({{ number_format($plotArea, 2) }} Ha)</span>
                          @endif
                      </li>
                      @endforeach
                  </ul>
                </td>
                
                <!-- Batch Asal -->
                <td class="py-2 px-3 border-b text-left">
                    <ul class="text-xs space-y-0.5">
                        @foreach($trx->sourcebatches_array as $batch)
                        <li class="font-mono">• {{ $batch }}</li>
                        @endforeach
                    </ul>
                </td>
                
                <!-- Batch Hasil -->
                <td class="py-2 px-3 border-b text-left">
                    <ul class="text-xs space-y-0.5">
                        @foreach($trx->resultbatches_array as $batch)
                        <li class="font-mono">• {{ $batch }}</li>
                        @endforeach
                    </ul>
                </td>
                
                <!-- Dominant Plot -->
                <td class="py-2 px-3 border-b text-left">
                    <span class="text-sm font-semibold">{{ $trx->dominantplot }}</span>
                </td>
                
                <!-- Alasan -->
                <td class="py-2 px-3 border-b text-left">
                    <div class="text-xs" title="{{ $trx->splitmergedreason }}">
                        {{ Str::limit($trx->splitmergedreason ?? '-', 50) }}
                    </div>
                </td>
                
                <!-- Input By -->
                <td class="py-2 px-3 border-b text-left">
                    <div class="text-xs">
                        <div class="font-medium">{{ $trx->inputby }}</div>
                        <div class="text-gray-500">{{ $trx->formatted_createdat }}</div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="py-4 text-center text-gray-500">
                    Belum ada transaksi split/merge
                </td>
            </tr>
            @endforelse
        </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        @if ($transactions->hasPages())
        {{ $transactions->appends(request()->query())->links() }}
        @else
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-700">
                Showing <span class="font-medium">{{ $transactions->count() }}</span> of <span class="font-medium">{{ $transactions->total() }}</span> results
            </p>
        </div>
        @endif
    </div>
    </div>

    <!-- SPLIT WIZARD MODAL (3 Steps in 1 Modal) -->
    <div x-show="showSplitWizard" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showSplitWizard" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="closeSplitWizard()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showSplitWizard"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          
          <!-- Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white">Proses Split Plot</h2>
            <!-- Compact Stepper -->
            <div class="flex items-center mt-3 space-x-2">
              <template x-for="step in 3" :key="'step-' + step">
                <div class="flex items-center flex-1">
                  <div class="flex items-center w-full">
                    <div :class="splitStep >= step ? 'bg-white text-blue-600' : 'bg-blue-500 text-white'" 
                         class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-all">
                      <span x-text="step"></span>
                    </div>
                    <div x-show="step < 3" 
                         :class="splitStep > step ? 'bg-white' : 'bg-blue-500'" 
                         class="flex-1 h-0.5 mx-2 transition-all"></div>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Step Content -->
          <div class="bg-white px-6 py-5">
            
            <!-- STEP 1: Pilih Plot -->
            <div x-show="splitStep === 1">
              <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">Pilih Plot untuk Split</h3>
                <p class="text-sm text-gray-600">Pilih satu plot yang akan dipisah</p>
              </div>
              
              <div class="mb-3">
                <input 
                  type="text" 
                  x-model="batchSearchQuery"
                  placeholder="Cari plot..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <!-- Selected Plot Preview -->
              <div x-show="selectedBatch" class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <span class="text-sm font-medium text-gray-700">Terpilih: </span>
                    <span class="text-sm font-bold text-blue-700" x-text="selectedBatch?.plot"></span>
                    <span class="text-xs text-gray-600 ml-2">(<span x-text="selectedBatch ? parseFloat(selectedBatch.batcharea).toFixed(2) : 0"></span> Ha)</span>
                  </div>
                  <button @click="selectedBatch = null" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-4 gap-2 max-h-64 overflow-y-auto p-1">
                <template x-for="(batch, batchIdx) in filteredBatches" :key="'batch-' + batchIdx">
                  <button 
                    @click="selectedBatch = batch"
                    :class="selectedBatch?.batchno === batch.batchno ? 'border-blue-500 bg-blue-50 shadow-md' : 'border-gray-300 hover:border-blue-300'"
                    class="border-2 p-2 rounded-lg text-left text-sm transition-all">
                    <div class="font-semibold text-sm" x-text="batch.plot"></div>
                    <div class="text-xs text-gray-600 mt-0.5" x-text="parseFloat(batch.batcharea).toFixed(2) + ' Ha'"></div>
                    <div class="text-xs mt-1">
                      <span class="px-1.5 py-0.5 rounded text-xs bg-gray-200" x-text="batch.lifecyclestatus"></span>
                    </div>
                  </button>
                </template>
              </div>
            </div>

            <!-- STEP 2: Detail Plot & Confirm -->
            <div x-show="splitStep === 2">
              <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">Detail Plot & Batch Aktif</h3>
                <p class="text-sm text-gray-600">Verifikasi informasi plot sebelum melanjutkan</p>
              </div>
              
              <!-- Warning Box -->
              <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                <div class="flex">
                  <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  <p class="text-sm text-yellow-700">
                    <strong>Perhatian:</strong> Pastikan plot sudah selesai panen dan telah dilaksanakan Trash Muscler/Brushing sebelum melakukan split!
                  </p>
                </div>
              </div>
              
              <template x-if="selectedBatch">
                <div>
                  <!-- Batch Info -->
                  <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 p-4 rounded-lg mb-4 shadow-sm">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                      <div>
                        <span class="text-gray-600">Plot:</span> 
                        <span class="font-semibold text-gray-900" x-text="selectedBatch.plot"></span>
                      </div>
                      <div>
                        <span class="text-gray-600">Area:</span> 
                        <span class="font-semibold text-gray-900" x-text="parseFloat(selectedBatch.batcharea).toFixed(2)"></span> Ha
                      </div>
                      <div>
                        <span class="text-gray-600">Batch Aktif:</span> 
                        <span class="font-mono font-semibold text-blue-700" x-text="selectedBatch.batchno"></span>
                      </div>
                      <div>
                        <span class="text-gray-600">Lifecycle:</span> 
                        <span class="font-semibold text-gray-900" x-text="selectedBatch.lifecyclestatus"></span>
                      </div>
                      <div>
                        <span class="text-gray-600">Batch Date:</span> 
                        <span class="font-medium text-gray-900" x-text="selectedBatch.formatted_batchdate || '-'"></span>
                      </div>
                      <div>
                        <span class="text-gray-600">Tanggal Panen:</span> 
                        <span class="font-medium text-gray-900" x-text="selectedBatch.formatted_tanggalpanen || '-'"></span>
                      </div>
                    </div>
                  </div>

                  <!-- Last 5 Activities -->
                  <div class="mb-4">
                    <h4 class="font-semibold text-sm text-gray-900 mb-2">5 Activity Terakhir</h4>
                    <template x-if="lastActivities && lastActivities.length > 0">
                      <div class="space-y-1.5">
                        <template x-for="(activity, actIdx) in lastActivities" :key="'act-' + actIdx">
                          <div class="flex items-center justify-between bg-gray-50 border border-gray-200 px-3 py-2 rounded-lg text-sm">
                            <div class="flex-1">
                              <span class="font-medium text-gray-900" x-text="activity.formatted_date"></span>
                              <span class="text-gray-600 mx-1">·</span>
                              <span class="text-gray-700" x-text="activity.activityname"></span>
                            </div>
                            <div class="text-xs font-medium text-blue-600">
                              <span x-text="parseFloat(activity.luashasil).toFixed(2)"></span> Ha
                            </div>
                          </div>
                        </template>
                      </div>
                    </template>
                    <template x-if="!lastActivities || lastActivities.length === 0">
                      <p class="text-sm text-gray-500 italic">Belum ada activity</p>
                    </template>
                  </div>
                </div>
              </template>
            </div>

            <!-- STEP 3: Form Split -->
            <div x-show="splitStep === 3">
              <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">Form Split Plot</h3>
                <p class="text-sm text-gray-600">Tentukan pembagian area plot baru</p>
              </div>
              
              <template x-if="selectedBatch">
                <div>
                  <!-- Batch Info Summary -->
                  <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 p-3 rounded-lg mb-4 shadow-sm">
                    <div class="text-sm">
                      <span class="text-gray-600">Batch:</span> <span class="font-mono font-semibold text-blue-700" x-text="selectedBatch.batchno"></span>
                      <span class="text-gray-400 mx-2">|</span> 
                      <span class="text-gray-600">Plot:</span> <span class="font-semibold text-gray-900" x-text="selectedBatch.plot"></span>
                      <span class="text-gray-400 mx-2">|</span> 
                      <span class="text-gray-600">Area:</span> <span class="font-semibold text-gray-900" x-text="parseFloat(selectedBatch.batcharea).toFixed(2)"></span> Ha
                    </div>
                  </div>
                  
                  <!-- Split Rows -->
                  <div class="space-y-2 mb-4">
                      <template x-for="(split, splitIdx) in splits" :key="'split-row-' + splitIdx">
                          <div>
                              <div class="flex items-center gap-2">
                                  <span class="text-sm font-medium w-20" x-text="'Plot ' + (splitIdx + 1)"></span>
                                  
                                  <!-- Input Plot -->
                                  <div class="flex-1">
                                      <input 
                                          type="text"
                                          x-model="splits[splitIdx].plot"
                                          :disabled="splitIdx === 0"
                                          :placeholder="splitIdx === 0 ? 'Plot Original (Locked)' : 'Nama Plot Baru'"
                                          :class="splitIdx === 0 ? 'bg-gray-100 cursor-not-allowed' : (plotValidation[splitIdx]?.exists ? 'border-red-500' : (plotValidation[splitIdx]?.valid ? 'border-green-500' : 'border-gray-300'))"
                                          class="border rounded px-3 py-2 w-full"
                                          maxlength="5"
                                          style="text-transform: uppercase"
                                          @input="
                                              splits[splitIdx].plot = splits[splitIdx].plot.toUpperCase().replace(/[^A-Z0-9]/g, '');
                                              if(splitIdx > 0) validatePlotName(splits[splitIdx].plot, splitIdx)
                                          "
                                      />
                                  </div>
                                  
                                  <!-- Input Area -->
                                  <input 
                                      type="number"
                                      step="0.01"
                                      x-model="splits[splitIdx].area"
                                      placeholder="0.00"
                                      :disabled="splitIdx > 0 && (!plotValidation[splitIdx]?.valid || plotValidation[splitIdx]?.checking)"
                                      :class="(splitIdx > 0 && (!plotValidation[splitIdx]?.valid || plotValidation[splitIdx]?.checking)) ? 'bg-gray-100 cursor-not-allowed' : ''"
                                      class="border border-gray-300 rounded px-3 py-2 w-32"
                                  />
                                  <span class="text-sm text-gray-600">Ha</span>
                                  
                                  <!-- Delete Button - always reserve space -->
                                  <div class="w-8">
                                      <button 
                                          @click="removeSplitRow(splitIdx)"
                                          x-show="splits.length > 2 && splitIdx > 0"
                                          class="text-red-600 hover:text-red-800">
                                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                          </svg>
                                      </button>
                                  </div>
                              </div>
                              
                              <!-- Validation Message - below the row -->
                              <div x-show="splitIdx > 0 && plotValidation[splitIdx]" class="text-xs mt-1 ml-24">
                                  <span x-show="plotValidation[splitIdx]?.checking" class="text-blue-600">
                                      ⏳ <span x-text="plotValidation[splitIdx]?.message"></span>
                                  </span>
                                  <span x-show="!plotValidation[splitIdx]?.checking && plotValidation[splitIdx]?.exists" 
                                        class="text-red-600 font-medium">
                                      ❌ <span x-text="plotValidation[splitIdx]?.message"></span>
                                  </span>
                                  <span x-show="!plotValidation[splitIdx]?.checking && plotValidation[splitIdx]?.valid" 
                                        class="text-green-600 font-medium">
                                      <span x-text="plotValidation[splitIdx]?.message"></span>
                                  </span>
                                  <span x-show="!plotValidation[splitIdx]?.checking && !plotValidation[splitIdx]?.valid && !plotValidation[splitIdx]?.exists && plotValidation[splitIdx]?.message" 
                                        class="text-orange-600 font-medium">
                                      ⚠️ <span x-text="plotValidation[splitIdx]?.message"></span>
                                  </span>
                              </div>
                          </div>
                      </template>
                  </div>
                  
                  <div class="flex items-center justify-between mb-4">
                    <button 
                      @click="addSplitRow()"
                      class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                      + Tambah Plot
                    </button>
                    <div class="text-sm font-medium">
                      Total: <span x-text="totalSplitArea.toFixed(2)"></span> Ha
                      <span 
                        x-show="selectedBatch && Math.abs(totalSplitArea - parseFloat(selectedBatch.batcharea)) > 0.01"
                        class="text-red-600 ml-2">
                        (Harus sama dengan area original: <span x-text="parseFloat(selectedBatch.batcharea).toFixed(2)"></span> Ha)
                      </span>
                    </div>
                  </div>

                  <!-- Alasan Split -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Split</label>
                    <textarea 
                      x-model="reason"
                      rows="3"
                      placeholder="Contoh: Split after trashmuscler 3 Ha"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                    </textarea>
                    <p class="text-xs text-gray-500 mt-1">
                      <strong>Plot Dominan:</strong> <span x-text="splits[0]?.plot || selectedBatch.plot"></span> (otomatis dari plot original)
                    </p>
                  </div>
                </div>
              </template>
            </div>

          </div>

          <!-- SPLIT MODAL Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-between border-t">
                <div>
                    <button 
                        @click="splitStep > 1 ? splitStep-- : closeSplitWizard()" 
                        type="button"
                        :disabled="loading"
                        class="px-5 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-text="splitStep === 1 ? 'Tutup' : 'Kembali'"></span>
                    </button>
                </div>
                
                <div>
                    <button 
                        x-show="splitStep < 3"
                        @click="goToNextSplitStep()"
                        :disabled="(splitStep === 1 && !selectedBatch) || loading"
                        type="button"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all flex items-center gap-2">
                        <!-- Loading Spinner -->
                        <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Loading...' : (splitStep === 2 ? 'Konfirmasi & Lanjutkan' : 'Lanjutkan')"></span>
                    </button>
                    
                    <button 
                      x-show="splitStep === 3"
                      @click="submitSplit()"
                      :disabled="loading || splits.length < 2 || !allPlotsValid"
                      type="button"
                      class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all flex items-center gap-2">
                      <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span x-show="!loading">Proses Split</span>
                      <span x-show="loading">Processing...</span>
                    </button>
                </div>
            </div>

        </div>
      </div>
    </div>

    <!-- MERGE WIZARD MODAL (3 Steps in 1 Modal) - Dengan style yang sama -->
    <div x-show="showMergeWizard" x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showMergeWizard" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="closeMergeWizard()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showMergeWizard"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
          
          <!-- Header -->
          <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white">Proses Merge Plot</h2>
            <!-- Compact Stepper -->
            <div class="flex items-center mt-3 space-x-2">
              <template x-for="step in 3" :key="'merge-step-' + step">
                <div class="flex items-center flex-1">
                  <div class="flex items-center w-full">
                    <div :class="mergeStep >= step ? 'bg-white text-green-600' : 'bg-green-500 text-white'" 
                         class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold transition-all">
                      <span x-text="step"></span>
                    </div>
                    <div x-show="step < 3" 
                         :class="mergeStep > step ? 'bg-white' : 'bg-green-500'" 
                         class="flex-1 h-0.5 mx-2 transition-all"></div>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Step Content -->
          <div class="bg-white px-6 py-5">
            
            <!-- STEP 1: Pilih Plot (bisa pilih multiple) -->
            <div x-show="mergeStep === 1">
              <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">Pilih Plot untuk Merge</h3>
                <p class="text-sm text-gray-600">Pilih minimal 2 plot yang akan digabungkan</p>
              </div>
              
              <div class="mb-3">
                <input 
                  type="text" 
                  x-model="batchSearchQuery"
                  placeholder="Cari plot..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                />
              </div>

              <!-- Selected Batches Preview -->
              <div x-show="mergeBatches.length > 0" class="mb-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                <div class="mb-2">
                  <span class="text-sm font-medium text-gray-700">Terpilih (<span x-text="mergeBatches.length"></span>):</span>
                </div>
                <div class="flex flex-wrap gap-2">
                  <template x-for="(batch, idx) in mergeBatches" :key="'selected-' + idx">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-green-100 text-green-800 font-medium">
                      <span x-text="batch.plot"></span>
                      <button @click="removeBatchFromMerge(idx)" class="ml-1.5 hover:text-green-900">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>
                    </span>
                  </template>
                </div>
              </div>

              <div class="grid grid-cols-4 gap-2 max-h-64 overflow-y-auto p-1">
                <template x-for="(batch, batchIdx) in filteredBatches" :key="'batch-merge-' + batchIdx">
                  <button 
                    @click="toggleBatchForMerge(batch)"
                    :class="mergeBatches.find(b => b.batchno === batch.batchno) ? 'border-green-500 bg-green-50 shadow-md' : 'border-gray-300 hover:border-green-300'"
                    class="border-2 p-2 rounded-lg text-left text-sm transition-all">
                    <div class="font-semibold text-sm" x-text="batch.plot"></div>
                    <div class="text-xs text-gray-600 mt-0.5" x-text="parseFloat(batch.batcharea).toFixed(2) + ' Ha'"></div>
                    <div class="text-xs mt-1">
                      <span class="px-1.5 py-0.5 rounded text-xs bg-gray-200" x-text="batch.lifecyclestatus"></span>
                    </div>
                  </button>
                </template>
              </div>
            </div>

            <!-- STEP 2: Detail Batches yang dipilih -->
            <div x-show="mergeStep === 2">
                <div class="mb-4">
                    <h3 class="text-base font-semibold text-gray-900 mb-1">Detail Batch yang akan di-Merge</h3>
                    <p class="text-sm text-gray-600">Verifikasi informasi sebelum melanjutkan</p>
                </div>
                
                <!-- Warning Box -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-yellow-700">
                            <strong>Perhatian:</strong> Pastikan plot sudah selesai panen dan telah dilaksanakan Trash Muscler/Brushing sebelum melakukan merge!
                        </p>
                    </div>
                </div>
                
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-8">
                    <svg class="animate-spin h-8 w-8 mx-auto text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-600 mt-2">Loading activities...</p>
                </div>
                
                <!-- Batch Details with Activities -->
                <div x-show="!loading" class="space-y-3">
                    <template x-for="(batch, idx) in mergeBatches" :key="'detail-merge-' + idx">
                        <div class="border-2 border-green-200 bg-white rounded-lg shadow-sm overflow-hidden">
                            <!-- Batch Header -->
                            <div class="bg-gradient-to-br from-green-50 to-green-100 p-3 border-b border-green-200">
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">Plot:</span> 
                                        <span class="font-bold text-gray-900 text-base" x-text="batch.plot"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Area:</span> 
                                        <span class="font-semibold text-gray-900" x-text="parseFloat(batch.batcharea).toFixed(2)"></span> Ha
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Batch:</span> 
                                        <span class="font-mono font-semibold text-green-700 text-xs" x-text="batch.batchno"></span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Lifecycle:</span> 
                                        <span class="font-semibold text-gray-900" x-text="batch.lifecyclestatus"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Last 5 Activities (Compact) -->
                            <div class="p-3 bg-gray-50">
                                <h5 class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Last 5 Activities</h5>
                                <template x-if="mergeActivities[batch.batchno] && mergeActivities[batch.batchno].length > 0">
                                    <div class="space-y-1">
                                        <template x-for="(activity, actIdx) in mergeActivities[batch.batchno]" :key="'merge-act-' + batch.batchno + '-' + actIdx">
                                            <div class="flex items-center justify-between text-xs py-1.5 px-2 bg-white rounded border border-gray-200">
                                                <div class="flex-1 flex items-center gap-2">
                                                    <span class="font-medium text-gray-900 w-20" x-text="activity.formatted_date"></span>
                                                    <span class="text-gray-400">·</span>
                                                    <span class="text-gray-700 flex-1 truncate" x-text="activity.activityname" :title="activity.activityname"></span>
                                                </div>
                                                <span class="font-semibold text-green-600 ml-2 whitespace-nowrap" x-text="parseFloat(activity.luashasil).toFixed(2) + ' Ha'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!mergeActivities[batch.batchno] || mergeActivities[batch.batchno].length === 0">
                                    <p class="text-xs text-gray-500 italic py-2">Belum ada activity</p>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Total Summary -->
                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Total Area Merge:</span> 
                        <span class="text-lg font-bold text-blue-700" x-text="totalMergeArea.toFixed(2) + ' Ha'"></span>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Form Merge -->
            <div x-show="mergeStep === 3">
              <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-1">Form Merge Plot</h3>
                <p class="text-sm text-gray-600">Tentukan plot dominan dan alasan merge</p>
              </div>
              
              <!-- Summary -->
              <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 p-3 rounded-lg mb-4 shadow-sm">
                <div class="text-sm">
                  <span class="text-gray-600">Merge:</span> 
                  <template x-for="(batch, idx) in mergeBatches" :key="'summary-' + idx">
                    <span>
                      <span class="font-semibold text-gray-900" x-text="batch.plot"></span><span x-show="idx < mergeBatches.length - 1" class="text-gray-600"> + </span>
                    </span>
                  </template>
                  <span class="text-gray-400 mx-2">|</span> 
                  <span class="text-gray-600">Total:</span> <span class="font-bold text-gray-900" x-text="totalMergeArea.toFixed(2)"></span> Ha
                </div>
              </div>

              <!-- Dominant Plot & Reason -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Plot Dominan</label>
                  <select 
                    x-model="dominantPlot"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">-- Pilih Plot Dominan --</option>
                    <template x-for="(batch, domMergeIdx) in mergeBatches" :key="'dom-merge-' + domMergeIdx">
                      <option :value="batch.plot" x-text="batch.plot"></option>
                    </template>
                  </select>
                  <p class="text-xs text-gray-500 mt-1">Plot yang akan mewarisi data historis</p>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Merge</label>
                  <textarea 
                    x-model="reason"
                    rows="3"
                    placeholder="Contoh: Merge untuk efisiensi pengelolaan"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                  </textarea>
                </div>
              </div>
            </div>

          </div>

          <!-- MERGE MODAL Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-between border-t">
                <div>
                    <button 
                        @click="mergeStep > 1 ? mergeStep-- : closeMergeWizard()" 
                        type="button"
                        :disabled="loading"
                        class="px-5 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-text="mergeStep === 1 ? 'Tutup' : 'Kembali'"></span>
                    </button>
                </div>
                
                <div>
                    <button 
                        x-show="mergeStep < 3"
                        @click="goToNextMergeStep()"
                        :disabled="(mergeStep === 1 && mergeBatches.length < 2) || loading"
                        type="button"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all flex items-center gap-2">
                        <!-- Loading Spinner -->
                        <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="loading ? 'Loading...' : (mergeStep === 2 ? 'Konfirmasi & Lanjutkan' : 'Lanjutkan')"></span>
                    </button>
                    
                    <button 
                        x-show="mergeStep === 3"
                        @click="submitMerge()"
                        :disabled="loading || mergeBatches.length < 2 || !dominantPlot"
                        type="button"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium shadow-sm transition-all flex items-center gap-2">
                        <svg x-show="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading">Proses Merge</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
            </div>

        </div>
      </div>
    </div>
  

    <!-- Approval Detail Modal -->
    <div x-show="showApprovalDetailModal" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto" 
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showApprovalDetailModal" 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
            @click="showApprovalDetailModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showApprovalDetailModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          
          <!-- Modal Header -->
          <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-3 sm:px-6">
            <div class="flex items-center justify-between">
              <h3 class="text-lg leading-6 font-medium text-white">
                Approval Detail
              </h3>
              <button @click="showApprovalDetailModal = false" class="text-white hover:text-gray-200">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
            <div x-show="isLoadingApprovalDetail" class="text-center py-4">
              <svg class="animate-spin h-8 w-8 mx-auto text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="text-sm text-gray-600 mt-2">Loading...</p>
            </div>

            <div x-show="!isLoadingApprovalDetail">
              <!-- Approval Info -->
              <div class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                <div class="text-sm">
                  <span class="font-semibold text-gray-700">Approval No:</span>
                  <span class="ml-2 font-mono text-purple-700" x-text="approvalDetail.approvalno"></span>
                </div>
                <div class="text-sm mt-1">
                  <span class="font-semibold text-gray-700">Transaction:</span>
                  <span class="ml-2 text-gray-900" x-text="approvalDetail.transactionnumber"></span>
                </div>
                <div class="text-sm mt-1">
                  <span class="font-semibold text-gray-700">Category:</span>
                  <span class="ml-2 text-gray-900" x-text="approvalDetail.category"></span>
                </div>
              </div>

              <!-- Approval Levels -->
              <div class="space-y-3">
                <template x-if="approvalDetail.jumlahapproval >= 1">
                  <div class="p-3 rounded-lg border" 
                      :class="approvalDetail.approval1flag === '1' ? 'bg-green-50 border-green-200' : 
                              approvalDetail.approval1flag === '0' ? 'bg-red-50 border-red-200' : 
                              'bg-gray-50 border-gray-200'">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="text-sm font-semibold text-gray-700">Approval Level 1</div>
                        <div class="text-xs text-gray-600 mt-1" x-text="approvalDetail.jabatan1_name || '-'"></div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval1userid">
                          By: <span x-text="approvalDetail.approval1_username"></span>
                        </div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval1date">
                          Date: <span x-text="approvalDetail.approval1date"></span>
                        </div>
                      </div>
                      <div>
                        <span x-show="approvalDetail.approval1flag === '1'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          ✓ Approved
                        </span>
                        <span x-show="approvalDetail.approval1flag === '0'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          ✗ Declined
                        </span>
                        <span x-show="approvalDetail.approval1flag === null" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                          ○ Pending
                        </span>
                      </div>
                    </div>
                  </div>
                </template>

                <template x-if="approvalDetail.jumlahapproval >= 2">
                  <div class="p-3 rounded-lg border" 
                      :class="approvalDetail.approval2flag === '1' ? 'bg-green-50 border-green-200' : 
                              approvalDetail.approval2flag === '0' ? 'bg-red-50 border-red-200' : 
                              'bg-gray-50 border-gray-200'">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="text-sm font-semibold text-gray-700">Approval Level 2</div>
                        <div class="text-xs text-gray-600 mt-1" x-text="approvalDetail.jabatan2_name || '-'"></div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval2userid">
                          By: <span x-text="approvalDetail.approval2_username"></span>
                        </div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval2date">
                          Date: <span x-text="approvalDetail.approval2date"></span>
                        </div>
                      </div>
                      <div>
                        <span x-show="approvalDetail.approval2flag === '1'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          ✓ Approved
                        </span>
                        <span x-show="approvalDetail.approval2flag === '0'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          ✗ Declined
                        </span>
                        <span x-show="approvalDetail.approval2flag === null" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                          ○ Pending
                        </span>
                      </div>
                    </div>
                  </div>
                </template>

                <template x-if="approvalDetail.jumlahapproval >= 3">
                  <div class="p-3 rounded-lg border" 
                      :class="approvalDetail.approval3flag === '1' ? 'bg-green-50 border-green-200' : 
                              approvalDetail.approval3flag === '0' ? 'bg-red-50 border-red-200' : 
                              'bg-gray-50 border-gray-200'">
                    <div class="flex items-center justify-between">
                      <div>
                        <div class="text-sm font-semibold text-gray-700">Approval Level 3</div>
                        <div class="text-xs text-gray-600 mt-1" x-text="approvalDetail.jabatan3_name || '-'"></div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval3userid">
                          By: <span x-text="approvalDetail.approval3_username"></span>
                        </div>
                        <div class="text-xs text-gray-500" x-show="approvalDetail.approval3date">
                          Date: <span x-text="approvalDetail.approval3date"></span>
                        </div>
                      </div>
                      <div>
                        <span x-show="approvalDetail.approval3flag === '1'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          ✓ Approved
                        </span>
                        <span x-show="approvalDetail.approval3flag === '0'" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          ✗ Declined
                        </span>
                        <span x-show="approvalDetail.approval3flag === null" 
                              class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                          ○ Pending
                        </span>
                      </div>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button @click="showApprovalDetailModal = false" 
                    type="button"
                    class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function splitMergeData() {
      return {
        // Wizard states
        showSplitWizard: false,
        showMergeWizard: false,
        splitStep: 1,
        mergeStep: 1,
        
        // Data
        activeBatches: @json($activeBatches),
        batchSearchQuery: '',
        selectedBatch: null,
        lastActivities: [],
        mergeBatches: [],
        mergeActivities: {},
        splits: [],
        dominantPlot: '',
        reason: '',
        loading: false,

        // Approval Modal
        showApprovalDetailModal: false,
        selectedApprovalNo: '',
        approvalDetail: {},
        isLoadingApprovalDetail: false,
        
        // Computed
        get filteredBatches() {
          if (!this.batchSearchQuery) return this.activeBatches;
          
          const query = this.batchSearchQuery.toLowerCase();
          return this.activeBatches.filter(batch => 
            batch.batchno.toLowerCase().includes(query) ||
            batch.plot.toLowerCase().includes(query)
          );
        },
        
        get totalSplitArea() {
          return this.splits.reduce((sum, split) => sum + parseFloat(split.area || 0), 0);
        },
        
        get totalMergeArea() {
          return this.mergeBatches.reduce((sum, batch) => sum + parseFloat(batch.batcharea || 0), 0);
        },
        
        // Split Methods
        openSplitWizard() {
          this.showSplitWizard = true;
          this.splitStep = 1;
          this.selectedBatch = null;
          this.lastActivities = [];
          this.splits = [];
          this.reason = '';
          this.batchSearchQuery = '';
        },
        
        closeSplitWizard() {
          this.showSplitWizard = false;
          this.splitStep = 1;
          this.selectedBatch = null;
          this.lastActivities = [];
          this.splits = [];
          this.reason = '';
        },
        
        async goToNextSplitStep() {
            if (this.splitStep === 1 && this.selectedBatch) {
                // Load batch details when going to step 2
                this.loading = true;
                try {
                    const response = await fetch(`{{ url('masterdata/split-merge-plot/batch') }}/${this.selectedBatch.batchno}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.lastActivities = data.data.last_activities || [];
                        this.splitStep = 2;
                    } else {
                        alert(data.message);
                    }
                } catch (error) {
                    console.error('Error loading batch:', error);
                    alert('Gagal memuat detail batch');
                } finally {
                    this.loading = false;
                }
            } else if (this.splitStep === 2) {
                // Initialize splits when going to step 3
                this.splits = [
                    { plot: this.selectedBatch.plot, area: 0 },
                    { plot: '', area: 0 }
                ];
                this.splitStep = 3;
            }
        },
        
        addSplitRow() {
          this.splits.push({ plot: '', area: 0 });
        },
        
        removeSplitRow(index) {
          if (this.splits.length > 2 && index > 0) {
            this.splits.splice(index, 1);
          }
        },
        
        async submitSplit() {
          if (!this.selectedBatch) {
              alert('Pilih batch terlebih dahulu');
              return;
          }
          
          if (this.splits.length < 2) {
              alert('Minimal 2 plot hasil split');
              return;
          }
          
          // CEK VALIDASI PLOT NAME
          if (this.hasInvalidPlots) {
              alert('Ada nama plot yang sudah digunakan! Gunakan nama plot lain.');
              return;
          }
          
          // Cek apakah ada plot yang belum divalidasi
          for (let i = 1; i < this.splits.length; i++) {
              if (!this.splits[i].plot) {
                  alert('Semua plot harus memiliki nama');
                  return;
              }
              if (this.plotValidation[i]?.checking) {
                  alert('Tunggu proses validasi plot selesai');
                  return;
              }
          }
          
          const totalArea = this.totalSplitArea;
          const originalArea = parseFloat(this.selectedBatch.batcharea);
          
          if (Math.abs(totalArea - originalArea) > 0.01) {
              alert(`Total area split (${totalArea.toFixed(2)} Ha) harus sama dengan area batch original (${originalArea.toFixed(2)} Ha)`);
              return;
          }
          
          this.loading = true;
          
          try {
              const response = await fetch('{{ url("masterdata/split-merge-plot/split") }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                  },
                  body: JSON.stringify({
                      source_batchno: this.selectedBatch.batchno,
                      splits: this.splits,
                      dominant_plot: this.splits[0].plot,
                      reason: this.reason
                  })
              });
              
              const data = await response.json();
              
              if (data.success) {
                  alert(data.message);
                  window.location.reload();
              } else {
                  alert(data.message);
              }
          } catch (error) {
              console.error('Error:', error);
              alert('Terjadi kesalahan saat memproses split');
          } finally {
              this.loading = false;
          }
        },
        
        // Merge Methods
        openMergeWizard() {
            this.showMergeWizard = true;
            this.mergeStep = 1;
            this.mergeBatches = [];
            this.mergeActivities = {}; // Tambahkan ini untuk store activities per batch
            this.dominantPlot = '';
            this.reason = '';
            this.batchSearchQuery = '';
        },

        closeMergeWizard() {
            this.showMergeWizard = false;
            this.mergeStep = 1;
            this.mergeBatches = [];
            this.mergeActivities = {}; // Reset activities
            this.dominantPlot = '';
            this.reason = '';
        },

        async goToNextMergeStep() {
            if (this.mergeStep === 1 && this.mergeBatches.length >= 2) {
                // Load activities untuk semua batch yang dipilih
                this.loading = true;
                try {
                    for (const batch of this.mergeBatches) {
                        const response = await fetch(`{{ url('masterdata/split-merge-plot/batch') }}/${batch.batchno}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.mergeActivities[batch.batchno] = data.data.last_activities || [];
                        }
                    }
                    this.mergeStep = 2;
                } catch (error) {
                    console.error('Error loading activities:', error);
                    alert('Gagal memuat detail activities');
                } finally {
                    this.loading = false;
                }
            } else if (this.mergeStep === 2) {
                this.mergeStep = 3;
            }
        },
        
        toggleBatchForMerge(batch) {
          const index = this.mergeBatches.findIndex(b => b.batchno === batch.batchno);
          if (index > -1) {
            this.mergeBatches.splice(index, 1);
          } else {
            this.mergeBatches.push(batch);
          }
        },
        
        removeBatchFromMerge(index) {
          this.mergeBatches.splice(index, 1);
        },
        
        async submitMerge() {
          if (this.mergeBatches.length < 2) {
            alert('Minimal 2 batch untuk di-merge');
            return;
          }
          
          if (!this.dominantPlot) {
            alert('Pilih plot dominan');
            return;
          }
          
          const resultPlot = this.dominantPlot;
          
          this.loading = true;
          
          try {
            const response = await fetch('{{ url("masterdata/split-merge-plot/merge") }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                source_batches: this.mergeBatches.map(b => b.batchno),
                result_plot: resultPlot,
                dominant_plot: this.dominantPlot,
                reason: this.reason
              })
            });
            
            const data = await response.json();
            
            if (data.success) {
              alert(data.message);
              window.location.reload();
            } else {
              alert(data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses merge');
          } finally {
            this.loading = false;
          }
        },

        async loadApprovalDetail(approvalno) {
          this.isLoadingApprovalDetail = true;
          this.approvalDetail = {};
          
          try {
            const response = await fetch(`{{ url('masterdata/split-merge-plot/approval') }}/${approvalno}`);
            const data = await response.json();
            
            if (data.success) {
              this.approvalDetail = data.data;
            } else {
              alert('Gagal memuat detail approval: ' + data.message);
            }
          } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat detail approval');
          } finally {
            this.isLoadingApprovalDetail = false;
          }
        },

        
        // Plot validation
        plotValidation: {}, // { plotName: { checking: false, exists: false, message: '' } }
        validationTimer: null,

        // Method untuk validasi plot name real-time
        async validatePlotName(plotName, index) {
          if (!plotName || plotName.length < 2) {
              this.plotValidation[index] = { checking: false, exists: false, message: '', valid: false };
              return;
          }
          
          // Debounce - tunggu user selesai ngetik
          clearTimeout(this.validationTimer);
          this.plotValidation[index] = { checking: true, exists: false, message: 'Checking...', valid: false };
          
          this.validationTimer = setTimeout(async () => {
              try {
                  // FIX: Gunakan Laravel URL helper
                  const response = await fetch(`{{ url('masterdata/split-merge-plot/check-plot') }}?plot=${encodeURIComponent(plotName)}`);
                  
                  // CEK APAKAH RESPONSE VALID JSON
                  const contentType = response.headers.get('content-type');
                  if (!contentType || !contentType.includes('application/json')) {
                      throw new Error('Response bukan JSON. Kemungkinan session expired atau tidak ada akses.');
                  }
                  
                  const data = await response.json();
                  
                  if (data.error) {
                      this.plotValidation[index] = { 
                          checking: false, 
                          exists: false, 
                          message: data.message || 'Error validasi',
                          valid: false
                      };
                  } else if (data.exists) {
                      this.plotValidation[index] = { 
                          checking: false, 
                          exists: true, 
                          message: `Plot ${plotName} sudah ada! Gunakan nama lain.`,
                          valid: false
                      };
                  } else {
                      this.plotValidation[index] = { 
                          checking: false, 
                          exists: false, 
                          message: `✓ Plot ${plotName} tersedia`,
                          valid: true
                      };
                  }
              } catch (error) {
                  console.error('Error validating plot:', error);
                  this.plotValidation[index] = { 
                      checking: false, 
                      exists: false, 
                      message: 'Error validasi - Cek koneksi atau reload page',
                      valid: false
                  };
              }
          }, 500); // 500ms debounce
        },

        get hasInvalidPlots() {
            return Object.values(this.plotValidation).some(v => v.exists || !v.valid);
        },

        get allPlotsValid() {
            // Cek apakah semua plot baru sudah valid
            for (let i = 1; i < this.splits.length; i++) {
                if (!this.splits[i].plot) return false;
                if (!this.plotValidation[i]) return false;
                if (this.plotValidation[i].checking) return false;
                if (!this.plotValidation[i].valid) return false;
            }
            return true;
        }




      }
    }
  </script>
</x-layout>