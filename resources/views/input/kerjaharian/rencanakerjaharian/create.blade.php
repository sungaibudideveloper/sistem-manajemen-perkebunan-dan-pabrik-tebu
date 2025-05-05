<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="mx-auto bg-white rounded-md shadow-md p-6">

  <!-- Baris 1: No RKH (kiri) + Summary (kanan) -->
<div class="flex justify-between items-start mb-2">
  <!-- No RKH -->
  <div class="w-1/5">
    <label for="no_rkh" class="block text-xs font-medium text-gray-700">No RKH</label>
    <input
      type="text"
      name="no_rkh"
      id="no_rkh"
      placeholder="No RKH"
      value="RKH2904125"
      class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
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

<!-- Baris 2: Mandor, Tanggal, Divisi -->
<div class="grid grid-cols-3 gap-4 mb-6 w-1/3">
  <div>
    <label for="mandor" class="block text-xs font-medium text-gray-700">Mandor</label>
    <input
      type="text"
      name="mandor"
      id="mandor"
      placeholder="Pilih Mandor"
      value="Gerald"
      class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
    >
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
  <div>
    <label for="divisi" class="block text-xs font-medium text-gray-700">Divisi</label>
    <input
      type="text"
      name="divisi"
      id="divisi"
      value="Divisi 1"
      placeholder="Divisi"
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

    function calculateRow(row) {
      const laki = row.querySelector('input[name$="[laki_laki]"]');
      const perempuan = row.querySelector('input[name$="[perempuan]"]');
      const jumlah = row.querySelector('input[name$="[jumlah_tenaga]"]');
      jumlah.value = (parseInt(laki.value) || 0) + (parseInt(perempuan.value) || 0);
    }

    function calculateTotals() {
      let luasSum = 0, lakiSum = 0, perempuanSum = 0, tenagaSum = 0;
      tbody.querySelectorAll('tr').forEach(row => {
        const luas = parseFloat(row.querySelector('input[name$="[luas]"]').value) || 0;
        const laki = parseInt(row.querySelector('input[name$="[laki_laki]"]').value) || 0;
        const perempuan = parseInt(row.querySelector('input[name$="[perempuan]"]').value) || 0;
        const jumlah = laki + perempuan;

        luasSum += luas;
        lakiSum += laki;
        perempuanSum += perempuan;
        tenagaSum += jumlah;

        row.querySelector('input[name$="[jumlah_tenaga]"]').value = jumlah;
      });
      totalLuas.textContent = luasSum;
      totalLaki.textContent = lakiSum;
      totalPerempuan.textContent = perempuanSum;
      totalTenaga.textContent = tenagaSum;
    }

    function attachListeners(row) {
      ['[laki_laki]', '[perempuan]', '[luas]'].forEach(suffix => {
        row.querySelector(`input[name$="${suffix}"]`).addEventListener('input', () => {
          calculateRow(row);
          calculateTotals();
        });
      });
    }

    function updateRowNumbers() {
      tbody.querySelectorAll('tr').forEach((row, i) => {
        row.querySelector('.row-number').textContent = i + 1;
        row.querySelectorAll('input').forEach(input => {
          input.name = input.name.replace(/rows\[\d+\]/, `rows[${i}]`);
        });
      });
    }

    // Inisialisasi baris awal
    tbody.querySelectorAll('tr').forEach(row => {
      attachListeners(row);
    });
    calculateTotals();

    addBtn.addEventListener('click', () => {
      const rows = tbody.querySelectorAll('tr');
      const newRow = rows[0].cloneNode(true);

      newRow.querySelectorAll('input').forEach(input => input.value = '');
      tbody.appendChild(newRow);

      attachListeners(newRow);
      updateRowNumbers();
      calculateTotals();
    });

    removeBtn.addEventListener('click', () => {
      const rows = tbody.querySelectorAll('tr');
      if (rows.length > 1) {
        rows[rows.length - 1].remove();
        updateRowNumbers();
        calculateTotals();
      }
      document.getElementById('summary-laki').textContent = lakiSum;
      document.getElementById('summary-perempuan').textContent = perempuanSum;
      document.getElementById('summary-total').textContent = tenagaSum; 
    });
  });
</script>
</x-layout>
