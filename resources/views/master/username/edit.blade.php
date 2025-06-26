<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    <x-slot:navnav>{{ $title }}</x-slot:navnav>

    <div class="mx-4 pb-4 bg-white rounded-md shadow-md p-4">
        @error('duplicate')
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 dark:bg-gray-800 dark:text-red-400 w-fit">
                {{ $message }}</div>
        @enderror

        <form
            action="{{ route('master.username.update', ['userid' => $user->userid, 'companycode' => $usercompany->companycode]) }}"
            method="POST">
            @csrf
            @method('PUT')
            <div class="gap-4">
                <div class="mb-4">
                    <label class="block text-md">Username</label>
                    <input type="text" name="usernm" value="{{ $user->userid }}" autocomplete="off"
                        class="border rounded-md border-gray-300 p-2 w-auto focus:ring-0 focus:border-gray-300 bg-gray-100 text-gray-600" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-md">Name</label>
                    <input type="text" name="name" value="{{ $user->name }}" autocomplete="off"
                        class="border rounded-md border-gray-300 p-2 w-auto" required>
                </div>
                <div class="mb-4">
                    <label class="block text-md">Password</label>
                    <input type="text" name="password" autocomplete="off"
                        class="border rounded-md border-gray-300 p-2 w-auto" required>
                </div>
                <div class="mb-4">
                    <label class="block text-md">Kode Company</label>
                    <div class="relative">
                        <button type="button"
                            class="dropdown-button border rounded-md border-gray-300 bg-white p-2 w-auto text-left flex gap-2 justify-between focus:outline focus:outline-1 focus:outline-blue-600 focus:border-blue-600">
                            <span class="dropdown-text text-gray-500">--Pilih Company--</span>
                            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>

                        <div
                            class="mt-1 dropdown-menu absolute hidden border border-gray-300 bg-white rounded-md max-h-60 overflow-auto z-10 shadow-sm">
                            @php
                                $oldKdComp = is_array(old('companycode'))
                                    ? old('companycode')
                                    : explode(',', old('companycode', $usercompany->companycode));
                            @endphp

                            @foreach ($company as $comp)
                                <label class="flex items-center gap-x-2 px-4 py-2 cursor-pointer hover:bg-gray-200">
                                    <input type="hidden" name="companycode[]" value="">
                                    <input type="checkbox" name="companycode[]" value="{{ $comp->companycode }}" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-sm focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        {{ in_array($comp->companycode, $oldKdComp) ? 'checked' : '' }}>
                                    {{ $comp->companycode }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="flex items-center space-x-2 bg-green-500 text-white px-4 py-2 rounded w-full md:w-auto hover:bg-green-600">
                        <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                            viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 11.917 9.724 16.5 19 7.5" />
                        </svg>
                        <span>Save</span>
                    </button>
                    <a href="{{ route('masterdata.username.index') }}"
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
            </div>
        </form>
    </div>
    <style>
        .dropdown-menu {
            display: none;
        }

        .dropdown-menu.visible {
            display: block;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownButton = document.querySelector('.dropdown-button');
            const dropdownText = document.querySelector('.dropdown-text');
            const dropdownMenu = document.querySelector('.dropdown-menu');
            const checkboxes = document.querySelectorAll('.dropdown-menu input[type="checkbox"]');

            function updateDropdownText() {
                const selected = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.parentNode.textContent.trim());

                if (selected.length > 0) {
                    dropdownText.textContent = selected.join(', ');
                    dropdownText.classList.remove('text-gray-500');
                    dropdownText.classList.add('text-black');
                } else {
                    dropdownText.textContent = '--Pilih Company--';
                    dropdownText.classList.remove('text-black');
                    dropdownText.classList.add('text-gray-500');
                }
            }

            dropdownButton.addEventListener('click', function() {
                dropdownMenu.classList.toggle('visible');
            });

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateDropdownText);
            });

            updateDropdownText();

            document.addEventListener('click', function(e) {
                if (!dropdownButton.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.remove('visible');
                }
            });
        });
    </script>

</x-layout>
