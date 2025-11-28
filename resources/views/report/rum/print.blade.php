<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <style>
        @media print {

            html,
            body {
                height: auto !important;
                min-height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
            }

            .layout-container,
            .main-wrapper,
            main {
                display: block !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
            }
        }
    </style>

    <div class="max-w-full mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Header dengan tombol print saja -->
        <div
            class="no-print bg-gradient-to-r from-green-600 to-emerald-500 text-white p-4 flex justify-between items-center">
            <h1 class="text-xl font-bold">Preview Rekap Upah Mingguan</h1>
            <div class="flex items-center space-x-4">
                <button id="print-btn"
                    class="bg-white text-green-600 px-4 py-2 rounded font-semibold hover:bg-gray-100 transition flex items-center gap-2">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                            d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                    </svg>
                    <span>
                        Print
                    </span>
                </button>
            </div>
        </div>

        <!-- Container untuk konten yang akan dicetak -->
        <div id="print-container" class="print-container p-8 bg-white">
            <!-- Header Report -->
            <div class="text-center mb-6">
                <h1 class="text-xl font-bold mb-2">Rekap Upah Mingguan</h1>
                <h2 class="text-lg">Tenaga Kerja {{ session('tenagakerjarum') == 'Harian' ? 'Harian' : 'Borongan' }}
                </h2>
                <h2 class="text-lg">Divisi {{ session('companycode') }}</h2>
                @php
                    // Backup current locale
                    $currentLocale = \Carbon\Carbon::getLocale();

                    // Set locale ke Indonesia hanya untuk proses ini
                    \Carbon\Carbon::setLocale('id');

                    $start = \Carbon\Carbon::parse($startDate);
                    $end = \Carbon\Carbon::parse($endDate);
                @endphp

                <h3 class="text-base">Periode:
                    @if ($start->format('m Y') === $end->format('m Y'))
                        {{ $start->translatedFormat('d') }} s.d {{ $end->translatedFormat('d F Y') }}
                    @else
                        {{ $start->translatedFormat('d F Y') }} s.d {{ $end->translatedFormat('d F Y') }}
                    @endif
                </h3>

                @php
                    // Restore original locale
                    \Carbon\Carbon::setLocale($currentLocale);
                @endphp
            </div>

            <div class="mb-2">
                <span class="font-medium">
                    No. Voucher:
                </span>
            </div>

            <!-- Tabel Data dengan wrapper untuk scroll horizontal di screen -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 mb-4" style="min-width: 100%;">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-2 py-2" style="width: 4%;">No.</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 16%;">Kegiatan</th>
                            @if (session('tenagakerjarum') == 'Harian')
                                <th class="border border-gray-300 px-2 py-2" style="width: 12%;">Tenaga Kerja</th>
                            @endif
                            <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Plot</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 8%;">Luas (Ha)</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Status Tanam</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 8%;">Hasil (Ha)</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 10%;">Cost/Unit</th>
                            <th class="border border-gray-300 px-2 py-2" style="width: 17%;">Biaya (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Group data by activityname
                            $groupedData = [];
                            $totalKeseluruhan = 0;

                            if (isset($data) && count($data) > 0) {
                                foreach ($data as $item) {
                                    $activityName = $item->activityname;
                                    if (!isset($groupedData[$activityName])) {
                                        $groupedData[$activityName] = [];
                                    }
                                    $groupedData[$activityName][] = $item;
                                }
                            }

                            $rowNumber = 1;
                        @endphp

                        @if (count($groupedData) > 0)
                            @foreach ($groupedData as $activityName => $items)
                                @php
                                    $subtotal = 0;
                                    $itemCount = count($items);
                                @endphp

                                @foreach ($items as $index => $item)
                                    <tr>
                                        <td class="border border-gray-300 px-2 py-2 text-center">{{ $rowNumber++ }}.
                                        </td>

                                        <!-- Merge kolom kegiatan dengan rowspan -->
                                        @if ($index === 0)
                                            <td class="border border-gray-300 px-2 py-2 font-semibold bg-gray-50"
                                                rowspan="{{ $itemCount }}">
                                                {{ $item->activityname }}
                                            </td>
                                        @endif

                                        @if (session('tenagakerjarum') == 'Harian')
                                            <td class="border border-gray-300 px-2 py-2">{{ $item->namatenagakerja }}
                                            </td>
                                        @endif
                                        <td class="border border-gray-300 px-2 py-2">{{ $item->plot }}</td>
                                        <td class="border border-gray-300 px-2 py-2 text-right">
                                            {{ number_format($item->luasan, 2) }}</td>
                                        <td class="border border-gray-300 px-2 py-2">
                                            {{ $item->batchdate }}/{{ $item->lifecyclestatus }}</td>
                                        <td class="border border-gray-300 px-2 py-2 text-right">
                                            {{ number_format($item->hasil, 2) }}</td>
                                        <td class="border border-gray-300 px-2 py-2 text-right">{{ $item->upah }}
                                        </td>
                                        <td class="border border-gray-300 px-2 py-2 text-right">{{ $item->total }}
                                        </td>
                                    </tr>
                                    @php
                                        // Konversi nilai total ke numeric
                                        // Hapus semua karakter kecuali angka, koma, dan titik
                                        $cleanTotal = preg_replace('/[^\d,.]/', '', $item->total);

                                        // Jika format Indonesia (1.000.000,00), ubah ke format standar
                                        if (strpos($cleanTotal, ',') !== false && strpos($cleanTotal, '.') !== false) {
                                            // Format: 1.000.000,00 -> hapus titik, ganti koma dengan titik
                                            $cleanTotal = str_replace('.', '', $cleanTotal);
                                            $cleanTotal = str_replace(',', '.', $cleanTotal);
                                        } elseif (strpos($cleanTotal, ',') !== false) {
                                            // Format: 1000000,00 -> ganti koma dengan titik
                                            $cleanTotal = str_replace(',', '.', $cleanTotal);
                                        }

                                        $totalValue = floatval($cleanTotal);
                                        $subtotal += $totalValue;
                                    @endphp
                                @endforeach

                                <!-- Row Subtotal untuk setiap kegiatan -->
                                <tr class="bg-yellow-50 font-bold">
                                    <td class="border border-gray-300 px-2 py-2 text-center"
                                        colspan="{{ session('tenagakerjarum') == 'Harian' ? '8' : '7' }}">
                                        Subtotal {{ $activityName }}
                                    </td>
                                    <td class="border border-gray-300 px-2 py-2 text-right">
                                        Rp {{ number_format($subtotal, 2, ',', '.') }}
                                    </td>
                                </tr>

                                @php
                                    $totalKeseluruhan += $subtotal;
                                @endphp
                            @endforeach

                            <!-- Row Total Keseluruhan -->
                            <tr class="bg-green-100 font-bold text-base">
                                <td class="border border-gray-300 px-2 py-2 text-center"
                                    colspan="{{ session('tenagakerjarum') == 'Harian' ? '8' : '7' }}">
                                    TOTAL KESELURUHAN
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-right">
                                    Rp {{ number_format($totalKeseluruhan, 2, ',', '.') }}
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="{{ session('tenagakerjarum') == 'Harian' ? '9' : '8' }}"
                                    class="border border-gray-300 px-2 py-2 text-center">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Footer dengan tanggal cetak -->
            <div class="text-right text-sm text-gray-500">
                Dicetak pada: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi untuk mencetak
            document.getElementById('print-btn').addEventListener('click', function() {
                window.print();
            });
        });
    </script>

</x-layout>
