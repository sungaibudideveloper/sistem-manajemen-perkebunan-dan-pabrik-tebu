{{--resources\views\input\rencanakerjaharian\create.blade.php--}} 
<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <!-- VALIDATION ERROR MODAL -->
  <div x-data="{ showValidationModal: false, validationErrors: [] }" 
       x-show="showValidationModal" 
       x-cloak
       class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
       style="display: none;"
       @validation-error.window="showValidationModal = true; validationErrors = $event.detail.errors">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
      <div class="p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
          <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Form Belum Lengkap</h3>
        <p class="text-sm text-gray-600 mb-4">Mohon lengkapi field yang diperlukan:</p>
        
        <!-- Error List -->
        <div class="text-left bg-red-50 rounded-lg p-3 mb-4 max-h-48 overflow-y-auto">
          <ul class="text-sm text-red-700 space-y-1">
            <template x-for="error in validationErrors" :key="error">
              <li x-text="error"></li>
            </template>
          </ul>
        </div>

        <button @click="showValidationModal = false; highlightRequiredFields()"
                class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors">
          OK, Saya Mengerti
        </button>
      </div>
    </div>
  </div>
  <div x-data="{ showModal: false, modalType: '', modalMessage: '', modalErrors: [] }" 
       x-show="showModal" 
       x-cloak
       class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-4"
       style="display: none;">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
      <!-- Success Modal -->
      <div x-show="modalType === 'success'" class="p-6 text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
          <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Berhasil!</h3>
        <p class="text-sm text-gray-600 mb-4" x-html="modalMessage"></p>
        <button @click="window.location.href = '{{ route('input.rencanakerjaharian.index') }}'"
                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
          OK
        </button>
      </div>
    </div>
  </div>

  <form id="rkh-form" action="{{ route('input.rencanakerjaharian.store') }}" method="POST">
    @csrf

    <!-- OLD ERROR HANDLING - Keep for non-AJAX fallback -->
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

  <!-- HEADER CONTENT -->
