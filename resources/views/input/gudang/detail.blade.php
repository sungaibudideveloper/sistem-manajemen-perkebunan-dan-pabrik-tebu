
<x-layout>
@php 
@endphp

<style>
@media print {
select.item-select { display: none !important; }
.print-label { display: inline !important; }
table th, table td {
    border: 1px solid #d1d5db; /* abu Tailwind gray-300 */
  }
}
@media screen {
.print-label { display: none; }
}

</style>

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
                <div class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded shadow text-sm no-print">
                    <i class="bi bi-clock me-1"></i>Apabila Sudah Diperiksa, Klik Button Penyerahan Di Bawah
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'DISPATCHED')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                    <i class="bi bi-check-circle me-1"></i>Barang Sudah Diserahan Kepada Mandor.
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'RECEIVED_BY_MANDOR')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                    <i class="bi bi-check-circle me-1"></i>Barang Sudah Diterima. Untuk Retur, Ajukan Dokumen Retur.
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'RETURNED_BY_MANDOR')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                    <i class="bi bi-check-circle me-1"></i>Barang Sudah Diretur.
                </div>
            @elseif(strtoupper($details[0]->flagstatus) == 'RETURN_RECEIVED')
                <div class="bg-green-100 text-green-800 px-3 py-1 rounded shadow text-sm no-print">
                    <i class="bi bi-check-circle me-1"></i>Barang Retur Sudah Diterima.
                </div>
            @else
                <div class="bg-blue-100 text-blue-800 px-3 py-1 rounded shadow text-sm no-print">
                    <i class="bi bi-check-all me-1"></i>Dokumen RKH Herbisida Telah Diselesaikan
                </div>
            @endif
        </div>
    
        <!-- Header Section - Compact -->
        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <!-- Box 1: No RKH & Mandor -->
            <div class="w-full md:w-1/3 p-2 bg-white shadow rounded">
                <div class="text-gray-600 text-xs font-medium">Company:</div>
                <div class="text-xs bg-blue-100 px-1 mb-1"><b>{{ $details[0]->companyinv }}</b></div>
                <div class="text-gray-600 text-xs font-medium">No Dokumen:</div>
                <div class="text-xs bg-green-100 px-1 mb-1">RKH No. <b>{{ $details[0]->rkhno }}</b></div>
                <div class="text-xs bg-blue-100 px-1 mb-2">USE No. <b>{{ $details[0]->nouse }}</b></div>
                <div class="text-gray-600 text-xs font-medium">Nama Mandor:</div>
                <div class="text-xs mb-1 bg-green-100 px-1 mb-2">{{ $details[0]->name }}</div>
                <div class="text-gray-600 text-xs font-medium">Tanggal:</div>
                <div class="text-xs bg-blue-100 px-1 mb-2">{{ \Carbon\Carbon::parse($details[0]->createdat)->format('d/m/Y') }}</div>
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
                        @php $totalLuas = 0; $plots = $details->unique('lkhno'); @endphp
                        @foreach($plots as $d)  
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-1 px-2">{{ $d->lkhno }}</td>
                                <td class="py-1 px-2">{{ $d->blok }}</td>
                                <td class="py-1 px-2">{{ $d->plot }}</td>
                                <td class="py-1 px-2 text-right">{{ $d->luasrkh }} HA</td>
                                <td class="py-1 px-2 bg-green-100">{{ $d->activitycode }} {{ $d->herbisidagroupname }}</td>
                            </tr>
                            @php $totalLuas += floatval($d->luasrkh); @endphp

                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-200 text-gray-800">
                            <td colspan="3" class="py-1 px-2 font-semibold text-right text-xs">Total Luas</td>
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
            


            <table class='min-w-full md:w-1/3 p-2 bg-white shadow rounded text-xs'>
                <thead class="text-gray-700">
                    <tr>
                        <th class="py-2 px-2 border-b text-center">Herbisida - Item</th>
                        <th class="py-2 px-2 border-b text-center">Dosage (HA)</th>
                        <th class="py-2 px-2 border-b text-center">Qty Disiapkan</th>
                        <th class="py-2 px-2 border-b text-center">Qty Retur</th>
                        <th class="py-2 px-2 border-b text-center">Nomor LKH</th>
                        <th class="py-2 px-2 border-b text-center">Nomor Retur</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600">
                    @foreach ($detailmaterial as $d)
                        @php
                            // cari luas untuk grup ini
                            $plot    = $plots->Where('lkhno', $d->lkhno)->first();
                            $luas    = $plot->luasrkh ?? 0;

                            if(empty($luas) || $plot->luasrkh == 0){
                                dd($plots, $d->lkhno, $detailmaterial);
                            }

                        @endphp
                    
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-2 px-2">
                                <select
                                    @if (strtoupper($details[0]->flagstatus) != 'ACTIVE') disabled @endif style="color: #374151 !important; opacity: 1 !important;"
                                    name="itemcode[{{ $d->lkhno }}][{{ $d->itemcode }}]"
                                    class="item-select w-full border-none bg-yellow-100 text-xs"
                                    data-luas="{{ $luas }}"
                                >
                                    @foreach ($itemlist as $item)

                                    <option value="{{ $item->itemcode }}" {{ $item->itemcode == $d->itemcode && $item->dosageperha == $d->dosageperha ? 'selected' : '' }}
                                        data-dosage="{{$item->dosageperha}}" >
                                          Herbisida {{$item->herbisidagroupid}} - {{ $item->itemcode }} - {{ $item->itemcode == $d->itemcode ? ($item->itemname ?? '[Nama Item]') : $item->itemname }} - {{$item->dosageperha}} ({{$item->measure}})
                                        </option>
                                    @endforeach
                                </select>

                                <span class="print-label text-xs">
                                    Herbisida {{ $d->herbisidagroupid }} - {{ $d->itemcode }} - {{ $d->itemname ?? '[Nama Item]' }} - {{ $d->dosageperha }} ({{ $d->unit }}) ({{ $luas }})
                                </span>
                    
                                {{-- keep current values for submit --}}
                                <input type="hidden" name="qty[{{ $d->lkhno }}][{{ $d->itemcode }}]"
                                       class="selected-qty" value="{{ $d->qty }}">
                                <input type="hidden" name="dosage[{{ $d->lkhno }}][{{ $d->itemcode }}]"
                                       class="selected-dosage" value="{{ $d->dosageperha }}">
                                <input type="hidden" name="unit[{{ $d->lkhno }}][{{ $d->itemcode }}]"
                                       class="selected-unit" value="{{ $d->unit }}">
                                <input type="hidden" name="itemcodelist[{{ $d->lkhno }}][]"
                                       class="selected-itemcode" value="{{ $d->itemcode }}">
                            </td>
                    
                            <td class="py-2 px-2 text-center">
                                <span class="labeldosage">{{ $d->dosageperha }} {{ $d->dosageunit }}</span>
                            </td>
                    
                    
                            <td class="py-2 px-2 text-center">
                                <span class="labelqty">{{ $d->qty }}</span>
                            </td>
                    
                            <td class="py-2 px-2 text-center">
                                {{ $d->qtyretur ?? 0 }}
                            </td>

                            <td class="py-2 px-2 text-center">
                                {{ $d->lkhno }}
                            </td>
                    
                            <td class="py-2 px-2 text-center">
                                @if (empty($d->noretur) && strtoupper($details[0]->flagstatus) != 'ACTIVE')
                                    <a href="{{ route('input.gudang.retur', [
                                            'retur' => $d->qtyretur,
                                            'itemcode' => $d->itemcode,
                                            'rkhno' => $details[0]->rkhno,
                                            'herbisidagroupid' => $d->herbisidagroupid
                                        ]) }}"
                                       class="inline-block bg-yellow-100 text-gray-800 hover:bg-blue-600 hover:text-white text-xs py-1 px-2 rounded shadow transition no-print"
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
    
            <!-- Submit Button -->
            @if(strtoupper($details[0]->flagstatus) == 'ACTIVE' )
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
               class="bg-white inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 font-semibold py-2 px-4 rounded shadow transition no-print">
                ← Kembali
            </a>&nbsp;
            <button type="button"
                onclick="window.print()"
                class="bg-white border border-gray-300 hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 rounded shadow no-print">
                🖨️ Cetak
            </button>
        </div>
        
    </div>
    
    </x-layout>
    
    <script>
        $(document).ready(function() {
            $('.item-select').each(function() {
                const selected = $(this).find('option:selected');
                const dosage = selected.data('dosage');
                const luasArea = $(this).data('luas');
                const qty = (dosage * luasArea).toFixed(3); // Max 3 angka belakang koma
                
                const row = $(this).closest('tr');
                row.find('.labelqty').text(qty);
                row.find('.selected-qty').val(qty); // Update nilai input hidden juga
            });
        });
        
        $('.item-select').on('change', function () {
            const selected = $(this).find('option:selected');
            const dosage = selected.data('dosage');
            const luasArea = $(this).data('luas');
            const qty = (dosage * luasArea).toFixed(3); // Max 3 angka belakang koma
            const newItemcode = selected.val();
        
            // Ambil lkhno dari name attribute select
            const selectName = $(this).attr('name');
            const lkhno = selectName.match(/\[(.*?)\]/)[1];
        
            const row = $(this).closest('tr');
            
            // Update values
            row.find('.selected-dosage').val(dosage);
            row.find('.selected-qty').val(qty);
            
            // Update name attribute dengan itemcode baru
            row.find('.selected-dosage').attr('name', `dosage[${lkhno}][${newItemcode}]`);
            row.find('.selected-qty').attr('name', `qty[${lkhno}][${newItemcode}]`);
            row.find('.selected-unit').attr('name', `unit[${lkhno}][${newItemcode}]`);
            $(this).attr('name', `itemcode[${lkhno}][${newItemcode}]`);
            
            // Update display
            row.find('.labeldosage').text(dosage);
            row.find('.labelqty').text(qty);

            row.find('.selected-itemcode').val(selected.val());
        });
        </script>