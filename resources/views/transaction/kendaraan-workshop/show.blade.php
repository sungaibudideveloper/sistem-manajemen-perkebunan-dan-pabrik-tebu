{{-- resources/views/transaction/kendaraan-workshop/show.blade.php --}}
<x-layout>
    <x-slot:title>Order Pengeluaran BBM - {{ $lkhData->lkhno }}</x-slot:title>
    <x-slot:navbar>Order Pengeluaran BBM</x-slot:navbar>
    <x-slot:nav>Print Order</x-slot:nav>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        
        @media screen {
            .print-area {
                max-width: 210mm;
                margin: 0 auto;
                background: white;
                padding: 20mm;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
        }
    </style>

    <div class="print-area">
        {{-- Header --}}
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-xl font-bold uppercase tracking-wide">ORDER PENGELUARAN BBM</h1>
            <p class="text-sm mt-1">{{ config('app.name') }}</p>
            <div class="flex justify-between items-center mt-4">
                <div class="text-left">
                    <div class="text-sm">No. Order:</div>
                    <div class="text-lg font-bold">{{ $orderNumber }}</div>
                </div>
                <div class="text-right">
                    <div class="text-sm">{{ $printDate }}</div>
                </div>
            </div>
        </div>

        {{-- LKH Information --}}
<div class="mb-6">
    <h2 class="text-sm font-bold uppercase border-b border-gray-400 pb-1 mb-3">Informasi Pekerjaan</h2>
    <div class="grid grid-cols-2 gap-6 text-sm">
        <div>
            <table class="w-full">
                <tr>
                    <td class="py-1 w-24">LKH No</td>
                    <td class="py-1">: {{ $lkhData->lkhno }}</td>
                </tr>
                <tr>
                    <td class="py-1">Tanggal</td>
                    <td class="py-1">: {{ \Carbon\Carbon::parse($lkhData->lkhdate)->translatedFormat('d F Y') }}</td>
                </tr>
            </table>
        </div>
        <div>
            <table class="w-full">
                <tr>
                    <td class="py-1 w-32">Activity</td>
                    <td class="py-1">: {{ $lkhData->activitycode }} - {{ $lkhData->activityname }}</td>
                </tr>
                <tr>
                    <td class="py-1">Mandor</td>
                    <td class="py-1">: {{ $lkhData->mandor_nama ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>


        {{-- Vehicle & BBM Details --}}
        <div class="mb-6">
            <h2 class="text-sm font-bold uppercase border-b border-gray-400 pb-1 mb-3">Detail Kendaraan & BBM</h2>
            <table class="w-full text-sm border-collapse border border-black">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-2 text-left">Kendaraan</th>
                        <th class="border border-black px-2 py-2 text-left">Jenis</th>
                        <th class="border border-black px-2 py-2 text-left">Operator</th>
                        <th class="border border-black px-2 py-2 text-center">Plot</th>
                        <th class="border border-black px-2 py-2 text-center">Jam Kerja</th>
                        <th class="border border-black px-2 py-2 text-center">HM Start</th>
                        <th class="border border-black px-2 py-2 text-center">HM End</th>
                        <th class="border border-black px-2 py-2 text-center">Selisih</th>
                        <th class="border border-black px-2 py-2 text-center">Solar (L)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicleData as $vehicle)
                    <tr>
                        <td class="border border-black px-2 py-2 font-semibold">{{ $vehicle->nokendaraan }}</td>
                        <td class="border border-black px-2 py-2">{{ $vehicle->jenis ?? '-' }}</td>
                        <td class="border border-black px-2 py-2">{{ $vehicle->operator_nama ?? '-' }}</td>
                        <td class="border border-black px-2 py-2 text-center">
                            <span class="text-xs">{{ $vehicle->plots ?? '-' }}</span>
                        </td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ substr($vehicle->jammulai, 0, 5) }} - {{ substr($vehicle->jamselesai, 0, 5) }}
                        </td>
                        <td class="border border-black px-2 py-2 text-center">{{ number_format($vehicle->hourmeterstart ?? 0, 1) }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ number_format($vehicle->hourmeterend ?? 0, 1) }}</td>
                        <td class="border border-black px-2 py-2 text-center font-semibold">
                            {{ number_format(($vehicle->hourmeterend ?? 0) - ($vehicle->hourmeterstart ?? 0), 1) }}
                        </td>
                        <td class="border border-black px-2 py-2 text-center font-bold">
                            {{ number_format($vehicle->solar ?? 0, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="8" class="border border-black px-2 py-2 text-right">TOTAL SOLAR:</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ number_format($totalSolar, 2) }} L
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>



        {{-- Signature Section --}}
        <div class="grid grid-cols-3 gap-8 text-center text-sm">
            <div>
                <div class="font-semibold mb-16">Diminta Oleh:</div>
                <div class="border-t border-black pt-2">
                    <div class="font-semibold">{{ $lkhData->mandor_nama ?? 'N/A' }}</div>
                    <div class="text-xs">Mandor</div>
                </div>
            </div>
            <div>
                <div class="font-semibold mb-16">Disetujui Oleh:</div>
                <div class="border-t border-black pt-2">
                    <div class="font-semibold">_________________</div>
                    <div class="text-xs">Admin Kendaraan</div>
                </div>
            </div>
            <div>
                <div class="font-semibold mb-16">Diserahkan Oleh:</div>
                <div class="border-t border-black pt-2">
                    <div class="font-semibold">{{ auth()->user()->name ?? 'Admin BBM' }}</div>
                    <div class="text-xs">Admin BBM</div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs mt-6 border-t border-gray-300 pt-2">
            Dicetak pada: {{ $printDate }} | Order No: {{ $orderNumber }}
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</x-layout>