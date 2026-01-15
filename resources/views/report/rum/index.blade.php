<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    <div class="mx-auto py-4">
        <!-- Header Card with Gradient -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-700 rounded-xl shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white mb-2">Rekap Upah Mingguan</h1>
                    <p class="text-indigo-100 text-sm">Kelola dan pantau rekap upah tenaga kerja mingguan</p>
                </div>
                <div class="hidden md:block">
                    <svg class="w-16 h-16 text-indigo-300 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-visible">
            <!-- Filter Section -->
            <form method="POST" action="{{ route('report.rekap-upah-mingguan.index') }}">
                @csrf
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200 p-4 rounded-t-lg">
                    <div class="flex flex-wrap items-end gap-3 lg:justify-between justify-center">
                        <!-- Left Section - Tenaga Kerja -->
                        <div class="flex gap-3">
                            <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                <label for="tenagakerjarum" class="block text-xs font-semibold text-gray-700 mb-2">
                                    Jenis Tenaga Kerja <span class="text-red-500">*</span>
                                </label>
                                <select name="tenagakerjarum" id="tenagakerjarum"
                                    onchange="Alpine.store('loading').start(); this.form.submit()"
                                    class="w-48 border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                    <option value="" disabled
                                        {{ old('tenagakerjarum', session('tenagakerjarum')) == null ? 'selected' : '' }}>
                                        -- Pilih Tenaga Kerja --
                                    </option>
                                    <option value="Harian"
                                        {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Harian' ? 'selected' : '' }}>
                                        Harian
                                    </option>
                                    <option value="Borongan"
                                        {{ old('tenagakerjarum', session('tenagakerjarum')) == 'Borongan' ? 'selected' : '' }}>
                                        Borongan
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Right Section - Filters & Actions -->
                        <div class="flex items-end gap-2 flex-wrap">
                            <!-- Date Filter -->
                            <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Range Tanggal</label>
                                <div class="relative">
                                    <button type="button"
                                        class="inline-flex items-center gap-2 bg-white px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                                        id="menu-button" onclick="toggleDropdown()">
                                        <svg class="h-4 w-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>Date Filter</span>
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div class="absolute left-0 z-10 mt-2 w-72 rounded-lg bg-white border border-gray-200 shadow-xl hidden"
                                        id="menu-dropdown">
                                        <div class="p-4 space-y-3">
                                            <div>
                                                <label for="start_date"
                                                    class="block text-sm font-semibold text-gray-700 mb-1.5">Start
                                                    Date</label>
                                                <input type="date" id="start_date" name="start_date" required
                                                    value="{{ old('start_date', $startDate ?? now()->startOfWeek()->format('Y-m-d')) }}"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm" />
                                            </div>
                                            <div>
                                                <label for="end_date"
                                                    class="block text-sm font-semibold text-gray-700 mb-1.5">End
                                                    Date</label>
                                                <input type="date" id="end_date" name="end_date" required
                                                    value="{{ old('end_date', $endDate ?? now()->endOfWeek()->format('Y-m-d')) }}"
                                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 text-sm" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Items per page -->
                            <div
                                class="flex items-center gap-2 bg-white px-3 py-2 rounded-lg border border-gray-200 shadow-sm">
                                <label for="perPage"
                                    class="text-xs font-medium text-gray-600 whitespace-nowrap">Show:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    class="w-12 p-1.5 border border-gray-300 rounded-md text-sm text-center focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                                <span class="text-xs font-medium text-gray-600">entries</span>
                            </div>

                            <!-- Search Box -->
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                                    </svg>
                                </div>
                                <input type="text" id="search" name="search"
                                    value="{{ old('search', $search) }}"
                                    class="w-80 pl-10 pr-4 py-2 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                    placeholder="Search Kegiatan..." />
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2">
                                <button type="button" onclick="openPreviewReport()"
                                    class="bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd"
                                            d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm.5 5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm0 5c.47 0 .917-.092 1.326-.26l1.967 1.967a1 1 0 0 0 1.414-1.414l-1.817-1.818A3.5 3.5 0 1 0 11.5 17Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Preview</span>
                                </button>
                                <button type="button" onclick="printBp()"
                                    class="bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-700 hover:to-rose-600 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                                    </svg>
                                    <span>Print BP</span>
                                </button>
                                <button type="button" onclick="exportToExcel()"
                                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd"
                                            d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Zm2-2a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm0 3a1 1 0 1 0 0 2h3a1 1 0 1 0 0-2h-3Zm-6 4a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-6Zm8 1v1h-2v-1h2Zm0 3h-2v1h2v-1Zm-4-3v1H9v-1h2Zm0 3H9v1h2v-1Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>Export</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div id="ajax-data" data-url="{{ route('report.rekap-upah-mingguan.index') }}"></div>

            <!-- Table Container -->
            <div class="overflow-x-auto" id="tables">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-gray-100 to-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                No.</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                LKH No.</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Kegiatan</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Plot</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Total Biaya (Rp)</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                @if (session()->has('tenagakerjarum'))
                                    {{ session('tenagakerjarum') == 'Harian' ? 'TKH' : 'TKB' }}
                                @else
                                    TK
                                @endif
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if (session('tenagakerjarum') != null && $startDate && $endDate)
                            @forelse ($rum as $item)
                                <tr class="hover:bg-indigo-50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->no }}.</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->lkhno }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->activityname }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 max-w-96">{{ $item->plots }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->lkhdate }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->totalupahall }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $item->totalworkers ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex items-center justify-center">
                                            <button onclick="showList('{{ $item->lkhno }}')"
                                                class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all duration-200"
                                                title="View Details">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                    <path stroke-width="2"
                                                        d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <svg class="w-20 h-20 text-gray-300 mb-4" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Rekap Upah
                                                Minggu
                                                Ini</h3>
                                            <p class="text-gray-500 text-sm mb-4">Belum ada rekapan upah mingguan yang
                                                sesuai dengan filter Anda</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-gray-600 font-medium">Belum Ada Data</p>
                                            <p class="text-sm text-gray-500 mt-1">Silakan pilih Jenis Tenaga Kerja dan
                                                Range Tanggal</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 rounded-b-lg" id="pagination-links">
                @if ($rum->hasPages())
                    {{ $rum->appends(['perPage' => $rum->perPage()])->links() }}
                @else
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing <span class="font-semibold text-gray-900">{{ $rum->count() }}</span> of
                            <span class="font-semibold text-gray-900">{{ $rum->total() }}</span> results
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 backdrop-blur-sm p-4 invisible opacity-0 transition-all duration-300">
        <div
            class="bg-white w-11/12 max-w-7xl max-h-[90vh] flex flex-col rounded-2xl shadow-2xl transform scale-95 transition-transform duration-300">
            <!-- Modal Header -->
            <div
                class="flex items-center justify-between p-6 border-b bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-2xl flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Detail Daftar List</h2>
                </div>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-all duration-200">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="overflow-auto flex-1 p-6">
                <div class="rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-100 to-gray-200 sticky top-0">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    No.</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Kegiatan</th>
                                @if (session('tenagakerjarum') == 'Harian')
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Tenaga Kerja</th>
                                @endif
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Plot</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Luasan (Ha)</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Status Tanam</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                    Hasil (Ha)</th>
                                @if (session('tenagakerjarum') == 'Harian')
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Cost/Unit</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Biaya (Rp)</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="listTableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="10" class="text-center py-8">
                                    <div
                                        class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-indigo-600">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

        .overflow-auto::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>

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

        function showList(lkhno) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            tableBody.innerHTML =
                '<tr><td colspan="10" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-300 border-t-indigo-600"></div></td></tr>';

            const url = `{{ route('report.rekap-upah-mingguan.show', ['lkhno' => '__lkhno__']) }}`.replace('__lkhno__',
                lkhno);

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(response => {
                    tableBody.innerHTML = '';

                    if (response.error) {
                        tableBody.innerHTML =
                            `<tr><td colspan="10" class="text-center py-8 text-red-600">${response.error}</td></tr>`;
                        return;
                    }

                    const data = response.data || response;

                    if (!data || data.length === 0) {
                        tableBody.innerHTML =
                            '<tr><td colspan="10" class="text-center py-8 text-gray-500">Tidak ada data</td></tr>';
                        return;
                    }

                    let totalBiaya = 0;

                    data.forEach(item => {
                        @if (session('tenagakerjarum') == 'Harian')
                            const biaya = item.total || '0';
                            const biayaNumeric = parseFloat(biaya.toString().replace(/[^0-9,-]/g, '').replace(
                                ',', '.')) || 0;
                            totalBiaya += biayaNumeric;
                        @endif

                        const row = `
                            <tr class="hover:bg-indigo-50 transition-colors duration-150">
                                <td class="px-4 py-3 text-sm text-gray-900">${item.no}.</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${item.activityname || ''}</td>
                                @if (session('tenagakerjarum') == 'Harian')
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.namatenagakerja}</td>
                                @endif
                                <td class="px-4 py-3 text-sm text-gray-700">${item.plot || ''}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${item.luasrkh || ''}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${item.batchdate || ''}/${item.lifecyclestatus}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">${item.luashasil || ''}</td>
                                @if (session('tenagakerjarum') == 'Harian')
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.upah || '-'}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">${item.total || '-'}</td>
                                @endif
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });

                    @if (session('tenagakerjarum') == 'Harian')
                        const totalFormatted = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(totalBiaya);

                        const totalRow = `
                            <tr class="font-bold bg-indigo-50">
                                <td colspan="8" class="px-4 py-3 text-right border-t-2 border-indigo-400 text-gray-900">Total Biaya:</td>
                                <td class="px-4 py-3 border-t-2 border-indigo-400 text-indigo-700">${totalFormatted}</td>
                            </tr>
                        `;
                        tableBody.innerHTML += totalRow;
                    @endif

                    modal.classList.remove('invisible');
                    modal.classList.add('visible');
                    setTimeout(() => {
                        modal.style.opacity = "1";
                        modal.querySelector('.bg-white').style.transform = "scale(1)";
                    }, 10);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    tableBody.innerHTML =
                        `<tr><td colspan="10" class="text-center py-8 text-red-600">Gagal memuat data: ${error.message}</td></tr>`;
                });
        }

        function closeModal() {
            const modal = document.getElementById('listModal');
            modal.style.opacity = "0";
            modal.querySelector('.bg-white').style.transform = "scale(0.95)";
            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
        }

        function openPreviewReport() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const tenagaKerja = document.getElementById('tenagakerjarum').value;

            if (!tenagaKerja) {
                alert('Harap pilih tenaga kerja terlebih dahulu');
                return;
            }

            if (!startDate || !endDate) {
                alert('Harap pilih range tanggal terlebih dahulu');
                return;
            }

            const baseUrl = "{{ route('report.rekap-upah-mingguan.preview') }}";
            const url = `${baseUrl}?start_date=${startDate}&end_date=${endDate}`;

            window.open(url, '_blank');
        }

        function printBp() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const tenagaKerja = document.getElementById('tenagakerjarum').value;

            if (!tenagaKerja) {
                alert('Harap pilih tenaga kerja terlebih dahulu');
                return;
            }

            if (!startDate || !endDate) {
                alert('Harap pilih range tanggal terlebih dahulu');
                return;
            }

            const baseUrl = "{{ route('report.rekap-upah-mingguan.print-bp') }}";
            const url = `${baseUrl}?start_date=${startDate}&end_date=${endDate}&tenagakerja=${tenagaKerja}`;

            window.open(url, '_blank');
        }

        function exportToExcel() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
                alert('Harap pilih range tanggal terlebih dahulu');
                return;
            }

            const baseUrl = "{{ route('report.rekap-upah-mingguan.export-excel') }}";
            const url = `${baseUrl}?start_date=${startDate}&end_date=${endDate}`;

            window.location.href = url;
        }
    </script>
</x-layout>
