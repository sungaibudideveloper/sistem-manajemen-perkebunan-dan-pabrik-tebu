<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto px-6">
        <div class="flex flex-wrap gap-4">
            <div class="w-full h-fit bg-white shadow rounded-md">
                <form method="POST" action="{{ route('dashboard.agronomi') }}">
                    @csrf
                    <div class="p-2 text-center">
                        <h3 class="text-lg font-semibold text-gray-900 px-3">Filter</h3>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 rounded-md flex items-center gap-3 justify-center flex-wrap">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Average of</label>
                            <div class="relative">
                                <div class="dropdown relative">
                                    <button type="button" id="dropdownButtonAvg"
                                        class="w-[200px] flex items-center justify-between rounded-md font-medium border border-gray-300 shadow-sm hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white px-4 py-2 text-sm text-gray-700">
                                        <span id="dropdownLabelAvg">
                                            {{ Arr::get($verticalLabels, $verticalField, 'Pilih Field') }}
                                        </span>
                                        <svg class="-mr-1 ml-2 w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="m19 9-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div id="dropdownContentAvg"
                                        class="z-10 absolute hidden mt-[1px] w-auto text-sm bg-white border border-gray-300 shadow-md rounded-md p-2 px-3 max-h-[250px] overflow-auto">
                                        <div class="flex flex-col space-y-1 min-w-max">
                                            @foreach ($verticalLabels as $key => $label)
                                                <button type="button"
                                                    class="text-left w-full px-2 py-1 rounded hover:bg-gray-100 text-gray-700 text-sm {{ $verticalField == $key ? 'bg-gray-50 font-semibold' : '' }}"
                                                    onclick="selectAverage('{{ $key }}', '{{ $label }}')">
                                                    {{ $label }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="vertical" id="selectedAverage" value="{{ $verticalField }}">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Kebun</label>
                            <div class="relative">
                                <div class="dropdown relative">
                                    <button type="button" id="dropdownButtonComp"
                                        class="w-auto flex items-center justify-between rounded-md font-medium border border-gray-300 shadow-sm hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white px-4 py-2 text-sm text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                            class="h-4 w-4 mr-2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Filter Kebun</span>
                                        <svg class="-mr-1 ml-2 w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="m19 9-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div id="dropdownContentComp"
                                        class="z-10 absolute hidden mt-[1px] w-auto text-sm bg-white border border-gray-300 shadow-md rounded-md p-2 px-3 max-h-[600px] overflow-auto">
                                        <div class="flex flex-col space-y-1 min-w-max">
                                            <h6 class="mb-2 text-sm font-medium text-gray-900">Pilih Company
                                            </h6>
                                            <label class="inline-flex items-center" onchange="this.form.submit()">
                                                <input type="checkbox" id="selectAllComp"
                                                    class="form-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                    {{ old('companycode', request()->companycode ?? []) && count(old('companycode', request()->companycode ?? [])) === count($kdCompAgroOpt) ? 'checked' : '' }} />
                                                <span class="ml-2">(Select All)</span>
                                            </label>
                                            @foreach ($kdCompAgroOpt as $comp)
                                                <label class="inline-flex items-center" onchange="this.form.submit()">
                                                    <input type="checkbox" name="companycode[]"
                                                        value="{{ $comp->companycode }}"
                                                        class="form-checkbox comp-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                        {{ in_array($comp->companycode, old('companycode', request()->companycode ?? [])) ? 'checked' : '' }} />
                                                    <span class="ml-2">{{ $comp->nama }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Blok</label>
                            <div class="relative">
                                <div class="dropdown relative">
                                    <button type="button" id="dropdownButtonBlok"
                                        class="w-auto flex items-center justify-between rounded-md font-medium border border-gray-300 shadow-sm hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white px-4 py-2 text-sm text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                            class="h-4 w-4 mr-2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Filter Blok</span>
                                        <svg class="-mr-1 ml-2 w-4 h-4 text-gray-600" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="m19 9-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div id="dropdownContentBlok"
                                        class="z-10 absolute hidden mt-[1px] w-auto text-sm bg-white border border-gray-300 shadow-md rounded-md p-2 px-3 max-h-[600px] overflow-auto">
                                        <div class="flex flex-col space-y-1 min-w-max">
                                            <h6 class="mb-2 text-sm font-medium text-gray-900">Pilih Blok
                                            </h6>
                                            <label class="inline-flex items-center" onchange="this.form.submit()">
                                                <input type="checkbox" id="selectAllBlok"
                                                    class="form-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                    {{ old('blok', request()->blok ?? []) && count(old('blok', request()->blok ?? [])) === count($kdBlokAgroOpt) ? 'checked' : '' }} />
                                                <span class="ml-2">(Select All)</span>
                                            </label>
                                            @foreach ($kdBlokAgroOpt as $blok)
                                                <label class="inline-flex items-center" onchange="this.form.submit()">
                                                    <input type="checkbox" name="blok[]"
                                                        value="{{ $blok->blok }}"
                                                        class="form-checkbox blok-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                        {{ in_array($blok->blok, old('blok', request()->blok ?? [])) ? 'checked' : '' }} />
                                                    <span class="ml-2">{{ $blok->blok }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Plot</label>
                            <div class="relative">
                                <div class="dropdown relative">
                                    <button type="button" id="dropdownButtonPlot"
                                        class="w-auto flex items-center justify-between rounded-md font-medium border border-gray-300 shadow-sm hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 bg-white px-4 py-2 text-sm text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                            class="h-4 w-4 mr-2 text-gray-400" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Filter Plot</span>
                                        <svg class="-mr-1 ml-2 w-4 h-4 text-gray-600"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="m19 9-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div id="dropdownContentPlot"
                                        class="z-10 absolute hidden mt-[1px] w-auto text-sm bg-white border border-gray-300 shadow-md rounded-md p-2 px-3 max-h-[350px] overflow-auto">
                                        <div class="flex flex-col space-y-1 min-w-max">
                                            <h6 class="mb-2 text-sm font-medium text-gray-900">Pilih Plot
                                            </h6>
                                            <label class="inline-flex items-center" onchange="this.form.submit()">
                                                <input type="checkbox" id="selectAllPlot"
                                                    class="form-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                    {{ old('plot', request()->plot ?? []) && count(old('plot', request()->plot ?? [])) === count($kdPlotAgroOpt) ? 'checked' : '' }} />
                                                <span class="ml-2">(Select All)</span>
                                            </label>
                                            @foreach ($kdPlotAgroOpt as $plot)
                                                <label class="inline-flex items-center" onchange="this.form.submit()">
                                                    <input type="checkbox" name="plot[]"
                                                        value="{{ $plot->plot }}"
                                                        class="form-checkbox plot-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                        {{ in_array($plot->plot, old('plot', request()->plot ?? [])) ? 'checked' : '' }} />
                                                    <span class="ml-2">{{ $plot->plot }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="relative inline-block text-left w-full max-w-xs">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Waktu Pengamatan</label>
                                <div>
                                    <button type="button"
                                        class="inline-flex justify-center w-auto items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        id="menu-button" aria-expanded="false" aria-haspopup="true"
                                        onclick="toggleDropdown()">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                            class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Filter Waktu</span>
                                        <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="absolute z-10 mt-[1px] w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden"
                                    id="menu-dropdown">
                                    <div class="py-1 px-4" role="menu" aria-orientation="vertical"
                                        aria-labelledby="menu-button">
                                        <div class="flex items-center gap-2">
                                            <div class="py-2">
                                                <label for="start_month"
                                                    class="block text-sm font-medium text-gray-700">Start Month</label>
                                                <select id="start_month" name="start_month"
                                                    class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-700">
                                                    <option value="">Select Month</option>
                                                    @foreach ($monthsLabel as $month)
                                                        <option value="{{ $month }}"
                                                            {{ old('start_month', $startMonth ?? '') == $month ? 'selected' : '' }}>
                                                            {{ $month }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span
                                                class="flex items-end text-lg font-medium text-gray-400 pt-6">/</span>
                                            <div class="py-2">
                                                <label for="start_year"
                                                    class="block text-sm font-medium text-gray-700">Start Year</label>
                                                <input type="text" name="start_year" id="start_year"
                                                    oninput="validateNumber(this)"
                                                    class="border rounded-md border-gray-300 p-2 w-[80px] mt-1 text-sm"
                                                    maxlength="4" value="{{ $startYear }}">
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <div class="py-2">
                                                <label for="end_month"
                                                    class="block text-sm font-medium text-gray-700">End
                                                    Month</label>
                                                <select id="end_month" name="end_month"
                                                    class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-700">
                                                    <option value="">Select Month</option>
                                                    @foreach ($monthsLabel as $month)
                                                        <option value="{{ $month }}"
                                                            {{ old('end_month', $endMonth ?? '') == $month ? 'selected' : '' }}>
                                                            {{ $month }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span
                                                class="flex items-end text-lg font-medium text-gray-400 pt-6">/</span>
                                            <div class="py-2">
                                                <label for="end_year"
                                                    class="block text-sm font-medium text-gray-700">End Year</label>
                                                <input type="text" name="end_year" id="end_year"
                                                    oninput="validateNumber(this)"
                                                    class="border rounded-md border-gray-300 p-2 w-[80px] mt-1 text-sm"
                                                    maxlength="4" value="{{ $endYear }}">
                                            </div>
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
                            <div class="relative inline-block text-left w-full max-w-xs">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Umur Tanaman</label>
                                <div>
                                    <button type="button"
                                        class="inline-flex justify-center w-auto items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                        id="age-menu-button" aria-expanded="false" aria-haspopup="true"
                                        onclick="toggleAgeDropdown()">
                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true"
                                            class="h-4 w-4 mr-2 text-gray-400" viewbox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Filter Umur</span>
                                        <svg class="-mr-1 ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>

                                <div class="absolute z-10 mt-[1px] w-auto rounded-md bg-white border border-gray-300 shadow-lg hidden"
                                    id="age-menu-dropdown">
                                    <div class="py-1 px-4" role="menu" aria-orientation="vertical"
                                        aria-labelledby="age-menu-button">
                                        <div class="flex items-center gap-2">
                                            <div class="py-2">
                                                <label for="min_age"
                                                    class="block text-sm font-medium text-gray-700">Min Age</label>
                                                <input type="number" name="min_age" id="min_age"
                                                    class="border rounded-md border-gray-300 p-2 w-[80px] mt-1 text-sm"
                                                    min="0" value="{{ $minAge ?? '' }}" placeholder="Min">
                                            </div>
                                            <span
                                                class="flex items-end text-lg font-medium text-gray-400 pt-6">-</span>
                                            <div class="py-2">
                                                <label for="max_age"
                                                    class="block text-sm font-medium text-gray-700">Max Age</label>
                                                <input type="number" name="max_age" id="max_age"
                                                    class="border rounded-md border-gray-300 p-2 w-[80px] mt-1 text-sm"
                                                    min="0" value="{{ $maxAge ?? '' }}" placeholder="Max">
                                            </div>
                                        </div>

                                        <div class="py-2">
                                            <label for="age_unit"
                                                class="block text-sm font-medium text-gray-700">Unit</label>
                                            <select id="age_unit" name="age_unit"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-700">
                                                <option value="bulan"
                                                    {{ ($ageUnit ?? 'bulan') == 'bulan' ? 'selected' : '' }}>Bulan
                                                </option>
                                                <option value="tahun"
                                                    {{ ($ageUnit ?? 'bulan') == 'tahun' ? 'selected' : '' }}>Tahun
                                                </option>
                                            </select>
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
            </div>

            <div class="w-full h-auto">
                <div class="bg-white shadow sm:rounded-lg p-4">
                    <canvas id="pivotChart" class="max-h-[500px]"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('pivotChart').getContext('2d');
        const rawLabels = @json($xAxis);
        const rawDatasets = @json($chartData);
        const sortedIndices = rawLabels.map((label, index) => {
            const parts = label.split(' - ');
            return {
                index,
                category: parts[1] || '',
                age: parseInt(parts[0]) || 0,
                blokName: parts.splice(-2, 1)[0] || '',
                companyName: parts.pop()
            };
        }).sort((a, b) => {
            if (a.companyName !== b.companyName) {
                return a.companyName.localeCompare(b.companyName);
            }
            if (a.blokName !== b.blokName) {
                return a.blokName.localeCompare(b.blokName);
            }
            if (a.category !== b.category) {
                return a.category.localeCompare(b.category);
            }
            return a.age - b.age;
        });

        const sortedLabels = sortedIndices.map(item => rawLabels[item.index]);
        const sortedDatasets = rawDatasets.map(dataset => {
            const sortedData = sortedIndices.map(item => dataset.data[item.index]);
            return {
                ...dataset,
                data: sortedData
            };
        });

        const data = {
            labels: sortedLabels,
            datasets: sortedDatasets.map(item => ({
                label: item.label,
                data: item.data,
                companyName: item.companyName,
                blokName: item.blokName
            }))
        };

        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const verticalLabel = '{{ $verticalLabel }}';
                                const formattedValue =
                                    verticalLabel === 'Populasi' || verticalLabel === 'pH Tanah' ?
                                    value :
                                    `${value}%`;

                                return `${formattedValue}`;
                            }
                        }
                    },
                    legend: {
                        position: 'right',
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: '{{ $horizontalLabel }}'
                        },
                        stacked: false
                    },
                    y: {
                        title: {
                            display: true,
                            text: '{{ $verticalLabel }}'
                        },
                        ticks: {
                            callback: function(value) {
                                const verticalLabel = '{{ $verticalLabel }}';
                                return verticalLabel === 'Populasi' || verticalLabel === 'pH Tanah' ?
                                    value :
                                    `${value}%`;
                            }
                        }
                    }
                }
            },
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const dropdowns = ["Comp", "Blok", "Plot"];

            dropdowns.forEach(type => {
                const dropdownButton = document.getElementById(`dropdownButton${type}`);
                const dropdownContent = document.getElementById(`dropdownContent${type}`);
                const selectAll = document.getElementById(`selectAll${type}`);
                const checkboxes = document.querySelectorAll(`.${type.toLowerCase()}-checkbox`);

                dropdownButton.addEventListener("click", () => {
                    dropdownContent.classList.toggle("hidden");
                });

                document.addEventListener("click", (e) => {
                    if (!dropdownButton.contains(e.target) && !dropdownContent.contains(e.target)) {
                        dropdownContent.classList.add("hidden");
                    }
                });

                selectAll.addEventListener("change", (e) => {
                    checkboxes.forEach(checkbox => checkbox.checked = e.target.checked);
                });

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener("change", () => {
                        selectAll.checked = [...checkboxes].every(cb => cb.checked);
                    });
                });
            });
        });
    </script>

    <script>
        function toggleAgeDropdown() {
            const dropdown = document.getElementById('age-menu-dropdown');
            dropdown.classList.toggle('hidden');

            const timeDropdown = document.getElementById('menu-dropdown');
            if (!timeDropdown.classList.contains('hidden')) {
                timeDropdown.classList.add('hidden');
            }
        }

        function toggleDropdown() {
            const dropdown = document.getElementById('menu-dropdown');
            dropdown.classList.toggle('hidden');

            const ageDropdown = document.getElementById('age-menu-dropdown');
            if (!ageDropdown.classList.contains('hidden')) {
                ageDropdown.classList.add('hidden');
            }
        }

        document.addEventListener('click', function(event) {
            const ageButton = document.getElementById('age-menu-button');
            const ageDropdown = document.getElementById('age-menu-dropdown');
            const timeButton = document.getElementById('menu-button');
            const timeDropdown = document.getElementById('menu-dropdown');

            if (!ageButton.contains(event.target) && !ageDropdown.contains(event.target)) {
                ageDropdown.classList.add('hidden');
            }

            if (!timeButton.contains(event.target) && !timeDropdown.contains(event.target)) {
                timeDropdown.classList.add('hidden');
            }
        });
    </script>
    <script>
        const btnAvg = document.getElementById("dropdownButtonAvg");
        const menuAvg = document.getElementById("dropdownContentAvg");
        const labelAvg = document.getElementById("dropdownLabelAvg");
        const inputAvg = document.getElementById("selectedAverage");

        btnAvg.addEventListener("click", () => {
            menuAvg.classList.toggle("hidden");
        });

        function selectAverage(value, label) {
            inputAvg.value = value;
            labelAvg.textContent = label;
            menuAvg.classList.add("hidden");
            btnAvg.closest("form").submit();
        }

        document.addEventListener("click", (e) => {
            if (!btnAvg.contains(e.target) && !menuAvg.contains(e.target)) {
                menuAvg.classList.add("hidden");
            }
        });
    </script>
    <style>
        .relative {
            overflow: visible;
        }
    </style>

</x-layout>