<div class="bg-gray-50 rounded-lg p-6 mb-8 border border-blue-100">
  <div class="flex justify-between items-start">
    <!-- KIRI: No RKH, Mandor, Tanggal, Keterangan -->
    <div class="flex flex-col w-2/3 space-y-2">
      <!-- Hidden No RKH -->
      <input type="hidden" name="rkhno" value="{{ $rkhno }}">

      <!-- Mandor & Tanggal -->
      <div x-data="mandorPicker()" class="grid grid-cols-2 gap-4 max-w-md" x-init="
        @if(old('mandor_id'))
            selected = {
                userid: '{{ old('mandor_id') }}',
                name: '{{ collect($mandors)->firstWhere('userid', old('mandor_id'))->name ?? '' }}'
            }
        @endif
      ">
        <!-- Input Mandor -->
        <div>
          <label for="mandor" class="block text-sm font-semibold text-gray-700 mb-1">Mandor</label>
          <input
            type="text"
            name="mandor"
            id="mandor"
            readonly
            placeholder="Pilih Mandor"
            @click="open = true"
            :value="selected.userid && selected.name ? `${selected.userid} - ${selected.name}` : ''"
            class="w-full text-sm font-medium border-2 border-gray-200 rounded-lg px-4 py-2 cursor-pointer bg-gray hover:bg-gray-50 transition-colors focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
          <input type="hidden" name="mandor_id" x-model="selected.userid">
        </div>

        <!-- Input Tanggal -->
        <div>
          <label for="tanggal" class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
          <input
            type="date"
            name="tanggal"
            id="tanggal"
            value="{{ $selectedDate }}"
            readonly
            class="w-full border-2 border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-sm font-medium cursor-not-allowed"
          />
        </div>

        @include('input.rencanakerjaharian.modal-mandor')
      </div>

      <!-- Keterangan Dokumen -->
      <div class="max-w-2xl">
        <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-1">
          Keterangan Dokumen
          <span class="text-xs text-gray-500 font-normal">(opsional)</span>
        </label>
        <input 
          type="text"
          name="keterangan"
          id="keterangan"
          placeholder="Masukkan keterangan untuk dokumen RKH ini..."
          value="{{ old('keterangan') }}"
          maxlength="500"
          class="w-full text-sm border-2 border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        @error('keterangan')
          <p class="mt-1 text-red-600 text-sm">{{ $message }}</p>
        @enderror
      </div>

    </div>

    <!-- RIGHT HEADER: Absen Tenaga Kerja -->
    <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200 w-[320px] md:w-[400px] lg:w-[430px]">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
          <h3 class="text-sm font-bold text-gray-800">Data Absen</h3>
        </div>
        <!-- Mandor & Tanggal Info (moved to top right) -->
        <div class="text-right">
          <p class="text-xs text-gray-600" id="absen-info">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</p>
        </div>
      </div>
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

      <!-- Main Table -->
      <div class="bg-white mt-6 rounded-xl border border-gray-300 border-r shadow-md">
        <div class="overflow-x-auto">
          <table id="rkh-table" class="table-fixed w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
            <colgroup>
              <col style="width: 32px"><!-- No. -->
              <col style="width: 32px"><!-- Blok -->
              <col style="width: 48px"><!-- Plot -->
              <col style="width: 150px"><!-- Aktivitas -->
              <col style="width: 60px"><!-- Luas -->
              <col style="width: 45px"><!-- L -->
              <col style="width: 45px"><!-- P -->
              <col style="width: 50px"><!-- Total -->
              <col style="width: 70px"><!-- Jenis -->
              <col style="width: 80px"><!-- Material -->
              <col style="width: 80px"><!-- Kendaraan -->
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
              </tr>
              <tr class="bg-gray-700">
                <th>L</th>
                <th>P</th>
                <th>Total</th>
                <th>Jenis</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              @for ($i = 0; $i < 8; $i++)
                <tr x-data="activityPicker({{ $i }})" class="rkh-row hover:bg-blue-50 transition-colors" x-init="
                  @if(old('rows.'.$i.'.nama'))
                    selected = {
                      activitycode: '{{ old('rows.'.$i.'.nama') }}',
                      activityname: '{{ collect($activities)->firstWhere('activitycode', old('rows.'.$i.'.nama'))->activityname ?? '' }}',
                      usingvehicle: {{ old('rows.'.$i.'.usingvehicle', 'null') }},
                      jenistenagakerja: {{ collect($activities)->firstWhere('activitycode', old('rows.'.$i.'.nama'))->jenistenagakerja ?? 'null' }}
                    };
                    updateJenisField();
                  @endif
                ">

                  <!-- #No -->
                  <td class="px-1 py-3 text-sm text-center font-medium text-gray-600 bg-gray-50">{{ $i + 1 }}</td>
                  
                  <!-- #Blok -->
                  <td class="px-1 py-3">
                    <div x-data="blokPicker({{ $i }})" class="relative" x-init="
                      init();
                      @if(old('rows.'.$i.'.blok'))
                        selected = {
                          blok: '{{ old('rows.'.$i.'.blok') }}'
                        };
                        Alpine.store('blokPerRow').setBlok({{ $i }}, '{{ old('rows.'.$i.'.blok') }}');
                      @endif
                    ">
                      <input
                        type="text"
                        readonly
                        @click="open = true"
                        :value="selected.blok ? selected.blok : ''"
                        class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        data-validation-message="Blok harus dipilih"
                      >
                      <input type="hidden" name="rows[{{ $i }}][blok]" x-model="selected.blok">
                      @include('input.rencanakerjaharian.modal-blok')
                    </div>
                  </td>

                  <!-- #Plot -->
                  <td class="px-1 py-3">
                    <div x-data="plotPicker({{ $i }})" class="relative" x-init="
                      init();
                      @if(old('rows.'.$i.'.plot'))
                        selected = {
                          plot: '{{ old('rows.'.$i.'.plot') }}'
                        };
                      @endif
                    ">
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
                        data-validation-message="Plot harus dipilih"
                      >
                      <input type="hidden" name="rows[{{ $i }}][plot]" x-model="selected.plot">
                      @include('input.rencanakerjaharian.modal-plot')
                    </div>
                  </td>

                  <!-- #Activity -->
                  <td class="px-1 py-3">
                    <div class="relative">
                      <input
                        type="text"
                        readonly
                        placeholder=""
                        @click="open = true"
                        :value="selected.activitycode && selected.activityname ? `${selected.activitycode} – ${selected.activityname}` : ''"
                        class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-center cursor-pointer bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="selected.activitycode ? 'bg-blue-50 text-blue-900' : 'bg-gray-50 text-gray-500'"
                        data-validation-message="Aktivitas harus dipilih"
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
                    @include('input.rencanakerjaharian.modal-activity')
                  </td>

                  <!-- #Luas -->
                  <td class="px-1 py-3">
                    <input 
                      type="number" 
                      name="rows[{{ $i }}][luas]" 
                      min="0" 
                      value="{{ old('rows.'.$i.'.luas') }}" 
                      step="0.01" 
                      class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      data-validation-message="Luas area harus diisi"
                      data-row-index="{{ $i }}"
                    >
                  </td>

                  <!-- #Tenaga Kerja -->
                  <td class="px-1 py-3">
                    <input 
                      type="number" 
                      name="rows[{{ $i }}][laki_laki]" 
                      min="0" 
                      value="{{ old('rows.'.$i.'.laki_laki', '0') }}" 
                      class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      data-validation-message="Jumlah laki-laki harus diisi"
                    >
                  </td>
                  <td class="px-1 py-3">
                    <input 
                      type="number" 
                      name="rows[{{ $i }}][perempuan]" 
                      min="0" 
                      value="{{ old('rows.'.$i.'.perempuan', '0') }}" 
                      class="w-full text-sm border-2 border-gray-200 rounded-lg px-3 py-2 text-right focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      data-validation-message="Jumlah perempuan harus diisi"
                    >
                  </td>
                  <td class="px-1 py-3">
                    <input type="number" name="rows[{{ $i }}][jumlah_tenaga]" class="w-full text-sm border-2 border-gray-300 rounded-lg px-3 py-2 text-right bg-gray-100 font-semibold text-gray-700" readonly placeholder="-">
                  </td>
                  <td class="px-1 py-3">
                    <input 
                      type="text" 
                      name="rows[{{ $i }}][jenistenagakerja]" 
                      readonly 
                       onfocus="this.blur()" 
                      class="w-full border-2 border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-center text-sm font-medium cursor-not-allowed"
                      placeholder="-"
                      id="jenistenagakerja-{{ $i }}"
                    >
                  </td>

                  <!-- #Material -->
                  <td class="px-1 py-3" x-data="materialPicker({{ $i }})" x-init="init()">
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
                          class="w-full text-sm border-2 rounded-lg px-3 py-2 text-center transition-colors focus:ring-2 focus:ring-blue-500 min-h-[40px] flex items-center justify-center bg-gray-100"
                        >
                        <div x-show="!currentActivityCode" x-cloak class="text-gray-500 text-xs">-</div>
                        <div x-show="currentActivityCode && !hasMaterial" x-cloak class="text-xs font-medium">Tidak</div>
                        <div x-show="hasMaterial && !selectedGroup" x-cloak class="text-green-600 text-xs font-medium">
                          <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                          </svg>
                          Pilih Grup
                        </div>
                        <div x-show="hasMaterial && selectedGroup" class="text-green-800 text-xs font-medium text-center">
                          <div class="font-semibold" x-text="selectedGroup ? selectedGroup.herbisidagroupname : ''"></div>
                        </div>
                      </div>
                      
                      <!-- Hidden inputs - akan di-create otomatis oleh JavaScript -->
                    </div>
                    
                    @include('input.rencanakerjaharian.modal-material')
                  </td>

                  <!-- #Kendaraan -->
                  <td class="px-1 py-3" x-data="kendaraanPicker({{ $i }})" x-init="init()">
                    <div class="relative">
                      <div
                        @click="checkVehicle()"
                        :class="{
                          'cursor-pointer bg-white hover:bg-gray-50': hasVehicle,
                          'cursor-not-allowed bg-gray-100': !hasVehicle,
                          'border-green-500 bg-green-50': hasVehicle && selectedOperator,
                          'border-green-300 bg-green-25': hasVehicle && !selectedOperator,
                          'border-gray-300': !hasVehicle
                        }"
                         class="w-full text-sm border-2 rounded-lg px-3 py-2 text-center transition-colors focus:ring-2 focus:ring-blue-500 min-h-[40px] flex items-center justify-center bg-gray-100"
                      >
                        <div x-show="!currentActivityCode" x-cloak class="text-gray-500 text-xs">-</div>
                        <div x-show="currentActivityCode && !hasVehicle" x-cloak class="text-xs font-medium">Tidak</div>
                        <div x-show="hasVehicle && !selectedOperator" x-cloak class="text-green-600 text-xs font-medium">
                          <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                          </svg>
                          Pilih Operator
                        </div>
                        <div x-show="hasVehicle && selectedOperator" class="text-green-800 text-xs font-medium text-center">
                          <!-- Tampilkan info operator -->
                          <div class="font-semibold" x-text="selectedOperator ? selectedOperator.nokendaraan : ''"></div>
                          <div class="text-gray-600 text-[10px]" x-text="selectedOperator ? selectedOperator.nama : ''"></div>
                          
                          <!-- TAMBAHAN: Tampilkan info helper jika ada -->
                          <div x-show="useHelper && selectedHelper" x-cloak class="text-purple-600 text-[9px] mt-1">
                            + Helper: <span x-text="selectedHelper ? selectedHelper.nama : ''"></span>
                          </div>
                        </div>
                      </div>
                    
                      <!-- Hidden inputs - Akan dibuat otomatis oleh JavaScript -->
                    </div>
                  
                    @include('input.rencanakerjaharian.modal-kendaraan')
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
                <td colspan="3" class="px-1 py-3 bg-gray-100"></td>
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
            onclick="window.location.href = '{{ route('input.rencanakerjaharian.index') }}';"
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
          id="submit-btn"
          class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-12 py-4 rounded-lg text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="submit-icon">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          <!-- Loading Spinner (hidden by default) -->
          <svg class="animate-spin w-5 h-5 mr-2 hidden" fill="none" viewBox="0 0 24 24" id="loading-spinner">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span id="submit-text">Submit RKH</span>
        </button>
      </div>
    
  </div>
