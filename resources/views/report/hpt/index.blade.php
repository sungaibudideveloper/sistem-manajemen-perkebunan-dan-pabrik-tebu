<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        @if (!$startDate && !$endDate)
            <div class="px-4 py-2 text-gray-500 font-medium text-sm">*(silahkan pilih range tanggal pengamatan untuk
                menampilkan data)</div>
        @endif
        <div class="flex lg:justify-between mx-4 items-center gap-2 justify-center flex-wrap">
            @if ($startDate && $endDate)
                <div class="flex gap-2 text-sm">
                    @if(hasPermission('Excel HPT'))
                        <button
                            class="bg-green-600 text-white px-4 py-2 border border-transparent shadow-sm rounded-md font-medium hover:bg-green-500 flex items-center space-x-2"
                            onclick="window.location.href='{{ route('report.hpt.exportExcel', ['company' => old('company', request()->company), 'start_date' => old('start_date', request()->start_date), 'end_date' => old('end_date', request()->end_date)]) }}'">
                            <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export to Excel</span>
                        </button>
                    @endif

                    @if(hasPermission('Pivot HPT'))
                        <a class="bg-blue-700 text-white px-4 py-2 border border-transparent shadow-sm rounded-md font-medium hover:bg-blue-800 flex items-center space-x-2"
                            href="{{ route('pivotTableHPT', ['start_date' => old('start_date', request()->start_date), 'end_date' => old('end_date', request()->end_date)]) }}">
                            <svg class="w-5 h-5 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm-1 9a1 1 0 1 0-2 0v2a1 1 0 1 0 2 0v-2Zm2-5a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1Zm4 4a1 1 0 1 0-2 0v3a1 1 0 1 0 2 0v-3Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Export to Pivot</span>
                        </a>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('report.hpt.index') }}">
                @csrf
                <div class="flex items-center gap-2 flex-wrap justify-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700 font-medium">Range tanggal pengamatan : </span>
                        <div class="relative inline-block w-fit">
                            <button type="button"
                                class="inline-flex justify-center w-fit items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                id="menu-button" aria-expanded="false" aria-haspopup="true" onclick="toggleDropdown()">
                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                    class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Date Filter</span>
                                <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div class="absolute left-0 z-10 mt-[1px] w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden"
                                id="menu-dropdown">
                                <div class="py-1 px-4" role="menu" aria-orientation="vertical"
                                    aria-labelledby="menu-button">
                                    <div class="py-2">
                                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start
                                            Date</label>
                                        <input type="date" id="start_date" name="start_date"
                                            value="{{ old('start_date', $startDate ?? '') }}"
                                            class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400"
                                            oninput="this.className = this.value ? 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                                    </div>

                                    <div class="py-2">
                                        <label for="end_date" class="block text-sm font-medium text-gray-700">End
                                            Date</label>
                                        <input type="date" id="end_date" name="end_date"
                                            value="{{ old('end_date', $endDate ?? '') }}"
                                            class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400"
                                            oninput="this.className = this.value ? 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-black' : 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-400'">
                                    </div>

                                    <div class="py-2">
                                        <button type="submit" name="filter"
                                            class="w-full py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Apply
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="perPage" class="text-sm font-medium text-gray-700">Items per page:</label>
                        <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                            onchange="this.form.submit()"
                            class="w-10 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                </div>
            </form>
        </div>

        @if ($startDate && $endDate)
            <div class="mx-auto px-4 py-4">
                <div class="overflow-x-auto rounded-md border border-gray-300">
                    <table class="min-w-full bg-white text-sm text-center" id="tables">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kebun
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.
                                    Sample</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Blok
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Plot
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Luas
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal
                                    Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Umur
                                    Tanam</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Varietas
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal
                                    Pengamatan</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Bulan
                                    Pengamatan</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">ni</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Jumlah
                                    Batang</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">PPT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">PPT Aktif</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">PBT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">PBT Aktif</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Skor 0
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Skor 1
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Skor 2
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Skor 3
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Skor 4
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">%PPT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">%PPT Aktif
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">%PBT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">%PBT Aktif
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Σni*vi
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Intensitas Kerusakan</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Telur
                                    PPT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PPT 1</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PPT 2</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PPT 3</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PPT 4</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Pupa PPT
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Ngengat
                                    PPT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kosong
                                    PPT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Telur
                                    PBT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PBT 1</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PBT 2</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PBT 3</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Larva
                                    PBT 4</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Pupa PBT
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Ngengat
                                    PBT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kosong
                                    PBT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">DH</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">DT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">KBP</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">KBB</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">KP</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Cabuk
                                </th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Belalang</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">BTG Terserang
                                    Ul.grayak</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">
                                    Jumlah Ul.Grayak</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">BTG
                                    Terserang SMUT</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">SMUT
                                    Stadia 1</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">SMUT
                                    Stadia 2</th>
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">SMUT
                                    Stadia 3</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($hpt as $item)
                                <tr>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->no }}.</td>
                                    <td class="py-2 px-4 border-b border-gray-300">
                                        {{ $item->compName ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->nosample }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->blokName ?? '' }}
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->plotName ?? '' }}
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">
                                        {{ $item->luasarea ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->tanggaltanam ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ ceil($item->umur_tanam) }} Bulan
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->varietas ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->tanggalpengamatan ?? '' }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->bulanPengamatan }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->nourut }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->jumlahbatang }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->ppt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->ppt_aktif }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->pbt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->pbt_aktif }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->skor0 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->skor1 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->skor2 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->skor3 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->skor4 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->per_ppt * 100 }}%</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->per_ppt_aktif * 100 }}%
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->per_pbt * 100 }}%</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->per_pbt_aktif * 100 }}%
                                    </td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->sum_ni }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->int_rusak * 100 }}%</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->telur_ppt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_ppt1 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_ppt2 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_ppt3 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_ppt4 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->pupa_ppt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->ngengat_ppt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->kosong_ppt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->telur_pbt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_pbt1 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_pbt2 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_pbt3 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->larva_pbt4 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->pupa_pbt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->ngengat_pbt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->kosong_pbt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->dh }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->dt }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->kbp }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->kbb }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->kp }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->cabuk }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->belalang }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->serang_grayak }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->jum_grayak }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->serang_smut }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->smut_stadia1 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->smut_stadia2 }}</td>
                                    <td class="py-2 px-4 border-b border-gray-300">{{ $item->smut_stadia3 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="mx-4 mt-1" id="pagination-links">
                @if ($hpt->hasPages())
                    {{ $hpt->appends(['perPage' => $hpt->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing <span class="font-medium">{{ $hpt->count() }}</span> of <span
                                class="font-medium">{{ $hpt->total() }}</span> results
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputElement = document.getElementById("perPage");

            inputElement.addEventListener("input", (event) => {
                event.target.value = event.target.value.replace(/[^0-9]/g, '');
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const dropdownButton = document.getElementById("dropdownButton");
            const dropdownContent = document.getElementById("dropdownContent");
            const selectAll = document.getElementById("selectAll");
            const checkboxes = document.querySelectorAll(".company-checkbox");

            dropdownButton.addEventListener("click", () => {
                dropdownContent.classList.toggle("hidden");
            });

            selectAll.addEventListener("change", (e) => {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = e.target.checked;
                });
            });

            document.addEventListener("click", (e) => {
                if (!dropdownButton.contains(e.target) && !dropdownContent.contains(e.target)) {
                    dropdownContent.classList.add("hidden");
                }
            });

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener("change", () => {
                    if ([...checkboxes].every((cb) => cb.checked)) {
                        selectAll.checked = true;
                    } else {
                        selectAll.checked = false;
                    }
                });
            });
        });
    </script>
    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('menu-dropdown');
            dropdown.classList.toggle('hidden');
        }

        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("menu-dropdown");
            const button = document.getElementById("menu-button");

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add("hidden");
            }
        });
    </script>

    <style>
        th,
        td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .max-h-96 {
            max-height: 24rem;
            overflow-x: auto;
            overflow-y: hidden;
        }
    </style>

</x-layout>
