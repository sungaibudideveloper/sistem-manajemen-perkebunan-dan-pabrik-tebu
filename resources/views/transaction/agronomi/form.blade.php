<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>
    @include('errorfile')
    @php
        $isEdit = isset($header);
    @endphp

    @error('duplicate')
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400 w-fit">
            {{ $message }}</div>
    @enderror

    <form action="{{ $url }}" method="POST">
        @csrf
        @method($method)
        <div class="mx-4 p-6 bg-white rounded-md shadow-md">

            {{-- <div class="text-center text-xl pb-2 mb-6 -mt-2 border-b font-medium border-gray-300">Header</div> --}}
            <div class="flex items-center gap-2 mb-4">
                <div>
                    <label class="block text-sm mb-1">Nomor Sample</label>
                    <input type="text" name="nosample" class="border rounded-md border-gray-300 p-2 w-full"
                        autocomplete="off" maxlength="4" value="{{ old('nosample', $header->nosample ?? '') }}"
                        required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Plot</label>
                    <input id="plot" type="text" name="plot" maxlength="10"
                        class="border rounded-md border-gray-300 p-2 w-full" autocomplete="off"
                        value="{{ old('plot', $header->plot ?? '') }}" required>
                </div>
            </div>
            <div class="flex items-center gap-2 mb-2">
                <div>
                    <label class="block text-sm mb-1">Blok</label>
                    <input id="blok" type="text" name="blok" maxlength="2" placeholder="(Otomatis)"
                        class="border rounded-md border-gray-300 p-2 w-[12ch]" autocomplete="off"
                        value="{{ old('blok', $header->blok ?? '') }}" readonly>
                </div>
                <div>
                    <label class="block text-sm mb-1">Company</label>
                    <input id="companycode" type="text" name="companycode" maxlength="6"
                        class="border rounded-md border-gray-300 p-2 w-[15ch]" autocomplete="off"
                        value="{{ old('companycode', $header->companycode ?? session('companycode')) }}" readonly>
                </div>
                <div>
                    <label class="block text-sm mb-1">Varietas</label>
                    <input type="text" name="varietas" id="varietas" placeholder="(Otomatis)"
                        class="border rounded-md border-gray-300 p-2 w-[20ch]" autocomplete="off" maxlength="10"
                        value="{{ old('varietas', $header->varietas ?? '') }}" required>
                </div>

                <div>
                    <label class="block text-sm mb-1">Kategori</label>
                    <input type="text" name="kat" id="kat" placeholder="(Otomatis)"
                        class="border rounded-md border-gray-300 p-2 w-[14ch]" autocomplete="off" maxlength="3"
                        value="{{ old('kat', $header->kat ?? '') }}" required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Tanggal Tanam</label>
                    <input type="date" name="tanggaltanam" id="tanggaltanam" placeholder="dd/mm/yyyy"
                        pattern="\d{2}/\d{2}/\d{4}" value="{{ old('tanggaltanam', $header->tanggaltanam ?? '') }}"
                        class="border rounded-md border-gray-300 p-2 w-full placeholder-gray-400 text-gray-400 focus:text-black valid:text-black"
                        required>
                </div>
                <div>
                    <label class="block text-sm mb-1">Tanggal Pengamatan</label>
                    <input type="date" name="tanggalpengamatan" placeholder="dd/mm/yyyy" pattern="\d{2}/\d{2}/\d{4}"
                        value="{{ old('tanggalpengamatan', $header->tanggalpengamatan ?? now()->toDateString()) }}"
                        class="border rounded-md border-gray-300 p-2 w-full placeholder-gray-400 text-gray-400 focus:text-black valid:text-black"
                        required>
                </div>
            </div>
        </div>

        <div class="mx-4 p-6 bg-white rounded-md shadow-md mt-4">
            <div class="text-center text-xl pb-2 mb-6 -mt-2 border-b font-medium border-gray-300">Detail Pengamatan (ni)
            </div>
            <div class="table-container">
                <table class="table table-bordered" id="listTable">
                    <thead>
                        <tr>
                            <th class="text-xs font-medium">ni</th>
                            <th class="text-xs font-medium">Jml Batang</th>
                            <th class="text-xs font-medium">Panj. GAP</th>
                            <th class="text-xs font-medium">PH Tanah</th>
                            <th class="text-xs font-medium">Ktk Gulma</th>
                            <th class="text-xs font-medium">T. Primer</th>
                            <th class="text-xs font-medium">T. Sekunder</th>
                            <th class="text-xs font-medium">T. Tersier</th>
                            <th class="text-xs font-medium">T. Kuarter</th>
                            <th class="text-xs font-medium">D. Primer</th>
                            <th class="text-xs font-medium">D. Sekunder</th>
                            <th class="text-xs font-medium">D. Tersier</th>
                            <th class="text-xs font-medium">D. Kuarter</th>
                            <th class="text-xs font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $lists = old('lists', $isEdit ? $header->lists : [[]]);
                        @endphp
                        @foreach ($lists as $index => $list)
                            <tr>
                                <td>
                                    <input type="text" name="lists[{{ $index }}][nourut]" min="0"
                                        value="{{ $list->nourut ?? $index + 1 }}"
                                        class="form-control border rounded-md border-gray-300 text-center" readonly>
                                </td>
                                <td>
                                    <input type="number" name="lists[{{ $index }}][jumlahbatang]"
                                        min="0" value="{{ $list->jumlahbatang ?? 0 }}" maxlength="5"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" name="lists[{{ $index }}][pan_gap]" min="0"
                                        value="{{ $list->pan_gap ?? 0 }}" maxlength="5"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" step="0.1" name="lists[{{ $index }}][ph_tanah]"
                                        min="0" value="{{ $list->ph_tanah ?? 0 }}" max="999.9"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" name="lists[{{ $index }}][ktk_gulma]"
                                        min="0" value="{{ $list->ktk_gulma ?? 0 }}" maxlength="2"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" name="lists[{{ $index }}][t_primer]" min="0"
                                        value="{{ $list->t_primer ?? 0 }}" maxlength="3"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" name="lists[{{ $index }}][t_sekunder]"
                                        min="0" value="{{ $list->t_sekunder ?? 0 }}" maxlength="3"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" name="lists[{{ $index }}][t_tersier]"
                                        min="0" value="{{ $list->t_tersier ?? 0 }}" maxlength="3"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" name="lists[{{ $index }}][t_kuarter]"
                                        min="0" value="{{ $list->t_kuarter ?? 0 }}" maxlength="3"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" step="any" name="lists[{{ $index }}][d_primer]"
                                        min="0" value="{{ $list->d_primer ?? 0 }}" max="999.9"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][d_sekunder]" min="0"
                                        value="{{ $list->d_sekunder ?? 0 }}" max="999.99"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][d_tersier]" min="0"
                                        value="{{ $list->d_tersier ?? 0 }}" max="999.999"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <input type="number" step="any"
                                        name="lists[{{ $index }}][d_kuarter]" min="0"
                                        value="{{ $list->d_kuarter ?? 0 }}" max="999.9999"
                                        class="form-control border rounded-md border-gray-300 auto-clear-zero"
                                        autocomplete="off" required>
                                </td>
                                <td>
                                    <button type="button" class="flex items-center group remove-row">
                                        <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2"
                                                d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                        </svg>

                                        <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" fill="currentColor" viewBox="0 0 24 24">
                                            <path fill-rule="evenodd"
                                                d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span
                                            class="text-red-500 dark:text-white group-hover:underline font-medium text-sm">Delete</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-center items-center mt-2">
                <button type="button" id="addRow" class="flex items-center group gap-1">
                    <svg class="w-6 h-6 text-gray-800 dark:text-white group-hover:hidden" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 7.757v8.486M7.757 12h8.486M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <svg class="w-6 h-6 text-gray-800 dark:text-white hidden group-hover:block" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4.243a1 1 0 1 0-2 0V11H7.757a1 1 0 1 0 0 2H11v3.243a1 1 0 1 0 2 0V13h3.243a1 1 0 1 0 0-2H13V7.757Z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-gray-800 dark:text-white group-hover:underline font-bold text-sm">Add</span>
                </button>
            </div>
            <div class="mt-6 flex gap-2">
                <button type="submit"
                    class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-green-600">
                    <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 11.917 9.724 16.5 19 7.5" />
                    </svg>
                    <span>{{ $buttonSubmit }}</span>
                </button>
                <a href="{{ route('transaction.agronomi.index') }}"
                    class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-red-600">
                    <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18 17.94 6M18 18 6.06 6" />
                    </svg>
                    <span>Cancel</span>
                </a>
            </div>
        </div>
    </form>


    <div id="scrollToTop">
        <button onclick="scrollToTop()"><svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                    d="M12 6v13m0-13 4 4m-4-4-4 4" />
            </svg>
        </button>
    </div>

    <script>
        document.getElementById('addRow').addEventListener('click', () => {
            const table = document.getElementById('listTable').querySelector('tbody');
            const rowCount = table.rows.length + 1;
            const newRow = `
            <tr>
                <td>
                    <input type="text" name="lists[${rowCount}][nourut]" min="0" value="${rowCount}"
                        class="form-control border rounded-md border-gray-300 text-center" readonly>
                </td>
                <td>
                    <input type="number" name="lists[${rowCount}][jumlahbatang]" min="0" maxlength="5" value="0"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" required>
                </td>
                <td>
                    <input type="number" name="lists[${rowCount}][pan_gap]" min="0" maxlength="5"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" step="0.1" name="lists[${rowCount}][ph_tanah]" min="0" max="999.9"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" name="lists[${rowCount}][ktk_gulma]" min="0" maxlength="2"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" name="lists[${rowCount}][t_primer]" min="0" maxlength="3"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" name="lists[${rowCount}][t_sekunder]" min="0" maxlength="3"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" name="lists[${rowCount}][t_tersier]" min="0" maxlength="3"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" name="lists[${rowCount}][t_kuarter]" min="0" maxlength="3"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" step="0.1" name="lists[${rowCount}][d_primer]" min="0" max="999.9"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" step="0.1" name="lists[${rowCount}][d_sekunder]" min="0" max="999.99"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" step="0.1" name="lists[${rowCount}][d_tersier]" min="0" max="999.999"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <input type="number" step="0.1" name="lists[${rowCount}][d_kuarter]" min="0" max="999.9999"
                        class="form-control border rounded-md border-gray-300 auto-clear-zero" autocomplete="off" value="0" required>
                </td>
                <td>
                    <button type="button" class="flex items-center group remove-row">
                        <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                        </svg>

                        <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd"
                                d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-red-500 dark:text-white hover:underline font-medium text-sm remove-row">Delete</span>
                    </button>
                </td>
            </tr>
        `;
            table.insertAdjacentHTML('beforeend', newRow);
        });

        document.getElementById('listTable').addEventListener('click', (e) => {
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
                resetRowNumbers();
            }
        });

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

        function resetRowNumbers() {
            const rows = document.querySelectorAll('#listTable tbody tr');
            rows.forEach((row, index) => {
                const noUrutInput = row.querySelector('input[name^="lists"][name$="[nourut]"]');
                noUrutInput.value = index + 1;
            });
        }
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
                            // $('#idblokplot').val(response.idblokplot);
                            $('#blok').val(response.blok);
                        },
                        error: function() {
                            alert('Data Mapping tidak ditemukan');
                            // $('#idblokplot').val('');
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

    <script>
        document.querySelectorAll('.auto-clear-zero').forEach(input => {
            input.addEventListener('focus', function() {
                if (this.value === '0') this.value = '';
            });
            input.addEventListener('blur', function() {
                if (this.value.trim() === '') this.value = '0';
            });
        });
    </script>
    <script>
        document.getElementById('plot').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>

</x-layout>
