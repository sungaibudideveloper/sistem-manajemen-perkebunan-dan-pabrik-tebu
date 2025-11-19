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
                    <h2 class="text-xl font-semibold text-gray-800">{{ $title }}</h2>
                    <p class="mt-1 text-sm text-gray-600">Generate dan preview laporan data trash</p>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Back Button -->
                    <a href="{{ route('pabrik.trash.index') }}"
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m0 7h18"></path>
                        </svg>
                        Kembali
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

                {{ csrf_field() }}
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
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="start_date"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="end_date"
                            class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
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
                        <button id="btnPreview" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex items-center gap-2 transition-colors duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Submit 
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Results Section -->
        <div id="reportResults" class="px-6 pb-6" style="display: none;">
        </div>
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
        // Set default dates
        $(document).ready(function() {
            const today = new Date();
            const yesterday = new Date(today);
            yesterday.setDate(today.getDate() - 1);

            $('#end_date').val(today.toISOString().split('T')[0]);
            $('#start_date').val(yesterday.toISOString().split('T')[0]);

            // Handle report type change
            $('#report_type').change(function() {
                const reportType = $(this).val();
                const companySelect = $('#company');

                // Clear existing options except the first one
                companySelect.find('option:not(:first)').remove();

                if (reportType === 'harian') {
                    // For harian: only "Semua Company"
                    companySelect.append('<option value="all">Semua Company</option>');
                    companySelect.val('all');
                } else if (reportType === 'mingguan') {
                    // For mingguan: TBL and BNIL
                    companySelect.append('<option value="TBL">TBL</option>');
                    companySelect.append('<option value="BNIL">BNIL</option>');
                }
            });
        });

        // Preview button click
        $('#btnPreview').click(function() {
            if (!validateForm()) return;

            $('#reportResults').empty();
            var loader = $('.loader:first').clone();
            $('#reportResults').append(loader).show();
            loader.show();

            $.ajax({
                url: "{{ route('pabrik.trash.report.preview') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    report_type: $('#report_type').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    company: $('#company').val()
                },
                type: 'post',
                success: function(response) {
                    loader.hide();

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
                        ${response}
                    `;

                    $('#reportResults').html(previewWithButton);
                },
                error: function(request, status, err) {
                    loader.remove();
                    if (status == "timeout") {
                        $('#reportResults').html('<div class="text-center py-12"><div class="text-red-600 text-lg">Request timeout, periksa kembali jaringan atau server sedang padat.</div></div>');
                    } else {
                        $('#reportResults').html('<div class="text-center py-12"><div class="text-red-600 text-lg">Error, mohon hubungi IT untuk segera diperbaiki.</div></div>');
                    }
                }
            });
        });

        // Generate button click
        $('#btnGenerate').click(function() {
            if (!validateForm()) return;

            // Create form and submit for file download
            const form = $('<form>', {
                method: 'POST',
                action: '{{ route("pabrik.trash.report") }}',
                target: '_blank'
            });

            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: '{{ csrf_token() }}'
            }));
            form.append($('<input>', {
                type: 'hidden',
                name: 'report_type',
                value: $('#report_type').val()
            }));
            form.append($('<input>', {
                type: 'hidden',
                name: 'start_date',
                value: $('#start_date').val()
            }));
            form.append($('<input>', {
                type: 'hidden',
                name: 'end_date',
                value: $('#end_date').val()
            }));
            form.append($('<input>', {
                type: 'hidden',
                name: 'company',
                value: $('#company').val()
            }));
            form.append($('<input>', {
                type: 'hidden',
                name: 'format',
                value: $('input[name="format"]:checked').val()
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        });

        function validateForm() {
            const reportType = $('#report_type').val();
            const startDate = $('#start_date').val();
            const endDate = $('#end_date').val();
            const company = $('#company').val();

            if (!reportType || !startDate || !endDate || !company) {
                alert('Mohon lengkapi semua field yang wajib diisi');
                return false;
            }

            return true;
        }
    </script>

    <!-- Print Styles -->
    <style>
        @media print {

            /* Hide everything except preview content */
            .no-print,
            .px-6.py-4,
            .px-6.py-6,
            #reportResults .border-t.border-gray-200.pt-4 {
                display: none !important;
            }

            /* Show only preview content */
            #reportResults {
                margin: 0 !important;
                padding: 0 !important;
            }

            /* Make print layout clean */
            body {
                margin: 0;
                padding: 0;
                font-size: 10px;
            }

            table {
                font-size: 9px;
                border-collapse: collapse !important;
            }

            th,
            td {
                padding: 3px !important;
                border: 2px solid #333 !important;
            }

            .border-2 {
                border-width: 2px !important;
                border-color: #333 !important;
            }
        }

        .print-only {
            display: none;
            /* Hide by default */
        }

        @media print {
            .print-only {
                display: block !important;
                /* Show when printing */
            }
        }
    </style>
</x-layout>