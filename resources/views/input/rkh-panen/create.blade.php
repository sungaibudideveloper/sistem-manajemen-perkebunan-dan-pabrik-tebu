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
      
      <!-- Header Info (NO TARGET FIELDS) -->
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

  <!-- Choices.js -->
  <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

  <script>
    // Global data
    window.kontraktorsData = @json($kontraktors ?? []);
    window.plotsData = @json($plots ?? []);
    let rowIndex = 0;
    let choicesInstances = {};

    // YPH options (10, 20, 30, ... 200)
    function generateYphOptions(defaultValue = 80) {
      let options = '';
      for (let i = 10; i <= 200; i += 10) {
        options += `<option value="${i}" ${i === defaultValue ? 'selected' : ''}>${i}</option>`;
      }
      return options;
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
            <select name="kontraktors[${index}][lokasiplot]" 
                    id="lokasi-plot-${index}"
                    class="lokasi-plot-select"
                    multiple 
                    required>
              ${window.plotsData.map(p => 
                `<option value="${p.plot}">${p.blok}-${p.plot} (${p.luasarea} ha)</option>`
              ).join('')}
            </select>
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
      
      // Initialize Choices.js for the new select
      const selectElement = document.getElementById(`lokasi-plot-${rowIndex}`);
      choicesInstances[rowIndex] = new Choices(selectElement, {
        removeItemButton: true,
        searchEnabled: true,
        searchPlaceholderValue: 'Cari plot...',
        noResultsText: 'Plot tidak ditemukan',
        itemSelectText: 'Klik untuk pilih',
        placeholder: true,
        placeholderValue: 'Pilih lokasi plot...',
      });
      
      rowIndex++;
    });

    // Remove row
    function removeRow(index) {
      const row = document.querySelector(`tr[data-index="${index}"]`);
      if (row) {
        // Destroy Choices instance
        if (choicesInstances[index]) {
          choicesInstances[index].destroy();
          delete choicesInstances[index];
        }
        row.remove();
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
      
      // Convert Choices.js values to comma-separated string
      Object.keys(choicesInstances).forEach(index => {
        const choicesInstance = choicesInstances[index];
        const selectedValues = choicesInstance.getValue(true); // Get array of values
        
        // Create hidden input with comma-separated values
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `kontraktors[${index}][lokasiplot]`;
        hiddenInput.value = selectedValues.join(',');
        this.appendChild(hiddenInput);
        
        // Remove the original multiple select from form data
        const originalSelect = document.querySelector(`select[name="kontraktors[${index}][lokasiplot]"]`);
        if (originalSelect) {
          originalSelect.removeAttribute('name');
        }
      });
      
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