<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
  {{-- Untuk Data Default Awal (Modal)--}}
    x-data="{
      open: @json($errors->any()),
      mode: 'create',
      form: { companycode:'TBL1', activitycode: '', itemcode: '', time: '', description: '', totaldosage: '', dosageunit: 'L'},
      items: [],
      loadItems() {
      fetch('{{ route("masterdata.herbisida.items") }}'+ '?companycode=' + this.form.companycode) {{-- Ambil data item dari routes (secara default fetch = GET)--}}
        .then(res => res.json()) {{-- Mengambil data dari response json dan Convert--}}
        .then(data => this.items = data); {{-- Simpan data ke dalam items --}}
      },
      resetForm() {
        this.mode = 'create';
        this.form = { companycode:'TBL1', activitycode: '', itemcode: '', time: '', description: '', totaldosage: '', dosageunit: 'L' };
        this.open = true;
        this.loadItems()
      }
    }"
    x-init="loadItems()"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    <div class="flex items-center justify-between px-4 py-2">

      {{-- Create Button (Modal)--}}
      @if (auth()->user() && in_array('Create Dosis Herbisida', json_decode(auth()->user()->permissions ?? '[]')))
        <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
                <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14m-7 7V5" />
                    </svg> New Data
        </button>
      @endif
        
        {{-- Search Form --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
        <input
          type="text"
          name="search"
          id="search"
          value="{{ request('search') }}"
          class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
          onkeydown="if(event.key==='Enter') this.form.submit()"
        />
      </form>

      {{-- Item Per Page: --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select 
          name="perPage" id="perPage"
          onchange="this.form.submit()"
          class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
            <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>
    

      {{-- Modal - Form --}}
      <div x-show="open" x-cloak class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Modal - Backdrop --}}
        <div x-show="open" x-transition.opacity
            class="fixed inset-0 bg-gray-500/75" aria-hidden="true"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div {{-- @click.away="open = false" --}} {{-- matikan @clickaway= memencet selain modal akan menjadi false--}}
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">
              <form method="POST" id="formcreateedit"
                :action="mode === 'edit'
                ? '{{ url('masterdata/herbisida-dosage') }}/' + form.companycodeoriginal +'/'+ form.activitycodeoriginal +'/'+ form.itemcodeoriginal
                : '{{ url('masterdata/herbisida-dosage') }}'"
                class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH"> {{-- Spoofing PATCH method --}}
                </template>
                <div class="text-center sm:text-left">
                  <h3 class="text-lg font-medium text-gray-900" id="modal-title" x-text="mode === 'edit' ? 'Edit Herbisida Dosage' : 'Create Herbisida Dosage'">
                  </h3>
                  <div class="mt-4 space-y-4">
                    @error('activitycode')
                      <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <div>
                      <label for="companycode" class="block text-sm font-medium text-gray-700">Kode Company</label>
                      <select name="companycode" id="companycode" x-model="form.companycode" @change="loadItems()" x-init="form.companycode = '{{ old('companycode') }}'"
                        class="mt-1 block w-1/3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="TBL1">TBL1</option>
                        <option value="TBL2">TBL2</option>
                        <option value="TBL3">TBL3</option>
                      </select>
                    </div>
                    <div>
                      <label for="activitycode" class="block text-sm font-medium text-gray-700">Kode Aktivitas</label>
                      <input type="text" name="activitycode" id="activitycode" x-model="form.activitycode"
                            x-init="form.activitycode = '{{ old('activitycode') }}'"
                            @input="form.activitycode = form.activitycode.toUpperCase()"
                            class="mt-1 block w-1/2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                            maxlength="10" required>
                    </div>
                    <div>
                      <label for="itemcode" class="block text-sm font-medium text-gray-700">Kode Item</label>
                      <select id="itemcode" name="itemcode" x-model="form.itemcode" x-init="form.itemcode = '{{ old('itemcode') }}'"
                        class="mt-1 block w-1/2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required
                        >
                        <option value="" disabled selected>Pilih Kode Item…</option>
                        <template x-for="i in items" :key="i.itemcode">
                          <option :value="i.itemcode" x-text="`${i.itemcode} – ${i.itemname}`"></option>
                        </template>
                      </select>
                    </div>
                    <div>
                      <label for="time" class="block text-sm font-medium text-gray-700">Waktu</label>
                      <input type="text" name="time" id="time" x-model="form.time" x-init="form.time = '{{ old('time') }}'"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            maxlength="50" required>
                    </div>
                    <div>
                      <label for="description" class="block text-sm font-medium text-gray-700">Keterangan</label>
                      <textarea name="description" id="description" x-model="form.description" x-init="form.description = '{{ old('description') }}'"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" maxlength="100"></textarea>
                    </div>
                    <div class="grid grid-cols-5 gap-4">
                      <div class="col-span-2">
                        <label for="totaldosage" class="block text-sm font-medium text-gray-700">Total Dosis</label>
                        <input type="number" step="0.01" name="totaldosage" id="totaldosage" x-model="form.totaldosage" x-init="form.totaldosage = '{{ old('totaldosage') }}'"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              min="0" max="9999.99" required>
                      </div>
                      <div>
                        <label for="dosageunit" class="block text-sm font-medium text-gray-700">Satuan</label>
                        <select name="dosageunit" id="dosageunit" x-model="form.dosageunit" x-init="form.dosageunit = '{{ old('dosageunit') }}'"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                          <option value="L">L</option>
                          <option value="gr">gr</option>
                          <option value="kg">kg</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                  <button type="submit" id="submitmodal"
                          class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto"
                          x-text="mode === 'edit' ? 'Update' : 'Create'">
                    Save
                  </button>
                  <button @click.prevent="open = false" type="button"
                          class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-gray-700 text-sm font-medium shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto">
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="mx-auto px-4 py-2">
        <div class="overflow-x-auto border border-gray-300 rounded-md">
            <table class="min-w-full bg-white text-sm text-center">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="py-2 px-4 border-b">No.</th>
                        <th class="py-2 px-4 border-b">Kode Company</th>
                        <th class="py-2 px-4 border-b">Kode Aktivitas</th>
                        <th class="py-2 px-4 border-b">Kode Item</th>
                        <th class="py-2 px-4 border-b">Waktu</th>
                        <th class="py-2 px-4 border-b">Keterangan</th>
                        <th class="py-2 px-4 border-b">Total Dosis</th>
                        <th class="py-2 px-4 border-b">Satuan</th>
                        <th class="py-2 px-4 border-b">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($herbisidaDosages as $index => $data)
                        <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border-b">{{ $herbisidaDosages->firstItem() + $index }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->companycode }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->activitycode }}</td>
                            <td class="py-2 px-4 border-b">
                              <a
                                href="{{ route('masterdata.herbisida.index', ['search' => $data->itemcode]) }}"
                                class="text-blue-600 hover:underline"
                              >
                              {{ $data->itemcode }} – {{ $data->itemname }}
                                
                              </a>
                            </td>
                            <td class="py-2 px-4 border-b">{{ $data->time }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->description }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->totaldosage }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->dosageunit }}</td>
                            {{-- Edit Button (Modal)--}}
                              <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center space-x-2">
                                  @if (auth()->user() && in_array('Edit Dosis Herbisida', json_decode(auth()->user()->permissions ?? '[]')))
                                  <button
                                    @click="
                                      mode = 'edit';
                                      form.companycodeoriginal = '{{ $data->companycode }}';
                                      form.companycode = '{{ $data->companycode }}';
                                      form.activitycodeoriginal = '{{ $data->activitycode }}'; {{-- Original activity code for update --}}
                                      form.activitycode = '{{ $data->activitycode }}';
                                      form.itemcodeoriginal = '{{ $data->itemcode }}'; {{-- Original item code for update --}}
                                      form.itemcode = '{{ $data->itemcode }}';
                                      form.time = '{{ $data->time }}';
                                      form.description = '{{ $data->description }}';
                                      form.totaldosage = {{ $data->totaldosage ?? 0}};
                                      form.dosageunit = '{{ $data->dosageunit }}';
                                      loadItems(); 
                                      open = true
                                    "
                                    class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm" {{-- Pake class group biar icon bisa ganti pas di hover --}}
                                    >
                                    <svg
                                      class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                      aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                      width="24" height="24" fill="none"
                                      viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-outline" /> {{-- Ambil dari sprite-svg.blade yang sudah di incldue di x-sprite-svg di x-layout --}}
                                    </svg>
                                    <svg 
                                      class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                        aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                        width="24" height="24" fill="currentColor"
                                        viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-solid" />
                                        <use xlink:href="#icon-edit-solid2" />
                                    </svg>
                                    <span class="w-0.5"></span>
                                  </button>
                                  @endif
                                  {{-- Delete Button --}}
                                  @if (auth()->user() && in_array('Hapus Dosis Herbisida', json_decode(auth()->user()->permissions ?? '[]')))
                                    <form 
                                      action="{{ url("masterdata/herbisida-dosage/{$data->companycode}/{$data->activitycode}/{$data->itemcode}") }}" 
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus data ini?');"
                                      class="inline"
                                      >
                                      @csrf
                                      @method('DELETE')
                                      <button 
                                        type="submit"
                                        class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm"
                                        >
                                        <svg
                                          class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                          aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                          width="24" height="24" fill="none"
                                          viewBox="0 0 24 24">
                                            <use xlink:href="#icon-trash-outline" />
                                        </svg>
                                        <svg 
                                          class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                                          aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                          width="24" height="24" fill="currentColor"
                                          viewBox="0 0 24 24">
                                            <use xlink:href="#icon-trash-solid" />
                                        </svg>
                                      </button>
                                    </form>
                                  @endif
                                </div> {{-- Untuk membungkus 2 button edit dan delete agar sebelahan --}}
                              </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mx-4 my-1">
        @if ($herbisidaDosages->hasPages())
            {{ $herbisidaDosages->appends(['perPage' => $herbisidaDosages->perPage()])->links() }}
        @else
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ $herbisidaDosages->count() }}</span> of <span class="font-medium">{{ $herbisidaDosages->total() }}</span> results
                </p>
            </div>
        @endif
    </div>

    {{-- Toast Notification --}}
    @if (session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif
  </div>
</x-layout>

<script>
  $('#submitmodal').on('click', function() {
    $(this).attr('disabled', true); // Disable the button
    $(this).text('Saving...'); // Change the button text to "Saving..."
    $('#formcreateedit').submit(); // Submit the form
  });
</script>