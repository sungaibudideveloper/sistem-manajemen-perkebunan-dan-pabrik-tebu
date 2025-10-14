<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <!-- Success/Error Notifications -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-green-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-red-200 rounded"
            @click="show = false">&times;</span>
    </div>
    @endif

    <div x-data="{
        open: false,
        mode: 'create',
        editUrl: '',
        showNotification: false,
        notificationMessage: '',
        notificationType: 'success',
        form: {
            plot: '',
            luasarea: '',
            jaraktanam: '',
            status: '',
            companycode: '{{ session('companycode') }}'
        },
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.form = {
                plot: '',
                luasarea: '',
                jaraktanam: '',
                status: '',
                companycode: '{{ session('companycode') }}'
            };
            this.open = true;
        },
        editForm(data, url) {
            this.mode = 'edit';
            this.editUrl = url;
            this.form = {
                plot: data.plot,
                luasarea: data.luasarea,
                jaraktanam: data.jaraktanam,
                status: data.status,
                companycode: data.companycode
            };
            this.open = true;
        },
        showAlert(message, type = 'success') {
            this.notificationMessage = message;
            this.notificationType = type;
            this.showNotification = true;
            setTimeout(() => { this.showNotification = false; }, 5000);
        },
        async addToMasterlist(plot, companycode) {
            if (!confirm('Tambahkan plot ' + plot + ' ke masterlist?\n\nPlot akan menjadi aktif dan bisa digunakan untuk membuat RKH.')) {
                return;
            }
            
            try {
                const response = await fetch('{{ route('masterdata.plotting.addToMasterlist') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: JSON.stringify({ plot, companycode })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showAlert(data.message || 'Gagal menambahkan ke masterlist', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Terjadi kesalahan saat menambahkan ke masterlist', 'error');
            }
        },
        async deleteRecord(plot, companycode) {
            if (!confirm('Yakin ingin menghapus plot ' + plot + '?')) {
                return;
            }
            
            try {
                const deleteRoute = '{{ route('masterdata.plotting.destroy', ['plot' => '__plot__', 'companycode' => '__companycode__']) }}'
                    .replace('__plot__', plot)
                    .replace('__companycode__', companycode);
                    
                const response = await fetch(deleteRoute, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showAlert(data.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Terjadi kesalahan saat menghapus data', 'error');
            }
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Dynamic Notification -->
        <div x-show="showNotification" x-transition
            :class="notificationType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'"
            class="mx-4 mb-4 border px-4 py-3 rounded relative">
            <strong class="font-bold" x-text="notificationType === 'success' ? 'Berhasil!' : 'Error!'"></strong>
            <span class="block sm:inline" x-text="notificationMessage"></span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer hover:bg-opacity-20 rounded"
                @click="showNotification = false">&times;</span>
        </div>

        <!-- Header Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                
                <!-- Tambah Data Button -->
                <div class="flex justify-start">
                    @if (hasPermission('Create Plotting'))
                    <button @click="resetForm()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="hidden sm:inline">Tambah Plot</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                    @endif
                </div>

                <!-- Search and Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    
                    <!-- Masterlist Status Filter -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="masterlist_filter" class="text-xs font-medium text-gray-700 whitespace-nowrap">Masterlist:</label>
                        <select name="masterlist_filter" id="masterlist_filter" onchange="this.form.submit()"
                            class="text-xs w-28 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="">Semua</option>
                            <option value="active" {{ request('masterlist_filter') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('masterlist_filter') === 'inactive' ? 'selected' : '' }}>Belum Aktif</option>
                        </select>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('perPage'))
                            <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>
                    
                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Cari plot, status, blok..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                            <a href="{{ route('masterdata.plotting.index') }}" 
                               class="text-gray-500 hover:text-gray-700 px-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                        @if(request('masterlist_filter'))
                            <input type="hidden" name="masterlist_filter" value="{{ request('masterlist_filter') }}">
                        @endif
                        @if(request('perPage'))
                            <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <!-- Per Page Form -->
                    <form method="POST" action="{{ url()->current() }}" class="flex items-center gap-2">
                        @csrf
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage" onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('masterlist_filter'))
                            <input type="hidden" name="masterlist_filter" value="{{ request('masterlist_filter') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plot</th>
                            <th class="py-3 px-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16">Blok</th>
                            <th class="py-3 px-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Luas Area (Ha)</th>
                            <th class="py-3 px-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jarak Tanam (cm)</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Masterlist</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Company</th>
                            @if (hasPermission('Edit Plotting') || hasPermission('Hapus Plotting'))
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($plotting as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-2 text-center text-sm text-gray-500">{{ $item->no }}</td>
                            <td class="py-3 px-3 text-sm font-medium text-gray-900">{{ $item->plot }}</td>
                            <td class="py-3 px-2 text-center text-sm text-gray-700 font-medium">
                                {{ $item->blok ?? substr($item->plot, 0, 1) }}
                            </td>
                            <td class="py-3 px-3 text-right text-sm text-gray-700 font-mono">{{ number_format($item->luasarea, 2) }}</td>
                            <td class="py-3 px-3 text-right text-sm text-gray-700 font-mono">{{ number_format($item->jaraktanam) }}</td>
                            <td class="py-3 px-3 text-center text-sm text-gray-700 font-medium">
                                {{ $item->status }}
                            </td>
                            <td class="py-3 px-3 text-center text-sm">
                                @if($item->is_in_masterlist)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <button @click="addToMasterlist('{{ $item->plot }}', '{{ $item->companycode }}')"
                                        class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-150"
                                        title="Tambah ke masterlist agar plot bisa digunakan di RKH">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Add to Masterlist
                                    </button>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center text-sm text-gray-700 hidden sm:table-cell">
                                <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $item->companycode }}</code>
                            </td>
                            @if (hasPermission('Edit Plotting') || hasPermission('Hapus Plotting'))
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    @if (hasPermission('Edit Plotting'))
                                    <button @click='editForm({
                                            plot: "{{ $item->plot }}",
                                            luasarea: "{{ $item->luasarea }}",
                                            jaraktanam: "{{ $item->jaraktanam }}",
                                            status: "{{ $item->status }}",
                                            companycode: "{{ $item->companycode }}"
                                        }, "{{ route('masterdata.plotting.update', ['plot' => $item->plot, 'companycode' => $item->companycode]) }}")'
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit Plot">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endif

                                    @if (hasPermission('Hapus Plotting'))
                                    <button @click="deleteRecord('{{ $item->plot }}', '{{ $item->companycode }}')"
                                        class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                        title="Hapus Plot">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data plot</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada plot yang terdaftar' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($plotting->hasPages())
            <div class="mt-6">
                {{ $plotting->appends(request()->query())->links() }}
            </div>
            @else
            <div class="mt-4 flex items-center justify-between text-sm text-gray-700">
                <p>Menampilkan <span class="font-medium">{{ $plotting->count() }}</span> dari <span class="font-medium">{{ $plotting->total() }}</span> data</p>
            </div>
            @endif
        </div>

        <!-- Modal -->
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="open = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="mode === 'create' ? 'Tambah Plot Baru' : 'Edit Plot'"></h3>
                    <button @click="open = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form :action="mode === 'create' ? '{{ route('masterdata.plotting.handle') }}' : editUrl" method="POST">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Company Code (Read-only) -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company Code</label>
                            <input type="text" name="companycode" x-model="form.companycode" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-600 cursor-not-allowed" 
                                readonly>
                            <p class="mt-1 text-xs text-gray-500">Company code otomatis dari session</p>
                        </div>

                        <!-- Plot Code -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kode Plot <span class="text-red-500">*</span></label>
                            <input type="text" name="plot" x-model="form.plot"
                                placeholder="Contoh: A0101, B0202" maxlength="5"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                                pattern="^[A-Z][0-9]{3,4}$" required
                                @input="form.plot = $el.value.toUpperCase()">
                            <p class="mt-1 text-xs text-gray-500">Format: 1 huruf diikuti 3-4 angka (A001 atau A0001)</p>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                            <select name="status" x-model="form.status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Status --</option>
                                @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}">{{ $key }} - {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Luas Area -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Luas Area (Ha) <span class="text-red-500">*</span></label>
                            <input type="number" name="luasarea" x-model="form.luasarea"
                                placeholder="0.00" min="0" max="999.99" step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <p class="mt-1 text-xs text-gray-500">Maksimal 999.99 hektar dengan 2 desimal</p>
                        </div>

                        <!-- Jarak Tanam -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jarak Tanam (cm) <span class="text-red-500">*</span></label>
                            <input type="number" name="jaraktanam" x-model="form.jaraktanam"
                                placeholder="150" min="0" max="999"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <p class="mt-1 text-xs text-gray-500">Jarak tanam dalam centimeter, maksimal 999 cm</p>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button type="button" @click="open = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                <span x-text="mode === 'create' ? 'Simpan' : 'Perbarui'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
        input[readonly] { background-color: #f9fafb; cursor: not-allowed; }
        input:focus, select:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    </style>

</x-layout>