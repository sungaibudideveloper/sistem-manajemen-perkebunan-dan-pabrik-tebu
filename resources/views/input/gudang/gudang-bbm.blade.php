{{-- resources/views/input/gudang/gudang-bbm.blade.php --}}
<x-layout>
    <x-slot:title>{{ $title }}</x-slot:title>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>

<div x-data="gudangBbmData()" class="relative">
    <div class="mx-auto bg-white rounded-md shadow-md p-6">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gudang BBM - Order BBM</h1>
                <p class="text-gray-600 mt-1">Kelola order BBM untuk kendaraan</p>
            </div>
            
            {{-- Search & Filter --}}
            <div class="flex items-center space-x-2 mt-4 md:mt-0">
                <form class="flex items-center space-x-2" action="{{ route('input.gudang.bbm.index') }}" method="GET">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari LKH No atau Kendaraan..."
                           class="text-sm border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"/>
                    <input type="date" name="filter_date" value="{{ $filterDate ?? '' }}" 
                           class="text-sm border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"/>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm rounded">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        {{-- Stats Summary - Minimalist --}}
        <div class="bg-white p-4 rounded-lg shadow-sm border mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-green-600">{{ $stats['ready_print'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Siap Print</div>
                </div>
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-blue-600">{{ $stats['printed'] ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Sudah Print</div>
                </div>
                <div class="border-r border-gray-200 last:border-r-0">
                    <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['total_solar'] ?? 0, 0) }}L</div>
                    <div class="text-xs text-gray-600">Total Solar</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_hm'] ?? 0, 0) }}</div>
                    <div class="text-xs text-gray-600">Total HM</div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LKH No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kendaraan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plot</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HM Start/End</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solar</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($bbmData as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-blue-600">
                            {{ $item->lkhno }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($item->lkh_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div>
                                <div class="font-medium">{{ $item->activityname }}</div>
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
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded">
                                {{ $item->plots }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="text-xs">
                                <div><strong>Start:</strong> {{ number_format($item->hourmeterstart, 1) }}</div>
                                <div><strong>End:</strong> {{ number_format($item->hourmeterend, 1) }}</div>
                                <div class="text-gray-500"><strong>Diff:</strong> {{ number_format($item->hourmeterend - $item->hourmeterstart, 1) }}</div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <span class="font-medium">{{ number_format($item->solar, 2) }} L</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($item->status === 'PRINTED')
                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded">Sudah Print</span>
                            @else
                                <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded">Siap Print</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            @if($item->status === 'PRINTED')
                                <div class="flex items-center space-x-2">
                                    <span class="text-gray-500 text-xs">Printed</span>
                                    <a href="{{ route('input.gudang.bbm.print', $item->lkhno) }}" target="_blank"
                                       class="text-blue-600 hover:text-blue-800 text-xs underline">Lihat</a>
                                </div>
                            @else
                                <a href="{{ route('input.gudang.bbm.print', $item->lkhno) }}" target="_blank"
                                   class="bg-green-600 text-white px-3 py-1 text-sm rounded hover:bg-green-700">Print</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                            <div>Tidak ada order BBM yang siap diprint</div>
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

        {{-- Summary Section - Simplified --}}
        @if($bbmData->count() > 0)
        <div class="mt-6 bg-gray-50 p-4 rounded-lg border">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Ringkasan Order BBM</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-lg font-bold text-blue-600">{{ $bbmData->count() }}</div>
                    <div class="text-xs text-gray-600">Total Order</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-green-600">
                        {{ number_format($bbmData->sum('solar'), 2) }} L
                    </div>
                    <div class="text-xs text-gray-600">Total Solar</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-orange-600">
                        {{ number_format($bbmData->sum(function($item) { return $item->hourmeterend - $item->hourmeterstart; }), 1) }}
                    </div>
                    <div class="text-xs text-gray-600">Total Hour Meter</div>
                </div>
                <div>
                    <div class="text-lg font-bold text-purple-600">{{ $bbmData->unique('nokendaraan')->count() }}</div>
                    <div class="text-xs text-gray-600">Unit Kendaraan</div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function gudangBbmData() {
    return {
        init() {
            console.log('Gudang BBM page initialized');
        }
    };
}

// Add loading state for print buttons
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[href*="print"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            const button = e.target.closest('a');
            const originalText = button.innerHTML;
            button.innerHTML = 'Loading...';
            button.classList.add('opacity-75');
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('opacity-75');
            }, 2000);
        });
    });
});
</script>
</x-layout>