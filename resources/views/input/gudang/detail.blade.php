<x-layout>
    <x-slot:title>Pemakaian Herbisida RKH</x-slot:title>

    <!-- ========================================================================= -->
    <!-- WRAPPER: Header, Data Grid, "Pupuk" Heading, All Grouped Tables, Approve -->
    <!-- ========================================================================= -->
    <div class="p-8 max-w-screen-xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:gap-4 gap-2 mb-8">
            <!-- Box 1: No RKH & Mandor -->
            <div class="w-full md:w-1/3 p-3 bg-white shadow rounded-lg">
                <strong class="block text-gray-600 text-sm">No Dokumen:</strong>
                <p class="text-sm text-gray-800 bg-green-100">RKH <b>{{ $details[0]->rkhno }}</b></p>
                <p class="text-sm text-gray-800 bg-blue-100">USE <b>{{ $details[0]->nouse }}</b></p>
                <strong class="block text-gray-600 text-sm mt-2">Nama Mandor:</strong>
                <p class="text-sm text-gray-800">{{ $details[0]->mandorname }}</p>
                <strong class="block text-gray-600 text-sm">Tanggal:</strong>
                <p class="text-sm text-gray-800">
                    {{ \Carbon\Carbon::parse($details[0]->createdat)->format('d/m/Y') }}
                </p>
            </div>

            <!-- Box 3: Table of Blok, Plot, Luas, Activity -->
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
                                <td class="py-2 px-3 bg-green-100">
                                    {{ $d->activitycode }} {{ $d->herbisidagroupname }}
                                </td>
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
            </div>
        </div>

        <!-- "Pupuk yang Perlu Disiapkan" Heading -->
        <h3 class="text-xl font-semibold text-gray-800 mb-4 text-center">
            Pupuk yang Perlu Disiapkan
        </h3>

        <!-- ========================================================================= -->
        <!-- FORM STARTS HERE - WRAPPING ALL TABLES -->
        <!-- ========================================================================= -->
        <form action="{{ route('input.gudang.submit', ['rkhno' => $details[0]->rkhno]) }}" method="POST">
            @csrf
            
            <!-- ========================================================================= -->
            <!-- GROUPED TABLES: one per herbisidagroupid, each in its own box -->
            <!-- ========================================================================= -->
            
            @foreach($details->groupby('herbisidagroupid') as $groupId => $items)
                @php
                    $title = $items->first();
                @endphp

                <div class="mb-8 p-6 bg-white shadow rounded-lg">
                    <table class="min-w-full bg-white text-sm text-left rounded-lg shadow-md overflow-hidden">
                        <thead class="bg-gray-200 text-gray-700">
                            <tr>
                                <th colspan="1" class="py-3 px-4 border-b bg-green-100">
                                    {{ $title->activitycode }} {{ $title->herbisidagroupname }} 
                                </th>
                                <th colspan="1" class="py-3 px-4 border-b">
                                    Dosage (HA)
                                </th>
                                <th colspan="1" class="py-3 px-4 border-b">
                                    Luas (HA)
                                </th>
                                <th colspan="1" class="py-3 px-4 border-b">
                                    Total Qty Dibutuhkan 
                                </th>
                                <th colspan="1" class="py-3 px-4 border-b">
                                    Qty Retur 
                                </th>
                                <th colspan="1" class="py-3 px-4 border-b">
                                    Nomor Retur 
                                </th>
                            </tr>
                        </thead>

                        <tbody class="text-gray-600">
                            
                        {{-- @foreach($dosage->where('herbisidagroupid', $groupId) as $d) --}}
                        @foreach( $lst->where('herbisidagroupid', $groupId) as $d )
                        @php
                            $matched = collect($lst)->where('itemcode', $d->itemcode)->where('herbisidagroupid', $d->herbisidagroupid)->first();
                            $luas = (float) $details->where('herbisidagroupid',$d->herbisidagroupid)->sum(function($item) {
                                                return (float) $item->luasarea;
                                            });    
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <select name="itemcode[{{ $groupId }}][{{ $loop->iteration }}]" class="item-select w-full border-none bg-yellow-100">
                                    @foreach($itemlist as $item) 
                                    @php $currentluas = $luas * $item->dosageperha; @endphp
                                    <option value="{{ $item->itemcode }}" {{ $item->itemcode == $d->itemcode && $item->herbisidagroupid == $d->herbisidagroupid ? 'selected' : '' }}
                                    data-dosage="{{$item->dosageperha}}" data-qty="{{$currentluas}}">
                                      Herbisida {{$item->herbisidagroupid}} -  {{ $item->itemcode }} - {{ $item->itemcode == $d->itemcode ? ($item->itemname ?? '[Nama Item]') : $item->itemname }} - {{$item->dosageperha}} 
                                    </option>
                                    @endforeach
                                </select> 
                                <!-- Hidden fields to capture additional data -->
                                <input type="hidden" name="dosage[{{ $groupId }}][{{ $loop->iteration }}]" class="selected-dosage" value="{{ $d->dosageperha }}">
                                <input type="hidden" name="unit[{{ $groupId }}][{{ $loop->iteration }}]" class="selected-dosage" value="{{ $d->dosageunit }}">
                                
                                
                            </td>
                            <td class="py-3 px-4">
                                <label class="labeldosage">{{ $d->dosageperha }} {{ $d->dosageunit }}</label>
                            </td>
                            <td class="py-3 px-4">
                                <label class="luas" id='luas'>{{$luas}}</label>
                            </td>
                            <td class="py-3 px-4">
                                <label class="labelqty"> {{ $matched->qty ?? '-' }}</label>
                            </td>
                            <td class="py-3 px-4">
                            <input name="qtyretur[{{ $groupId }}][{{ $loop->iteration }}]" type="text" value="{{ $matched->qtyretur ?? '0' }}" 
                               class="w-32 border-none bg-yellow-100">
                            </td>
                            <td class="py-3 px-4">
                                {{ $matched->noretur ?? '-' }}
                            </td>
                        </tr>
                    @endforeach

                        </tbody>
                    </table>
                </div>
            @endforeach

            <!-- ========================================================================= -->
            <!-- Submit BUTTON (now inside the form) -->
            <!-- ========================================================================= -->
            <div class="flex justify-center mt-6">
                <button @if($details->whereNotNull('nouse')->count()<1 == false) @endif 
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white text-lg font-semibold py-2 px-8 rounded-lg shadow-md transition duration-300 ease-in-out"
                >
                    Penyerahan
                </button>
            </div>
        </form>
        <!-- FORM ENDS HERE -->
        
    </div>
    <!-- End of p-8 wrapper -->

    <!-- ========================================================================= -->
    <!-- "Kembali" Button remains outside, so it doesn't "waste space" inside the table area -->
    <!-- ========================================================================= -->
    <div class="mt-8 flex justify-center">
        <a
            href="{{ url()->previous() }}"
            class="inline-block bg-gray-200 text-gray-800 hover:bg-gray-300 text-lg font-semibold py-2 px-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 ease-in-out"
        >
            ← Kembali
        </a>
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
    row.find('.selected-dosage').val(dosage);  // Update hidden field
    row.find('.selected-qty').val(qty);        // Update hidden field
    
    row.find('.labeldosage').text(dosage);
    row.find('.labelqty').text(qty);
  });
</script>