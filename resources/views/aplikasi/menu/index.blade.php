<x-layout>
    <x-slot:title>Menu</x-slot:title>
    <x-slot:navbar>Menu Navbar</x-slot:navbar>
    <x-slot:nav>Menu Navigation</x-slot:nav>

    <link rel="stylesheet" href="{{ asset('asset/font-awesome-6.5.1-all.min.css') }}">
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
        form: { menuid: '', name: '', slug: '' },
        resetForm() {
            this.mode = 'create';
            this.form = { menuid: '', name: '', slug: '' };
            this.open = true;
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">
        <div class="flex items-center justify-between px-4 py-2">
            <button @click="resetForm()"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2">
                <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7 7V5" />
                </svg>
                New Data
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

        <div class="mx-auto px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm text-center">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b bg-gray-100 text-gray-700 w-1">No.</th>
                            <th class="py-2 px-4 border-b bg-gray-100 text-gray-700">Nama Menu</th>
                            <th class="py-2 px-4 border-b bg-gray-100 text-gray-700">Slug</th>
                            <th class="py-2 px-4 border-b bg-gray-100 text-gray-700 w-40">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $d)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $d->menuid }}</td>
                            <td class="py-2 px-4 border-b">{{ $d->name }}</td>
                            <td class="py-2 px-4 border-b">{{ $d->slug }}</td>
                            <td class="py-2 px-4 border-b">
                                <div class="flex items-center justify-center space-x-3">
                                    <button
                                        @click="mode = 'edit'; form.menuid = '{{ $d->menuid }}'; form.name = '{{ $d->name }}'; form.slug = '{{ $d->slug }}'; open = true;"
                                        class="group flex items-center text-blue-600 hover:text-blue-800 rounded-md px-2 py-1 text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="w-5 h-5 fill-current text-blue-600">
                                            <path d="M441 58.9L453.1 71c9.4 9.4 9.4 24.6 0 33.9L424 134.1 377.9 88 407 58.9c9.4-9.4 24.6-9.4 33.9 0zM209.8 256.2L344 121.9 390.1 168 255.8 302.2c-2.9 2.9-6.5 5-10.4 6.1l-58.5 16.7 16.7-58.5c1.1-3.9 3.2-7.5 6.1-10.4zM373.1 25L175.8 222.2c-8.7 8.7-15 19.4-18.3 31.1l-28.6 100c-2.4 8.4-.1 17.4 6.1 23.6s15.2 8.5 23.6 6.1l100-28.6c11.8-3.4 22.5-9.7 31.1-18.3L487 138.9c28.1-28.1 28.1-73.7 0-101.8L474.9 25C446.8-3.1 401.2-3.1 373.1 25zM88 64C39.4 64 0 103.4 0 152L0 424c0 48.6 39.4 88 88 88l272 0c48.6 0 88-39.4 88-88l0-112c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 112c0 22.1-17.9 40-40 40L88 464c-22.1 0-40-17.9-40-40l0-272c0-22.1 17.9-40 40-40l112 0c13.3 0 24-10.7 24-24s-10.7-24-24-24L88 64z" />
                                        </svg>

                                    </button>

                                    <form action="{{ url("aplikasi/menu/{$d->menuid}/{$d->name}") }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="group flex items-center text-red-600 hover:text-red-800 rounded-md px-2 py-1 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="w-5 h-5 fill-current text-red-600">
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
            <div class="mt-4">
                {{ $data->links() }}
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-cloak
            @keydown.window.escape="open = false">
            <div class="bg-white rounded-md p-6 w-96 max-w-full shadow-lg">
                <h2 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Tambah Menu' : 'Edit Menu'"></h2>
                <form :action="mode === 'create' ? '{{ route('aplikasi.menu.store') }}' :  '{{ url('aplikasi/menu') }}/' + form.menuid" method="POST">
                    @csrf
                    <template x-if="mode === 'edit'">
                        @method('PUT')
                    </template>
                    <!-- Nama Menu -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Menu</label>
                        <input type="text" id="name" name="name" x-model="form.name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- Slug -->
                    <div class="mb-4">
                        <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                        <input type="text" id="slug" name="slug" x-model="form.slug" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- Tombol -->
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