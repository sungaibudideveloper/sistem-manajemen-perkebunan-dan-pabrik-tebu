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

    @if ($errors->any())
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Validation Error!</strong>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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
        selectedUser: null,
        loading: false,
        
        resetForm() {
            this.form = {
                userid: '',
                companycode: '',
                permissionid: '',
                permissiontype: 'GRANT',
                reason: ''
            };
            this.userCompanies = {};
            this.selectedUser = null;
            this.open = true;
        },
        
        async loadUserCompanies() {
            if (!this.form.userid) {
                this.userCompanies = {};
                this.selectedUser = null;
                return;
            }
            
            this.loading = true;
            
            try {
                // Gunakan base URL Laravel untuk compatibility localhost & production
                const response = await fetch(`{{ url('usermanagement/ajax/users') }}/${this.form.userid}/companies`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success || data.companies) {
                    this.userCompanies = data.companies || {};
                    this.selectedUser = data.user || null;
                    
                    // Reset company selection if current selection not in user's companies
                    if (this.form.companycode && !this.userCompanies[this.form.companycode]) {
                        this.form.companycode = '';
                    }
                } else {
                    this.userCompanies = {};
                    this.selectedUser = null;
                    console.error('Response:', data);
                }
            } catch (error) {
                console.error('Error loading user companies:', error);
                this.userCompanies = {};
                this.selectedUser = null;
                
                // Jika 404, berarti route belum ada - beri pesan yang jelas
                if (error.message.includes('404')) {
                    console.warn('AJAX route /usermanagement/ajax/users/{userid}/companies belum terdaftar. Tambahkan di routes/user-management.php');
                }
            } finally {
                this.loading = false;
            }
        },
        
        getPermissionsByCategory(category) {
            return this.availablePermissions[category] || [];
        },
        
        hasUserCompanies() {
            return Object.keys(this.userCompanies).length > 0;
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
                    <form method="GET" action="{{ route('usermanagement.user-permission.index') }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="User ID, Permission, Company..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                            <a href="{{ route('usermanagement.user-permission.index') }}" 
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
                    <form method="GET" action="{{ route('usermanagement.user-permission.index') }}" class="flex items-center gap-2">
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
        <div class="px-4 py-2.5 bg-blue-50 border-b border-blue-200">
            <div class="flex items-center text-sm text-blue-800">
                <svg class="w-4 h-4 text-blue-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span><strong>Permission Override:</strong> <span class="text-green-700 font-medium">GRANT</span> = berikan akses | <span class="text-red-700 font-medium">DENY</span> = tolak akses (override permission dari jabatan)</span>
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
                                    @if($userPermission->user)
                                        <span class="text-xs text-gray-500">{{ $userPermission->user->name }}</span>
                                        @if($userPermission->user->jabatan)
                                            <span class="text-xs text-blue-600 mt-0.5">{{ $userPermission->user->jabatan->namajabatan }}</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col gap-1">
                                    @php
                                        $perm = $userPermission->permission; // ← GANTI dari permissionModel
                                        $slug = $perm ? "{$perm->module}.{$perm->resource}.{$perm->action}" : $userPermission->permissionid;
                                    @endphp
                                    <code class="text-xs font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded inline-block">
                                        {{ $slug }}
                                    </code>
                                    @if($perm && $perm->displayname)
                                        <span class="text-xs text-gray-500">{{ $perm->displayname }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <span class="font-medium">{{ $userPermission->companycode }}</span>
                            </td>
                            <td class="py-3 px-3 text-center text-sm">
                                @if($userPermission->permissiontype === 'GRANT')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        GRANT
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        DENY
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="max-w-xs" title="{{ $userPermission->reason }}">
                                    {{ $userPermission->reason ? Str::limit($userPermission->reason, 50) : '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $userPermission->grantedby ?: 'System' }}</span>
                                    <span class="text-xs text-gray-500">
                                        {{ $userPermission->createdat ? \Carbon\Carbon::parse($userPermission->createdat)->format('d/m/Y H:i') : '-' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Remove Override Button -->
                                    @php
                                        $permSlug = $userPermission->permission 
                                            ? "{$userPermission->permission->module}.{$userPermission->permission->resource}.{$userPermission->permission->action}"
                                            : "Permission ID {$userPermission->permissionid}";
                                    @endphp
                                    <form action="{{ route('usermanagement.user-permission.destroy', $userPermission->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus permission override {{ $permSlug }} untuk user {{ $userPermission->userid }}?');" class="inline">
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
                                    <p class="text-sm mt-1">
                                        {{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada permission overrides yang dikonfigurasi' }}
                                    </p>
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
            @keydown.window.escape="open = false"
            @click.self="open = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Tambah Permission Override</h3>
                        <p class="text-sm text-gray-600 mt-1">Override permission dari default jabatan untuk user tertentu</p>
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
                    <form action="{{ route('usermanagement.user-permission.store') }}" method="POST">
                        @csrf

                        <!-- User Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                User <span class="text-red-500">*</span>
                            </label>
                            <select name="userid" x-model="form.userid" @change="loadUserCompanies()" required
                                :disabled="loading"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100">
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
                            
                            <!-- Loading Indicator -->
                            <div x-show="loading" class="mt-2 flex items-center text-blue-600 text-sm">
                                <svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memuat data company...
                            </div>

                            <!-- User Info Display -->
                            <div x-show="selectedUser && !loading" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <div class="text-sm">
                                    <div class="flex items-center text-blue-800">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span class="font-medium" x-text="selectedUser?.name"></span>
                                    </div>
                                    <div class="mt-1 ml-6 text-blue-600 text-xs" x-text="selectedUser?.jabatan?.namajabatan || 'No Jabatan'"></div>
                                    <div class="mt-1 ml-6 text-blue-600 text-xs">
                                        <span x-text="Object.keys(userCompanies).length"></span> company tersedia
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Company Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Company <span class="text-red-500">*</span>
                            </label>
                            <select name="companycode" x-model="form.companycode" required
                                :disabled="!form.userid || loading || !hasUserCompanies()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100">
                                <option value="">-- Pilih Company --</option>
                                <template x-for="(companyName, code) in userCompanies" :key="code">
                                    <option :value="code" x-text="`${code} - ${companyName}`"></option>
                                </template>
                            </select>
                            <div class="text-xs mt-1" :class="hasUserCompanies() ? 'text-gray-500' : 'text-amber-600'">
                                <span x-show="!form.userid">Pilih user terlebih dahulu</span>
                                <span x-show="form.userid && !hasUserCompanies() && !loading">⚠️ User tidak memiliki akses ke company manapun</span>
                                <span x-show="form.userid && hasUserCompanies()">Hanya company yang dapat diakses user yang ditampilkan</span>
                            </div>
                        </div>

                        <!-- Permission Type -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Override Type <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all" 
                                       :class="form.permissiontype === 'GRANT' ? 'border-green-500 bg-green-50 shadow-sm' : 'border-gray-300 hover:bg-gray-50'">
                                    <input type="radio" name="permissiontype" value="GRANT" x-model="form.permissiontype" class="sr-only">
                                    <div class="flex items-center">
                                        <div class="w-5 h-5 rounded-full border-2 mr-3 flex items-center justify-center"
                                             :class="form.permissiontype === 'GRANT' ? 'border-green-500 bg-green-500' : 'border-gray-300'">
                                            <div class="w-2.5 h-2.5 rounded-full bg-white" x-show="form.permissiontype === 'GRANT'"></div>
                                        </div>
                                        <div>
                                            <div class="font-semibold" :class="form.permissiontype === 'GRANT' ? 'text-green-700' : 'text-gray-700'">GRANT</div>
                                            <div class="text-xs" :class="form.permissiontype === 'GRANT' ? 'text-green-600' : 'text-gray-500'">Berikan akses</div>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                       :class="form.permissiontype === 'DENY' ? 'border-red-500 bg-red-50 shadow-sm' : 'border-gray-300 hover:bg-gray-50'">
                                    <input type="radio" name="permissiontype" value="DENY" x-model="form.permissiontype" class="sr-only">
                                    <div class="flex items-center">
                                        <div class="w-5 h-5 rounded-full border-2 mr-3 flex items-center justify-center"
                                             :class="form.permissiontype === 'DENY' ? 'border-red-500 bg-red-500' : 'border-gray-300'">
                                            <div class="w-2.5 h-2.5 rounded-full bg-white" x-show="form.permissiontype === 'DENY'"></div>
                                        </div>
                                        <div>
                                            <div class="font-semibold" :class="form.permissiontype === 'DENY' ? 'text-red-700' : 'text-gray-700'">DENY</div>
                                            <div class="text-xs" :class="form.permissiontype === 'DENY' ? 'text-red-600' : 'text-gray-500'">Tolak akses</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <div class="mt-2 p-3 bg-gray-50 rounded-md border border-gray-200">
                                <div class="text-xs text-gray-600">
                                    <div class="font-medium mb-1">Penjelasan:</div>
                                    <ul class="space-y-1 ml-4 list-disc">
                                        <li><strong>GRANT:</strong> Memberikan akses permission meskipun jabatan user tidak memilikinya</li>
                                        <li><strong>DENY:</strong> Menolak akses permission meskipun jabatan user memilikinya</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Permission Selection -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Permission <span class="text-red-500">*</span>
                            </label>
                            <select name="permissionid" x-model="form.permissionid" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Permission --</option>
                                @foreach($permissions as $category => $categoryPermissions)
                                    <optgroup label="{{ $category }}">
                                        @foreach($categoryPermissions as $permission)
                                            <option value="{{ $permission->id }}">
                                                {{ $permission->module }}.{{ $permission->resource }}.{{ $permission->action }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">Permission yang akan di-override untuk user ini</div>
                        </div>

                        <!-- Reason -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Reason / Alasan
                            </label>
                            <textarea name="reason" x-model="form.reason"
                                placeholder="Contoh: User membutuhkan akses sementara untuk project khusus..."
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            <div class="text-xs text-gray-500 mt-1">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Dokumentasi untuk audit trail (opsional tapi sangat disarankan)
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0 pt-4 border-t border-gray-200">
                            <button type="button" @click="open = false"
                                class="w-full sm:w-auto px-5 py-2.5 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                :disabled="!form.userid || !form.companycode || !form.permissionid"
                                class="w-full sm:w-auto px-5 py-2.5 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors duration-150">
                                <span class="flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Simpan Permission Override
                                </span>
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