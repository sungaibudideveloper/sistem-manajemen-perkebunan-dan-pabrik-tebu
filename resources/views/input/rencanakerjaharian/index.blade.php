{{--resources\views\input\rencanakerjaharian\index.blade.php--}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

    <div x-data="mainData()" class="relative">
        <div class="mx-auto bg-white rounded-md shadow-md p-6">
            {{-- Search & Action Buttons --}}
            <div class="flex flex-col md:flex-row justify-between mb-4">
                <div class="flex flex-wrap gap-2 items-center">
                    <form class="flex items-center space-x-2" action="{{ route('input.rencanakerjaharian.index') }}" method="GET">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search No RKH..."
                            class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"/>
                        <input type="hidden" name="filter_approval" value="{{ $filterApproval }}">
                        <input type="hidden" name="filter_status" value="{{ $filterStatus }}">
                        <input type="hidden" name="filter_date" value="{{ $filterDate }}">
                        <input type="hidden" name="all_date" value="{{ $allDate }}">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded">Search</button>
                    </form>

                    <button type="button" @click="showRkhApprovalModal = true; loadPendingApprovals()"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded">Approve RKH</button>
                    <button type="button" @click="showLkhApprovalModal = true; loadPendingLKHApprovals()"
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded">Approve LKH</button>
                </div>

                <div class="mt-2 md:mt-0">
                    <button @click="showDateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs rounded">Create RKH</button>
                </div>
            </div>

            {{-- Filters --}}
            <form action="{{ route('input.rencanakerjaharian.index') }}" method="GET" id="filterForm">
                <input type="hidden" name="search" value="{{ $search }}">
                
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <select name="filter_approval" onchange="document.getElementById('filterForm').submit()"
                                class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Approval</option>
                            <option value="Approved" {{ $filterApproval == 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Waiting" {{ $filterApproval == 'Waiting' ? 'selected' : '' }}>Waiting</option>
                            <option value="Decline" {{ $filterApproval == 'Decline' ? 'selected' : '' }}>Decline</option>
                        </select>

                        <select name="filter_status" onchange="document.getElementById('filterForm').submit()"
                                class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="Completed" {{ $filterStatus == 'Completed' ? 'selected' : '' }}>Completed</option>
                            <option value="In Progress" {{ $filterStatus == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        </select>

                        <input type="date" name="filter_date" value="{{ $filterDate }}" onchange="document.getElementById('filterForm').submit()"
                            class="text-xs border border-gray-300 rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                            {{ $allDate ? 'disabled' : '' }}/>

                        <label class="flex items-center text-xs space-x-1">
                            <input type="checkbox" name="all_date" value="1" onchange="toggleDateFilter(); document.getElementById('filterForm').submit();"
                                {{ $allDate ? 'checked' : '' }}/>
                            <span>Show All Date</span>
                        </label>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="button" @click="showAbsenModal = true; loadAbsenData(absenDate)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs rounded">Check Data Absen</button>
                        <button type="button" @click="showGenerateDTHModal = true"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded">Generate DTH</button>
                        <button type="button" @click="showGenerateRekapLKHModal = true"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 text-xs rounded">Daily Reports</button>
                    </div>
                </div>
            </form>

            {{-- Main Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-xs">
                            <th class="border px-2 py-1">No.</th>
                            <th class="border px-2 py-1">No RKH</th>
                            <th class="border px-2 py-1">Tanggal</th>
                            <th class="border px-2 py-1">Mandor</th>
                            <th class="border px-2 py-1">Approval</th>
                            <th class="border px-2 py-1">LKH</th>
                            <th class="border px-2 py-1">Status</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rkhData as $index => $rkh)
                        <tr class="text-xs">
                            <td class="border px-2 py-1">{{ $rkhData->firstItem() + $index }}</td>
                            <td class="border px-2 py-1">
                                <a href="{{ route('input.rencanakerjaharian.show', $rkh->rkhno) }}" 
                                class="text-blue-600 hover:text-blue-800 hover:underline font-medium">{{ $rkh->rkhno }}</a>
                            </td>
                            <td class="border px-2 py-1">{{ Carbon\Carbon::parse($rkh->rkhdate)->format('d/m/Y') }}</td>
                            <td class="border px-2 py-1">{{ $rkh->mandor_nama ?? '-' }}</td>

                            <td class="border px-2 py-1 text-center">
                                @if($rkh->approval_status == 'Approved')
                                    <button @click="showRkhApprovalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadRkhApprovalDetail('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded hover:bg-green-200 cursor-pointer">
                                        Approved
                                    </button>
                                @elseif($rkh->approval_status == 'No Approval Required')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-blue-800 bg-blue-100 rounded">No Approval Required</span>
                                @elseif(str_contains($rkh->approval_status, 'Declined'))
                                    <button @click="showRkhApprovalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadRkhApprovalDetail('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded hover:bg-red-200 cursor-pointer">
                                        {{ $rkh->approval_status }}
                                    </button>
                                @else
                                    @php
                                        $total = $rkh->jumlahapproval ?? 0;
                                        $completed = 0;
                                        if($rkh->approval1flag == '1') $completed++;
                                        if($rkh->approval2flag == '1') $completed++;
                                        if($rkh->approval3flag == '1') $completed++;
                                        $waitingText = $total == 0 ? "Waiting for Approval" : "Waiting for Approval ({$completed}/{$total})";
                                    @endphp
                                    <button @click="showRkhApprovalInfoModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadRkhApprovalDetail('{{ $rkh->rkhno }}')"
                                            class="px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded hover:bg-yellow-200 cursor-pointer">
                                        {{ $waitingText }}
                                    </button>
                                @endif
                            </td>

                            {{-- UPDATED LKH BUTTON WITH PROGRESS STATUS --}}
                            <td class="border px-2 py-1 text-center">
                                <button @click="showLKHModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadLKHData('{{ $rkh->rkhno }}')"
                                        class="px-2 py-0.5 text-xs font-semibold rounded transition-colors
                                            @if($rkh->lkh_progress_status['color'] === 'green')
                                                bg-green-100 text-green-700 hover:bg-green-200
                                            @elseif($rkh->lkh_progress_status['color'] === 'yellow')
                                                bg-yellow-100 text-yellow-700 hover:bg-yellow-200
                                            @else
                                                bg-gray-100 text-gray-700 hover:bg-gray-200
                                            @endif">
                                    @if($rkh->lkh_progress_status['color'] === 'green')
                                        LKH
                                    @else
                                        {{ $rkh->lkh_progress_status['progress'] }}
                                    @endif
                                </button>
                            </td>

                            {{-- UPDATED STATUS BUTTON WITH LKH COMPLETION CHECK --}}
                            <td class="border px-2 py-1 text-center">
                                @if($rkh->current_status == 'Completed')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded">Completed</span>
                                @else
                                    <button onclick="updateStatus('{{ $rkh->rkhno }}')"
                                            @if(!$rkh->lkh_progress_status['can_complete']) disabled @endif
                                            class="px-2 py-0.5 text-xs rounded font-semibold transition-colors
                                                @if($rkh->lkh_progress_status['can_complete'])
                                                    bg-yellow-100 hover:bg-yellow-200 text-yellow-800 cursor-pointer
                                                @else
                                                    bg-gray-200 text-gray-500 cursor-not-allowed
                                                @endif"
                                            @if(!$rkh->lkh_progress_status['can_complete'])
                                                title="Semua LKH harus selesai terlebih dahulu"
                                            @endif>
                                        @if($rkh->lkh_progress_status['can_complete'])
                                            In Progress
                                        @else
                                            In Progress
                                        @endif
                                    </button>
                                @endif
                            </td>

                            <td class="border px-2 py-1">
                                <div class="flex items-center justify-center space-x-2">
                                    @if($rkh->approval1flag == '1' || $rkh->approval2flag == '1' || $rkh->approval3flag == '1' || $rkh->approval1flag == '0' || $rkh->approval2flag == '0' || $rkh->approval3flag == '0')
                                        <div class="text-gray-400 px-2 py-1 cursor-not-allowed" title="Tidak dapat diedit karena sudah disetujui">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </div>
                                        <div class="text-gray-400 px-2 py-1 cursor-not-allowed" title="Tidak dapat dihapus karena sudah disetujui">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <button onclick="window.location.href='{{ route('input.rencanakerjaharian.edit', $rkh->rkhno) }}'"
                                                class="text-blue-600 hover:text-blue-800 px-2 py-1" title="Edit RKH">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="deleteRKH('{{ $rkh->rkhno }}')"
                                                class="text-red-600 hover:text-red-800 px-2 py-1" title="Hapus RKH">
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
                            <td colspan="8" class="border px-2 py-4 text-center text-gray-500">Tidak ada data RKH ditemukan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @if($rkhData->hasPages())
                <div class="mt-4">{{ $rkhData->appends(request()->query())->links() }}</div>
                @endif
            </div>

            {{-- Include All Modals --}}
            @include('input.rencanakerjaharian.indexmodal.index-modal-create-rkh')
            @include('input.rencanakerjaharian.indexmodal.index-modal-rkh-approval')
            @include('input.rencanakerjaharian.indexmodal.index-modal-lkh-approval')
            @include('input.rencanakerjaharian.indexmodal.index-modal-lkh-list')
            @include('input.rencanakerjaharian.indexmodal.index-modal-absen')
            @include('input.rencanakerjaharian.indexmodal.index-modal-dth')
            @include('input.rencanakerjaharian.indexmodal.index-modal-rekap')
        </div>
    </div>

    <script>
    function toggleDateFilter() {
        const dateInput = document.querySelector('input[name="filter_date"]');
        const checkbox = document.querySelector('input[name="all_date"]');
        dateInput.disabled = checkbox.checked;
    }

    // UPDATED: Enhanced validation for LKH completion check
    function updateStatus(rkhno) {
        // Check if button is disabled (additional client-side validation)
        const button = event.target.closest('button');
        if (button.disabled || button.classList.contains('cursor-not-allowed')) {
            alert('Semua LKH harus diselesaikan dan diapprove terlebih dahulu sebelum RKH dapat ditandai sebagai Completed');
            return;
        }

        if (!confirm('Apakah anda yakin ingin menandai RKH ini sebagai Completed? Pastikan semua LKH sudah approved.')) return;

        fetch('{{ route("input.rencanakerjaharian.updateStatus") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ rkhno: rkhno, status: 'Completed' }) // CHANGED: Done -> Completed
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
        if (!confirm('Apakah anda yakin ingin menghapus RKH ini?')) return;

        fetch('{{ route("input.rencanakerjaharian.destroy", ":rkhno") }}'.replace(':rkhno', rkhno), {
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

    function mainData() {
        return {
            // Modal states
            showLKHModal: false,
            showAbsenModal: false,
            showGenerateDTHModal: false,
            showGenerateRekapLKHModal: false,
            showDateModal: false,
            showRkhApprovalModal: false,
            showRkhApprovalInfoModal: false,
            showLkhApprovalModal: false,
            showLkhApprovalInfoModal: false,

            // Loading states
            isRkhApprovalLoading: false,
            isLkhApprovalLoading: false,
            isRkhInfoLoading: false,
            isLkhInfoLoading: false,
            isLkhModalLoading: false,
            isAbsenLoading: false,

            // Data
            selectedRkhno: '',
            selectedLkhno: '',
            lkhData: [],
            absenDate: '{{ request('filter_date', date('Y-m-d')) }}',
            absenList: @json($absentenagakerja ?? []),
            selectedMandor: '',
            mandorList: [],
            createDate: new Date().toISOString().split('T')[0],
            dthDate: '{{ request('filter_date', date('Y-m-d')) }}',
            rekapLkhDate: '{{ request('filter_date', date('Y-m-d')) }}',
            
            // ✅ NEW: Operator Report Properties
            selectedReportType: '',
            selectedOperatorId: '',
            availableOperators: [],
            isLoadingOperators: false,
            
            // RKH Approval
            pendingRkhApprovals: [],
            rkhUserInfo: {},
            selectedRKHs: [],
            rkhApprovalDetail: {},
            
            // LKH Approval
            pendingLKHApprovals: [],
            lkhUserInfo: {},
            selectedLKHs: [],
            lkhApprovalDetail: {},
            
            get today() {
                return new Date().toISOString().split('T')[0];
            },
            
            get maxDate() {
                const date = new Date();
                date.setDate(date.getDate() + 7);
                return date.toISOString().split('T')[0];
            },

            // ✅ NEW: Computed property for report generation
            get canGenerateReport() {
                if (!this.rekapLkhDate || !this.selectedReportType) return false;
                if (this.selectedReportType === 'operator' && !this.selectedOperatorId) return false;
                return true;
            },

            // ✅ NEW: Initialize watchers
            init() {
                this.$watch('selectedReportType', (newValue) => {
                    if (newValue === 'operator' && this.rekapLkhDate) {
                        this.loadOperatorsForDate();
                    } else if (newValue !== 'operator') {
                        this.selectedOperatorId = '';
                        this.availableOperators = [];
                    }
                });
            },

            // Create RKH
            proceedToCreate() {
                if (!this.createDate) {
                    alert('Silakan pilih tanggal terlebih dahulu');
                    return;
                }
                // Buat Munculin Global Loading State
                Alpine.store('loading').start();
                window.location.href = `{{ route('input.rencanakerjaharian.create') }}?date=${this.createDate}`;
            },

            // Reset report modal
            resetReportModal() {
                this.selectedReportType = '';
                this.selectedOperatorId = '';
                this.availableOperators = [];
                this.isLoadingOperators = false;
            },

            // Load operators for selected date
            async loadOperatorsForDate() {
                if (!this.rekapLkhDate) {
                    this.availableOperators = [];
                    return;
                }

                if (this.selectedReportType === 'operator') {
                    this.isLoadingOperators = true;
                    try {
                        const response = await fetch(`{{ route('input.rencanakerjaharian.getOperatorsForDate') }}?date=${this.rekapLkhDate}`);
                        const data = await response.json();
                        
                        if (data.success) {
                            this.availableOperators = data.operators || [];
                            this.selectedOperatorId = ''; // Reset selection
                        } else {
                            alert('Gagal memuat data operator: ' + data.message);
                            this.availableOperators = [];
                        }
                    } catch (error) {
                        console.error('Error loading operators:', error);
                        alert('Terjadi kesalahan saat memuat data operator');
                        this.availableOperators = [];
                    } finally {
                        this.isLoadingOperators = false;
                    }
                }
            },

            // Generate selected report (unified method)
            async generateSelectedReport() {
                if (!this.canGenerateReport) return;

                try {
                    let url, payload;
                    
                    if (this.selectedReportType === 'rekap') {
                        // Existing rekap LKH logic
                        url = '{{ route("input.rencanakerjaharian.generateRekapLKH") }}';
                        payload = { date: this.rekapLkhDate };
                    } else if (this.selectedReportType === 'operator') {
                        // New operator report logic
                        url = '{{ route("input.rencanakerjaharian.generateOperatorReport") }}';
                        payload = { 
                            date: this.rekapLkhDate,
                            operator_id: this.selectedOperatorId 
                        };
                    } else {
                        alert('Pilih jenis laporan terlebih dahulu');
                        return;
                    }

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.showGenerateRekapLKHModal = false;
                        this.resetReportModal();
                        window.open(data.redirect_url, '_blank');
                    } else {
                        alert('Gagal generate laporan: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error generating report:', error);
                    alert('Terjadi kesalahan saat generate laporan');
                }
            },

            // LKH Modal Methods - UPDATED
            async loadLKHData(rkhno) {
                this.isLkhModalLoading = true;
                this.lkhData = [];
                
                try {
                    const response = await fetch(`{{ url('input/kerjaharian/rencanakerjaharian') }}/${rkhno}/lkh`);
                    const data = await response.json();
                    
                    if (data.success) {
                        this.lkhData = (data.lkh_data || []).map(lkh => ({
                            lkhno: lkh.lkhno || '',
                            activityname: lkh.activityname || 'Unknown Activity',
                            activitycode: lkh.activitycode || '',
                            plots: lkh.plots || 'No plots',
                            jenis_tenaga: lkh.jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                            status: lkh.status || 'EMPTY',
                            approval_status: lkh.approval_status || 'No Approval Required',
                            workers_assigned: lkh.workers_assigned || 0,
                            totalhasil: lkh.totalhasil || 0,
                            totalsisa: lkh.totalsisa || 0,
                            totalupah: lkh.totalupah || 0,
                            material_count: lkh.material_count || 0,
                            issubmit: lkh.issubmit || false,
                            can_submit: lkh.can_submit || false,
                            can_edit: lkh.can_edit || false,
                            view_url: lkh.view_url || '#',
                            edit_url: lkh.edit_url || '#'
                        }));
                    } else {
                        alert('Gagal memuat data LKH: ' + data.message);
                        this.lkhData = [];
                    }
                } catch (error) {
                    console.error('LKH Loading Error:', error);
                    alert('Terjadi kesalahan saat memuat data LKH');
                    this.lkhData = [];
                } finally {
                    this.isLkhModalLoading = false;
                }
            },

            async submitLKH(lkhno) {
                if (!confirm('Apakah Anda yakin ingin mengirim LKH ini untuk approval?')) return;

                try {
                    const response = await fetch('{{ route("input.rencanakerjaharian.submitLKH") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ lkhno: lkhno })
                    });

                    const data = await response.json();
                    if (data.success) {
                        alert(data.message);
                        await this.loadLKHData(this.selectedRkhno);
                    } else {
                        alert('Gagal mengirim LKH: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengirim LKH');
                }
            },

            // Absen Methods
            async loadAbsenData(date, mandorId = '') {
                this.isAbsenLoading = true;
                try {
                    const response = await fetch(`{{ route('input.rencanakerjaharian.loadAbsenByDate') }}?date=${date}&mandor_id=${mandorId}`);
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
                } finally {
                    this.isAbsenLoading = false;
                }
            },

            // Generate Methods
            async generateDTH() {
                if (!this.dthDate) {
                    alert('Silakan pilih tanggal terlebih dahulu');
                    return;
                }

                try {
                    const response = await fetch('{{ route("input.rencanakerjaharian.generateDTH") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ date: this.dthDate })
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

            // Legacy method now uses unified approach
            async generateRekapLKH() {
                this.selectedReportType = 'rekap';
                await this.generateSelectedReport();
            },

            // RKH Approval Methods
            async loadPendingApprovals() {
                this.isRkhApprovalLoading = true;
                try {
                    const response = await fetch('{{ route("input.rencanakerjaharian.getPendingApprovals") }}');
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
                } finally {
                    this.isRkhApprovalLoading = false;
                }
            },

            async loadRkhApprovalDetail(rkhno) {
                this.isRkhInfoLoading = true;
                try {
                    const response = await fetch(`{{ route("input.rencanakerjaharian.getApprovalDetail", ":rkhno") }}`.replace(':rkhno', rkhno));
                    const data = await response.json();
                    
                    if (data.success) {
                        this.rkhApprovalDetail = data.data;
                    } else {
                        alert('Gagal memuat detail approval: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail approval');
                } finally {
                    this.isRkhInfoLoading = false;
                }
            },

            toggleSelectAllRkh(checked) {
                this.selectedRKHs = checked ? this.pendingRkhApprovals.map(rkh => rkh.rkhno) : [];
            },

            async bulkApproveRkh() {
                if (this.selectedRKHs.length === 0) {
                    alert('Silakan pilih RKH yang akan di-approve');
                    return;
                }

                if (!confirm(`Apakah Anda yakin ingin menyetujui ${this.selectedRKHs.length} RKH yang dipilih?`)) return;

                try {
                    const promises = this.selectedRKHs.map(rkhno => {
                        const rkh = this.pendingRkhApprovals.find(r => r.rkhno === rkhno);
                        return this.processRkhApproval(rkhno, 'approve', rkh.approval_level);
                    });

                    const results = await Promise.all(promises);
                    const successCount = results.filter(r => r.success).length;
                    
                    alert(`${successCount} RKH berhasil di-approve`);
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

                if (!confirm(`Apakah Anda yakin ingin menolak ${this.selectedRKHs.length} RKH yang dipilih?`)) return;

                try {
                    const promises = this.selectedRKHs.map(rkhno => {
                        const rkh = this.pendingRkhApprovals.find(r => r.rkhno === rkhno);
                        return this.processRkhApproval(rkhno, 'decline', rkh.approval_level);
                    });

                    const results = await Promise.all(promises);
                    const successCount = results.filter(r => r.success).length;
                    
                    alert(`${successCount} RKH berhasil di-decline`);
                    await this.loadPendingApprovals();
                    location.reload();
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat bulk decline');
                }
            },

            async processRkhApproval(rkhno, action, level) {
                try {
                    const response = await fetch('{{ route("input.rencanakerjaharian.processApproval") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ rkhno, action, level })
                    });
                    return await response.json();
                } catch (error) {
                    console.error('Error:', error);
                    return { success: false, message: 'Network error' };
                }
            },

            // LKH Approval Methods
            async loadPendingLKHApprovals() {
                this.isLkhApprovalLoading = true;
                try {
                    const response = await fetch('{{ route("input.rencanakerjaharian.getPendingLKHApprovals") }}');
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
                } finally {
                    this.isLkhApprovalLoading = false;
                }
            },

            async loadLkhApprovalDetail(lkhno) {
                this.isLkhInfoLoading = true;
                try {
                    const response = await fetch(`{{ route("input.rencanakerjaharian.getLkhApprovalDetail", ":lkhno") }}`.replace(':lkhno', lkhno));
                    const data = await response.json();
                    
                    if (data.success) {
                        this.lkhApprovalDetail = data.data;
                    } else {
                        alert('Gagal memuat detail approval LKH: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat detail approval LKH');
                } finally {
                    this.isLkhInfoLoading = false;
                }
            },

            toggleSelectAllLKH(checked) {
                this.selectedLKHs = checked ? this.pendingLKHApprovals.map(lkh => lkh.lkhno) : [];
            },

            async bulkApproveLKH() {
                if (this.selectedLKHs.length === 0) {
                    alert('Silakan pilih LKH yang akan di-approve');
                    return;
                }

                if (!confirm(`Apakah Anda yakin ingin menyetujui ${this.selectedLKHs.length} LKH yang dipilih?`)) return;

                try {
                    const promises = this.selectedLKHs.map(lkhno => {
                        const lkh = this.pendingLKHApprovals.find(l => l.lkhno === lkhno);
                        return this.processLKHApproval(lkhno, 'approve', lkh.approval_level);
                    });

                    const results = await Promise.all(promises);
                    const successCount = results.filter(r => r.success).length;
                    
                    alert(`${successCount} LKH berhasil di-approve`);
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

                if (!confirm(`Apakah Anda yakin ingin menolak ${this.selectedLKHs.length} LKH yang dipilih?`)) return;

                try {
                    const promises = this.selectedLKHs.map(lkhno => {
                        const lkh = this.pendingLKHApprovals.find(l => l.lkhno === lkhno);
                        return this.processLKHApproval(lkhno, 'decline', lkh.approval_level);
                    });

                    const results = await Promise.all(promises);
                    const successCount = results.filter(r => r.success).length;
                    
                    alert(`${successCount} LKH berhasil di-decline`);
                    await this.loadPendingLKHApprovals();
                    location.reload();
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat bulk decline LKH');
                }
            },

            async processLKHApproval(lkhno, action, level) {
                try {
                    const response = await fetch('{{ route("input.rencanakerjaharian.processLKHApproval") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ lkhno, action, level })
                    });
                    return await response.json();
                } catch (error) {
                    console.error('Error:', error);
                    return { success: false, message: 'Network error' };
                }
            }
        };
    }

    // Initialize date filter on page load
    document.addEventListener('DOMContentLoaded', function() {
        const filterDate = document.querySelector('input[name="filter_date"]');
        if (!filterDate.value && !document.querySelector('input[name="all_date"]').checked) {
            filterDate.value = '{{ date("Y-m-d") }}';
        }
    });
    </script>
</x-layout>