</form>



<script>
// ===== GLOBAL DATA - SEMUA DI SINI =====
window.bloksData = @json($bloks ?? []);
window.masterlistData = @json($masterlist ?? []);
window.herbisidaData = @json($herbisidagroups ?? []);
window.operatorsData = @json($operatorsData ?? []);
window.absenData = @json($absentenagakerja ?? []);
window.plotsData = @json($plotsData ?? []);
window.activitiesData = @json($activities ?? []);
window.helpersData = @json($helpersData ?? []);

// ===== ALPINE STORES - SEMUA DI SINI =====
document.addEventListener('alpine:init', () => {
  // Modal states
  Alpine.store('modal', {
    showModal: false,
    modalType: '',
    modalMessage: '',
    modalErrors: []
  });
  
  // Blok tracking per row
  Alpine.store('blokPerRow', {
    selected: {},
    
    setBlok(rowIndex, blok) {
      this.selected[rowIndex] = blok;
    },
    
    getBlok(rowIndex) {
      return this.selected[rowIndex] || '';
    },
    
    hasBlok(rowIndex) {
      return !!this.selected[rowIndex];
    }
  });
  
  // Activity tracking per row
  Alpine.store('activityPerRow', {
    selected: {},
    
    setActivity(rowIndex, activity) {
      this.selected[rowIndex] = activity;
    },
    
    getActivity(rowIndex) {
      return this.selected[rowIndex] || null;
    }
  });
});

