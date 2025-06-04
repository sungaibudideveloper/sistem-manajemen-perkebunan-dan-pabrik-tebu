<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <form action="{{ route('input.kerjaharian.rencanakerjaharian.store') }}" method="POST">
    @csrf

       {{-- ERROR HANDLING - TARUH DI SINI --}}
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                        Terdapat {{ $errors->count() }} kesalahan yang perlu diperbaiki:
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
    {{-- END ERROR HANDLING --}}

  <div class="bg-gray-50 rounded-lg p-6 mb-8 border border-blue-100">
    <div class="flex justify-between items-start">
      <!-- KIRI: No RKH + Mandor + Tanggal -->
      <div class="flex flex-col space-y-6 w-2/3">
        <!-- No RKH -->
        <div>
          <label for="rkhno" class="block text-sm font-semibold text-gray-700 mb-2">No RKH</label>
          <p id="rkhno" class="text-5xl font-mono tracking-wider text-gray-800">
            {{ $rkhno ?? '-' }}
          </p>
          <input type="hidden" name="rkhno" value="{{ $rkhno }}">
        </div>

      <!-- Mandor & Tanggal -->
      <div x-data="mandorPicker()" class="grid grid-cols-2 gap-6 max-w-md" x-init="
    @if(old('mandor_id'))
        selected = {
            userid: '{{ old('mandor_id') }}',
            name: '{{ old('mandor') }}'
        }
    @endif
">
        <!-- Input Mandor -->
        <div>
          <label for="mandor" class="block text-sm font-semibold text-gray-700 mb-2">Mandor</label>
          <input
            type="text"
            name="mandor"
            id="mandor"
            readonly
            placeholder="Pilih Mandor"
            @click="open = true"
            :value="selected.userid && selected.name ? `${selected.userid} – ${selected.name}` : ''"
            class="w-full text-sm font-medium border-2 border-gray-200 rounded-lg px-4 py-3 cursor-pointer bg-gray hover:bg-gray-50 transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
          <input type="hidden" name="mandor_id" x-model="selected.userid">
        </div>

        <!-- Input Tanggal -->
        @php
          $todayFormatted = \Carbon\Carbon::now()->format('d/m/Y');
        @endphp

        <div>
          <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal</label>
<input
  type="date"
  name="tanggal"
  id="tanggal"
  value="{{ old('tanggal', \Carbon\Carbon::now()->format('Y-m-d')) }}"
  class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 bg-gray-100 text-sm font-medium"
  readonly
/>
@error('tanggal')
  <p class="mt-1 text-red-600 text-sm">{{ $message }}</p>
