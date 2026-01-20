{{-- resources/views/transaction/gudang/gudang-bbm.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="gudangBbmData()" class="relative">
    <div class="mx-auto bg-white rounded-md shadow-md p-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gudang BBM - Konfirmasi BBM</h1>
                <p class="text-gray-600 mt-1">Konfirmasi pengeluaran BBM untuk kendaraan</p>
            </div>
            {{-- Search & Filter --}}
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <form class="flex items-center space-x-2" action="{{ route('transaction.gudang-bbm.index') }}" method="GET">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari LKH No, Order No, atau Kendaraan..."
                           class="text-sm border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"/>
                    <input type="date" name="filter_date" value="{{ $filterDate ?? '' }}"
                           class="text-sm border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"/>
                    <label class="inline-flex items-center space-x-1 text-sm text-gray-700">
                        <input type="checkbox" name="show_all" value="1" {{ request('show_all') ? 'checked' : '' }}
                               onchange="this.form.submit()"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span>Show All Date</span>
                    </label>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        {{-- Stats Summary --}}
        <div class="bg-white p-4 rounded-lg shadow-sm border mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['pending_confirm'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Perlu Konfirmasi</div>
                </div>
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['confirmed_today'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Sudah Konfirmasi Hari Ini</div>
                </div>
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_vehicles'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Total Kendaraan</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_solar'] ?? 0, 2) }}L</div>
                    <div class="text-xs text-gray-600">Total Solar</div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kendaraan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solar (L)</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bbmData as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($item->ordernumber)
                                <span class="font-mono text-green-600 font-medium">#{{ $item->ordernumber }}</span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($item->lkh_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $item->activityname ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->activitycode }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $item->nokendaraan }}</div>
                                <div class="text-xs text-gray-500">{{ $item->jenis }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $item->operator_nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="font-medium text-green-600">{{ number_format($item->solar, 2) }} L</span>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if(($item->gudangconfirm ?? 0) == 1)
                                <span class="inline-flex items-center rounded-md bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium">Confirmed</span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-orange-100 text-orange-700 px-2 py-0.5 text-xs font-medium">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('transaction.gudang-bbm.show', $item->ordernumber) }}"
                                   class="text-gray-600 hover:text-gray-800 p-1" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <button @click="confirmBbm('{{ $item->ordernumber }}')"
                                        :disabled="{{ ($item->gudangconfirm ?? 0) == 1 ? 'true' : 'false' }}"
                                        class="px-3 py-1 text-sm rounded text-white {{ ($item->gudangconfirm ?? 0) == 1 ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700' }}">
                                    {{ ($item->gudangconfirm ?? 0) == 1 ? 'Terkonfirmasi' : 'Konfirmasi' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                            <div>Tidak ada order BBM untuk tanggal ini</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if(method_exists($bbmData, 'links'))
            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $bbmData->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function gudangBbmData() {
    return {
        async confirmBbm(ordernumber) {
            if (!ordernumber) return;
            if (!confirm('Apakah Anda yakin ingin mengkonfirmasi pengeluaran BBM untuk Order #' + ordernumber + '?')) {
                return;
            }
            try {
                const btn = event?.currentTarget;
                if (btn) { btn.disabled = true; btn.classList.add('opacity-75'); }
                const response = await fetch(`{{ url('transaction/gudang-bbm') }}/${ordernumber}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
                    },
                    body: JSON.stringify({ ordernumber })
                });
                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Gagal konfirmasi: ' + (data.message || 'Unknown'));
                    if (btn) { btn.disabled = false; btn.classList.remove('opacity-75'); }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat konfirmasi BBM');
            }
        }
    };
}
</script>
</x-layout>