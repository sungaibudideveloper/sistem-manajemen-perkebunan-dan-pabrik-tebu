{{-- resources/views/input/approval/index.blade.php --}}
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
            <div class="space-y-3">
                @foreach($pendingRKH as $rkh)
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-4 py-3 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('input.rencanakerjaharian.show', $rkh->rkhno) }}" 
                                   class="text-base font-bold text-emerald-700 hover:text-emerald-800">
                                    {{ $rkh->rkhno }}
                                </a>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    {{ \Carbon\Carbon::parse($rkh->rkhdate)->format('d M Y') }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Level {{ $rkh->approval_level }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="px-4 py-3 space-y-2">
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-gray-600">Mandor:</span>
                            <span class="ml-1 font-medium text-gray-900">{{ $rkh->mandor_nama ?? '-' }}</span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-gray-600">Activity:</span>
                            <span class="ml-1 font-medium text-gray-900">{{ $rkh->activity_group_name ?? '-' }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2">
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

                    <!-- Card Actions -->
                    <div class="px-4 py-3 bg-gray-50 border-t flex space-x-2">
                        <form action="{{ route('input.approval.processRKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Approve RKH {{ $rkh->rkhno }}?')"
                                    class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('input.approval.processRKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="rkhno" value="{{ $rkh->rkhno }}">
                            <input type="hidden" name="action" value="decline">
                            <input type="hidden" name="level" value="{{ $rkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Decline RKH {{ $rkh->rkhno }}?')"
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
            <div class="space-y-3">
                @foreach($pendingLKH as $lkh)
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <a href="{{ route('input.rencanakerjaharian.showLKH', $lkh->lkhno) }}" 
                                   class="text-base font-bold text-blue-700 hover:text-blue-800">
                                    {{ $lkh->lkhno }}
                                </a>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    {{ \Carbon\Carbon::parse($lkh->lkhdate)->format('d M Y') }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Level {{ $lkh->approval_level }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="px-4 py-3 space-y-2">
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span class="text-gray-600">Mandor:</span>
                            <span class="ml-1 font-medium text-gray-900">{{ $lkh->mandor_nama ?? '-' }}</span>
                        </div>
                        
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            <span class="text-gray-600">Activity:</span>
                            <span class="ml-1 font-medium text-gray-900">{{ $lkh->activityname ?? '-' }}</span>
                        </div>

                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span class="text-gray-600">RKH:</span>
                            <span class="ml-1 font-medium text-gray-900">{{ $lkh->rkhno }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2">
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

                    <!-- Card Actions -->
                    <div class="px-4 py-3 bg-gray-50 border-t flex space-x-2">
                        <form action="{{ route('input.approval.processLKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Approve LKH {{ $lkh->lkhno }}?')"
                                    class="w-full py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('input.approval.processLKH') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="lkhno" value="{{ $lkh->lkhno }}">
                            <input type="hidden" name="action" value="decline">
                            <input type="hidden" name="level" value="{{ $lkh->approval_level }}">
                            <button type="submit" 
                                    onclick="return confirm('Decline LKH {{ $lkh->lkhno }}?')"
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
                lkhCount: {{ $pendingLKH->count() }}
            }
        }
    </script>
</x-layout>