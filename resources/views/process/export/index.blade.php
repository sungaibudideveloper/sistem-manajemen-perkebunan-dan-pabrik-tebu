<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $title }}</x-slot:navbar>

    <div class="p-4 bg-white rounded-md shadow-md">
        {{-- <div class="mb-4">
            <label class="block">Filter</label>
            <select name="filterkml" class="border rounded-md border-gray-300 p-2 w-full max-w-[20ch]" required>
                <option value="" disabled selected>--Filter--</option>
                <option value="comp" class="text-black">Kebun</option>
                <option value="blok" class="text-black">Blok</option>
            </select>
        </div> --}}
        <form action="{{ route('export.kml') }}" method="post">
            @csrf
            <div class="border-b mb-4">
                <span class="block text-md mb-3 font-medium">Generate KML for Google Earth</span>
            </div>
            <button type="submit" class="flex items-center gap-2 font-medium text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-md shadow-sm">
                <svg class="w-5 h-5 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M9.586 2.586A2 2 0 0 1 11 2h2a2 2 0 0 1 2 2v.089l.473.196.063-.063a2.002 2.002 0 0 1 2.828 0l1.414 1.414a2 2 0 0 1 0 2.827l-.063.064.196.473H20a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-.089l-.196.473.063.063a2.002 2.002 0 0 1 0 2.828l-1.414 1.414a2 2 0 0 1-2.828 0l-.063-.063-.473.196V20a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2v-.089l-.473-.196-.063.063a2.002 2.002 0 0 1-2.828 0l-1.414-1.414a2 2 0 0 1 0-2.827l.063-.064L4.089 15H4a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2h.09l.195-.473-.063-.063a2 2 0 0 1 0-2.828l1.414-1.414a2 2 0 0 1 2.827 0l.064.063L9 4.089V4a2 2 0 0 1 .586-1.414ZM8 12a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/>
                  </svg>                  
                <span>Generate</span>
            </button>
        </form>
    </div>
    <style>
        select:invalid {
            color: gray;
        }
    </style>
</x-layout>
