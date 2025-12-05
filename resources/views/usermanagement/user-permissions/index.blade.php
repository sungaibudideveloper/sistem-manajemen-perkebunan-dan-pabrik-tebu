{{-- resources/views/master/usermanagement/user-permissions/index.blade.php --}}
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
        form: {
            userid: '',
            companycode: '',
            permissionid: '',
            permissiontype: 'GRANT',
            reason: ''
        },
        availablePermissions: @js($permissions),
        userCompanies: {},
        
        resetForm() {
            this.form = {
                userid: '',
                companycode: '',
                permissionid: '',
                permissiontype: 'GRANT',
                reason: ''
            };
            this.userCompanies = {};
            this.open = true;
        },
        
        async loadUserCompanies() {
            if (!this.form.userid) {
                this.userCompanies = {};
                return;
            }
            
            try {
                const response = await fetch(`/api/usermanagement/users/${this.form.userid}/companies`);
                const data = await response.json();
                this.userCompanies = data.companies || {};
            } catch (error) {
                console.error('Error loading user companies:', error);
                this.userCompanies = {};
            }
        },
        
        getPermissionsByCategory(category) {
            return this.availablePermissions[category] || [];
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

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
                        <span class="hidden sm:inline">Tambah Permission Override</span>
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
                            placeholder="User ID, Permission, Company..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                            <a href="{{ route('usermanagement.user-permissions.index') }}" 
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
                            <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ ($perPage ?? 15) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPage ?? 15) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="px-4 py-3 bg-blue-50 border-b border-blue-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium">Permission Override System</p>
                    <p>GRANT = Berikan akses meskipun tidak ada di jabatan | DENY = Tolak akses meskipun ada di jabatan</p>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permission</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Granted By</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $userPermission)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $userPermission->userid }}</span>
                                    <span class="text-xs text-gray-500">{{ $userPermission->user->name ?? 'Unknown User' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $userPermission->permission }}</span>
                                    @if($userPermission->permissionModel && $userPermission->permissionModel->category)
                                        <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full inline-block w-fit mt-1">
                                            {{ $userPermission->permissionModel->category }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <span class="font-medium">{{ $userPermission->companycode }}</span>
                            </td>
                            <td class="py-3 px-3 text-center text-sm">
                                @if($userPermission->permissiontype === 'GRANT')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        GRANT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        DENY
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="max-w-xs truncate" title="{{ $userPermission->reason }}">
                                    {{ $userPermission->reason ?: '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $userPermission->grantedby ?: 'System' }}</span>
                                    <span class="text-xs text-gray-500">{{ $userPermission->createdat ? \Carbon\Carbon::parse($userPermission->createdat)->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Remove Override Button -->
                                    <form action="{{ route('usermanagement.user-permissions.destroy', [$userPermission->userid, $userPermission->companycode, $userPermission->permission]) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus permission override {{ $userPermission->permission }} untuk user {{ $userPermission->userid }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                            title="Hapus Override">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.9-2a9 9 0 11-11.8 0M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada permission overrides</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada permission overrides yang dikonfigurasi' }}</p>
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
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Tambah Permission Override</h3>
                        <p class="text-sm text-gray-600">Override permission dari default jabatan untuk user tertentu</p>
                    </div>
                    <button @click="open = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form action="{{ route('usermanagement.user-permissions.store') }}" method="POST">
                        @csrf

                        <!-- User Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">User <span class="text-red-500">*</span></label>
                            <select name="userid" x-model="form.userid" @change="loadUserCompanies()" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->userid }}">
                                    {{ $user->userid }} - {{ $user->name }} 
                                    @if($user->jabatan)
                                        ({{ $user->jabatan->namajabatan }})
                                    @endif
                                </option>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">Pilih user yang akan di-override permissionnya</div>
                        </div>

                        <!-- Company Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company <span class="text-red-500">*</span></label>
                            <select name="companycode" x-model="form.companycode" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Company --</option>
                                @foreach($companies as $company)
                                <option value="{{ $company->companycode }}">
                                    {{ $company->companycode }} - {{ $company->companyname }}
                                </option>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">User harus memiliki akses ke company ini terlebih dahulu</div>
                        </div>

                        <!-- Permission Type -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Override Type <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50" 
                                       :class="form.permissiontype === 'GRANT' ? 'border-green-500 bg-green-50' : 'border-gray-300'">
                                    <input type="radio" name="permissiontype" value="GRANT" x-model="form.permissiontype" class="sr-only">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 rounded-full border-2 mr-3 flex items-center justify-center"
                                             :class="form.permissiontype === 'GRANT' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                                            <div class="w-2 h-2 rounded-full bg-white" x-show="form.permissiontype === 'GRANT'"></div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-green-700">GRANT</div>
                                            <div class="text-xs text-green-600">Berikan akses</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                                       :class="form.permissiontype === 'DENY' ? 'border-red-500 bg-red-50' : 'border-gray-300'">
                                    <input type="radio" name="permissiontype" value="DENY" x-model="form.permissiontype" class="sr-only">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 rounded-full border-2 mr-3 flex items-center justify-center"
                                             :class="form.permissiontype === 'DENY' ? 'border-red-500 bg-red-500' : 'border-gray-300'">
                                            <div class="w-2 h-2 rounded-full bg-white" x-show="form.permissiontype === 'DENY'"></div>
                                        </div>
                                        <div>
                                            <div class="font-medium text-red-700">DENY</div>
                                            <div class="text-xs text-red-600">Tolak akses</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Permission Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permission <span class="text-red-500">*</span></label>
                            <select name="permissionid" x-model="form.permissionid" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Permission --</option>
                                @foreach($permissions as $category => $categoryPermissions)
                                    <optgroup label="{{ $category }}">
                                        @foreach($categoryPermissions as $permission)
                                            <option value="{{ $permission->permissionid }}">{{ $permission->permissionname }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">Permission yang akan di-override untuk user ini</div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                            <textarea name="reason" x-model="form.reason"
                                placeholder="Jelaskan alasan mengapa permission ini perlu di-override..."
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            <div class="text-xs text-gray-500 mt-1">Dokumentasi untuk audit trail (opsional tapi disarankan)</div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button type="button" @click="open = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Simpan Permission Override
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