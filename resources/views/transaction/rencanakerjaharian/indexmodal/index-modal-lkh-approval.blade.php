{{-- resources\views\input\rencanakerjaharian\indexmodal\index-modal-lkh-approval.blade.php --}}

{{-- LKH APPROVAL INFO MODAL --}}
<div x-show="showLkhApprovalInfoModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-60">
    <div x-show="showLkhApprovalInfoModal" x-transition.scale
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-purple-50 to-indigo-50">
            <h2 class="text-lg font-semibold text-gray-900">LKH Approval Status Detail</h2>
            <button @click="showLkhApprovalInfoModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <!-- Loading State -->
        <div x-show="isLkhInfoLoading" class="p-6 text-center">
            <div class="loading-spinner mx-auto"></div>
            <p class="mt-2 text-gray-500 loading-dots">Loading LKH approval details</p>
        </div>

        <!-- Body -->
        <div x-show="!isLkhInfoLoading" class="p-6 overflow-hidden flex-grow">
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
                                <svg x-show="level.status === 'approved'" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <svg x-show="level.status === 'declined'" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <svg x-show="level.status === 'waiting'" class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
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
        <div x-show="!isLkhInfoLoading" class="flex justify-end p-4 border-t bg-gray-50">
            <button @click="showLkhApprovalInfoModal = false"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded">Close</button>
        </div>
    </div>
</div>

{{-- LKH APPROVAL ACTION MODAL --}}
<div x-show="showLkhApprovalModal" x-cloak x-transition.opacity
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div x-show="showLkhApprovalModal" x-transition.scale
         class="bg-white rounded-lg shadow-lg w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b bg-gradient-to-r from-gray-50 to-white-50">
            <h2 class="text-lg font-semibold text-gray-900">LKH Approval Actions</h2>
            <button @click="showLkhApprovalModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
        </div>

        <!-- User Info -->
        <div class="p-4 bg-blue-50 border-b">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <span class="font-medium text-blue-900">Logged in as:</span>
                    <span class="text-blue-800" x-text="lkhUserInfo.name"></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="font-medium text-blue-900">Position:</span>
                    <span class="text-blue-800" x-text="lkhUserInfo.jabatan_name"></span>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="isLkhApprovalLoading" class="p-6 text-center">
            <div class="loading-spinner mx-auto"></div>
            <p class="mt-2 text-gray-500 loading-dots">Loading LKH approval data</p>
        </div>

        <!-- Body -->
        <div x-show="!isLkhApprovalLoading" class="p-4 overflow-hidden flex-grow">
            <div class="mb-4">
                <p class="text-sm text-gray-600">LKH yang menunggu persetujuan Anda:</p>
            </div>
            <div class="overflow-x-auto">
                <div class="max-h-[400px] overflow-y-auto">
                    <table class="min-w-full table-auto text-sm">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-3 py-2 text-center">
                                    <input type="checkbox" @change="toggleSelectAllLKH($event.target.checked)"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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
                                        <input type="checkbox" :value="lkh.lkhno" x-model="selectedLKHs"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="border px-3 py-2 font-mono text-xs" x-text="lkh.lkhno"></td>
                                    <td class="border px-3 py-2 font-mono text-xs" x-text="lkh.rkhno"></td>
                                    <td class="border px-3 py-2" x-text="lkh.lkhdate_formatted"></td>
                                    <td class="border px-3 py-2" x-text="lkh.mandor_nama"></td>
                                    <td class="border px-3 py-2" x-text="lkh.activityname"></td>
                                    <td class="border px-3 py-2 text-center">
                                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Waiting</span>
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
        <div x-show="!isLkhApprovalLoading" class="flex justify-between items-center p-4 border-t bg-gray-50">
            <div class="text-sm text-gray-600">
                <span x-text="selectedLKHs.length"></span> of <span x-text="pendingLKHApprovals.length"></span> selected
            </div>
            <div class="flex space-x-2">
                <button @click="bulkApproveLKH()" :disabled="selectedLKHs.length === 0"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 text-sm rounded">
                    Approve Selected
                </button>
                <button @click="bulkDeclineLKH()" :disabled="selectedLKHs.length === 0"
                        class="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 text-sm rounded">
                    Decline Selected
                </button>
                <button @click="showLkhApprovalModal = false"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded">Close</button>
            </div>
        </div>
    </div>
</div>