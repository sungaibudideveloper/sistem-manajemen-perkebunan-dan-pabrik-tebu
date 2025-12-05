{{-- resources\views\report\saldo-panen\index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="max-w-full mx-auto">
        <!-- Header & Filters -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6 border border-gray-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Report Saldo Panen</h2>
                    <p class="text-sm text-gray-600 mt-1">Snapshot status panen per tanggal</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('report.panen-track-plot.index') }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-md no-print">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Tracking Detail per Plot
                    </a>
                    <button onclick="loadData()" class="bg-gray-900 hover:bg-black text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-md">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    <button onclick="handlePrint()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-md no-print">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
                    <input type="date" id="filterDate" value="{{ $selectedDate }}" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mandor</label>
                    <select id="filterMandor" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                        <option value="">Semua Mandor</option>
                        @foreach($mandors as $mandor)
                        <option value="{{ $mandor->userid }}">{{ $mandor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lifecycle</label>
                    <select id="filterLifecycle" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                        <option value="">Semua Status</option>
                        <option value="PC">PC</option>
                        <option value="RC1">RC1</option>
                        <option value="RC2">RC2</option>
                        <option value="RC3">RC3</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Progress</label>
                    <select id="filterProgress" class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                        <option value="">Semua Progress</option>
                        <option value="low">< 50%</option>
                        <option value="medium">50% - 90%</option>
                        <option value="high">> 90%</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cari Plot/Batch</label>
                    <input type="text" id="filterSearch" placeholder="Cari..." class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-900 focus:border-gray-900">
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mb-4"></div>
            <p class="text-gray-600 font-medium">Memuat data...</p>
        </div>

        <!-- Summary Cards -->
        <div id="summarySection" class="hidden grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- Will be populated by JavaScript -->
        </div>

        <!-- Data Section (Grouped by Mandor) -->
        <div id="dataSection" class="hidden space-y-6">
            <!-- Will be populated by JavaScript - one table per mandor -->
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="bg-white rounded-lg shadow-lg p-12 text-center border border-gray-200">
            <svg class="w-24 h-24 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Pilih tanggal untuk melihat saldo panen</h3>
            <p class="text-gray-500">Klik "Refresh" setelah memilih filter</p>
        </div>
    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>

    <script>
        let currentData = [];

        // Auto-load on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadData();
        });

        // Trigger load on filter change
        ['filterDate', 'filterMandor', 'filterLifecycle', 'filterProgress'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', loadData);
        });

        // Search with debounce
        let searchTimeout;
        document.getElementById('filterSearch')?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadData, 500);
        });

        function loadData() {
            const params = new URLSearchParams({
                date: document.getElementById('filterDate').value,
                mandor_id: document.getElementById('filterMandor').value,
                lifecycle: document.getElementById('filterLifecycle').value,
                progress_filter: document.getElementById('filterProgress').value,
                search: document.getElementById('filterSearch').value
            });

            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('summarySection').classList.add('hidden');
            document.getElementById('dataSection').classList.add('hidden');
            document.getElementById('emptyState').classList.add('hidden');

            fetch(`{{ route('report.saldo-panen.data') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        currentData = data.data;
                        renderSummary(data.summary);
                        renderTable(data.data, data.summary);
                        
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
            const section = document.getElementById('summarySection');
            section.innerHTML = `
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-gray-800">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Plot</p>
                    <p class="text-3xl font-bold text-gray-900">${summary.total_plots}</p>
                    <div class="mt-2 flex gap-2 text-xs">
                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded">${summary.panen_hari_ini} hari ini</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">${summary.ongoing} ongoing</span>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-blue-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Total Luas Batch</p>
                    <p class="text-3xl font-bold text-blue-600">${parseFloat(summary.total_luas_batch).toFixed(2)}</p>
                    <p class="text-xs text-gray-500 mt-2">Hektar</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-green-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Sudah Dipanen</p>
                    <p class="text-3xl font-bold text-green-600">${parseFloat(summary.total_dipanen).toFixed(2)}</p>
                    <p class="text-xs text-gray-500 mt-2">Hektar</p>
                </div>
                <div class="bg-white rounded-lg shadow-md p-5 border-l-4 border-orange-600">
                    <p class="text-sm text-gray-600 mb-1 font-semibold">Sisa</p>
                    <p class="text-3xl font-bold text-orange-600">${parseFloat(summary.total_sisa).toFixed(2)}</p>
                    <p class="text-xs text-gray-500 mt-2">Hektar • ${parseFloat(summary.avg_progress).toFixed(1)}% progress</p>
                </div>
            `;
        }

        function renderTable(data, summary) {
            const section = document.getElementById('dataSection');
            section.innerHTML = '';

            if (data.length === 0) {
                section.innerHTML = '<div class="bg-white rounded-lg shadow-lg p-8 text-center"><p class="text-gray-500">Tidak ada data</p></div>';
                return;
            }

            // Group by mandor
            const groupedByMandor = {};
            data.forEach(item => {
                const mandorKey = item.last_mandor_name || 'Belum Pernah Dipanen';
                if (!groupedByMandor[mandorKey]) {
                    groupedByMandor[mandorKey] = [];
                }
                groupedByMandor[mandorKey].push(item);
            });

            // Render table for each mandor
            Object.keys(groupedByMandor).sort().forEach(mandorName => {
                const mandorData = groupedByMandor[mandorName];
                
                // Calculate mandor subtotal
                const mandorSubtotal = {
                    total_plots: mandorData.length,
                    total_luas: mandorData.reduce((sum, item) => sum + parseFloat(item.batcharea), 0),
                    total_dipanen: mandorData.reduce((sum, item) => sum + parseFloat(item.total_dipanen), 0),
                    total_sisa: mandorData.reduce((sum, item) => sum + parseFloat(item.sisa), 0),
                    avg_progress: mandorData.reduce((sum, item) => sum + parseFloat(item.progress), 0) / mandorData.length
                };

                const tableHtml = `
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-200">
                        <div class="bg-gray-800 px-6 py-4 flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-white">Mandor: ${mandorName}</h3>
                                <p class="text-xs text-gray-300 mt-1">${mandorSubtotal.total_plots} Plot • ${mandorSubtotal.total_luas.toFixed(2)} Ha</p>
                            </div>
                            <div class="text-right text-white text-sm">
                                <div>Progress: <span class="font-bold">${mandorSubtotal.avg_progress.toFixed(1)}%</span></div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300 text-xs">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide w-16">Blok</th>
                                        <th class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide w-20">Plot</th>
                                        <th class="px-3 py-3 text-left font-semibold text-gray-700 uppercase tracking-wide w-32">Batch No</th>
                                        <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-20">Status</th>
                                        <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-24">Varietas</th>
                                        <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-24">Mulai Panen</th>
                                        <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-20">Hari</th>
                                        <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide w-24 bg-blue-50">Luas Batch<br>(Ha)</th>
                                        <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide w-24 bg-green-50">Sudah Dipanen<br>(Ha)</th>
                                        <th class="px-3 py-3 text-right font-semibold text-gray-700 uppercase tracking-wide w-24 bg-orange-50">Sisa<br>(Ha)</th>
                                        <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-24">Progress<br>(%)</th>
                                        <th class="px-3 py-3 text-center font-semibold text-gray-700 uppercase tracking-wide w-32">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    ${renderMandorRows(mandorData)}
                                </tbody>
                                <tfoot class="bg-gray-100 font-semibold border-t-2 border-gray-300">
                                    <tr>
                                        <td colspan="7" class="px-3 py-3 text-right text-gray-900 uppercase text-xs">Subtotal:</td>
                                        <td class="px-3 py-3 text-right text-gray-900 bg-blue-50">${mandorSubtotal.total_luas.toFixed(2)}</td>
                                        <td class="px-3 py-3 text-right text-gray-900 bg-green-50">${mandorSubtotal.total_dipanen.toFixed(2)}</td>
                                        <td class="px-3 py-3 text-right text-gray-900 bg-orange-50">${mandorSubtotal.total_sisa.toFixed(2)}</td>
                                        <td class="px-3 py-3 text-center text-gray-900">${mandorSubtotal.avg_progress.toFixed(1)}%</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                `;
                
                section.innerHTML += tableHtml;
            });

            // Add grand total section
            const grandTotalHtml = `
                <div class="bg-gray-900 rounded-lg shadow-lg p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">Grand Total - Semua Mandor</h3>
                    <div class="grid grid-cols-4 gap-4">
                        <div class="text-center">
                            <p class="text-sm opacity-80">Total Plot</p>
                            <p class="text-2xl font-bold">${summary.total_plots}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm opacity-80">Luas Batch</p>
                            <p class="text-2xl font-bold">${parseFloat(summary.total_luas_batch).toFixed(2)} Ha</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm opacity-80">Sudah Dipanen</p>
                            <p class="text-2xl font-bold">${parseFloat(summary.total_dipanen).toFixed(2)} Ha</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm opacity-80">Sisa</p>
                            <p class="text-2xl font-bold">${parseFloat(summary.total_sisa).toFixed(2)} Ha</p>
                        </div>
                    </div>
                </div>
            `;
            section.innerHTML += grandTotalHtml;
        }

        function renderMandorRows(mandorData) {
            const statusColors = {
                'PC': 'bg-yellow-100 text-yellow-800',
                'RC1': 'bg-green-100 text-green-800',
                'RC2': 'bg-blue-100 text-blue-800',
                'RC3': 'bg-purple-100 text-purple-800'
            };

            const statusLabelColors = {
                'PANEN HARI INI': 'bg-green-600 text-white',
                'ONGOING': 'bg-blue-100 text-blue-800',
                'SELESAI': 'bg-gray-600 text-white',
                'BELUM PERNAH PANEN': 'bg-orange-100 text-orange-800'
            };

            return mandorData.map(item => `
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-3 text-gray-900 font-medium">${item.blok}</td>
                    <td class="px-3 py-3 text-gray-900 font-medium">${item.plot}</td>
                    <td class="px-3 py-3 text-gray-900 font-mono text-xs">${item.batchno}</td>
                    <td class="px-3 py-3 text-center">
                        <span class="px-2 py-1 rounded text-xs font-medium ${statusColors[item.lifecycle] || 'bg-gray-100 text-gray-800'}">
                            ${item.lifecycle}
                        </span>
                    </td>
                    <td class="px-3 py-3 text-center text-gray-700">${item.kodevarietas || '-'}</td>
                    <td class="px-3 py-3 text-center text-gray-700">${item.tanggal_panen}</td>
                    <td class="px-3 py-3 text-center text-gray-700">${item.hari_panen}</td>
                    <td class="px-3 py-3 text-right text-gray-700 bg-blue-50">${item.batcharea}</td>
                    <td class="px-3 py-3 text-right font-semibold text-green-700 bg-green-50">${item.total_dipanen}</td>
                    <td class="px-3 py-3 text-right font-semibold text-orange-700 bg-orange-50">${item.sisa}</td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                <div class="bg-gray-900 h-2 rounded-full" style="width: ${Math.min(100, item.progress)}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-gray-900">${item.progress}%</span>
                        </div>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <span class="px-2 py-1 rounded text-xs font-semibold ${statusLabelColors[item.status_label] || 'bg-gray-100 text-gray-800'}">
                            ${item.status_label}
                        </span>
                    </td>
                </tr>
            `).join('');
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