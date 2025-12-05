{{-- resources/views/master/usermanagement/permissions-masterdata/index.blade.php --}}
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
        categoryFilterOpen: false,
        mode: 'create',
        editUrl: '',
        selectedCategories: @js(request('categories', [])),
        form: {
            permissionid: '',
            permissionname: '',
            category: '',
            description: '',
            isactive: 1
        },
        resetForm() {
            this.mode = 'create';
            this.editUrl = '';
            this.form = {
                permissionid: '',
                permissionname: '',
                category: '',
                description: '',
                isactive: 1
            };
            this.open = true;
        },
        editForm(data, url) {
            this.mode = 'edit';
            this.editUrl = url;
            this.form = {
                permissionid: data.permissionid,
                permissionname: data.permissionname,
                category: data.category,
                description: data.description,
                isactive: data.isactive
            };
            this.open = true;
        },
        toggleCategory(category) {
            const index = this.selectedCategories.indexOf(category);
            if (index > -1) {
                this.selectedCategories.splice(index, 1);
            } else {
                this.selectedCategories.push(category);
            }
            // DON'T auto apply - wait for user to click Apply
        },
        isCategorySelected(category) {
            return this.selectedCategories.includes(category);
        },
        applyFilter() {
            const url = new URL(window.location.href);
            if (this.selectedCategories.length > 0) {
                url.searchParams.set('categories', this.selectedCategories.join(','));
            } else {
                url.searchParams.delete('categories');
            }
            window.location.href = url.toString();
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
                        <span class="hidden sm:inline">Tambah Permission</span>
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
                            placeholder="Permission, Category, Deskripsi..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                            <a href="{{ route('usermanagement.permissions-masterdata.index') }}" 
                               class="text-gray-500 hover:text-gray-700 px-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                        @if(request('perPage'))
                            <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                        @if(request('categories'))
                            <input type="hidden" name="categories" value="{{ request('categories') }}">
                        @endif
                    </form>

                    <!-- Category Filter -->
                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filter Category
                            <template x-if="selectedCategories.length > 0">
                                <span class="ml-1 bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full" x-text="selectedCategories.length"></span>
                            </template>
                            <svg class="ml-1 w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition
                            class="absolute right-0 mt-1 w-64 bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-48 overflow-y-auto">
                            <div class="py-2">
                                @foreach($categories as $category)
                                <label class="flex items-center px-4 py-2 hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" 
                                           :checked="isCategorySelected('{{ $category }}')"
                                           @change="toggleCategory('{{ $category }}')"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-3 text-sm text-gray-700">{{ $category }}</span>
                                </label>
                                @endforeach
                            </div>
                            <div class="border-t border-gray-200 p-3 flex flex-col space-y-2">
                                <button @click="applyFilter()" 
                                        class="w-full bg-blue-600 text-white px-3 py-2 text-xs rounded hover:bg-blue-700 transition-colors">
                                    Apply Filter
                                </button>
                                @if($categories->count() > 0)
                                <button @click="selectedCategories = []; applyFilter()" 
                                        class="w-full text-xs text-gray-500 hover:text-gray-700 transition-colors">
                                    Clear All Filters
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Per Page Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per halaman:</label>
                        <select name="perPage" id="perPage" onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('categories'))
                            <input type="hidden" name="categories" value="{{ request('categories') }}">
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
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permission Name</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result->sortBy('permissionid') as $permission)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 text-sm font-medium text-gray-900">{{ $permission->permissionid }}</td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="font-medium">{{ $permission->permissionname }}</div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $permission->category }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="max-w-xs truncate" title="{{ $permission->description }}">
                                    {{ $permission->description ?: '-' }}
                                </div>
                            </td>
                            <td class="py-3 px-3 text-center text-sm">
                                @if($permission->isactive == 1)
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
                                            permissionid: {{ $permission->permissionid }},
                                            permissionname: {{ json_encode($permission->permissionname) }},
                                            category: {{ json_encode($permission->category) }},
                                            description: {{ json_encode($permission->description) }},
                                            isactive: {{ $permission->isactive ?? 0 }}
                                        }, {{ json_encode(route('usermanagement.permissions-masterdata.update', $permission->permissionid)) }})'
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('usermanagement.permissions-masterdata.destroy', $permission->permissionid) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menonaktifkan permission {{ $permission->permissionname }}? Permission ini mungkin sedang digunakan.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                            title="Nonaktifkan">
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
                            <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2L4.257 10.257a6 6 0 017.743-7.743L15 5a2 2 0 012 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data permission</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada permission yang terdaftar' }}</p>
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
                    <h3 class="text-xl font-semibold text-gray-900" x-text="mode === 'create' ? 'Tambah Permission' : 'Edit Permission'"></h3>
                    <button @click="open = false"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form :action="mode === 'create' ? '{{ route('usermanagement.permissions-masterdata.store') }}' : editUrl" method="POST">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Permission ID (Edit only) -->
                        <template x-if="mode === 'edit'">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Permission ID</label>
                                <input type="text" x-model="form.permissionid" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-600 cursor-not-allowed" 
                                    readonly>
                            </div>
                        </template>

                        <!-- Permission Name -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permission Name <span class="text-red-500">*</span></label>
                            <input type="text" name="permissionname" x-model="form.permissionname"
                                placeholder="Contoh: Create User, Edit Company"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required maxlength="100">
                            <div class="text-xs text-gray-500 mt-1">Nama permission harus unik dan deskriptif</div>
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                            <select name="category" x-model="form.category" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                                <option value="__custom__">+ Buat Category Baru</option>
                            </select>
                            
                            <!-- Custom Category Input (show when custom selected) -->
                            <div x-show="form.category === '__custom__'" x-transition class="mt-2">
                                <input type="text" 
                                    placeholder="Masukkan nama category baru"
                                    @input="form.category = $event.target.value"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    maxlength="50">
                            </div>
                            
                            <div class="text-xs text-gray-500 mt-1">Pilih category yang sudah ada atau buat category baru</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" x-model="form.description"
                                placeholder="Deskripsi detail tentang fungsi permission ini"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                            <div class="text-xs text-gray-500 mt-1">Jelaskan dengan detail apa yang bisa dilakukan dengan permission ini</div>
                        </div>

                        <!-- Status Active -->
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="isactive" 
                                       :checked="form.isactive == 1" 
                                       value="1" class="mr-2">
                                <span class="text-sm font-medium text-gray-700">Status Aktif</span>
                            </label>
                            <div class="text-xs text-gray-500 mt-1">Permission nonaktif tidak akan muncul dalam assignment</div>
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
    </style>

</x-layout>