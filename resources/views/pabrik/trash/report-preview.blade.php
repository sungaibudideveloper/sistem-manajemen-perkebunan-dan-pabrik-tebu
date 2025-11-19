<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview Report Trash</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">
    <div class="max-w-full mx-auto p-4 bg-white">

        <!-- Report Header -->
        <div class="text-center mb-6">
            @php
            // Ambil company pertama dari actualCompanies untuk judul
            $primaryCompany = !empty($actualCompanies) ? $actualCompanies[0] : 'UNKNOWN';

            // Mapping company code ke nama lengkap
            $companyNames = [
            'TBL1' => 'KEBUN TBL', 'TBL2' => 'KEBUN TBL', 'TBL3' => 'KEBUN TBL',
            'BNIL1' => 'KEBUN BNIL', 'BNIL2' => 'KEBUN BNIL', 'BNIL3' => 'KEBUN BNIL', 'BNIL4' => 'KEBUN BNIL',
            'SILVA1' => 'KEBUN SILVA', 'SILVA2' => 'KEBUN SILVA', 'SILVA3' => 'KEBUN SILVA'
            ];

            $kebunName = 'KEBUN UNKNOWN';
            foreach($companyNames as $code => $name) {
            if (strpos($primaryCompany, substr($code, 0, -1)) === 0) {
            $kebunName = $name;
            break;
            }
            }
            @endphp

            <!-- Judul untuk screen (original) -->
            <h2 class="text-xl font-bold text-gray-800 uppercase no-print">
                LAPORAN {{ strtoupper($reportType) }} DATA TRASH
            </h2>

            <!-- Judul untuk print (dinamis hanya untuk mingguan) -->
            @if($reportType === 'mingguan')
            <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                HASIL ANALISA TRASH {{ $kebunName }}
            </h2>
            @else
            <h2 class="text-xl font-bold text-gray-800 uppercase print-only" style="display: none;">
                LAPORAN {{ strtoupper($reportType) }} DATA TRASH
            </h2>
            @endif
            <h3 class="text-lg font-semibold text-gray-700">SUNGAI BUDI GROUP</h3>
            <p class="text-gray-600 mt-2">
                Periode: {{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }}
            </p>
            <p class="text-gray-600">
                Company: {{ !empty($actualCompanies) ? implode(', ', $actualCompanies) : (is_array($company) ? implode(', ', $company) : ($company === 'all' ? 'SEMUA COMPANY' : $company)) }}
            </p>
            <p class="text-gray-600 text-sm">Preview pada: {{ date('d/m/Y H:i:s') }}</p>
        </div>

        <div class="px-2">
            @php
            $isAllCompanies = ($company === 'all') || (is_array($company) && in_array('all', $company)) || (is_array($company) && count($company) > 1);
            @endphp

            @if($reportType === 'harian' && $isAllCompanies)
            <!-- LAPORAN HARIAN -->
            @forelse($dataGrouped as $tanggal => $items)
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 bg-blue-100 p-2 rounded">
                    TANGGAL: {{ date('d/m/Y', strtotime($tanggal)) }}
                </h3>

                <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                    <table class="min-w-full bg-white text-xs border-collapse">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">No</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Jenis</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Surat Jalan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">No. Polisi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Asal Tebu</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Plot</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Kontraktor</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sub Kontraktor</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Toleransi</th>
                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($items as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ ($item['jenis'] ?? '') === 'manual' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($item['jenis'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['suratjalanno'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['nomorpolisi'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['companycode'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['plot'] ?? '' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['namakontraktor'] ?? '-' }}</td>
                                <td class="px-2 py-2 text-xs text-center border-2 border-gray-300">{{ $item['namasubkontraktor'] ?? '-' }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['pucuk'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['daungulma'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['sogolan'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['siwilan'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tebumati'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tanahetc'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['total'] ?? 0, 3, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['toleransi'] ?? 0, 2, ',', '.') }}</td>
                                <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['nettotrash'] ?? 0, 3, ',', '.') }}</td>
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
            <!-- LAPORAN MINGGUAN -->
            @forelse($dataGrouped as $jenis => $companies)
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-800 mb-4 bg-{{ $jenis === 'manual' ? 'green' : 'blue' }}-100 p-2 rounded">
                    JENIS: {{ strtoupper($jenis) }}
                </h3>

                @foreach($companies as $companyCode => $items)
                <div class="mb-6">
                    <h4 class="text-lg font-bold text-gray-700 mb-2 bg-gray-100 p-3 rounded">
                        Company: {{ $companyCode }}
                    </h4>

                    <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                        <table class="min-w-full bg-white text-xs border-collapse">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanggal</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">No</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Surat Jalan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Kontraktor</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sub Kontraktor</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Toleransi</th>
                                    <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ date('d/m/Y', strtotime($item['createddate'] ?? '')) }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $index + 1 }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['suratjalanno'] ?? '' }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['namakontraktor'] ?? '-' }}</td>
                                    <td class="px-2 py-2 text-xs border-2 border-gray-300">{{ $item['namasubkontraktor'] ?? '-' }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['pucuk'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['daungulma'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['sogolan'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['siwilan'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tebumati'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['tanahetc'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['total'] ?? 0, 3, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($item['toleransi'] ?? 0, 2, ',', '.') }}%</td>
                                    <td class="px-2 py-2 text-xs text-right font-semibold border-2 border-gray-300">{{ number_format($item['nettotrash'] ?? 0, 3, ',', '.') }}</td>
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

        <!-- Signature Section - Khusus untuk Mingguan dan hanya saat Print -->
        @if($reportType === 'mingguan')
        <div class="print-only mt-8 px-4" style="display: none;">
            <div class="flex justify-between items-start mt-12">
                <!-- Tanda Tangan 1: Disetujui Oleh -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-2 mt-5">DISETUJUI OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space untuk tanda tangan -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">Tandy</p>
                        <p class="text-xs mt-1">General Manager</p>
                    </div>
                </div>

                <!-- Tanda Tangan 2: Diketahui Oleh -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-2 mt-5">DIKETAHUI OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space untuk tanda tangan -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">Tyas Rudito M</p>
                        <p class="text-xs mt-1">Kabag Laborat</p>
                    </div>
                </div>

                <!-- Tanda Tangan 3: Disiapkan Oleh -->
                <div class="text-center flex-1">
                    <p class="text-sm font-semibold mb-3 mt-0">Terbanggi Besar, {{ date('d-M-Y') }}</p>
                    <p class="text-sm font-semibold mb-2">DISIAPKAN OLEH</p>
                    <div class="h-20 mb-2"></div> <!-- Space untuk tanda tangan -->
                    <div class="inline-block">
                        <p class="text-sm font-bold border-b-2 border-black px-4 pb-1">{{ Auth::user()->userid ?? 'Admin' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Summary info -->
        <div class="mt-6 text-center text-sm text-gray-600 border-t border-gray-200 pt-4">
            <p><strong>Preview Report</strong> - Klik "Generate Report" untuk mendapatkan file lengkap dengan format cetak</p>
        </div>

    </div>
</body>

</html>