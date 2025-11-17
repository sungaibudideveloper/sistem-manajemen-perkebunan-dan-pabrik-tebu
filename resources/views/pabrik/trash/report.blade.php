<x-layout>
    <x-slot:title>Laporan Trash {{ ucfirst($reportType) }}</x-slot:title>
    <x-slot:navbar>Pabrik</x-slot:navbar>
    <x-slot:nav>Laporan Trash</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Print Controls -->
        <div class="no-print px-4 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Trash</h1>
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a1 1 0 001-1v-4a1 1 0 00-1-1H9a1 1 0 001 1v4a1 1 0 001 1zm3-5h2a2 2 0 002-2v-3a2 2 0 00-2-2H5a2 2 0 00-2 2v3a2 2 0 002 2h2"></path>
                    </svg>
                    Print
                </button>
                <button onclick="window.close()"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Tutup
                </button>
            </div>
        </div>

        <!-- Report Header -->
        <div class="text-center mb-6 px-4">
            <h2 class="text-xl font-bold text-gray-800 uppercase">
                LAPORAN {{ strtoupper($reportType) }} DATA TRASH
            </h2>
            <h3 class="text-lg font-semibold text-gray-700">
                SUNGAI BUDI GROUP
            </h3>
            <p class="text-gray-600 mt-2">
                Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}
            </p>
            <p class="text-gray-600">
                Company: {{ !empty($actualCompanies) ? implode(', ', $actualCompanies) : (is_array($company) ? implode(', ', $company) : ($company === 'all' ? 'SEMUA COMPANY' : $company)) }}
            </p>
            <p class="text-gray-600 text-sm">
                Dicetak pada: {{ date('d/m/Y H:i:s') }}
            </p>
        </div>

        <div class="px-4">
            @php
            // Fix: Handle both array and string company values for view condition
            $isAllCompanies = ($company === 'all') || (is_array($company) && in_array('all', $company)) || (is_array($company) && count($company) > 1);
            @endphp

            @if($reportType === 'harian' && $isAllCompanies)
            <!-- LAPORAN HARIAN - Dipisah per tanggal -->
            @forelse($dataGrouped as $tanggal => $items)
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 bg-blue-100 p-2 rounded">
                    TANGGAL: {{ date('d/m/Y', strtotime($tanggal)) }}
                </h3>

                <div class="overflow-x-auto rounded-md border border-gray-300">
                    <table class="min-w-full bg-white text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jenis</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Surat Jalan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">No. Polisi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Asal Tebu</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Plot</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kontraktor</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sub Kontraktor</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pucuk</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Daun Gulma</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sogolan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Siwilan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tebu Mati</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanah dll</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Toleransi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Netto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-2 text-xs text-center">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 text-xs text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ ($item['jenis'] ?? '') === 'manual' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($item['jenis'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-xs text-center">{{ $item['suratjalanno'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center">{{ $item['nomorpolisi'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center">{{ $item['companycode'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center">{{ $item['plot'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center">{{ $item['namakontraktor'] ?? '-' }}</td>
                                <td class="px-2 py-2 text-xs text-center">{{ $item['namasubkontraktor'] ?? '-' }}</td>
                                <td class="px-2 py-2 text-xs text-right">{{ number_format($item['pucuk'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right">{{ number_format($item['daungulma'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right">{{ number_format($item['sogolan'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right">{{ number_format($item['siwilan'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right">{{ number_format($item['tebumati'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right">{{ number_format($item['tanahetc'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right font-semibold">{{ number_format($item['total'] ?? 0, 3, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right">{{ number_format($item['toleransi'] ?? 0, 2, ',', '.') }}%</td>
                                <td class="px-2 py-2 text-xs text-right font-semibold">{{ number_format($item['nettotrash'] ?? 0, 3, ',', '.') }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-gray-500">Tidak ada data untuk ditampilkan</p>
            </div>
            @endforelse

            @elseif($reportType === 'mingguan')
            <!-- LAPORAN MINGGUAN - Dipisah per jenis dan company -->
            @forelse($dataGrouped as $jenis => $companies)
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 bg-{{ $jenis === 'manual' ? 'green' : 'blue' }}-100 p-2 rounded">
                    JENIS: {{ strtoupper($jenis) }}
                </h3>

                @foreach($companies as $companyCode => $items)
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-700 mb-2 bg-gray-100 p-2 rounded">
                        Company: {{ $companyCode }}
                    </h4>

                    <div class="overflow-x-auto rounded-md border border-gray-300">
                        <table class="min-w-full bg-white text-xs">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Surat Jalan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kontraktor</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sub Kontraktor</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pucuk</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Daun Gulma</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Sogolan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Siwilan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tebu Mati</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanah dll</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Toleransi</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Netto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 text-xs">{{ date('d/m/Y', strtotime($item['createddate'] ?? '')) }}</td>
                                    <td class="px-2 py-2 text-xs">{{ $index + 1 }}</td>
                                    <td class="px-2 py-2 text-xs">{{ $item['suratjalanno'] ?? '' }}</td>
                                    <td class="px-2 py-2 text-xs">{{ $item['namakontraktor'] ?? '-' }}</td>
                                    <td class="px-2 py-2 text-xs">{{ $item['namasubkontraktor'] ?? '-' }}</td>
                                    <td class="px-2 py-2 text-xs text-right">{{ number_format($item['pucuk'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right">{{ number_format($item['daungulma'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right">{{ number_format($item['sogolan'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right">{{ number_format($item['siwilan'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right">{{ number_format($item['tebumati'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right">{{ number_format($item['tanahetc'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right font-semibold">{{ number_format($item['total'] ?? 0, 3, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right">{{ number_format($item['toleransi'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right font-semibold">{{ number_format($item['nettotrash'] ?? 0, 3, ',', '.') }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-gray-500">Tidak ada data untuk ditampilkan</p>
            </div>
            @endforelse
            @endif
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-600 px-4">
            <p>--- End of Report ---</p>
            <p class="mt-2">Report ini digenerate secara otomatis oleh sistem Sungai Budi Group</p>
        </div>

    </div>

    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 10px;
            }

            .no-print {
                display: none !important;
            }

            table {
                font-size: 9px;
            }

            th,
            td {
                padding: 3px !important;
            }
        }
    </style>

</x-layout>