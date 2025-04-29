<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="mx-auto bg-white rounded-md shadow-md p-6">
    <form action="{{ url()->current() }}" method="POST">
      @csrf

      <!-- No RKH -->
      <div class="mb-6 w-1/4">
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

      <!-- Header Fields -->
      <div class="grid grid-cols-2 gap-4 mb-6 w-1/2">
        <div>
          <label for="tanggal" class="block text-xs font-medium text-gray-700">Tanggal</label>
          <input
            type="text"
            name="tanggal"
            id="tanggal"
            placeholder="__/__/____"
            value="{{ date('d/m/Y') }}"
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
        <table id="rkh-table" class="min-w-full table-auto border-collapse">
          <thead>
            <tr>
              <th class="border px-2 py-1 text-xs w-12" rowspan="2">No.</th>
              <th class="border px-2 py-1 text-xs w-1/4" rowspan="2">Kegiatan</th>
              <th class="border px-2 py-1 text-xs w-12" rowspan="2">Blok</th>
              <th class="border px-2 py-1 text-xs w-16" rowspan="2">Luas (ha)</th>
              <th class="border px-2 py-1 text-xs text-center" colspan="3">Tenaga</th>
              <th class="border px-2 py-1 text-xs w-16" rowspan="2">Hasil</th>
              <th class="border px-2 py-1 text-xs w-12" rowspan="2">Satuan</th>
              <th class="border px-2 py-1 text-xs w-1/4" rowspan="2">Keterangan</th>
            </tr>
            <tr>
              <th class="border px-2 py-1 text-xs w-16">Laki-laki</th>
              <th class="border px-2 py-1 text-xs w-16">Perempuan</th>
              <th class="border px-2 py-1 text-xs w-16">Jumlah Tenaga</th>
            </tr>
          </thead>
          <tbody>
          <!-- Baris Input Data -->
          <!-- Baris 1 -->
          <tr class="rkh-row">
            <td class="border px-2 py-1 text-xs row-number">1</td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][nama]" value="W105 - Weeding" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][blok]" value="A" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][luas]" value="32" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[0][laki_laki]" value="3" class="w-full text-xs border-none focus:ring-0" min="0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[0][perempuan]" value="5" class="w-full text-xs border-none focus:ring-0" min="0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[0][jumlah_tenaga]" value="8" class="w-full text-xs border-none focus:ring-0" readonly></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][hasil]" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][satuan]" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][keterangan]" class="w-full text-xs border-none focus:ring-0"></td>
          </tr>
          <!-- Baris 2 -->
          <tr class="rkh-row">
            <td class="border px-2 py-1 text-xs row-number">2</td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][nama]" value="M102 - Sanitasi" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][blok]" value="B" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][luas]" value="22" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[1][laki_laki]" value="4" class="w-full text-xs border-none focus:ring-0" min="0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[1][perempuan]" value="1" class="w-full text-xs border-none focus:ring-0" min="0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[1][jumlah_tenaga]" value="5" class="w-full text-xs border-none focus:ring-0" readonly></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][hasil]" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][satuan]" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][keterangan]" class="w-full text-xs border-none focus:ring-0"></td>
          </tr>
          <!-- Baris 3 -->
          <tr class="rkh-row">
            <td class="border px-2 py-1 text-xs row-number">3</td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][nama]" value="D45 - Drainase" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][blok]" value="D" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][luas]" value="22" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[2][laki_laki]" value="1" class="w-full text-xs border-none focus:ring-0" min="0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[2][perempuan]" value="15" class="w-full text-xs border-none focus:ring-0" min="0"></td>
            <td class="border px-2 py-1 text-xs"><input type="number" name="rows[2][jumlah_tenaga]" value="16" class="w-full text-xs border-none focus:ring-0" readonly></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][hasil]" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][satuan]" class="w-full text-xs border-none focus:ring-0"></td>
            <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][keterangan]" class="w-full text-xs border-none focus:ring-0"></td>
          </tr>
        </tbody>
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

    // helper: attach sum listeners
    function attachSumListeners(row) {
      const laki = row.querySelector('input[name$="[laki_laki]"]');
      const perempuan = row.querySelector('input[name$="[perempuan]"]');
      const jumlah = row.querySelector('input[name$="[jumlah_tenaga]"]');
      const calc = () => {
        jumlah.value = (parseInt(laki.value) || 0) + (parseInt(perempuan.value) || 0);
      };
      laki.addEventListener('input', calc);
      perempuan.addEventListener('input', calc);
    }

    // helper: update nomor & index name
    function updateRowNumbers() {
      tbody.querySelectorAll('tr').forEach((row, i) => {
        row.querySelector('.row-number').textContent = i + 1;
        row.querySelectorAll('input').forEach(input => {
          input.name = input.name.replace(/rows\[\d+\]/, `rows[${i}]`);
        });
      });
    }

    // inisialisasi first row
    attachSumListeners(tbody.querySelector('tr'));

    // event Add
    addBtn.addEventListener('click', () => {
      const rows = tbody.querySelectorAll('tr');
      const index = rows.length;
      const newRow = rows[0].cloneNode(true);

      newRow.querySelectorAll('input').forEach(input => input.value = '');
      newRow.querySelector('.row-number').textContent = index + 1;
      newRow.querySelectorAll('input').forEach(input => {
        input.name = input.name.replace(/rows\[\d+\]/, `rows[${index}]`);
      });

      attachSumListeners(newRow);
      tbody.appendChild(newRow);
    });

    // event Remove Last Row
    removeBtn.addEventListener('click', () => {
      const rows = tbody.querySelectorAll('tr');
      if (rows.length > 1) {
        rows[rows.length - 1].remove();
        updateRowNumbers();
      }
    });
  });
</script>
</x-layout>
