<x-layout>
    <x-slot:title>Laporan Trash Bulanan</x-slot:title>
    <x-slot:navbar>Pabrik</x-slot:navbar>
    <x-slot:nav>Laporan Trash Bulanan</x-slot:nav>

    <div class="mx-auto py-4 bg-white rounded-md shadow-md">

        <!-- Print Controls -->
        <div class="no-print px-4 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Laporan Trash Bulanan</h1>
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
            @php
            // Monthly names in Indonesian
            $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            @endphp

            <h2 class="text-xl font-bold text-gray-800 uppercase">
                RATA-RATA TRASH KEBUN {{ strtoupper($monthNames[$month] ?? $month) }} {{ $year }}
            </h2>
            <h3 class="text-lg font-semibold text-gray-700">
                SUNGAI BUDI GROUP
            </h3>
            <p class="text-gray-600 mt-2">
                Periode: {{ $monthNames[$month] ?? $month }} {{ $year }}
            </p>
            <p class="text-gray-600">
                Company: {{ !empty($actualCompanies) ? implode(', ', $actualCompanies) : (is_array($company) ? implode(', ', $company) : ($company === 'all' ? 'SEMUA COMPANY' : $company)) }}
            </p>
            <p class="text-gray-600 text-sm">
                Dicetak pada: {{ date('d/m/Y H:i:s') }}
            </p>
        </div>

        <div class="px-4 overflow-x-auto    ">
            <div class="flex gap-6 mb-8">
                <!-- Tabel Utama -->
                <div class="flex-1">
                    <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                        <table class="min-w-full bg-white text-xs border-collapse">
                            <thead class="bg-gray-50">
                                <!-- Header Level 1 -->
                                <tr>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Asal Tebu</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tonase</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk (%)</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun Gulma (%)</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan (%)</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan (%)</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati (%)</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll (%)</th>
                                    <th rowspan="2" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Total Trash</th>
                                    <th colspan="2" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Trash Persentase</th>
                                    <th colspan="2" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">KG Trash</th>
                                </tr>
                                <!-- Header Level 2 -->
                                <tr>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Bruto</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto (Pot 5%)</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Bruto</th>
                                    <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Netto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                @php
                                $grandTotalPucuk = 0;
                                $grandTotalDaun = 0;
                                $grandTotalSogolan = 0;
                                $grandTotalSiwilan = 0;
                                $grandTotalTebumati = 0;
                                $grandTotalTanah = 0;
                                $grandTotalCompanies = 0;
                                $grandSumTonase = 0;

                                // Group companies by prefix (TBL, BNIL, SILVA)
                                $companyGroups = [];
                                foreach($dataGrouped as $companyCode => $items) {
                                $prefix = '';
                                if (strpos($companyCode, 'TBL') === 0) {
                                $prefix = 'TBL';
                                } elseif (strpos($companyCode, 'BNIL') === 0) {
                                $prefix = 'BNIL';
                                } elseif (strpos($companyCode, 'SILVA') === 0) {
                                $prefix = 'SILVA';
                                } else {
                                $prefix = $companyCode;
                                }

                                if (!isset($companyGroups[$prefix])) {
                                $companyGroups[$prefix] = [];
                                }
                                $companyGroups[$prefix][$companyCode] = $items;
                                }

                                $previousGroupName = null;
                                @endphp

                                @foreach($companyGroups as $groupName => $companies)
                                @php
                                // spasi antar group
                                if ($previousGroupName !== null && $previousGroupName !== $groupName) {
                                echo '<tr class="bg-white">
                                    <td colspan="13" class="px-3 py-2 border-0">&nbsp;</td>
                                </tr>';
                                }
                                $previousGroupName = $groupName;

                                $groupTotalPucuk = 0;
                                $groupTotalDaun = 0;
                                $groupTotalSogolan = 0;
                                $groupTotalSiwilan = 0;
                                $groupTotalTebumati = 0;
                                $groupTotalTanah = 0;
                                $groupTotalCompanies = 0;
                                $groupSumTonase = 0;
                                @endphp

                                @foreach($companies as $companyCode => $items)
                                @php
                                // Group by jenis within this company
                                $companyByJenis = [];
                                foreach($items as $item) {
                                $jenis = $item['jenis'];
                                if (!isset($companyByJenis[$jenis])) {
                                $companyByJenis[$jenis] = [];
                                }
                                $companyByJenis[$jenis][] = $item;
                                }

                                // Sort jenis: manual first, then mesin
                                $sortedJenis = [];
                                if (isset($companyByJenis['manual'])) {
                                $sortedJenis['manual'] = $companyByJenis['manual'];
                                }
                                if (isset($companyByJenis['mesin'])) {
                                $sortedJenis['mesin'] = $companyByJenis['mesin'];
                                }
                                @endphp

                                @foreach($sortedJenis as $jenis => $jenisItems)
                                @php
                                // Calculate averages for trash percentages and SUM for tonase
                                $totalPucuk = 0;
                                $totalDaun = 0;
                                $totalSogolan = 0;
                                $totalSiwilan = 0;
                                $totalTebumati = 0;
                                $totalTanah = 0;
                                $sumTonase = 0;
                                $count = count($jenisItems);

                                foreach($jenisItems as $item) {
                                $totalPucuk += $item['pucuk'];
                                $totalDaun += $item['daungulma'];
                                $totalSogolan += $item['sogolan'];
                                $totalSiwilan += $item['siwilan'];
                                $totalTebumati += $item['tebumati'];
                                $totalTanah += $item['tanahetc'];
                                $sumTonase += $item['tonase_netto'];
                                }

                                $avgPucuk = $count > 0 ? $totalPucuk / $count : 0;
                                $avgDaun = $count > 0 ? $totalDaun / $count : 0;
                                $avgSogolan = $count > 0 ? $totalSogolan / $count : 0;
                                $avgSiwilan = $count > 0 ? $totalSiwilan / $count : 0;
                                $avgTebumati = $count > 0 ? $totalTebumati / $count : 0;
                                $avgTanah = $count > 0 ? $totalTanah / $count : 0;

                                // Add to group totals
                                $groupTotalPucuk += $avgPucuk;
                                $groupTotalDaun += $avgDaun;
                                $groupTotalSogolan += $avgSogolan;
                                $groupTotalSiwilan += $avgSiwilan;
                                $groupTotalTebumati += $avgTebumati;
                                $groupTotalTanah += $avgTanah;
                                $groupTotalCompanies++;
                                $groupSumTonase += $sumTonase;

                                $totaltrash = $avgPucuk + $avgDaun + $avgSogolan + $avgSiwilan + $avgTebumati + $avgTanah;

                                $nettoTrash = $totaltrash - 5;
                                $nettoTrash = $nettoTrash < 0 ? 0 : $nettoTrash;

                                    $kgTrashBruto=($totaltrash / 100) * $sumTonase;
                                    $kgTrashNetto=($nettoTrash / 100) * $sumTonase;
                                    @endphp

                                    <tr class="hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                        {{ $companyCode }} ({{ ucfirst($jenis) }})
                                    </td>
                                    <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                        {{ number_format($sumTonase, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgPucuk, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgDaun, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgSogolan, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgSiwilan, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgTebumati, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($avgTanah, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">{{ number_format($totaltrash, 2, ',', '.') }}</td>

                                    <!-- Trash Persentase - Bruto -->
                                    <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                        {{ number_format($totaltrash, 2, ',', '.') }}
                                    </td>
                                    <!-- Trash Persentase - Netto (Pot 5%) -->
                                    <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                        {{ number_format($nettoTrash, 2, ',', '.') }}
                                    </td>
                                    <!-- KG Trash - Bruto -->
                                    <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                        {{ number_format($kgTrashBruto, 0, ',', '.') }}
                                    </td>
                                    <!-- KG Trash - Netto -->
                                    <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                        {{ number_format($kgTrashNetto, 0, ',', '.') }}
                                    </td>
                                    </tr>
                                    @endforeach
                                    @endforeach

                                    @php
                                    // Calculate group averages
                                    $groupAvgPucuk = $groupTotalCompanies > 0 ? $groupTotalPucuk / $groupTotalCompanies : 0;
                                    $groupAvgDaun = $groupTotalCompanies > 0 ? $groupTotalDaun / $groupTotalCompanies : 0;
                                    $groupAvgSogolan = $groupTotalCompanies > 0 ? $groupTotalSogolan / $groupTotalCompanies : 0;
                                    $groupAvgSiwilan = $groupTotalCompanies > 0 ? $groupTotalSiwilan / $groupTotalCompanies : 0;
                                    $groupAvgTebumati = $groupTotalCompanies > 0 ? $groupTotalTebumati / $groupTotalCompanies : 0;
                                    $groupAvgTanah = $groupTotalCompanies > 0 ? $groupTotalTanah / $groupTotalCompanies : 0;

                                    // Calculate group total trash
                                    $groupTotalTrash = $groupAvgPucuk + $groupAvgDaun + $groupAvgSogolan + $groupAvgSiwilan + $groupAvgTebumati + $groupAvgTanah;

                                    // Calculate group KG Trash
                                    $groupKgTrashBruto = ($groupSumTonase * $groupTotalTrash) / 100;
                                    $groupNettoTrash = $groupTotalTrash - 5;
                                    $groupNettoTrash = $groupNettoTrash < 0 ? 0 : $groupNettoTrash;
                                        $groupKgTrashNetto=($groupSumTonase * $groupNettoTrash) / 100;

                                        // Calculate group trash percentage (harusnya sama dengan totalTrash, tapi dibiarkan kalau mau tampil juga)
                                        $trashPersentaseBruto=$groupSumTonase> 0 ? ($groupKgTrashBruto / $groupSumTonase) * 100 : 0;
                                        $trashPersentaseNetto = $groupSumTonase > 0 ? ($groupKgTrashNetto / $groupSumTonase) * 100 : 0;
                                        @endphp

                                        <!-- Group Total Row -->
                                        <tr class="bg-gray-100 font-semibold">
                                            <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                                <strong>{{ $groupName }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-center border-2 border-gray-300">
                                                <strong>{{ number_format($groupSumTonase, 0, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupAvgPucuk, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupAvgDaun, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupAvgSogolan, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupAvgSiwilan, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupAvgTebumati, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupAvgTanah, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupTotalTrash, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($trashPersentaseBruto, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($trashPersentaseNetto, 2, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupKgTrashBruto, 0, ',', '.') }}</strong>
                                            </td>
                                            <td class="px-2 py-2 text-sm text-right border-2 border-gray-300">
                                                <strong>{{ number_format($groupKgTrashNetto, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                        @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>

                <!-- Tabel Tambahan (Perhitungan) -->
                <div class="flex-shrink-0 w-80">
                    <div class="rounded-md border-2 border-gray-400">
                        <table class="w-full bg-white text-xs border-collapse">
                            <thead class="bg-gray-50">
                                <!-- Header Level 1 - Samakan tinggi dengan tabel utama -->
                                <tr>
                                    <th rowspan="4" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk</th>
                                    <th rowspan="4" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Daun</th>
                                    <th rowspan="4" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan</th>
                                    <th rowspan="4" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan</th>
                                    <th rowspan="4" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati</th>
                                    <th rowspan="4" class="px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll</th>
                                </tr>
                                <tr>
                                    <th class="px-2 py-2 border-0">&nbsp;</th>
                                    <th class="px-2 py-2 border-0">&nbsp;</th>
                                    <th class="px-2 py-2 border-0">&nbsp;</th>
                                    <th class="px-2 py-2 border-0">&nbsp;</th>
                                    <th class="px-2 py-2 border-0">&nbsp;</th>
                                    <th class="px-2 py-2 border-0">&nbsp;</th>
                                </tr>

                            </thead>
                            <tbody class="bg-white">
                                @php
                                $previousGroupName2 = null;

                                // GRAND TOTAL nasional (kalau mau dipakai lagi di bawah)
                                $grandKgPucukAll = 0;
                                $grandKgDaunAll = 0;
                                $grandKgSogolanAll = 0;
                                $grandKgSiwilanAll = 0;
                                $grandKgTebumatiAll = 0;
                                $grandKgTanahAll = 0;
                                @endphp

                                @foreach($companyGroups as $groupName => $companies)
                                @php
                                if ($previousGroupName2 !== null && $previousGroupName2 !== $groupName) {
                                echo '<tr class="bg-white">
                                    <td colspan="6" class="px-2 py-2 border-0">&nbsp;</td>
                                </tr>';
                                }
                                $previousGroupName2 = $groupName;

                                // TOTAL per GROUP COMPANY (SUM KG)
                                $groupKgPucuk = 0;
                                $groupKgDaun = 0;
                                $groupKgSogolan = 0;
                                $groupKgSiwilan = 0;
                                $groupKgTebumati= 0;
                                $groupKgTanah = 0;
                                @endphp

                                @foreach($companies as $companyCode => $items)
                                @php
                                // Group by jenis within this company
                                $companyByJenis = [];
                                foreach($items as $item) {
                                $jenis = $item['jenis'];
                                if (!isset($companyByJenis[$jenis])) {
                                $companyByJenis[$jenis] = [];
                                }
                                $companyByJenis[$jenis][] = $item;
                                }

                                // Sort jenis: manual first, then mesin
                                $sortedJenis = [];
                                if (isset($companyByJenis['manual'])) {
                                $sortedJenis['manual'] = $companyByJenis['manual'];
                                }
                                if (isset($companyByJenis['mesin'])) {
                                $sortedJenis['mesin'] = $companyByJenis['mesin'];
                                }
                                @endphp

                                @foreach($sortedJenis as $jenis => $jenisItems)
                                @php
                                // Calculate averages for trash percentages and SUM for tonase
                                $totalPucuk = 0;
                                $totalDaun = 0;
                                $totalSogolan = 0;
                                $totalSiwilan = 0;
                                $totalTebumati = 0;
                                $totalTanah = 0;
                                $sumTonase = 0;
                                $count = count($jenisItems);

                                foreach($jenisItems as $item) {
                                $totalPucuk += $item['pucuk'];
                                $totalDaun += $item['daungulma'];
                                $totalSogolan += $item['sogolan'];
                                $totalSiwilan += $item['siwilan'];
                                $totalTebumati+= $item['tebumati'];
                                $totalTanah += $item['tanahetc'];
                                $sumTonase += $item['tonase_netto'];
                                }

                                $avgPucuk = $count > 0 ? $totalPucuk / $count : 0;
                                $avgDaun = $count > 0 ? $totalDaun / $count : 0;
                                $avgSogolan = $count > 0 ? $totalSogolan / $count : 0;
                                $avgSiwilan = $count > 0 ? $totalSiwilan / $count : 0;
                                $avgTebumati = $count > 0 ? $totalTebumati / $count : 0;
                                $avgTanah = $count > 0 ? $totalTanah / $count : 0;

                                // Calculate KG Trash dari tabel utama - sama seperti di tabel utama
                                $kgPucuk = ($avgPucuk / 100) * $sumTonase;
                                $kgDaun = ($avgDaun / 100) * $sumTonase;
                                $kgSogolan = ($avgSogolan / 100) * $sumTonase;
                                $kgSiwilan = ($avgSiwilan / 100) * $sumTonase;
                                $kgTebumati = ($avgTebumati / 100) * $sumTonase;
                                $kgTanah = ($avgTanah / 100) * $sumTonase;

                                // SUM ke level GROUP
                                $groupKgPucuk += $kgPucuk;
                                $groupKgDaun += $kgDaun;
                                $groupKgSogolan += $kgSogolan;
                                $groupKgSiwilan += $kgSiwilan;
                                $groupKgTebumati+= $kgTebumati;
                                $groupKgTanah += $kgTanah;

                                // SUM ke GRAND TOTAL nasional
                                $grandKgPucukAll += $kgPucuk;
                                $grandKgDaunAll += $kgDaun;
                                $grandKgSogolanAll += $kgSogolan;
                                $grandKgSiwilanAll += $kgSiwilan;
                                $grandKgTebumatiAll+= $kgTebumati;
                                $grandKgTanahAll += $kgTanah;
                                @endphp

                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($kgPucuk, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($kgDaun, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($kgSogolan, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($kgSiwilan, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($kgTebumati, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">{{ number_format($kgTanah, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                @endforeach

                                {{-- TOTAL per GROUP COMPANY (SUM KG) --}}
                                <tr class="bg-gray-100 font-semibold">
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                        <strong>{{ number_format($groupKgPucuk, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                        <strong>{{ number_format($groupKgDaun, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                        <strong>{{ number_format($groupKgSogolan, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                        <strong>{{ number_format($groupKgSiwilan, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                        <strong>{{ number_format($groupKgTebumati, 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                        <strong>{{ number_format($groupKgTanah, 0, ',', '.') }}</strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <div class="mt-8 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3 text-center">
                    REKAPITULASI PER GROUP COMPANY
                </h3>

                <div class="flex gap-6">
                    {{-- KIRI: TABEL REKAP (TOTAL + RATA PERTON) --}}
                    <div class="flex-1">
                        <div class="overflow-x-auto rounded-md border-2 border-gray-400">
                            <table class="w-full bg-white text-xs md:text-sm border-collapse">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Keterangan</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Total Tonase</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Pucuk (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Daun (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Sogolan (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Siwilan (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Tebu Mati (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Tanah dll (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Total Trash (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Trash Bruto (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">Trash Netto (%)</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">KG Trash Bruto</th>
                                        <th class="px-4 py-3 text-center font-bold text-gray-700 uppercase border-2 border-gray-400">KG Trash Netto</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @php
                                    // ====== hitung GRAND TOTAL & RATA PERTON (LOGIKA SAMA KAYAK YANG SUDAH KITA PAKAI) ======
                                    $grandTotalTonaseAll = 0;
                                    $grandTotalPucukAll = 0;
                                    $grandTotalDaunAll = 0;
                                    $grandTotalSogolanAll = 0;
                                    $grandTotalSiwilanAll = 0;
                                    $grandTotalTebumatiAll = 0;
                                    $grandTotalTanahAll = 0;
                                    $grandKgTrashBrutoAll = 0;
                                    $grandKgTrashNettoAll = 0;
                                    $grandCountGroups = 0;

                                    $grandKgPucukAll = 0;
                                    $grandKgDaunAll = 0;
                                    $grandKgSogolanAll = 0;
                                    $grandKgSiwilanAll = 0;
                                    $grandKgTebumatiAll = 0;
                                    $grandKgTanahAll = 0;

                                    foreach($companyGroups as $groupName => $companies) {
                                    $groupSumTonase = 0;
                                    $groupTotalPucuk = 0;
                                    $groupTotalDaun = 0;
                                    $groupTotalSogolan = 0;
                                    $groupTotalSiwilan = 0;
                                    $groupTotalTebumati = 0;
                                    $groupTotalTanah = 0;
                                    $groupTotalCompanies = 0;

                                    foreach ($companies as $companyCode => $items) {
                                    $companyByJenis = [];
                                    foreach ($items as $item) {
                                    $jenis = $item['jenis'];
                                    if (!isset($companyByJenis[$jenis])) {
                                    $companyByJenis[$jenis] = [];
                                    }
                                    $companyByJenis[$jenis][] = $item;
                                    }

                                    $sortedJenis = [];
                                    if (isset($companyByJenis['manual'])) {
                                    $sortedJenis['manual'] = $companyByJenis['manual'];
                                    }
                                    if (isset($companyByJenis['mesin'])) {
                                    $sortedJenis['mesin'] = $companyByJenis['mesin'];
                                    }

                                    foreach ($sortedJenis as $jenis => $jenisItems) {
                                    $count = count($jenisItems);
                                    if ($count === 0) continue;

                                    $sumTonase = 0;
                                    $sumPucuk = 0;
                                    $sumDaun = 0;
                                    $sumSogolan = 0;
                                    $sumSiwilan = 0;
                                    $sumTebumati = 0;
                                    $sumTanah = 0;

                                    foreach ($jenisItems as $row) {
                                    $sumTonase += $row['tonase_netto'];
                                    $sumPucuk += $row['pucuk'];
                                    $sumDaun += $row['daungulma'];
                                    $sumSogolan += $row['sogolan'];
                                    $sumSiwilan += $row['siwilan'];
                                    $sumTebumati += $row['tebumati'];
                                    $sumTanah += $row['tanahetc'];
                                    }

                                    $avgPucuk = $sumPucuk / $count;
                                    $avgDaun = $sumDaun / $count;
                                    $avgSogolan = $sumSogolan / $count;
                                    $avgSiwilan = $sumSiwilan / $count;
                                    $avgTebumati = $sumTebumati / $count;
                                    $avgTanah = $sumTanah / $count;

                                    // KG per komponen untuk company+jenis ini
                                    $kgPucuk = ($avgPucuk / 100) * $sumTonase;
                                    $kgDaun = ($avgDaun / 100) * $sumTonase;
                                    $kgSogolan = ($avgSogolan / 100) * $sumTonase;
                                    $kgSiwilan = ($avgSiwilan / 100) * $sumTonase;
                                    $kgTebumati = ($avgTebumati / 100) * $sumTonase;
                                    $kgTanah = ($avgTanah / 100) * $sumTonase;

                                    $groupSumTonase += $sumTonase;
                                    $groupTotalPucuk += $avgPucuk;
                                    $groupTotalDaun += $avgDaun;
                                    $groupTotalSogolan += $avgSogolan;
                                    $groupTotalSiwilan += $avgSiwilan;
                                    $groupTotalTebumati += $avgTebumati;
                                    $groupTotalTanah += $avgTanah;
                                    $groupTotalCompanies++;

                                    $grandKgPucukAll += $kgPucuk;
                                    $grandKgDaunAll += $kgDaun;
                                    $grandKgSogolanAll += $kgSogolan;
                                    $grandKgSiwilanAll += $kgSiwilan;
                                    $grandKgTebumatiAll += $kgTebumati;
                                    $grandKgTanahAll += $kgTanah;
                                    }
                                    }

                                    $groupAvgPucuk = $groupTotalCompanies > 0 ? $groupTotalPucuk / $groupTotalCompanies : 0;
                                    $groupAvgDaun = $groupTotalCompanies > 0 ? $groupTotalDaun / $groupTotalCompanies : 0;
                                    $groupAvgSogolan = $groupTotalCompanies > 0 ? $groupTotalSogolan / $groupTotalCompanies : 0;
                                    $groupAvgSiwilan = $groupTotalCompanies > 0 ? $groupTotalSiwilan / $groupTotalCompanies : 0;
                                    $groupAvgTebumati = $groupTotalCompanies > 0 ? $groupTotalTebumati / $groupTotalCompanies : 0;
                                    $groupAvgTanah = $groupTotalCompanies > 0 ? $groupTotalTanah / $groupTotalCompanies : 0;

                                    $groupTotalTrash = $groupAvgPucuk + $groupAvgDaun + $groupAvgSogolan + $groupAvgSiwilan + $groupAvgTebumati + $groupAvgTanah;
                                    $groupNettoTrash = max(0, $groupTotalTrash - 5);

                                    $groupKgTrashBruto = ($groupSumTonase * $groupTotalTrash) / 100;
                                    $groupKgTrashNetto = ($groupSumTonase * $groupNettoTrash) / 100;

                                    $grandTotalTonaseAll += $groupSumTonase;
                                    $grandTotalPucukAll += $groupAvgPucuk;
                                    $grandTotalDaunAll += $groupAvgDaun;
                                    $grandTotalSogolanAll += $groupAvgSogolan;
                                    $grandTotalSiwilanAll += $groupAvgSiwilan;
                                    $grandTotalTebumatiAll += $groupAvgTebumati;
                                    $grandTotalTanahAll += $groupAvgTanah;
                                    $grandKgTrashBrutoAll += $groupKgTrashBruto;
                                    $grandKgTrashNettoAll += $groupKgTrashNetto;
                                    $grandCountGroups++;
                                    }

                                    $grandAvgPucuk = $grandCountGroups > 0 ? $grandTotalPucukAll / $grandCountGroups : 0;
                                    $grandAvgDaun = $grandCountGroups > 0 ? $grandTotalDaunAll / $grandCountGroups : 0;
                                    $grandAvgSogolan = $grandCountGroups > 0 ? $grandTotalSogolanAll / $grandCountGroups : 0;
                                    $grandAvgSiwilan = $grandCountGroups > 0 ? $grandTotalSiwilanAll / $grandCountGroups : 0;
                                    $grandAvgTebumati = $grandCountGroups > 0 ? $grandTotalTebumatiAll / $grandCountGroups : 0;
                                    $grandAvgTanah = $grandCountGroups > 0 ? $grandTotalTanahAll / $grandCountGroups : 0;

                                    $grandTotalTrashSum = $grandAvgPucuk + $grandAvgDaun + $grandAvgSogolan + $grandAvgSiwilan + $grandAvgTebumati + $grandAvgTanah;

                                    $grandTrashPersentaseBruto = $grandTotalTonaseAll > 0 ? ($grandKgTrashBrutoAll / $grandTotalTonaseAll) * 100 : 0;
                                    $grandTrashPersentaseNetto = $grandTotalTonaseAll > 0 ? ($grandKgTrashNettoAll / $grandTotalTonaseAll) * 100 : 0;

                                    // rata per ton dari KG / tonase
                                    $rataPucukPerTon = $grandTotalTonaseAll > 0 ? ($grandKgPucukAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataDaunPerTon = $grandTotalTonaseAll > 0 ? ($grandKgDaunAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataSogolanPerTon = $grandTotalTonaseAll > 0 ? ($grandKgSogolanAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataSiwilanPerTon = $grandTotalTonaseAll > 0 ? ($grandKgSiwilanAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataTebumatiPerTon = $grandTotalTonaseAll > 0 ? ($grandKgTebumatiAll / $grandTotalTonaseAll) * 100 : 0;
                                    $rataTanahPerTon = $grandTotalTonaseAll > 0 ? ($grandKgTanahAll / $grandTotalTonaseAll) * 100 : 0;

                                    $totalTrashPerTon = $rataPucukPerTon + $rataDaunPerTon + $rataSogolanPerTon +
                                    $rataSiwilanPerTon + $rataTebumatiPerTon + $rataTanahPerTon;
                                    @endphp

                                    {{-- TOTAL --}}
                                    <tr class="bg-yellow-100">
                                        <td class="px-4 py-3 text-sm text-center font-bold border-2 border-gray-400">
                                            TOTAL
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandTotalTonaseAll, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandAvgPucuk, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandAvgDaun, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandAvgSogolan, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandAvgSiwilan, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandAvgTebumati, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandAvgTanah, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandTotalTrashSum, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandTrashPersentaseBruto, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandTrashPersentaseNetto, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandKgTrashBrutoAll, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-bold border-2 border-gray-400">
                                            {{ number_format($grandKgTrashNettoAll, 0, ',', '.') }}
                                        </td>
                                    </tr>

                                    {{-- RATA PERTON: format kolom sama, cuma tonase & kolom terakhir dikosongin --}}
                                    <tr class="bg-white">
                                        <td colspan="2" class="px-4 py-3 text-sm text-center font-bold border-2 border-gray-400">
                                            RATA PERTON
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{ number_format($rataPucukPerTon, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{ number_format($rataDaunPerTon, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{ number_format($rataSogolanPerTon, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{ number_format($rataSiwilanPerTon, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{ number_format($rataTebumatiPerTon, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{ number_format($rataTanahPerTon, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{ number_format($totalTrashPerTon, 2, ',', '.') }}
                                        </td>
                                        <td colspan="4" class="px-4 py-3 text-sm text-right border-2 border-gray-400">
                                            {{-- kolom ekstra dibiarkan kosong --}}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- KANAN: TABEL REKAP SAMPING (TOTAL KG PUCUK–DLL) --}}
                    <div class="flex-shrink-0 w-80">
                        <div class="rounded-md border-2 border-gray-400">
                            <table class="w-full bg-white text-xs border-collapse">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-2 py-2 text-center font-medium text-gray-500 uppercase border-2 border-gray-400">Pucuk</th>
                                        <th class="px-2 py-2 text-center font-medium text-gray-500 uppercase border-2 border-gray-400">Daun</th>
                                        <th class="px-2 py-2 text-center font-medium text-gray-500 uppercase border-2 border-gray-400">Sogolan</th>
                                        <th class="px-2 py-2 text-center font-medium text-gray-500 uppercase border-2 border-gray-400">Siwilan</th>
                                        <th class="px-2 py-2 text-center font-medium text-gray-500 uppercase border-2 border-gray-400">Tebu Mati</th>
                                        <th class="px-2 py-2 text-center font-medium text-gray-500 uppercase border-2 border-gray-400">Tanah dll</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @php
                                    // di sini pakai total KG nasional yang sudah lu hitung (bisa pake $grandKgPucukAll, dst)
                                    $totalKgPucuk = $grandKgPucukAll ?? 0;
                                    $totalKgDaun = $grandKgDaunAll ?? 0;
                                    $totalKgSogolan = $grandKgSogolanAll ?? 0;
                                    $totalKgSiwilan = $grandKgSiwilanAll ?? 0;
                                    $totalKgTebumati= $grandKgTebumatiAll?? 0;
                                    $totalKgTanah = $grandKgTanahAll ?? 0;
                                    @endphp

                                    <tr class="bg-yellow-100 font-semibold">
                                        <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                            {{ number_format($totalKgPucuk, 0, ',', '.') }}
                                        </td>
                                        <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                            {{ number_format($totalKgDaun, 0, ',', '.') }}
                                        </td>
                                        <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                            {{ number_format($totalKgSogolan, 0, ',', '.') }}
                                        </td>
                                        <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                            {{ number_format($totalKgSiwilan, 0, ',', '.') }}
                                        </td>
                                        <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                            {{ number_format($totalKgTebumati, 0, ',', '.') }}
                                        </td>
                                        <td class="px-2 py-2 text-xs text-right border-2 border-gray-300">
                                            {{ number_format($totalKgTanah, 0, ',', '.') }}
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

            .print-only {
                display: block !important;
            }

            table {
                font-size: 9px;
                border-collapse: collapse !important;
            }

            th,
            td {
                padding: 3px !important;
                border: 2px solid #333 !important;
            }

            .border-2 {
                border-width: 2px !important;
                border-color: #333 !important;
            }
        }
    </style>

</x-layout>