@enderror
        </div>
        

        <!-- include modal di sini jika perlu -->
        @include('input.kerjaharian.rencanakerjaharian.modal-mandor')
      </div>
    </div>

    <!-- KANAN: Ringkasan Tenaga Kerja -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 min-w-[320px]">
      <div class="flex items-center mb-4">
        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
        <h3 class="text-sm font-bold text-gray-800">Absen Hari Ini</h3>
      </div>
      <p class="text-xs text-gray-600 mb-3" id="absen-info">{{ date('d/m/Y') }}</p>
      <div class="grid grid-cols-3 gap-4 text-center">
        <div class="bg-blue-50 rounded-lg p-3">
                <div class="text-lg font-bold" id="summary-laki">0</div>
                <div class="text-xs text-gray-600">Laki-laki</div>
              </div>
              <div class="bg-pink-50 rounded-lg p-3">
                <div class="text-lg font-bold" id="summary-perempuan">0</div>
                <div class="text-xs text-gray-600">Perempuan</div>
              </div>
              <div class="bg-green-50 rounded-lg p-3">
                <div class="text-lg font-bold" id="summary-total">0</div>
                <div class="text-xs text-gray-600">Total</div>
              </div>
      </div>
    </div>
  </div>

      <!-- Modern Table -->
      <div class="bg-white mt-12 rounded-xl p-6 border border-gray-300 border-r shadow-md">
        <div class="overflow-x-auto">
          <table id="rkh-table" class="table-fixed w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
            <colgroup>
              <col style="width: 32px"><!-- No. -->
              <col style="width: 32px"><!-- Blok -->
              <col style="width: 48px"><!-- Plot -->
              <col style="width: 208px"><!-- Aktivitas -->
              <col style="width: 48px"><!-- Luas -->
              <col style="width: 32px"><!-- L -->
              <col style="width: 32px"><!-- P -->
              <col style="width: 32px"><!-- Total -->
              <col style="width: 55px"><!-- Jenis -->
              <col style="width: 105px"><!-- Material -->
              <col style="width: 40px"><!-- Kendaraan -->
              <col style="width: 150px"><!-- Keterangan -->
            </colgroup>

            <thead class="bg-gradient-to-r from-gray-800 to-gray-700 text-white">
              <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">Blok</th>
                <th rowspan="2">Plot</th>
                <th rowspan="2">Aktivitas</th>
                <th rowspan="2">Luas<br>(ha)</th>
                <th colspan="4" class="text-center">Tenaga Kerja</th>
                <th rowspan="2">Material</th>
                <th rowspan="2">Kendaraan</th>
                <th rowspan="2">Keterangan</th>
              </tr>
              <tr class="bg-gray-700">
                <th>L</th>
                <th>P</th>
                <th>Total</th>
                <th>Jenis</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              {{-- Modifikasi untuk bagian table rows --}}
              @for ($i = 0; $i < 8; $i++)
                <tr x-data="activityPicker({{ $i }})" class="rkh-row hover:bg-blue-50 transition-colors">

                  <!-- #No -->
                  <td class="px-1 py-3 text-sm text-center font-medium text-gray-600 bg-gray-50">{{ $i + 1 }}</td>
                  
                  <!-- #Blok -->
                  <td class="px-1 py-3">
                    <div x-data="blokPicker({{ $i }})" class="relative">
                      <input
                        type="text"
                        readonly
                        required
                        @click="open = true"
                        :value="selected.blok ? selected.blok : ''"
                        class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      >
                      <input type="hidden" name="rows[{{ $i }}][blok]" x-model="selected.blok">

                      {{-- Include modal-blok --}}
                      @include('input.kerjaharian.rencanakerjaharian.modal-blok')
                    </div>
                  </td>

                  <!-- #Plot -->
                  <td class="px-1 py-3">
                    <div x-data="plotPicker({{ $i }})" class="relative">
                      <input
                        type="text"
                        readonly
                        @click="isBlokSelected ? (open = true) : null"
                        :value="selected.plot ? selected.plot : ''"
                        :class="{
                          'cursor-pointer bg-white hover:bg-gray-50': isBlokSelected,
                          'cursor-not-allowed bg-gray-100': !isBlokSelected,
                          'border-gray-200': isBlokSelected,
                          'border-gray-300': !isBlokSelected
                        }"
                        class="w-full text-sm border-2 rounded-lg px-3 py-2 text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                      >
                      <input type="hidden" name="rows[{{ $i }}][plot]" x-model="selected.plot">

                      @include('input.kerjaharian.rencanakerjaharian.modal-plot')
                    </div>
                  </td>

                  {{-- Sisa kolom tetap sama --}}
                  <!-- #Activity -->
                  <td class="px-1 py-3" ">
                    <div class="relative">
                      <input
                        type="text"
                        readonly
                        placeholder=""
                        @click="open = true"
                        :value="selected.activitycode && selected.activityname ? `${selected.activitycode} – ${selected.activityname}` : ''"
                        class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="selected.activitycode ? 'bg-blue-50 text-blue-900' : 'bg-gray-50 text-gray-500'"
                      >
                      <div class="absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                      </div>
                    </div>
                    <!-- Hidden inputs -->
                    <input 
                      type="hidden" 
                      name="rows[{{ $i }}][nama]" 
                      x-model="selected.activitycode"
                      x-ref="activityInput"
                    >
                    <input 
                      type="hidden" 
                      name="rows[{{ $i }}][usingvehicle]" 
                      x-model="selected.usingvehicle"
                    >
                    @include('input.kerjaharian.rencanakerjaharian.modal-activity')
                  </td>

                  <!-- #Luas -->
                  <td class="px-1 py-3">
                    <input type="number" name="rows[{{ $i }}][luas]" min="0" value="{{ old('rows.'.$i.'.luas') }}" step="0.01" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>

                  <!-- #Tenaga Kerja -->
                  

                  <td class="px-1 py-3">
                    <input type="number" name="rows[{{ $i }}][laki_laki]" min="0" value="{{ old('rows.'.$i.'.laki_laki') }}" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-1 py-3">
                    <input type="number" name="rows[{{ $i }}][perempuan]" min="0" value="{{ old('rows.'.$i.'.perempuan') }}" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>
                  <td class="px-1 py-3">
                    <input type="number" name="rows[{{ $i }}][jumlah_tenaga]" class="w-full text-sm border-2 border-gray-300 rounded-lg px-3 py-2 text-right bg-gray-100 font-semibold text-gray-700" readonly placeholder="-">
                  </td>
                  <td class="px-1 py-3">
                    <input 
                      type="text" 
                      name="rows[{{ $i }}][jenistenagakerja]" 
                      readonly 
                      class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-center text-xs font-medium"
                      placeholder="-"
                      id="jenistenagakerja-{{ $i }}"
                    >
                  </td>


                  

                  <!-- #Material  -->
                  <td class="px-1 py-3" x-data="materialPicker({{ $i }})">
                    <div class="relative">
                      <div 
                        @click="checkMaterial()"
                        :class="{
                          'cursor-pointer bg-white hover:bg-gray-50': hasMaterial,
                          'cursor-not-allowed bg-gray-100': !hasMaterial,
                          'border-green-500 bg-green-50': hasMaterial && selectedGroup,
                          'border-green-300 bg-green-25': hasMaterial && !selectedGroup,
                          'border-gray-300': !hasMaterial
                        }"
                        class="w-full text-sm border-2 rounded-lg px-3 py-2 text-center transition-colors focus:ring-2 focus:ring-blue-500 min-h-[40px] flex items-center justify-center"
                      >
                        <!-- 1. Default sebelum pilih activity -->
                        <div x-show="!currentActivityCode" class="text-gray-500 text-xs">-</div>

                        <!-- 2. Sudah pilih activity tapi kosong grup -->
                        <div x-show="currentActivityCode && !hasMaterial" class="text-xs font-medium">Tidak</div>
                        <div x-show="hasMaterial && !selectedGroup" class="text-green-600 text-xs font-medium">
                          <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                          </svg>
                          Pilih Grup
                        </div>
                        <div x-show="hasMaterial && selectedGroup" class="text-green-800 text-xs font-medium text-center">
                          <div class="font-semibold" x-text="selectedGroup.herbisidagroupname"></div>
                        </div>
                      </div>
                      
                      <!-- Hidden inputs untuk menyimpan selected group -->
                      <input type="hidden" :name="`rows[{{ $i }}][material_group_id]`" x-model="selectedGroup ? selectedGroup.herbisidagroupid : ''">
                      <input type="hidden" :name="`rows[{{ $i }}][material_group_name]`" x-model="selectedGroup ? selectedGroup.herbisidagroupname : ''">
                    </div>
                    
                    @include('input.kerjaharian.rencanakerjaharian.modal-material')
                  </td>

                  <!-- #Kendaraan -->
                  <td class="px-1 py-3">
                    <!-- hidden input untuk usingvehicle, terikat ke Alpine -->
                    <input 
                      type="hidden" 
                      name="rows[{{ $i }}][usingvehicle]" 
                      x-model.number="selected.usingvehicle"
                    >

                    <!-- kolom Kendaraan -->
                    <input 
                      type="text" 
                      name="rows[{{ $i }}][kendaraan]" 
                      readonly 
                      x-bind:value="
                      selected.usingvehicle === 1 
                        ? 'Ya' 
                        : (selected.usingvehicle === 0 
                            ? 'Tidak' 
                            : '-'
                          )
                    "
                      class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-center text-xs font-medium"
                      id="kendaraan-{{ $i }}"
                    >
                  </td>

                  <td class="px-1 py-3">
                    <input type="text" name="rows[{{ $i }}][keterangan]" value="{{ old('rows.'.$i.'.keterangan') }}" class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  </td>

                </tr>
              @endfor
            </tbody>
            <tfoot class="bg-gray-100">
              <tr class="border-t-2 border-gray-200">
                <td colspan="4" class="px-1 py-3 text-center text-sm font-bold uppercase tracking-wider text-gray-700 bg-gray-100">Total</td>
                <td id="total-luas" class="px-1 py-3 text-center text-sm font-bold bg-gray-50">0</td>
                <td id="total-laki" class="px-1 py-3 text-center text-sm font-bold bg-blue-50">0</td>
                <td id="total-perempuan" class="px-1 py-3 text-center text-sm font-bold bg-red-50">0</td>
                <td id="total-tenaga" class="px-1 py-3 text-center text-sm font-bold bg-green-50">0</td>
                <td colspan="4" class="px-1 py-3 bg-gray-100"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-8 flex flex-col items-center space-y-4">
        <!-- Secondary Actions -->
        <div class="flex justify-center space-x-4">
          <button
            type="button"
            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            Preview
          </button>
          <button
            type="button"
            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print
          </button>
          <button
            type="button"
            onclick="window.history.back()"
            class="bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 px-8 py-3 rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
          </button>
        </div>
        
        <!-- Primary Submit Button -->
        <button
          type="submit"
          class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-12 py-4 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          Submit RKH
        </button>

