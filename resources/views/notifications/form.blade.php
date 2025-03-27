<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>
    <x-slot:routeName>{{ $routeName }}</x-slot:routeName>

    <div class="container mx-auto py-4">
        <form action="{{ $url }}" method="POST">
            @csrf
            @method($method)
            <div class="mb-4">
                <label class="block text-md">To :</label>
                <div class="relative">
                    <!-- Dropdown Button -->
                    <button id="dropdownButton" type="button"
                        class="flex items-center justify-between border rounded-md border-gray-300 p-2 bg-white w-full max-w-[50ch] text-left text-gray-500 focus:ring-1 focus:ring-blue-600">
                        -- Pilih Company --
                        <svg class="mr-1 ml-2 w-4 h-4 text-gray-800" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="dropdownMenu"
                        class="absolute z-10 hidden bg-white border border-gray-300 rounded-md shadow-md mt-1 w-full max-w-[50ch]">
                        <div class="p-2 max-h-40 overflow-y-auto">
                            @foreach ($company as $comp)
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" id="comp-{{ $comp->kd_comp }}" name="kd_comp[]"
                                        value="{{ $comp->kd_comp }}" class="rounded-sm bg-gray-100 border-gray-300"
                                        {{ $comp->kd_comp == $notification->kd_comp ? 'checked' : '' }} />
                                    <label for="comp-{{ $comp->kd_comp }}" class="ml-2 text-black">
                                        {{ $comp->nama }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block">Title</label>
                <input type="text" name="title" value="{{ old('title', $notification->title) }}"
                    autocomplete="off" maxlength="50"
                    class="rounded-md p-2 w-full max-w-[50ch]
                {{ $errors->has('title') ? 'border-red-600 focus:ring-red-600 focus:border-red-600' : 'border-gray-300 focus:ring-blue-600 focus:border-blue-600' }}"
                    required>
                @error('title')
                    <div class="text-red-600">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-4">
                <label class="block">Body</label>
                <textarea name="body" class="border rounded-md border-gray-300 p-2 w-full max-w-[50ch]" rows="5" required>{{ old('body', $notification->body) }}</textarea>
            </div>
            <div class="mt-6 flex gap-2">
                <button type="submit"
                    class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-green-600">
                    <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 11.917 9.724 16.5 19 7.5" />
                    </svg>
                    <span>{{ $buttonSubmit }}</span>
                </button>
                <a href="{{ route('notifications.index') }}"
                    class="flex items-center space-x-2 bg-red-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-red-600">
                    <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18 17.94 6M18 18 6.06 6" />
                    </svg>
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
    <style>
        select:invalid {
            color: gray;
        }

        #dropdownMenu {
            display: none;
            transition:
        }

        #dropdownMenu.show {
            display: block;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const dropdownButton = document.getElementById("dropdownButton");
            const dropdownMenu = document.getElementById("dropdownMenu");

            dropdownButton.addEventListener("click", function() {
                dropdownMenu.classList.toggle("show");
            });

            document.addEventListener("click", function(event) {
                if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.remove("show");
                }
            });
        });
    </script>
</x-layout>
