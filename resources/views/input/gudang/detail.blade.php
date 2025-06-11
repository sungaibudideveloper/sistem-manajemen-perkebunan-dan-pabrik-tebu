<x-layout>
    <x-slot:title>Detail Gudang</x-slot:title>

    <div class="p-8 max-w-screen-xl mx-auto">
        <!-- Header Section -->
        
        <!-- Data Grid Section -->
        <div class="flex flex-col md:flex-row md:gap-4 gap-2 mb-8">
    <!-- Box 1: No RKH & Mandor -->
    <div class="w-full md:w-1/3 p-3 bg-white shadow rounded-lg">
        <strong class="block text-gray-600 text-sm">No RKH:</strong>
        <p class="text-sm text-gray-800">{{ $details[0]->rkhno }}</p>
        <strong class="block text-gray-600 text-sm mt-2">Nama Mandor:</strong>
        <p class="text-sm text-gray-800">{{ $details[0]->mandorid }}</p>
        <strong class="block text-gray-600 text-sm">Tanggal:</strong>
        <p class="text-sm text-gray-800">{{ \Carbon\Carbon::parse($details[0]->createdat)->format('d/m/Y') }}</p>
    </div>

    <!-- Box 3: Table -->
    <div class="w-full md:w-1/3 p-3 bg-white shadow rounded-lg overflow-x-auto">
        <table class="w-full text-xs text-left rounded-lg">
            <thead class="bg-gray-200 text-gray-700">
                <tr>
                    <th class="py-2 px-3 border-b">Blok</th>
                    <th class="py-2 px-3 border-b">Plot</th>
                    <th class="py-2 px-3 border-b">Luas (HA)</th>
                    <th class="py-2 px-3 border-b">Activity</th>
                </tr>
            </thead>
            <tbody class="text-gray-600">
                @php $totalLuas = 0; @endphp
                @foreach($details as $d)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-2 px-3">{{ $d->blok }}</td>
                        <td class="py-2 px-3">{{ $d->plot }}</td>
                        <td class="py-2 px-3">{{ $d->luasarea }} HA</td>
                        <td class="py-2 px-3">{{$d->activitycode}} {{ $d->herbisidagroupname }} </td>
                    </tr>
                    @php $totalLuas += floatval($d->luasarea); @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-200 text-gray-800">
                    <td colspan="2" class="py-2 px-3 font-semibold">Total Luas</td>
                    <td class="py-2 px-3 font-semibold">{{ $totalLuas }} HA</td>
                    <td class="py-2 px-3 font-semibold"></td>
                </tr>
            </tfoot>
        </table>
            
            <div class="mt-8">
            <a href="{{ url()->previous() }}" class="inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 text-lg font-semibold py-2 px-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out">
                Approve
            </a>
            </div>
    </div>
</div>



<h3 style="text-align: center;" class="text-xl font-semibold text-gray-800 mb-4">Pupuk yang Perlu Disiapkan</h3>
</div>

        <!-- Blok dan Plot Section -->
        @php $grouped=$dosage->groupby('herbisidagroupid'); @endphp
        <!-- Pupuk Section -->
        @foreach( $grouped as $group => $items )
        @php $title= collect($details)->where('herbisidagroupid',$group)->first(); @endphp
        <div class="mb-8 p-6 bg-white shadow rounded-lg">
            <table class="min-w-full bg-white text-sm text-left rounded-lg shadow-md overflow-hidden">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th colspan="2" class="py-3 px-4 border-b">{{$title->activitycode}} {{ $title->herbisidagroupname }}</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600">
                    @foreach($items as $d)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $d->itemcode }}</td>
                            <td class="py-3 px-4">{{ $d->dosageperha }} {{ $d->dosageunit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach

        <!-- Back Button -->
        <div class="mt-8">
            <a href="{{ url()->previous() }}" class="inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 text-lg font-semibold py-2 px-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out">
                ← Kembali
            </a>
        </div>
    </div>
</x-layout>
