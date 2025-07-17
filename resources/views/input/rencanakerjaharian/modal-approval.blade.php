<!-- Approval Modal -->
            <div
                x-show="showApprovalModal"
                x-transition.opacity
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div
                    x-show="showApprovalModal"
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
                            <h2 class="text-lg font-semibold text-gray-900">Approve RKH</h2>
                        </div>
                        <button
                            @click="showApprovalModal = false"
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
                                <span class="text-blue-800" x-text="userInfo.name"></span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-blue-900">Position:</span>
                                <span class="text-blue-800" x-text="userInfo.jabatan_name"></span>
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
                                            <th class="px-3 py-2 text-left">No RKH</th>
                                            <th class="px-3 py-2 text-left">Tanggal</th>
                                            <th class="px-3 py-2 text-left">Mandor</th>
                                            <th class="px-3 py-2 text-left">Activity Group</th>
                                            <th class="px-3 py-2 text-center">Level</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                            <th class="px-3 py-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="rkh in pendingApprovals" :key="rkh.rkhno">
                                            <tr class="hover:bg-gray-50">
                                                <td class="border px-3 py-2 font-mono text-xs" x-text="rkh.rkhno"></td>
                                                <td class="border px-3 py-2" x-text="rkh.rkhdate_formatted"></td>
                                                <td class="border px-3 py-2" x-text="rkh.mandor_nama"></td>
                                                <td class="border px-3 py-2" x-text="rkh.activity_group_name"></td>
                                                <td class="border px-3 py-2 text-center">
                                                    <span class="px-2 py-1 text-xs rounded-full" 
                                                          :class="getApprovalLevelClass(rkh.approval_level)"
                                                          x-text="'Level ' + rkh.approval_level"></span>
                                                </td>
                                                <td class="border px-3 py-2 text-center">
                                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                        Waiting
                                                    </span>
                                                </td>
                                                <td class="border px-3 py-2 text-center">
                                                    <div class="flex justify-center space-x-2">
                                                        <button
                                                            @click="approveRKH(rkh.rkhno, rkh.approval_level)"
                                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs"
                                                        >
                                                            Approve
                                                        </button>
                                                        <button
                                                            @click="declineRKH(rkh.rkhno, rkh.approval_level)"
                                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs"
                                                        >
                                                            Decline
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="pendingApprovals.length === 0">
                                            <td colspan="7" class="border px-3 py-8 text-center text-gray-500">
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
                            <span x-text="pendingApprovals.length"></span> RKH menunggu persetujuan
                        </div>
                        <button
                            @click="showApprovalModal = false"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 text-sm rounded"
                        >Close</button>
                    </div>
                </div>
            </div>