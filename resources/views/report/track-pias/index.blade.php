{{-- resources\views\report\track-pias\index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="max-w-full mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Report Track Pias (Activity 5.2.1)</h2>
                    <p class="text-sm text-gray-600 mt-1">Tracking aplikasi Pias & Parasitoid - 2x per bulan (RON1 & RON2)</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadData()" class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-md">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Tampilkan Data
                    </button>
                    <button onclick="handlePrint()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-md no-print">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                    <select id="filterYear" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                        @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Blok
                        <button onclick="toggleSelectAll()" class="ml-2 text-xs text-blue-600 hover:text-blue-800 font-normal underline">
                            Select All / Deselect All
                        </button>
                    </label>
                    <div id="blokSelection" class="border-2 border-gray-300 rounded-lg p-3 max-h-32 overflow-y-auto bg-white">
                        @foreach($bloks as $blok)
                        <label class="flex items-center mb-2 cursor-pointer hover:bg-gray-50 p-1 rounded">
                            <input type="checkbox" name="bloks[]" value="{{ $blok }}" class="blok-checkbox w-4 h-4 text-gray-900 border-gray-300 rounded focus:ring-gray-900">
                            <span class="ml-2 text-sm text-gray-700 font-medium">{{ $blok }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="selectedCount">0</span> blok dipilih
                    </p>
                </div>
            </div>
        </div>

        <div id="loadingState" class="hidden bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mb-4"></div>
            <p class="text-gray-600 font-medium">Memuat data...</p>
        </div>

        <div id="summarySection" class="hidden mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-gray-800">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Plot</p>
                    <p class="text-3xl font-bold text-gray-900" id="summaryTotalPlots">0</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">RON1 Completed</p>
                    <p class="text-3xl font-bold text-blue-600" id="summaryRON1">0</p>
                    <p class="text-xs text-gray-500 mt-2"><span id="summaryRON1Percent">0</span>% completion</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">RON2 Completed</p>
                    <p class="text-3xl font-bold text-green-600" id="summaryRON2">0</p>
                    <p class="text-xs text-gray-500 mt-2"><span id="summaryRON2Percent">0</span>% completion</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-orange-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Activities</p>
                    <p class="text-3xl font-bold text-orange-600" id="summaryTotal">0</p>
                </div>
            </div>
        </div>

        <div id="dataSection" class="hidden space-y-6">
        </div>

        <div id="emptyState" class="bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih Blok untuk Melihat Data</h3>
            <p class="text-gray-500">Centang minimal 1 blok, lalu klik "Tampilkan Data"</p>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            @page {
                size: landscape;
                margin: 10mm;
            }
            
            body {
                font-size: 9pt;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
        
        .month-cell {
            min-width: 80px;
            max-width: 80px;
        }
        
        .ron-cell {
            min-width: 40px;
            max-width: 40px;
            font-size: 11px;
        }
    </style>

    <script>
        let currentData = [];
        let allBloksSelected = false;

        // Update selected count
        document.querySelectorAll('.blok-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const count = document.querySelectorAll('.blok-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = count;
        }

        function toggleSelectAll() {
            allBloksSelected = !allBloksSelected;
            document.querySelectorAll('.blok-checkbox').forEach(checkbox => {
                checkbox.checked = allBloksSelected;
            });
            updateSelectedCount();
        }

        function loadData() {
            const selectedBloks = Array.from(document.querySelectorAll('.blok-checkbox:checked')).map(cb => cb.value);
            
            if (selectedBloks.length === 0) {
                alert('Pilih minimal 1 blok terlebih dahulu');
                return;
            }

            const year = document.getElementById('filterYear').value;

            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('summarySection').classList.add('hidden');
            document.getElementById('dataSection').classList.add('hidden');
            document.getElementById('emptyState').classList.add('hidden');

            fetch(`{{ route('report.track-pias.data') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    year: year,
                    bloks: selectedBloks
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentData = data.data;
                    renderSummary(data.summary);
                    renderTable(data.data, data.year);
                    
                    document.getElementById('loadingState').classList.add('hidden');
                    document.getElementById('summarySection').classList.remove('hidden');
                    document.getElementById('dataSection').classList.remove('hidden');
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Gagal memuat data');
            });
        }

        function renderSummary(summary) {
            document.getElementById('summaryTotalPlots').textContent = summary.total_plots;
            document.getElementById('summaryRON1').textContent = summary.total_ron1;
            document.getElementById('summaryRON2').textContent = summary.total_ron2;
            document.getElementById('summaryTotal').textContent = summary.total_ron1 + summary.total_ron2;
            document.getElementById('summaryRON1Percent').textContent = summary.completion_rate_ron1;
            document.getElementById('summaryRON2Percent').textContent = summary.completion_rate_ron2;
        }

        function renderTable(data, year) {
            const section = document.getElementById('dataSection');
            section.innerHTML = '';

            if (data.length === 0) {
                section.innerHTML = '<div class="bg-white rounded-lg shadow-lg p-8 text-center"><p class="text-gray-500">Tidak ada data</p></div>';
                return;
            }

            // Group by blok
            const groupedByBlok = {};
            data.forEach(item => {
                if (!groupedByBlok[item.blok]) {
                    groupedByBlok[item.blok] = [];
                }
                groupedByBlok[item.blok].push(item);
            });

            // Render table for each blok
            Object.keys(groupedByBlok).sort().forEach(blok => {
                const blokData = groupedByBlok[blok];
                
                const tableHtml = `
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                        <div class="bg-gray-800 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">Blok: ${blok}</h3>
                            <p class="text-xs text-gray-300 mt-1">${blokData.length} Plot • Tahun ${year}</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th rowspan="2" class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300 sticky left-0 bg-gray-50 z-10">Plot</th>
                                        <th rowspan="2" class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300">Batch</th>
                                        <th rowspan="2" class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300">Status</th>
                                        <th rowspan="2" class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide border-r-2 border-gray-300">Varietas</th>
                                        ${renderMonthHeaders()}
                                    </tr>
                                    <tr>
                                        ${renderRONHeaders()}
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    ${renderBlokRows(blokData)}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                
                section.innerHTML += tableHtml;
            });
        }

        function renderMonthHeaders() {
            const months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
            return months.map(month => 
                `<th colspan="2" class="px-2 py-2 text-center font-semibold text-gray-700 uppercase tracking-wide border-r border-gray-300 month-cell bg-blue-50">${month}</th>`
            ).join('');
        }

        function renderRONHeaders() {
            let html = '';
            for (let i = 0; i < 12; i++) {
                html += `
                    <th class="px-1 py-2 text-center font-semibold text-gray-700 text-xs border-r border-gray-200 ron-cell bg-green-50">R1</th>
                    <th class="px-1 py-2 text-center font-semibold text-gray-700 text-xs border-r border-gray-300 ron-cell bg-orange-50">R2</th>
                `;
            }
            return html;
        }

        function renderBlokRows(blokData) {
            const statusColors = {
                'PC': 'bg-yellow-100 text-yellow-800',
                'RC1': 'bg-green-100 text-green-800',
                'RC2': 'bg-blue-100 text-blue-800',
                'RC3': 'bg-purple-100 text-purple-800'
            };

            return blokData.map(item => {
                const monthCells = item.months.map(month => {
                    const ron1Display = month.ron1 ? month.ron1 : '';
                    const ron2Display = month.ron2 ? month.ron2 : '';
                    const ron1Class = month.ron1 ? 'bg-green-100 text-green-800 font-semibold' : 'bg-gray-50';
                    const ron2Class = month.ron2 ? 'bg-orange-100 text-orange-800 font-semibold' : 'bg-gray-50';
                    
                    return `
                        <td class="px-1 py-2 text-center border-r border-gray-200 ron-cell ${ron1Class}">${ron1Display}</td>
                        <td class="px-1 py-2 text-center border-r border-gray-300 ron-cell ${ron2Class}">${ron2Display}</td>
                    `;
                }).join('');

                return `
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-3 text-gray-900 font-semibold border-r-2 border-gray-300 sticky left-0 bg-white">${item.plot}</td>
                        <td class="px-3 py-3 text-gray-900 font-mono text-xs border-r-2 border-gray-300">${item.batchno}</td>
                        <td class="px-3 py-3 text-center border-r-2 border-gray-300">
                            <span class="px-2 py-1 rounded text-xs font-medium ${statusColors[item.lifecycle] || 'bg-gray-100 text-gray-800'}">
                                ${item.lifecycle}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center text-gray-700 border-r-2 border-gray-300">${item.varietas || '-'}</td>
                        ${monthCells}
                    </tr>
                `;
            }).join('');
        }

        function showError(message) {
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('emptyState').classList.remove('hidden');
            alert(message);
        }

        function handlePrint() {
            window.print();
        }
    </script>
</x-layout>