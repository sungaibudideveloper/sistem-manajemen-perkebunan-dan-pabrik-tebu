{{-- resources/views/input/rkh-panen/edit-hasil.blade.php --}}
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
        <button @click="window.location.href = '{{ route('input.rkh-panen.show', $rkhPanen->rkhpanenno) }}'"
                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          OK
        </button>
      </div>
    </div>
  </div>

  <form id="hasil-panen-form" action="{{ route('input.rkh-panen.updateHasil', $rkhPanen->rkhpanenno) }}" method="POST">
    @csrf
    @method('PUT')

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
      
      <!-- RKH Info Header -->
      <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">No RKH Panen</label>
            <p class="text-sm font-bold text-gray-900">{{ $rkhPanen->rkhpanenno }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
            <p class="text-sm font-semibold text-gray-900">{{ \Carbon\Carbon::parse($rkhPanen->rkhdate)->format('d/m/Y') }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Mandor</label>
            <p class="text-sm font-semibold text-gray-900">{{ $rkhPanen->mandor->name ?? '-' }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Target</label>
            <p class="text-sm font-semibold text-gray-900">
              {{ number_format($rkhPanen->targettoday ?? 0, 2) }} ton / {{ number_format($rkhPanen->targetha ?? 0, 2) }} ha
            </p>
          </div>
        </div>
      </div>

      <!-- Section 2: Hasil Panen Kemarin -->
      <div class="bg-white rounded-xl border border-gray-300 shadow-md">
        <div class="bg-gradient-to-r from-orange-700 to-orange-600 text-white px-6 py-3 rounded-t-xl">
          <h3 class="text-lg font-bold">Section 2: Input Hasil Panen Kemarin</h3>
        </div>

        <div class="p-6">
          <div class="overflow-x-auto">
            <table id="hasil-table" class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-100">
                <tr>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Blok</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Plot</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Luas Plot (Ha)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">KTG</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Hari Tebang Ke-</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">STC (Ha)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">HC (Ha)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">BC (Ha)</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">FB RIT</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">FB TON</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-center">Premium</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-left">Keterangan</th>
                  <th class="px-3 py-2 text-xs font-semibold text-gray-700 text-center">Aksi</th>
                </tr>
              </thead>
              <tbody id="hasil-rows" class="divide-y divide-gray-200">
                @foreach($rkhPanen->results as $index => $hasil)
                  <tr data-index="{{ $index }}" class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                      <select name="hasil[{{ $index }}][blok]" 
                              required
                              class="blok-select w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
                        <option value="">Pilih</option>
                        @foreach($bloks as $blok)
                          <option value="{{ $blok->blok }}" {{ $hasil->blok == $blok->blok ? 'selected' : '' }}>
                            {{ $blok->blok }}
                          </option>
                        @endforeach
                      </select>
                    </td>
                    <td class="px-3 py-2">
                      <select name="hasil[{{ $index }}][plot]" 
                              required
                              class="plot-select w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:ring-1 focus:ring-blue-500">
                        <option value="">Pilih</option>
                        @foreach($plots as $plot)
                          <option value="{{ $plot->plot }}" {{ $hasil->plot == $plot->plot ? 'selected' : '' }}>
                            {{ $plot->plot }}
                          </option>
                        @endforeach
                      </select>
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" name="hasil[{{ $index }}][luasplot]" step="0.01" min="0" 
                             value="{{ $hasil->luasplot }}"
                             required
                             class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                    </td>
                    <td class="px-3 py-2">
                      <select name="hasil[{{ $index }}][kodestatus]" 
                              required
                              class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                        <option value="PC" {{ $hasil->kodestatus == 'PC' ? 'selected' : '' }}>PC</option>
                        <option value="RC1" {{ $hasil->kodestatus == 'RC1' ? 'selected' : '' }}>RC1</option>
                        <option value="RC2" {{ $hasil->kodestatus == 'RC2' ? 'selected' : '' }}>RC2</option>
                        <option value="RC3" {{ $hasil->kodestatus == 'RC3' ? 'selected' : '' }}>RC3</option>
                      </select>
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" name="hasil[{{ $index }}][haritebang]" min="1" 
                             value="{{ $hasil->haritebang }}"
                             required
                             class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" name="hasil[{{ $index }}][stc]" step="0.01" min="0" 
                             value="{{ $hasil->stc }}"
                             required
                             class="stc-input w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" name="hasil[{{ $index }}][hc]" step="0.01" min="0" 
                             value="{{ $hasil->hc }}"
                             required
                             class="hc-input w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" name="hasil[{{ $index }}][bc]" step="0.01" 
                             value="{{ $hasil->bc }}"
                             readonly
                             class="bc-input w-full text-xs border border-gray-300 rounded px-2 py-1.5 bg-gray-100 font-semibold">
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" name="hasil[{{ $index }}][fbrit]" min="0" 
                             value="{{ $hasil->fbrit }}"
                             class="fbrit-input w-full text-xs border border-gray-300 rounded px-2 py-1.5">
                    </td>
                    <td class="px-3 py-2">
                      <input type="number" name="hasil[{{ $index }}][fbton]" step="0.01" 
                             value="{{ $hasil->fbton }}"
                             readonly
                             class="fbton-input w-full text-xs border border-gray-300 rounded px-2 py-1.5 bg-gray-100 font-semibold">
                    </td>
                    <td class="px-3 py-2 text-center">
                      <input type="checkbox" name="hasil[{{ $index }}][ispremium]" value="1"
                             {{ $hasil->ispremium ? 'checked' : '' }}
                             class="w-4 h-4 text-blue-600 rounded">
                    </td>
                    <td class="px-3 py-2">
                      <textarea name="hasil[{{ $index }}][keterangan]" rows="2"
                                class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 resize-none">{{ $hasil->keterangan }}</textarea>
                    </td>
                    <td class="px-3 py-2 text-center">
                      <button type="button" onclick="removeHasilRow({{ $index }})" 
                              class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <button type="button" 
                  id="add-hasil-row" 
                  class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Plot
          </button>
        </div>
      </div>

      <!-- Buttons -->
      <div class="mt-8 flex justify-center space-x-4">
        <button type="button" 
                onclick="window.location.href = '{{ route('input.rkh-panen.show', $rkhPanen->rkhpanenno) }}';" 
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
          <span id="submit-text">Simpan Hasil Panen</span>
        </button>
      </div>
    </div>
  </form>

  <script>
    // Global data
    window.bloksData = @json($bloks ?? []);
    window.plotsData = @json($plots ?? []);
    let hasilRowIndex = {{ $rkhPanen->results->count() }};

    // Hasil row template
    function createHasilRow(index) {
      return `
        <tr data-index="${index}" class="hover:bg-gray-50">
          <td class="px-3 py-2">
            <select name="hasil[${index}][blok]" required
                    class="blok-select w-full text-xs border border-gray-300 rounded px-2 py-1.5">
              <option value="">Pilih</option>
              ${window.bloksData.map(b => `<option value="${b.blok}">${b.blok}</option>`).join('')}
            </select>
          </td>
          <td class="px-3 py-2">
            <select name="hasil[${index}][plot]" required
                    class="plot-select w-full text-xs border border-gray-300 rounded px-2 py-1.5">
              <option value="">Pilih</option>
              ${window.plotsData.map(p => `<option value="${p.plot}">${p.plot}</option>`).join('')}
            </select>
          </td>
          <td class="px-3 py-2">
            <input type="number" name="hasil[${index}][luasplot]" step="0.01" min="0" required
                   class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <select name="hasil[${index}][kodestatus]" required
                    class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
              <option value="PC">PC</option>
              <option value="RC1">RC1</option>
              <option value="RC2">RC2</option>
              <option value="RC3">RC3</option>
            </select>
          </td>
          <td class="px-3 py-2">
            <input type="number" name="hasil[${index}][haritebang]" min="1" value="1" required
                   class="w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <input type="number" name="hasil[${index}][stc]" step="0.01" min="0" required
                   class="stc-input w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <input type="number" name="hasil[${index}][hc]" step="0.01" min="0" required
                   class="hc-input w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <input type="number" name="hasil[${index}][bc]" step="0.01" readonly
                   class="bc-input w-full text-xs border border-gray-300 rounded px-2 py-1.5 bg-gray-100 font-semibold">
          </td>
          <td class="px-3 py-2">
            <input type="number" name="hasil[${index}][fbrit]" min="0"
                   class="fbrit-input w-full text-xs border border-gray-300 rounded px-2 py-1.5">
          </td>
          <td class="px-3 py-2">
            <input type="number" name="hasil[${index}][fbton]" step="0.01" readonly
                   class="fbton-input w-full text-xs border border-gray-300 rounded px-2 py-1.5 bg-gray-100 font-semibold">
          </td>
          <td class="px-3 py-2 text-center">
            <input type="checkbox" name="hasil[${index}][ispremium]" value="1"
                   class="w-4 h-4 text-blue-600 rounded">
          </td>
          <td class="px-3 py-2">
            <textarea name="hasil[${index}][keterangan]" rows="2"
                      class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 resize-none"></textarea>
          </td>
          <td class="px-3 py-2 text-center">
            <button type="button" onclick="removeHasilRow(${index})" 
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
    document.getElementById('add-hasil-row').addEventListener('click', function() {
      const tbody = document.getElementById('hasil-rows');
      tbody.insertAdjacentHTML('beforeend', createHasilRow(hasilRowIndex));
      hasilRowIndex++;
    });

    // Remove row
    function removeHasilRow(index) {
      const row = document.querySelector(`tr[data-index="${index}"]`);
      if (row) row.remove();
    }

    // Auto-calculate BC (STC - HC)
    document.addEventListener('input', function(e) {
      if (e.target.classList.contains('stc-input') || e.target.classList.contains('hc-input')) {
        const row = e.target.closest('tr');
        const stc = parseFloat(row.querySelector('.stc-input').value) || 0;
        const hc = parseFloat(row.querySelector('.hc-input').value) || 0;
        const bcInput = row.querySelector('.bc-input');
        bcInput.value = (stc - hc).toFixed(2);
      }
    });

    // Auto-calculate FB TON (RIT * 5)
    document.addEventListener('input', function(e) {
      if (e.target.classList.contains('fbrit-input')) {
        const row = e.target.closest('tr');
        const fbrit = parseInt(row.querySelector('.fbrit-input').value) || 0;
        const fbtonInput = row.querySelector('.fbton-input');
        fbtonInput.value = (fbrit * 5).toFixed(2);
      }
    });

    // Form submission
    document.getElementById('hasil-panen-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Validation
      const rows = document.querySelectorAll('#hasil-rows tr');
      if (rows.length === 0) {
        alert('Minimal harus ada 1 plot hasil panen!');
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
          document.getElementById('submit-text').textContent = 'Simpan Hasil Panen';
          document.getElementById('submit-icon').classList.remove('hidden');
          document.getElementById('loading-spinner').classList.add('hidden');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan sistem');
        submitBtn.disabled = false;
        document.getElementById('submit-text').textContent = 'Simpan Hasil Panen';
        document.getElementById('submit-icon').classList.remove('hidden');
        document.getElementById('loading-spinner').classList.add('hidden');
      });
    });
  </script>

</x-layout>