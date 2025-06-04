<x-layout>
    <x-slot:title>Submenu</x-slot:title>
    <x-slot:navbar>Submenu Navbar</x-slot:navbar>
    <x-slot:nav>Submenu Navigation</x-slot:nav>

    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">

    {{-- Flash Message --}}
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Berhasil!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false" style="cursor:pointer;">&times;</span>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Gagal!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false" style="cursor:pointer;">&times;</span>
    </div>
    @endif

    <div x-data="{
        open: false,
        mode: 'create',
        form: { submenuid: '', menuid: '', submenuname: '', slug: '' , parentid: '' },
        resetForm() {
            this.mode = 'create';
            this.form = { submenuid: '', menuid: '', submenuname: '', slug: '', parentid: '' };
            this.open = true;
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-2">
            <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                </svg>
                New Submenu
            </button>

            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                <label for="search" class="text-xs font-medium text-gray-700">Search:</label>
                <input type="text" name="search" id="search"
                    value="{{ request('search') }}"
                    class="text-xs mt-1 block w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    onkeydown="if(event.key==='Enter') this.form.submit()" />
            </form>

            <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                <label for="perPage" class="text-xs font-medium text-gray-700">Items per page:</label>
                <select name="perPage" id="perPage"
                    onchange="this.form.submit()"
                    class="text-xs mt-1 block w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="10" {{ (int)request('perPage', $perPage) === 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ (int)request('perPage', $perPage) === 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ (int)request('perPage', $perPage) === 50 ? 'selected' : '' }}>50</option>
                </select>
            </form>
        </div>

        {{-- Table --}}
        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b bg-gray-100">Menu Group</th>
                            <th class="py-2 px-4 border-b bg-gray-100">Submenu Name</th>
                            <th class="py-2 px-4 border-b bg-gray-100">Slug</th>
                            <th class="py-2 px-4 border-b bg-gray-100 w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $d)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $d->menu_name }}</td>
                            <td class="py-2 px-4 border-b">{{ $d->name }}</td>
                            <td class="py-2 px-4 border-b">{{ $d->slug }}</td>
                            <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center gap-2">
                                    <button
                                        @click="
                                            mode = 'edit';
                                            form.submenuid = '{{ $d->submenuid }}';
                                            form.menuid = '{{ $d->menuid }}';
                                            form.submenuname = '{{ $d->name }}';
                                            form.slug = '{{ $d->slug }}';
                                            form.parentid = '{{ $d->parentid }}';
                                            open = true;
                                        "
                                        class="text-blue-600 hover:text-blue-800">
                                        ✎
                                    </button>
                                    <form action="{{ url("aplikasi/submenu/{$d->submenuid}/{$d->name}") }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $data->links() }}
            </div>
        </div>

        {{-- Modal --}}
        <div x-show="open" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @keydown.window.escape="open = false">
            <div class="bg-white rounded-md p-6 w-96 max-w-full shadow-lg">
                <h2 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Tambah Submenu' : 'Edit Submenu'"></h2>
                <form :action="mode === 'create' ? '{{ route('aplikasi.submenu.store') }}' : '{{ url('aplikasi/submenu') }}/' + form.submenuid" method="POST">
                    @csrf
                    <template x-if="mode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    {{-- Menu ID (Dropdown) --}}
                    <div class="mb-4">
                        <label for="menuid" class="block text-sm font-medium text-gray-700">Menu Group</label>
                        <select id="menuid" name="menuid" x-model="form.menuid" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-blue-500">
                            <option value="">-- Pilih Menu --</option>
                            @foreach ($allMenu as $menu)
                                <option value="{{ $menu->menuid }}">{{ $menu->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Submenu Name --}}
                    <div class="mb-4">
                        <label for="submenuname" class="block text-sm font-medium text-gray-700">Nama Submenu</label>
                        <input type="text" id="submenuname" name="submenuname" x-model="form.submenuname" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-blue-500" />
                    </div>

                    {{-- Slug --}}
                    <div class="mb-4">
                        <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                        <input type="text" id="slug" name="slug" x-model="form.slug" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-blue-500" />
                    </div>

                    
                    {{-- submenu parent --}}
                    <div class="mb-4">
                        <label for="parentid" class="block text-sm font-medium text-gray-700">Submenu parent code</label>
                        <input type="text" id="parentid" name="parentid" x-model="form.parentid" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-blue-500" />
                    </div>

                    {{-- Tombol --}}
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="open = false"
                            class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layout>
