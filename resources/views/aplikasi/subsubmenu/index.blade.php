<x-layout>
    <x-slot:title>Subsubmenu</x-slot:title>
    <x-slot:navbar>Subsubmenu Navbar</x-slot:navbar>
    <x-slot:nav>Subsubmenu Navigation</x-slot:nav>

    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">

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

    <div x-data="{
        open: false,
        mode: 'create',
        form: { subsubmenuid: '', submenuid: '', subsubmenuname: ''},
        resetForm() {
            this.mode = 'create';
            this.form = { subsubmenuid: '', submenuid: '', subsubmenuname: ''};
            this.open = true;
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Section with Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <!-- Mobile-First Responsive Layout -->
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                
                <!-- New Subsubmenu Button -->
                <div class="flex justify-start">
                    <button @click="resetForm()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                        </svg>
                        <span class="hidden sm:inline">New Subsubmenu</span>
                        <span class="sm:hidden">New</span>
                    </button>
                </div>

                <!-- Search and Per Page Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    
                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Search:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Search subsubmenu..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        <button type="submit" class="sm:hidden bg-blue-500 text-white px-3 py-2 rounded-md hover:bg-blue-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Per Page Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per page:</label>
                        <select name="perPage" id="perPage"
                            onchange="this.form.submit()"
                            class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
                        </select>
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
                            <th class="py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Submenu Group
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subsubmenu Name
                            </th>
                            <th class="py-3 px-2 sm:px-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($data as $d)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-2 sm:px-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $d->submenu_name }}</div>
                                <!-- Show additional info on mobile if needed -->
                                <div class="text-xs text-gray-500 sm:hidden mt-1">
                                    ID: {{ $d->submenuid }}
                                </div>
                            </td>
                            <td class="py-3 px-2 sm:px-4 text-sm text-gray-700">
                                <div class="font-medium">{{ $d->name }}</div>
                            </td>
                            <td class="py-3 px-2 sm:px-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <button
                                        @click="
                                            mode = 'edit';
                                            form.subsubmenuid = '{{ $d->subsubmenuid }}';
                                            form.submenuid = '{{ $d->submenuid }}';
                                            form.subsubmenuname = '{{ $d->name }}';
                                            open = true;
                                        "
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-4 h-4 fill-current">
                                            <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ url("aplikasi/subsubmenu/{$d->subsubmenuid}/{$d->name}") }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
                                            title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-4 h-4 fill-current">
                                                <path d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $data->links() }}
            </div>
        </div>

        <!-- Responsive Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @keydown.window.escape="open = false">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900" 
                        x-text="mode === 'create' ? 'Tambah Subsubmenu' : 'Edit Subsubmenu'"></h2>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <form :action="mode === 'create' ? '{{ route('masterdata.subsubmenu.store') }}' : '{{ url('aplikasi/subsubmenu') }}/' + form.subsubmenuid" 
                          method="POST">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Submenu Group Dropdown -->
                        <div class="mb-6">
                            <label for="submenuid" class="block text-sm font-medium text-gray-700 mb-2">Submenu Group</label>
                            <select id="submenuid" name="submenuid" x-model="form.submenuid" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">-- Pilih Submenu --</option>
                                @foreach ($allMenu as $a)
                                <option value="{{ $a->submenuid }}">{{ $a->name }} ({{ $a->menuname }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Select the parent submenu for this subsubmenu</p>
                        </div>

                        <!-- Subsubmenu Name -->
                        <div class="mb-6">
                            <label for="subsubmenuname" class="block text-sm font-medium text-gray-700 mb-2">Nama Subsubmenu</label>
                            <input type="text" id="subsubmenuname" name="subsubmenuname" x-model="form.subsubmenuname" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Masukkan nama subsubmenu" />
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                            <button type="button" @click="open = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                <span x-text="mode === 'create' ? 'Tambah' : 'Simpan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar for modal */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Mobile-friendly focus styles */
        @media (max-width: 768px) {
            input:focus, select:focus, button:focus {
                outline: 2px solid #3b82f6;
                outline-offset: 2px;
            }
        }
    </style>

</x-layout>