// ===== FORM HANDLER =====
document.addEventListener('DOMContentLoaded', function() {
  // Initialize calculations
  const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
  rows.forEach(row => attachListeners(row));
  calculateTotals();

  // MODERN FORM SUBMISSION with AJAX
  document.getElementById('rkh-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Clear previous validation errors
    clearValidationErrors();
    
    // CLIENT-SIDE VALIDATION
    const validationResult = validateForm();
    if (!validationResult.isValid) {
      showValidationModal(validationResult.errors);
      return;
    }

    // Show loading state
    showLoadingState();

    // Prepare form data
    const formData = new FormData(this);

    // AJAX submission
    fetch(this.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success modal
        showModal('success', data.message);
      } else {
        // Show error modal
        const errors = data.errors ? Object.values(data.errors).flat() : [];
        showModal('error', data.message || 'Terjadi kesalahan saat menyimpan data', errors);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      showModal('error', 'Terjadi kesalahan sistem');
    })
    .finally(() => {
      hideLoadingState();
    });
  });
});

// ===== VALIDATION FUNCTIONS =====
function validateForm() {
  const errors = [];
  
  const mandorId = document.querySelector('input[name="mandor_id"]').value;
  if (!mandorId) {
    errors.push('Silakan pilih Mandor terlebih dahulu');
  }

  const rows = document.querySelectorAll('#rkh-table tbody tr.rkh-row');
  let hasCompleteRow = false;

  rows.forEach((row, index) => {
    const blokInput = row.querySelector('input[name$="[blok]"]');
    const plotInput = row.querySelector('input[name$="[plot]"]');
    const activityInput = row.querySelector('input[name$="[nama]"]');
    const luasInput = row.querySelector('input[name$="[luas]"]');
    const lakiInput = row.querySelector('input[name$="[laki_laki]"]');
    const perempuanInput = row.querySelector('input[name$="[perempuan]"]');

    const blok = blokInput.value;
    const plot = plotInput.value;
    const activity = activityInput.value;
    const luas = luasInput.value;
    const laki = lakiInput.value;
    const perempuan = perempuanInput.value;

    // Check if blok is filled (trigger field)
    if (blok) {
      hasCompleteRow = true;
      const rowNum = index + 1;
      
      // If blok is filled, all required fields must be filled
      if (!plot) errors.push(`Baris ${rowNum}: Plot harus dipilih`);
      if (!activity) errors.push(`Baris ${rowNum}: Aktivitas harus dipilih`);
      if (!luas) errors.push(`Baris ${rowNum}: Luas area harus diisi`);
      if (laki === '' || laki === null) errors.push(`Baris ${rowNum}: Jumlah laki-laki harus diisi`);
      if (perempuan === '' || perempuan === null) errors.push(`Baris ${rowNum}: Jumlah perempuan harus diisi`);
      
      // Check material requirement
      if (activity) {
        const hasMaterialOptions = window.herbisidaData && window.herbisidaData.some(item => item.activitycode === activity);
        
        if (hasMaterialOptions) {
          const materialGroupInput = row.querySelector('input[name$="[material_group_id]"]');
          const materialValue = materialGroupInput ? materialGroupInput.value : '';
          
          if (!materialGroupInput || !materialGroupInput.value) {
            const errorMsg = `Baris ${rowNum}: Grup material harus dipilih untuk aktivitas ini`;
            errors.push(errorMsg);
          }
        }
      }
    }
  });

  if (!hasCompleteRow) {
    errors.push('Minimal satu baris harus diisi dengan lengkap');
  }

  return {
    isValid: errors.length === 0,
    errors: errors
  };
}

