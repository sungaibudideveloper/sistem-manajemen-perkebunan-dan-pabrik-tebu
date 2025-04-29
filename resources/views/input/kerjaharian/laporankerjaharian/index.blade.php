<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div class="mx-auto bg-white rounded-md shadow-md p-6">
    <form action="{{ url()->current() }}" method="POST">
      @csrf

      <!-- Judul Form -->
      <h2 class="text-lg font-semibold mb-4">RKH2904125 - LKH002345</h2>

      <!-- Header Fields -->
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label for="tanggal" class="block text-xs font-medium text-gray-700">Hari / Tanggal</label>
          <input type="text" name="tanggal" id="tanggal" placeholder="__/__/____"
                 class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label for="divisi" class="block text-xs font-medium text-gray-700">Divisi</label>
          <input type="text" name="divisi" id="divisi" placeholder="Divisi"
                 class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label for="kegiatan" class="block text-xs font-medium text-gray-700">Kegiatan</label>
          <input type="text" name="kegiatan" id="kegiatan" placeholder="Kegiatan"
                 class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label for="mandor" class="block text-xs font-medium text-gray-700">Mandor</label>
          <input type="text" name="mandor" id="mandor" placeholder="Mandor"
                 class="mt-1 block w-full text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>

      <!-- Tabel Input -->
      <div class="overflow-x-auto">
        <table id="rkh-table" class="min-w-full table-auto border-collapse">
          <thead>
            <tr>
              <th class="border px-2 py-1 text-xs">No.</th>
              <th class="border px-2 py-1 text-xs">Nama</th>
              <th class="border px-2 py-1 text-xs">No KTP</th>
              <th class="border px-2 py-1 text-xs">Blok</th>
              <th class="border px-2 py-1 text-xs">Plot</th>
              <th class="border px-2 py-1 text-xs" colspan="3">Hasil</th>
              <th class="border px-2 py-1 text-xs" colspan="2">Jam Kerja</th>
              <th class="border px-2 py-1 text-xs">Premi</th>
              <th class="border px-2 py-1 text-xs">Rp.</th>
              <th class="border px-2 py-1 text-xs">Material</th>
            </tr>
            <tr>
              <th></th><th></th><th></th><th></th><th></th>
              <th class="border px-2 py-1 text-xs">Luas</th>
              <th class="border px-2 py-1 text-xs">Hasil</th>
              <th class="border px-2 py-1 text-xs">Sisa</th>
              <th class="border px-2 py-1 text-xs">Jam Datang</th>
              <th class="border px-2 py-1 text-xs">Jam Pulang</th>
              <th></th><th></th><th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border px-2 py-1 text-xs row-number">1</td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][nama]" value="Budi" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][no_ktp]" value="1234567890123456" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][blok]" value="A1" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][plot]" value="101" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][luas]" value="100" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][hasil]" value="90" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][sisa]" value="10" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][jam_datang]" value="07:00" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][jam_pulang]" value="15:00" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][premi]" value="50" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][rp]" value="90000" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][material]" value="Semprotan" class="w-full text-xs border-none focus:ring-0"></td>
            </tr>
            <tr>
              <td class="border px-2 py-1 text-xs row-number">2</td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][nama]" value="Siti" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][no_ktp]" value="2345678901234567" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][blok]" value="B2" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][plot]" value="202" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][luas]" value="150" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][hasil]" value="140" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][sisa]" value="10" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][jam_datang]" value="08:00" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][jam_pulang]" value="16:00" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][premi]" value="60" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][rp]" value="140000" class="w-full text-xs border-none focus:ring-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][material]" value="Pupuk" class="w-full text-xs border-none focus:ring-0"></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td class="border px-2 py-1 text-xs" colspan="5"><strong>Total</strong></td>
              <td class="border px-2 py-1 text-xs"><strong>250</strong></td>
              <td class="border px-2 py-1 text-xs"><strong>230</strong></td>
              <td class="border px-2 py-1 text-xs"><strong>20</strong></td>
              <td class="border px-2 py-1 text-xs" colspan="2"></td>
              <td class="border px-2 py-1 text-xs"></td>
              <td class="border px-2 py-1 text-xs"><strong>230000</strong></td>
              <td class="border px-2 py-1 text-xs"></td>
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
        >Add Row</button>
        <button
          type="button"
          id="remove-row"
          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-xs"
        >Remove Last Row</button>
      </div>

      <!-- Tombol Preview & Print -->
      <div class="mt-6 flex justify-center space-x-6">
        <button
          type="button"
          class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm"
        >Preview</button>
        <button
          type="button"
          class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm"
        >Print</button>
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
        >Submit</button>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const tbody = document.querySelector('#rkh-table tbody');
      const addBtn = document.getElementById('add-row');
      const removeBtn = document.getElementById('remove-row');

      // Hitung jumlah tenaga (Laki+Perempuan)
      function attachSumListeners(row) {
        const laki = row.querySelector('input[name$="[jam_datang]"]'); // jika ada field jam, ubah sesuai
        const perempuan = row.querySelector('input[name$="[jam_pulang]"]');
        const hasil = row.querySelector('input[name$="[sisa]"]');
        // contoh, sesuaikan kalkulasi sesuai yang diinginkan...
      }

      // Update nomor baris & indeks name
      function updateRowNumbers() {
        tbody.querySelectorAll('tr').forEach((row, i) => {
          row.querySelector('.row-number').textContent = i + 1;
          row.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/rows\[\d+\]/, `rows[${i}]`);
          });
        });
      }

      // Inisialisasi: attach listener & normalisasi index
      tbody.querySelectorAll('tr').forEach(row => {
        // kalau mau kalkulasi otomatis, panggil attachSumListeners(row);
      });
      updateRowNumbers();

      // Tambah baris baru (clone first row & clear value)
      addBtn.addEventListener('click', () => {
        const rows = tbody.querySelectorAll('tr');
        const index = rows.length;
        const newRow = rows[0].cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        tbody.appendChild(newRow);
        updateRowNumbers();
      });

      // Hapus baris terakhir (pastikan minimal 1 baris tetap ada)
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
