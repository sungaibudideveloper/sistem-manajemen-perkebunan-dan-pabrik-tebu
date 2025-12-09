<!-- resources\views\usermanagement\support-ticket\index.blade.php -->
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
        detailModal: false,
        updateModal: false,
        selectedTicket: null,
        updateForm: {
            status: '',
            priority: '',
            resolution_notes: ''
        },
        showDetail(ticket) {
            this.selectedTicket = ticket;
            this.detailModal = true;
        },
        showUpdate(ticket) {
            this.selectedTicket = ticket;
            this.updateForm = {
                status: ticket.status,
                priority: ticket.priority,
                resolution_notes: ticket.resolution_notes || ''
            };
            this.updateModal = true;
        },
        getStatusBadge(status) {
            const badges = {
                'open': 'bg-blue-100 text-blue-800 border border-blue-200',
                'in_progress': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                'resolved': 'bg-green-100 text-green-800 border border-green-200',
                'closed': 'bg-gray-100 text-gray-800 border border-gray-200'
            };
            return badges[status] || 'bg-gray-100 text-gray-800';
        }
    }" class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Statistics Cards - Minimalist Gray/Black/White -->
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Open Tickets -->
                <div class="bg-white border border-gray-300 rounded-md p-3 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Open</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['open'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- In Progress -->
                <div class="bg-white border border-gray-300 rounded-md p-3 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">In Progress</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['in_progress'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Resolved -->
                <div class="bg-white border border-gray-300 rounded-md p-3 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['resolved'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Tickets -->
                <div class="bg-white border border-gray-300 rounded-md p-3 hover:shadow-sm transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
                        </div>
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Company Filter Info -->
            <div class="mt-3 text-xs text-gray-600 bg-white border border-gray-200 rounded px-3 py-2">
                <span class="font-medium">Company Filter:</span> {{ $companycode }} - {{ $companies->firstWhere('companycode', $companycode)->name ?? 'Unknown' }}
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="px-4 py-4 border-b border-gray-200">
            <div class="flex flex-col space-y-4 lg:flex-row lg:items-center lg:justify-between lg:space-y-0">
                
                <!-- Left: Filters -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    
                    <!-- Status Filter -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="status" class="text-xs font-medium text-gray-700 whitespace-nowrap">Status:</label>
                        <select name="status" id="status" onchange="this.form.submit()"
                            class="text-xs w-32 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="">Semua</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('perPage'))
                            <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>

                    <!-- Category Filter -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="category" class="text-xs font-medium text-gray-700 whitespace-nowrap">Category:</label>
                        <select name="category" id="category" onchange="this.form.submit()"
                            class="text-xs w-40 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-2 py-2">
                            <option value="">Semua</option>
                            <option value="forgot_password" {{ request('category') == 'forgot_password' ? 'selected' : '' }}>Forgot Password</option>
                            <option value="bug_report" {{ request('category') == 'bug_report' ? 'selected' : '' }}>Bug Report</option>
                            <option value="support" {{ request('category') == 'support' ? 'selected' : '' }}>Support</option>
                            <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('perPage'))
                            <input type="hidden" name="perPage" value="{{ request('perPage') }}">
                        @endif
                    </form>
                </div>

                <!-- Right: Search and Per Page -->
                <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:space-y-0 sm:space-x-4">
                    
                    <!-- Search Form -->
                    <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2">
                        <label for="search" class="text-xs font-medium text-gray-700 whitespace-nowrap">Cari:</label>
                        <input type="text" name="search" id="search"
                            value="{{ request('search') }}"
                            placeholder="Ticket, Nama, Username..."
                            class="text-xs w-full sm:w-48 md:w-64 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2"
                            onkeydown="if(event.key==='Enter') this.form.submit()" />
                        @if(request('search'))
                            <a href="{{ route('usermanagement.support-ticket.index') }}" 
                               class="text-gray-500 hover:text-gray-700 px-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        @endif
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
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
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
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
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket #</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Info</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Progress</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved</th>
                            <th class="py-3 px-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="py-3 px-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($result as $ticket)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="py-3 px-3 text-sm font-medium">
                                <button @click="showDetail({{ json_encode([
                                    'ticket_id' => $ticket->ticket_id,
                                    'ticket_number' => $ticket->ticket_number,
                                    'category' => $ticket->category,
                                    'status' => $ticket->status,
                                    'priority' => $ticket->priority,
                                    'fullname' => $ticket->fullname,
                                    'username' => $ticket->username,
                                    'companycode' => $ticket->companycode,
                                    'company_name' => $ticket->company->name ?? '',
                                    'description' => $ticket->description,
                                    'resolution_notes' => $ticket->resolution_notes,
                                    'inprogress_by' => $ticket->inprogress_by,
                                    'inprogress_at' => $ticket->inprogress_at ? $ticket->inprogress_at->format('d M Y H:i') : null,
                                    'resolved_by' => $ticket->resolved_by,
                                    'resolved_at' => $ticket->resolved_at ? $ticket->resolved_at->format('d M Y H:i') : null,
                                    'createdat' => $ticket->createdat->format('d M Y H:i')
                                ]) }})" class="text-blue-600 hover:underline">
                                    {{ $ticket->ticket_number }}
                                </button>
                            </td>
                            <td class="py-3 px-3 text-center text-sm">
                                <span :class="getStatusBadge('{{ $ticket->status }}')" 
                                      class="inline-flex items-center px-2 py-1 rounded text-xs font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $ticket->fullname }}</span>
                                    <span class="text-xs text-gray-500">{{ $ticket->username }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                {{ ucfirst($ticket->priority) }}
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-600">
                                @if($ticket->inprogress_by)
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium">{{ $ticket->inprogress_by }}</span>
                                        <span class="text-xs text-gray-500">{{ $ticket->inprogress_at ? $ticket->inprogress_at->format('d M Y H:i') : '-' }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-600">
                                @if($ticket->resolved_by)
                                    <div class="flex flex-col">
                                        <span class="text-xs font-medium">{{ $ticket->resolved_by }}</span>
                                        <span class="text-xs text-gray-500">{{ $ticket->resolved_at ? $ticket->resolved_at->format('d M Y H:i') : '-' }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span>{{ $ticket->createdat->format('d M Y') }}</span>
                                    <span class="text-xs text-gray-500">{{ $ticket->createdat->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Detail Button -->
                                    <button @click="showDetail({{ json_encode([
                                        'ticket_id' => $ticket->ticket_id,
                                        'ticket_number' => $ticket->ticket_number,
                                        'category' => $ticket->category,
                                        'status' => $ticket->status,
                                        'priority' => $ticket->priority,
                                        'fullname' => $ticket->fullname,
                                        'username' => $ticket->username,
                                        'companycode' => $ticket->companycode,
                                        'company_name' => $ticket->company->name ?? '',
                                        'description' => $ticket->description,
                                        'resolution_notes' => $ticket->resolution_notes,
                                        'inprogress_by' => $ticket->inprogress_by,
                                        'inprogress_at' => $ticket->inprogress_at ? $ticket->inprogress_at->format('d M Y H:i') : null,
                                        'resolved_by' => $ticket->resolved_by,
                                        'resolved_at' => $ticket->resolved_at ? $ticket->resolved_at->format('d M Y H:i') : null,
                                        'createdat' => $ticket->createdat->format('d M Y H:i')
                                    ]) }})"
                                        class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-md p-2 transition-all duration-150"
                                        title="View Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>

                                    <!-- Update Button -->
                                    <button @click="showUpdate({{ json_encode([
                                        'ticket_id' => $ticket->ticket_id,
                                        'ticket_number' => $ticket->ticket_number,
                                        'status' => $ticket->status,
                                        'priority' => $ticket->priority,
                                        'resolution_notes' => $ticket->resolution_notes
                                    ]) }})"
                                        class="text-green-600 hover:text-green-800 hover:bg-green-50 rounded-md p-2 transition-all duration-150"
                                        title="Update Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('usermanagement.support-ticket.destroy', $ticket->ticket_id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus ticket {{ $ticket->ticket_number }}?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-red-600 hover:text-red-800 hover:bg-red-50 rounded-md p-2 transition-all duration-150"
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
                            <td colspan="9" class="py-8 px-4 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="text-lg font-medium">Tidak ada ticket</p>
                                    <p class="text-sm">{{ request('search') ? 'Tidak ada hasil untuk pencarian "'.request('search').'"' : 'Belum ada ticket yang masuk' }}</p>
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

        <!-- Detail Modal -->
        <div x-show="detailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="detailModal = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">Ticket Detail</h3>
                            <p class="text-sm text-gray-600" x-text="selectedTicket ? selectedTicket.ticket_number : ''"></p>
                        </div>
                    </div>
                    <button @click="detailModal = false"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4" x-show="selectedTicket">
                    <!-- User Info -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">User Information</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Full Name</p>
                                <p class="text-sm font-medium text-gray-900" x-text="selectedTicket?.fullname"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Username</p>
                                <p class="text-sm font-medium text-gray-900" x-text="selectedTicket?.username"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Company Code</p>
                                <p class="text-sm font-medium text-gray-900" x-text="selectedTicket?.companycode"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Company Name</p>
                                <p class="text-sm font-medium text-gray-900" x-text="selectedTicket?.company_name"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Info -->
                    <div class="grid grid-cols-4 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Category</p>
                            <p class="text-sm font-medium text-gray-700" x-text="selectedTicket ? selectedTicket.category.replace('_', ' ').toUpperCase() : ''"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Priority</p>
                            <p class="text-sm font-medium text-gray-700" x-text="selectedTicket ? selectedTicket.priority.toUpperCase() : ''"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Status</p>
                            <span x-text="selectedTicket ? selectedTicket.status.replace('_', ' ').toUpperCase() : ''" 
                                  :class="getStatusBadge(selectedTicket?.status)"
                                  class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"></span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Created</p>
                            <p class="text-sm font-medium text-gray-700" x-text="selectedTicket?.createdat"></p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <p class="text-xs text-gray-500 mb-2">Description</p>
                        <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 border border-gray-200" x-text="selectedTicket?.description || '-'"></div>
                    </div>

                    <!-- In Progress Info -->
                    <template x-if="selectedTicket?.inprogress_by">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-yellow-800 mb-2">In Progress Information</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-yellow-700">Handler</p>
                                    <p class="text-sm font-medium text-yellow-900" x-text="selectedTicket.inprogress_by"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-yellow-700">Started At</p>
                                    <p class="text-sm font-medium text-yellow-900" x-text="selectedTicket.inprogress_at"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Resolution Notes -->
                    <template x-if="selectedTicket?.resolution_notes">
                        <div>
                            <p class="text-xs text-gray-500 mb-2">Resolution Notes</p>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-gray-700" x-text="selectedTicket.resolution_notes"></div>
                        </div>
                    </template>

                    <!-- Resolved Info -->
                    <template x-if="selectedTicket?.resolved_by">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-green-800 mb-2">Resolution Information</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-green-700">Resolved By</p>
                                    <p class="text-sm font-medium text-green-900" x-text="selectedTicket.resolved_by"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-green-700">Resolved At</p>
                                    <p class="text-sm font-medium text-green-900" x-text="selectedTicket.resolved_at"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                    <button @click="detailModal = false"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Update Status Modal -->
        <div x-show="updateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" x-cloak
            @keydown.window.escape="updateModal = false">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900">Update Ticket</h3>
                    <button @click="updateModal = false"
                        class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6" x-show="selectedTicket">
                    <form :action="selectedTicket ? '{{ url('usermanagement/support-ticket') }}/' + selectedTicket.ticket_id : ''" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-4">
                                Ticket: <span class="font-medium" x-text="selectedTicket?.ticket_number"></span>
                            </p>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                            <select name="status" x-model="updateForm.status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                            <select name="priority" x-model="updateForm.priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <!-- Resolution Notes -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resolution Notes</label>
                            <textarea name="resolution_notes" x-model="updateForm.resolution_notes" rows="4"
                                placeholder="Enter resolution details or notes..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                        </div>

                        <!-- Modal Actions -->
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0 mt-6">
                            <button type="button" @click="updateModal = false"
                                class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                Update Ticket
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