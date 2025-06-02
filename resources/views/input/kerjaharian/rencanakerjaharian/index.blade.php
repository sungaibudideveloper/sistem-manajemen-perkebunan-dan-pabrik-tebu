<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="{
  showLKHModal: false,
  showAbsenModal: false,
  showGenerateDTHModal: false,
  dthDate: '{{ request('filter_date', date('Y-m-d')) }}',
  selectedRkhno: '',
  lkhData: [],
  absenDate: '{{ request('filter_date', date('Y-m-d')) }}',
  absenList: @json($absentenagakerja ?? []),
    selectedMandor: '',
  mandorList: [],
  
  async loadAbsenData(date) {
    try {
        const response = await fetch(`{{ route('input.kerjaharian.rencanakerjaharian.loadAbsenByDate') }}?date=${date}`);
        const data = await response.json();
        
        if (data.success) {
            this.absenList = data.data || [];
        } else {
            alert('Gagal memuat data absen: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data absen');
    }
  }
}" class="relative">


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
                    <a
                        href="{{ route('input.kerjaharian.rencanakerjaharian.create') }}"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs rounded"
                    >
                        Create RKH
                    </a>
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
                                {{ $rkh->rkhno }}
                            </td>
                            <td class="border px-2 py-1">{{ Carbon\Carbon::parse($rkh->rkhdate)->format('d/m/Y') }}</td>
                            <td class="border px-2 py-1">{{ $rkh->mandor_nama ?? '-' }}</td>
                            <td class="border px-2 py-1">
                                @if($rkh->approval_status == 'Approved')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-green-800 bg-green-100 rounded">Approved</span>
                                @elseif($rkh->approval_status == 'Decline')
                                    <span class="px-2 py-0.5 text-xs font-semibold text-red-800 bg-red-100 rounded">Decline</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded">Waiting</span>
                                @endif
                            </td>
                            <td class="border px-2 py-1 text-center">
                                <button
                                    @click="showLKHModal = true; selectedRkhno = '{{ $rkh->rkhno }}'; loadLKHData('{{ $rkh->rkhno }}')"
                                    class="text-white bg-green-600 hover:bg-green-700 px-2 py-0.5 rounded text-xs"
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
                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        onclick="window.location.href='{{ route('input.kerjaharian.rencanakerjaharian.edit', $rkh->rkhno) }}'"
                                        class="group flex items-center text-blue-600 hover:text-blue-800 focus:ring-2 focus:ring-blue-500 rounded-md px-2 py-1 text-sm"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    {{-- Delete Button --}}
                                    <button
                                        type="button"
                                        onclick="deleteRKH('{{ $rkh->rkhno }}')"
                                        class="group flex items-center text-red-600 hover:text-red-800 focus:ring-2 focus:ring-red-500 rounded-md px-2 py-1 text-sm"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
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
                    <div class="flex items-center space-x-2">
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
                                                <td class="border px-2 py-1" x-text="person.id"></td>
                                                <td class="border px-2 py-1" x-text="person.nama"></td>
                                                <td class="border px-2 py-1 text-center" x-text="person.gender"></td>
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
                    class="bg-white rounded-lg shadow-lg w-11/12 md:w-1/2">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 border-b">
                        <h2 class="text-lg font-semibold">List Nomor LKH yang Sudah Diunggah</h2>
                        <button @click="showLKHModal = false" class="text-gray-600 hover:text-gray-800 text-2xl leading-none">&times;</button>
                    </div>
                    <!-- Body -->
                    <div class="p-4">
                        <div class="max-h-60 overflow-y-auto">
                            <table class="min-w-full table-auto text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="px-2 py-1 text-left">No LKH</th>
                                        <th class="px-2 py-1 text-left">Kegiatan</th>
                                        <th class="px-2 py-1 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="lkh in lkhData" :key="lkh.lkhno">
                                        <tr>
                                            <td class="border px-2 py-1" x-text="lkh.lkhno"></td>
                                            <td class="border px-2 py-1" x-text="lkh.activity"></td>
                                            <td class="border px-2 py-1 text-center">
                                                <a :href="lkh.check_url" class="underline text-blue-600">Check</a>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="lkhData.length === 0">
                                        <td colspan="3" class="border px-2 py-4 text-center text-gray-500">
                                            Belum ada LKH yang diunggah
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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

        function loadLKHData(rkhno) {
            // Fetch LKH data untuk RKH tertentu
            fetch(`/input/kerjaharian/rencanakerjaharian/${rkhno}/lkh`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update Alpine.js data
                        Alpine.store('lkhData', data.lkh_data || []);
                    } else {
                        console.error('Failed to load LKH data');
                    }
                })
                .catch(error => {
                    console.error('Error loading LKH data:', error);
                });
        }

        function generateDTH() {
            const dthDate = document.getElementById('dth_date').value;
            
            if (!dthDate) {
                alert('Silakan pilih tanggal terlebih dahulu');
                return;
            }

            // Implementasi generate DTH
            fetch('{{ route("input.kerjaharian.rencanakerjaharian.generateDTH") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    date: dthDate
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('DTH berhasil di-generate');
                    // Close modal
                    Alpine.store('showGenerateDTHModal', false);
                } else {
                    alert('Gagal generate DTH: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat generate DTH');
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


        // Tambahkan fungsi ini ke dalam script section di file blade

    </script>
</x-layout>