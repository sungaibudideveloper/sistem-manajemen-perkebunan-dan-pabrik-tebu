<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <style>
        @media print {
            @page {
                size: 21.59cm 15.24cm;
                margin: 0;
            }

            html,
            body {
                height: 15.24cm !important;
                width: 21.59cm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: hidden !important;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                width: 21.59cm !important;
                height: 15.24cm !important;
                margin: 0 !important;
                padding: 1.5cm 2cm !important;
                border: none !important;
                box-shadow: none !important;
                page-break-after: avoid !important;
            }
        }

        .print-container {
            width: 21.59cm;
            height: 15.24cm;
            /* margin: 20px auto; */
            padding: 1.5cm 2cm;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-family: 'Courier New', monospace;
            font-size: 11pt;
            position: relative;
        }
    </style>

    <div class="max-w-fit mx-auto bg-white">
        <!-- Header Print Button -->
        <div
            class="no-print bg-gradient-to-r from-green-600 to-emerald-500 text-white p-4 flex justify-between items-center rounded-t-lg">
            <div class="block">
                <h1 class="text-xl font-bold">Preview Bukti Pembayaran</h1>
                <h6 class="text-sm">*Pastikan Headers dan Footers tidak di ceklis.</h6>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" id="print-bp-btn"
                    class="bg-white text-green-600 px-4 py-2 rounded font-semibold hover:bg-gray-100 transition flex items-center gap-2">
                    <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                        height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                            d="M16.444 18H19a1 1 0 0 0 1-1v-5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h2.556M17 11V5a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v6h10ZM7 15h10v4a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1v-4Z" />
                    </svg>
                    <span>Print</span>
                </button>
            </div>
        </div>

        <!-- Print Container -->
        <div class="print-container">
            <!-- Company Code - Top Left -->
            <div class="mb-8" style="letter-spacing: 0.4em;">
                <strong>{{ session('companycode') }}</strong>
            </div>

            <!-- Main Content -->
            <div class="space-y-1 ml-[100px]">
                <div>PEMBAYARAN UPAH TENAGA KERJA {{ Str::upper($tenagakerjarum) }}</div>
                <div class="flex justify-between">
                    @php
                        // Backup current locale
                        $currentLocale = \Carbon\Carbon::getLocale();

                        // Set locale ke Indonesia hanya untuk proses ini
                        \Carbon\Carbon::setLocale('id');

                        $start = \Carbon\Carbon::parse($startDate);
                        $end = \Carbon\Carbon::parse($endDate);
                    @endphp

                    <span>Periode
                        @if ($start->format('m Y') === $end->format('m Y'))
                            {{ $start->translatedFormat('d') }} S/D {{ $end->translatedFormat('d F Y') }}
                        @else
                            {{ $start->translatedFormat('d F Y') }} S/D {{ $end->translatedFormat('d F Y') }}
                        @endif
                    </span>

                    @php
                        // Restore original locale
                        \Carbon\Carbon::setLocale($currentLocale);
                    @endphp
                    <span>Rp.{{ number_format($totalAmount, 2, '.', ',') }}</span>
                </div>
                <div>Rincian Terlampir</div>
            </div>

            <!-- Amount Box -->
            <div class="text-right mt-6 mb-12">
                <span>Rp.{{ number_format($totalAmount, 2, '.', ',') }}</span>
            </div>

            <!-- Amount in Words -->
            <div class="mb-16 ml-[100px]">
                #{{ $amountInWords }}#
            </div>

            <!-- Date - Bottom Right -->
            <div class="text-right absolute bottom-16 right-20">
                {{ \Carbon\Carbon::now()->format('d-M-Y') }}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('print-bp-btn').addEventListener('click', function() {
                window.print();
            });
        });
    </script>

</x-layout>
