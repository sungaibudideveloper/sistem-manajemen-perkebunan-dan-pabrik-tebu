{{-- resources/views/transaction/approval/index.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div class="min-h-screen bg-gray-50 pb-20" x-data="approvalData()">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-emerald-600 to-green-600 px-4 py-6 shadow-md">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Approval Center</h1>
                        <p class="text-sm text-emerald-100">{{ $userInfo['jabatan_name'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Filter Section -->
        <div class="bg-white border-b shadow-sm">
            <div class="max-w-4xl mx-auto px-4 py-3">
                <form action="{{ route('transaction.approval.index') }}" method="GET" id="filterForm" class="flex items-center gap-3">
                    <div class="flex-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <input type="date" 
                               name="filter_date" 
                               value="{{ $filterDate }}" 
                               @change="document.getElementById('filterForm').submit()"
                               :disabled="allDateChecked"
                               class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-emerald-500 focus:border-emerald-500 disabled:bg-gray-100 disabled:text-gray-500">
                    </div>
                    
                    <label class="flex items-center text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" 
                               name="all_date" 
                               value="1" 
                               x-model="allDateChecked"
                               @change="document.getElementById('filterForm').submit()"
                               {{ $allDate ? 'checked' : '' }}
                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 mr-2">
                        <span class="whitespace-nowrap">All Time</span>
                    </label>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-white border-b sticky top-0 z-10 shadow-sm">
            <div class="max-w-4xl mx-auto px-4">
                <div class="flex space-x-1">
                    <button @click="activeTab = 'rkh'" 
                            :class="activeTab === 'rkh' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors">
                        RKH Approval
                        <span x-show="rkhCount > 0" 
                              class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600"
                              x-text="rkhCount"></span>
                    </button>
                    <button @click="activeTab = 'lkh'" 
                            :class="activeTab === 'lkh' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors">
                        LKH Approval
                        <span x-show="lkhCount > 0" 
                              class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600"
                              x-text="lkhCount"></span>
                    </button>
                    <button @click="activeTab = 'other'" 
                            :class="activeTab === 'other' ? 'border-purple-600 text-purple-600' : 'border-transparent text-gray-500'"
                            class="flex-1 py-3 text-sm font-medium border-b-2 transition-colors">
                        Approval Lainnya
                        <span x-show="otherCount > 0" 
                              class="ml-1 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-600"
                              x-text="otherCount"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="max-w-4xl mx-auto px-4 mt-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-4xl mx-auto px-4 mt-4">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-red-800">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <!-- RKH Approval Tab -->
        <div x-show="activeTab === 'rkh'" class="max-w-4xl mx-auto px-4 mt-4">
            @if($pendingRKH->isEmpty())
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 font-medium">Tidak ada RKH yang perlu diapprove</p>
            </div>
            @else
            <!-- RKH Cards (same as before) -->
            <div class="space-y-3">
                @foreach($pendingRKH as $rkh)
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-4 py-2.5 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('transaction.rencanakerjaharian.show', $rkh->rkhno) }}" 
                                   target="_blank"
                                   class="text-base font-bold text-emerald-700 hover:text-emerald-800">
                                    {{ $rkh->rkhno }}
                                </a>
                                <p class="text-xs text-gray-600">
                                    {{ \Carbon\Carbon::parse($rkh->rkhdate)->format('d M Y') }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Level {{ $rkh->approval_level }}
                            </span>
                        </div>
                    </div>

                    <div class="px-4 py-2.5 space-y-1.5">
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-gray-600 mr-1">Mandor:</span>
                            <span class="font-medium text-gray-900">{{ $rkh->mandor_nama ?? '-' }}</span>
                        </div>
                        
                        <div class="flex items-start text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <div class="flex-1">
                                <span class="text-gray-600">Activities:</span>
                                <span class="font-medium text-gray-900 text-xs block mt-0.5">{{ $rkh->activities_list }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div class="text-sm">
                                <span class="text-gray-600">Luas:</span>
                                <span class="ml-1 font-semibold text-gray-900">{{ number_format($rkh->totalluas, 2) }} ha</span>
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-600">Pekerja:</span>
                                <span class="ml-1 font-semibold text-gray-900">{{ $rkh->manpower }} orang</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-2.5 bg-gray-50 border-t flex space-x-2">
                        <form action="{{ route('transaction.approval.processRKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Approve RKH {{ $rkh->rkhno }}?')"
                                    class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('transaction.approval.processRKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                            <input type="hidden" name="action" value="decline">
                            <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Decline RKH {{ $rkh->rkhno }}?')"
                                    class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- LKH Approval Tab -->
        <div x-show="activeTab === 'lkh'" class="max-w-4xl mx-auto px-4 mt-4">
            @if($pendingLKH->isEmpty())
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 font-medium">Tidak ada LKH yang perlu diapprove</p>
            </div>
            @else
            <!-- LKH Cards (same as before) -->
            <div class="space-y-3">
                @foreach($pendingLKH as $lkh)
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-2.5 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('transaction.rencanakerjaharian.showLKH', $lkh->lkhno) }}" 
                                   target="_blank"
                                   class="text-base font-bold text-blue-700 hover:text-blue-800">
                                    {{ $lkh->lkhno }}
                                </a>
                                <p class="text-xs text-gray-600">
                                    {{ \Carbon\Carbon::parse($lkh->lkhdate)->format('d M Y') }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Level {{ $lkh->approval_level }}
                            </span>
                        </div>
                    </div>

                    <div class="px-4 py-2.5 space-y-1.5">
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-gray-600 mr-1">Mandor:</span>
                            <span class="font-medium text-gray-900">{{ $lkh->mandor_nama ?? '-' }}</span>
                        </div>

                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-gray-600 mr-1">Activity:</span>
                            <span class="font-medium text-gray-900 text-xs">{{ $lkh->activityname ?? '-' }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div class="text-sm">
                                <span class="text-gray-600">Hasil:</span>
                                <span class="ml-1 font-semibold text-gray-900">{{ number_format($lkh->totalhasil, 2) }} ha</span>
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-600">Pekerja:</span>
                                <span class="ml-1 font-semibold text-gray-900">{{ $lkh->totalworkers }} orang</span>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-2.5 bg-gray-50 border-t flex space-x-2">
                        <form action="{{ route('transaction.approval.processLKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Approve LKH {{ $lkh->lkhno }}?')"
                                    class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('transaction.approval.processLKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                            <input type="hidden" name="action" value="decline">
                            <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Decline LKH {{ $lkh->lkhno }}?')"
                                    class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Other Approval Tab (NEW) -->
        <div x-show="activeTab === 'other'" class="max-w-4xl mx-auto px-4 mt-4">
            @if($pendingOther->isEmpty())
            <div class="bg-white rounded-lg shadow-sm border p-8 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 font-medium">Tidak ada approval lainnya yang perlu diproses</p>
            </div>
            @else
            <!-- Other Approval Cards -->
            <div class="space-y-3">
                @foreach($pendingOther as $approval)
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-4 py-2.5 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="text-base font-bold text-purple-700">
                                    {{ $approval->transactionnumber }}
                                </div>
                                <p class="text-xs text-gray-600">
                                    {{ $approval->formatted_date ?? '-' }} • {{ $approval->category }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Level {{ $approval->approval_level }}
                                </span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $approval->transactiontype === 'SPLIT' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $approval->transactiontype }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="px-4 py-2.5 space-y-2">
                        <!-- Source Plots/Batches -->
                        <div class="text-sm">
                            <span class="text-gray-600 font-medium">{{ $approval->transactiontype === 'SPLIT' ? 'Plot Asal:' : 'Plot yang di-Merge:' }}</span>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @if(isset($approval->sourceplots_array))
                                    @foreach($approval->sourceplots_array as $plot)
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs font-medium">
                                            {{ $plot }}
                                            {{-- Gunakan real_batch_areas dari batch aktif --}}
                                            @if(isset($approval->real_batch_areas[$plot]))
                                                <span class="text-gray-500">({{ number_format($approval->real_batch_areas[$plot], 2) }} Ha)</span>
                                            @elseif(isset($approval->areamap_array[$plot]))
                                                <span class="text-gray-500">({{ number_format($approval->areamap_array[$plot], 2) }} Ha)</span>
                                            @endif
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Arrow Icon - Ke Bawah -->
                        <div class="flex justify-left">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </div>

                        <!-- Result Plots/Batches -->
                        <div class="text-sm">
                            <span class="text-gray-600 font-medium">Plot Hasil:</span>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @if(isset($approval->resultplots_array))
                                    @foreach($approval->resultplots_array as $plot)
                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-semibold">
                                            {{ $plot }}
                                            @if(isset($approval->areamap_array[$plot]))
                                                <span class="text-purple-600">({{ number_format($approval->areamap_array[$plot], 2) }} Ha)</span>
                                            @endif
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Dominant Plot & Reason -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t">
                            <div class="text-sm">
                                <span class="text-gray-600">Dominant Plot:</span>
                                <span class="ml-1 font-semibold text-gray-900">{{ $approval->dominantplot }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="text-gray-600">Input By:</span>
                                <span class="ml-1 font-medium text-gray-900">{{ $approval->inputby_name ?? $approval->inputby }}</span>
                            </div>
                        </div>

                        @if($approval->splitmergedreason)
                        <div class="text-sm pt-1">
                            <span class="text-gray-600">Alasan:</span>
                            <p class="mt-0.5 text-xs text-gray-700 italic">{{ $approval->splitmergedreason }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Card Actions -->
                    <div class="px-4 py-2.5 bg-gray-50 border-t flex space-x-2">
                        <form action="{{ route('transaction.approval.processOther') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="approvalno" value="{{ $approval->approvalno }}">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="level" value="{{ $approval->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Approve {{ $approval->category }} {{ $approval->transactionnumber }}?')"
                                    class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('transaction.approval.processOther') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="approvalno" value="{{ $approval->approvalno }}">
                            <input type="hidden" name="action" value="decline">
                            <input type="hidden" name="level" value="{{ $approval->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Decline {{ $approval->category }} {{ $approval->transactionnumber }}?')"
                                    class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Decline
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <script>
        function approvalData() {
            return {
                activeTab: 'rkh',
                rkhCount: {{ $pendingRKH->count() }},
                lkhCount: {{ $pendingLKH->count() }},
                otherCount: {{ $pendingOther->count() }},
                selectedRKHs: [],
                selectedLKHs: [],
                allDateChecked: {{ $allDate ? 'true' : 'false' }},
                isProcessing: false, // Track processing state

                // RKH Selection Methods
                toggleSelectAllRkh(checked) {
                    if (checked) {
                        this.selectedRKHs = @json($pendingRKH->pluck('rkhno')->toArray());
                    } else {
                        this.selectedRKHs = [];
                    }
                },

                toggleRkhSelection(rkhno) {
                    const index = this.selectedRKHs.indexOf(rkhno);
                    if (index > -1) {
                        this.selectedRKHs.splice(index, 1);
                    } else {
                        this.selectedRKHs.push(rkhno);
                    }
                },

                // LKH Selection Methods
                toggleSelectAllLkh(checked) {
                    if (checked) {
                        this.selectedLKHs = @json($pendingLKH->pluck('lkhno')->toArray());
                    } else {
                        this.selectedLKHs = [];
                    }
                },

                toggleLkhSelection(lkhno) {
                    const index = this.selectedLKHs.indexOf(lkhno);
                    if (index > -1) {
                        this.selectedLKHs.splice(index, 1);
                    } else {
                        this.selectedLKHs.push(lkhno);
                    }
                },

                // Bulk Approve/Decline RKH
                async bulkApproveRkh() {
                    if (this.selectedRKHs.length === 0) {
                        alert('Silakan pilih RKH yang akan di-approve');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin approve ${this.selectedRKHs.length} RKH?`)) return;

                    this.isProcessing = true;
                    try {
                        let successCount = 0;
                        for (const rkhno of this.selectedRKHs) {
                            const rkh = @json($pendingRKH);
                            const rkhData = rkh.find(r => r.rkhno === rkhno);
                            if (!rkhData) continue;

                            const formData = new FormData();
                            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                            formData.append('rkhno', rkhno);
                            formData.append('action', 'approve');
                            formData.append('level', rkhData.approval_level);

                            const response = await fetch('{{ route("transaction.approval.processRKH") }}', {
                                method: 'POST',
                                body: formData
                            });

                            if (response.ok) successCount++;
                        }

                        alert(`Berhasil approve ${successCount} RKH`);
                        window.location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk approve');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async bulkDeclineRkh() {
                    if (this.selectedRKHs.length === 0) {
                        alert('Silakan pilih RKH yang akan di-decline');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin decline ${this.selectedRKHs.length} RKH?`)) return;

                    this.isProcessing = true;
                    try {
                        let successCount = 0;
                        for (const rkhno of this.selectedRKHs) {
                            const rkh = @json($pendingRKH);
                            const rkhData = rkh.find(r => r.rkhno === rkhno);
                            if (!rkhData) continue;

                            const formData = new FormData();
                            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                            formData.append('rkhno', rkhno);
                            formData.append('action', 'decline');
                            formData.append('level', rkhData.approval_level);

                            const response = await fetch('{{ route("transaction.approval.processRKH") }}', {
                                method: 'POST',
                                body: formData
                            });

                            if (response.ok) successCount++;
                        }

                        alert(`Berhasil decline ${successCount} RKH`);
                        window.location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk decline');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                // Bulk Approve/Decline LKH
                async bulkApproveLkh() {
                    if (this.selectedLKHs.length === 0) {
                        alert('Silakan pilih LKH yang akan di-approve');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin approve ${this.selectedLKHs.length} LKH?`)) return;

                    this.isProcessing = true;
                    try {
                        let successCount = 0;
                        for (const lkhno of this.selectedLKHs) {
                            const lkh = @json($pendingLKH);
                            const lkhData = lkh.find(l => l.lkhno === lkhno);
                            if (!lkhData) continue;

                            const formData = new FormData();
                            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                            formData.append('lkhno', lkhno);
                            formData.append('action', 'approve');
                            formData.append('level', lkhData.approval_level);

                            const response = await fetch('{{ route("transaction.approval.processLKH") }}', {
                                method: 'POST',
                                body: formData
                            });

                            if (response.ok) successCount++;
                        }

                        alert(`Berhasil approve ${successCount} LKH`);
                        window.location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk approve LKH');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async bulkDeclineLkh() {
                    if (this.selectedLKHs.length === 0) {
                        alert('Silakan pilih LKH yang akan di-decline');
                        return;
                    }

                    if (!confirm(`Apakah Anda yakin ingin decline ${this.selectedLKHs.length} LKH?`)) return;

                    this.isProcessing = true;
                    try {
                        let successCount = 0;
                        for (const lkhno of this.selectedLKHs) {
                            const lkh = @json($pendingLKH);
                            const lkhData = lkh.find(l => l.lkhno === lkhno);
                            if (!lkhData) continue;

                            const formData = new FormData();
                            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                            formData.append('lkhno', lkhno);
                            formData.append('action', 'decline');
                            formData.append('level', lkhData.approval_level);

                            const response = await fetch('{{ route("transaction.approval.processLKH") }}', {
                                method: 'POST',
                                body: formData
                            });

                            if (response.ok) successCount++;
                        }

                        alert(`Berhasil decline ${successCount} LKH`);
                        window.location.reload();
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat bulk decline LKH');
                    } finally {
                        this.isProcessing = false;
                    }
                }
            };
        }
    </script>
</x-layout>