<!-- Debug Submit Button -->
<button
  type="button"
  id="debug-submit"
  class="bg-yellow-500 hover:bg-yellow-600 text-white px-12 py-4 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center"
>
  Debug Submit
</button>

      </div>
    
  </div>
</form>



  <script>

window.bloksData = @json($bloks ?? []);
window.masterlistData = @json($masterlist ?? []);
window.herbisidaData = @json($herbisidagroups ?? []);
window.absenData = @json($absentenagakerja ?? []);

// Pastikan data tersedia secara global
document.addEventListener('DOMContentLoaded', function() {


  // Jika data dikirim dari controller, simpan ke variabel global
    if (typeof herbisidagroups !== 'undefined') {
    window.herbisidaData = herbisidagroups;
  }

  if (typeof bloksData !== 'undefined') {
    window.bloksData = bloksData;
  }
  if (typeof masterlistData !== 'undefined') {
    window.masterlistData = masterlistData;
  }
  
  // Existing code untuk calculate totals
  const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
  rows.forEach(row => attachListeners(row));
  calculateTotals();

  document.getElementById('debug-submit').addEventListener('click', () => {
    const data = [];

    const nomorRKH = document.getElementById('rkhno').textContent.trim();
  const mandor = document.querySelector('input[name="mandor_id"]').value;
  const tanggal = document.querySelector('input[name="tanggal"]').value;

    rows.forEach((row, index) => {
      const rowData = {
        no: index + 1,
        blok: row.querySelector('input[name$="[blok]"]').value,
        plot: row.querySelector('input[name$="[plot]"]').value,
        aktivitas: row.querySelector('input[name$="[nama]"]').value,
        luas: row.querySelector('input[name$="[luas]"]').value,
        laki_laki: row.querySelector('input[name$="[laki_laki]"]').value,
        perempuan: row.querySelector('input[name$="[perempuan]"]').value,
        jumlah_tenaga: row.querySelector('input[name$="[jumlah_tenaga]"]').value,
        jenis_tenagakerja: row.querySelector('input[name$="[jenistenagakerja]"]').value,
        material_group_id: row.querySelector('input[name$="[material_group_id]"]').value,
        material_group_name: row.querySelector('input[name$="[material_group_name]"]').value,
        kendaraan: row.querySelector('input[name$="[kendaraan]"]').value,
        keterangan: row.querySelector('input[name$="[keterangan]"]').value,
      };
      data.push(rowData);
    });

    const debugData = {
    nomor_rkh: nomorRKH,
    mandor_id: mandor,
    tanggal: tanggal,
    rows: data,
  };
    console.log('Debug Data:', debugData);
  });
});

