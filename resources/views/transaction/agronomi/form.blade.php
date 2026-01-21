<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    @php
        $isEdit = isset($header);
    @endphp

    @error('duplicate')
        <div class="mx-4 mb-4 flex items-center gap-2 p-3 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            <span class="font-medium">{{ $message }}</span>
        </div>
    @enderror

    <form action="{{ $url }}" method="POST">
        @csrf
        @method($method)

        <!-- Header Section -->
        <div class="mx-4 mb-5">
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-5 py-3">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Informasi Header
                    </h2>
                    <p class="text-blue-100 text-xs mt-0.5">* Beberapa field akan terisi otomatis setelah memilih plot
                    </p>
                </div>

                <!-- Card Body -->
                <div class="p-5 space-y-4">
                    <!-- Row 1 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Nomor Sample <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nosample"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                autocomplete="off" maxlength="4" value="{{ old('nosample', $header->nosample ?? '') }}"
                                placeholder="Masukkan nomor sample" required>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Plot <span class="text-red-500">*</span>
                            </label>
                            <input id="plot" type="text" name="plot" maxlength="10"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase placeholder:capitalize"
                                autocomplete="off" value="{{ old('plot', $header->plot ?? '') }}"
                                placeholder="Masukkan plot" required>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Blok <span class="text-xs text-gray-500 italic">(Otomatis)</span>
                            </label>
                            <input id="blok" type="text" name="blok" maxlength="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 text-gray-600 cursor-not-allowed"
                                autocomplete="off" value="{{ old('blok', $header->blok ?? '') }}" placeholder="-"
                                readonly>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Company <span class="text-xs text-gray-500 italic">(Otomatis)</span>
                            </label>
                            <input id="companycode" type="text" name="companycode" maxlength="6"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 text-gray-600"
                                autocomplete="off"
                                value="{{ old('companycode', $header->companycode ?? session('companycode')) }}"
                                readonly>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Varietas <span class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="text" name="varietas" id="varietas"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                autocomplete="off" maxlength="10"
                                value="{{ old('varietas', $header->varietas ?? '') }}" placeholder="Masukkan Varietas"
                                required>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Kategori <span class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="text" name="kat" id="kat"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                autocomplete="off" maxlength="3" value="{{ old('kat', $header->kat ?? '') }}"
                                placeholder="Masukkan Kategori" required>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Tanggal Tanam <span class="text-xs text-gray-500 italic">(Otomatis)</span> <span
                                    class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggaltanam" id="tanggaltanam"
                                value="{{ old('tanggaltanam', $header->tanggaltanam ?? '') }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400 focus:text-black valid:text-black"
                                required>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1.5 block">
                                Tanggal Pengamatan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggalpengamatan"
                                value="{{ old('tanggalpengamatan', $header->tanggalpengamatan ?? now()->toDateString()) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-400 focus:text-black valid:text-black"
                                required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Section -->
        <div class="mx-4 mb-5">
            <div class="bg-white rounded-lg shadow border border-gray-200">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-green-600 to-green-700 px-5 py-3">
                    <h2 class="text-base font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Detail Pengamatan (ni)
                    </h2>
                </div>

                <!-- Card Body -->
                <div class="p-5">
                    <!-- Table Container with Scroll -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full bg-white" id="listTable">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        ni</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        Jumlah Batang</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        Panjang GAP</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        pH Tanah</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        Ktk Gulma</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        T. Primer</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        T. Sekunder</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        T. Tersier</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        T. Kuarter</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        D. Primer</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        D. Sekunder</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        D. Tersier</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200">
                                        D. Kuarter</th>
                                    <th
                                        class="px-3 py-2.5 text-xs font-semibold text-gray-700 border-b border-gray-200 text-center">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php
                                    $lists = old('lists', $isEdit ? $header->lists : [[]]);
                                @endphp
                                @foreach ($lists as $index => $list)
                                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50">
                                        <td class="px-3 py-2">
                                            <input type="text" name="lists[{{ $index }}][nourut]"
                                                value="{{ $list->nourut ?? $index + 1 }}"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded text-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-600 text-sm"
                                                readonly>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="lists[{{ $index }}][jumlahbatang]"
                                                min="0" value="{{ $list->jumlahbatang ?? 0 }}" maxlength="5"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="lists[{{ $index }}][pan_gap]"
                                                min="0" value="{{ $list->pan_gap ?? 0 }}" maxlength="5"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="0.1"
                                                name="lists[{{ $index }}][ph_tanah]" min="0"
                                                value="{{ $list->ph_tanah ?? 0 }}" max="999.9"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="lists[{{ $index }}][ktk_gulma]"
                                                min="0" value="{{ $list->ktk_gulma ?? 0 }}" maxlength="2"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="lists[{{ $index }}][t_primer]"
                                                min="0" value="{{ $list->t_primer ?? 0 }}" maxlength="3"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="lists[{{ $index }}][t_sekunder]"
                                                min="0" value="{{ $list->t_sekunder ?? 0 }}" maxlength="3"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="lists[{{ $index }}][t_tersier]"
                                                min="0" value="{{ $list->t_tersier ?? 0 }}" maxlength="3"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" name="lists[{{ $index }}][t_kuarter]"
                                                min="0" value="{{ $list->t_kuarter ?? 0 }}" maxlength="3"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="any"
                                                name="lists[{{ $index }}][d_primer]" min="0"
                                                value="{{ $list->d_primer ?? 0 }}" max="999.9"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="any"
                                                name="lists[{{ $index }}][d_sekunder]" min="0"
                                                value="{{ $list->d_sekunder ?? 0 }}" max="999.99"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="any"
                                                name="lists[{{ $index }}][d_tersier]" min="0"
                                                value="{{ $list->d_tersier ?? 0 }}" max="999.999"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2">
                                            <input type="number" step="any"
                                                name="lists[{{ $index }}][d_kuarter]" min="0"
                                                value="{{ $list->d_kuarter ?? 0 }}" max="999.9999"
                                                class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm"
                                                autocomplete="off" required>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button type="button"
                                                class="inline-flex items-center gap-1 px-2 py-1.5 text-red-600 hover:bg-red-50 border border-red-200 hover:border-red-300 rounded transition-colors remove-row"
                                                title="Hapus baris">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="text-xs font-medium">Hapus</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Add Row Button -->
                    <div class="flex justify-center mt-5">
                        <button type="button" id="addRow"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-medium">Tambah Baris</span>
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 mt-6 pt-5 border-t border-gray-200">
                        <button type="submit"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="font-medium">{{ $buttonSubmit }}</span>
                        </button>
                        <a href="{{ route('transaction.agronomi.index') }}"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="font-medium">Batal</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Scroll to Top Button -->
    <button onclick="scrollToTop()" id="scrollToTop"
        class="fixed bottom-6 right-6 p-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all z-50 hidden">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <style>
        html {
            scroll-behavior: smooth;
        }

        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            opacity: 1;
        }

        table {
            white-space: nowrap;
        }

        #scrollToTop.show {
            display: block !important;
        }
    </style>

    <script>
        // Add Row Function
        document.getElementById('addRow').addEventListener('click', () => {
            const table = document.getElementById('listTable').querySelector('tbody');
            const rowCount = table.rows.length + 1;
            const newRow = `
            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50">
                <td class="px-3 py-2">
                    <input type="text" name="lists[${rowCount}][nourut]" value="${rowCount}"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded text-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-600 text-sm" readonly>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][jumlahbatang]" min="0" maxlength="5" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][pan_gap]" min="0" maxlength="5" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.1" name="lists[${rowCount}][ph_tanah]" min="0" max="999.9" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][ktk_gulma]" min="0" maxlength="2" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][t_primer]" min="0" maxlength="3" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][t_sekunder]" min="0" maxlength="3" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][t_tersier]" min="0" maxlength="3" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="lists[${rowCount}][t_kuarter]" min="0" maxlength="3" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.1" name="lists[${rowCount}][d_primer]" min="0" max="999.9" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.1" name="lists[${rowCount}][d_sekunder]" min="0" max="999.99" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.1" name="lists[${rowCount}][d_tersier]" min="0" max="999.999" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.1" name="lists[${rowCount}][d_kuarter]" min="0" max="999.9999" value="0"
                        class="w-full px-2 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 auto-clear-zero text-sm" autocomplete="off" required>
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" 
                        class="inline-flex items-center gap-1 px-2 py-1.5 text-red-600 hover:bg-red-50 border border-red-200 hover:border-red-300 rounded transition-colors remove-row"
                        title="Hapus baris">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span class="text-xs font-medium">Hapus</span>
                    </button>
                </td>
            </tr>
        `;
            table.insertAdjacentHTML('beforeend', newRow);
        });

        // Remove Row Function
        document.getElementById('listTable').addEventListener('click', (e) => {
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
                resetRowNumbers();
            }
        });

        // Auto Clear Zero on Focus
        document.addEventListener('focusin', function(e) {
            if (e.target.matches('.auto-clear-zero') && e.target.value === '0') {
                e.target.value = '';
            }
        });

        document.addEventListener('focusout', function(e) {
            if (e.target.matches('.auto-clear-zero') && e.target.value.trim() === '') {
                e.target.value = '0';
            }
        });

        // Reset Row Numbers
        function resetRowNumbers() {
            const rows = document.querySelectorAll('#listTable tbody tr');
            rows.forEach((row, index) => {
                const noUrutInput = row.querySelector('input[name^="lists"][name$="[nourut]"]');
                noUrutInput.value = index + 1;
            });
        }

        // Scroll to Top Function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Show/Hide Scroll to Top Button
        window.addEventListener('scroll', function() {
            const scrollBtn = document.getElementById('scrollToTop');
            if (window.pageYOffset > 300) {
                scrollBtn.classList.remove('hidden');
                scrollBtn.classList.add('show');
            } else {
                scrollBtn.classList.add('hidden');
                scrollBtn.classList.remove('show');
            }
        });

        // Plot to Uppercase
        document.getElementById('plot').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>

    <script>
        $(document).ready(function() {
            // === SCRIPT: GET BLOK & GET VARIETAS ===
            $('#plot').change(function() {
                const plot = $(this).val();

                if (plot) {
                    // === AJAX 1: GET BLOK ===
                    $.ajax({
                        url: "{{ route('transaction.agronomi.getBlok') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            plot: plot
                        },
                        success: function(response) {
                            $('#blok').val(response.blok);
                        },
                        error: function() {
                            alert('Data Mapping tidak ditemukan');
                            $('#blok').val('');
                        }
                    });

                    // === AJAX 2: GET VARIETAS, KAT, TANGGAL TANAM ===
                    $.ajax({
                        url: "{{ route('transaction.agronomi.getVar') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            plot: plot
                        },
                        success: function(response) {
                            $('#varietas').val(response.varietas);
                            $('#kat').val(response.kat);
                            $('#tanggaltanam').val(response.tanggaltanam);
                        },
                        error: function() {
                            alert('Varietas, Kategori, dan Tanggal Tanam tidak ditemukan');
                            $('#varietas').val('');
                            $('#kat').val('');
                            $('#tanggaltanam').val('');
                        }
                    });

                } else {
                    // Jika plot kosong, kosongkan semua field terkait
                    $('#blok, #varietas, #kat, #tanggaltanam').val('');
                }
            });
        });
    </script>

</x-layout>
