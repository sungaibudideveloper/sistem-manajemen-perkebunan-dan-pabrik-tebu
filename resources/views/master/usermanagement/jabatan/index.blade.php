{{-- resources/views/master/usermanagement/jabatan/index.blade.php --}}
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
        permissionModal: false,
        selectedJabatan: null,
        selectedJabatanName: '',
        selectedPermissions: [],
        availablePermissions: @js($permissions),
        isLoadingPermissions: false,
        
        openPermissionModal(jabatan) {
            this.selectedJabatan = jabatan.idjabatan;
            this.selectedJabatanName = jabatan.namajabatan;
            this.selectedPermissions = [];
            this.isLoadingPermissions = true;
            this.permissionModal = true;
            
            // Load current permissions for this jabatan
            this.loadCurrentPermissions();
        },
        
        async loadCurrentPermissions() {
            try {
                const baseUrl = '{{ url("/") }}';
                const response = await fetch(baseUrl + '/api/usermanagement/jabatan/' + this.selectedJabatan + '/permissions');
                const data = await response.json();
                this.selectedPermissions = data.permissions.map(p => p.permissionid);
                this.isLoadingPermissions = false;
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
        
        selectAllInCategory(categoryPermissions) {
            categoryPermissions.forEach(permission => {
                if (!this.isPermissionSelected(permission.permissionid)) {
                    this.selectedPermissions.push(permission.permissionid);
                }
            });
        },
        
        deselectAllInCategory(categoryPermissions) {
            categoryPermissions.forEach(permission => {
                const index = this.selectedPermissions.indexOf(permission.permissionid);
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

                <!-- Search and Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    
                    <!-- Search Form -->
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

                    <!-- Per Page Form -->
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
                        @forelse ($result->sortBy('idjabatan') as $jabatan)
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
                                    <!-- Manage Permissions Button -->
                                    <button @click="openPermissionModal({
                                            idjabatan: {{ $jabatan->idjabatan }},
                                            namajabatan: '{{ $jabatan->namajabatan }}'
                                        })"
                                        class="bg-slate-600 text-white px-3 py-2 rounded-md hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 text-xs transition-colors duration-150 inline-flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                        Kelola Permissions
                                    </button>
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

        <!-- Permission Assignment Modal -->
        <div x-show="permissionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="permissionModal = false">
            <div class="relative bg-white rounded-lg shadow-xl w-[80vw] h-[80vh] flex flex-col">
                
                <!-- Modal Header -->
                <div class="flex-shrink-0 flex items-center justify-between p-6 border-b border-gray-200 bg-white">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Kelola Permissions</h3>
                        <p class="text-sm text-gray-600" x-text="`Jabatan: ${selectedJabatanName}`"></p>
                        <p class="text-xs text-blue-600 mt-1">Total permissions available: {{ collect($permissions)->flatten()->count() }}</p>
                    </div>
                    <button @click="permissionModal = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-hidden">
                    <!-- Loading State -->
                    <div x-show="isLoadingPermissions" class="h-full flex items-center justify-center">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-slate-600 mx-auto mb-4"></div>
                            <p class="text-gray-600">Loading permissions...</p>
                        </div>
                    </div>

                    <!-- Content -->
                    <div x-show="!isLoadingPermissions" class="h-full overflow-y-auto p-6">
                        <form :action="'{{ route('usermanagement.jabatan.assign-permission') }}'" method="POST" id="permissionForm">
                            @csrf
                            <input type="hidden" name="idjabatan" x-model="selectedJabatan">

                            <!-- Permissions by Category -->
                            @foreach($permissions as $category => $categoryPermissions)
                            <div class="mb-6 border rounded-lg">
                                <!-- Category Header -->
                                <div class="bg-gray-50 px-4 py-3 border-b flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-900">{{ $category }}</h4>
                                    <div class="flex space-x-2">
                                        <button type="button" 
                                            @click="selectAllInCategory(@js($categoryPermissions->toArray()))"
                                            class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded hover:bg-blue-200 transition-colors">
                                            Select All
                                        </button>
                                        <button type="button" 
                                            @click="deselectAllInCategory(@js($categoryPermissions->toArray()))"
                                            class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200 transition-colors">
                                            Deselect All
                                        </button>
                                    </div>
                                </div>

                                <!-- Permissions Grid -->
                                <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($categoryPermissions as $permission)
                                    <label class="flex items-start space-x-3 p-3 border rounded-md hover:bg-gray-50 cursor-pointer transition-colors">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission->permissionid }}"
                                               x-model="selectedPermissions"
                                               :checked="isPermissionSelected({{ $permission->permissionid }})"
                                               class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-sm text-gray-900">{{ $permission->permissionname }}</div>
                                            @if($permission->description)
                                            <div class="text-xs text-gray-500 mt-1">{{ Str::limit($permission->description, 100) }}</div>
                                            @endif
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach

                            <!-- Selected Count -->
                            <div class="mb-6 p-3 bg-blue-50 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-900">
                                        <span x-text="selectedPermissions.length"></span> permission(s) dipilih
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Footer - Always Visible -->
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