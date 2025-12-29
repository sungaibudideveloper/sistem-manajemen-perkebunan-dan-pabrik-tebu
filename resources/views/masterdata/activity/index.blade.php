<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-1 bg-white shadow-md rounded-md">
        
        <div class="flex items-center justify-between px-4 py-2">
            @can('masterdata.aktivitas.create')
                <button onclick="openCreateModal()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                    </svg>
                    New Data
                </button>
            @endcan

            {{-- Search Form --}}
            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Kode, Nama, atau Group"
                       class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       onkeydown="if(event.key==='Enter') this.form.submit()">
            </form>

            {{-- Items per page --}}
            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
                <select name="perPage" id="perPage" onchange="this.form.submit()"
                        class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ (int)request('perPage', $perPage) === 100 ? 'selected' : '' }}>100</option>
                </select>
            </form>
        </div>

        <div class="mx-auto px-4 py-2">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="py-2 px-4 border-b">No.</th>
                            <th class="py-2 px-4 border-b">GROUP</th>
                            <th class="py-2 px-4 border-b">KODE</th>
                            <th class="py-2 px-4 border-b">NAMA</th>
                            <th class="py-2 px-4 border-b">VAR1</th>
                            <th class="py-2 px-4 border-b">VAR2</th>
                            <th class="py-2 px-4 border-b">VAR3</th>
                            <th class="py-2 px-4 border-b">VAR4</th>
                            <th class="py-2 px-4 border-b">VAR5</th>
                            <th class="py-2 px-4 border-b">MATERIAL</th>
                            <th class="py-2 px-4 border-b">VEHICLE</th>
                            <th class="py-2 px-4 border-b">JENIS TK</th>
                            <th class="py-2 px-4 border-b">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $index => $activity)
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-4 border-b text-center">{{ $activities->firstItem() + $index }}</td>
                                <td class="py-2 px-4 border-b">{{ $activity->activitygroup }} - {{ $activity->group->groupname ?? '-' }}</td>
                                <td class="py-2 px-4 border-b text-center">{{ $activity->activitycode }}</td>
                                <td class="py-2 px-4 border-b">{{ $activity->activityname }}</td>
                                <td class="py-2 px-4 border-b">{{ $activity->var1 ? $activity->var1 . ' - ' . $activity->satuan1 : '-' }}</td>
                                <td class="py-2 px-4 border-b">{{ $activity->var2 ? $activity->var2 . ' - ' . $activity->satuan2 : '-' }}</td>
                                <td class="py-2 px-4 border-b">{{ $activity->var3 ? $activity->var3 . ' - ' . $activity->satuan3 : '-' }}</td>
                                <td class="py-2 px-4 border-b">{{ $activity->var4 ? $activity->var4 . ' - ' . $activity->satuan4 : '-' }}</td>
                                <td class="py-2 px-4 border-b">{{ $activity->var5 ? $activity->var5 . ' - ' . $activity->satuan5 : '-' }}</td>
                                <td class="py-2 px-4 border-b text-center">{{ $activity->usingmaterial == 1 ? 'YA' : 'TIDAK' }}</td>
                                <td class="py-2 px-4 border-b text-center">{{ $activity->usingvehicle == 1 ? 'YA' : 'TIDAK' }}</td>
                                <td class="py-2 px-4 border-b text-center">{{ $activity->jenistenagakerja == 1 ? 'HARIAN' : 'BORONGAN' }}</td>
                                <td class="py-2 px-4 border-b">
                                    <div class="flex items-center justify-center space-x-2">
                                        @can('masterdata.aktivitas.edit')
                                            <button onclick='openEditModal(@json($activity))'
                                                class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm">
                                                <svg class="w-6 h-6 text-blue-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-edit-outline"/>
                                                </svg>
                                                <svg class="w-6 h-6 text-blue-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-edit-solid"/>
                                                </svg>
                                            </button>
                                        @endcan
                                        @can('masterdata.aktivitas.delete')
                                            <button type="button" class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm delete-button"
                                                data-activitycode="{{ $activity->activitycode }}">
                                                <svg class="w-6 h-6 text-red-500 group-hover:hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-trash-outline"/>
                                                </svg>
                                                <svg class="w-6 h-6 text-red-500 hidden group-hover:block" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                                    <use xlink:href="#icon-trash-solid"/>
                                                </svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="py-4 text-center text-gray-500">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mx-4 my-1">
            {{ $activities->appends(['perPage' => $activities->perPage(), 'search' => $search])->links() }}
        </div>
    </div>

    {{-- Modal Form --}}
    <div id="crud-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-500 bg-opacity-75">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl">
                
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900" id="modal-title">Create Data</h3>
                    <button type="button" onclick="closeModal()"
                            class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <form id="crud-form" method="POST" class="px-6 py-4">
                    @csrf
                    <input type="hidden" id="crud-method" name="_method" value="POST">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Left Column --}}
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Grup Aktivitas <span class="text-red-500">*</span></label>
                                <select name="grupaktivitas" required
                                        class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Pilih Group --</option>
                                    @foreach($activityGroup as $group)
                                        <option value="{{ $group->activitygroup }}">{{ $group->activitygroup }} - {{ $group->groupname }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Kode Aktivitas <span class="text-red-500">*</span></label>
                                <input type="text" name="kodeaktivitas" maxlength="3" required
                                       class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 uppercase"
                                       placeholder="Max 3 karakter">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Aktivitas <span class="text-red-500">*</span></label>
                                <input type="text" name="namaaktivitas" required
                                       class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan</label>
                                <textarea name="keterangan" rows="2" maxlength="150"
                                       class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Material? <span class="text-red-500">*</span></label>
                                    <div class="flex gap-3">
                                        <label class="inline-flex items-center text-sm">
                                            <input type="radio" name="material" value="1" class="mr-1"> Ya
                                        </label>
                                        <label class="inline-flex items-center text-sm">
                                            <input type="radio" name="material" value="0" checked class="mr-1"> Tidak
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Kendaraan? <span class="text-red-500">*</span></label>
                                    <div class="flex gap-3">
                                        <label class="inline-flex items-center text-sm">
                                            <input type="radio" name="vehicle" value="1" class="mr-1"> Ya
                                        </label>
                                        <label class="inline-flex items-center text-sm">
                                            <input type="radio" name="vehicle" value="0" checked class="mr-1"> Tidak
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Jenis TK <span class="text-red-500">*</span></label>
                                <div class="flex gap-3">
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" name="jenistenagakerja" value="1" checked class="mr-1"> Harian
                                    </label>
                                    <label class="inline-flex items-center text-sm">
                                        <input type="radio" name="jenistenagakerja" value="2" class="mr-1"> Borongan
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column - Variables --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-medium text-gray-700">Hasil Aktivitas (Max 5)</label>
                                <button type="button" id="btn-tambah-variable"
                                        class="text-xs bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah
                                </button>
                            </div>
                            
                            <div class="div-variable space-y-2 max-h-80 overflow-y-auto pr-2">
                                <div class="variable-row flex gap-2 items-start">
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-600 mb-1">Var <span class="input-var">1</span></label>
                                        <input type="text" name="var[]" required
                                               class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="flex-1">
                                        <label class="block text-xs text-gray-600 mb-1">Satuan</label>
                                        <input type="text" name="satuan[]" required
                                               class="text-sm block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div class="pt-6 hidden item-end">
                                        <button type="button" onclick="deleteAktivitasRow(this)"
                                                class="text-red-600 hover:text-red-800 p-1">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-6 flex justify-end gap-2 pt-4 border-t border-gray-200">
                        <button type="button" onclick="closeModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Toast --}}
    @if(session('success'))
        <script>alert('{{ session('success') }}');</script>
    @endif

    @if($errors->any())
        <script>alert('{{ $errors->first() }}');</script>
    @endif

    <script>
        const modal = document.getElementById('crud-modal');
        const form = document.getElementById('crud-form');

        function openCreateModal() {
            resetRow();
            resetForm();
            document.getElementById('modal-title').textContent = "Create Data";
            form.action = "{{ route('masterdata.aktivitas.store') }}";
            document.getElementById('crud-method').value = "POST";
            form.querySelector('input[name="kodeaktivitas"]').removeAttribute('readonly');
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        function openEditModal(activity) {
            resetRow();
            resetForm();
            document.getElementById('modal-title').textContent = "Edit Data";
            
            const editRoute = "{{ route('masterdata.aktivitas.update', ['aktivitas' => '__activitycode__']) }}";
            form.action = editRoute.replace('__activitycode__', activity.activitycode);
            document.getElementById('crud-method').value = 'PUT';
            
            form.querySelector('select[name="grupaktivitas"]').value = activity.activitygroup;
            form.querySelector('input[name="kodeaktivitas"]').value = activity.activitycode;
            form.querySelector('input[name="kodeaktivitas"]').setAttribute('readonly', 'true');
            form.querySelector('input[name="namaaktivitas"]').value = activity.activityname;
            form.querySelector('textarea[name="keterangan"]').value = activity.description || '';
            
            form.querySelector(`input[name="material"][value="${activity.usingmaterial}"]`).checked = true;
            form.querySelector(`input[name="vehicle"][value="${activity.usingvehicle}"]`).checked = true;
            form.querySelector(`input[name="jenistenagakerja"][value="${activity.jenistenagakerja}"]`).checked = true;
            
            const vars = [
                {var: activity.var1, satuan: activity.satuan1},
                {var: activity.var2, satuan: activity.satuan2},
                {var: activity.var3, satuan: activity.satuan3},
                {var: activity.var4, satuan: activity.satuan4},
                {var: activity.var5, satuan: activity.satuan5}
            ];
            
            vars.forEach((item, index) => {
                if (item.var) {
                    if (index > 0) document.getElementById('btn-tambah-variable').click();
                    form.querySelectorAll('input[name="var[]"]')[index].value = item.var;
                    form.querySelectorAll('input[name="satuan[]"]')[index].value = item.satuan;
                }
            });
            
            modal.classList.remove('hidden');
        }

        function resetRow() {
            const rows = document.querySelectorAll('.variable-row');
            rows.forEach((row, index) => { if (index > 0) row.remove(); });
        }

        function resetForm() {
            form.reset();
            form.querySelector('input[name="material"][value="0"]').checked = true;
            form.querySelector('input[name="vehicle"][value="0"]').checked = true;
            form.querySelector('input[name="jenistenagakerja"][value="1"]').checked = true;
        }

        document.getElementById('btn-tambah-variable').addEventListener('click', function() {
            const rows = document.querySelectorAll('.variable-row');
            if (rows.length < 5) {
                const firstRow = document.querySelector('.variable-row');
                const newRow = firstRow.cloneNode(true);
                newRow.querySelector('.item-end').classList.remove('hidden');
                newRow.querySelectorAll('input').forEach(input => input.value = '');
                const count = rows.length + 1;
                newRow.querySelectorAll('.input-var').forEach(span => span.textContent = count);
                document.querySelector('.div-variable').appendChild(newRow);
            } else {
                alert('Maksimal 5 variable hasil aktivitas');
            }
        });

        function deleteAktivitasRow(btn) {
            btn.closest('.variable-row').remove();
            document.querySelectorAll('.variable-row').forEach((row, index) => {
                row.querySelectorAll('.input-var').forEach(span => span.textContent = index + 1);
            });
        }

        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function() {
                if (confirm('Yakin ingin menghapus data ini?')) {
                    const activitycode = this.getAttribute('data-activitycode');
                    const deleteRoute = "{{ route('masterdata.aktivitas.destroy', ['aktivitas' => '__activitycode__']) }}";
                    
                    fetch(deleteRoute.replace('__activitycode__', activitycode), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({_method: 'DELETE'})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) location.reload();
                        else alert(data.message || 'Gagal menghapus data');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus data');
                    });
                }
            });
        });

        // Auto uppercase
        form.querySelector('input[name="kodeaktivitas"]').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase();
        });

        // Close modal on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
    </script>
</x-layout>