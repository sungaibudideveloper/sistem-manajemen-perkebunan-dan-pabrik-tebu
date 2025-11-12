<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Success Alert -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <!-- Error Alert -->
    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <div x-data="trashManagement()" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Section with Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">

                <!-- New Data Button -->
                <div class="flex justify-start">
                    <button @click="openModal('create')"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                        </svg>
                        <span class="hidden sm:inline">Tambah Data Trash</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                </div>

                <!-- Search and Per Page Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nomor surat jalan..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                        <a href="{{ route('pabrik.trash.index') }}"
                            class="text-gray-500 hover:text-gray-700 px-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </a>
                        @endif
                        @if(request('perPage'))
                        <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <!-- Per Page Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage"
                            onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ (int)request('perPage', 10) === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int)request('perPage', 10) === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int)request('perPage', 10) === 50 ? 'selected' : '' }}>50</option>
                        </select>
                        @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Surat Jalan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Toleransi</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pucuk</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Daun Gulma</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sogolan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siwilan</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tebu Mati</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanah Dll</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Trash</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Netto Trash</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        {{-- Loop data trash --}}
                        @forelse($data ?? [] as $index => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $data->firstItem() + $index }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->suratjalanno ?? 'N/A' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->jenis === 'manual' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst($item->jenis ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->toleransi ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->pucuk ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->daun_gulma ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->sogolan ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->siwilan ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->tebumati ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->tanah_etc ?? 0, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->total ?? 0, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->netto_trash ?? 0, 3, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->createdby ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item->createddate ? date('d/m/Y H:i', strtotime($item->createddate)) : '-' }}</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <button @click="openModal('edit', {
                                            suratjalanno: '{{ $item->suratjalanno }}',
                                            companycode: '{{ $item->companycode }}',
                                            jenis: '{{ $item->jenis }}',
                                            toleransi: '{{ $item->toleransi }}',
                                            berat_bersih: '{{ $item->berat_bersih }}',
                                            pucuk: '{{ $item->pucuk }}',
                                            daun_gulma: '{{ $item->daun_gulma }}',
                                            sogolan: '{{ $item->sogolan }}',
                                            siwilan: '{{ $item->siwilan }}',
                                            tebumati: '{{ $item->tebumati }}',
                                            tanah_etc: '{{ $item->tanah_etc }}'
                                        })"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-1 transition-all duration-150"
                                        title="Edit Data Trash">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="deleteItem({
                                            suratjalanno: '{{ $item->suratjalanno }}',
                                            companycode: '{{ $item->companycode }}',
                                            jenis: '{{ $item->jenis }}'
                                        })"
                                        class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-1 transition-all duration-150"
                                        title="Hapus Data Trash">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="15" class="px-4 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data trash yang ditemukan</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada data trash yang diinput' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($data) && $data->hasPages())
            <div class="mt-6">
                {{ $data->appends(request()->query())->links() }}
            </div>
            @else
            <div class="mt-4 flex items-center justify-between text-sm text-gray-700">
                <p>Menampilkan <span class="font-medium">{{ $data->count() }}</span> dari <span class="font-medium">{{ $data->total() }}</span> data</p>
            </div>
            @endif
        </div>

        <!-- Modal Form -->
        <div x-show="showModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form :action="formAction" method="POST">
                        @csrf
                        <div x-show="mode === 'edit'">
                            @method('POST')
                        </div>

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="modalTitle"></h3>

                                    <!-- Step 1: Company Code dan Nomor Surat Jalan -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Company Code</label>
                                            <input type="text" name="companycode" x-model="form.companycode"
                                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                placeholder="Masukkan company code" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Surat Jalan</label>
                                            <input type="text" name="no_surat_jalan" x-model="form.no_surat_jalan"
                                                class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                placeholder="Masukkan nomor surat jalan" required>
                                        </div>
                                    </div>

                                    <!-- Tombol Cari -->
                                    <div class="mb-4">
                                        <button type="button" @click="searchSuratJalan()"
                                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                            </svg>
                                            Cari Surat Jalan
                                        </button>
                                        <div x-show="searchMessage" class="mt-2 text-sm" :class="searchSuccess ? 'text-green-600' : 'text-red-600'" x-text="searchMessage"></div>
                                    </div>

                                    <!-- Step 2: Data Entry Fields (shown only after successful search) -->
                                    <div x-show="suratJalanFound" x-transition class="space-y-4">

                                        <!-- Row 1: Jenis -->
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                                                <select name="jenis" x-model="form.jenis"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                                    <option value="">Pilih Jenis</option>
                                                    <option value="manual">Manual</option>
                                                    <option value="mesin">Mesin</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Row 2: Berat Bersih, Pucuk, Daun -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Berat Bersih</label>
                                                <input type="text" name="berat_bersih" x-model="form.berat_bersih"
                                                    @input="calculateBeratKotor()"
                                                    @blur="formatInput('berat_bersih')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Pucuk</label>
                                                <input type="text" name="pucuk" x-model="form.pucuk"
                                                    @input="calculateBeratKotor()"
                                                    @blur="formatInput('pucuk')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Daun Gulma</label>
                                                <input type="text" name="daun_gulma" x-model="form.daun_gulma"
                                                    @input="calculateBeratKotor()"
                                                    @blur="formatInput('daun_gulma')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                            </div>
                                        </div>

                                        <!-- Row 3: Sogolan, Siwilan, Tebu Mati -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Sogolan</label>
                                                <input type="text" name="sogolan" x-model="form.sogolan"
                                                    @input="calculateBeratKotor()"
                                                    @blur="formatInput('sogolan')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Siwilan</label>
                                                <input type="text" name="siwilan" x-model="form.siwilan"
                                                    @input="calculateBeratKotor()"
                                                    @blur="formatInput('siwilan')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Tebu Mati</label>
                                                <input type="text" name="tebumati" x-model="form.tebumati"
                                                    @input="calculateBeratKotor()"
                                                    @blur="formatInput('tebumati')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                            </div>
                                        </div>

                                        <!-- Row 4: Tanah dan Lain, Berat Kotor, Toleransi -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanah dll</label>
                                                <input type="text" name="tanah_etc" x-model="form.tanah_etc"
                                                    @input="calculateBeratKotor()"
                                                    @blur="formatInput('tanah_etc')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                                    Berat Kotor
                                                    <span class="text-xs text-gray-500">(Auto Calculate)</span>
                                                </label>
                                                <input type="text" name="berat_kotor" x-model="form.berat_kotor" readonly
                                                    class="w-full border border-gray-200 rounded-md shadow-sm bg-gray-50 px-3 py-2 text-gray-700 cursor-not-allowed"
                                                    placeholder="Auto calculated">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Toleransi (%)</label>
                                                <input type="text" name="toleransi" x-model="form.toleransi"
                                                    @blur="formatInput('toleransi')"
                                                    class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                                                    placeholder="Default: 5,00" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" x-show="suratJalanFound"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <span x-text="mode === 'create' ? 'Simpan' : 'Update'"></span>
                            </button>
                            <button type="button" @click="closeModal()"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function trashManagement() {
            return {
                showModal: false,
                mode: 'create',
                modalTitle: '',
                formAction: '',
                suratJalanFound: false,
                searchMessage: '',
                searchSuccess: false,
                form: {
                    companycode: '',
                    no_surat_jalan: '',
                    jenis: '',
                    toleransi: '5,00', // Default value 5
                    berat_bersih: '',
                    pucuk: '',
                    daun_gulma: '',
                    sogolan: '',
                    siwilan: '',
                    tebumati: '',
                    tanah_etc: '',
                    berat_kotor: ''
                },

                openModal(mode, data = null) {
                    this.mode = mode;
                    this.showModal = true;
                    this.suratJalanFound = false;
                    this.searchMessage = '';

                    if (mode === 'create') {
                        this.modalTitle = 'Tambah Data Trash';
                        this.formAction = '{{ route("pabrik.trash.store") }}';
                        this.resetForm();
                    } else {
                        this.modalTitle = 'Edit Data Trash';
                        this.formAction = `{{ url('/') }}/pabrik/trash/update/${data.suratjalanno}/${data.companycode}/${data.jenis}`;
                        console.log('Form Action:', this.formAction);
                        this.fillForm(data);
                        this.suratJalanFound = true;
                    }
                },

                closeModal() {
                    this.showModal = false;
                    this.resetForm();
                },

                resetForm() {
                    this.form = {
                        companycode: '',
                        no_surat_jalan: '',
                        jenis: '',
                        toleransi: '5,00', // Default value 5
                        berat_bersih: '',
                        pucuk: '',
                        daun_gulma: '',
                        sogolan: '',
                        siwilan: '',
                        tebumati: '',
                        tanah_etc: '',
                        berat_kotor: ''
                    };
                },

                fillForm(data) {
                    this.form = {
                        companycode: data.companycode || '',
                        no_surat_jalan: data.suratjalanno || '',
                        jenis: data.jenis || '',
                        toleransi: data.toleransi || '5,00',
                        berat_bersih: data.berat_bersih || '',
                        pucuk: data.pucuk || '',
                        daun_gulma: data.daun_gulma || '',
                        sogolan: data.sogolan || '',
                        siwilan: data.siwilan || '',
                        tebumati: data.tebumati || '',
                        tanah_etc: data.tanah_etc || '',
                        berat_kotor: data.berat_kotor || ''
                    };
                    // Calculate berat kotor after filling form
                    this.calculateBeratKotor();
                },

                calculateBeratKotor() {
                    // Parse values, handle comma format
                    const beratBersih = parseFloat(this.form.berat_bersih.toString().replace(',', '.')) || 0;
                    const pucuk = parseFloat(this.form.pucuk.toString().replace(',', '.')) || 0;
                    const daunGulma = parseFloat(this.form.daun_gulma.toString().replace(',', '.')) || 0;
                    const sogolan = parseFloat(this.form.sogolan.toString().replace(',', '.')) || 0;
                    const siwilan = parseFloat(this.form.siwilan.toString().replace(',', '.')) || 0;
                    const tebumati = parseFloat(this.form.tebumati.toString().replace(',', '.')) || 0;
                    const tanahEtc = parseFloat(this.form.tanah_etc.toString().replace(',', '.')) || 0;

                    // Calculate berat kotor (berat bersih + all trash components)
                    const beratKotor = beratBersih + pucuk + daunGulma + sogolan + siwilan + tebumati + tanahEtc;

                    // Format dengan 3 desimal dan koma
                    this.form.berat_kotor = beratKotor.toFixed(2).replace('.', ',');
                },

                // Format input saat user selesai mengetik
                formatInput(field) {
                    if (this.form[field] && this.form[field] !== '') {
                        const value = parseFloat(this.form[field].toString().replace(',', '.')) || 0;
                        this.form[field] = value.toFixed(2).replace('.', ',');
                        if (field !== 'toleransi') {
                            this.calculateBeratKotor();
                        }
                    }
                },

                async searchSuratJalan() {
                    if (!this.form.no_surat_jalan) {
                        this.searchMessage = 'Masukkan nomor surat jalan terlebih dahulu';
                        this.searchSuccess = false;
                        return;
                    }

                    this.searchMessage = 'Mencari...';

                    try {
                        const response = await fetch(`{{ url('/') }}/pabrik/trash/surat-jalan/check?no=${encodeURIComponent(this.form.no_surat_jalan)}`);
                        const result = await response.json();

                        if (result.exists) {
                            this.searchMessage = result.message || 'Surat jalan ditemukan!';
                            this.searchSuccess = true;
                            this.suratJalanFound = true;

                            // Auto-fill company code dari hasil pencarian
                            if (result.data && result.data.companycode) {
                                this.form.companycode = result.data.companycode;
                            }
                        } else {
                            this.searchMessage = result.message || 'Nomor surat jalan tidak ditemukan!';
                            this.searchSuccess = false;
                            this.suratJalanFound = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.searchMessage = 'Terjadi kesalahan saat mencari surat jalan';
                        this.searchSuccess = false;
                        this.suratJalanFound = false;
                    }
                },

                deleteItem(data) {
                    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ url('/') }}/pabrik/trash/delete/${data.suratjalanno}/${data.companycode}/${data.jenis}`;
                        form.innerHTML = `
                        @csrf
                    `; 
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            }
        }
    </script>

</x-layout>