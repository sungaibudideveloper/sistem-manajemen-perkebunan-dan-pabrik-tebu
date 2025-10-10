<x-layout>
  <x-slot:title>{{ $title }}</x-slot:title>
  <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
  <x-slot:nav>{{ $nav }}</x-slot:nav>

  <div
    x-data="{
      open: @json($errors->any()),
      mode: 'create',
      form: { companycode: '', id: null, name: '' },
      resetForm() {
        this.mode = 'create';
        this.form = { companycode: '', id: null, name: '' };
        this.open = true;
      }
    }"
    class="mx-auto py-1 bg-white rounded-md shadow-md"
  >

    <div class="flex items-center justify-between px-4 py-2">
      {{-- Create Button --}}
      @if(hasPermission('Create Mandor'))
        <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
          <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
          </svg>
          New Data
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

      {{-- Items per page --}}
      <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
        <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
        <select
          name="perPage"
          id="perPage"
          onchange="this.form.submit()"
          class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
          <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
          <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
        </select>
      </form>

      {{-- Modal Form --}}
      <div x-show="open" x-cloak class="relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-gray-500/75" aria-hidden="true"></div>
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
              class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg"
            >
              <form method="POST"
                    :action="mode === 'edit'
                      ? '{{ url('masterdata/mandor') }}/' + form.companycode + '/' + form.id
                      : '{{ url('masterdata/mandor') }}'"
                    class="bg-white px-4 pt-2 pb-4 sm:p-6 sm:pt-1 sm:pb-4 space-y-6">
                @csrf
                <template x-if="mode === 'edit'">
                  <input type="hidden" name="_method" value="PATCH">
                </template>

                <div class="text-center sm:text-left">
                  <h3
                    class="text-lg font-medium text-gray-900"
                    id="modal-title"
                    x-text="mode === 'edit' ? 'Edit Mandor' : 'Create Mandor'"
                  ></h3>
                  @include('errorfile')
                  <div class="mt-4 space-y-4">
                  <template x-if="mode === 'edit'">
                    <div>
                      <label for="id" class="block text-sm font-medium text-gray-700">ID Mandor</label>
                      <input
                        type="text"
                        name="id"
                        id="id"
                        x-model="form.id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled"
                        required
                        readonly
                      >
                      @error('id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                      @enderror
                    </div>
                  </template>
                    <div>
                      <label for="name" class="block text-sm font-medium text-gray-700">Nama Mandor</label>
                      <input
                        type="text"
                        name="name"
                        id="name"
                        x-model="form.name"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        maxlength="50"
                        required
                        x-init="form.name = '{{ old('name') }}'"
                      >
                      @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                      @enderror
                    </div>
                    <template x-if="mode === 'edit'">
                      <div>
                        <label for="id" class="block text-sm font-medium text-gray-700">Active</label>
                        <input
                          type="checkbox"
                          name="isactive"
                          id="isactive"
                          :checked="form.isactive == 1"
                          class="mt-1  border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 disabled"
                          value="1"
                        >
                        @error('id')
                          <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                      </div>
                    </template>
                  </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                  <button
                    type="submit"
                    class="inline-flex w-full justify-center rounded-md bg-blue-600 px-4 py-2 text-white text-sm font-medium shadow-sm hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto"
                    x-text="mode === 'edit' ? 'Update' : 'Create'"
                  >
                    Save
                  </button>
                  <button
                    @click.prevent="open = false"
                    type="button"
                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-gray-700 text-sm font-medium shadow-sm ring-1 ring-gray-300 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto"
                  >
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
        <table class="table-fixed w-full bg-white text-sm text-center">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="w-2/12 py-2 px-4 border-b">Company</th>
                    <th class="w-1/12 py-2 px-4 border-b">ID</th>
                    <th class="w-6/12 py-2 px-4 border-b">Nama Mandor</th>
                    <th class="w-6/12 py-2 px-4 border-b">Status</th>
                    <th class="w-3/12 py-2 px-4 border-b">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($mandor as $data)
              <tr class="hover:bg-gray-50">
                <td class="py-2 px-4 border-b">{{ $data->companycode }}</td>
                <td class="py-2 px-4 border-b">{{ $data->userid }}</td>
                <td class="py-2 px-4 border-b">{{ $data->name }}</td>
                <td class="py-2 px-4 border-b">{{ $data->isactive == 1 ? 'Aktif' : 'Tidak Aktif' }}</td>
                <td class="py-2 px-4 border-b">
                  <div class="flex items-center justify-center space-x-2">
                    {{-- Edit --}}
                    @if(hasPermission('Edit Mandor'))
                      <button
                        @click="
                          mode = 'edit';
                          form.companycode = '{{ $data->companycode }}';
                          form.id = '{{ $data->userid }}';
                          form.name = '{{ $data->name }}';
                          form.isactive = {{ $data->isactive ?? 0 }} == 1 ? 1 : 0;
                          open = true
                        "
                        class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm"
                      >
                        <svg class="w-6 h-6 text-blue-500 group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                          <use xlink:href="#icon-edit-outline"/>
                        </svg>
                        <svg class="w-6 h-6 text-blue-500 hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                          <use xlink:href="#icon-edit-solid"/> <use xlink:href="#icon-edit-solid2" />
                        </svg>
                      </button>
                    @endif

                    {{-- Delete --}}
                    @if(hasPermission('Hapus Mandor'))
                      <form
                        action="{{ url("masterdata/mandor/{$data->companycode}/{$data->userid}") }}"
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
                          <svg class="w-6 h-6 text-red-500 group-hover:hidden" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-outline"/>
                          </svg>
                          <svg class="w-6 h-6 text-red-500 hidden group-hover:block" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                            <use xlink:href="#icon-trash-solid"/>
                          </svg>
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
        </table>
      </div>
    </div>

    {{-- Pagination --}}
    <div class="mx-4 my-1">
      @if($mandor->hasPages())
        {{ $mandor->appends(['perPage' => $mandor->perPage(), 'search' => $search])->links() }}
      @else
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ $mandor->count() }}</span> of <span class="font-medium">{{ $mandor->total() }}</span> results
          </p>
        </div>
      @endif
    </div>

    {{-- Toast --}}
    @if(session('success'))
      <div x-data x-init="alert('{{ session('success') }}')"></div>
    @endif

  </div>
</x-layout>
