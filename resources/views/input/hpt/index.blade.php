<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        <div class="flex lg:justify-between items-end mx-4 gap-2 justify-center flex-wrap">

            @if (auth()->user() && in_array('Create HPT', json_decode(auth()->user()->permissions ?? '[]')))
                <a href="{{ route('input.hpt.create') }}"
                    class="bg-blue-500 text-white px-4 py-2 text-sm border border-transparent shadow-sm font-medium rounded-md hover:bg-blue-600 flex items-center gap-2">
                    <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14m-7 7V5" />
                    </svg>
                    <span>New Data</span>
                </a>
            @endif
            <div class="flex justify-center items-end gap-2 flex-wrap">
                <form method="POST" action="{{ route('input.hpt.handle') }}">
                    @csrf
                    <div class="flex gap-2 items-end">
                        <div>
                            <label for="perPage" class="text-sm font-medium text-gray-700">Items per page:</label>
                            <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                min="1" onchange="this.form.submit()"
                                class="w-10 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div>
                            <div class="relative inline-block text-left w-full max-w-xs">
                                <div>
                                    <button type="button"
                                        class="inline-flex justify-center w-full items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        id="menu-button" aria-expanded="false" aria-haspopup="true"
                                        onclick="toggleDropdown()">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                            class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Date Filter</span>
                                        <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="absolute z-10 mt-1 w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden"
                                    id="menu-dropdown">
                                    <div class="py-1 px-4" role="menu" aria-orientation="vertical"
                                        aria-labelledby="menu-button">
                                        <div class="py-2">
                                            <label for="start_date"
                                                class="block text-sm font-medium text-gray-700">Start Date</label>
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
                    </div>
                </form>

                @if (auth()->user() && in_array('Excel HPT', json_decode(auth()->user()->permissions ?? '[]')))
                    <button
                        class="bg-green-500 text-white px-4 py-2 rounded-md text-sm border border-transparent shadow-sm font-medium hover:bg-green-600 flex items-center space-x-2"
                        onclick="window.location.href='{{ route('input.hpt.exportExcel', ['start_date' => old('start_date', request()->start_date), 'end_date' => old('end_date', request()->end_date)]) }}'">
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
            </div>
        </div>

        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border-gray-300 border">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-1">No.
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Company
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No.
                                Sample
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Plot
                                Sample</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Varietas
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal
                                Tanam</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Tanggal
                                Pengamatan</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Status
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-40">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hpt as $item)
                            <tr>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-1">
                                    {{ $item->no }}.</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->company->nama }}
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->no_sample }}</td>
                                </td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->plotcodesample }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->varietas }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->tanggaltanam }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->tglamat }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-40">
                                    <span
                                        class="{{ $item->status === 'Posted' ? 'bg-green-600' : 'bg-red-600' }} px-2 py-1 rounded-md text-white font-medium shadow-md">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td
                                    class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} flex justify-center">
                                    <div class="flex items-center">
                                        <button class="group flex items-center"
                                            onclick="showList('{{ $item->no_sample }}', '{{ $item->companycode }}', '{{ $item->tanggaltanam }}')"><svg
                                                class="w-6 h-6 text-gray-500 dark:text-white group-hover:hidden"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-width="2"
                                                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                                <path stroke="currentColor" stroke-width="2"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            <svg class="w-6 h-6 text-gray-500 dark:text-white hidden group-hover:block"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span class="w-2"></span>
                                        </button>
                                        @if (auth()->user() && in_array('Edit HPT', json_decode(auth()->user()->permissions ?? '[]')))
                                            @if ($item->status === 'Unposted')
                                                <a href="{{ route('input.hpt.edit', ['no_sample' => $item->no_sample, 'companycode' => $item->companycode, 'tanggaltanam' => $item->tanggaltanam]) }}"
                                                    class="group flex items-center"><svg
                                                        class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <path stroke="currentColor" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2"
                                                            d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                                    </svg>
                                                    <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path fill-rule="evenodd"
                                                            d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                                                            clip-rule="evenodd" />
                                                        <path fill-rule="evenodd"
                                                            d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span class="w-0.5"></span>
                                                </a>
                                            @endif
                                        @endif
                                        @if (auth()->user() && in_array('Hapus HPT', json_decode(auth()->user()->permissions ?? '[]')))
                                            @if ($item->status === 'Unposted')
                                                <form
                                                    action="{{ route('input.hpt.destroy', ['no_sample' => $item->no_sample, 'companycode' => $item->companycode, 'tanggaltanam' => $item->tanggaltanam]) }}"
                                                    method="POST" class="inline">@csrf @method('DELETE')
                                                    <button type="submit" class="group flex"
                                                        onclick="return confirm('Yakin ingin menghapus data ini?')"><svg
                                                            class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                            width="24" height="24" fill="none"
                                                            viewBox="0 0 24 24">
                                                            <path stroke="currentColor" stroke-linecap="round"
                                                                stroke-linejoin="round" stroke-width="2"
                                                                d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                        </svg>
                                                        <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                            width="24" height="24" fill="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path fill-rule="evenodd"
                                                                d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mx-4 my-1">
            @if ($hpt->hasPages())
                {{ $hpt->appends(['perPage' => $hpt->perPage(), 'start_date' => $startDate, 'end_date' => $endDate])->links() }}
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $hpt->count() }}</span> of <span
                            class="font-medium">{{ $hpt->total() }}</span> results
                    </p>
                </div>
            @endif
        </div>
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
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0 transform scale-95"
        style="opacity: 0; transform: scale(0.95);">
        <div class="bg-white w-11/12 p-4 rounded shadow-lg transition-transform duration-300 ease-out transform">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">Daftar List</h2>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-200 rounded-md">
                    <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18 17.94 6M18 18 6.06 6" />
                    </svg>
                </button>
            </div>

            <div class="overflow-auto text-sm border border-gray-300 rounded">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">No.</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">No. Sample</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Kebun</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">block</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Plot</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Luas</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Tanggal Tanam</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Umur Tanam</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Varietas</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Tanggal Pengamatan</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Bulan Pengamatan</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">ni</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Jumlah Batang</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">PPT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">PPT Aktif</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">PBT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">PBT Aktif</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Skor 0</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Skor 1</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Skor 2</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Skor 3</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Skor 4</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">%PPT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">%PPT Aktif</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">%PBT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">%PBT Aktif</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Σni*vi</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Intensitas Kerusakan</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Telur PPT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PPT 1</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PPT 2</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PPT 3</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PPT 4</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Pupa PPT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Ngengat PPT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Kosong PPT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Telur PBT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PBT 1</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PBT 2</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PBT 3</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Larva PBT 4</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Pupa PBT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Ngengat PBT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Kosong PBT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">DH</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">DT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">KBP</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">KBB</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">KP</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Cabuk</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Belalang</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">BTG Terserang Ul.Grayak</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Jumlah Ul.Grayak</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">BTG Terserang SMUT</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">SMUT Stadia 1</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">SMUT Stadia 2</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">SMUT Stadia 3</th>
                        </tr>
                    </thead>
                    <tbody id="listTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <style>
        .invisible {
            visibility: hidden;
            pointer-events: none;
        }

        .visible {
            visibility: visible;
            pointer-events: auto;
        }
    </style>

    <script>
        function showList(no_sample, companycode, tanggaltanam) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            tableBody.innerHTML = '';

            const url =
                `{{ route('input.hpt.show', ['no_sample' => '__no_sample__', 'companycode' => '__companycode__', 'tanggaltanam' => '__tanggaltanam__']) }}`
                .replace('__no_sample__', no_sample)
                .replace('__companycode__', companycode)
                .replace('__tanggaltanam__', tanggaltanam);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {

                        const tanggaltanam = new Date(item.tanggaltanam);
                        const now = new Date();

                        const diffInTime = now - tanggaltanam;
                        const diffInMonths = Math.ceil(diffInTime / (1000 * 3600 * 24 * 30));
                        const umurTanam = diffInMonths >= 0 ? `${diffInMonths} Bulan` : 'Tunggu Tanggal Tanam';

                        const dateInput = new Date(item.tglamat);
                        const month = dateInput.toLocaleString('en-US', {
                            month: 'long'
                        });

                        const row = `
                            <tr class="text-center">
                                <td class="py-2 px-4 border-b border-gray-300">${item.no}.</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.no_sample}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.compName}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.blockName}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.plotName}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.luasarea}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.tanggaltanam}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${umurTanam}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.varietas}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.tglamat}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${month}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.nourut}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.jm_batang}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.ppt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.ppt_aktif}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.pbt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.pbt_aktif}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.skor0}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.skor1}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.skor2}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.skor3}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.skor4}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${(item.per_ppt*100).toFixed(2)}%</td>
                                <td class="py-2 px-4 border-b border-gray-300">${(item.per_ppt_aktif*100).toFixed(2)}%</td>
                                <td class="py-2 px-4 border-b border-gray-300">${(item.per_pbt*100).toFixed(2)}%</td>
                                <td class="py-2 px-4 border-b border-gray-300">${(item.per_pbt_aktif*100).toFixed(2)}%</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.sum_ni}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.int_rusak}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.telur_ppt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_ppt1}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_ppt2}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_ppt3}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_ppt4}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.pupa_ppt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.ngengat_ppt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.kosong_ppt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.telur_pbt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_pbt1}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_pbt2}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_pbt3}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.larva_pbt4}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.pupa_pbt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.ngengat_pbt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.kosong_pbt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.dh}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.dt}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.kbp}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.kbb}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.kp}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.cabuk}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.belalang}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.serang_grayak}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.jum_grayak}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.serang_smut}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.smut_stadia1}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.smut_stadia2}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.smut_stadia3}</td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });

                    modal.classList.remove('invisible');
                    modal.classList.add('visible');
                    setTimeout(() => {
                        modal.style.opacity = "1";
                        modal.style.transform = "scale(1)";
                    }, 50);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    alert('Gagal memuat data list.');
                });
        }

        function closeModal() {
            const modal = document.getElementById('listModal');

            modal.style.opacity = "0";
            modal.style.transform = "scale(0.95)";

            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
        }
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
