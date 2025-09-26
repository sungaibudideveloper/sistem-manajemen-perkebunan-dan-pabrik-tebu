<x-layout>

    @if(strtoupper($details[0]->flagstatus) == 'ACTIVE')
        <x-slot:title>Penyiapan RKH Herbisida</x-slot:title>
    @elseif(strtoupper($details[0]->flagstatus) == 'RECEIVED_BY_MANDOR')
        <x-slot:title>RKH Herbisida Diterima</x-slot:title>
    @else
        <x-slot:title>RKH Herbisida Selesai</x-slot:title>
    @endif
    
    <div class="p-4 max-w-screen-xl mx-auto">
        <!-- Status Badge -->
        <div class="flex justify-center mb-3">
            @if(strtoupper($details[0]->flagstatus) == 'ACTIVE')
                <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded shadow text-sm">
                    <i class="bi bi-clock me-1"></i>Apabila Sudah Diperiksa, Klik Button Penyerahan Di Bawah
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'DISPATCHED')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm">
                    <i class="bi bi-check-circle me-1"></i>Barang Sudah Diserahan Kepada Mandor.
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'RECEIVED_BY_MANDOR')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm">
                    <i class="bi bi-check-circle me-1"></i>Barang Sudah Diterima. Untuk Retur, Ajukan Dokumen Retur.
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'RETURNED_BY_MANDOR')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm">
                    <i class="bi bi-check-circle me-1"></i>Barang Sudah Diretur.
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'RETURN_RECEIVED')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm">
                    <i class="bi bi-check-circle me-1"></i>Barang Retur Sudah Diterima.
                </div>
            @else
                <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded shadow text-sm">
                    <i class="bi bi-check-all me-1"></i>Dokumen RKH Herbisida Telah Diselesaikan
                </div>
            @endif
        </div>
    
        <!-- Header Section - Compact -->
        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <!-- Box 1: No RKH & Mandor -->
            <div class="w-full md:w-1/3 p-2 bg-white shadow rounded">
                <div class="text-gray-600 text-xs font-medium">No Dokumen:</div>
                <div class="text-xs bg-green-100 px-1 mb-1">RKH <b>{{ $details[0]->rkhno }}</b></div>
                <div class="text-xs bg-blue-100 px-1 mb-2">USE <b>{{ $details[0]->nouse }}</b></div>
                <div class="text-gray-600 text-xs font-medium">Nama Mandor:</div>
                <div class="text-xs mb-1">{{ $details[0]->name }}</div>
                <div class="text-gray-600 text-xs font-medium">Tanggal:</div>
                <div class="text-xs">{{ \Carbon\Carbon::parse($details[0]->createdat)->format('d/m/Y') }}</div>
            </div>
    
            <!-- Box 2: Table of Blok, Plot, Luas, Activity -->
            <div class="w-full md:w-2/3 p-2 bg-white shadow rounded overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="bg-gray-200 text-gray-700">
                        <tr>
                            <th class="py-1 px-2 border-b">LKH</th>
                            <th class="py-1 px-2 border-b">Blok</th>
                            <th class="py-1 px-2 border-b">Plot</th>
                            <th class="py-1 px-2 border-b">Luas (HA)</th>
                            <th class="py-1 px-2 border-b">Activity</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600">
                        @php $totalLuas = 0; $plots = $details->unique('plot'); @endphp
                        @foreach($plots as $d)  
                        
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-1 px-2">{{ $d->lkhno }}</td>
                                <td class="py-1 px-2">{{ $d->blok }}</td>
                                <td class="py-1 px-2">{{ $d->plot }}</td>
                                <td class="py-1 px-2 text-right">{{ $d->luasarea }} HA</td>
                                <td class="py-1 px-2 bg-green-100">{{ $d->activitycode }} {{ $d->herbisidagroupname }}</td>
                            </tr>
                            @php $totalLuas += floatval($d->luasarea); @endphp

                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-200 text-gray-800">
                            <td class="py-1 px-2"></td>
                            <td colspan="2" class="py-1 px-2 font-semibold text-xs">Total Luas</td>
                            <td class="py-1 px-2 font-semibold text-right text-xs">{{ $totalLuas }} HA</td>
                            <td class="py-1 px-2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    
        <!-- Form -->
        <form action="{{ route('input.gudang.submit', ['rkhno' => $details[0]->rkhno]) }}" method="POST">
            @csrf
            
            @foreach($details->groupby('herbisidagroupid') as $groupId => $items)
                @php $title = $items->first(); //dd($groupId, $items);
                @endphp
    
                <div class="mb-4 p-3 bg-white shadow rounded">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-200 text-gray-700">
                            <tr>
                                <th class="py-2 px-2 border-b bg-green-100 text-left">
                                    {{ $title->activitycode }} {{ $title->herbisidagroupname }} {{ $title->lkhno }}
                                </th>
                                <th class="py-2 px-2 border-b text-center">Dosage (HA)</th>
                                <th class="py-2 px-2 border-b text-center">Luas (HA)</th>
                                <th class="py-2 px-2 border-b text-center">Total Qty</th>
                                <th class="py-2 px-2 border-b text-center">Qty Retur</th>
                                <th class="py-2 px-2 border-b text-center">Nomor Retur</th>
                            </tr>
                        </thead>
    
                        <tbody class="text-gray-600">
                            @foreach( $details->where('herbisidagroupid', $groupId) as $d )
                            @php
                                //$matched = collect($lst)->where('itemcode', $d->itemcode)->where('herbisidagroupid', $d->herbisidagroupid)->first();
                                $plot = $plots->where('herbisidagroupid',$d->herbisidagroupid)->first();

                            @endphp
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-2 px-2">
                                    <select @if(strtoupper($details[0]->flagstatus) == 'RECEIVED_BY_MANDOR') readonly style="pointer-events: none;" @endif
                                    name="itemcode[{{ $groupId }}][{{ $loop->iteration }}]" class="item-select w-full border-none bg-yellow-100 text-xs">
                                        @foreach($itemlist as $item) 
                                        @php 
                                        //$currentluas = $luas * $item->dosageperha; 
                                        @endphp
                                        <option value="{{ $item->itemcode }}" {{ $item->itemcode == $d->itemcode && $item->herbisidagroupid == $d->herbisidagroupid ? 'selected' : '' }}
                                        data-dosage="{{$item->dosageperha}}" >
                                          Herbisida {{$item->herbisidagroupid}} - {{ $item->itemcode }} - {{ $item->itemcode == $d->itemcode ? ($item->itemname ?? '[Nama Item]') : $item->itemname }} - {{$item->dosageperha}} ({{$item->measure}})
                                        </option>
                                        @endforeach
                                    </select> 
                                    <input type="hidden" name="dosage[{{ $groupId }}][{{ $loop->iteration }}]" class="selected-dosage" value="{{ $d->dosageperha }}">
                                    <input type="hidden" name="unit[{{ $groupId }}][{{ $loop->iteration }}]" class="selected-dosage" value="{{ $d->dosageunit }}">
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <span class="labeldosage">{{ $d->dosageperha }} {{ $d->dosageunit }}</span>
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <span class="luas">{{$plot->luasarea}}</span>
                                </td>
                                <td class="py-2 px-2 text-center">
                                    <span class="labelqty">{{ $d->dosageperha * $plot->luasarea ?? '-' }}</span>
                                </td>
                                <td class="py-2 px-2 text-center">
                                    {{ $d->qtyretur }}
                                </td>
                                <td class="py-2 px-2 text-center">
                                    @if(empty($d->noretur) && strtoupper($details[0]->flagstatus) != 'ACTIVE') 
                                    <a href="{{ route('input.gudang.retur', ['retur' => $d->qtyretur, 'itemcode' => $d->itemcode, 'rkhno' => $details[0]->rkhno, 'herbisidagroupid' => $d->herbisidagroupid] ) }}" 
                                    class="inline-block bg-yellow-100 text-gray-800 hover:bg-blue-600 hover:text-white text-xs py-1 px-2 rounded shadow transition"
                                    onclick="return confirm('Proses Retur Barang ini ?')">
                                    Retur ?
                                    </a>
                                    @else
                                     {{ $d->noretur ?? '-' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
    
            <!-- Submit Button -->
            @if(strtoupper($details[0]->flagstatus) == 'ACTIVE')
            <div class="flex justify-center mt-4">
                <button @if($details->whereNotNull('nouse')->count()<1 == false) @endif 
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded shadow transition"
                >
                    Penyerahan
                </button>
            </div>
            @endif
        </form>
        
        <!-- Kembali Button - Moved inside container with closer spacing -->
        <div class="flex justify-center mt-3">
            <a href="{{ url()->previous() }}" 
               class="inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 font-semibold py-2 px-4 rounded shadow transition">
                ← Kembali
            </a>
        </div>
        
    </div>
    
    </x-layout>
    
    <script>
    $(document).ready(function() {
        $('.item-select').each(function() {
            const selected = $(this).find('option:selected');
            const qty = selected.data('qty');
            const row = $(this).closest('tr');
            row.find('.labelqty').text(qty);
        });
    });
    
    $('.item-select').on('change', function () {
        const selected = $(this).find('option:selected');
        const dosage = selected.data('dosage');
        const qty = selected.data('qty');
    
        const row = $(this).closest('tr');
        row.find('.selected-dosage').val(dosage);
        row.find('.selected-qty').val(qty);
        
        row.find('.labeldosage').text(dosage);
        row.find('.labelqty').text(qty);
    });
    </script>