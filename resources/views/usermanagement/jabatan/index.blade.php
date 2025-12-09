<!-- resources\views\usermanagement\jabatan\index.blade.php -->
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
        crudModal: false,
        permissionModal: false,
        crudMode: 'create',
        selectedJabatan: null,
        selectedJabatanName: '',
        selectedPermissions: [],
        isLoadingPermissions: false,
        jabatanForm: {
            idjabatan: null,
            namajabatan: ''
        },
        
        // CRUD Methods
        openCreateModal() {
            this.crudMode = 'create';
            this.jabatanForm = {
                idjabatan: null,
                namajabatan: ''
            };
            this.crudModal = true;
        },
        
        openEditModal(idjabatan, namajabatan) {
            this.crudMode = 'edit';
            this.jabatanForm = {
                idjabatan: idjabatan,
                namajabatan: namajabatan
            };
            this.crudModal = true;
        },
        
        getFormAction() {
            if (this.crudMode === 'create') {
                return '{{ route('usermanagement.jabatan.store') }}';
            } else {
                return '/usermanagement/jabatan/' + this.jabatanForm.idjabatan;
            }
        },
        
        // Permission Modal Methods
        openPermissionModal(jabatan) {
            this.selectedJabatan = jabatan.idjabatan;
            this.selectedJabatanName = jabatan.namajabatan;
            this.selectedPermissions = [];
            this.isLoadingPermissions = true;
            this.permissionModal = true;
            this.loadCurrentPermissions();
        },
        
        async loadCurrentPermissions() {
            try {
                const baseUrl = '{{ url("/") }}';
                const response = await fetch(baseUrl + '/usermanagement/ajax/jabatan/' + this.selectedJabatan + '/permissions');
                const data = await response.json();
                // ✅ Extract permission IDs - support both 'id' and 'permissionid' keys
                this.selectedPermissions = data.permissions.map(p => p.id || p.permissionid);
                this.isLoadingPermissions = false;
                console.log('Loaded permissions:', this.selectedPermissions); // Debug
            } catch (error) {
                console.error('Error loading permissions:', error);
                this.isLoadingPermissions = false;
            }
        },
        
        togglePermission(permissionId) {
            const index = this.selectedPermissions.indexOf(permissionId);
            if (index > -1) {
                this.selectedPermissions.splice(index, 1);
            } else {
                this.selectedPermissions.push(permissionId);
            }
        },
        
        isPermissionSelected(permissionId) {
            return this.selectedPermissions.includes(permissionId);
        },
        
        selectAllInModule(modulePermissions) {
            modulePermissions.forEach(permission => {
                if (!this.isPermissionSelected(permission.id)) {
                    this.selectedPermissions.push(permission.id);
                }
            });
        },
        
        deselectAllInModule(modulePermissions) {
            modulePermissions.forEach(permission => {
                const index = this.selectedPermissions.indexOf(permission.id);
                if (index > -1) {
                    this.selectedPermissions.splice(index, 1);
                }
            });
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                
                <!-- Title -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Role & Permission Management</h2>
                    <p class="text-sm text-gray-600">Kelola default permission untuk setiap jabatan</p>
                </div>

                <!-- Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    @can('usermanagement.jabatan.create')
                    <button @click="openCreateModal()"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Jabatan
                    </button>
                    @endcan
                    
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Nama jabatan..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                            <a href="{{ route('usermanagement.jabatan.index') }}" 
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

                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage" onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ ($perPage ?? 10) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
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
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Jabatan</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Permissions</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $jabatan)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 text-sm font-medium text-gray-900">{{ $jabatan->idjabatan }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="font-medium">{{ $jabatan->namajabatan }}</div>
                            </td>
                            <td class="py-3 px-3 text-center text-sm text-gray-700">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $jabatan->jabatan_permissions_count > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $jabatan->jabatan_permissions_count }} permissions
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Manage Permissions -->
                                    <button @click="openPermissionModal({
                                            idjabatan: {{ $jabatan->idjabatan }},
                                            namajabatan: '{{ addslashes($jabatan->namajabatan) }}'
                                        })"
                                        class="text-purple-600 hover:text-purple-800 hover:bg-purple-50 rounded-md p-2 transition-all duration-150"
                                        title="Kelola Permissions">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </button>

                                    @can('usermanagement.jabatan.edit')
                                    <button @click="openEditModal({{ $jabatan->idjabatan }}, '{{ addslashes($jabatan->namajabatan) }}')"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit Jabatan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    @endcan
                                    
                                    @can('usermanagement.jabatan.delete')
                                    <form method="POST" action="{{ route('usermanagement.jabatan.destroy', $jabatan->idjabatan) }}" 
                                          onsubmit="return confirm('Yakin ingin menghapus jabatan: {{ addslashes($jabatan->namajabatan) }}?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                            title="Hapus Jabatan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data jabatan</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada jabatan yang terdaftar' }}</p>
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

        <!-- CRUD Modal -->
        <div x-show="crudModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="crudModal = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
                
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="crudMode === 'create' ? 'Tambah Jabatan Baru' : 'Edit Jabatan'"></h3>
                    <button @click="crudModal = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <form :action="getFormAction()" method="POST">
                        @csrf
                        <template x-if="crudMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jabatan <span class="text-red-500">*</span></label>
                            <input type="text" name="namajabatan" x-model="jabatanForm.namajabatan"
                                placeholder="Contoh: Manager, Supervisor, Staff"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                maxlength="30" required>
                            <p class="mt-1 text-xs text-gray-500">Maksimal 30 karakter</p>
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button type="button" @click="crudModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-150">
                                <span x-text="crudMode === 'create' ? 'Simpan' : 'Perbarui'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Permission Modal - Ultra Compact Slug List -->
        <div x-show="permissionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="permissionModal = false">
            <div class="relative bg-white rounded-lg shadow-xl w-[90vw] max-w-5xl h-[85vh] flex flex-col">
                
                <!-- Header -->
                <div class="flex-shrink-0 flex items-center justify-between p-6 border-b border-gray-200 bg-white">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Kelola Permissions</h3>
                        <p class="text-sm text-gray-600" x-text="`Jabatan: ${selectedJabatanName}`"></p>
                    </div>
                    <button @click="permissionModal = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Loading -->
                    <div x-show="isLoadingPermissions" class="h-full flex items-center justify-center">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-slate-600 mx-auto mb-4"></div>
                            <p class="text-gray-600">Loading permissions...</p>
                        </div>
                    </div>

                    <!-- Content -->
                    <div x-show="!isLoadingPermissions" class="h-full">
                        <form :action="'{{ route('usermanagement.jabatan.assign-permissions') }}'" method="POST" id="permissionForm">
                            @csrf
                            <input type="hidden" name="idjabatan" x-model="selectedJabatan">

                            <!-- Permissions by Module -->
                            <div class="space-y-4">
                                @foreach($permissions as $module => $modulePermissions)
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <!-- Module Header -->
                                    <div class="bg-slate-100 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between sticky top-0 z-10">
                                        <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wide">{{ $module }}</h4>
                                        <div class="flex items-center space-x-3">
                                            <button type="button" 
                                                @click="selectAllInModule(@js($modulePermissions->toArray()))"
                                                class="text-xs text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                                Select All
                                            </button>
                                            <span class="text-gray-400">|</span>
                                            <button type="button" 
                                                @click="deselectAllInModule(@js($modulePermissions->toArray()))"
                                                class="text-xs text-red-600 hover:text-red-800 font-semibold transition-colors">
                                                Clear All
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Permission List - Compact -->
                                    <div class="bg-white">
                                        @foreach($modulePermissions as $permission)
                                        <label class="flex items-start px-4 py-2.5 hover:bg-blue-50 cursor-pointer transition-colors border-b border-gray-100 last:border-b-0 group">
                                            <input type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}"
                                                   :checked="isPermissionSelected({{ $permission->id }})"
                                                   @change="togglePermission({{ $permission->id }})"
                                                   class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded flex-shrink-0">
                                            <div class="ml-3 flex-1 min-w-0">
                                                <div class="flex items-center gap-3">
                                                    <code class="text-sm font-mono font-semibold text-slate-900 group-hover:text-blue-700 transition-colors">
                                                        {{ $permission->module }}.{{ $permission->resource }}.{{ $permission->action }}
                                                    </code>
                                                    <span class="text-xs text-gray-600 font-medium">{{ $permission->displayname }}</span>
                                                </div>
                                                @if($permission->description)
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $permission->description }}</div>
                                                @endif
                                            </div>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- Selected Count -->
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold text-blue-900">
                                        <span x-text="selectedPermissions.length"></span> permission(s) terpilih
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex-shrink-0 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0 p-6 bg-gray-50 border-t">
                    <button type="button" @click="permissionModal = false"
                        class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors duration-150">
                        Batal
                    </button>
                    <button type="submit" form="permissionForm"
                        class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-slate-600 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500 transition-colors duration-150">
                        Simpan Permissions
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

</x-layout>