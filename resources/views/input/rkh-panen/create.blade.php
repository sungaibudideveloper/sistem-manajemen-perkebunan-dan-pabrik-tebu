{{-- resources/views/input/rkh-panen/create.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- Choices.js CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

  <!-- Success Modal -->
  <div x-data="{ showModal: false, modalMessage: '' }" 
       x-show="showModal" 
       x-cloak
       class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
       style="display: none;">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
      <div class="p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Berhasil!</h3>
        <p class="text-sm text-gray-600 mb-4" x-html="modalMessage"></p>
        <button @click="window.location.href = '{{ route('input.rkh-panen.index') }}'"
                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          OK
        </button>
      </div>
    </div>
  </div>

  <!-- ⭐ PLOT SELECTION MODAL -->
  <div id="plotModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
      <!-- Modal Header -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-t-xl flex justify-between items-center">
        <div>
          <h3 class="text-lg font-bold">Pilih Lokasi Plot</h3>
          <p class="text-xs mt-1 opacity-90">Pilih satu atau lebih plot untuk kontraktor ini</p>
        </div>
        <button onclick="closePlotModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6 overflow-y-auto flex-1">
        <!-- Search Box -->
        <div class="mb-4">
          <div class="relative">
            <input type="text" 
                   id="plotSearchInput" 
                   placeholder="Cari plot (contoh: A001, B-002)..." 
                   class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                   oninput="filterPlots()">
            <svg class="absolute left-3 top-3 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>

        <!-- Selected Counter -->
        <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-blue-800">Plot Terpilih:</span>
            <span id="selectedCount" class="text-lg font-bold text-blue-600">0</span>
          </div>
        </div>

        <!-- Plot Grid (Grouped by Blok) -->
        <div id="plotGrid" class="space-y-4">
          <!-- Will be populated by JS -->
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center bg-gray-50 rounded-b-xl">
        <button onclick="clearPlotSelection()" 
                class="text-sm text-gray-600 hover:text-gray-800 font-medium">
          Hapus Semua
        </button>
        <div class="flex gap-3">
          <button onclick="closePlotModal()" 
                  class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg font-medium transition-colors">
            Batal
          </button>
          <button onclick="confirmPlotSelection()" 
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
            Simpan Pilihan
          </button>
        </div>
      </div>
    </div>
  </div>

  <form id="rkh-panen-form" action="{{ route('input.rkh-panen.store') }}" method="POST">
    @csrf

    @if ($errors->any())
      <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-lg shadow-sm">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">
              Terdapat {{ $errors->count() }} kesalahan yang perlu diperbaiki:
            </h3>
            <div class="mt-2 text-sm text-red-700">
              <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    @endif

    <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-blue-100">
      
      <!-- Header Info -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          
          <!-- Tanggal (Read-only) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
            <input type="date" 
                   name="rkhdate" 
                   value="{{ $selectedDate }}" 
                   readonly
                   class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-sm font-medium cursor-not-allowed">
          </div>

          <!-- Mandor Panen -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Mandor Panen</label>
            <select name="mandorpanenid" 
                    required
                    class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Pilih Mandor</option>
              @foreach($mandorPanen as $mandor)
                <option value="{{ $mandor->userid }}" {{ old('mandorpanenid') == $mandor->userid ? 'selected' : '' }}>
                  {{ $mandor->userid }} - {{ $mandor->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Keterangan -->
        <div class="mt-4">
          <label class="block text-sm font-semibold text-gray-700 mb-1">
            Keterangan <span class="text-xs text-gray-500 font-normal">(opsional)</span>
          </label>
          <textarea name="keterangan" 
                    rows="2"
                    maxlength="500"
                    placeholder="Catatan tambahan untuk RKH Panen ini..."
                    class="w-full text-sm border-2 border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('keterangan') }}</textarea>
        </div>
      </div>

      <!-- Section 1: Rencana Panen per Kontraktor -->
      <div class="bg-white rounded-xl border border-gray-300 shadow-md">
        <div class="bg-gradient-to-r from-green-700 to-green-600 text-white px-6 py-3 rounded-t-xl">
          <h3 class="text-lg font-bold">Section 1: Rencana Panen (Kontraktor)</h3>
        </div>

        <div class="p-6">
          <div class="overflow-x-auto">
            <table id="kontraktor-table" class="min-w-full divide-y divide-gray-200 border border-gray-300">
              <thead class="bg-gray-100">
                <!-- 2-LEVEL HEADER -->
                <tr>
                  <th rowspan="2" class="px-3 py-3 text-xs font-semibold text-gray-700 text-center border-r border-gray-300">Kontraktor</th>
                  <th rowspan="2" class="px-3 py-3 text-xs font-semibold text-gray-700 text-center border-r border-gray-300">Jenis<br>Panen</th>
                  <th colspan="3" class="px-3 py-2 text-xs font-semibold text-gray-700 text-center border-r border-gray-300 bg-green-50">Rencana Panen</th>
                  <th colspan="2" class="px-3 py-2 text-xs font-semibold text-gray-700 text-center border-r border-gray-300 bg-blue-50">Tenaga</th>
                  <th colspan="2" class="px-3 py-2 text-xs font-semibold text-gray-700 text-center border-r border-gray-300 bg-purple-50">Armada</th>
                  <th rowspan="2" class="px-3 py-3 text-xs font-semibold text-gray-700 text-center border-r border-gray-300">Mesin<br>Panen</th>
                  <th rowspan="2" class="px-3 py-3 text-xs font-semibold text-gray-700 text-center border-r border-gray-300">Grab<br>Loader</th>
                  <th rowspan="2" class="px-3 py-3 text-xs font-semibold text-gray-700 text-center border-r border-gray-300">Lokasi Plot</th>
                  <th rowspan="2" class="px-3 py-3 text-xs font-semibold text-gray-700 text-center">Aksi</th>
                </tr>
                <tr class="bg-gray-50">
                  <!-- Rencana Panen sub-headers -->
                  <th class="px-3 py-2 text-xs font-medium text-gray-600 text-center border-r border-gray-300 bg-green-50">Netto<br>(Ton)</th>
                  <th class="px-3 py-2 text-xs font-medium text-gray-600 text-center border-r border-gray-300 bg-green-50">Luas<br>(Ha)</th>
                  <th class="px-3 py-2 text-xs font-medium text-gray-600 text-center border-r border-gray-300 bg-green-50">YPH</th>
                  <!-- Tenaga sub-headers -->
                  <th class="px-3 py-2 text-xs font-medium text-gray-600 text-center border-r border-gray-300 bg-blue-50">Tebang</th>
                  <th class="px-3 py-2 text-xs font-medium text-gray-600 text-center border-r border-gray-300 bg-blue-50">Muat</th>
                  <!-- Armada sub-headers -->
                  <th class="px-3 py-2 text-xs font-medium text-gray-600 text-center border-r border-gray-300 bg-purple-50">WL</th>
                  <th class="px-3 py-2 text-xs font-medium text-gray-600 text-center border-r border-gray-300 bg-purple-50">Umum</th>
                </tr>
              </thead>
              <tbody id="kontraktor-rows" class="divide-y divide-gray-200">
                <!-- Dynamic rows akan di-insert via JS -->
              </tbody>
              <!-- TOTAL ROW -->
              <tfoot class="bg-yellow-50 font-bold">
                <tr>
                  <td colspan="2" class="px-3 py-3 text-sm text-gray-800 text-right border-t-2 border-gray-400">TOTAL:</td>
                  <td class="px-3 py-3 text-sm text-gray-800 text-center border-t-2 border-gray-400" id="total-netto">0.00</td>
                  <td class="px-3 py-3 text-sm text-gray-800 text-center border-t-2 border-gray-400" id="total-luas">0.00</td>
                  <td colspan="9" class="border-t-2 border-gray-400"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <button type="button" 
                  id="add-kontraktor-row" 
                  class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Kontraktor
          </button>
        </div>
      </div>

      <!-- Buttons -->
      <div class="mt-8 flex justify-center space-x-4">
        <button type="button" 
                onclick="window.location.href = '{{ route('input.rkh-panen.index') }}';" 
                class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Kembali
        </button>
        
        <button type="submit" 
                id="submit-btn" 
                class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-12 py-3 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center">
          <svg class="w-5 h-5 mr-2" id="submit-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <svg class="animate-spin w-5 h-5 mr-2 hidden" id="loading-spinner" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span id="submit-text">Simpan RKH Panen</span>
        </button>
      </div>
    </div>
  </form>

  <script>
    // Global data
    window.kontraktorsData = @json($kontraktors ?? []);
    window.plotsData = @json($plots ?? []);
    let rowIndex = 0;
    let currentEditingRow = null;
    let selectedPlots = {}; // Store selected plots per row

    // YPH options (10-200, interval 10, default 80)
    function generateYphOptions(defaultValue = 80) {
      let options = '';
      for (let i = 10; i <= 200; i += 10) {
        options += `<option value="${i}" ${i === defaultValue ? 'selected' : ''}>${i}</option>`;
      }
      return options;
    }

    // ⭐ PLOT MODAL FUNCTIONS
    function openPlotModal(rowIdx) {
      currentEditingRow = rowIdx;
      const modal = document.getElementById('plotModal');
      modal.classList.remove('hidden');
      
      // Initialize selected plots for this row
      if (!selectedPlots[rowIdx]) {
        selectedPlots[rowIdx] = [];
      }
      
      renderPlotGrid();
      updateSelectedCount();
    }

    function closePlotModal() {
      document.getElementById('plotModal').classList.add('hidden');
      document.getElementById('plotSearchInput').value = '';
      currentEditingRow = null;
    }

    function renderPlotGrid() {
      const grid = document.getElementById('plotGrid');
      const searchTerm = document.getElementById('plotSearchInput').value.toLowerCase();
      let html = '';

      Object.keys(window.plotsData).forEach(blokCode => {
        const plots = window.plotsData[blokCode];
        const filteredPlots = plots.filter(p => 
          p.plot.toLowerCase().includes(searchTerm) || 
          `${p.blok}-${p.plot}`.toLowerCase().includes(searchTerm)
        );

        if (filteredPlots.length > 0) {
          html += `
            <div class="border border-gray-300 rounded-lg overflow-hidden">
              <div class="bg-gray-100 px-4 py-2 border-b border-gray-300">
                <h4 class="font-semibold text-gray-800">Blok ${blokCode}</h4>
              </div>
              <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
          `;

          filteredPlots.forEach(p => {
            const isSelected = selectedPlots[currentEditingRow]?.includes(p.plot);
            html += `
              <button type="button" 
                      onclick="togglePlot('${p.plot}')"
                      class="plot-item ${isSelected ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300'} 
                             border-2 rounded-lg px-3 py-2 text-sm font-medium hover:shadow-md transition-all duration-200">
                <div class="font-bold">${p.plot}</div>
                <div class="text-xs opacity-75">${p.lifecyclestatus}</div>
              </button>
            `;
          });

          html += `
              </div>
            </div>
          `;
        }
      });

      if (html === '') {
        html = '<div class="text-center py-8 text-gray-500">Tidak ada plot yang ditemukan</div>';
      }

      grid.innerHTML = html;
    }

    function togglePlot(plotCode) {
      if (!selectedPlots[currentEditingRow]) {
        selectedPlots[currentEditingRow] = [];
      }

      const index = selectedPlots[currentEditingRow].indexOf(plotCode);
      if (index > -1) {
        selectedPlots[currentEditingRow].splice(index, 1);
      } else {
        selectedPlots[currentEditingRow].push(plotCode);
      }

      renderPlotGrid();
      updateSelectedCount();
    }

    function updateSelectedCount() {
      const count = selectedPlots[currentEditingRow]?.length || 0;
      document.getElementById('selectedCount').textContent = count;
    }

    function clearPlotSelection() {
      selectedPlots[currentEditingRow] = [];
      renderPlotGrid();
      updateSelectedCount();
    }

    function confirmPlotSelection() {
      const plotCount = selectedPlots[currentEditingRow]?.length || 0;
      if (plotCount === 0) {
        alert('Pilih minimal 1 plot!');
        return;
      }

      // Update display in table
      updatePlotDisplay(currentEditingRow);
      
      // Update hidden input
      const hiddenInput = document.querySelector(`input[name="kontraktors[${currentEditingRow}][lokasiplot]"]`);
      if (hiddenInput) {
        hiddenInput.value = selectedPlots[currentEditingRow].join(',');
      }

      closePlotModal();
    }

    function updatePlotDisplay(rowIdx) {
      const displayBtn = document.querySelector(`#plot-display-${rowIdx}`);
      if (displayBtn) {
        const count = selectedPlots[rowIdx]?.length || 0;
        const plots = selectedPlots[rowIdx] || [];
        
        if (count === 0) {
          displayBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Pilih Plot
          `;
          displayBtn.className = 'w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-xs font-medium transition-colors flex items-center justify-center';
        } else {
          const displayText = count <= 3 ? plots.join(', ') : `${plots.slice(0, 3).join(', ')} +${count - 3}`;
          displayBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span class="truncate">${displayText}</span>
            <span class="ml-2 px-2 py-0.5 bg-blue-600 text-white rounded-full text-xs font-bold">${count}</span>
          `;
          displayBtn.className = 'w-full px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border-2 border-blue-300 rounded text-xs font-medium transition-colors flex items-center';
        }
      }
    }

    function filterPlots() {
      renderPlotGrid();
    }

    // Kontraktor row template
    function createKontraktorRow(index) {
      return `
        <tr data-index="${index}" class="hover:bg-gray-50">
          <td class="px-3 py-2 border-r border-gray-200">
            <select name="kontraktors[${index}][kontraktorid]" 
                    required
                    class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
              <option value="">Pilih</option>
              ${window.kontraktorsData.map(k => `<option value="${k.kontraktorid}">${k.namakontraktor}</option>`).join('')}
            </select>
          </td>
          <td class="px-3 py-2 border-r border-gray-200">
            <select name="kontraktors[${index}][jenispanen]" 
                    class="jenis-panen-select w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500"
                    data-row="${index}"
                    required>
              <option value="">Pilih</option>
              <option value="MANUAL">Manual</option>
              <option value="SEMI_MEKANIS">Semi-Mekanis</option>
              <option value="MEKANIS">Mekanis</option>
            </select>
          </td>
          <td class="px-3 py-2 border-r border-gray-200 bg-green-50">
            <input type="number" name="kontraktors[${index}][rencananetto]" step="0.01" min="0" 
                   class="rencana-netto w-full text-xs border border-gray-300 rounded px-2 py-1.5 text-right"
                   onchange="calculateTotals()">
          </td>
          <td class="px-3 py-2 border-r border-gray-200 bg-green-50">
            <input type="number" name="kontraktors[${index}][rencanaha]" step="0.01" min="0" 
                   class="rencana-luas w-full text-xs border border-gray-300 rounded px-2 py-1.5 text-right"
                   onchange="calculateTotals()">
          </td>
          <td class="px-3 py-2 border-r border-gray-200 bg-green-50">
            <select name="kontraktors[${index}][estimasiyph]" required
                    class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
              ${generateYphOptions(80)}
            </select>
          </td>
          <td class="px-3 py-2 border-r border-gray-200 bg-blue-50">
            <input type="number" name="kontraktors[${index}][tenagatebangjumlah]" min="0"
                   class="tenaga-tebang w-full text-xs border border-gray-300 rounded px-2 py-1.5 text-center">
          </td>
          <td class="px-3 py-2 border-r border-gray-200 bg-blue-50">
            <input type="number" name="kontraktors[${index}][tenagamuatjumlah]" min="0"
                   class="tenaga-muat w-full text-xs border border-gray-300 rounded px-2 py-1.5 text-center">
          </td>
          <td class="px-3 py-2 border-r border-gray-200 bg-purple-50">
            <input type="number" name="kontraktors[${index}][armadawl]" min="0"
                   class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 text-center">
          </td>
          <td class="px-3 py-2 border-r border-gray-200 bg-purple-50">
            <input type="number" name="kontraktors[${index}][armadaumum]" min="0"
                   class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 text-center">
          </td>
          <td class="px-3 py-2 text-center border-r border-gray-200">
            <input type="checkbox" name="kontraktors[${index}][mesinpanen]" value="1"
                   class="mesin-panen-check w-4 h-4 text-blue-600 rounded" disabled>
          </td>
          <td class="px-3 py-2 text-center border-r border-gray-200">
            <input type="checkbox" name="kontraktors[${index}][grabloader]" value="1"
                   class="grabloader-check w-4 h-4 text-blue-600 rounded" disabled>
          </td>
          <td class="px-3 py-2 border-r border-gray-200">
            <input type="hidden" name="kontraktors[${index}][lokasiplot]" value="" required>
            <button type="button" 
                    id="plot-display-${index}"
                    onclick="openPlotModal(${index})"
                    class="w-full px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 rounded text-xs font-medium transition-colors flex items-center justify-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Pilih Plot
            </button>
          </td>
          <td class="px-3 py-2 text-center">
            <button type="button" onclick="removeRow(${index})" 
                    class="text-red-600 hover:text-red-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
              </svg>
            </button>
          </td>
        </tr>
      `;
    }

    // Add row
    document.getElementById('add-kontraktor-row').addEventListener('click', function() {
      const tbody = document.getElementById('kontraktor-rows');
      tbody.insertAdjacentHTML('beforeend', createKontraktorRow(rowIndex));
      selectedPlots[rowIndex] = [];
      rowIndex++;
    });

    // Remove row
    function removeRow(index) {
      const row = document.querySelector(`tr[data-index="${index}"]`);
      if (row) {
        row.remove();
        delete selectedPlots[index];
        calculateTotals();
      }
    }

    // Calculate totals
    function calculateTotals() {
      let totalNetto = 0;
      let totalLuas = 0;

      document.querySelectorAll('.rencana-netto').forEach(input => {
        totalNetto += parseFloat(input.value) || 0;
      });

      document.querySelectorAll('.rencana-luas').forEach(input => {
        totalLuas += parseFloat(input.value) || 0;
      });

      document.getElementById('total-netto').textContent = totalNetto.toFixed(2);
      document.getElementById('total-luas').textContent = totalLuas.toFixed(2);
    }

    // Jenis panen logic
    document.addEventListener('change', function(e) {
      if (e.target.classList.contains('jenis-panen-select')) {
        const row = e.target.closest('tr');
        const jenisPanen = e.target.value;
        
        const mesinCheck = row.querySelector('.mesin-panen-check');
        const grabCheck = row.querySelector('.grabloader-check');
        const tenagaTebang = row.querySelector('.tenaga-tebang');
        const tenagaMuat = row.querySelector('.tenaga-muat');
        
        // Reset
        mesinCheck.disabled = true;
        mesinCheck.checked = false;
        grabCheck.disabled = true;
        grabCheck.checked = false;
        tenagaTebang.required = false;
        tenagaMuat.required = false;
        
        if (jenisPanen === 'MANUAL') {
          tenagaTebang.required = true;
          tenagaMuat.required = true;
        } else if (jenisPanen === 'SEMI_MEKANIS') {
          tenagaTebang.required = true;
          grabCheck.disabled = false;
          grabCheck.checked = true;
        } else if (jenisPanen === 'MEKANIS') {
          mesinCheck.disabled = false;
          mesinCheck.checked = true;
          grabCheck.disabled = false;
          grabCheck.checked = true;
        }
      }
    });

    // Form submission
  document.getElementById('rkh-panen-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validation
    const rows = document.querySelectorAll('#kontraktor-rows tr');
    if (rows.length === 0) {
      alert('Minimal harus ada 1 kontraktor!');
      return;
    }

    // Validate each row has plots selected
    let allValid = true;
    rows.forEach((row, idx) => {
      const rowIndex = row.dataset.index;
      
      // ⭐ Validate kontraktor selected
      const kontraktorSelect = row.querySelector(`select[name="kontraktors[${rowIndex}][kontraktorid]"]`);
      if (!kontraktorSelect || !kontraktorSelect.value) {
        alert(`Kontraktor baris ${parseInt(idx) + 1} belum dipilih!`);
        allValid = false;
        return;
      }
      
      // Validate plots selected
      if (!selectedPlots[rowIndex] || selectedPlots[rowIndex].length === 0) {
        alert(`Kontraktor baris ${parseInt(idx) + 1} belum memilih plot!`);
        allValid = false;
        return;
      }
    });

    if (!allValid) return;
    
    // Show loading
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = true;
    document.getElementById('submit-text').textContent = 'Menyimpan...';
    document.getElementById('submit-icon').classList.add('hidden');
    document.getElementById('loading-spinner').classList.remove('hidden');
    
    const formData = new FormData(this);
    
    fetch(this.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success modal
        const modalEl = document.querySelector('[x-data*="showModal"]');
        if (modalEl && modalEl._x_dataStack) {
          modalEl._x_dataStack[0].showModal = true;
          modalEl._x_dataStack[0].modalMessage = data.message;
        }
      } else {
        alert(data.message || 'Terjadi kesalahan');
        // Reset button
        submitBtn.disabled = false;
        document.getElementById('submit-text').textContent = 'Simpan RKH Panen';
        document.getElementById('submit-icon').classList.remove('hidden');
        document.getElementById('loading-spinner').classList.add('hidden');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan sistem');
      submitBtn.disabled = false;
      document.getElementById('submit-text').textContent = 'Simpan RKH Panen';
      document.getElementById('submit-icon').classList.remove('hidden');
      document.getElementById('loading-spinner').classList.add('hidden');
    });
  });

    // Add first row on load
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('add-kontraktor-row').click();
    });
  </script>

</x-layout>