<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
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
            nokendaraan: '',
            operator: '',
            jenis: '',
            hourmeter: '',
            isactive: 1
        },
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.form = {
                nokendaraan: '',
                operator: '',
                jenis: '',
                hourmeter: '',
                isactive: 1
            };
            this.open = true;
        },
        editForm(data, url) {
            this.mode = 'edit';
            this.editUrl = url;
            this.form = {
                nokendaraan: data.nokendaraan,
                operator: data.operator,
                jenis: data.jenis,
                hourmeter: data.hourmeter,
                isactive: data.isactive
            };
            this.open = true;
        },
        showAlert(message, type = 'success') {
            this.notificationMessage = message;
            this.notificationType = type;
            this.showNotification = true;
            setTimeout(() => { this.showNotification = false; }, 5000);
        },
        async submitForm() {
            const form = document.getElementById('kendaraan-form');
            const formData = new FormData(form);
            
            try {
                const response = await fetch(this.mode === 'create' ? '{{ route('masterdata.kendaraan.handle') }}' : this.editUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    this.open = false;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.showAlert(data.message || 'Terjadi kesalahan', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                this.showAlert('Terjadi kesalahan saat menyimpan data', 'error');
            }
        },
        async deleteRecord(companycode, nokendaraan) {
            if (!confirm('Yakin ingin menonaktifkan kendaraan ' + nokendaraan + '?')) {
                return;
            }
            
            try {
                const deleteRoute = '{{ route('masterdata.kendaraan.destroy', ['companycode' => '__companycode__', 'nokendaraan' => '__nokendaraan__']) }}'
                    .replace('__companycode__', companycode)
                    .replace('__nokendaraan__', nokendaraan);
                    
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
                    <button @click="resetForm()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="hidden sm:inline">Tambah Kendaraan</span>
                        <span class="sm:hidden">Tambah</span>
                    </button>
                </div>

                <!-- Search and Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    
                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="No. Kendaraan, Jenis, Operator..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                            <a href="{{ route('masterdata.kendaraan.index') }}" 
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
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Kendaraan</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="py-3 px-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hour Meter</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $item)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 text-sm font-medium text-gray-900">{{ $item->nokendaraan }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                @if($item->idtenagakerja)
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $item->idtenagakerja }}</span>
                                        <span class="text-xs text-gray-500">
                                            {{ $item->operator_nama ?: 'Nama tidak tersedia' }}
                                            @if($item->operator_nik)
                                                ({{ $item->operator_nik }})
                                            @endif
                                        </span>
                                        @if($item->operator_isactive == 0)
                                            <span class="text-xs text-red-500">Operator Nonaktif</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Belum ada operator</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">{{ $item->jenis ?: '-' }}</td>
                            <td class="py-3 px-3 text-right text-sm text-gray-700 font-mono">{{ number_format($item->hourmeter, 2) }}</td>
                            <td class="py-3 px-3 text-center text-sm">
                                @if($item->isactive == 1)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <button @click='editForm({
                                            nokendaraan: "{{ $item->nokendaraan }}",
                                            operator: "{{ $item->idtenagakerja }}",
                                            jenis: "{{ $item->jenis }}",
                                            hourmeter: "{{ $item->hourmeter }}",
                                            isactive: {{ $item->isactive ?? 0 }}
                                        }, "{{ route('masterdata.kendaraan.update', [$item->companycode, $item->nokendaraan]) }}")'
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="deleteRecord('{{ $item->companycode }}', '{{ $item->nokendaraan }}')"
                                        class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                        title="Nonaktifkan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data kendaraan</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada kendaraan yang terdaftar' }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($result->hasPages())
            <div class="mt-6">
                {{ $result->appends(request()->query())->links() }}
            </div>
            @else
            <div class="mt-4 flex items-center justify-between text-sm text-gray-700">
                <p>Menampilkan <span class="font-medium">{{ $result->count() }}</span> dari <span class="font-medium">{{ $result->total() }}</span> data</p>
            </div>
            @endif
        </div>

        <!-- Modal -->
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="open = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="mode === 'create' ? 'Tambah Kendaraan' : 'Edit Kendaraan'"></h3>
                    <button @click="open = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form id="kendaraan-form" @submit.prevent="submitForm()">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- No. Kendaraan -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. Kendaraan <span class="text-red-500">*</span></label>
                            <input type="text" name="nokendaraan" x-model="form.nokendaraan"
                                placeholder="Contoh: TRK001, EXC002"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                                required maxlength="10"
                                @input="form.nokendaraan = $el.value.toUpperCase()">
                        </div>

                        <!-- Operator -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Operator</label>
                            <select name="operator" x-model="form.operator"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Operator --</option>
                                <!-- Create Mode: Only available operators -->
                                @if(count($availableOperators) > 0)
                                    <template x-if="mode === 'create'">
                                        <optgroup label="Operator Tersedia">
                                            @foreach($availableOperators as $op)
                                            <option value="{{ $op->tenagakerjaid }}">{{ $op->tenagakerjaid }} - {{ $op->nama }}@if($op->nik) ({{ $op->nik }})@endif</option>
                                            @endforeach
                                        </optgroup>
                                    </template>
                                @else
                                    <template x-if="mode === 'create'">
                                        <option disabled>Tidak ada operator yang tersedia</option>
                                    </template>
                                @endif
                                
                                <!-- Edit Mode: All operators -->
                                <template x-if="mode === 'edit'">
                                    <optgroup label="Semua Operator">
                                        @foreach($allOperators as $op)
                                        <option value="{{ $op->tenagakerjaid }}">{{ $op->tenagakerjaid }} - {{ $op->nama }}@if($op->nik) ({{ $op->nik }})@endif</option>
                                        @endforeach
                                    </optgroup>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                <span x-show="mode === 'create'">Hanya operator yang belum memiliki kendaraan</span>
                                <span x-show="mode === 'edit'">Semua operator aktif (jenistenagakerja = 3)</span>
                            </p>
                        </div>

                        <!-- Jenis -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kendaraan <span class="text-red-500">*</span></label>
                            <input type="text" name="jenis" x-model="form.jenis"
                                placeholder="Contoh: Truk, Excavator, Bulldozer"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required maxlength="50">
                        </div>

                        <!-- Hour Meter -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hour Meter</label>
                            <input type="number" name="hourmeter" x-model="form.hourmeter"
                                placeholder="0.00" min="0" max="999999.99" step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Opsional, default 0 jika kosong</p>
                        </div>

                        <!-- Status Active (Edit only) -->
                        <template x-if="mode === 'edit'">
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input type="checkbox" name="isactive" 
                                           :checked="form.isactive == 1" 
                                           value="1" class="mr-2">
                                    <span class="text-sm font-medium text-gray-700">Status Aktif</span>
                                </label>
                            </div>
                        </template>

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
    </style>

</x-layout>