// Sisanya tetap sama (calculateRow, calculateTotals, attachListeners)
function calculateRow(row) {
  const lakiInput = row.querySelector('input[name$="[laki_laki]"]');
  const perempuanInput = row.querySelector('input[name$="[perempuan]"]');
  const jumlahInput = row.querySelector('input[name$="[jumlah_tenaga]"]');
  
    const laki      = parseInt(lakiInput.value)      || 0;
  const perempuan = parseInt(perempuanInput.value) || 0;
  const total     = laki + perempuan;

  if (total > 0) {
    jumlahInput.value = total;
  } else {
    // kosongkan value agar placeholder muncul
    jumlahInput.value = '';
  }
}

function calculateTotals() {
  let luasSum = 0, lakiSum = 0, perempuanSum = 0, tenagaSum = 0;
  document.querySelectorAll('#rkh-table tbody tr.rkh-row').forEach(row => {
    const luas = parseFloat(row.querySelector('input[name$="[luas]"]').value) || 0;
    const laki = parseInt(row.querySelector('input[name$="[laki_laki]"]').value) || 0;
    const perempuan = parseInt(row.querySelector('input[name$="[perempuan]"]').value) || 0;
    luasSum += luas;
    lakiSum += laki;
    perempuanSum += perempuan;
    tenagaSum += laki + perempuan;
    calculateRow(row);
  });
  document.getElementById('total-luas').textContent = `${luasSum.toFixed(2)} ha`;
  document.getElementById('total-laki').textContent = lakiSum;
  document.getElementById('total-perempuan').textContent = perempuanSum;
  document.getElementById('total-tenaga').textContent = tenagaSum;
}

