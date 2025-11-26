<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>Report</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Success Alert -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <!-- Error Alert -->
    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif
    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        <!-- Header Section -->
        <div class="no-print px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Report Trash</h2>
                    <p class="mt-1 text-sm text-gray-600">Generate dan preview laporan data trash</p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Back Button -->
                    <a href="{{ route('pabrik.trash.index') }}"                         class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m0 7h18"></path>
                        </svg>
                        Tambah Data
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="no-print px-6 py-6">
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Filter Report
                </h3>

                <form id="reportForm" action="{{ route('pabrik.trash.report.preview') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4">
                        <!-- Report Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Report <span class="text-red-500">*</span>
                            </label>
                            <select id="report_type" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                <option value="">Pilih Tipe</option>
                                <option value="harian">Laporan Harian</option>
                                <option value="mingguan">Laporan Mingguan</option>
                                <option value="bulanan">Laporan Bulanan</option>
                            </select>
                        </div>

                        <!-- Date Range - For Harian & Mingguan -->
                        <div id="dateRangeFields" style="display: none;">
                            <!-- Start Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="start_date" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>

                            <!-- End Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tanggal Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="end_date" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                            </div>
                        </div>

                        <!-- Month & Year - For Bulanan -->
                        <div id="monthYearFields" style="display: none;">
                            <!-- Month -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Bulan <span class="text-red-500">*</span>
                                </label>
                                <select id="month" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option value="">Pilih Bulan</option>
                                    <option value="01">Januari</option>
                                    <option value="02">Februari</option>
                                    <option value="03">Maret</option>
                                    <option value="04">April</option>
                                    <option value="05">Mei</option>
                                    <option value="06">Juni</option>
                                    <option value="07">Juli</option>
                                    <option value="08">Agustus</option>
                                    <option value="09">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>

                            <!-- Year -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tahun <span class="text-red-500">*</span>
                                </label>
                                <select id="year" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                    <option value="">Pilih Tahun</option>
                                    <!-- Years will be populated by JavaScript -->
                                </select>
                            </div>
                        </div>

                        <!-- Company -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company <span class="text-red-500">*</span>
                            </label>
                            <select id="company" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                <option value="">Pilih Company</option>
                                <!-- Options will be populated by JavaScript based on report type -->
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-end space-x-2">
                            <button type="button" id="btnPreview" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center gap-2 transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Preview
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <div id="reportResults" class="px-6 pb-6" style="display: none;"></div>
    </div>

    <!-- Loading Template (hidden) -->
    <div class="loader" style="display: none;">
        <div class="flex items-center justify-center py-12">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <span class="text-gray-600 text-lg">Memuat data...</span>
            </div>
        </div>
    </div>

    <script>
        // Initialize page
        $(document).ready(function() {
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);

            $('#end_date').val(today.toISOString().split('T')[0]);
            $('#start_date').val(yesterday.toISOString().split('T')[0]);

            // Populate year options
            populateYearOptions();
            
            // Set default month and year to current
            const currentMonth = String(today.getMonth() + 1).padStart(2, '0');
            const currentYear = today.getFullYear();
            $('#month').val(currentMonth);
            $('#year').val(currentYear);

            // Handle report type change
            $('#report_type').change(function() {
                const reportType = $(this).val();
                handleReportTypeChange(reportType);
            });
        });

        function populateYearOptions() {
            const currentYear = new Date().getFullYear();
            const yearSelect = $('#year');
            
            for (let year = currentYear; year >= currentYear - 10; year--) {
                yearSelect.append(`<option value="${year}">${year}</option>`);
            }
        }

        function handleReportTypeChange(reportType) {
            const companySelect = $('#company');
            const dateRangeFields = $('#dateRangeFields');
            const monthYearFields = $('#monthYearFields');

            // Clear existing options except the first one
            companySelect.find('option:not(:first)').remove();

            // Hide all date fields first
            dateRangeFields.hide().find('input').removeAttr('required');
            monthYearFields.hide().find('select').removeAttr('required');

            if (reportType === 'harian') {
                // Show date range fields
                dateRangeFields.show().find('input').attr('required', 'required');
                // For harian: only "Semua Company"
                companySelect.append('<option value="all">Semua Company</option>');
                companySelect.val('all');
                
            } else if (reportType === 'mingguan') {
                // Show date range fields
                dateRangeFields.show().find('input').attr('required', 'required');
                // For mingguan: TBL and BNIL
                companySelect.append('<option value="TBL">TBL</option>');
                companySelect.append('<option value="BNIL">BNIL</option>');
                
            } else if (reportType === 'bulanan') {
                // Show month/year fields
                monthYearFields.show().find('select').attr('required', 'required');
                // For bulanan: only "Semua Company"
                companySelect.append('<option value="all">Semua Company</option>');
                companySelect.val('all');
            }
        }

        // Preview button click
        $('#btnPreview').click(function() {
            if (!validateForm()) return;

            $('#reportResults').empty();
            var loader = $('.loader:first').clone();
            $('#reportResults').append(loader).show();
            loader.show();

            // Prepare form data based on report type
            const formData = {
                _token: '{{ csrf_token() }}',
                report_type: $('#report_type').val(),
                company: $('#company').val()
            };

            // Add date fields based on report type
            if (formData.report_type === 'harian' || formData.report_type === 'mingguan') {
                formData.start_date = $('#start_date').val();
                formData.end_date = $('#end_date').val();
            } else if (formData.report_type === 'bulanan') {
                formData.month = $('#month').val();
                formData.year = $('#year').val();
            }

            // Simulate API call with mock data
            setTimeout(function() {
                loader.hide();
                
                const mockResponse = generateMockReport(formData);
                
                // Add print button to preview response
                const previewWithButton = `
                    <div class="no-print flex justify-end mb-4">
                        <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 001 1v4a1 1 0 001 1zm3-5h2a2 2 0 002-2v-3a2 2 0 00-2-2H5a2 2 0 00-2 2v3a2 2 0 002 2h2"></path>
                            </svg>
                            Print Preview
                        </button>
                    </div>
                    ${mockResponse}
                `;

                $('#reportResults').html(previewWithButton);
            }, 2000);
        });

        function validateForm() {
            const reportType = $('#report_type').val();
            const company = $('#company').val();

            if (!reportType || !company) {
                alert('Mohon lengkapi semua field yang wajib diisi');
                return false;
            }

            if (reportType === 'harian' || reportType === 'mingguan') {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                
                if (!startDate || !endDate) {
                    alert('Mohon lengkapi tanggal mulai dan selesai');
                    return false;
                }
            } else if (reportType === 'bulanan') {
                const month = $('#month').val();
                const year = $('#year').val();
                
                if (!month || !year) {
                    alert('Mohon pilih bulan dan tahun');
                    return false;
                }
            }

            return true;
        }

        // Remove mock data functions since we're using real backend now
    </script>

    <!-- Print Styles -->
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; font-size: 10px; }
            table { font-size: 9px; border-collapse: collapse !important; }
            th, td { padding: 3px !important; border: 2px solid #333 !important; }
            .border-2 { border-width: 2px !important; border-color: #333 !important; }
        }

        .print-only { display: none; }
        @media print { .print-only { display: block !important; } }

        /* Uniform table styling for perfect alignment */
        .uniform-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 0.75rem;
        }

        .uniform-header {
            height: 40px;
            padding: 4px 6px;
            border: 2px solid #9CA3AF;
            background-color: #F9FAFB;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6B7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .uniform-header-sub {
            height: 32px;
            padding: 2px 4px;
            border: 2px solid #9CA3AF;
            background-color: #F9FAFB;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6B7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .uniform-cell {
            height: 32px;
            padding: 4px 6px;
            border: 2px solid #D1D5DB;
            background-color: white;
            vertical-align: middle;
            font-size: 0.75rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .uniform-cell-group {
            height: 32px;
            padding: 4px 6px;
            border: 2px solid #D1D5DB;
            background-color: #F3F4F6;
            vertical-align: middle;
            font-size: 0.75rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</x-layout>