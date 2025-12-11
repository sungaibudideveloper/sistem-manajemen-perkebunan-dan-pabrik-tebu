{{-- resources/views/info-updates/notifications/create.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>Info & Updates</x-slot:navbar>
    <x-slot:nav>Create Notification</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        <div class="px-6 py-4">
            <form action="{{ route('info-updates.notifications.admin.store') }}" method="POST" x-data="notificationForm()">
                @csrf

                <!-- Target Companies -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Target Companies <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <button type="button" @click="showDropdown = !showDropdown"
                            class="flex items-center justify-between border rounded-md border-gray-300 p-3 bg-white w-full text-left hover:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                            :class="{ 'border-red-500': @js($errors->has('companycodes')) }">
                            <span class="text-gray-700" x-text="selectedCompaniesText"></span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="showDropdown" @click.away="showDropdown = false" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <div class="p-3 space-y-2">
                                @foreach ($companies as $company)
                                <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="companycodes[]" value="{{ $company->companycode }}"
                                        x-model="selectedCompanies"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ $company->companycode }} - {{ $company->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @error('companycodes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Target Role (Optional) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Target Jabatan (Optional)
                    </label>
                    <div class="relative">
                        <button type="button" @click="showJabatanDropdown = !showJabatanDropdown"
                            class="flex items-center justify-between border rounded-md border-gray-300 p-3 bg-white w-full text-left hover:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                            <span class="text-gray-700" x-text="selectedJabatanText"></span>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="showJabatanDropdown" @click.away="showJabatanDropdown = false" x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            class="absolute z-10 mt-2 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <div class="p-3 space-y-2">
                                @foreach ($jabatan as $jab)
                                <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                    <input type="checkbox" name="target_jabatan[]" value="{{ $jab->idjabatan }}"
                                        x-model="selectedJabatan"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ $jab->namajabatan }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Leave empty to send to all jabatan in selected companies. Select multiple to target specific roles.</p>
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Notification Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        maxlength="200"
                        placeholder="Enter notification title..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Body -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Notification Body <span class="text-red-500">*</span>
                    </label>
                    <textarea name="body" rows="5" required
                        placeholder="Enter notification message..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('body') border-red-500 @enderror">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>

                <!-- Action URL (Optional) -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Action URL (Optional)
                    </label>
                    <input type="url" name="action_url" value="{{ old('action_url') }}"
                        placeholder="https://example.com/page"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">URL to redirect when notification is clicked</p>
                </div>

                <!-- Buttons -->
                <div class="flex items-center space-x-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Create Notification
                    </button>
                    <a href="{{ route('info-updates.notifications.admin.index') }}"
                        class="px-6 py-2 bg-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function notificationForm() {
        return {
            showDropdown: false,
            showJabatanDropdown: false,
            selectedCompanies: {{ json_encode(old('companycodes', [])) }},
            selectedJabatan: {{ json_encode(old('target_jabatan', [])) }},

            get selectedCompaniesText() {
                if (this.selectedCompanies.length === 0) {
                    return 'Select companies...';
                }
                if (this.selectedCompanies.length === 1) {
                    return this.selectedCompanies[0];
                }
                return `${this.selectedCompanies.length} companies selected`;
            },

            get selectedJabatanText() {
                if (this.selectedJabatan.length === 0) {
                    return 'All jabatan (optional)';
                }
                if (this.selectedJabatan.length === 1) {
                    return '1 jabatan dipilih';
                }
                return `${this.selectedJabatan.length} jabatan dipilih`;
            }
        }
    }
    </script>

    <style>
    [x-cloak] { display: none !important; }
    </style>
</x-layout>