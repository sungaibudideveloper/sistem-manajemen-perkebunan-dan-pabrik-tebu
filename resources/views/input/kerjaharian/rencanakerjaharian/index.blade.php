<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="{ showLKHModal: false }" class="relative">
    <div class="mx-auto bg-white rounded-md shadow-md p-6">
        {{-- Search & Filters --}}

        <div class="flex flex-col md:flex-row justify-between mb-4">
            <div class="flex justify-between items-center w-full ">
            <form class="flex items-center space-x-2" action="#" method="GET">
                <input
                    type="text"
                    name="search"
                    placeholder="Search No RKH..."
                    class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <button
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded"
                >
                    Search
                </button>
            </form>
            <a
    href="{{ route('input.kerjaharian.rencanakerjaharian.store') }}"
    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs rounded"
  >
    Create RKH
  </a>
            </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between mb-4">
            <div class="flex items-center space-x-2 mt-2 md:mt-0">
                <select
                    name="filter_approval"
                    class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Approval</option>
                    <option>Approved</option>
                    <option>Waiting</option>
                    <option>Decline</option>
                </select>
                <select
                    name="filter_status"
                    class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">All Status</option>
                    <option>Done</option>
                    <option>On Progress</option>
                </select>
                </div>
            </div>

        {{-- Table View --}}
        <div class="overflow-x-auto">
    <table id="rkh-table" class="min-w-full table-auto border-collapse">
        <thead>
            <tr class="bg-gray-100 text-xs">
                <!-- Each header now has a button with up/down icons for sorting -->
                <th class="border px-2 py-1">
                    <button type="button" onclick="sortTable(0)" class="flex items-center space-x-1">
                        <span>No.</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </th>
                <th class="border px-2 py-1">
                    <button type="button" onclick="sortTable(1)" class="flex items-center space-x-1">
                        <span>No RKH</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </th>
                <th class="border px-2 py-1">
                    <button type="button" onclick="sortTable(2)" class="flex items-center space-x-1">
                        <span>Tanggal</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </th>
                <th class="border px-2 py-1">
                    <button type="button" onclick="sortTable(3)" class="flex items-center space-x-1">
                        <span>Approval</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </th>
                <th class="border px-2 py-1 text-center">
                    <button type="button" onclick="sortTable(4)" class="flex items-center justify-center space-x-1">
                        <span>Distribusi Tenaga Harian</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg xmlns="www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </th>
                <th class="border px-2 py-1 text-center">
                    <button type="button" onclick="sortTable(5)" class="flex items-center justify-center space-x-1">
                        <span>Laporan Kegiatan Harian</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </th>
                <th class="border px-2 py-1 text-center">
                    <button type="button" onclick="sortTable(6)" class="flex items-center justify-center space-x-1">
                        <span>Status</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </th>
                <th class="border px-2 py-1">Actions</th>
            </tr>
        </thead>
        <tbody>
                    {{-- Row 1 --}}
                    <tr class="text-xs">
                        <td class="border px-2 py-1">1</td>
                        <td class="border px-2 py-1">RKH2904125</td>
                        <td class="border px-2 py-1">29/04/2025</td>
                        <td class="border px-2 py-1">
                            <span class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded">
                                Approved
                            </span>
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <a
                                href="{{ route('input.kerjaharian.distribusitenagaharian.index', ['no_rkh' => 'RKH2904125']) }}"
                                class="text-white bg-blue-600 hover:bg-blue-700 px-2 py-0.5 rounded text-xs"
                            >
                                DTH
                            </a>
                        </td>
                        <td class="border px-2 py-1 text-center">
    <a href="#"
       @click.prevent="showLKHModal = true"
       class="text-white bg-green-600 hover:bg-green-700 px-2 py-0.5 rounded text-xs">
      LKH
    </a>
  </td>
                        <td class="border px-2 py-1 text-center">
                            <select
                                name="status"
                                class="text-xs border border-gray-300 rounded p-1"
                                data-old="Done"
                                onchange="confirmStatusChange(this)"
                            >
                                <option value="Done" selected>Done</option>
                                <option value="On Progress">On Progress</option>
                            </select>
                        </td>
                        <td class="border px-2 py-1">
                            <div class="flex items-center justify-center space-x-2">
                                {{-- Edit Button --}}
                                <button
                                    type="button"
                                    class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm"
                                >
                                    <svg
                                        class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                        aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <use xlink:href="#icon-edit-outline" />
                                    </svg>
                                    <svg
                                        class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                        aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <use xlink:href="#icon-edit-solid" />
                                        <use xlink:href="#icon-edit-solid2" />
                                    </svg>
                                </button>
                                {{-- Delete Button --}}
                                <button
                                    type="button"
                                    class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm"
                                >
                                    <svg
                                        class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                        aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <use xlink:href="#icon-trash-outline" />
                                    </svg>
                                    <svg
                                        class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                                        aria-hidden="true"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="24"
                                        height="24"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <use xlink:href="#icon-trash-solid" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    {{-- Row 2 --}}
                    <tr class="text-xs">
                        <td class="border px-2 py-1">2</td>
                        <td class="border px-2 py-1">RKH2904126</td>
                        <td class="border px-2 py-1">28/04/2025</td>
                        <td class="border px-2 py-1">
                            <span class="px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded">
                                Waiting
                            </span>
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <a href="#" class="text-white bg-blue-600 hover:bg-blue-700 px-2 py-0.5 rounded text-xs">DTH</a>
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <a href="#" class="text-white bg-green-600 hover:bg-green-700 px-2 py-0.5 rounded text-xs">LKH</a>
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <select
                                name="status"
                                class="text-xs border border-gray-300 rounded p-1"
                                data-old="On Progress"
                                onchange="confirmStatusChange(this)"
                            >
                                <option value="Done">Done</option>
                                <option value="On Progress" selected>On Progress</option>
                            </select>
                        </td>
                        <td class="border px-2 py-1">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-outline" />
                                    </svg>
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-solid" />
                                        <use xlink:href="#icon-edit-solid2" />
                                    </svg>
                                </button>
                                <button type="button" class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm">
                                    <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-trash-outline" />
                                    </svg>
                                    <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-trash-solid" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    {{-- Row 3 --}}
                    <tr class="text-xs">
                        <td class="border px-2 py-1">3</td>
                        <td class="border px-2 py-1">RKH2904127</td>
                        <td class="border px-2 py-1">27/04/2025</td>
                        <td class="border px-2 py-1">
                            <span class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded">
                                Decline
                            </span>
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <a href="#" class="text-white bg-blue-600 hover:bg-blue-700 px-2 py-0.5 rounded text-xs">DTH</a>
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <a href="#" class="text-white bg-green-600 hover:bg-green-700 px-2 py-0.5 rounded text-xs">LKH</a>
                        </td>
                        <td class="border px-2 py-1 text-center">
                            <select
                                name="status"
                                class="text-xs border border-gray-300 rounded p-1"
                                data-old="On Progress"
                                onchange="confirmStatusChange(this)"
                            >
                                <option value="Done">Done</option>
                                <option value="On Progress" selected>On Progress</option>
                            </select>
                        </td>
                        <td class="border px-2 py-1">
                            <div class="flex items-center justify-center space-x-2">
                                <button type="button" class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-outline" />
                                    </svg>
                                    <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-solid" />
                                        <use xlink:href="#icon-edit-solid2" />
                                    </svg>
                                </button>
                                <button type="button" class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm">
                                    <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-trash-outline" />
                                    </svg>
                                    <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <use xlink:href="#icon-trash-solid" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
    </table>
