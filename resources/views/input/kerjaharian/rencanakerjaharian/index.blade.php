<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="{
  showLKHModal: false,
  showAbsenModal: false,
  showGenerateDTHModal: false,
dthDate: '{{ date("Y-m-d") }}',
  absenDate: '{{ date("Y-m-d") }}',
  absenList: [
    { id: 1, nama: 'Budi',    gender: 'L' },
    { id: 2, nama: 'Siti',    gender: 'P' },
    { id: 3, nama: 'Agus',    gender: 'L' },
    { id: 4, nama: 'Dewi',    gender: 'P' },
    { id: 5, nama: 'Ahmad',   gender: 'L' },
    { id: 6, nama: 'Rina',    gender: 'P' },
    { id: 7, nama: 'Joko',    gender: 'L' },
    { id: 8, nama: 'Maya',    gender: 'P' },
    { id: 9, nama: 'Tono',    gender: 'L' },
    { id: 10, nama: 'Indah',  gender: 'P' },
    { id: 11, nama: 'Dwi',    gender: 'L' },
    { id: 12, nama: 'Lina',   gender: 'P' },
    { id: 13, nama: 'Bambang',gender: 'L' },
    { id: 14, nama: 'Putri',  gender: 'P' },
    { id: 15, nama: 'Yudi',   gender: 'L' },
    { id: 16, nama: 'Santi',  gender: 'P' },
    { id: 17, nama: 'Eko',    gender: 'L' },
    { id: 18, nama: 'Fitri',  gender: 'P' },
    { id: 19, nama: 'Hasan',  gender: 'L' },
    { id: 20, nama: 'Wulan',  gender: 'P' },
    { id: 21, nama: 'Wahyu',  gender: 'L' },
    { id: 22, nama: 'Tika',   gender: 'P' },
    { id: 23, nama: 'Arif',   gender: 'L' },
    { id: 24, nama: 'Dina',   gender: 'P' },
    { id: 25, nama: 'Rani',   gender: 'P' }
  ]
}" class="relative">
        <div class="mx-auto bg-white rounded-md shadow-md p-6">
            {{-- Search & Filters --}}
            <div class="flex flex-col md:flex-row justify-between mb-4">
                <div class="flex justify-between items-center w-full">
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

            <div class="flex items-center justify-between mb-4">
  <!-- LEFT: 4 filter controls -->
  <div class="flex items-center space-x-2">
    <!-- All Approval -->
    <select name="filter_approval"
            class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
      <option value="">All Approval</option>
      <option>Approved</option>
      <option>Waiting</option>
      <option>Decline</option>
    </select>

    <!-- All Status -->
    <select name="filter_status"
            class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
      <option value="">All Status</option>
      <option>Done</option>
      <option>On Progress</option>
    </select>

    <!-- Tanggal -->
    <input type="date" id="filter_date" name="filter_date"
           class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
           value="{{ request('filter_date', date('Y-m-d')) }}" />

    <!-- Show All Date -->
    <label class="flex items-center text-xs space-x-1">
      <input type="checkbox" id="all_date_toggle" name="all_date" value="1"
             onchange="document.getElementById('filter_date').disabled = this.checked;"
             {{ request('all_date') ? 'checked' : '' }} />
      <span>Show All Date</span>
    </label>
  </div>

  <!-- RIGHT: 2 action buttons -->
  <div class="flex items-center space-x-2">
  <button
    type="button"
    @click="showAbsenModal = true"
    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded"
  >
    Check Data Absen
  </button>
  <button
  type="button"
  @click="showGenerateDTHModal = true"
  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded"
>
  Generate DTH
</button>
  </div>
