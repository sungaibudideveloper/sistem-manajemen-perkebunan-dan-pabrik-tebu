{{-- resources/views/notifications/admin/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>Notification</x-slot:navbar>
    <x-slot:nav>Management</x-slot:nav>

    <!-- Success/Error Messages -->
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
            <span class="text-2xl">&times;</span>
        </button>
    </div>
    @endif

    @if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
        class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show = false">
            <span class="text-2xl">&times;</span>
        </button>
    </div>
    @endif

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">
        
        <!-- Header Actions -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Notification Management</h2>
                    <p class="text-sm text-gray-600">Kelola dan broadcast notifikasi ke user</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('notifications.index') }}" 
                   class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 transition-colors">
                    View User Notifications
                </a>
                @if(hasPermission('Create Notifikasi'))
                <a href="{{ route('notifications.create') }}" 
                   class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Create Notification</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Search & Filters -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                
                <!-- Search -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Search:</label>
                    <input type="text" name="search" id="search"
                        value="{{ request('search') }}"
                        placeholder="Title, Body, Company..."
                        class="text-xs w-full sm:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                        onkeydown="if(event.key==='Enter') this.form.submit()" />
                    @if(request('search'))
                        <a href="{{ route('notifications.admin.index') }}" 
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

                <!-- Per Page -->
                <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                    <label for="perPage" class="text-xs font-medium text-gray-700 whitespace-nowrap">Per page:</label>
                    <select name="perPage" id="perPage" onchange="this.form.submit()"
                        class="text-xs w-20 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                        <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ ($perPage ?? 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="px-4 py-4">
            <div class="overflow-x-auto rounded-md border border-gray-300">
                <table class="min-w-full bg-white text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase">Companies</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase">Target Jabatan</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $notif)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-3">
                                @if($notif->notification_type === 'manual')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Manual
                                </span>
                                @elseif($notif->notification_type === 'support_ticket')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                    Support Ticket
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    System
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <div class="max-w-xs">
                                    <p class="font-medium text-gray-900 truncate">{{ $notif->title }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ Str::limit($notif->body, 50) }}</p>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                {{ Str::limit($notif->companycode, 20) }}
                            </td>
                            <td class="py-3 px-3">
                                @if($notif->priority === 'high')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                    High
                                </span>
                                @elseif($notif->priority === 'medium')
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Medium
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    Low
                                </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                @if($notif->target_jabatan)
                                    @php
                                        $jabatanIds = explode(',', $notif->target_jabatan);
                                        $jabatanNames = \App\Models\Jabatan::whereIn('idjabatan', $jabatanIds)->pluck('namajabatan')->toArray();
                                    @endphp
                                    {{ implode(', ', $jabatanNames) }}
                                @else
                                    <span class="text-gray-400">All</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                {{ $notif->createdat->format('d M Y H:i') }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    @if($notif->notification_type === 'manual' && hasPermission('Edit Notifikasi'))
                                    <a href="{{ route('notifications.edit', $notif->notification_id) }}"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    @endif

                                    @if(hasPermission('Hapus Notifikasi'))
                                    <form action="{{ route('notifications.destroy', $notif->notification_id) }}" method="POST"
                                        onsubmit="return confirm('Delete this notification?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all"
                                            title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-lg font-medium">No notifications found</p>
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
            <div class="mt-4 text-sm text-gray-700">
                <p>Showing <span class="font-medium">{{ $result->count() }}</span> of <span class="font-medium">{{ $result->total() }}</span> notifications</p>
            </div>
            @endif
        </div>
    </div>
</x-layout>