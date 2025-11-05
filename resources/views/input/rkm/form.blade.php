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
            {{ $message }}
        </div>
    @enderror

    <form action="{{ $url }}" method="POST">
        @csrf
        @method($method)

        {{-- ======== HEADER FORM ======== --}}
        <div class="mx-4 p-6 bg-white rounded-md shadow-md">
            <div class="mb-4 flex justify-between">
                <div class="flex gap-2">
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">No. RKM</label>
                        <input type="text" name="rkmno" class="border rounded-md border-gray-300 p-2 w-full text-sm"
                            autocomplete="off" maxlength="4"
                            value="{{ old('rkmno', $rkmno ?? ($header->rkmno ?? '')) }}" readonly>
                    </div>
                    <div>
                        <label class="block mb-1 text-sm font-medium text-gray-700">Tanggal RKM</label>
                        <input type="date" name="rkmdate"
                            class="border rounded-md border-gray-300 p-2 w-full text-sm" autocomplete="off"
                            value="{{ old('rkmdate', $selectedDate ?? ($header->rkmdate ?? '')) }}" readonly>
                    </div>
                </div>

                <div class="flex items-start gap-2">
                    <a href="{{ route('input.rencana-kerja-mingguan.index') }}"
                        class="flex items-center space-x-2 bg-red-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-red-700">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18 17.94 6M18 18 6.06 6" />
                        </svg>
                        <span>Cancel</span>
                    </a>
                    <button type="submit"
                        class="flex items-center space-x-2 bg-green-600 text-white px-4 py-2 rounded-lg shadow-sm hover:bg-green-700">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 11.917 9.724 16.5 19 7.5" />
                        </svg>
                        <span>{{ $buttonSubmit }}</span>
                    </button>
                </div>
            </div>

            <div class="flex gap-2">
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="startdate" value="{{ old('startdate', $header->startdate ?? '') }}"
                        class="border rounded-md border-gray-300 p-2 w-full text-sm" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <input type="date" name="enddate" value="{{ old('enddate', $header->enddate ?? '') }}"
                        class="border rounded-md border-gray-300 p-2 w-full text-sm" required>
                </div>
                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Aktivitas</label>
                    <select name="activitycode" id="activitycode"
                        class="border rounded-md border-gray-300 p-2 w-full max-w-fit text-sm" required>
                        <option value="" disabled selected>-- Pilih Aktivitas --</option>
                        @foreach ($activity as $act)
                            <option class="text-black" value="{{ $act->activitycode }}" @selected(old('activitycode', $header->activitycode ?? '') == $act->activitycode)>
                                {{ $act->activitycode }} - {{ $act->activityname }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ======== DETAIL LIST ======== --}}
        <div class="mx-4 p-6 bg-white rounded-md shadow-md mt-4">
            <div class="text-xl pb-2 mb-6 -mt-2 border-b font-medium border-gray-300">List Aktivitas</div>

            @php
                $lists = old('lists', $isEdit ? $header->lists : [[]]);
            @endphp

            <div id="input-container">
                @foreach ($lists as $index => $list)
                    <div class="my-3 flex items-center gap-2 input-row">
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">No.</label>
                            <div class="border rounded-md border-gray-300 p-2 w-[60px] text-sm">{{ $index + 1 }}
                            </div>
                        </div>

                        {{-- BLOK --}}
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Blok</label>
                            <select name="lists[{{ $index }}][blok]"
                                class="blok-select border rounded-md border-gray-300 p-2 w-full max-w-fit text-sm"
                                required>
                                <option value="" disabled selected>-- Pilih Blok --</option>
                                @foreach ($bloks as $blok)
                                    <option class="text-black" value="{{ $blok->blok }}"
                                        @selected(old("lists.$index.blok", $list->blok ?? '') == $blok->blok)>
                                        {{ $blok->blok }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- PLOT --}}
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Plot</label>
                            <select name="lists[{{ $index }}][plot]"
                                class="plot-select border rounded-md border-gray-300 p-2 w-full max-w-fit text-sm"
                                data-selected="{{ old("lists.$index.plot", $list->plot ?? '') }}" required>
                                <option value="" disabled selected>-- Pilih Plot --</option>
                            </select>
                        </div>

                        {{-- LUAS --}}
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Luas Plot (Ha)</label>
                            <input type="number" min="0" max="999.99" step="0.01"
                                name="lists[{{ $index }}][totalluasactual]"
                                value="{{ old("lists.$index.totalluasactual", $list->totalluasactual ?? '') }}"
                                class="border rounded-md border-gray-300 p-2 w-[120px] text-sm" required />
                        </div>

                        {{-- ESTIMASI --}}
                        <div>
                            <label class="block mb-1 text-sm font-medium text-gray-700">Estimasi Pengerjaan (Ha)</label>
                            <input type="number" min="0" max="999.99" step="0.01"
                                name="lists[{{ $index }}][totalestimasi]"
                                value="{{ old("lists.$index.totalestimasi", $list->totalestimasi ?? '') }}"
                                class="border rounded-md border-gray-300 p-2 w-[180px] text-sm" required />
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-start items-center mt-2">
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
        </div>
    </form>

    {{-- ======== SCRIPT SECTION ======== --}}
    <script>
        // Generate new row
        document.getElementById('addRow').addEventListener('click', function() {
            const container = document.getElementById('input-container');
            const rowCount = container.querySelectorAll('.input-row').length;
            const newRow = document.createElement('div');
            newRow.classList.add('my-3', 'flex', 'items-center', 'gap-2', 'input-row');
            newRow.innerHTML = `
                <div><div class="border rounded-md border-gray-300 p-2 w-[60px] text-sm">${rowCount + 1}</div></div>
                <div>
                    <select name="lists[${rowCount}][blok]" class="blok-select border rounded-md border-gray-300 p-2 w-full max-w-fit text-sm" required>
                        <option value="" disabled selected>-- Pilih Blok --</option>
                        @foreach ($bloks as $blok)
                            <option value="{{ $blok->blok }}" class="text-black">{{ $blok->blok }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="lists[${rowCount}][plot]" class="plot-select border rounded-md border-gray-300 p-2 w-full max-w-fit text-sm" required>
                        <option value="" disabled selected>-- Pilih Plot --</option>
                    </select>
                </div>
                <div>
                    <input type="number" min="0" max="999.99" step="0.01" name="lists[${rowCount}][totalluasactual]" class="border rounded-md border-gray-300 p-2 w-[120px] text-sm" required />
                </div>
                <div>
                    <input type="number" min="0" max="999.99" step="0.01" name="lists[${rowCount}][totalestimasi]" class="border rounded-md border-gray-300 p-2 w-[180px] text-sm" required />
                </div>`;
            container.appendChild(newRow);
        });

        // Fetch plot by blok
        function loadPlotOptions(blok, plotSelect, selectedPlot = null) {
            const getPlotUrl = "{{ route('rkm.getPlot', ':blok') }}".replace(':blok', blok);
            fetch(getPlotUrl)
                .then(response => response.json())
                .then(data => {
                    plotSelect.innerHTML = '<option value="" disabled selected>-- Pilih Plot --</option>';
                    data.forEach(plot => {
                        const option = document.createElement('option');
                        option.value = plot;
                        option.classList = 'text-black'
                        option.textContent = plot;
                        if (plot === selectedPlot) option.selected = true;
                        plotSelect.appendChild(option);
                    });
                })
                .catch(() => {
                    plotSelect.innerHTML = '<option value="">Error loading</option>';
                });
        }

        // load plot untuk data edit
        document.querySelectorAll('.input-row').forEach(row => {
            const blokSelect = row.querySelector('.blok-select');
            const plotSelect = row.querySelector('.plot-select');
            const selectedPlot = plotSelect.dataset.selected;
            if (blokSelect.value) {
                loadPlotOptions(blokSelect.value, plotSelect, selectedPlot);
            }
        });

        // Event: ketika blok berubah
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('blok-select')) {
                const row = e.target.closest('.input-row');
                const plotSelect = row.querySelector('.plot-select');
                loadPlotOptions(e.target.value, plotSelect);
            }
        });

        // Event: ketika plot berubah, isi luas otomatis
        $(document).on('change', '.plot-select', function() {
            const row = $(this).closest('.input-row');
            const plot = $(this).val();
            const luasInput = row.find('input[name$="[totalluasactual]"]');

            if (plot) {
                $.ajax({
                    url: "{{ route('rkm.getData') }}",
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        plot: plot
                    },
                    success: function(response) {
                        luasInput.val(response.luasarea);
                    },
                    error: function() {
                        alert('Gagal memuat data luas area.');
                    }
                });
            } else {
                luasInput.val('');
            }
        });
    </script>
</x-layout>