</div>

<!-- Modal backdrop -->
<div x-show="showLKHModal"
       x-transition.opacity
       class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <!-- Modal box -->
    <div x-show="showLKHModal"
         x-transition.scale
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2">
      <!-- Header -->
      <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-lg font-semibold">List Nomor LKH yang Sudah Diunggah</h2>
        <button @click="showLKHModal = false"
                class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
      </div>
      <!-- Body -->
      <div class="p-4">
        <table class="min-w-full table-auto text-sm">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-2 py-1 text-left">No LKH</th>
              <th class="px-2 py-1 text-left">Mandor</th>
              <th class="px-2 py-1 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="border px-2 py-1">LKH002345</td>
              <td class="border px-2 py-1">Rudi</td>
              <td class="border px-2 py-1 text-center">
                <a href="{{ route('input.kerjaharian.laporankerjaharian.index', ['no_rkh' => 'RKH2904125']) }}"
                   class="underline text-blue-600">Check</a>
              </td>
            </tr>
            <tr>
              <td class="border px-2 py-1">LKH002346</td>
              <td class="border px-2 py-1">Rudi</td>
              <td class="border px-2 py-1 text-center">
                <span class="underline text-blue-600 cursor-not-allowed">Check</span>
              </td>
            </tr>
            <tr>
              <td class="border px-2 py-1">LKH002347</td>
              <td class="border px-2 py-1">Rudi</td>
              <td class="border px-2 py-1 text-center">
                <span class="underline text-blue-600 cursor-not-allowed">Check</span>
              </td>
            </tr>
            <tr>
              <td class="border px-2 py-1">LKH002348</td>
              <td class="border px-2 py-1">Rudi</td>
              <td class="border px-2 py-1 text-center">
                <span class="underline text-blue-600 cursor-not-allowed">Check</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


        {{-- Pagination (mockup) --}}
        <nav class="mt-4">
            <ul class="inline-flex items-center -space-x-px">
                <li>
                    <a href="#" class="px-3 py-1 ml-0 leading-tight bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 text-xs">Previous</a>
                </li>
                <li>
                    <a href="#" class="px-3 py-1 leading-tight bg-white border border-gray-300 hover:bg-gray-100 text-xs">1</a>
                </li>
                <li>
                    <span class="px-3 py-1 leading-tight bg-blue-50 border border-blue-300 text-blue-600 text-xs">2</span>
                <li>
                    <a href="#" class="px-3 py-1 leading-tight bg-white border border-gray-300 hover:bg-gray-100 text-xs">3</a>
                </li>
                <li>
                    <a href="#" class="px-3 py-1 leading-tight bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 text-xs">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

    <script>
        function confirmStatusChange(select) {
            if (!confirm('Apakah anda yakin sudah ingin mengganti status? Pastikan LKH sudah terisi')) {
                select.value = select.getAttribute('data-old');
            } else {
                select.setAttribute('data-old', select.value);
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('select[name="status"]').forEach(function(select) {
                select.setAttribute('data-old', select.value);
            });
        });

  function sortTable(colIndex) {
    const table = document.getElementById('rkh-table');
    let rows = Array.from(table.tBodies[0].rows);
    let asc = table.getAttribute('data-sort-dir') === 'asc';
    rows.sort((a, b) => {
      let x = a.cells[colIndex].innerText.trim().toLowerCase();
      let y = b.cells[colIndex].innerText.trim().toLowerCase();
      // try numeric
      let nx = parseFloat(x.replace(/[^0-9.-]/g, ''));
      let ny = parseFloat(y.replace(/[^0-9.-]/g, ''));
      if (!isNaN(nx) && !isNaN(ny)) {
        return asc ? nx - ny : ny - nx;
      }
      // try date dd/mm/yyyy
      let dx = x.match(/(\d{2})\/(\d{2})\/(\d{4})/);
      if (dx) {
        [ , d1, m1, y1 ] = dx;
        x = new Date(`${y1}-${m1}-${d1}`);
        [ , d2, m2, y2 ] = y.match(/(\d{2})\/(\d{2})\/(\d{4})/);
        y = new Date(`${y2}-${m2}-${d2}`);
        return asc ? x - y : y - x;
      }
      // fallback string compare
      if (x < y) return asc ? -1 : 1;
      if (x > y) return asc ? 1 : -1;
      return 0;
    });
    // reattach
    rows.forEach(row => table.tBodies[0].appendChild(row));
    // toggle direction
    table.setAttribute('data-sort-dir', asc ? 'desc' : 'asc');
  }
    </script>
</x-layout>
