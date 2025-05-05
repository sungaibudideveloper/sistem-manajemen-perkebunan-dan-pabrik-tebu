<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- Tabs Navigation -->
  <div class="mx-auto mt-6">
    <ul class="flex border-b">
      <li class="-mb-px mr-1">
        <a href="#" id="tab-daily" class="bg-white inline-block py-2 px-4 font-semibold text-blue-700 border-l border-t border-r rounded-t">
          DT Harian
        </a>
      </li>
      <li class="mr-1">
        <a href="#" id="tab-borongan" class="bg-white inline-block py-2 px-4 font-semibold text-blue-500 hover:text-blue-700">
          DT Borongan
        </a>
      </li>
    </ul>
  </div>

  <!-- Tab Contents -->
  <div class="mx-auto bg-white rounded-md shadow-md p-6 mt-4">
    {{-- DT Harian --}}
    <div id="content-daily">
      <form action="{{ url()->current() }}" method="POST">
        @csrf
        <h2 class="text-lg font-semibold mb-4">RKH No: RKH2904125 (DT Harian)</h2>

        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label for="tanggal" class="block text-xs font-medium text-gray-700">Hari / Tanggal</label>
            <input type="text" name="tanggal" id="tanggal" placeholder="__/__/____"
                   class="mt-1 block w-full text-xs border rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="divisi" class="block text-xs font-medium text-gray-700">Divisi</label>
            <input type="text" name="divisi" id="divisi" placeholder="Divisi"
                   class="mt-1 block w-full text-xs border rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>

        <div class="overflow-x-auto">
          <table id="table-daily" class="min-w-full table-auto border-collapse mb-2">
            <thead>
              <tr>
                <th class="border px-2 py-1 text-xs">No.</th>
                <th class="border px-2 py-1 text-xs">Mandor</th>
                <th class="border px-2 py-1 text-xs">Kegiatan</th>
                <th class="border px-2 py-1 text-xs">Blok</th>
                <th class="border px-2 py-1 text-xs">Plot</th>
                <th class="border px-2 py-1 text-xs">RKH</th>
                <th class="border px-2 py-1 text-xs text-center" colspan="3">Tenaga</th>
                <th class="border px-2 py-1 text-xs">Keterangan</th>
              </tr>
              <tr>
                <th></th><th></th><th></th><th></th><th></th><th></th>
                <th class="border px-2 py-1 text-xs">Laki-laki</th>
                <th class="border px-2 py-1 text-xs">Perempuan</th>
                <th class="border px-2 py-1 text-xs">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border px-2 py-1 text-xs row-number">1</td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][mandor]" value="Pak Budi" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][kegiatan]" value="Pengecatan" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][blok]" value="A1" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][plot]" value="101" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][rkh]" value="100" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[0][laki]" value="5" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[0][perempuan]" value="2" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[0][total]" value="7" class="w-full text-xs border-none focus:ring-0 total-tenaga" readonly></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[0][keterangan]" value="-" class="w-full text-xs border-none focus:ring-0"></td>
              </tr>
              <tr>
                <td class="border px-2 py-1 text-xs row-number">2</td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][mandor]" value="Ibu Siti" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][kegiatan]" value="Keramik" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][blok]" value="B2" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][plot]" value="202" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][rkh]" value="150" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[1][laki]" value="4" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[1][perempuan]" value="3" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[1][total]" value="7" class="w-full text-xs border-none focus:ring-0 total-tenaga" readonly></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[1][keterangan]" value="-" class="w-full text-xs border-none focus:ring-0"></td>
              </tr>
              <tr>
                <td class="border px-2 py-1 text-xs row-number">3</td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][mandor]" value="Pak Tono" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][kegiatan]" value="Listrik" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][blok]" value="C3" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][plot]" value="303" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][rkh]" value="80" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[2][laki]" value="3" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[2][perempuan]" value="1" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="rows[2][total]" value="4" class="w-full text-xs border-none focus:ring-0 total-tenaga" readonly></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="rows[2][keterangan]" value="-" class="w-full text-xs border-none focus:ring-0"></td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td class="border px-2 py-1 text-xs text-right" colspan="5"><strong>Total:</strong></td>
                <td class="border px-2 py-1 text-xs" id="sum-rkh">330</td>
                <td class="border px-2 py-1 text-xs" id="sum-laki">12</td>
                <td class="border px-2 py-1 text-xs" id="sum-perempuan">6</td>
                <td class="border px-2 py-1 text-xs" id="sum-total">18</td>
                <td class="border px-2 py-1 text-xs"></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="flex space-x-2 mb-4">
          <button type="button" id="add-row-daily" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-xs">Add Row</button>
          <button type="button" id="remove-row-daily" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-xs">Remove Last Row</button>
        </div>

        <div class="mt-4 flex justify-center space-x-6">
          <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm">Preview</button>
          <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm">Print</button>
          <button type="button" onclick="window.history.back()" class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm">Back</button>
        </div>
        <div class="mt-4 flex justify-center space-x-6">
          <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-12 py-3 rounded-md text-sm">Submit</button>
        </div>
      </form>
    </div>

    {{-- DT Borongan --}}
    <div id="content-borongan" class="hidden">
      <form action="{{ url()->current() }}" method="POST">
        @csrf
        <h2 class="text-lg font-semibold mb-4">RKH No: RKH2904125 (DT Borongan)</h2>

        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label for="tanggal_b" class="block text-xs font-medium text-gray-700">Hari / Tanggal</label>
            <input type="text" name="tanggal_b" id="tanggal_b" placeholder="__/__/____"
                   class="mt-1 block w-full text-xs border rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>
          <div>
            <label for="divisi_b" class="block text-xs font-medium text-gray-700">Divisi</label>
            <input type="text" name="divisi_b" id="divisi_b" placeholder="Divisi"
                   class="mt-1 block w-full text-xs border rounded-md focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>

        <div class="overflow-x-auto">
          <table id="table-borongan" class="min-w-full table-auto border-collapse mb-2">
            <thead>
              <tr>
                <th class="border px-2 py-1 text-xs">No.</th>
                <th class="border px-2 py-1 text-xs">Mandor</th>
                <th class="border px-2 py-1 text-xs">Kegiatan</th>
                <th class="border px-2 py-1 text-xs">Blok</th>
                <th class="border px-2 py-1 text-xs">Plot</th>
                <th class="border px-2 py-1 text-xs">RKH</th>
                <th class="border px-2 py-1 text-xs text-center" colspan="3">Tenaga</th>
                <th class="border px-2 py-1 text-xs">Keterangan</th>
              </tr>
              <tr>
                <th></th><th></th><th></th><th></th><th></th><th></th>
                <th class="border px-2 py-1 text-xs">Laki-laki</th>
                <th class="border px-2 py-1 text-xs">Perempuan</th>
                <th class="border px-2 py-1 text-xs">Total</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border px-2 py-1 text-xs row-number">1</td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[0][mandor]" value="Pak Wawan" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[0][kegiatan]" value="Pengaspalan" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[0][blok]" value="D4" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[0][plot]" value="404" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[0][rkh]" value="200" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[0][laki]" value="6" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[0][perempuan]" value="3" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[0][total]" value="9" class="w-full text-xs border-none focus:ring-0 total-tenaga" readonly></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[0][keterangan]" value="-" class="w-full text-xs border-none focus:ring-0"></td>
              </tr>
              <tr>
                <td class="border px-2 py-1 text-xs row-number">2</td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[1][mandor]" value="Ibu Indah" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[1][kegiatan]" value="Pagar" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[1][blok]" value="E5" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[1][plot]" value="505" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[1][rkh]" value="120" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[1][laki]" value="4" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[1][perempuan]" value="2" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[1][total]" value="6" class="w-full text-xs border-none focus:ring-0 total-tenaga" readonly></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[1][keterangan]" value="-" class="w-full text-xs border-none focus:ring-0"></td>
              </tr>
              <tr>
                <td class="border px-2 py-1 text-xs row-number">3</td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[2][mandor]" value="Pak Agus" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[2][kegiatan]" value="Penimbunan" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[2][blok]" value="F6" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[2][plot]" value="606" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[2][rkh]" value="180" class="w-full text-xs border-none focus:ring-0"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[2][laki]" value="7" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[2][perempuan]" value="1" class="w-full text-xs border-none focus:ring-0 tenaga"></td>
                <td class="border px-2 py-1 text-xs"><input type="number" name="brows[2][total]" value="8" class="w-full text-xs border-none focus:ring-0 total-tenaga" readonly></td>
                <td class="border px-2 py-1 text-xs"><input type="text" name="brows[2][keterangan]" value="-" class="w-full text-xs border-none focus:ring-0"></td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td class="border px-2 py-1 text-xs text-right" colspan="5"><strong>Total:</strong></td>
                <td class="border px-2 py-1 text-xs" id="bsum-rkh">500</td>
                <td class="border px-2 py-1 text-xs" id="bsum-laki">17</td>
                <td class="border px-2 py-1 text-xs" id="bsum-perempuan">6</td>
                <td class="border px-2 py-1 text-xs" id="bsum-total">23</td>
                <td class="border px-2 py-1 text-xs"></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <div class="flex space-x-2 mb-4">
          <button type="button" id="add-row-bor" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-xs">Add Row</button>
          <button type="button" id="remove-row-bor" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-xs">Remove Last Row</button>
        </div>

        <div class="mt-4 flex justify-center space-x-6">
          <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm">Preview</button>
          <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm">Print</button>
          <button type="button" onclick="window.history.back()" class="bg-gray-500 hover:bg-gray-600 text-white px-12 py-3 rounded-md text-sm">Back</button>
        </div>
        <div class="mt-4 flex justify-center space-x-6">
          <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-12 py-3 rounded-md text-sm">Submit</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Tab switch
      const tabDaily = document.getElementById('tab-daily');
      const tabBor = document.getElementById('tab-borongan');
      const contentDaily = document.getElementById('content-daily');
      const contentBor = document.getElementById('content-borongan');
      tabDaily.addEventListener('click', e => {
        e.preventDefault();
        contentDaily.classList.remove('hidden');
        contentBor.classList.add('hidden');
        tabDaily.classList.add('text-blue-700','border-l','border-t','border-r');
        tabBor.classList.remove('text-blue-700','border-l','border-t','border-r');
      });
      tabBor.addEventListener('click', e => {
        e.preventDefault();
        contentBor.classList.remove('hidden');
        contentDaily.classList.add('hidden');
        tabBor.classList.add('text-blue-700','border-l','border-t','border-r');
        tabDaily.classList.remove('text-blue-700','border-l','border-t','border-r');
      });

      // Generic row handlers
      function setupTable(tableId, addBtnId, removeBtnId, sumIdsPrefix) {
        const table = document.getElementById(tableId);
        const tbody = table.querySelector('tbody');
        const addBtn = document.getElementById(addBtnId);
        const removeBtn = document.getElementById(removeBtnId);

        function updateRowNumbers() {
          tbody.querySelectorAll('tr').forEach((row, i) => {
            row.querySelector('.row-number').textContent = i+1;
            row.querySelectorAll('input').forEach(input => {
              input.name = input.name.replace(/\[\\d+\]/, `[${i}]`);
            });
          });
        }

        function updateTotals() {
          let sumR = 0, sumL=0, sumP=0, sumT=0;
          tbody.querySelectorAll('tr').forEach(row => {
            const r = parseFloat(row.querySelector('input[name$="[rkh]"]').value)||0;
            const l = parseInt(row.querySelector('input[name$="[laki]"]').value)||0;
            const p = parseInt(row.querySelector('input[name$="[perempuan]"]').value)||0;
            const t = l+p;
            sumR += r; sumL += l; sumP += p; sumT += t;
            row.querySelector('.total-tenaga').value = t;
          });
          document.getElementById(sumIdsPrefix+'-rkh').textContent = sumR;
          document.getElementById(sumIdsPrefix+'-laki').textContent = sumL;
          document.getElementById(sumIdsPrefix+'-perempuan').textContent = sumP;
          document.getElementById(sumIdsPrefix+'-total').textContent = sumT;
        }

        // Listeners
        addBtn.addEventListener('click', () => {
          const clone = tbody.querySelector('tr').cloneNode(true);
          clone.querySelectorAll('input').forEach(inp => inp.value = '');
          tbody.appendChild(clone);
          updateRowNumbers();
          setupTenagaListeners(clone);
          updateTotals();
        });
        removeBtn.addEventListener('click', () => {
          const rows = tbody.querySelectorAll('tr');
          if (rows.length>1) { rows[rows.length-1].remove(); updateTotals(); updateRowNumbers(); }
        });

        function setupTenagaListeners(row) {
          row.querySelectorAll('.tenaga').forEach(inp => {
            inp.addEventListener('input', updateTotals);
          });
        }
        // init
        tbody.querySelectorAll('tr').forEach(r => setupTenagaListeners(r));
        updateTotals();
      }

      setupTable('table-daily','add-row-daily','remove-row-daily','sum');
      setupTable('table-borongan','add-row-bor','remove-row-bor','bsum');
    });
  </script>
</x-layout>
