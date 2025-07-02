<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="mainData()" class="relative">

        <div class="mx-auto bg-white rounded-md shadow-md p-6">
            {{-- Search & Filters --}}
            <div class="flex flex-col md:flex-row justify-between mb-4">
                <div class="flex justify-between items-center w-full">
                    <form class="flex items-center space-x-2" action="{{ route('input.kerjaharian.rencanakerjaharian.index') }}" method="GET">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Search No RKH..."
                            class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                        <input type="hidden" name="filter_approval" value="{{ $filterApproval }}">
                        <input type="hidden" name="filter_status" value="{{ $filterStatus }}">
                        <input type="hidden" name="filter_date" value="{{ $filterDate }}">
                        <input type="hidden" name="all_date" value="{{ $allDate }}">
                        <button
                            type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded"
                        >
                            Search
                        </button>
                    </form>
                    {{-- CREATE RKH BUTTON WITH MODAL --}}
                    <button
                        @click="showDateModal = true"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs rounded"
                    >
                        Create RKH
                    </button>
                </div>
            </div>

            <form action="{{ route('input.kerjaharian.rencanakerjaharian.index') }}" method="GET" id="filterForm">
                <input type="hidden" name="search" value="{{ $search }}">
                
                <div class="flex items-center justify-between mb-4">
                    <!-- LEFT: 4 filter controls -->
                    <div class="flex items-center space-x-2">
                        <!-- All Approval -->
                        <select name="filter_approval" onchange="document.getElementById('filterForm').submit()"
                                class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Approval</option>
                            <option value="Approved" {{ $filterApproval == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Waiting" {{ $filterApproval == 'Waiting' ? 'selected' : '' }}>Waiting</option>
                            <option value="Decline" {{ $filterApproval == 'Decline' ? 'selected' : '' }}>Decline</option>
                        </select>

                        <!-- All Status -->
                        <select name="filter_status" onchange="document.getElementById('filterForm').submit()"
                                class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="Done" {{ $filterStatus == 'Done' ? 'selected' : '' }}>Done</option>
                            <option value="On Progress" {{ $filterStatus == 'On Progress' ? 'selected' : '' }}>On Progress</option>
                        </select>

                        <!-- Tanggal -->
                        <input type="date" id="filter_date" name="filter_date"
                               class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                               value="{{ $filterDate }}"
                               onchange="document.getElementById('filterForm').submit()"
                               {{ $allDate ? 'disabled' : '' }} />

                        <!-- Show All Date -->
                        <label class="flex items-center text-xs space-x-1">
                            <input type="checkbox" id="all_date_toggle" name="all_date" value="1"
                                   onchange="toggleDateFilter(); document.getElementById('filterForm').submit();"
                                   {{ $allDate ? 'checked' : '' }} />
                            <span>Show All Date</span>
                        </label>
                    </div>

                    <!-- RIGHT: 2 action buttons -->
                    <div class="flex items-center space-x-2">
                        <!-- LKH Approval Button -->
                        <button
                            type="button"
                            @click="showLkhApprovalModal = true; loadPendingLKHApprovals()"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 text-xs rounded flex items-center"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Approve LKH
                        </button>
                        <!-- RKH Approval Button -->
                        <button
                            type="button"
                            @click="showRkhApprovalModal = true; loadPendingApprovals()"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 text-xs rounded flex items-center"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Approve RKH
                        </button>
                        <button
                            type="button"
                            @click="showAbsenModal = true"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded"
                        >
                            Check Data Absen
                        </button>
                        <button
                            type="button"
                            @click="showGenerateDTHModal = true"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded"
                        >
                            Generate DTH
                        </button>
                    </div>
                </div>
            </form>

            {{-- Table View --}}
            <div class="overflow-x-auto">
                <table id="rkh-table" class="min-w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs">
                            <th class="border px-2 py-1">No.</th>
                            <th class="border px-2 py-1">No RKH</th>
                            <th class="border px-2 py-1">Tanggal</th>
                            <th class="border px-2 py-1 text-center">Mandor</th>
                            <th class="border px-2 py-1">Approval</th>
                            <th class="border px-2 py-1 text-center">Laporan Kegiatan Harian</th>
                            <th class="border px-2 py-1 text-center">Status</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rkhData as $index => $rkh)
                        <tr class="text-xs">
                            <td class="border px-2 py-1">{{ $rkhData->firstItem() + $index }}</td>
                            <td class="border px-2 py-1">
                                <a href="{{ route('input.kerjaharian.rencanakerjaharian.show', $rkh->rkhno) }}" 
                                class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                    {{ $rkh->rkhno }}
                                </a>
                            </td>
                            <td class="border px-2 py-1">{{ Carbon\Carbon::parse($rkh->rkhdate)->format('d/m/Y') }}</td>
                            <td class="border px-2 py-1">{{ $rkh->mandor_nama ?? '-' }}</td>

                            <td class="border px-2 py-1 text-center">
                                @if($rkh->approval_status == 'Approved')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded">Approved</span>
                                @elseif($rkh->approval_status == 'No Approval Required')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-blue-800 bg-blue-100 rounded">No Approval Required</span>
                                @elseif(str_contains($rkh->approval_status, 'Declined'))
                                    <span class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded">{{ $rkh->approval_status }}</span>
                                @else
                                    @php
                                        $total = $rkh->jumlahapproval ?? 0;
                                        $completed = 0;
                                        if($rkh->approval1flag == '1') $completed++;
                                        if($rkh->approval2flag == '1') $completed++;
                                        if($rkh->approval3flag == '1') $completed++;
                                        
                                        if($total == 0) {
                                            $waitingText = "Waiting";
                                        } else {
                                            $waitingText = "Waiting for Approve ({$completed} / {$total})";
                                        }
                                    @endphp
                                    <button
                                        @click="showRkhApprovalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadRkhApprovalDetail('{{ $rkh->rkhno }}')"
                                        class="px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded hover:bg-yellow-200 transition-colors cursor-pointer"
                                    >
                                        {{ $waitingText }}
                                    </button>
                                @endif
                            </td>

                            <td class="border px-2 py-1 text-center">
                                <button
                                    @click="showLKHModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadLKHData('{{ $rkh->rkhno }}')"
                                    class="text-white bg-green-600 hover:bg-green-700 px-2 py-0.5 rounded text-xs"
                                    title="Klik untuk melihat LKH"
                                >
                                    LKH
                                </button>
                            </td>

                            <td class="border px-2 py-1 text-center">
                                @if($rkh->current_status == 'Done')
                                    <span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">Done</span>
                                @else
                                    <button
                                        onclick="updateStatus('{{ $rkh->rkhno }}')"
                                        class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-2 py-1 text-xs rounded font-semibold"
                                        title="Klik untuk menandai selesai"
                                    >
                                        On Progress
                                    </button>
                                @endif
                            </td>

                            <td class="border px-2 py-1">
                                <div class="flex items-center justify-center space-x-2">
                                    @if($rkh->approval_status == 'Approved')
                                        {{-- Jika sudah approved, tampilkan icon disabled --}}
                                        <div class="group flex items-center text-gray-400 px-2 py-1 text-sm cursor-not-allowed" title="Tidak dapat diedit karena sudah disetujui">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </div>
                                        <div class="group flex items-center text-gray-400 px-2 py-1 text-sm cursor-not-allowed" title="Tidak dapat dihapus karena sudah disetujui">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </div>
                                    @else
                                        {{-- Jika belum approved, tampilkan button normal --}}
                                        <button
                                            type="button"
                                            onclick="window.location.href='{{ route('input.kerjaharian.rencanakerjaharian.edit', $rkh->rkhno) }}'"
                                            class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm"
                                            title="Edit RKH"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button
                                            type="button"
                                            onclick="deleteRKH('{{ $rkh->rkhno }}')"
                                            class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm"
                                            title="Hapus RKH"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="border px-2 py-4 text-center text-gray-500">
                                Tidak ada data RKH ditemukan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($rkhData->hasPages())
                <div class="mt-4">
                    {{ $rkhData->appends(request()->query())->links() }}
                </div>
                @endif
                
            </div>
            
            <!-- DATE SELECTION MODAL FOR CREATE RKH -->
            <div
                x-show="showDateModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    x-show="showDateModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/3"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-green-50 to-emerald-50">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Pilih Tanggal RKH</h2>
                        </div>
                        <button
                            @click="showDateModal = false"
                            class="text-gray-600 hover:text-gray-800 text-2xl leading-none"
                        >&times;</button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="create_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal RKH:</label>
                            <input
                                type="date"
                                id="create_date"
                                x-model="createDate"
                                :min="today"
                                :max="maxDate"
                                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            />
                            <p class="text-xs text-gray-500 mt-1">Pilih tanggal untuk membuat RKH (maksimal 7 hari ke depan)</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
                        <button
                            @click="showDateModal = false"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded-lg transition-colors"
                        >Cancel</button>
                        <button
                            @click="proceedToCreate()"
                            :disabled="!createDate"
                            class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-6 py-2 text-sm rounded-lg transition-colors"
                        >Lanjutkan</button>
                    </div>
                </div>
            </div>

            <!-- =========================== -->
            <!-- RKH APPROVAL MODALS SECTION -->
            <!-- =========================== -->

            <!-- RKH Approval Detail/Info Modal -->
            <div
                x-show="showRkhApprovalInfoModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    x-show="showRkhApprovalInfoModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 max-h-[90vh] flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">RKH Approval Status Detail</h2>
                        </div>
                        <button
                            @click="showRkhApprovalInfoModal = false; clearRkhApprovalDetail()"
                            class="text-gray-600 hover:text-gray-800 text-2xl leading-none flex-shrink-0"
                        >&times;</button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-hidden flex-grow">
                        <!-- RKH Info -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="font-medium">No RKH:</span> <span class="font-mono" x-text="rkhApprovalDetail.rkhno"></span></div>
                                <div><span class="font-medium">Tanggal:</span> <span x-text="rkhApprovalDetail.rkhdate_formatted"></span></div>
                                <div><span class="font-medium">Mandor:</span> <span x-text="rkhApprovalDetail.mandor_nama"></span></div>
                                <div><span class="font-medium">Activity Group:</span> <span x-text="rkhApprovalDetail.activity_group_name"></span></div>
                            </div>
                        </div>

                        <!-- RKH Approval Progress -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">RKH Approval Progress</h3>
                            
                            <template x-for="(level, index) in rkhApprovalDetail.levels" :key="index">
                                <div class="flex items-center space-x-4 p-4 rounded-lg border" 
                                     :class="{
                                         'bg-green-50 border-green-200': level.status === 'approved',
                                         'bg-red-50 border-red-200': level.status === 'declined', 
                                         'bg-yellow-50 border-yellow-200': level.status === 'waiting',
                                         'bg-gray-50 border-gray-200': level.status === 'not_required'
                                     }">
                                    
                                    <!-- Status Icon -->
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                             :class="{
                                                 'bg-green-100': level.status === 'approved',
                                                 'bg-red-100': level.status === 'declined',
                                                 'bg-yellow-100': level.status === 'waiting',
                                                 'bg-gray-100': level.status === 'not_required'
                                             }">
                                            <!-- Approved -->
                                            <svg x-show="level.status === 'approved'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <!-- Declined -->
                                            <svg x-show="level.status === 'declined'" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <!-- Waiting -->
                                            <svg x-show="level.status === 'waiting'" class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <!-- Not Required -->
                                            <svg x-show="level.status === 'not_required'" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Level Info -->
                                    <div class="flex-grow">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-900" x-text="level.jabatan_name"></h4>
                                            <span class="text-xs px-2 py-1 rounded-full"
                                                  :class="{
                                                      'bg-green-100 text-green-800': level.status === 'approved',
                                                      'bg-red-100 text-red-800': level.status === 'declined',
                                                      'bg-yellow-100 text-yellow-800': level.status === 'waiting',
                                                      'bg-gray-100 text-gray-600': level.status === 'not_required'
                                                  }"
                                                  x-text="level.status_text"></span>
                                        </div>
                                        
                                        <div class="mt-1 text-sm text-gray-600">
                                            <template x-if="level.status === 'approved' || level.status === 'declined'">
                                                <div>
                                                    <span x-text="level.status === 'approved' ? 'Approved' : 'Declined'"></span>
                                                    by <span class="font-medium" x-text="level.user_name"></span>
                                                    on <span x-text="level.date_formatted"></span>
                                                </div>
                                            </template>
                                            <template x-if="level.status === 'waiting'">
                                                <div>Waiting for approval</div>
                                            </template>
                                            <template x-if="level.status === 'not_required'">
                                                <div>Not required for this activity group</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end p-4 border-t bg-gray-50">
                        <button
                            @click="showRkhApprovalInfoModal = false; clearRkhApprovalDetail()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded"
                        >Close</button>
                    </div>
                </div>
            </div>

            <!-- RKH Approval Action Modal -->
            <div
                x-show="showRkhApprovalModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    x-show="showRkhApprovalModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-orange-50 to-amber-50">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">RKH Approval Actions</h2>
                        </div>
                        <button
                            @click="showRkhApprovalModal = false"
                            class="text-gray-600 hover:text-gray-800 text-2xl leading-none flex-shrink-0"
                        >&times;</button>
                    </div>

                    <!-- User Info -->
                    <div class="p-4 bg-blue-50 border-b">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="font-medium text-blue-900">Logged in as:</span>
                                <span class="text-blue-800" x-text="rkhUserInfo.name"></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-blue-900">Position:</span>
                                <span class="text-blue-800" x-text="rkhUserInfo.jabatan_name"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4 overflow-hidden flex-grow">
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">RKH yang menunggu persetujuan Anda:</p>
                        </div>
                        <div class="overflow-x-auto">
                            <div class="max-h-[400px] overflow-y-auto">
                                <table class="min-w-full table-auto text-sm">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-center">
                                                <input 
                                                    type="checkbox" 
                                                    @change="toggleSelectAllRkh($event.target.checked)"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                >
                                            </th>
                                            <th class="px-3 py-2 text-left">No RKH</th>
                                            <th class="px-3 py-2 text-left">Tanggal</th>
                                            <th class="px-3 py-2 text-left">Mandor</th>
                                            <th class="px-3 py-2 text-left">Activity Group</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="rkh in pendingRkhApprovals" :key="rkh.rkhno">
                                            <tr class="hover:bg-gray-50">
                                                <td class="border px-3 py-2 text-center">
                                                    <input 
                                                        type="checkbox" 
                                                        :value="rkh.rkhno"
                                                        x-model="selectedRKHs"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    >
                                                </td>
                                                <td class="border px-3 py-2 font-mono text-xs" x-text="rkh.rkhno"></td>
                                                <td class="border px-3 py-2" x-text="rkh.rkhdate_formatted"></td>
                                                <td class="border px-3 py-2" x-text="rkh.mandor_nama"></td>
                                                <td class="border px-3 py-2" x-text="rkh.activity_group_name"></td>
                                                <td class="border px-3 py-2 text-center">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                        Waiting
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="pendingRkhApprovals.length === 0">
                                            <td colspan="6" class="border px-3 py-8 text-center text-gray-500">
                                                Tidak ada RKH yang menunggu persetujuan Anda
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-between items-center p-4 border-t bg-gray-50 flex-shrink-0">
                        <div class="text-sm text-gray-600">
                            <span x-text="selectedRKHs.length"></span> of <span x-text="pendingRkhApprovals.length"></span> selected
                        </div>
                        <div class="flex space-x-2">
                            <button
                                @click="bulkApproveRkh()"
                                :disabled="selectedRKHs.length === 0"
                                class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 text-sm rounded transition-colors"
                            >
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve Selected
                            </button>
                            <button
                                @click="bulkDeclineRkh()"
                                :disabled="selectedRKHs.length === 0"
                                class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 text-sm rounded transition-colors"
                            >
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Decline Selected
                            </button>
                            <button
                                @click="showRkhApprovalModal = false"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded"
                            >Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =========================== -->
            <!-- LKH APPROVAL MODALS SECTION -->
            <!-- =========================== -->

            <!-- LKH Approval Detail/Info Modal -->
            <div
                x-show="showLkhApprovalInfoModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-60"
            >
                <div
                    x-show="showLkhApprovalInfoModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 max-h-[90vh] flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-purple-50 to-indigo-50">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">LKH Approval Status Detail</h2>
                        </div>
                        <button
                            @click="showLkhApprovalInfoModal = false; clearLkhApprovalDetail()"
                            class="text-gray-600 hover:text-gray-800 text-2xl leading-none flex-shrink-0"
                        >&times;</button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-hidden flex-grow">
                        <!-- LKH Info -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="font-medium">No LKH:</span> <span class="font-mono" x-text="lkhApprovalDetail.lkhno"></span></div>
                                <div><span class="font-medium">RKH:</span> <span class="font-mono" x-text="lkhApprovalDetail.rkhno"></span></div>
                                <div><span class="font-medium">Tanggal:</span> <span x-text="lkhApprovalDetail.lkhdate_formatted"></span></div>
                                <div><span class="font-medium">Mandor:</span> <span x-text="lkhApprovalDetail.mandor_nama"></span></div>
                                <div><span class="font-medium">Activity:</span> <span x-text="lkhApprovalDetail.activityname"></span></div>
                                <div><span class="font-medium">Location:</span> <span x-text="lkhApprovalDetail.location"></span></div>
                            </div>
                        </div>

                        <!-- LKH Approval Progress -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">LKH Approval Progress</h3>
                            
                            <template x-for="(level, index) in lkhApprovalDetail.levels" :key="index">
                                <div class="flex items-center space-x-4 p-4 rounded-lg border" 
                                     :class="{
                                         'bg-green-50 border-green-200': level.status === 'approved',
                                         'bg-red-50 border-red-200': level.status === 'declined', 
                                         'bg-yellow-50 border-yellow-200': level.status === 'waiting',
                                         'bg-gray-50 border-gray-200': level.status === 'not_required'
                                     }">
                                    
                                    <!-- Status Icon -->
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                             :class="{
                                                 'bg-green-100': level.status === 'approved',
                                                 'bg-red-100': level.status === 'declined',
                                                 'bg-yellow-100': level.status === 'waiting',
                                                 'bg-gray-100': level.status === 'not_required'
                                             }">
                                            <!-- Approved -->
                                            <svg x-show="level.status === 'approved'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <!-- Declined -->
                                            <svg x-show="level.status === 'declined'" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            <!-- Waiting -->
                                            <svg x-show="level.status === 'waiting'" class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <!-- Not Required -->
                                            <svg x-show="level.status === 'not_required'" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Level Info -->
                                    <div class="flex-grow">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-900" x-text="level.jabatan_name"></h4>
                                            <span class="text-xs px-2 py-1 rounded-full"
                                                  :class="{
                                                      'bg-green-100 text-green-800': level.status === 'approved',
                                                      'bg-red-100 text-red-800': level.status === 'declined',
                                                      'bg-yellow-100 text-yellow-800': level.status === 'waiting',
                                                      'bg-gray-100 text-gray-600': level.status === 'not_required'
                                                  }"
                                                  x-text="level.status_text"></span>
                                        </div>
                                        
                                        <div class="mt-1 text-sm text-gray-600">
                                            <template x-if="level.status === 'approved' || level.status === 'declined'">
                                                <div>
                                                    <span x-text="level.status === 'approved' ? 'Approved' : 'Declined'"></span>
                                                    by <span class="font-medium" x-text="level.user_name"></span>
                                                    on <span x-text="level.date_formatted"></span>
                                                </div>
                                            </template>
                                            <template x-if="level.status === 'waiting'">
                                                <div>Waiting for approval</div>
                                            </template>
                                            <template x-if="level.status === 'not_required'">
                                                <div>Not required for this activity group</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end p-4 border-t bg-gray-50">
                        <button
                            @click="showLkhApprovalInfoModal = false; clearLkhApprovalDetail()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded"
                        >Close</button>
                    </div>
                </div>
            </div>

            <!-- LKH Approval Action Modal -->
            <div
                x-show="showLkhApprovalModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    x-show="showLkhApprovalModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-purple-50 to-indigo-50">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">LKH Approval Actions</h2>
                        </div>
                        <button
                            @click="showLkhApprovalModal = false"
                            class="text-gray-600 hover:text-gray-800 text-2xl leading-none flex-shrink-0"
                        >&times;</button>
                    </div>

                    <!-- User Info -->
                    <div class="p-4 bg-blue-50 border-b">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="font-medium text-blue-900">Logged in as:</span>
                                <span class="text-blue-800" x-text="lkhUserInfo.name"></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-blue-900">Position:</span>
                                <span class="text-blue-800" x-text="lkhUserInfo.jabatan_name"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4 overflow-hidden flex-grow">
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">LKH yang menunggu persetujuan Anda:</p>
                        </div>
                        <div class="overflow-x-auto">
                            <div class="max-h-[400px] overflow-y-auto">
                                <table class="min-w-full table-auto text-sm">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-center">
                                                <input 
                                                    type="checkbox" 
                                                    @change="toggleSelectAllLKH($event.target.checked)"
                                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                >
                                            </th>
                                            <th class="px-3 py-2 text-left">No LKH</th>
                                            <th class="px-3 py-2 text-left">RKH</th>
                                            <th class="px-3 py-2 text-left">Tanggal</th>
                                            <th class="px-3 py-2 text-left">Mandor</th>
                                            <th class="px-3 py-2 text-left">Aktivitas</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="lkh in pendingLKHApprovals" :key="lkh.lkhno">
                                            <tr class="hover:bg-gray-50">
                                                <td class="border px-3 py-2 text-center">
                                                    <input 
                                                        type="checkbox" 
                                                        :value="lkh.lkhno"
                                                        x-model="selectedLKHs"
                                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    >
                                                </td>
                                                <td class="border px-3 py-2 font-mono text-xs" x-text="lkh.lkhno"></td>
                                                <td class="border px-3 py-2 font-mono text-xs" x-text="lkh.rkhno"></td>
                                                <td class="border px-3 py-2" x-text="lkh.lkhdate_formatted"></td>
                                                <td class="border px-3 py-2" x-text="lkh.mandor_nama"></td>
                                                <td class="border px-3 py-2" x-text="lkh.activityname"></td>
                                                <td class="border px-3 py-2 text-center">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                        Waiting
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="pendingLKHApprovals.length === 0">
                                            <td colspan="7" class="border px-3 py-8 text-center text-gray-500">
                                                Tidak ada LKH yang menunggu persetujuan Anda
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-between items-center p-4 border-t bg-gray-50 flex-shrink-0">
                        <div class="text-sm text-gray-600">
                            <span x-text="selectedLKHs.length"></span> of <span x-text="pendingLKHApprovals.length"></span> selected
                        </div>
                        <div class="flex space-x-2">
                            <button
                                @click="bulkApproveLKH()"
                                :disabled="selectedLKHs.length === 0"
                                class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 text-sm rounded transition-colors"
                            >
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve Selected
                            </button>
                            <button
                                @click="bulkDeclineLKH()"
                                :disabled="selectedLKHs.length === 0"
                                class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 text-sm rounded transition-colors"
                            >
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Decline Selected
                            </button>
                            <button
                                @click="showLkhApprovalModal = false"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded"
                            >Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absen Modal -->
            <div
                x-show="showAbsenModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    x-show="showAbsenModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b flex-shrink-0">
                        <h2 class="text-lg font-semibold">Data Absen Tenaga Kerja</h2>
                        <div class="flex items-center space-x-2">
                            <label for="absen_date" class="text-sm">Tanggal:</label>
                            <input
                                type="date"
                                id="absen_date"
                                x-model="absenDate"
                                @change="loadAbsenData(absenDate)"
                                class="text-sm border border-gray-300 rounded p-2"
                            />
                        </div>
                        <button
                            @click="showAbsenModal = false"
                            class="text-gray-600 hover:text-gray-800 text-2xl leading-none flex-shrink-0"
                        >&times;</button>
                    </div>

                    <!-- Filters -->
                    <div class="flex items-center space-x-2 p-4 border-b">
                        <label for="mandor_filter" class="text-sm">Mandor:</label>
                        <select
                            id="mandor_filter"
                            x-model="selectedMandor"
                            @change="loadAbsenData(absenDate, selectedMandor)"
                            class="text-sm border border-gray-300 rounded p-2"
                        >
                            <option value="">Semua Mandor</option>
                            <template x-for="mandor in mandorList" :key="mandor.id">
                                <option :value="mandor.id" x-text="mandor.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Body -->
                    <div class="p-4 overflow-hidden flex-grow">
                        <div class="overflow-x-auto">
                            <div class="max-h-[400px] overflow-y-auto">
                                <table class="min-w-full table-auto text-sm">
                                    <thead>
                                        <tr class="bg-gray-100">
                                            <th class="px-2 py-1 text-left">ID</th>
                                            <th class="px-2 py-1 text-left">Nama</th>
                                            <th class="px-2 py-1 text-center">Gender</th>
                                            <th class="px-2 py-1 text-left">Mandor</th>
                                            <th class="px-2 py-1 text-center">Jam Absen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="person in absenList" :key="person.id">
                                            <tr>
                                                <td class="border px-2 py-1" x-text="person.mandor_nama"></td>
                                                <td class="border px-2 py-1 text-center" x-text="person.jam_absen"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-between items-center p-4 border-t flex-shrink-0">
                        <div class="text-sm space-x-4">
                            <span>Total Laki-laki: <span x-text="absenList.filter(p => p.gender==='L').length"></span></span>
                            <span>Total Perempuan: <span x-text="absenList.filter(p => p.gender==='P').length"></span></span>
                            <span>Total: <span x-text="absenList.length"></span></span>
                        </div>
                        <button
                            @click="showAbsenModal = false"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm rounded"
                        >Close</button>
                    </div>
                </div>
            </div>

            <!-- Generate DTH Modal -->
            <div
                x-show="showGenerateDTHModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    x-show="showGenerateDTHModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/3"
                >
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b">
                        <h2 class="text-lg font-semibold">Generate DTH</h2>
                        <button
                            @click="showGenerateDTHModal = false"
                            class="text-gray-600 hover:text-gray-800 text-2xl leading-none"
                        >&times;</button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 space-y-4">
                        <label for="dth_date" class="block text-sm font-medium text-gray-700">Pilih Tanggal:</label>
                        <input
                            type="date"
                            id="dth_date"
                            x-model="dthDate"
                            class="w-full border border-gray-300 rounded p-2 text-sm"
                        />
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end space-x-2 p-4 border-t">
                        <button
                            @click="showGenerateDTHModal = false"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded"
                        >Cancel</button>
                        <button
                            @click="generateDTH()"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded"
                        >Generate</button>
                    </div>
                </div>
            </div>

            <!-- LKH Modal -->
            <div x-show="showLKHModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div x-show="showLKHModal"
                    x-transition.scale
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-4/5 lg:w-3/5 max-h-[90vh] flex flex-col">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-green-50 to-emerald-50">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Daftar LKH</h2>
                        </div>
                        <div class="text-sm text-gray-600">
                            RKH: <span class="font-mono font-medium" x-text="selectedRkhno"></span>
                        </div>
                        <button @click="showLKHModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
                    </div>
                    
                    <!-- Body -->
                                                        <div class="p-4 overflow-hidden flex-grow">
                        <div class="overflow-x-auto">
                            <div class="max-h-[400px] overflow-y-auto">
                                <table class="min-w-full table-auto text-sm">
                                    <thead class="bg-gray-100 sticky top-0">
                                        <tr>
                                            <th class="px-3 py-2 text-left">No LKH</th>
                                            <th class="px-3 py-2 text-left">Aktivitas</th>
                                            <th class="px-3 py-2 text-left">Blok</th>
                                            <th class="px-3 py-2 text-center">Jenis</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                            <th class="px-3 py-2 text-center">Approval</th>
                                            <th class="px-3 py-2 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(lkh, index) in lkhData" :key="`lkh-row-${index}-${lkh.lkhno || 'empty'}`">
                                            <tr class="hover:bg-gray-50">
                                                <td class="border px-3 py-2 font-mono text-xs" x-text="lkh.lkhno"></td>
                                                <td class="border px-3 py-2" x-text="lkh.activity"></td>
                                                <td class="border px-3 py-2" x-text="lkh.blok"></td>
                                                <td class="border px-3 py-2 text-center">
                                                    <span class="px-2 py-1 text-xs rounded-full"
                                                        :class="lkh.jenis_tenaga === 'Harian' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                                        x-text="lkh.jenis_tenaga"></span>
                                                </td>
                                                <td class="border px-3 py-2 text-center">
                                                    <span class="px-2 py-1 text-xs rounded-full"
                                                        :class="{
                                                            'bg-green-100 text-green-800': lkh.status === 'COMPLETED',
                                                            'bg-yellow-100 text-yellow-800': lkh.status === 'DRAFT',
                                                            'bg-blue-100 text-blue-800': lkh.status === 'SUBMITTED',
                                                            'bg-purple-100 text-purple-800': lkh.status === 'APPROVED',
                                                            'bg-gray-100 text-gray-800': lkh.status === 'EMPTY'
                                                        }"
                                                        x-text="lkh.status"></span>
                                                </td>
                                                <td class="border px-3 py-2 text-center">
                                                    <!-- LKH Approval Status Badge -->
                                                    <template x-if="lkh.approval_status === 'Approved'">
                                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Approved</span>
                                                    </template>
                                                    <template x-if="lkh.approval_status === 'No Approval Required'">
                                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">No Approval Required</span>
                                                    </template>
                                                    <template x-if="lkh.approval_status === 'Declined'">
                                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Declined</span>
                                                    </template>
                                                    <template x-if="lkh.approval_status === 'Not Yet Submitted'">
                                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Not Yet Submitted</span>
                                                    </template>
                                                    <template x-if="lkh.approval_status && lkh.approval_status.includes('Waiting')">
                                                        <button
                                                            @click="showLKHModal = false; setTimeout(() => { showLkhApprovalInfoModal = true; selectedLkhno = lkh.lkhno; loadLkhApprovalDetail(lkh.lkhno); }, 100)"
                                                            class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors cursor-pointer"
                                                        >
                                                            <span x-text="lkh.approval_status"></span>
                                                        </button>
                                                    </template>
                                                </td>
                                                <td class="border px-3 py-2 text-center">
                                                    <div class="flex justify-center space-x-1">
                                                        <a :href="lkh.view_url" 
                                                        class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs transition-colors"
                                                        target="_blank">
                                                            View
                                                        </a>
                                                        <template x-if="lkh.can_edit">
                                                            <a :href="lkh.edit_url" 
                                                            class="bg-green-600 hover:bg-green-700 text-white px-2 py-1 rounded text-xs transition-colors">
                                                                Edit
                                                            </a>
                                                        </template>
                                                        <template x-if="lkh.can_submit">
                                                            <button
                                                                @click="submitLKH(lkh.lkhno)"
                                                                class="bg-orange-600 hover:bg-orange-700 text-white px-2 py-1 rounded text-xs transition-colors"
                                                            >
                                                                Submit
                                                            </button>
                                                        </template>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="!Array.isArray(lkhData) || lkhData.length === 0">
                                            <td colspan="7" class="border px-3 py-8 text-center text-gray-500">
                                                <template x-if="!Array.isArray(lkhData)">
                                                    <div>Loading data LKH...</div>
                                                </template>
                                                <template x-if="Array.isArray(lkhData) && lkhData.length === 0">
                                                    <div>Belum ada LKH yang dibuat untuk RKH ini</div>
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="flex justify-between items-center p-4 border-t bg-gray-50">
                        <div class="text-sm text-gray-600">
                            Total: <span x-text="Array.isArray(lkhData) ? lkhData.length : 0"></span> LKH
                            <span x-show="!Array.isArray(lkhData)" class="text-yellow-600 ml-2">(Loading...)</span>
                        </div>
                        <button
                            @click="showLKHModal = false; lkhData = []"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded transition-colors"
                        >Close</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function toggleDateFilter() {
            const dateInput = document.getElementById('filter_date');
            const checkbox = document.getElementById('all_date_toggle');
            dateInput.disabled = checkbox.checked;
        }

        function updateStatus(rkhno) {
            if (!confirm('Apakah anda yakin ingin menandai RKH ini sebagai selesai? Pastikan semua LKH sudah terisi.')) {
                return;
            }

            fetch('{{ route("input.kerjaharian.rencanakerjaharian.updateStatus") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    rkhno: rkhno,
                    status: 'Done'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal mengupdate status: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate status');
            });
        }

        function deleteRKH(rkhno) {
            if (!confirm('Apakah anda yakin ingin menghapus RKH ini?')) {
                return;
            }

            fetch('{{ route("input.kerjaharian.rencanakerjaharian.destroy", ":rkhno") }}'.replace(':rkhno', rkhno), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Gagal menghapus RKH: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus RKH');
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date filter ke hari ini jika belum ada
            const filterDate = document.getElementById('filter_date');
            if (!filterDate.value && !document.getElementById('all_date_toggle').checked) {
                filterDate.value = '{{ date("Y-m-d") }}';
            }
        });

        function mainData() {
            return {
                // =========================
                // GENERAL MODAL FLAGS
                // =========================
                showLKHModal: false,
                showAbsenModal: false,
                showGenerateDTHModal: false,
                showDateModal: false,

                // =========================
                // RKH APPROVAL MODAL FLAGS
                // =========================
                showRkhApprovalModal: false,
                showRkhApprovalInfoModal: false,

                // =========================
                // LKH APPROVAL MODAL FLAGS  
                // =========================
                showLkhApprovalModal: false,
                showLkhApprovalInfoModal: false,

                // =========================
                // GENERAL DATA PROPERTIES
                // =========================
                dthDate: '{{ request('filter_date', date('Y-m-d')) }}',
                selectedRkhno: '',
                selectedLkhno: '',
                lkhData: [],
                absenDate: '{{ request('filter_date', date('Y-m-d')) }}',
                absenList: @json($absentenagakerja ?? []),
                selectedMandor: '',
                mandorList: [],
                createDate: new Date().toISOString().split('T')[0],
                today: new Date().toISOString().split('T')[0],
                
                // =========================
                // RKH APPROVAL DATA
                // =========================
                pendingRkhApprovals: [],
                rkhUserInfo: {},
                selectedRKHs: [],
                rkhApprovalDetail: {},
                
                // =========================
                // LKH APPROVAL DATA
                // =========================
                pendingLKHApprovals: [],
                lkhUserInfo: {},
                selectedLKHs: [],
                lkhApprovalDetail: {},
                
                get maxDate() {
                    const date = new Date();
                    date.setDate(date.getDate() + 7);
                    return date.toISOString().split('T')[0];
                },

                // =========================
                // GENERAL METHODS
                // =========================
                proceedToCreate() {
                    if (!this.createDate) {
                        alert('Silakan pilih tanggal terlebih dahulu');
                        return;
                    }
                    
                    window.location.href = `{{ route('input.kerjaharian.rencanakerjaharian.create') }}?date=${this.createDate}`;
                },

                async loadAbsenData(date, mandorId = '') {
                    try {
                        const response = await fetch(`{{ route('input.kerjaharian.rencanakerjaharian.loadAbsenByDate') }}?date=${date}&mandor_id=${mandorId}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.absenList = data.data || [];
                            this.mandorList = data.mandor_list || [];
                        } else {
                            alert('Gagal memuat data absen: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data absen');
                    }
                },

                async generateDTH() {
                    if (!this.dthDate) {
                        alert('Silakan pilih tanggal terlebih dahulu');
                        return;
                    }

                    try {
                        const response = await fetch('{{ route("input.kerjaharian.rencanakerjaharian.generateDTH") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                date: this.dthDate
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showGenerateDTHModal = false;
                            window.open(data.redirect_url, '_blank');
                        } else {
                            alert('Gagal generate DTH: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat generate DTH');
                    }
                },

                // =========================
                // LKH METHODS
                // =========================
                async loadLKHData(rkhno) {
                    // Reset data dan loading state
                    this.lkhData = [];
                    
                    try {
                        console.log('Loading LKH data for RKH:', rkhno);
                        
                        // Construct correct URL
                        const url = `{{ url('input/kerjaharian/rencanakerjaharian') }}/${rkhno}/lkh`;
                        console.log('Fetching URL:', url);
                        
                        const response = await fetch(url);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const data = await response.json();
                        console.log('LKH API Response:', data);
                        
                        if (data.success) {
                            // Pastikan data adalah array dan memiliki struktur yang benar
                            const lkhArray = Array.isArray(data.lkh_data) ? data.lkh_data : [];
                            
                            // Validate each item has required properties
                            this.lkhData = lkhArray.map(lkh => ({
                                lkhno: lkh.lkhno || '',
                                activity: lkh.activity || 'Unknown Activity',
                                blok: lkh.blok || 'Unknown Location',
                                jenis_tenaga: lkh.jenis_tenaga || 'Unknown',
                                status: lkh.status || 'EMPTY',
                                approval_status: lkh.approval_status || 'No Approval Required',
                                workers: lkh.workers || 0,
                                hasil: lkh.hasil || 0,
                                issubmit: lkh.issubmit || false,
                                can_submit: lkh.can_submit || false,
                                can_edit: lkh.can_edit || false,
                                view_url: lkh.view_url || '#',
                                edit_url: lkh.edit_url || '#'
                            }));
                            
                            console.log('Processed LKH Data:', this.lkhData);
                        } else {
                            this.lkhData = [];
                            console.error('LKH API Error:', data.message);
                            alert('Gagal memuat data LKH: ' + data.message);
                        }
                    } catch (error) {
                        console.error('LKH Loading Error:', error);
                        this.lkhData = [];
                        alert('Terjadi kesalahan saat memuat data LKH: ' + error.message);
                    }
                },

                // Submit LKH method (changed from lockLKH to submitLKH)
                async submitLKH(lkhno) {
                    if (!confirm('Apakah Anda yakin ingin mengirim LKH ini? LKH yang dikirim akan masuk ke proses approval.')) {
                        return;
                    }

                    try {
                        const response = await fetch('{{ route("input.kerjaharian.rencanakerjaharian.submitLKH") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                lkhno: lkhno
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            alert(data.message);
                            // Reload LKH data for current RKH
                            await this.loadLKHData(this.selectedRkhno);
                        } else {
                            alert('Gagal mengirim LKH: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengirim LKH');
                    }
                },

                // =========================
                // RKH APPROVAL METHODS
                // =========================
                async loadPendingApprovals() {
                    try {
                        const response = await fetch('{{ route("input.kerjaharian.rencanakerjaharian.getPendingApprovals") }}');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.pendingRkhApprovals = data.data || [];
                            this.rkhUserInfo = data.user_info || {};
                            this.selectedRKHs = [];
                        } else {
                            alert('Gagal memuat data approval: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data approval');
                    }
                },

                async loadRkhApprovalDetail(rkhno) {
                    this.rkhApprovalDetail = {};
                    
                    try {
                        const response = await fetch(`{{ route("input.kerjaharian.rencanakerjaharian.getApprovalDetail", ":rkhno") }}`.replace(':rkhno', rkhno));
                        const data = await response.json();
                        
                        if (data.success) {
                            this.rkhApprovalDetail = data.data;
                        } else {
                            alert('Gagal memuat detail approval: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat detail approval');
                    }
                },

                toggleSelectAllRkh(checked) {
                    if (checked) {
                        this.selectedRKHs = this.pendingRkhApprovals.map(rkh => rkh.rkhno);
                    } else {
                        this.selectedRKHs = [];
                    }
                },

                async bulkApproveRkh() {
                    if (this.selectedRKHs.length === 0) {
                        alert('Silakan pilih RKH yang akan di-approve');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin menyetujui ${this.selectedRKHs.length} RKH yang dipilih?`)) {
                        return;
                    }

                    try {
                        const promises = this.selectedRKHs.map(rkhno => {
                            const rkh = this.pendingRkhApprovals.find(r => r.rkhno === rkhno);
                            return this.processRkhApproval(rkhno, 'approve', rkh.approval_level);
                        });

                        const results = await Promise.all(promises);
                        const successCount = results.filter(r => r.success).length;
                        const failCount = results.length - successCount;

                        let message = `${successCount} RKH berhasil di-approve`;
                        if (failCount > 0) {
                            message += `, ${failCount} RKH gagal di-approve`;
                        }

                        alert(message);
                        await this.loadPendingApprovals();
                        location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk approve');
                    }
                },

                async bulkDeclineRkh() {
                    if (this.selectedRKHs.length === 0) {
                        alert('Silakan pilih RKH yang akan di-decline');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin menolak ${this.selectedRKHs.length} RKH yang dipilih?`)) {
                        return;
                    }

                    try {
                        const promises = this.selectedRKHs.map(rkhno => {
                            const rkh = this.pendingRkhApprovals.find(r => r.rkhno === rkhno);
                            return this.processRkhApproval(rkhno, 'decline', rkh.approval_level);
                        });

                        const results = await Promise.all(promises);
                        const successCount = results.filter(r => r.success).length;
                        const failCount = results.length - successCount;

                        let message = `${successCount} RKH berhasil di-decline`;
                        if (failCount > 0) {
                            message += `, ${failCount} RKH gagal di-decline`;
                        }

                        alert(message);
                        await this.loadPendingApprovals();
                        location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk decline');
                    }
                },

                async processRkhApproval(rkhno, action, level) {
                    try {
                        const response = await fetch('{{ route("input.kerjaharian.rencanakerjaharian.processApproval") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                rkhno: rkhno,
                                action: action,
                                level: level
                            })
                        });

                        return await response.json();
                    } catch (error) {
                        console.error('Error:', error);
                        return { success: false, message: 'Network error' };
                    }
                },

                clearRkhApprovalDetail() {
                    this.rkhApprovalDetail = {};
                    this.selectedRkhno = '';
                },

                // =========================
                // LKH APPROVAL METHODS
                // =========================
                async loadPendingLKHApprovals() {
                    try {
                        const response = await fetch('{{ route("input.kerjaharian.rencanakerjaharian.getPendingLKHApprovals") }}');
                        const data = await response.json();
                        
                        if (data.success) {
                            this.pendingLKHApprovals = data.data || [];
                            this.lkhUserInfo = data.user_info || {};
                            this.selectedLKHs = [];
                        } else {
                            alert('Gagal memuat data approval LKH: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat data approval LKH');
                    }
                },

                async loadLkhApprovalDetail(lkhno) {
                    this.lkhApprovalDetail = {};
                    
                    try {
                        const response = await fetch(`{{ route("input.kerjaharian.rencanakerjaharian.getLkhApprovalDetail", ":lkhno") }}`.replace(':lkhno', lkhno));
                        const data = await response.json();
                        
                        if (data.success) {
                            this.lkhApprovalDetail = data.data;
                        } else {
                            alert('Gagal memuat detail approval LKH: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat memuat detail approval LKH');
                    }
                },

                toggleSelectAllLKH(checked) {
                    if (checked) {
                        this.selectedLKHs = this.pendingLKHApprovals.map(lkh => lkh.lkhno);
                    } else {
                        this.selectedLKHs = [];
                    }
                },

                async bulkApproveLKH() {
                    if (this.selectedLKHs.length === 0) {
                        alert('Silakan pilih LKH yang akan di-approve');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin menyetujui ${this.selectedLKHs.length} LKH yang dipilih?`)) {
                        return;
                    }

                    try {
                        const promises = this.selectedLKHs.map(lkhno => {
                            const lkh = this.pendingLKHApprovals.find(l => l.lkhno === lkhno);
                            return this.processLKHApproval(lkhno, 'approve', lkh.approval_level);
                        });

                        const results = await Promise.all(promises);
                        const successCount = results.filter(r => r.success).length;
                        const failCount = results.length - successCount;

                        let message = `${successCount} LKH berhasil di-approve`;
                        if (failCount > 0) {
                            message += `, ${failCount} LKH gagal di-approve`;
                        }

                        alert(message);
                        await this.loadPendingLKHApprovals();
                        location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk approve LKH');
                    }
                },

                async bulkDeclineLKH() {
                    if (this.selectedLKHs.length === 0) {
                        alert('Silakan pilih LKH yang akan di-decline');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin menolak ${this.selectedLKHs.length} LKH yang dipilih?`)) {
                        return;
                    }

                    try {
                        const promises = this.selectedLKHs.map(lkhno => {
                            const lkh = this.pendingLKHApprovals.find(l => l.lkhno === lkhno);
                            return this.processLKHApproval(lkhno, 'decline', lkh.approval_level);
                        });

                        const results = await Promise.all(promises);
                        const successCount = results.filter(r => r.success).length;
                        const failCount = results.length - successCount;

                        let message = `${successCount} LKH berhasil di-decline`;
                        if (failCount > 0) {
                            message += `, ${failCount} LKH gagal di-decline`;
                        }

                        alert(message);
                        await this.loadPendingLKHApprovals();
                        location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk decline LKH');
                    }
                },

                async processLKHApproval(lkhno, action, level) {
                    try {
                        const response = await fetch('{{ route("input.kerjaharian.rencanakerjaharian.processLKHApproval") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                lkhno: lkhno,
                                action: action,
                                level: level
                            })
                        });

                        return await response.json();
                    } catch (error) {
                        console.error('Error:', error);
                        return { success: false, message: 'Network error' };
                    }
                },

                clearLkhApprovalDetail() {
                    this.lkhApprovalDetail = {};
                    this.selectedLkhno = '';
                }
            };
        }
    </script>
</x-layout>