</div>

            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table id="rkh-table" class="min-w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs">
                            <th class="border px-2 py-1">
                                <button type="button" onclick="sortTable(0)" class="flex items-center space-x-1">
                                    <span>No.</span>
                                    <!-- sorting icons -->
                                </button>
                            </th>
                            <th class="border px-2 py-1">
                                <button type="button" onclick="sortTable(1)" class="flex items-center space-x-1">
                                    <span>No RKH</span>
                                    <!-- sorting icons -->
                                </button>
                            </th>
                            <th class="border px-2 py-1">
                                <button type="button" onclick="sortTable(2)" class="flex items-center space-x-1">
                                    <span>Tanggal</span>
                                    <!-- sorting icons -->
                                </button>
                            </th>
                            <th class="border px-2 py-1 text-center">Mandor</th>
                            <th class="border px-2 py-1 text-center">Kegiatan</th>
                            <th class="border px-2 py-1">
                                <button type="button" onclick="sortTable(5)" class="flex items-center space-x-1">
                                    <span>Approval</span>
                                    <!-- sorting icons -->
                                </button>
                            </th>
                            <th class="border px-2 py-1 text-center">
                                <button type="button" onclick="sortTable(6)" class="flex items-center justify-center space-x-1">
                                    <span>Laporan Kegiatan Harian</span>
                                    <!-- sorting icons -->
                                </button>
                            </th>
                            <th class="border px-2 py-1 text-center">
                                <button type="button" onclick="sortTable(7)" class="flex items-center justify-center space-x-1">
                                    <span>Status</span>
                                    <!-- sorting icons -->
                                </button>
                            </th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-xs">
                            <td class="border px-2 py-1">1</td>
                            <td class="border px-2 py-1">RKH2904125</td>
                            <td class="border px-2 py-1">06/05/2025</td>
                            <td class="border px-2 py-1">Gerald</td>
                            <td class="border px-2 py-1">1/1</td>
                            <td class="border px-2 py-1">
                                <span class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded">Approved</span>
                            </td>
                            <td class="border px-2 py-1 text-center">
                                <a
                                    href="#"
                                    @click.prevent="showLKHModal = true"
                                    class="text-white bg-green-600 hover:bg-green-700 px-2 py-0.5 rounded text-xs"
                                >
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
                        <tr class="text-xs">
                            <td class="border px-2 py-1">2</td>
                            <td class="border px-2 py-1">RKH2904126</td>
                            <td class="border px-2 py-1">06/05/2025</td>
                            <td class="border px-2 py-1">Nathan</td>
                            <td class="border px-2 py-1">0/2</td>
                            <td class="border px-2 py-1">
                                <span class="px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded">Waiting</span>
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
                        <tr class="text-xs">
                            <td class="border px-2 py-1">3</td>
                            <td class="border px-2 py-1">RKH2904127</td>
                            <td class="border px-2 py-1">06/05/2025</td>
                            <td class="border px-2 py-1">Angky</td>
                            <td class="border px-2 py-1">2/3</td>
                            <td class="border px-2 py-1">
                                <span class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded">Decline</span>
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
                        </tr>
                        {{-- row 4 --}}
                        <tr class="text-xs">
                            <td class="border px-2 py-1">4</td>
                            <td class="border px-2 py-1 bg-red-300">RKH2304125</td>
                            <td class="border px-2 py-1">23/04/2025</td>
                            <td class="border px-2 py-1">Udin</td>
                            <td class="border px-2 py-1">1/3</td>
                            <td class="border px-2 py-1">
                            <span class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded">Approved</span>
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
                    </tbody>
                </table>
                <div class="mt-2 text-sm text-red-600 font-medium">
    RKH Merah = Melewati Estimasi Waktu
</div>
            </div>
            
            <!-- Absen Modal -->
    <div
      x-show="showAbsenModal"
      x-transition.opacity
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div
        x-show="showAbsenModal"
        x-transition.scale
        class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col"
      >
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b flex-shrink-0">
          <h2 class="text-lg font-semibold">Data Absen Tenaga Kerja</h2>
          <div class="flex items-center space-x-2">
            <label for="absen_date" class="text-sm">Tanggal:</label>
            <input
              type="date"
              id="absen_date"
              x-model="absenDate"
              class="text-sm border border-gray-300 rounded p-2"
            />
          </div>
          <button
            @click="showAbsenModal = false"
            class="text-gray-600 hover:text-gray-800 text-2xl leading-none flex-shrink-0"
          >&times;</button>
        </div>

        <!-- Body -->
        <div class="p-4 overflow-hidden flex-grow">
          <div class="overflow-x-auto">
            <div class="max-h-[400px] overflow-y-auto">
              <table class="min-w-full table-auto text-sm">
                <thead>
                  <tr class="bg-gray-100">
                    <th class="px-2 py-1 text-left">ID</th>
                    <th class="px-2 py-1 text-left">Nama</th>
                    <th class="px-2 py-1 text-center">Gender</th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="person in absenList" :key="person.id">
                    <tr>
                      <td class="border px-2 py-1" x-text="person.id"></td>
                      <td class="border px-2 py-1" x-text="person.nama"></td>
                      <td class="border px-2 py-1 text-center" x-text="person.gender"></td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center p-4 border-t flex-shrink-0">
          <div class="text-sm space-x-4">
            <span>Total Laki-laki: <span x-text="absenList.filter(p => p.gender==='L').length"></span></span>
            <span>Total Perempuan: <span x-text="absenList.filter(p => p.gender==='P').length"></span></span>
            <span>Total: <span x-text="absenList.length"></span></span>
          </div>
          <button
            @click="showAbsenModal = false"
            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm rounded"
          >Close</button>
        </div>
      </div>
    </div>
