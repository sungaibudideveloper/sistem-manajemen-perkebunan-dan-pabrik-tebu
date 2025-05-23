<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

<div class="bg-gray-50 rounded-lg p-6 mb-8 border border-blue-100">
  <div class="flex justify-between items-start">
    <!-- KIRI: No RKH + Mandor + Tanggal -->
    <div class="flex flex-col space-y-6 w-2/3">
      
      <!-- No RKH -->
      <div>
  <label for="rkhno" class="block text-sm font-semibold text-gray-700 mb-2">No RKH</label>
  <p id="rkhno" class="text-5xl font-mono tracking-wider text-gray-800">
  {{ $rkhno ?? '-' }}
</p>
</div>

      <!-- Mandor & Tanggal -->
      <div x-data="mandorPicker()" class="grid grid-cols-2 gap-6 max-w-md">
        <!-- Input Mandor -->
        <div>
          <label for="mandor" class="block text-sm font-semibold text-gray-700 mb-2">Mandor</label>
          <input
            type="text"
            name="mandor"
            id="mandor"
            readonly
            placeholder="Pilih Mandor"
            @click="open = true"
            :value="selected.id && selected.name ? `${selected.id} – ${selected.name}` : ''"
            class="w-full text-sm border-2 border-gray-200 rounded-lg px-4 py-3 cursor-pointer bg-white hover:bg-gray-50 transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
          <input type="hidden" name="mandor_id" x-model="selected.id">
        </div>

        <!-- Input Tanggal -->
        <div>
          <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
          <input
            type="date"
            name="tanggal"
            id="tanggal"
            value="{{ date('Y-m-d') }}"
            class="w-full text-sm border-2 border-gray-200 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
        </div>

        <!-- include modal di sini jika perlu -->
        @include('input.kerjaharian.rencanakerjaharian.modal-mandor')
      </div>
    </div>

    <!-- KANAN: Ringkasan Tenaga Kerja -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 min-w-[320px]">
      <div class="flex items-center mb-4">
        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
        <h3 class="text-sm font-bold text-gray-800">Absen Hari Ini</h3>
      </div>
      <p class="text-xs text-gray-600 mb-3">{{ date('d F Y') }}</p>
      <div class="grid grid-cols-3 gap-4 text-center">
        <div class="bg-blue-50 rounded-lg p-3">
                <div class="text-lg font-bold" id="summary-laki">0</div>
                <div class="text-xs text-gray-600">Laki-laki</div>
              </div>
              <div class="bg-pink-50 rounded-lg p-3">
                <div class="text-lg font-bold" id="summary-perempuan">0</div>
                <div class="text-xs text-gray-600">Perempuan</div>
              </div>
              <div class="bg-green-50 rounded-lg p-3">
                <div class="text-lg font-bold" id="summary-total">0</div>
                <div class="text-xs text-gray-600">Total</div>
              </div>
      </div>
    </div>
  </div>

      <!-- Modern Table -->
      <div class="bg-white mt-12 rounded-xl p-6 border border-gray-300 border-r shadow-md">
        <div class="overflow-x-auto">
          <table id="rkh-table" class="table-fixed w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
            <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
              <tr>
                <th class="px-4 py-4 text-xs font-semibold w-12" rowspan="2">No.</th>
                <th class="px-4 py-4 text-xs font-semibold w-60" rowspan="2">Aktivitas</th>
                <th class="px-4 py-4 text-xs font-semibold w-12" rowspan="2">Blok</th>
                <th class="px-4 py-4 text-xs font-semibold w-12" rowspan="2">Plot</th>
                <th class="px-4 py-4 text-xs font-semibold w-16" rowspan="2">Luas<br>(ha)</th>
                <th class="px-4 py-4 text-xs font-semibold text-center w-32" colspan="3">Tenaga Kerja</th>
                <th class="px-4 py-4 text-xs font-semibold w-24" rowspan="2">Estimasi<br>Waktu</th>
                <th class="px-4 py-4 text-xs font-semibold w-20" rowspan="2">Material</th>
                <th class="px-4 py-4 text-xs font-semibold w-32" rowspan="2">Keterangan</th>
              </tr>
              <tr class="bg-gray-700">
                <th class="px-4 py-3 text-xs font-medium">L</th>
                <th class="px-4 py-3 text-xs font-medium">P</th>
                <th class="px-4 py-3 text-xs font-medium">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @for ($i = 0; $i < 8; $i++)
                <tr class="rkh-row hover:bg-blue-50 transition-colors">
                  <td class="px-4 py-4 text-sm text-center font-medium text-gray-600 bg-gray-50">{{ $i + 1 }}</td>
                  <td class="px-4 py-4" x-data="activityPicker()">
                    <div class="relative">
                      <input
                        type="text"
                        readonly
                        placeholder=""
                        @click="open = true"
                        :value="selected.activitycode && selected.activityname ? `${selected.activitycode} – ${selected.activityname}` : ''"
                        class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 cursor-pointer hover:border-blue-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        :class="selected.activitycode ? 'bg-blue-50 text-blue-900' : 'bg-gray-50 text-gray-500'"
                      >
                      <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                      </div>
                    </div>
                    <input type="hidden" name="rows[{{ $i }}][nama]" x-model="selected.activitycode">
                    @include('input.kerjaharian.rencanakerjaharian.modal-activity')
                  </td>
                  <td class="px-4 py-4">
                    <input type="text" name="rows[{{ $i }}][blok]" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-4 py-4">
                    <input type="text" name="rows[{{ $i }}][plot]" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-4 py-4">
                    <input type="number" name="rows[{{ $i }}][luas]" step="0.01" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-4 py-4">
                    <input type="number" name="rows[{{ $i }}][laki_laki]" min="0" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-4 py-4">
                    <input type="number" name="rows[{{ $i }}][perempuan]" min="0" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-4 py-4">
                    <input type="number" name="rows[{{ $i }}][jumlah_tenaga]" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right bg-gray-50 font-semibold text-gray-700" readonly>
                  </td>
                  <td class="px-4 py-4">
                    <input type="text" name="rows[{{ $i }}][estimasiwaktu]" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-4 py-4 text-center">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                      </svg>
                      Ya
                    </span>
                  </td>
                  <td class="px-4 py-4">
                    <input type="text" name="rows[{{ $i }}][keterangan]" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                </tr>
              @endfor
            </tbody>
            <tfoot class="bg-gray-100">
              <tr class="border-t-2 border-gray-200">
                <td colspan="4" class="px-4 py-4 text-center text-sm font-bold uppercase tracking-wider text-gray-700 bg-gray-100">Total</td>
                <td id="total-luas" class="px-4 py-4 text-center text-sm font-bold bg-gray-50">0</td>
                <td id="total-laki" class="px-4 py-4 text-center text-sm font-bold bg-blue-50">0</td>
                <td id="total-perempuan" class="px-4 py-4 text-center text-sm font-bold bg-red-50">0</td>
                <td id="total-tenaga" class="px-4 py-4 text-center text-sm font-bold bg-green-50">0</td>
                <td colspan="3" class="px-4 py-4 bg-gray-100"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-8 flex flex-col items-center space-y-4">
        <!-- Secondary Actions -->
        <div class="flex justify-center space-x-4">
          <button
            type="button"
            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Preview
          </button>
          <button
            type="button"
            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print
          </button>
          <button
            type="button"
            onclick="window.history.back()"
            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
          </button>
        </div>
        
        <!-- Primary Submit Button -->
        <button
          type="submit"
          class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-12 py-4 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          Submit RKH
        </button>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
      rows.forEach(row => attachListeners(row));
      calculateTotals();
    });

    function calculateRow(row) {
      const lakiInput = row.querySelector('input[name$="[laki_laki]"]');
      const perempuanInput = row.querySelector('input[name$="[perempuan]"]');
      const jumlahInput = row.querySelector('input[name$="[jumlah_tenaga]"]');
      const laki = parseInt(lakiInput.value) || 0;
      const perempuan = parseInt(perempuanInput.value) || 0;
      if (jumlahInput) jumlahInput.value = laki + perempuan;
    }

    function calculateTotals() {
      let luasSum = 0, lakiSum = 0, perempuanSum = 0, tenagaSum = 0;
      document.querySelectorAll('#rkh-table tbody tr.rkh-row').forEach(row => {
        const luas = parseFloat(row.querySelector('input[name$="[luas]"]').value) || 0;
        const laki = parseInt(row.querySelector('input[name$="[laki_laki]"]').value) || 0;
        const perempuan = parseInt(row.querySelector('input[name$="[perempuan]"]').value) || 0;
        luasSum += luas;
        lakiSum += laki;
        perempuanSum += perempuan;
        tenagaSum += laki + perempuan;
        calculateRow(row);
      });
      document.getElementById('total-luas').textContent = `${luasSum.toFixed(2)} ha`;
      document.getElementById('total-laki').textContent = lakiSum;
      document.getElementById('total-perempuan').textContent = perempuanSum;
      document.getElementById('total-tenaga').textContent = tenagaSum;
      document.getElementById('summary-laki').textContent = lakiSum;
      document.getElementById('summary-perempuan').textContent = perempuanSum;
      document.getElementById('summary-total').textContent = tenagaSum;
    }

    function attachListeners(row) {
      ['[laki_laki]', '[perempuan]', '[luas]'].forEach(suffix => {
        const input = row.querySelector(`input[name$="${suffix}"]`);
        if (input) input.addEventListener('input', () => calculateTotals());
      });
    }
  </script>
</x-layout>
