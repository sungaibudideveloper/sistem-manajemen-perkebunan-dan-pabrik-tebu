<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="mx-auto bg-white rounded-md shadow-md p-6">
    <form method="POST" action="{{ route('input.kerjaharian.rencanakerjaharian.store') }}">
      @csrf
      
      <!-- Baris 1: No RKH (kiri) + Summary (kanan) -->
      <div class="flex justify-between items-start mb-2">
        <!-- No RKH -->
        <div class="w-1/5">
          <label for="no_rkh" class="block text-xs font-medium text-gray-700">No RKH</label>
          <input
            type="text"
            name="rkhno"
            id="rkhno"
            value="{{ $rkhno ?? '' }}"
            class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm bg-gray-100"
            readonly
          >
        </div>

        <!-- Summary Absen -->
        <div class="text-right text-xs bg-gray-100 p-4 rounded-md shadow-sm w-80">
          <p class="font-semibold mb-2">Jumlah Absen Tenaga Gerald - {{ date('d/m/Y') }}</p>
          <div class="flex justify-between text-left gap-4">
            <p>Laki-laki: <span id="summary-laki">8</span></p>
            <p>Perempuan: <span id="summary-perempuan">21</span></p>
            <p class="font-semibold">Total: <span id="summary-total">29</span></p>
          </div>
        </div>
      </div>

      <!-- Baris 2: Mandor, Tanggal -->
      <div x-data="mandorPicker()" class="grid grid-cols-3 gap-4 mb-6 w-1/3">
        {{-- Input Mandor --}}
        <div>
          <label for="mandor" class="block text-xs font-medium text-gray-700">Mandor</label>
          <input
            type="text"
            name="mandor"
            id="mandor"
            readonly
            placeholder="Pilih Mandor"
            @click="open = true"
            :value="selected.id && selected.name ? `${selected.id} – ${selected.name}` : ''"
            class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 cursor-pointer bg-white"
          >
          <input type="hidden" name="mandor_id" x-model="selected.id">
        </div>

        {{-- SECTION - Modal Mandor - START--}}
        <div
          x-show="open"
          x-cloak
          class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
          style="display: none;"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
        >
          <div
            @click.away="open = false"
            class="bg-white rounded-lg shadow-2xl w-full max-w-md max-h-[85vh] flex flex-col overflow-hidden"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
          >
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                  </div>
                  <h2 class="text-lg font-semibold text-gray-900">Pilih Mandor</h2>
                </div>
                <button 
                  @click="open = false" 
                  type="button" 
                  class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors duration-200"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Search Bar -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <input
                  type="text"
                  placeholder="Cari nama atau ID mandor..."
                  x-model="searchQuery"
                  class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                >
              </div>
            </div>
            
            <!-- Content Area -->
            <div class="flex-1 overflow-hidden">
              <div class="overflow-y-auto" style="max-height: 400px;">
                <table class="w-full">
                  <thead class="bg-gray-100 sticky top-0">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mandor</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="mandor in filteredMandors" :key="mandor.companycode + mandor.id">
                      <tr
                        @click="selectMandor(mandor)"
                        class="hover:bg-blue-50 cursor-pointer transition-colors duration-150 group"
                      >
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            
                            <span class="text-sm font-medium text-gray-900" x-text="mandor.id"></span>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900 font-medium" x-text="mandor.name"></div>
                        </td>
                      </tr>
                    </template>
                  </tbody>
                </table>
                
                <!-- Empty State -->
                <template x-if="filteredMandors.length === 0">
                  <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.467-.881-6.072-2.327"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada mandor ditemukan</h3>
                    <p class="mt-1 text-sm text-gray-500">Coba ubah kata kunci pencarian Anda.</p>
                  </div>
                </template>
              </div>
            </div>

            <!-- Footer (Optional) -->
            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
              <div class="flex justify-between items-center text-xs text-gray-500">
                <span x-text="`${filteredMandors.length} mandor tersedia`"></span>
                <span>Klik untuk memilih</span>
              </div>
            </div>
          </div>
        </div>
        {{-- SECTION - Modal Mandor - END--}}
        
        <div>
          <label for="tanggal" class="block text-xs font-medium text-gray-700">Tanggal</label>
          <input
            type="date"
            name="tanggal"
            id="tanggal"
            value="{{ date('Y-m-d') }}"
            class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
      </div>

      <!-- Tabel Input -->
      <div class="overflow-x-auto">
        <table id="rkh-table" class="min-w-full table-fixed border-collapse">
          <thead>
            <tr>
              <th class="border px-2 py-1 text-xs w-[48px]" rowspan="2">No.</th>
              <th class="border px-2 py-1 text-xs w-[200px]" rowspan="2">Kegiatan</th>
              <th class="border px-2 py-1 text-xs w-[60px]" rowspan="2">Blok</th>
              <th class="border px-2 py-1 text-xs w-[60px]" rowspan="2">Plot</th>
              <th class="border px-2 py-1 text-xs w-[60px]" rowspan="2">Luas (ha)</th>
              <th class="border px-2 py-1 text-xs text-center w-[180px]" colspan="3">Tenaga</th>
              <th class="border px-2 py-1 text-xs w-[80px]" rowspan="2">Estimasi Waktu</th>
              <th class="border px-2 py-1 text-xs w-[40px]" rowspan="2">Material</th>
              <th class="border px-2 py-1 text-xs w-[160px]" rowspan="2">Keterangan</th>
            </tr>
            <tr>
              <th class="border px-2 py-1 text-xs w-[20px]">L</th>
              <th class="border px-2 py-1 text-xs w-[20px]">P</th>
              <th class="border text-xs w-[10px]">Jumlah Tenaga</th>
            </tr>
          </thead>
          <tbody>
            <!-- Baris Input Data -->
            <!-- Baris 1 -->
            <tr class="rkh-row">
              <td class="border px-2 py-1 text-xs row-number text-center">1</td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][nama]" value="W105 - Weeding" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border text-xs"><input type="text" name="rows[0][blok]" value="A" class="w-full text-xs border-none focus:ring-0 text-center"></td>
              <td class="border text-xs"><input type="text" name="rows[0][plot]" value="A10" class="w-full text-xs border-none focus:ring-0 text-center"></td>
              <td class="border text-xs"><input type="number" name="rows[0][luas]" value="18" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border text-xs"><input type="number" name="rows[0][laki_laki]" value="2" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0"></td>
              <td class="border text-xs"><input type="number" name="rows[0][perempuan]" value="3" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0"></td>
              <td class="border text-xs"><input type="number" name="rows[0][jumlah_tenaga]" value="5" class="w-full text-xs border-none focus:ring-0 text-right p-0" readonly></td>
              <td class="border text-xs"><input type="text" name="rows[0][estimasiwaktu]" value="7 Hari" class="w-full text-xs border-none focus:ring-0 text-right"></td>
              <td class="border text-xs text-center">Yes</td>
              <td class="border text-xs"><input type="text" name="rows[0][keterangan]" class="w-full text-xs border-none focus:ring-0"></td>
            </tr>
            <!-- Baris 2 -->
            <tr class="rkh-row">
              <td class="border px-2 py-1 text-xs row-number text-center">2</td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][nama]" value="M102 - Sanitasi" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border text-xs"><input type="text" name="rows[1][blok]" value="B" class="w-full text-xs border-none focus:ring-0 text-center"></td>
              <td class="border text-xs"><input type="text" name="rows[1][plot]" value="B23" class="w-full text-xs border-none focus:ring-0 text-center"></td>
              <td class="border text-xs"><input type="number" name="rows[1][luas]" value="22" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border text-xs"><input type="number" name="rows[1][laki_laki]" value="4" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0"></td>
              <td class="border text-xs"><input type="number" name="rows[1][perempuan]" value="1" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0"></td>
              <td class="border text-xs"><input type="number" name="rows[1][jumlah_tenaga]" value="5" class="w-full text-xs border-none focus:ring-0 text-right p-0" readonly></td>
              <td class="border text-xs"><input type="text" name="rows[1][estimasiwaktu]" value="1 Hari" class="w-full text-xs border-none focus:ring-0 text-right"></td>
              <td class="border text-xs text-center">Yes</td>
              <td class="border text-xs"><input type="text" name="rows[1][keterangan]" class="w-full text-xs border-none focus:ring-0"></td>
            </tr>
            <!-- Baris 3 -->
            <tr class="rkh-row">
              <td class="border px-2 py-1 text-xs row-number text-center">3</td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][nama]" value="D45 - Drainase" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border text-xs"><input type="text" name="rows[2][blok]" value="D" class="w-full text-xs border-none focus:ring-0 text-center"></td>
              <td class="border text-xs"><input type="text" name="rows[2][plot]" value="D43" class="w-full text-xs border-none focus:ring-0 text-center"></td>
              <td class="border text-xs"><input type="number" name="rows[2][luas]" value="22" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border text-xs"><input type="number" name="rows[2][laki_laki]" value="1" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0"></td>
              <td class="border text-xs"><input type="number" name="rows[2][perempuan]" value="15" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0"></td>
              <td class="border text-xs"><input type="number" name="rows[2][jumlah_tenaga]" value="16" class="w-full text-xs border-none focus:ring-0 text-right p-0" readonly></td>
              <td class="border text-xs"><input type="text" name="rows[2][estimasiwaktu]" value="10 Hari" class="w-full text-xs border-none focus:ring-0 text-right"></td>
              <td class="border text-xs text-center">No</td>
              <td class="border text-xs"><input type="text" name="rows[2][keterangan]" class="w-full text-xs border-none focus:ring-0"></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4" class="text-right text-xs font-bold border px-2 py-1">Total</td>
              <td id="total-luas" class="border text-xs text-right font-bold px-4 py-1">0</td>
              <td id="total-laki" class="border text-xs text-right font-bold px-4 py-1">0</td>
              <td id="total-perempuan" class="border text-xs text-right font-bold px-4 py-1">0</td>
              <td id="total-tenaga" class="border text-xs text-right font-bold px-4 py-1">0</td>
              <td colspan="3" class="border px-2 py-1"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- Tombol Add & Remove Rows -->
      <div class="mt-2 space-x-2">
        <button
          type="button"
          id="add-row"
          class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-xs"
        >
          Add Row
        </button>
        <button
          type="button"
          id="remove-row"
          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-xs"
        >
          Remove Last Row
        </button>
      </div>

      <!-- Tombol Preview, Print & Back -->
      <div class="mt-6 flex justify-center space-x-6">
        <button
          type="button"
          class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm"
        >
          Preview
        </button>
        <button
          type="button"
          class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm"
        >
          Print
        </button>
        <button
          type="button"
          onclick="window.history.back()"
          class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm"
        >
          Back
        </button>
      </div>
      
      <!-- Tombol Submit -->
      <div class="mt-6 flex justify-center">
        <button
          type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white px-16 py-4 rounded-md text-sm"
        >
          Submit
        </button>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tbody = document.querySelector('#rkh-table tbody');
      const addBtn = document.getElementById('add-row');
      const removeBtn = document.getElementById('remove-row');

      const totalLuas = document.getElementById('total-luas');
      const totalLaki = document.getElementById('total-laki');
      const totalPerempuan = document.getElementById('total-perempuan');
      const totalTenaga = document.getElementById('total-tenaga');

      const summaryLaki = document.getElementById('summary-laki');
      const summaryPerempuan = document.getElementById('summary-perempuan');
      const summaryTotal = document.getElementById('summary-total');

      function calculateRow(row) {
        const laki = row.querySelector('input[name$="[laki_laki]"]');
        const perempuan = row.querySelector('input[name$="[perempuan]"]');
        const jumlah = row.querySelector('input[name$="[jumlah_tenaga]"]');
        
        if (laki && perempuan && jumlah) {
          jumlah.value = (parseInt(laki.value) || 0) + (parseInt(perempuan.value) || 0);
        }
      }

      function calculateTotals() {
        let luasSum = 0, lakiSum = 0, perempuanSum = 0, tenagaSum = 0;
        
        tbody.querySelectorAll('tr').forEach(row => {
          const luasInput = row.querySelector('input[name$="[luas]"]');
          const lakiInput = row.querySelector('input[name$="[laki_laki]"]');
          const perempuanInput = row.querySelector('input[name$="[perempuan]"]');
          
          if (luasInput && lakiInput && perempuanInput) {
            const luas = parseFloat(luasInput.value) || 0;
            const laki = parseInt(lakiInput.value) || 0;
            const perempuan = parseInt(perempuanInput.value) || 0;
            const jumlah = laki + perempuan;

            luasSum += luas;
            lakiSum += laki;
            perempuanSum += perempuan;
            tenagaSum += jumlah;

            const jumlahInput = row.querySelector('input[name$="[jumlah_tenaga]"]');
            if (jumlahInput) {
              jumlahInput.value = jumlah;
            }
          }
        });

        if (totalLuas) totalLuas.textContent = luasSum;
        if (totalLaki) totalLaki.textContent = lakiSum;
        if (totalPerempuan) totalPerempuan.textContent = perempuanSum;
        if (totalTenaga) totalTenaga.textContent = tenagaSum;

        // Update summary
        if (summaryLaki) summaryLaki.textContent = lakiSum;
        if (summaryPerempuan) summaryPerempuan.textContent = perempuanSum;
        if (summaryTotal) summaryTotal.textContent = tenagaSum;
      }

      function attachListeners(row) {
        ['[laki_laki]', '[perempuan]', '[luas]'].forEach(suffix => {
          const input = row.querySelector(`input[name$="${suffix}"]`);
          if (input) {
            input.addEventListener('input', () => {
              calculateRow(row);
              calculateTotals();
            });
          }
        });
      }

      function updateRowNumbers() {
        tbody.querySelectorAll('tr').forEach((row, i) => {
          const rowNumber = row.querySelector('.row-number');
          if (rowNumber) {
            rowNumber.textContent = i + 1;
          }
          
          row.querySelectorAll('input').forEach(input => {
            if (input.name) {
              input.name = input.name.replace(/rows\[\d+\]/, `rows[${i}]`);
            }
          });
        });
      }

      // Inisialisasi baris awal
      tbody.querySelectorAll('tr').forEach(row => {
        attachListeners(row);
      });
      calculateTotals();

      if (addBtn) {
        addBtn.addEventListener('click', () => {
          const rows = tbody.querySelectorAll('tr');
          if (rows.length > 0) {
            const newRow = rows[0].cloneNode(true);

            newRow.querySelectorAll('input').forEach(input => {
              if (input.type !== 'hidden') {
                input.value = '';
              }
            });
            
            tbody.appendChild(newRow);
            attachListeners(newRow);
            updateRowNumbers();
            calculateTotals();
          }
        });
      }

      if (removeBtn) {
        removeBtn.addEventListener('click', () => {
          const rows = tbody.querySelectorAll('tr');
          if (rows.length > 1) {
            rows[rows.length - 1].remove();
            updateRowNumbers();
            calculateTotals();
          }
        });
      }
    });

    function mandorPicker() {
      return {
        open: false,
        searchQuery: '',
        mandors: @json($mandors ?? []),
        selected: { companycode: '', id: '', name: '' },

        get filteredMandors() {
          if (!this.searchQuery) {
            return this.mandors;
          }
          const q = this.searchQuery.toString().toUpperCase();
          return this.mandors.filter(m =>
            m.name.toUpperCase().includes(q) ||
            m.id.toString().toUpperCase().includes(q)
          );
        },

        selectMandor(mandor) {
          this.selected = mandor;
          this.open = false;
        },
      }
    }
  </script>
</x-layout>