</div>

            <!-- Generate DTH Modal -->
<div
  x-show="showGenerateDTHModal"
  x-transition.opacity
  class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
>
  <div
    x-show="showGenerateDTHModal"
    x-transition.scale
    class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/3"
  >
    <!-- Header -->
    <div class="flex justify-between items-center p-4 border-b">
      <h2 class="text-lg font-semibold">Generate DTH</h2>
      <button
        @click="showGenerateDTHModal = false"
        class="text-gray-600 hover:text-gray-800 text-2xl leading-none"
      >&times;</button>
    </div>

    <!-- Body -->
    <div class="p-4 space-y-4">
      <label for="dth_date" class="block text-sm font-medium text-gray-700">Pilih Tanggal:</label>
      <input
        type="date"
        id="dth_date"
        x-model="dthDate"
        class="w-full border border-gray-300 rounded p-2 text-sm"
      />
    </div>

    <!-- Footer -->
    <div class="flex justify-end space-x-2 p-4 border-t">
      <button
        @click="showGenerateDTHModal = false"
        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded"
      >Cancel</button>
      <button
        @click="generateDTH()"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded"
      >Generate</button>
    </div>
  </div>
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
                        <button @click="showLKHModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
                    </div>
                    <!-- Body -->
                    <div class="p-4">
                        <table class="min-w-full table-auto text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-2 py-1 text-left">No LKH</th>
                                    <th class="px-2 py-1 text-left">Kegiatan</th>
                                    <th class="px-2 py-1 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border px-2 py-1">LKH002345</td>
                                    <td class="border px-2 py-1">W105 - Weeding</td>
                                    <td class="border px-2 py-1 text-center">
                                        <a href="{{ route('input.kerjaharian.laporankerjaharian.index', ['no_rkh' => 'RKH2904125']) }}" class="underline text-blue-600">Check</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border px-2 py-1">LKH002346</td>
                                    <td class="border px-2 py-1">M102 - Sanitasi</td>
                                    <td class="border px-2 py-1 text-center">
                                        <span class="underline text-blue-600 cursor-not-allowed">Check</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="border px-2 py-1">LKH002347</td>
                                    <td class="border px-2 py-1">D45 - Drainase</td>
                                    <td class="border px-2 py-1 text-center">
                                        <span class="underline text-blue-600 cursor-not-allowed">Check</span>
                                    </td>
                                </tr>
                                <!-- other rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                let nx = parseFloat(x.replace(/[^0-9.-]/g, ''));
                let ny = parseFloat(y.replace(/[^0-9.-]/g, ''));
                if (!isNaN(nx) && !isNaN(ny)) return asc ? nx - ny : ny - nx;
                let dx = x.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                if (dx) {
                    x = new Date(`${dx[3]}-${dx[2]}-${dx[1]}`);
                    let dy = y.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                    y = new Date(`${dy[3]}-${dy[2]}-${dy[1]}`);
                    return asc ? x - y : y - x;
                }
                if (x < y) return asc ? -1 : 1;
                if (x > y) return asc ? 1 : -1;
                return 0;
            });
            rows.forEach(row => table.tBodies[0].appendChild(row));
            table.setAttribute('data-sort-dir', asc ? 'desc' : 'asc');
        }

        function generateDTH() {
    const tanggal = document.querySelector('#dth_date').value;
    if (!tanggal) {
        alert('Silakan pilih tanggal terlebih dahulu.');
        return;
    }

    const baseUrl = "{{ route('input.kerjaharian.distribusitenagaharian.index') }}";
    window.location.href = `${baseUrl}?date=${tanggal}`;
}
    </script>
</x-layout>
