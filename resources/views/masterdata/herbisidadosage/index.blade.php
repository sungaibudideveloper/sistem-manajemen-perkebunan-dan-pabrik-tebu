<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div 
    x-data="{
      open: @json($errors->any()),
      mode: 'create',
      form: { companycode:'TBL1', activitycode: '', itemcode: '', time: '', description: '', dosageperha: ''},
      items: [],
       groups: [],
      loadItems() {
      fetch('{{ route("masterdata.herbisida.items") }}'+ '?companycode=' + this.form.companycode)
        .then(res => res.json())
        .then(data => this.items = data);
      },
      loadGroups() {
      fetch('{{ route("masterdata.herbisida.group") }}?herbisidagroupid=' + this.form.herbisidagroupid)
        .then(res => res.json())
        .then(data => this.groups = data);
      },
      resetForm() {
        this.mode = 'create';
        this.form = { companycode:'TBL1', activitycode: '', itemcode: '', time: '', description: '', dosageperha: '' };
        this.open = true;
        this.loadItems()
      },
      loadFormData() {
        this.loadItems();
        this.loadGroups();
      }
    }"
    x-init="loadFormData()"
    class="mx-auto py-1 bg-white rounded-md shadow-md">

    <div class="flex items-center justify-between px-4 py-2">

      @can('masterdata.herbisidadosage.create')
        <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
                <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14m-7 7V5" />
                    </svg> New Data
        </button>
      @endcan
        
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
    
      <div x-show="open" x-cloak class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="open" x-transition.opacity
            class="fixed inset-0 bg-gray-500/75" aria-hidden="true"></div> 

        <div class="fixed inset-0 z-10 overflow-y-auto">
          <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div
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
                ? '{{ url('masterdata/herbisida-dosage') }}/' + form.companycodeoriginal +'/'+ form.herbisidagroupid_original +'/'+ form.itemcodeoriginal
                : '{{ url('masterdata/herbisida-dosage') }}'"
                class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH">
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
                      <select name="companycode" id="companycode" x-model="form.companycode" @change="loadFormData()" x-init="form.companycode = '{{ old('companycode') }}'"
                        class="mt-1 block w-1/3 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="TBL1">TBL1</option>
                        <option value="TBL2">TBL2</option>
                        <option value="TBL3">TBL3</option>
                      </select>
                    </div>
                    <div>
                      <label for="herbisidagroupid" class="block text-sm font-medium text-gray-700">Herbisida Group</label>
                      <select id="herbisidagroupid" name="herbisidagroupid" x-model="form.herbisidagroupid" x-init="form.herbisidagroupid = '{{ old('herbisidagroupid') }}'"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required
                        >
                        <option value="" disabled selected>Pilih Kode Herbisida</option>
                        <template x-for="i in groups" :key="i.herbisidagroupid">
                          <option :value="i.herbisidagroupid" x-text="`${i.herbisidagroupid} – ${i.herbisidagroupname}`"></option>
                        </template>
                      </select>

                    </div>
                    <div>
                      <label for="itemcode" class="block text-sm font-medium text-gray-700">Kode Item</label>
                      <select id="itemcode" name="itemcode" x-model="form.itemcode" x-init="form.itemcode = '{{ old('itemcode') }}'"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required
                        >
                        <option value="" disabled selected>Pilih Kode Item…</option>
                        <template x-for="i in items" :key="i.itemcode">
                          <option :value="i.itemcode" x-text="`${i.itemcode} – ${i.itemname}`"></option>
                        </template>
                      </select>
                    </div>
                    <div>
                      <label for="time" class="block text-sm font-medium text-gray-700">Waktu</label>
                      <input readonly style="border:none;" type="text" name="time" id="time" x-model="form.time" x-init="form.time = '{{ old('time') }}'"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            maxlength="50" required>
                    </div>
                    <div>
                      <label for="description" class="block text-sm font-medium text-gray-700">Keterangan</label>
                      <textarea readonly name="description" id="description" x-model="form.description" x-init="form.description = '{{ old('description') }}'"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" maxlength="100"
                                style='border:none;'></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                      <div class="col-span-2">
                        <label for="dosageperha" class="block text-sm font-medium text-gray-700">Total Dosis (per Ha)</label>
                        <input type="number" step="0.01" name="dosageperha" id="dosageperha" x-model="form.dosageperha" x-init="form.dosageperha = '{{ old('dosageperha') }}'"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              min="0" max="9999.99" required>
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

    <div class="mx-auto px-4 py-2">
        <div class="overflow-x-auto border border-gray-300 rounded-md">
            <table class="min-w-full bg-white text-sm text-center">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="py-2 px-4 border-b">No.</th>
                        <th class="py-2 px-4 border-b">Kode Company</th>
                        <th class="py-2 px-4 border-b">Grup - Kode Aktivitas</th>
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
                            <td class="py-2 px-4 border-b">{{ $data->herbisidagroupid }} - {{ $data->activitycode }}</td>
                            <td class="py-2 px-4 border-b">
                              <a
                                href="{{ route('masterdata.herbisida.index', ['search' => $data->itemcode]) }}"
                                class="text-blue-600 hover:underline"
                              >
                              {{ $data->itemcode }} – {{ $data->itemname }}
                                
                              </a>
                            </td>
                            <td class="py-2 px-4 border-b">{{ $data->createdat }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->itemname }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->dosageperha }}</td>
                            <td class="py-2 px-4 border-b">{{ $data->measure }}</td>
                              <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center space-x-2">
                                  @can('masterdata.herbisidadosage.edit')
                                  {{-- Edit Button (Modal)--}}
                                  <button
                                    @click="
                                      mode = 'edit';
                                      form.companycodeoriginal = '{{ $data->companycode }}';
                                      form.companycode = '{{ $data->companycode }}';
                                      form.activitycodeoriginal = '{{ $data->activitycode }}';
                                      form.activitycode = '{{ $data->activitycode }}';
                                      form.herbisidagroupid_original = '{{ $data->herbisidagroupid }}';
                                      form.herbisidagroupid = '{{ $data->herbisidagroupid }}';
                                      form.itemcodeoriginal = '{{ $data->itemcode }}';
                                      form.itemcode = '{{ $data->itemcode }}';
                                      form.time = '{{ $data->createdat }}';
                                      form.description = '{{ $data->itemname }}';
                                      form.dosageperha = {{ $data->dosageperha ?? 0}};
                                      loadFormData(); 
                                      open = true

                                      console.log('form after edit clicked:', form);
                                    "
                                    class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm"
                                    >
                                    <svg
                                      class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                      aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                      width="24" height="24" fill="none"
                                      viewBox="0 0 24 24">
                                        <use xlink:href="#icon-edit-outline" />
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
                                  @endcan
                                  @can('masterdata.herbisidadosage.delete')
                                    <form 
                                      action="{{ url("masterdata/herbisida-dosage/{$data->companycode}/{$data->herbisidagroupid}/{$data->itemcode}") }}" 
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
                                  @endcan
                                </div>
                              </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

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

    @if (session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif
  </div>
</x-layout>

<script>
  $('#submitmodal').on('click', function() {
    $(this).attr('disabled', true);
    $(this).text('Saving...');
    $('#formcreateedit').submit();
  });
</script>