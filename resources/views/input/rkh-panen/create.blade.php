{{-- resources/views/input/rkh-panen/create.blade.php --}}
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

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
      
      <!-- Header Info -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          
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

          <!-- Target Ton -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Target Hari Ini (Ton)</label>
            <input type="number" 
                   name="targettoday" 
                   step="0.01" 
                   min="0"
                   value="{{ old('targettoday') }}"
                   placeholder="0.00"
                   class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>

          <!-- Target Hektar -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Target Hari Ini (Ha)</label>
            <input type="number" 
                   name="targetha" 
                   step="0.01" 
                   min="0"
                   value="{{ old('targetha') }}"
                   placeholder="0.00"
                   class="w-full border-2 border-gray-200 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
            <table id="kontraktor-table" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Kontraktor</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Jenis Panen</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Rencana (Ton)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Rencana (Ha)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">YPH</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Tenaga (Tebang/Muat)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Armada (WL/Umum)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-center">Mesin Panen</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-center">Grab Loader</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Lokasi Plot</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody id="kontraktor-rows" class="divide-y divide-gray-200">
                <!-- Dynamic rows akan di-insert via JS -->
              </tbody>
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
    let rowIndex = 0;

    // Kontraktor row template
    function createKontraktorRow(index) {
      return `
        <tr data-index="${index}" class="hover:bg-gray-50">
          <td class="px-3 py-2">
            <select name="kontraktors[${index}][kontraktorid]" 
                    required
                    class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
              <option value="">Pilih</option>
              ${window.kontraktorsData.map(k => `<option value="${k.kontraktorid}">${k.nama}</option>`).join('')}
            </select>
          </td>
          <td class="px-3 py-2">
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
          <td class="px-3 py-2">
            <input type="number" name="kontraktors[${index}][rencananetto]" step="0.01" min="0" 
                   class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <input type="number" name="kontraktors[${index}][rencanaha]" step="0.01" min="0" 
                   class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <input type="number" name="kontraktors[${index}][estimasiyph]" step="0.01" min="0" 
                   class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <div class="flex gap-1">
              <input type="number" name="kontraktors[${index}][tenagatebangjumlah]" min="0" placeholder="Tebang"
                     class="tenaga-tebang w-full text-xs border border-gray-300 rounded px-2 py-1.5">
              <input type="number" name="kontraktors[${index}][tenagamuatjumlah]" min="0" placeholder="Muat"
                     class="tenaga-muat w-full text-xs border border-gray-300 rounded px-2 py-1.5">
            </div>
          </td>
          <td class="px-3 py-2">
            <div class="flex gap-1">
              <input type="number" name="kontraktors[${index}][armadawl]" min="0" placeholder="WL"
                     class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
              <input type="number" name="kontraktors[${index}][armadaumum]" min="0" placeholder="Umum"
                     class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
            </div>
          </td>
          <td class="px-3 py-2 text-center">
            <input type="checkbox" name="kontraktors[${index}][mesinpanen]" value="1"
                   class="mesin-panen-check w-4 h-4 text-blue-600 rounded" disabled>
          </td>
          <td class="px-3 py-2 text-center">
            <input type="checkbox" name="kontraktors[${index}][grabloader]" value="1"
                   class="grabloader-check w-4 h-4 text-blue-600 rounded" disabled>
          </td>
          <td class="px-3 py-2">
            <textarea name="kontraktors[${index}][lokasiplot]" rows="2" placeholder="A001, A002..."
                      class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 resize-none"></textarea>
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
      rowIndex++;
    });

    // Remove row
    function removeRow(index) {
      const row = document.querySelector(`tr[data-index="${index}"]`);
      if (row) row.remove();
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