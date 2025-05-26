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
            <p>Laki-laki: <span id="summary-laki">0</span></p>
            <p>Perempuan: <span id="summary-perempuan">0</span></p>
            <p class="font-semibold">Total: <span id="summary-total">0</span></p>
          </div>
        </div>
      </div>

      <!-- Baris 2: Mandor, Tanggal -->
      <div x-data="mandorPicker()" class="grid grid-cols-3 gap-3 mb-6 w-1/3">
        <!-- Input Mandor -->
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

        @include('input.kerjaharian.rencanakerjaharian.modal-mandor')
      </div>

      <!-- Tabel Input (8 baris statis) -->
      <div class="overflow-x-auto">
        <table id="rkh-table" class="min-w-full table-fixed border-collapse">
          <thead>
            <tr>
              <th class="border px-2 py-1 text-xs w-[48px]" rowspan="2">No.</th>
              <th class="border px-2 py-1 text-xs w-[200px]" rowspan="2">Aktivitas</th>
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
            @for ($i = 0; $i < 8; $i++)
              <tr class="rkh-row">
                <td class="border px-2 py-1 text-xs text-center">{{ $i + 1 }}</td>
                <td class="border px-2 py-1 text-xs" x-data="activityPicker()">
                  <input
                    type="text"
                    readonly
                    placeholder="Klik untuk Memilih Aktivitas"
                    @click="open = true"
                    :value="selected.activitycode && selected.activityname ? `${selected.activitycode} – ${selected.activityname}` : ''"
                    class="w-full text-xs border-none focus:ring-0 cursor-pointer"
                  >
                  <input type="hidden" name="rows[{{ $i }}][nama]" x-model="selected.activitycode">
                  @include('input.kerjaharian.rencanakerjaharian.modal-activity')
                </td>
                <td class="border text-xs">
                  <input type="text" name="rows[{{ $i }}][blok]" class="w-full text-xs border-none focus:ring-0 text-center">
                </td>
                <td class="border text-xs">
                  <input type="text" name="rows[{{ $i }}][plot]" class="w-full text-xs border-none focus:ring-0 text-center">
                </td>
                <td class="border text-xs">
                  <input type="number" name="rows[{{ $i }}][luas]" class="w-full text-xs border-none focus:ring-0 text-right p-0">
                </td>
                <td class="border text-xs">
                  <input type="number" name="rows[{{ $i }}][laki_laki]" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0">
                </td>
                <td class="border text-xs">
                  <input type="number" name="rows[{{ $i }}][perempuan]" class="w-full text-xs border-none focus:ring-0 text-right p-0" min="0">
                </td>
                <td class="border text-xs">
                  <input type="number" name="rows[{{ $i }}][jumlah_tenaga]" class="w-full text-xs border-none focus:ring-0 text-right p-0" readonly>
                </td>
                <td class="border text-xs">
                  <input type="text" name="rows[{{ $i }}][estimasiwaktu]" class="w-full text-xs border-none focus:ring-0 text-right">
                </td>
                <td class="border text-xs text-center">Yes</td>
                <td class="border text-xs">
                  <input type="text" name="rows[{{ $i }}][keterangan]" class="w-full text-xs border-none focus:ring-0">
                </td>
              </tr>
            @endfor
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
      document.getElementById('total-luas').textContent = luasSum;
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
