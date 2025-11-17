<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $title }}</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md w-full">
        <div class="flex mx-4 items-center gap-2 flex-wrap lg:justify-between justify-center">
            <div class="flex gap-2 text-sm">
                <button type="button" id="openModalBtn"
                    class="bg-blue-600 text-white px-4 py-2 text-sm border border-transparent shadow-sm font-medium rounded-md hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-white dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14m-7 7V5" />
                    </svg>
                    <span>Create RKM</span>
                </button>
                <button
                    class="bg-green-600 text-white px-4 py-2 border border-transparent shadow-sm rounded-md font-medium hover:bg-green-500 flex items-center space-x-2"
                    onclick="window.location.href='{{ route('input.rencana-kerja-mingguan.exportExcel', ['start_date' => old('start_date', request()->start_date), 'end_date' => old('end_date', request()->end_date)]) }}'">
                    <svg class="w-5 h-5 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd"
                            d="M9 7V2.221a2 2 0 0 0-.5.365L4.586 6.5a2 2 0 0 0-.365.5H9Zm2 0V2h7a2 2 0 0 1 2 2v9.293l-2-2a1 1 0 0 0-1.414 1.414l.293.293h-6.586a1 1 0 1 0 0 2h6.586l-.293.293A1 1 0 0 0 18 16.707l2-2V20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9h5a2 2 0 0 0 2-2Z"
                            clip-rule="evenodd" />
                    </svg>
                    <span>Export to Excel</span>
                </button>
            </div>

            <form method="POST" action="{{ route('input.rencana-kerja-mingguan.index') }}">
                @csrf
                <div class="flex items-center gap-2 flex-wrap justify-center">
                    <div id="ajax-data" data-url="{{ route('input.rencana-kerja-mingguan.index') }}">
                        <div class="flex items-center gap-2 w-full">
                            <div>
                                <label for="perPage" class="text-sm font-medium text-gray-700">Items per
                                    page:</label>
                                <input type="text" name="perPage" id="perPage" value="{{ $perPage }}"
                                    min="1" autocomplete="off"
                                    class="w-10 p-2 border border-gray-300 rounded-md text-sm text-center focus:ring-blue-500 focus:border-blue-500" />
                            </div>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <input type="text" id="search" autocomplete="off" name="search"
                            value="{{ old('search', $search) }}"
                            class="block w-[350px] p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search No.RKM, or Activity Code..." />
                    </div>
                </div>
            </form>
        </div>

        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm text-center" id="tables">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700 w-1">No.</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">No. RKM</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">RKM Date</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Start Date</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">End Date</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Kode Aktivitas</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Nama Aktivitas</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100 text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rkm as $item)
                            <tr>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-1">
                                    {{ $item->no }}.</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->rkmno }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->rkmdate }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->startdate ?? '' }}
                                </td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->enddate ?? '' }}
                                </td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->activitycode ?? '' }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }}">
                                    {{ $item->activityname ?? '' }}</td>
                                <td class="py-2 px-4 {{ $loop->last ? '' : 'border-b border-gray-300' }} w-40">
                                    <div class="flex items-center justify-center">
                                        <button class="group flex items-center"
                                            onclick="showList('{{ $item->rkmno }}')"><svg
                                                class="w-6 h-6 text-gray-500 dark:text-white group-hover:hidden"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-width="2"
                                                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                                <path stroke="currentColor" stroke-width="2"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            <svg class="w-6 h-6 text-gray-500 dark:text-white hidden group-hover:block"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span class="w-2"></span>
                                        </button>

                                        <a href="{{ route('input.rencana-kerja-mingguan.edit', ['rkmno' => $item->rkmno]) }}"
                                            class="group flex items-center"><svg
                                                class="w-6 h-6 text-blue-500 dark:text-white group-hover:hidden"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="2"
                                                    d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                            </svg>
                                            <svg class="w-6 h-6 text-blue-500 dark:text-white hidden group-hover:block"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                                                height="24" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z"
                                                    clip-rule="evenodd" />
                                                <path fill-rule="evenodd"
                                                    d="M19.846 4.318a2.148 2.148 0 0 0-.437-.692 2.014 2.014 0 0 0-.654-.463 1.92 1.92 0 0 0-1.544 0 2.014 2.014 0 0 0-.654.463l-.546.578 2.852 3.02.546-.579a2.14 2.14 0 0 0 .437-.692 2.244 2.244 0 0 0 0-1.635ZM17.45 8.721 14.597 5.7 9.82 10.76a.54.54 0 0 0-.137.27l-.536 2.84c-.07.37.239.696.588.622l2.682-.567a.492.492 0 0 0 .255-.145l4.778-5.06Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span class="w-0.5"></span>
                                        </a>

                                        <form
                                            action="{{ route('input.rencana-kerja-mingguan.destroy', ['rkmno' => $item->rkmno]) }}"
                                            method="POST" class="inline">@csrf @method('DELETE')
                                            <button type="submit" class="group flex"
                                                onclick="return confirm('Yakin ingin menghapus data ini?')"><svg
                                                    class="w-6 h-6 text-red-500 dark:text-white group-hover:hidden"
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
                                                <span class="w-0.5"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mx-4 mt-1" id="pagination-links">
            @if ($rkm->hasPages())
                {{ $rkm->appends(['perPage' => $rkm->perPage()])->links() }}
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Showing <span class="font-medium">{{ $rkm->count() }}</span> of <span
                            class="font-medium">{{ $rkm->total() }}</span> results
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    <div id="targetDateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="modal-content bg-white rounded-lg shadow-lg w-11/12 md:w-1/3">
            <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-green-50 to-emerald-50">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Create RKM Baru</h2>
                </div>
                <button id="closeModalBtn"
                    class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
            </div>

            <form id="targetDateForm">
                <div class="p-6 space-y-4">
                    <div>
                        <label for="targetDate" class="block text-sm font-medium text-gray-700 mb-2">Tanggal
                            RKM:</label>
                        <input type="date" id="targetDate" name="targetDate" required
                            class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Pilih tanggal untuk membuat RKM (maksimal 7 hari ke
                            depan)</p>
                        <p id="errorMessage" class="text-red-500 text-xs mt-1 hidden">Silakan pilih tanggal terlebih
                            dahulu</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
                    <button type="button" id="cancelBtn"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 text-sm rounded-lg transition-colors">
                        Lanjutkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="listModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 ease-out invisible opacity-0 transform scale-95"
        style="opacity: 0; transform: scale(0.95);">
        <div class="bg-white w-11/12 p-4 rounded shadow-lg transition-transform duration-300 ease-out transform">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">Daftar List</h2>
                <button onclick="closeListModal()" class="p-2 hover:bg-gray-200 rounded-md">
                    <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18 17.94 6M18 18 6.06 6" />
                    </svg>
                </button>
            </div>

            <div class="overflow-auto text-sm rounded border border-gray-300">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">No.</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">No. RKM</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Blok</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Plot</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Luas Plot (Ha)</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Estimasi Pengerjaan (Ha)</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Aktual Pengerjaan (Ha)</th>
                            <th class="py-2 px-4 border-b border-gray-300 bg-gray-100">Sisa (Ha)</th>
                        </tr>
                    </thead>
                    <tbody id="listTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('targetDateModal');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const targetDateForm = document.getElementById('targetDateForm');
        const targetDateInput = document.getElementById('targetDate');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');

        function setDateLimits() {
            const today = new Date();
            const maxDate = new Date();
            maxDate.setDate(today.getDate() + 7);
            const todayStr = today.toISOString().split('T')[0];
            const maxDateStr = maxDate.toISOString().split('T')[0];
            targetDateInput.setAttribute('min', todayStr);
            targetDateInput.setAttribute('max', maxDateStr);
        }

        function updateSubmitButton() {
            submitBtn.disabled = !targetDateInput.value;
        }

        openModalBtn.addEventListener('click', () => {
            modal.classList.add('show');
            const today = new Date();
            targetDateInput.value = today.toISOString().split('T')[0];
            errorMessage.classList.add('hidden');
            setDateLimits();
            updateSubmitButton();
        });

        function closeModal() {
            modal.classList.remove('show');
        }

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        targetDateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const targetDate = targetDateInput.value;
            if (!targetDate) {
                errorMessage.classList.remove('hidden');
                return;
            }
            window.location.href = "{{ route('input.rencana-kerja-mingguan.create') }}" + "?targetDate=" +
                targetDate;
        });

        targetDateInput.addEventListener('input', () => {
            errorMessage.classList.add('hidden');
            updateSubmitButton();
        });
    </script>

    <script>
        function showList(rkmno, companycode) {
            const modal = document.getElementById('listModal');
            const tableBody = document.getElementById('listTableBody');

            tableBody.innerHTML = '';

            const url =
                `{{ route('input.rencana-kerja-mingguan.show', ['rkmno' => '__rkmno__', 'companycode' => '__companycode__']) }}`
                .replace('__rkmno__', rkmno)
                .replace('__companycode__', companycode);

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    data.forEach(item => {

                        const row = `
                            <tr class="text-center">
                                <td class="py-2 px-4 border-b border-gray-300">${item.no}.</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.rkmno}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.blok}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.plot}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.totalluasactual}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.totalestimasi}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.totalhasil ?? 0}</td>
                                <td class="py-2 px-4 border-b border-gray-300">${item.totalsisa ?? 0}</td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });

                    modal.classList.remove('invisible');
                    modal.classList.add('visible');
                    setTimeout(() => {
                        modal.style.opacity = "1";
                        modal.style.transform = "scale(1)";
                    }, 50);
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    alert('Gagal memuat data list.');
                });
        }

        function closeListModal() {
            const modal = document.getElementById('listModal');

            modal.style.opacity = "0";
            modal.style.transform = "scale(0.95)";

            setTimeout(() => {
                modal.classList.remove('visible');
                modal.classList.add('invisible');
            }, 300);
        }
    </script>

    <style>
        /* --- Fade in/out animation --- */
        #targetDateModal {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }

        #targetDateModal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            transform: translateY(-20px);
            transition: all 0.3s ease-in-out;
        }

        #targetDateModal.show .modal-content {
            transform: translateY(0);
        }

        th,
        td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .max-h-96 {
            max-height: 24rem;
            overflow-x: auto;
            overflow-y: hidden;
        }
    </style>
</x-layout>
