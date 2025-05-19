<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white shadow-md rounded-md">
        @include('errorfile')
        <div class="flex items-center justify-between mx-4 gap-2">
                <button onclick="openCreateModal()"
                    class="bg-blue-500 text-white px-4 py-2 text-sm border border-transparent shadow-sm font-medium rounded-md hover:bg-blue-600 flex items-center gap-2">
                    <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14m-7 7V5" />
                    </svg>
                    <span class="text-sm">New Data</span>
                </button>
            <form method="POST" action="{{ url()->current() }}" class="flex items-center justify-end gap-2">
                @csrf
                <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}" min="1"
                    onchange="this.form.submit()" autocomplete="off"
                    class="w-10 p-2 border border-gray-300 rounded-md text-xs text-center focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
            </form>
        </div>
        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">GROUP</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">KODE</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">NAMA</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">ACCNO</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">VAR1</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">VAR2</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">VAR3</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">VAR4</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">VAR5</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">MATERIAL</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">VEHICLE</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">JENIS TK</th>
                          <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                      @foreach( $actifities as $actifity )
                        <tr>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">{{ $actifity->actifitygroup }} - {{ $actifity->group->groupname }}</td>
                            <td class="text-center py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->actifitycode }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->actifityname }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->jurnalno }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->var1 }} - {{ $actifity->satuan1 }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->var2 }} - {{ $actifity->satuan2 }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->var3 }} - {{ $actifity->satuan3 }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->var4 }} - {{ $actifity->satuan4 }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->var5 }} - {{ $actifity->satuan5 }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->usingmaterial == 1 ? 'YA' : 'TIDAK' }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->usingvehicle == 1 ? 'YA' : 'TIDAK' }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}"> {{ $actifity->jenistenagakerja == 1 ? 'HARIAN' : 'BORONGAN' }}</td>
                            <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300 w-36' }}">
                                  <div class="flex items-center justify-center">
                                          <button
                                              onclick="openEditModal('{{ $actifity->actifitygroup }}','{{ $actifity->actifitycode }}', '{{ $actifity->actifityname }}', '{{ $actifity->var1 }}', '{{ $actifity->satuan1 }}', '{{ $actifity->var2 }}', '{{ $actifity->satuan2 }}', '{{ $actifity->var3 }}', '{{ $actifity->satuan3 }}', '{{ $actifity->var4 }}', '{{ $actifity->satuan4 }}', '{{ $actifity->var5 }}', '{{ $actifity->satuan5 }}', '{{ $actifity->usingmaterial }}', '{{ $actifity->usingvehicle }}', '{{ $actifity->description }}')"
                                              class="group flex items-center edit-button"><svg
                                                  class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                                  aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                  width="24" height="24" fill="none"
                                                  viewBox="0 0 24 24">
                                                  <path stroke="currentColor" stroke-linecap="round"
                                                      stroke-linejoin="round" stroke-width="2"
                                                      d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                              </svg>
                                              <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                                  aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                  width="24" height="24" fill="currentColor"
                                                  viewBox="0 0 24 24">
                                                  <path fill-rule="evenodd"
                                                      d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                                                      clip-rule="evenodd" />
                                                  <path fill-rule="evenodd"
                                                      d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                                      clip-rule="evenodd" />
                                              </svg>
                                              <span class="w-0.5"></span>
                                          </button>
                                          <button type="button" class="group flex delete-button"
                                              data-actifitycode="{{ $actifity->actifitycode }}">
                                              <svg class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
                                                  aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                  width="24" height="24" fill="none"
                                                  viewBox="0 0 24 24">
                                                  <path stroke="currentColor" stroke-linecap="round"
                                                      stroke-linejoin="round" stroke-width="2"
                                                      d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                              </svg>
                                              <svg class="w-6 h-6 text-red-500 dark:text-white hidden group-hover:block"
                                                  aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                  width="24" height="24" fill="currentColor"
                                                  viewBox="0 0 24 24">
                                                  <path fill-rule="evenodd"
                                                      d="M8.586 2.586A2 2 0 0 1 10 2h4a2 2 0 0 1 2 2v2h3a1 1 0 1 1 0 2v12a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V8a1 1 0 0 1 0-2h3V4a2 2 0 0 1 .586-1.414ZM10 6h4V4h-4v2Zm1 4a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Zm4 0a1 1 0 1 0-2 0v8a1 1 0 1 0 2 0v-8Z"
                                                      clip-rule="evenodd" />
                                              </svg>
                                          </button>
                                  </div>
                            </td>
                        </tr>
                      @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mx-4 my-1">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">Showing <span class="font-medium"></span> of <span class="font-medium"></span> results</p>
                </div>
        </div>
    </div>

    <div id="crud-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0 transform scale-95" style="opacity: 0; transform: scale(0.95);">
        <div class="relative p-4 w-full relative" style="max-width: 80rem">
            <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 transition-transform duration-300 ease-out transform">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="modal-title">Create Data</h3>
                    <button type="button" onclick="closeModal()"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <form class="p-4 md:p-5" id="crud-form" action="" method="POST">
                    @csrf
                    <input type="hidden" id="crud-method" name="_method" value="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-md">Grup Aktifitas</label>
                                <select class="rounded-md p-2 w-full border border-gray-300" id="actifitygroup" name="grupaktifitas">
                                    @foreach($actifityGroup as $group)
                                        <option value="{{ $group->actifitygroup }}" @if( old('actifitygroup') == $group->actifitygroup ) selected @endif>
                                            {{ $group->actifitygroup }} - {{ $group->groupname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-md">Kode Aktifitas</label>
                                <input type="text" name="kodeaktifitas" id="kodeaktifitas" maxlength="3"  @if(old('kodeaktifitas')) value="{{ old('kodeaktifitas') }}" @endif class="rounded-md p-2 w-full border border-gray-300" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-md">Nama Aktifitas</label>
                                <input type="text" name="namaaktifitas" id="namaaktifitas" @if(old('namaaktifitas')) value="{{ old('namaaktifitas') }}" @endif class="rounded-md p-2 w-full border border-gray-300" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-md">Menggunakan Material ?</label>
                                <label><input type="radio" name="material" value="1" @if(old('material') == 1) checked @endif>Ya</label>
                                <label><input type="radio" name="material" class="ml-6" value="0" @if(old('material') == 0) checked @else checked @endif>Tidak</label>
                            </div>
                            <div class="mb-4">
                                <label class="block text-md">Menggunakan Kendaraan ?</label>
                                <label><input type="radio" name="vehicle" value="1" @if(old('vehicle') == 1) checked @endif>Ya</label>
                                <label><input type="radio" name="vehicle" class="ml-6" value="0" @if(old('vehicle') == 0) checked @else checked @endif>Tidak</label>
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                              <label class="block text-md">Keterangan Aktifitas</label>
                              <input type="text" name="keterangan" id="keterangan" class="rounded-md p-2 w-full border border-gray-300" required>
                            </div>
                            <div>
                                <h4 class="text-2xl font-bold text-blue-800 text-center mb-4">Hasil Aktifitas</h4>
                                <div class="div-variable">
                                    <div class="flex flex-wrap gap-3 variable-row w-full mb-4">
                                        <div class="flex-1 min-w-[150px] text-center">
                                          <label class="block text-md">Var <span class="input-var">1</span></label>
                                          <input type="text" name="var[]"
                                                 class="rounded-md p-2 w-full border border-gray-300" required>
                                        </div>

                                        <!-- SATUAN 1 -->
                                        <div class="flex-1 min-w-[150px] text-center">
                                          <label class="block text-md">Satuan <span class="input-var">1</span></label>
                                          <input type="text" name="satuan[]"
                                                 class="rounded-md p-2 w-full border border-gray-300" required>
                                        </div>

                                        <!-- TOMBOL -->
                                        <div class="flex items-end hidden ">
                                          <button type="button" onclick="deleteAktifitasRow(this)"
                                            class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                                            <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                          </button>
                                        </div>
                                      </div>
                                  </div>
                                <div class="flex items-center gap-2 mb-4 text-center">
                                  <button type="button" id="btn-tambah-variable"
                                      class="bg-blue-500 text-white px-4 py-2 text-sm border border-transparent shadow-sm font-medium rounded-md hover:bg-blue-600 flex items-center gap-2">
                                      <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                          width="24" height="24" fill="none" viewBox="0 0 24 24">
                                          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 12h14m-7 7V5" />
                                      </svg>
                                      Tambah Variable
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex gap-2">
                        <button type="submit"
                            class="text-white inline-flex space-x-2 items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5" />
                            </svg>
                            <span>Save</span>
                        </button>
                        <button type="button" onclick="closeModal()"
                            class="text-white inline-flex space-x-2 items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800">
                            <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6" />
                            </svg>
                            <span>Cancel</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .invisible {
            visibility: hidden;
            pointer-events: none;
        }

        .visible {
            visibility: visible;
            pointer-events: auto;
        }
    </style>

    <script>
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Yakin ingin menghapus data ini?')) {
                  let actifitycode = this.getAttribute('data-actifitycode');
                  fetch(`{{ route('master.aktifitas.destroy', ['aktifitas' => '__actifitycode__']) }}`
                          .replace('__actifitycode__', actifitycode), {
                              method: 'POST',
                              headers: {
                                  'X-CSRF-TOKEN': document.querySelector(
                                          'meta[name="csrf-token"]')
                                      .getAttribute('content'),
                                  'Content-Type': 'application/json'
                              },
                              body: JSON.stringify({
                                  _method: 'DELETE'
                              })
                          })
                      .then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              location.reload();
                          } else {
                              alert('Gagal menghapus data');
                          }
                      })
                      .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
    <script>
        const modal = document.getElementById("crud-modal");
        const form = document.getElementById("crud-form");
        const modalTitle = document.getElementById("modal-title");
        const crudMethod = document.getElementById("crud-method");

        function openCreateModal() {
            modal.classList.remove("invisible");
            modal.classList.add("visible");
            modalTitle.textContent = "Create Data";
            form.action = "{{ route('master.aktifitas.store') }}";
            crudMethod.value = "POST";
            $('input[name="nama"]').attr('readonly','false');
            setTimeout(() => {
                modal.style.opacity = "1";
                modal.style.transform = "scale(1)";
            }, 50);
        }

        function closeModal() {
            modal.style.opacity = "0";
            modal.style.transform = "scale(0.95)";

            setTimeout(() => {
                modal.classList.add("invisible");
                modal.classList.remove("visible");
            }, 300);
        }

        function openEditModal(actifitygroup,actifitycode,actifityname,var1,satuan1,var2,satuan2,var3,satuan3,var4,satuan4,var5,satuan5,usingvehicle,usingmaterial,keterangan) {
            resetRow();
            modal.classList.remove("invisible");
            modal.classList.add("visible");
            modalTitle.textContent = "Edit Data";
            var editRoute = "{{ route('master.aktifitas.update', ['aktifitas' => '__actifitycode__']) }}";
            const form = $('#crud-form');
            form.attr('action', editRoute.replace('__actifitycode__', actifitycode));
            form.find('input[name="kodeaktifitas"]').attr('readonly','true');
            form.find('input[name="_method"]').val('PUT');
            form.find('input[name="grupaktifitas"]').val(actifitygroup).trigger('change');
            form.find('input[name="kodeaktifitas"]').val(actifitycode)
            form.find('input[name="namaaktifitas"]').val(actifityname)
            if( usingmaterial == 1 ){
              form.find('input[name="material"][value="1"]').prop('checked', true)
            }else{
              form.find('input[name="material"][value="0"]').prop('checked', true)
            }

            if( usingvehicle == 1 ){
              form.find('input[name="vehicle"][value="1"]').prop('checked', true)
            }else{
              form.find('input[name="vehicle"][value="0"]').prop('checked', true)
            }
            form.find('input[name="keterangan"]').val(keterangan)
            form.find('input[name="var[]"]').eq(0).val(var1)
            form.find('input[name="satuan[]"]').eq(0).val(satuan1)
            if( var2 != '' ){
              $('#btn-tambah-variable').click();
              form.find('input[name="var[]"]').eq(1).val(var2)
              form.find('input[name="satuan[]"]').eq(1).val(satuan2)
            }
            if( var3 != '' ){
              $('#btn-tambah-variable').click();
              form.find('input[name="var[]"]').eq(2).val(var3)
              form.find('input[name="satuan[]"]').eq(2).val(satuan3)
            }

            if( var4 != '' ){
              $('#btn-tambah-variable').click();
              form.find('input[name="var[]"]').eq(3).val(var3)
              form.find('input[name="satuan[]"]').eq(3).val(satuan3)
            }

            if( var5 != '' ){
              $('#btn-tambah-variable').click();
              form.find('input[name="var[]"]').eq(4).val(var4)
              form.find('input[name="satuan[]"]').eq(4).val(satuan4)
            }

            setTimeout(() => {
                modal.style.opacity = "1";
                modal.style.transform = "scale(1)";
            }, 50);
        }

        function deleteRow(kdblok, kdComp, row) {
            if (confirm("Yakin ingin menghapus data ini?")) {
                fetch(`{{ route('master.blok.destroy', ['blok' => '__kdblok__', 'companycode' => '__kdComp__']) }}`
                        .replace('__kdblok__', kdblok)
                        .replace('__kdComp__', kdComp), {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            row.remove();
                        } else {
                            alert("Terjadi kesalahan: " + data.message);
                        }
                    })
                    .catch(error => console.error("Error:", error));
            }
        }

        function resetRow()
        {
          $('.variable-row').each(function(i, v){
            if( i > 0 ){
              $('.variable-row').eq(i).remove();
            }
          })
        }


        document.addEventListener("DOMContentLoaded", () => {
            const inputElement = document.getElementById("perPage");

            inputElement.addEventListener("input", (event) => {
                event.target.value = event.target.value.replace(/[^0-9]/g, '');
            });
        });

        $('#btn-tambah-variable').click(function(){
          if( $('.variable-row').length < 5 ){
            var temp = $('.div-variable .variable-row').first().clone();
            $(temp).find('.item-end').removeClass('hidden');
            $(temp).find('input').val('')
            var count = $('.div-variable .variable-row').length+1;
            $(temp).find('.input-var').html(count);
            $(temp).find('.items-end').removeClass('hidden');
            $('.div-variable').append(temp);
          }else{
            alert('Maximal 5, apa bila membutuhkan lebih, hubungi IT');
          }
        });

        function deleteAktifitasRow(div)
        {
          $(div).parent().parent().remove();
           setVariableLable()
        }


        function setVariableLable()
        {
          let counter = $('.div-variable .variable-row').length;
          $('.variable-row').each(function(i, v){
            $(this).find('.input-var').html(i+1)
          })

        }
    </script>

</x-layout>