function showValidationModal(errors) {
  window.dispatchEvent(new CustomEvent('validation-error', {
    detail: { errors: errors }
  }));
}

function clearValidationErrors() {
  const errorFields = document.querySelectorAll('.border-red-500');
  errorFields.forEach(field => {
    field.classList.remove('border-red-500', 'bg-red-50');
    field.classList.add('border-gray-200');
  });
}

// ===== LOADING STATE FUNCTIONS =====
function showLoadingState() {
  const submitBtn = document.getElementById('submit-btn');
  const submitText = document.getElementById('submit-text');
  const submitIcon = document.getElementById('submit-icon');
  const loadingSpinner = document.getElementById('loading-spinner');
  
  submitBtn.disabled = true;
  submitText.textContent = 'Menyimpan...';
  submitIcon.classList.add('hidden');
  loadingSpinner.classList.remove('hidden');
}

function hideLoadingState() {
  const submitBtn = document.getElementById('submit-btn');
  const submitText = document.getElementById('submit-text');
  const submitIcon = document.getElementById('submit-icon');
  const loadingSpinner = document.getElementById('loading-spinner');
  
  submitBtn.disabled = false;
  submitText.textContent = 'Submit RKH';
  submitIcon.classList.remove('hidden');
  loadingSpinner.classList.add('hidden');
}