function attachListeners(row) {
  ['[laki_laki]', '[perempuan]', '[luas]'].forEach(suffix => {
    const input = row.querySelector(`input[name$="${suffix}"]`);
    if (input) input.addEventListener('input', () => calculateTotals());
  });
}

  // Pass data PHP ke JavaScript
  window.herbisidaData = @json($herbisidagroups ?? []);
  
  console.log('Herbisida data loaded:', window.herbisidaData); // De



  // Validasi Checkin data form diisi

  document.querySelector('form').addEventListener('submit', function(e) {
    console.log('Form is being submitted');
    
    // Check if mandor is selected
    const mandorId = document.querySelector('input[name="mandor_id"]').value;
    if (!mandorId) {
        e.preventDefault();
        alert('Please select a Mandor');
        return;
    }
    
    // Check if at least one row has data
    const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
    let hasData = false;
    rows.forEach(row => {
        const blok = row.querySelector('input[name$="[blok]"]').value;
        const plot = row.querySelector('input[name$="[plot]"]').value;
        const activity = row.querySelector('input[name$="[nama]"]').value;
        if (blok && plot && activity) {
            hasData = true;
        }
    });
    
    if (!hasData) {
        e.preventDefault();
        alert('Please fill at least one complete row');
        return;
    }
});

// Update function untuk menghitung absen berdasarkan mandor
function updateAbsenSummary(selectedMandorId, selectedMandorCode = '', selectedMandorName = '') {
    if (!selectedMandorId || !window.absenData) {
        // Reset ke 0 jika tidak ada mandor dipilih
        document.getElementById('summary-laki').textContent = '0';
        document.getElementById('summary-perempuan').textContent = '0';
        document.getElementById('summary-total').textContent = '0';
        // Reset absen info ke tanggal saja
        const today = new Date().toLocaleDateString('id-ID', {
            day: '2-digit',
            month: '2-digit', 
            year: 'numeric'
        });
        document.getElementById('absen-info').textContent = today;
        return;
    }

    // Update absen info dengan nama mandor
    const today = new Date().toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit', 
        year: 'numeric'
    });

    if (selectedMandorCode && selectedMandorName) {
        document.getElementById('absen-info').textContent = `${selectedMandorCode} ${selectedMandorName} - ${today}`;
    }

    // Filter data absen berdasarkan mandor yang dipilih
    const filteredAbsen = window.absenData.filter(absen => 
        absen.idmandor === selectedMandorId
    );

    // Hitung jumlah berdasarkan gender
    let lakiCount = 0;
    let perempuanCount = 0;

    filteredAbsen.forEach(absen => {
        if (absen.gender === 'L') {
            lakiCount++;
        } else if (absen.gender === 'P') {
            perempuanCount++;
        }
    });

    const totalCount = lakiCount + perempuanCount;

    // Update tampilan
    document.getElementById('summary-laki').textContent = lakiCount;
    document.getElementById('summary-perempuan').textContent = perempuanCount;
    document.getElementById('summary-total').textContent = totalCount;
}


  </script>
</x-layout>
