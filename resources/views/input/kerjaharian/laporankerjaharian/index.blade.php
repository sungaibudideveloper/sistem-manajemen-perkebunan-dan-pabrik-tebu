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
      <div class="grid grid-cols-4 gap-4 mb-6 w-1/3">
        <div>
          <label class="block text-xs font-medium text-gray-700">Mandor</label>
          <p class="mt-1 text-xs text-gray-900">Gerald</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Tanggal</label>
          <p class="mt-1 text-xs text-gray-900">29/04/2025</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Kegiatan</label>
          <p class="mt-1 text-xs text-gray-900">W105 - Weeding</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700">Divisi</label>
          <p class="mt-1 text-xs text-gray-900">Divisi 1</p>
        </div>
        
      </div>

      <!-- Tabel Input -->
      <div class="overflow-x-auto">
        <table id="rkh-table" class="min-w-full table-fixed border-collapse">
          <thead>
            <tr>
              <th class="border px-2 py-1 text-xs w-[40px]" rowspan="2">No.</th>
              <th class="border px-2 py-1 text-xs w-[120px]" rowspan="2">Nama</th>
              <th class="border px-2 py-1 text-xs w-[200px]" rowspan="2">No KTP</th>
              <th class="border px-2 py-1 text-xs w-[80px]" rowspan="2">Blok</th>
              <th class="border px-2 py-1 text-xs w-[80px]" rowspan="2">Plot</th>
              <th class="border px-2 py-1 text-xs text-center w-[240px]" colspan="3">Hasil</th>
              <th class="border px-2 py-1 text-xs text-center w-[160px]" colspan="2">Jam Kerja</th>
              <th class="border px-2 py-1 text-xs w-[80px]" rowspan="2">Premi</th>
              <th class="border px-2 py-1 text-xs w-[100px]" rowspan="2">Rp.</th>
              <th class="border px-2 py-1 text-xs w-[150px]" rowspan="2">Material</th>
            </tr>
            <tr>
              <th class="border px-2 py-1 text-xs w-[80px]">Luas</th>
              <th class="border px-2 py-1 text-xs w-[80px]">Hasil</th>
              <th class="border px-2 py-1 text-xs w-[80px]">Sisa</th>
              <th class="border px-2 py-1 text-xs w-[80px]">Jam Datang</th>
              <th class="border px-2 py-1 text-xs w-[80px]">Jam Pulang</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border px-2 py-1 text-xs row-number text-center">1</td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][nama]" value="Budi" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][no_ktp]" value="1234567890123456" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][blok]" value="A1" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][plot]" value="A101" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border  text-xs"><input type="number" name="rows[0][luas]" value="100" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="number" name="rows[0][hasil]" value="90" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="number" name="rows[0][sisa]" value="10" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="time" name="rows[0][jam_datang]" value="07:00" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="time" name="rows[0][jam_pulang]" value="15:00" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="number" name="rows[0][premi]" value="50" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border px-4 py-1 text-xs"><input type="text" name="rows[0][rp]" value="{{ number_format(90000, 0, ',', '.') }}" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][material]" value="Semprotan" class="w-full text-xs border-none focus:ring-0"></td>
            </tr>
            <tr>
              <td class="border px-2 py-1 text-xs row-number text-center"">2</td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][nama]" value="Siti" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][no_ktp]" value="2345678901234567" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][blok]" value="B2" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][plot]" value="B202" class="w-full text-xs border-none focus:ring-0 text-center""></td>
              <td class="border  text-xs"><input type="number" name="rows[1][luas]" value="150" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="number" name="rows[1][hasil]" value="140" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="number" name="rows[1][sisa]" value="10" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="time" name="rows[1][jam_datang]" value="08:00" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="time" name="rows[1][jam_pulang]" value="16:00" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border  text-xs"><input type="number" name="rows[1][premi]" value="60" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border px-4 py-1 text-xs"><input type="text" name="rows[0][rp]" value="{{ number_format(90000, 0, ',', '.') }}" class="w-full text-xs border-none focus:ring-0 text-right p-0"></td>
              <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][material]" value="Pupuk" class="w-full text-xs border-none focus:ring-0"></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td class="border px-2 py-1 text-xs text-right" colspan="5"><strong>Total</strong></td>
              <td class="border px-4 py-1 text-xs text-right"><strong>250</strong></td>
              <td class="border px-4 py-1 text-xs text-right"><strong>230</strong></td>
              <td class="border px-4 py-1 text-xs text-right"><strong>20</strong></td>
              <td class="border px-4 py-1 text-xs text-right" colspan="2"></td>
              <td class="border px-2 py-1 text-xs"></td>
              <td class="border px-4 py-1 text-xs text-right"><strong>{{ number_format(180000, 0, ',', '.') }}</strong></td>
              <td class="border px-4 py-1 text-xs text-right"></td>
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