// ===== MODAL FUNCTIONS =====
function showModal(type, message, errors = []) {
  if (type === 'success') {
    const modalElement = document.querySelector('[x-data*="showModal"]');
    if (modalElement && modalElement._x_dataStack && modalElement._x_dataStack[0]) {
      modalElement._x_dataStack[0].showModal = true;
      modalElement._x_dataStack[0].modalType = type;
      modalElement._x_dataStack[0].modalMessage = message;
      modalElement._x_dataStack[0].modalErrors = errors;
    }
  } else {
    showValidationModal([message, ...errors]);
  }
}

// ===== CALCULATION FUNCTIONS =====
function calculateRow(row) {
  const lakiInput = row.querySelector('input[name$="[laki_laki]"]');
  const perempuanInput = row.querySelector('input[name$="[perempuan]"]');
  const jumlahInput = row.querySelector('input[name$="[jumlah_tenaga]"]');
  
  const laki = parseInt(lakiInput.value) || 0;
  const perempuan = parseInt(perempuanInput.value) || 0;
  const total = laki + perempuan;

  if (total > 0) {
    jumlahInput.value = total;
  } else {
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

// ===== PLOT CHANGE LISTENER =====
window.addEventListener('plot-changed', function(e) {
  const { plotCode, rowIndex } = e.detail;
  updateLuasFromPlot(plotCode, rowIndex);
});

function updateLuasFromPlot(plotCode, rowIndex) {
  if (plotCode && window.plotsData) {
    const plotData = window.plotsData.find(p => p.plot === plotCode);
    if (plotData && plotData.luasarea) {
      const luasInput = document.querySelector(`input[name="rows[${rowIndex}][luas]"]`);
      if (luasInput) {
        luasInput.value = plotData.luasarea;
        luasInput.dispatchEvent(new Event('input'));
      }
    }
  }
}

// ===== ABSEN SUMMARY FUNCTION =====
function updateAbsenSummary(selectedMandorId, selectedMandorCode = '', selectedMandorName = '') {
  if (!selectedMandorId || !window.absenData) {
    document.getElementById('summary-laki').textContent = '0';
    document.getElementById('summary-perempuan').textContent = '0';
    document.getElementById('summary-total').textContent = '0';
    const selectedDate = '{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}';
    document.getElementById('absen-info').textContent = selectedDate;
    return;
  }

  const selectedDate = '{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}';

  if (selectedMandorCode && selectedMandorName) {
    document.getElementById('absen-info').textContent = `${selectedMandorCode} ${selectedMandorName} - ${selectedDate}`;
  }

  const filteredAbsen = window.absenData.filter(absen => 
    absen.mandorid === selectedMandorId
  );

  let lakiCount = 0;
  let perempuanCount = 0;

  filteredAbsen.forEach(absen => {
    if (absen.gender === 'L') {
      lakiCount++;
    } else if (absen.gender === 'P') {
      perempuanCount++;
    }
  });

  document.getElementById('summary-laki').textContent = lakiCount;
  document.getElementById('summary-perempuan').textContent = perempuanCount;
  document.getElementById('summary-total').textContent = lakiCount + perempuanCount;
}

// ===== MATERIAL PICKER FUNCTION =====
function materialPicker(rowIndex) {
  return {
    open: false,
    rowIndex: rowIndex,
    currentActivityCode: '',
    selectedGroup: null,
    
    get hasMaterial() {
      const hasOptions = this.currentActivityCode && this.availableGroups.length > 0;
      return hasOptions;
    },
    
    get availableGroups() {
      if (!this.currentActivityCode || !window.herbisidaData) return [];
      
      const groups = {};
      window.herbisidaData.forEach(item => {
        if (item.activitycode === this.currentActivityCode) {
          if (!groups[item.herbisidagroupid]) {
            groups[item.herbisidagroupid] = {
              herbisidagroupid: item.herbisidagroupid,
              herbisidagroupname: item.herbisidagroupname,
              showDetails: false,
              items: []
            };
          }
          groups[item.herbisidagroupid].items.push(item);
        }
      });
      
      return Object.values(groups);
    },
    
    checkMaterial() {
      if (this.hasMaterial) {
        this.open = true;
      } else {
        this.selectedGroup = null;
        this.updateHiddenInputs();
      }
    },
    
    selectGroup(group) {
      this.selectedGroup = {
        herbisidagroupid: group.herbisidagroupid,
        herbisidagroupname: group.herbisidagroupname
      };
    },
    
    clearSelection() {
      this.selectedGroup = null;
      this.updateHiddenInputs();
    },
    
    confirmSelection() {
      this.updateHiddenInputs();
      this.open = false;
    },
    
    updateHiddenInputs() {
      this.ensureHiddenInputsExist();
      
      const materialGroupInput = document.querySelector(`input[name="rows[${this.rowIndex}][material_group_id]"]`);
      const materialGroupNameInput = document.querySelector(`input[name="rows[${this.rowIndex}][material_group_name]"]`);
      const usingMaterialInput = document.querySelector(`input[name="rows[${this.rowIndex}][usingmaterial]"]`);
      
      if (materialGroupInput) {
        materialGroupInput.value = this.selectedGroup ? this.selectedGroup.herbisidagroupid : '';
      }
      
      if (materialGroupNameInput) {
        materialGroupNameInput.value = this.selectedGroup ? this.selectedGroup.herbisidagroupname : '';
      }
      
      if (usingMaterialInput) {
        usingMaterialInput.value = (this.hasMaterial && this.selectedGroup) ? '1' : '0';
      }
    },
    
    ensureHiddenInputsExist() {
      const materialCell = document.querySelector(`tr:nth-child(${this.rowIndex + 1}) td:nth-child(10)`);
      if (!materialCell) return;
      
      if (!document.querySelector(`input[name="rows[${this.rowIndex}][material_group_id]"]`)) {
        const groupIdInput = document.createElement('input');
        groupIdInput.type = 'hidden';
        groupIdInput.name = `rows[${this.rowIndex}][material_group_id]`;
        groupIdInput.value = '';
        materialCell.appendChild(groupIdInput);
      }
      
      if (!document.querySelector(`input[name="rows[${this.rowIndex}][material_group_name]"]`)) {
        const groupNameInput = document.createElement('input');
        groupNameInput.type = 'hidden';
        groupNameInput.name = `rows[${this.rowIndex}][material_group_name]`;
        groupNameInput.value = '';
        materialCell.appendChild(groupNameInput);
      }
      
      if (!document.querySelector(`input[name="rows[${this.rowIndex}][usingmaterial]"]`)) {
        const usingMaterialInput = document.createElement('input');
        usingMaterialInput.type = 'hidden';
        usingMaterialInput.name = `rows[${this.rowIndex}][usingmaterial]`;
        usingMaterialInput.value = '0';
        materialCell.appendChild(usingMaterialInput);
      }
    },
    
    init() {
      this.ensureHiddenInputsExist();
      
      const activityInput = document.querySelector(`input[name="rows[${this.rowIndex}][nama]"]`);
      if (activityInput) {
        const observer = new MutationObserver(() => {
          const newActivity = activityInput.value || '';
          if (this.currentActivityCode !== newActivity) {
            this.currentActivityCode = newActivity;
            this.selectedGroup = null;
            this.updateHiddenInputs();
          }
        });
        
        observer.observe(activityInput, {
          attributes: true,
          attributeFilter: ['value']
        });
        
        activityInput.addEventListener('input', () => {
          const newActivity = activityInput.value || '';
          if (this.currentActivityCode !== newActivity) {
            this.currentActivityCode = newActivity;
            this.selectedGroup = null;
            this.updateHiddenInputs();
          }
        });
        
        this.currentActivityCode = activityInput.value || '';
        this.updateHiddenInputs();
      }
    }
  }
}
</script>



</x-layout>