<x-layout>
    <x-slot:title>Detail Gudang</x-slot:title>

    <div class="p-8">
        <h2 class="text-2xl font-bold mb-6">Detail Data</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <strong>No RKH:</strong> {{ $header->rkhno }}
            </div>
            <div>
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($header->createdat)->format('d/m/Y') }}
            </div>
            <div>
                <strong>Nama Mandor:</strong> {{ $header->mandorid }}
            </div>
            <div>
                <strong>Nama Kegiatan:</strong> {{ $header->herbisidagroupname ?? '-' }}
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-2">Blok dan Plot</h3>
            <table class="min-w-full bg-white text-sm text-center mb-4">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b bg-gray-100">Blok</th>
                        <th class="py-2 px-4 border-b bg-gray-100">Plot</th>
                        <th class="py-2 px-4 border-b bg-gray-100">Luas</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalLuas = 0; @endphp
                    @foreach($details1 as $d)
                        <tr>
                            <td class="py-2 px-4">{{ $d->blok }}</td>
                            <td class="py-2 px-4">{{ $d->plot }}</td>
                            <td class="py-2 px-4">{{ $d->luasarea }} HA</td>
                        </tr>
                        @php $totalLuas += floatval($d->luasarea); @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="py-2 px-4"><strong>Total Luas</strong></td>
                        <td class="py-2 px-4"><strong>{{ $totalLuas }} HA</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mb-8">
            <h3 class="text-lg font-semibold mb-2">Pupuk yang perlu disiapkan</h3>
            <table class="min-w-full bg-white text-sm text-center">
                <thead>
                    <tr>
                        <th colspan="2" class="py-2 px-4 border-b bg-gray-100">Paket 2</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details2 as $d)
                        <tr>
                            <td class="py-2 px-4">{{ $d->itemcode }}</td>
                            <td class="py-2 px-4">{{ $d->qty }} {{ $d->unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            <a href="{{ url()->previous() }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">
                ← Kembali
            </a>
        </div>
    </div>
</x-layout>