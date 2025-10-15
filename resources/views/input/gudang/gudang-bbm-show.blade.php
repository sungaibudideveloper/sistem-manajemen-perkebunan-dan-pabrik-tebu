{{-- resources/views/input/gudang/gudang-bbm-show.blade.php --}}
<x-layout>
    <x-slot:title>Konfirmasi Pengeluaran BBM - {{ $lkhData->lkhno }}</x-slot:title>
    <x-slot:navbar>Konfirmasi Pengeluaran BBM</x-slot:navbar>
    <x-slot:nav>Konfirmasi BBM</x-slot:nav>

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

    {{-- Action Buttons - No Print --}}
    <div class="no-print mb-4 flex justify-between items-center">
        <a href="{{ route('input.gudang-bbm.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm">
            ← Kembali
        </a>
        
        @if($canConfirm)
        <div class="space-x-2">
            <button onclick="confirmBbm('{{ $orderNumber }}')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                Konfirmasi BBM
            </button>
            <button onclick="window.print()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                Print
            </button>
        </div>
        @else
        <div class="space-x-2">
            <span class="text-green-600 text-sm font-medium">✓ Sudah Dikonfirmasi</span>
            <button onclick="window.print()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                Print
            </button>
        </div>
        @endif
    </div>

    <div class="print-area">
        {{-- Header --}}
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-xl font-bold uppercase tracking-wide">KONFIRMASI PENGELUARAN BBM</h1>
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
                        <th class="border border-black px-2 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vehicleData as $vehicle)
                    <tr>
                        <td class="border border-black px-2 py-2 font-semibold">{{ $vehicle->nokendaraan }}</td>
                        <td class="border border-black px-2 py-2">{{ $vehicle->jenis ?? '-' }}</td>
                        <td class="border border-black px-2 py-2">{{ $vehicle->operator_nama ?? '-' }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $vehicle->plot }}</td>
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
                        <td class="border border-black px-2 py-2 text-center">
                            @if($vehicle->gudangconfirm)
                                <span class="text-xs bg-green-100 text-green-700 px-1 py-1 rounded">✓ Confirmed</span>
                            @else
                                <span class="text-xs bg-yellow-100 text-yellow-700 px-1 py-1 rounded">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="9" class="border border-black px-2 py-2 text-right">TOTAL SOLAR:</td>
                        <td class="border border-black px-2 py-2 text-center">
                            {{ number_format($totalSolar, 2) }} L
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Confirmation Status --}}
        <div class="mb-6 p-4 border rounded-lg {{ $canConfirm ? 'bg-yellow-50 border-yellow-300' : 'bg-green-50 border-green-300' }}">
            <h3 class="text-sm font-bold uppercase mb-2">Status Konfirmasi</h3>
            @if($canConfirm)
                <p class="text-sm text-yellow-700">
                    <strong>Status:</strong> Menunggu konfirmasi dari gudang BBM
                </p>
            @else
                <p class="text-sm text-green-700">
                    <strong>Status:</strong> ✓ Sudah dikonfirmasi oleh gudang BBM
                </p>
            @endif
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
                <div class="font-semibold mb-16">Dikonfirmasi Oleh:</div>
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
        async function confirmBbm(ordernumber) {
            if (!confirm('Apakah Anda yakin ingin mengkonfirmasi pengeluaran BBM untuk Order #' + ordernumber + '?')) {
                return;
            }

            try {
                const response = await fetch(`{{ url('input/gudang/bbm') }}/${ordernumber}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        ordernumber: ordernumber
                    })
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal konfirmasi: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat konfirmasi BBM');
            }
        }
    </script>
</x-layout>