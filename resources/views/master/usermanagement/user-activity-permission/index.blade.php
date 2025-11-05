{{-- resources/views/master/usermanagement/user-activity-permission/index.blade.php --}}
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
        activityModal: false,
        editMode: false,
        selectedUser: null,
        selectedUserName: '',
        selectedCompanycode: '',
        selectedActivities: [],
        currentUserActivities: [],
        isLoadingActivities: false,
        availableUsers: @js($users->toArray()),
        availableActivityGroups: @js(array_values($activitygroup->toArray())),
        
        openActivityModal() {
            this.editMode = false;
            this.selectedUser = null;
            this.selectedUserName = '';
            this.selectedCompanycode = '';
            this.selectedActivities = [];
            this.currentUserActivities = [];
            this.isLoadingActivities = false;
            this.activityModal = true;
        },
        
        openEditModal(userid, username, companycode, activities) {
            this.editMode = true;
            this.selectedUser = userid;
            this.selectedUserName = username;
            this.selectedCompanycode = companycode;
            
            // Parse activities (comma-separated string to array)
            this.selectedActivities = activities ? activities.split(',').map(s => s.trim()) : [];
            this.currentUserActivities = [...this.selectedActivities];
            this.isLoadingActivities = false;
            this.activityModal = true;
        },
        
        loadCurrentUserActivities() {
            if (!this.selectedUser) {
                this.selectedActivities = [];
                this.currentUserActivities = [];
                return;
            }
            
            // Find selected user name
            const user = this.availableUsers.find(u => u.userid === this.selectedUser);
            this.selectedUserName = user ? user.name : '';
            
            this.isLoadingActivities = true;
            
            // Fetch current user activities via AJAX
            fetch(`/usermanagement/user-activities/${this.selectedUser}/{{ session('companycode') }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Response berisi array of activities string (comma-separated)
                        // Kita parse jadi array
                        const activitiesStr = data.activities[0] || '';
                        this.currentUserActivities = activitiesStr ? activitiesStr.split(',').map(s => s.trim()) : [];
                        this.selectedActivities = [...this.currentUserActivities];
                    } else {
                        this.currentUserActivities = [];
                        this.selectedActivities = [];
                    }
                })
                .catch(error => {
                    console.error('Error loading activities:', error);
                    this.currentUserActivities = [];
                    this.selectedActivities = [];
                })
                .finally(() => {
                    this.isLoadingActivities = false;
                });
        },
        
        toggleActivity(activitygroup) {
            const index = this.selectedActivities.indexOf(activitygroup);
            if (index > -1) {
                this.selectedActivities.splice(index, 1);
            } else {
                this.selectedActivities.push(activitygroup);
            }
        },
        
        isActivitySelected(activitygroup) {
            return this.selectedActivities.includes(activitygroup);
        },
        
        selectAllActivities() {
            this.selectedActivities = [...this.availableActivityGroups];
        },
        
        deselectAllActivities() {
            this.selectedActivities = [];
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Header Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">

                <!-- Info Badge & Button -->
                <div class="flex items-center space-x-4">
                    <!-- Company Info Badge -->
                    <div class="flex items-center space-x-2 bg-blue-50 border border-blue-200 px-4 py-2 rounded-md">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0h3m2 0h5M9 7h6m-6 4h6m-6 4h6"></path>
                        </svg>
                        <div>
                            <div class="text-xs text-blue-600 font-medium">Current Company</div>
                            <div class="text-sm font-bold text-blue-900">{{ $companycode }}</div>
                        </div>
                    </div>

                    <!-- Add Activity Button -->
                    <button @click="openActivityModal()"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 flex items-center gap-2 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Assign Activity Groups</span>
                    </button>
                </div>

                <!-- Search and Controls -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">

                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="User ID, Nama, Activity..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                        <a href="{{ route('usermanagement.user-activity-permission.index') }}"
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

        <!-- Table Section -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity Groups</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Granted By</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $activity)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $activity->user->name ?? 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $activity->userid }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="text-sm text-gray-900">
                                    <div class="font-medium">{{ $activity->companycode }}</div>
                                    @if($activity->company)
                                    <div class="text-xs text-gray-500">{{ $activity->company->name }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                @php
                                // Parse comma-separated codes: "I,II,III"
                                $codes = collect(explode(',', $activity->activitygroup))
                                    ->map(fn($c) => trim($c))
                                    ->filter();

                                // Flip untuk mapping: ["IV" => "Pemanenan", ...]
                                $codeToName = $activitygroup->flip();

                                // Convert codes to names
                                $names = $codes->map(fn($c) => $codeToName->get($c, $c));
                                @endphp

                                <div class="flex flex-wrap gap-1">
                                    @foreach ($names as $name)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $name }}
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="text-sm text-gray-500">
                                    {{ $activity->grantedby ?? '-' }}
                                </div>
                                @if($activity->createdat)
                                <div class="text-xs text-gray-400">
                                    {{ $activity->createdat->format('d M Y H:i') }}
                                </div>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Edit Button -->
                                    <button @click="openEditModal('{{ $activity->userid }}', '{{ addslashes($activity->user->name ?? 'N/A') }}', '{{ $activity->companycode }}', '{{ $activity->activitygroup }}')"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-1 transition-all duration-150"
                                        title="Edit Activity Groups">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('usermanagement.user-activity-permission.destroy', [$activity->userid, $activity->companycode, $activity->activitygroup]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Hapus semua activity groups dari user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-1 transition-all duration-150"
                                            title="Hapus">
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
                            <td colspan="5" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada data activity assignment</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada user yang di-assign activity groups untuk company ini' }}</p>
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

        <!-- Activity Assignment Modal -->
        <div x-show="activityModal"
            x-cloak
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
            @click.self="activityModal = false">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white max-h-[80vh] overflow-y-auto">
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center mb-4 pb-3 border-b">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" x-text="editMode ? 'Edit Activity Groups' : 'Assign Activity Groups'">
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Company: <span class="font-medium text-blue-600" x-text="editMode ? selectedCompanycode : '{{ $companycode }}'"></span>
                            </p>
                        </div>
                        <button @click="activityModal = false"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Form Content -->
                    <form method="POST" action="{{ route('usermanagement.user-activity-permission.assign') }}">
                        @csrf
                        <input type="hidden" name="companycode" :value="editMode ? selectedCompanycode : '{{ $companycode }}'">

                        <!-- User Selection (Create Mode Only) -->
                        <div class="mb-4" x-show="!editMode">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih User <span class="text-red-500">*</span>
                            </label>
                            <select name="userid"
                                x-model="selectedUser"
                                @change="loadCurrentUserActivities()"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :required="!editMode">
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->userid }}">
                                    {{ $user->userid }} - {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                            <div class="text-xs text-gray-500 mt-1">
                                Hanya menampilkan user yang memiliki akses ke company ini
                            </div>
                        </div>

                        <!-- User Info (Edit Mode) -->
                        <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200" x-show="editMode">
                            <input type="hidden" name="userid" :value="selectedUser">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-blue-900" x-text="selectedUserName"></div>
                                    <div class="text-xs text-blue-600" x-text="selectedUser"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div x-show="isLoadingActivities && !editMode" class="text-center py-4">
                            <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                            <p class="mt-2 text-gray-600 text-sm">Loading current activities...</p>
                        </div>

                        <!-- Activity Groups Selection -->
                        <div class="mb-4" x-show="(selectedUser && !isLoadingActivities) || editMode">
                            <div class="flex justify-between items-center mb-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Activity Groups
                                </label>
                                <div class="space-x-2">
                                    <button type="button"
                                        @click="selectAllActivities()"
                                        class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        Select All
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button"
                                        @click="deselectAllActivities()"
                                        class="text-xs text-red-600 hover:text-red-800 font-medium">
                                        Deselect All
                                    </button>
                                </div>
                            </div>

                            <!-- Activity Checkboxes -->
                            <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                                @foreach($activitygroup as $groupname => $activityname)
                                <label class="flex items-center space-x-3 p-2 border rounded hover:bg-blue-50 cursor-pointer transition-colors">
                                    <input type="checkbox"
                                        name="activitygroups[]"
                                        value="{{ $activityname }}"
                                        x-model="selectedActivities"
                                        :checked="isActivitySelected('{{ $activityname }}')"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <div class="flex-1">
                                        <div class="font-medium text-sm text-gray-900">{{ $activityname }}</div>
                                        <div class="text-xs text-gray-500">{{ $groupname }}</div>
                                    </div>
                                </label>
                                @endforeach
                            </div>

                            <!-- Selected Count -->
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg" x-show="selectedActivities.length > 0">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-900">
                                        <span x-text="selectedActivities.length"></span> activity groups dipilih
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0 pt-4 border-t">
                            <button type="button" @click="activityModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                :disabled="!selectedUser && !editMode"
                                :class="(!selectedUser && !editMode) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150"
                                x-text="editMode ? 'Update Activity Groups' : 'Simpan Activity Groups'">
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</x-layout>