<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        <div class="flex items-center justify-between mx-4 gap-2">
            @if (auth()->user() && in_array('Create Plotting', json_decode(auth()->user()->permissions ?? '[]')))
                <button onclick="openCreateModal()"
                    class="bg-blue-500 text-white px-4 py-2 text-sm border border-transparent shadow-sm font-medium rounded-md hover:bg-blue-600 flex items-center gap-2">
                    <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14m-7 7V5" />
                    </svg>
                    <span class="text-sm">New Data</span>
                </button>
                </a>
            @endif
            <form method="POST" action="{{ url()->current() }}" class="flex items-center justify-end gap-2">
                @csrf
                <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}" min="1"
                    onchange="this.form.submit()"
                    class="w-10 p-2 border border-gray-300 rounded-md text-xs text-center focus:ring-1 focus:ring-blue-500 focus:border-blue-500" />
            </form>
        </div>

        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-1">No.</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Plot
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Luas Area
                            </th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Jarak
                                Tanam</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kode
                                Company</th>
                            @if (auth()->user() &&
                                    collect(json_decode(auth()->user()->permissions ?? '[]'))->intersect(['Edit Plotting', 'Hapus Plotting'])->isNotEmpty())
                                <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-36">Aksi
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($plotting as $item)
                            <tr>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300 w-1' }}">
                                    {{ $item->no }}.</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->kd_plot }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->luas_area }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->jarak_tanam }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->kd_comp }}</td>
                                @if (auth()->user() &&
                                        collect(json_decode(auth()->user()->permissions ?? '[]'))->intersect(['Edit Plotting', 'Hapus Plotting'])->isNotEmpty())
                                    <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300 w-36' }}">
                                        <div class="flex items-center justify-center">
                                            @if (auth()->user() && in_array('Edit Plotting', json_decode(auth()->user()->permissions ?? '[]')))
                                                <button
                                                    onclick="openEditModal('{{ $item->kd_plot }}', '{{ $item->luas_area }}', '{{ $item->jarak_tanam }}', '{{ $item->kd_comp }}')"
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
                                            @endif
                                            @if (auth()->user() && in_array('Hapus Plotting', json_decode(auth()->user()->permissions ?? '[]')))
                                                <form class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="group flex delete-button"
                                                        data-kd-plot="{{ $item->kd_plot }}"
                                                        data-kd-comp="{{ $item->kd_comp }}">
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
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mx-4 my-1">
            @if ($plotting->hasPages())
                {{ $plotting->appends(['perPage' => $plotting->perPage()])->links() }}
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ $plotting->count() }}</span> of <span
                            class="font-medium">{{ $plotting->total() }}</span> results
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div id="crud-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0 transform scale-95"
        style="opacity: 0; transform: scale(0.95);">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div
                class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700 transition-transform duration-300 ease-out transform">
                <div
                    class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
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
                    <input type="hidden" name="_method" id="crud-method" value="POST">
                    <input type="hidden" name="kd_comp" id="kd_comp" value="{{ session('dropdown_value') }}">

                    <div class="mb-4">
                        <label class="block text-md">Plot</label>
                        <input type="text" name="kd_plot" id="kd_plot" value="" autocomplete="off"
                            maxlength="5" class="rounded-md p-2 w-full max-w-[20ch] border-gray-300" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-md">Luas Area</label>
                        <input type="number" id="luas_area" name="luas_area" autocomplete="off" max="9999.99"
                            value="" min="0" step="0.01"
                            class="rounded-md p-2 w-full max-w-[20ch] border-gray-300" required inputmode="decimal"
                            pattern="[0-9]+([.][0-9]+)?" onkeypress="return hanyaAngka(event)">
                    </div>

                    <div class="mb-4">
                        <label class="block text-md">Jarak Tanam</label>
                        <input type="number" id="jarak_tanam" name="jarak_tanam" autocomplete="off" value=""
                            min="0" class="rounded-md p-2 w-full max-w-[20ch] border-gray-300" required
                            inputmode="numeric" pattern="[0-9]*" onkeypress="return hanyaAngka(event)">
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
                let kdPlot = this.getAttribute('data-kd-plot');
                let kdComp = this.getAttribute('data-kd-comp');
                if (confirm('Yakin ingin menghapus data ini?')) {
                    fetch(`{{ route('master.plotting.destroy', ['kd_plot' => '__kdPlot__', 'kd_comp' => '__kdComp__']) }}`
                            .replace('__kdPlot__', kdPlot)
                            .replace('__kdComp__', kdComp), {
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
        const kdPlotInput = document.getElementById("kd_plot");
        const luasAreaInput = document.getElementById("luas_area");
        const jarakTanamInput = document.getElementById("jarak_tanam");
        const kdCompInput = document.getElementById("kd_comp");
        const crudMethod = document.getElementById("crud-method");

        function openCreateModal() {
            modal.classList.remove("invisible");
            modal.classList.add("visible");
            modalTitle.textContent = "Create Data";
            form.action = "{{ route('master.plotting.handle') }}";
            crudMethod.value = "POST";
            kdPlotInput.value = "";
            luasAreaInput.value = "";
            jarakTanamInput.value = "";
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

        function openEditModal(kdPlot, luasArea, jarakTanam, kdComp) {
            modal.classList.remove("invisible");
            modal.classList.add("visible");
            modalTitle.textContent = "Edit Data";
            var editRoute =
                "{{ route('master.plotting.update', ['kd_plot' => '__kdPlot__', 'kd_comp' => '__kdComp__']) }}";
            form.action = editRoute.replace('__kdPlot__', kdPlot).replace('__kdComp__', kdComp);
            crudMethod.value = "PUT";
            kdPlotInput.value = kdPlot;
            luasAreaInput.value = luasArea;
            jarakTanamInput.value = jarakTanam;
            kdCompInput.value = kdComp;
            setTimeout(() => {
                modal.style.opacity = "1";
                modal.style.transform = "scale(1)";
            }, 50);
        }

        function deleteRow(kdPlot, kdComp, row) {
            if (confirm("Yakin ingin menghapus data ini?")) {
                fetch(`{{ route('master.plotting.destroy', ['kd_plot' => '__kdPlot__', 'kd_comp' => '__kdComp__']) }}`
                        .replace('__kdPlot__', kdPlot)
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

        form.addEventListener("submit", function(event) {
            if (crudMethod.value === "POST") {
                event.preventDefault();
            }
            const formData = new FormData(form);
            fetch(form.action, {
                    method: crudMethod.value,
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Data berhasil disimpan!");
                        updateTable(data.newData);
                        form.reset();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        });

        function updateTable(newData) {
            const tableBody = document.querySelector("tbody");

            let newRow = document.createElement("tr");
            newRow.innerHTML = `
                <td class="py-2 px-4 border-b border-gray-300 w-fit">
                    <span class="bg-red-600 text-white text-xs rounded-md font-medium py-0.5 px-1 w-fit">
                        ${newData.no}
                    </span>
                </td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.kd_plot}</td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.luas_area}</td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.jarak_tanam}</td>
                <td class="py-2 px-4 border-b border-gray-300">${newData.kd_comp}</td>
                @if (auth()->user() &&
                        collect(json_decode(auth()->user()->permissions ?? '[]'))->intersect(['Edit Plotting', 'Hapus Plotting'])->isNotEmpty())
                <td class="py-2 px-4 border-b border-gray-300">
                    <div class="flex items-center justify-center">
                        @if (auth()->user() && in_array('Edit Plotting', json_decode(auth()->user()->permissions ?? '[]')))
                        <button class="group flex items-center edit-button" onclick="openEditModal('${newData.kd_plot}','${newData.luas_area}','${newData.jarak_tanam}','${newData.kd_comp}')">
                            <svg
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
                        </button>
                        <span class="w-0.5"></span>
                        @endif
                        @if (auth()->user() && in_array('Hapus Plotting', json_decode(auth()->user()->permissions ?? '[]')))
                        <button class="group flex delete-button" data-kd_plot="${newData.kd_plot}" data-kd_comp="${newData.kd_comp}">
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
                        @endif
                    </div>
                </td>
                @endif
            `;
            tableBody.prepend(newRow);
            newRow.querySelector(".delete-button").addEventListener("click", function() {
                deleteRow(this.dataset.kd_plot, this.dataset.kd_comp, newRow);
            });
        }
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputElement = document.getElementById("perPage");

            inputElement.addEventListener("input", (event) => {
                event.target.value = event.target.value.replace(/[^0-9]/g, '');
            });
        });
    </script>
    <script>
        document.getElementById('luas_area').addEventListener('input', function(e) {
            const value = e.target.value;

            if (!/^\d{0,2}(\.\d{0,2})?$/.test(value)) {
                e.target.value = value.slice(0, -1);
            }
        });
        document.getElementById('jarak_tanam').addEventListener('input', function(e) {
            const value = e.target.value;

            if (!/^\d{0,3}(\.\d{0,0})?$/.test(value)) {
                e.target.value = value.slice(0, -1);
            }
        });
    </script>

    <script>
        function hanyaAngka(event) {
            let charCode = event.which ? event.which : event.keyCode;
            if (charCode !== 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
                event.preventDefault();
                return false;
            }
            return true;
        }
    </script>
</x-layout>
