<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto px-6">
        <div class="flex flex-wrap gap-4">
            <div class="w-full h-fit pb-1 bg-white shadow rounded-md">
                <form method="POST" action="{{ route('dashboard.hpt') }}">
                    @csrf
                    <div class="p-2 text-center">
                        <h3 class="text-lg font-semibold text-gray-900 px-3">Filter</h3>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 rounded-md flex items-center gap-3 justify-center">
                        <div>
                            <label for="vertical" class="block text-xs font-medium text-gray-700">Average of</label>
                            <select name="vertical" id="vertical" onchange="this.form.submit()"
                                class="font-medium text-gray-700 mt-1 block w-fit rounded-md border-gray-300 shadow-sm text-sm hover:bg-gray-50">
                                @foreach ($verticalLabels as $key => $label)
                                    <option value="{{ $key }}" {{ $verticalField == $key ? 'selected' : '' }}
                                        class="text-black">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
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
                                        class="z-10 absolute hidden mt-[1px] w-auto text-sm bg-white border border-gray-300 shadow-md rounded-md p-2 px-3">
                                        <div class="flex flex-col space-y-1 min-w-max">
                                            <h6 class="mb-2 text-sm font-medium text-gray-900">Pilih Company
                                            </h6>
                                            <label class="inline-flex items-center" onchange="this.form.submit()">
                                                <input type="checkbox" id="selectAllComp"
                                                    class="form-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                    {{ old('kd_comp', request()->kd_comp ?? []) && count(old('kd_comp', request()->kd_comp ?? [])) === count($kdCompHPTOpt) ? 'checked' : '' }} />
                                                <span class="ml-2">(Select All)</span>
                                            </label>
                                            @foreach ($kdCompHPTOpt as $comp)
                                                <label class="inline-flex items-center" onchange="this.form.submit()">
                                                    <input type="checkbox" name="kd_comp[]" value="{{ $comp->kd_comp }}"
                                                        class="form-checkbox comp-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                        {{ in_array($comp->kd_comp, old('kd_comp', request()->kd_comp ?? [])) ? 'checked' : '' }} />
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
                                        class="z-10 absolute hidden mt-[1px] w-auto text-sm bg-white border border-gray-300 shadow-md rounded-md p-2 px-3">
                                        <div class="flex flex-col space-y-1 min-w-max">
                                            <h6 class="mb-2 text-sm font-medium text-gray-900">Pilih Blok
                                            </h6>
                                            <label class="inline-flex items-center" onchange="this.form.submit()">
                                                <input type="checkbox" id="selectAllBlok"
                                                    class="form-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                    {{ old('kd_blok', request()->kd_blok ?? []) && count(old('kd_blok', request()->kd_blok ?? [])) === count($kdBlokHPTOpt) ? 'checked' : '' }} />
                                                <span class="ml-2">(Select All)</span>
                                            </label>
                                            @foreach ($kdBlokHPTOpt as $blok)
                                                <label class="inline-flex items-center" onchange="this.form.submit()">
                                                    <input type="checkbox" name="kd_blok[]"
                                                        value="{{ $blok->kd_blok }}"
                                                        class="form-checkbox blok-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                        {{ in_array($blok->kd_blok, old('kd_blok', request()->kd_blok ?? [])) ? 'checked' : '' }} />
                                                    <span class="ml-2">{{ $blok->kd_blok }}</span>
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
                                        class="z-10 absolute hidden mt-[1px] w-auto text-sm bg-white border border-gray-300 shadow-md rounded-md p-2 px-3 max-h-[600px] overflow-auto">
                                        <div class="flex flex-col space-y-1 min-w-max">
                                            <h6 class="mb-2 text-sm font-medium text-gray-900">Pilih Plot
                                            </h6>
                                            <label class="inline-flex items-center" onchange="this.form.submit()">
                                                <input type="checkbox" id="selectAllPlot"
                                                    class="form-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                    {{ old('kd_plot', request()->kd_plot ?? []) && count(old('kd_plot', request()->kd_plot ?? [])) === count($kdPlotHPTOpt) ? 'checked' : '' }} />
                                                <span class="ml-2">(Select All)</span>
                                            </label>
                                            @foreach ($kdPlotHPTOpt as $plot)
                                                <label class="inline-flex items-center" onchange="this.form.submit()">
                                                    <input type="checkbox" name="kd_plot[]"
                                                        value="{{ $plot->kd_plot }}"
                                                        class="form-checkbox plot-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                        {{ in_array($plot->kd_plot, old('kd_plot', request()->kd_plot ?? [])) ? 'checked' : '' }} />
                                                    <span class="ml-2">{{ $plot->kd_plot }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="relative inline-block text-left w-full max-w-xs">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Bulan Pengamatan</label>
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
                                        <span>Month Filter</span>
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
                                        <div class="py-2">
                                            <label for="start_month"
                                                class="block text-sm font-medium text-gray-700">Start Month</label>
                                            <select id="start_month" name="start_month"
                                                class="mt-1 block w-auto rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm text-gray-700">
                                                <option value="" selected disabled>Select Month</option>
                                                @foreach ($monthsLabel as $month)
                                                    <option value="{{ $month }}"
                                                        {{ old('start_month', $startMonth ?? '') == $month ? 'selected' : '' }}>
                                                        {{ $month }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="py-2">
                                            <label for="end_month" class="block text-sm font-medium text-gray-700">End
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
                                    verticalLabel === '% PPT' || verticalLabel === '% PBT' ?
                                    `${value}%` :
                                    value;

                                return `${formattedValue}`;
                            }
                        }
                    },
                    legend: {
                        position: 'right'
                    },
                    // datalabels: {
                    //     anchor: 'center',
                    //     align: 'center',
                    //     formatter: (value, context) => {
                    //         const verticalLabel = '{{ $verticalLabel }}';
                    //         return verticalLabel === '% PPT' || verticalLabel === '% PBT' ?
                    //             `${value}%` :
                    //             value;
                    //     },
                    //     font: {
                    //         weight: 'bold',
                    //         size: 12
                    //     },
                    //     color: '#fff'
                    // }
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
                                return verticalLabel === '% PPT' || verticalLabel === '% PBT' ?
                                    `${value}%` :
                                    value;
                            }
                        }
                    }
                }
            },
            // plugins: [ChartDataLabels]
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
        .relative {
            overflow: visible;
        }
    </style>

</x-layout>
