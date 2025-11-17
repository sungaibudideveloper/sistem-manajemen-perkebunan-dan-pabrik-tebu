<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Panen Tebu Giling - Report</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Custom Print Styles -->
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
            .print-break {
                page-break-after: always;
            }

            /* Ensure clean print layout */
            * {
                box-shadow: none !important;
                text-shadow: none !important;
            }
        }

        /* Clean white background */
        body {
            background-color: white;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Custom table styling for better print */
        .report-table {
            border-collapse: collapse;
            width: 100%;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        .report-table th {
            background-color: #f9fafb;
            font-weight: 600;
        }

        .report-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="bg-white min-h-screen">

    <!-- Action Buttons (Hidden when printing) -->
    <div class="no-print fixed top-4 right-4 z-50 flex space-x-2">
        <!-- Print Button -->
        <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transition duration-200 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            <span>Print</span>
        </button>
    </div>

    <!-- Back Button -->
    <div class="no-print fixed top-4 left-4 z-50">
        <button onclick="window.history.back()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md shadow-lg transition duration-200 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali</span>
        </button>
    </div>

    <!-- Main Content Container -->
    <div class="w-full max-w-none mx-auto p-8">

        <!-- Company Header -->
        <div class="text-center mb-2">
            <div class="text-lg font-bold text-gray-900 uppercase tracking-wide">
                {{ session('companycode') ?? 'PT. PERKEBUNAN NUSANTARA' }}
            </div>
        </div>

        <!-- Report Header -->
        <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4 uppercase">
                BERITA ACARA PANEN TEBU GILING
            </h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-700 max-w-4xl mx-auto">
                <div class="bg-gray-50 p-3 rounded">
                    <span class="font-semibold">Kontraktor:</span><br>
                    <span class="text-base">{{ $kontraktor }} - {{$data[0]->namakontraktor}}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded">
                    <span class="font-semibold">Periode:</span><br>
                    <span class="text-base">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</span>
                </div>
                <div class="bg-gray-50 p-3 rounded">
                    <span class="font-semibold">Tanggal Cetak:</span><br>
                    <span class="text-base">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        <!-- Detail Table Per Tanggal -->
        <div class="mb-8">
            @php
                // Grouping data by tanggalangkut
                $groupedData = collect($data)->groupBy(function($item) {
                    return \Carbon\Carbon::parse($item->tanggalangkut)->format('Y-m-d');
                })->sortKeys();
            @endphp

            @foreach ($groupedData as $tanggal => $dataPerTanggal)
                @php
                    // Variabel untuk total per tanggal
                    $totalPotKg = 0;
                    $totalBeratBersihHarian = 0;
                @endphp

                <div class="mb-8 @if(!$loop->last) print-break @endif">
                    <!-- Header untuk setiap tanggal -->
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 text-center uppercase border-b border-gray-200 pb-2">
                        <strong>Detail Panen Harian ({{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }})</strong>
                    </h2>

                    <div class="overflow-x-auto">
                        <table class="report-table min-w-full">
                            <thead>
                                <!-- First row of headers (Main headers) -->
                                <tr class="bg-gray-100">
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>No</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Sub Kontraktor</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Nama Sopir</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>No Polisi</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>No SJL</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Plot</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Bruto (KG)</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Tarra (KG)</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Netto (KG)</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Trash Pabrik %</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Trash Kebun %</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Pot (KG)</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Berat Bersih (KG)</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" colspan="2"><strong>Tebang Muat Tebu Giling Manual</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" colspan="2"><strong>Tebang Muat Tebu Giling GL</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Kirim <br><br> Rp 35/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Extra Fooding <br><br> Rp {{number_format($tabelharga[0]->extrafooding ?? 0)}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Tebu Tdk Diseset <br><br> Rp {{number_format($tabelharga[0]->tebutdkseset ?? 0)}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Fee Kontraktor/P <br><br> Rp {{number_format($tabelharga[0]->manualfeekont ?? 0)}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Tebu Sulit <br><br> Rp {{number_format($tabelharga[0]->manualtebusulit ?? 0)}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Langsir <br><br> Rp {{number_format($tabelharga[0]->langsir ?? 0)}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Insentif BSM/P <br><br> Rp {{number_format($tabelharga[0]->manualbsm ?? 0)}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2"><strong>Ket Kg <br>(BSM)</th>
                                </tr>

                                <!-- Second row of headers (Sub headers) -->
                                <tr class="bg-gray-100">
                                    <!-- Sub-columns for "Tebang Muat Tebu Giling Manual" -->
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide"><strong>Premium <br><br> Rp {{number_format(($tabelharga[0]->manualtebang ?? 0) + ($tabelharga[0]->manualmuat ?? 0))}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide"><strong>Non Premium <br><br> Rp {{number_format($tabelharga[0]->manualnonpremi ?? 0)}}/kg</th>
                                    <!-- Sub-columns for "Tebang Muat Tebu Giling GL" -->
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide"><strong>Premium GL <br><br> Rp {{number_format(($tabelharga[0]->glkebuntebang ?? 0) + ($tabelharga[0]->glkebunmuat ?? 0))}}/kg</strong></th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide"><strong>Non Premium GL <br><br> Rp {{number_format($tabelharga[0]->glkebunnonpremi ?? 0)}}/kg</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataPerTanggal as $dt)
                                @php
                                    // Perhitungan variabel untuk konsistensi
                                    $trashKebun = ($dt->trash_percentage > 3) ? $dt->trash_percentage - 3 : 0;
                                    $potKg = ($trashKebun > 0) ? round($dt->netto * $trashKebun / 100, 0, PHP_ROUND_HALF_UP) : 0;
                                    $beratBersih = $dt->netto - $potKg;

                                    // Accumulate untuk total
                                    $totalPotKg += $potKg;
                                    $totalBeratBersihHarian += $beratBersih;
                                @endphp
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$dt->namasubkontraktor}}</td>
                                    <td>{{$dt->namasupir}}</td>
                                    <td>{{$dt->nomorpolisi}}</td>
                                    <td>{{$dt->suratjalanno}}</td>
                                    <td>{{$dt->plot}}</td>
                                    <td>{{number_format($dt->bruto)}}</td>
                                    <td>{{number_format($dt->brkend)}}</td>
                                    <td>{{number_format($dt->netto)}}</td>
                                    <td>
                                        @if($dt->trash_percentage > 3)
                                            {{ number_format($dt->trash_percentage, 3) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($trashKebun > 0)
                                            {{ number_format($trashKebun, 3) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($potKg > 0)
                                            {{ number_format($potKg) }}
                                        @endif
                                    </td>
                                    <td>{{ number_format($beratBersih) }}</td>
                                    <td>@if($dt->kodetebang == 'Premium' && $dt->muatgl == '0') {{number_format($beratBersih)}} @endif</td>
                                    <td>@if($dt->kodetebang != 'Premium' && $dt->muatgl == '0') {{number_format($beratBersih)}} @endif</td>
                                    <td>@if($dt->kodetebang == 'Premium' && $dt->muatgl == '1') {{number_format($beratBersih)}} @endif</td>
                                    <td>@if($dt->kodetebang != 'Premium' && $dt->muatgl == '1') {{number_format($beratBersih)}} @endif</td>
                                    <td>@if($dt->kendaraankontraktor == 1) {{number_format($dt->netto)}}  @endif</td>
                                    <td>@if($dt->kendaraankontraktor == 0) {{number_format($dt->netto)}}  @endif</td>
                                    <td>{{number_format($beratBersih)}}</td>
                                    <td>{{number_format($beratBersih)}}</td>
                                    <td>@if($dt->tebusulit == 1) {{number_format($beratBersih)}} @endif</td>
                                    <td>@if($dt->langsir == 1) {{number_format($beratBersih)}} @endif</td>
                                    <td>
                                        @if(!empty($dt->averagescore) && $dt->averagescore < 1200)
                                            {{ number_format($beratBersih) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($dt->averagescore) && !empty($dt->grade))
                                            {{ number_format($dt->averagescore, 1) }} ({{ $dt->grade }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach

                                <!-- Summary row untuk setiap tanggal -->
                                @php
                                    // Hitung total untuk kategori tebang muat menggunakan berat bersih yang sudah dihitung
                                    $totalPremiumManual = 0;
                                    $totalNonPremiumManual = 0;
                                    $totalPremiumGL = 0;
                                    $totalNonPremiumGL = 0;
                                    $totalTebuSulit = 0;
                                    $totalLangsir = 0;
                                    $totalInsentifBSM = 0;

                                    foreach ($dataPerTanggal as $dt) {
                                        $trashKebunCalc = ($dt->trash_percentage > 3) ? $dt->trash_percentage - 3 : 0;
                                        $potKgCalc = ($trashKebunCalc > 0) ? round($dt->netto * $trashKebunCalc / 100, 0, PHP_ROUND_HALF_UP) : 0;
                                        $beratBersihCalc = $dt->netto - $potKgCalc;

                                        if($dt->kodetebang == 'Premium' && $dt->muatgl == '0') $totalPremiumManual += $beratBersihCalc;
                                        if($dt->kodetebang != 'Premium' && $dt->muatgl == '0') $totalNonPremiumManual += $beratBersihCalc;
                                        if($dt->kodetebang == 'Premium' && $dt->muatgl == '1') $totalPremiumGL += $beratBersihCalc;
                                        if($dt->kodetebang != 'Premium' && $dt->muatgl == '1') $totalNonPremiumGL += $beratBersihCalc;
                                        if($dt->tebusulit == 1) $totalTebuSulit += $beratBersihCalc;
                                        if($dt->langsir == 1) $totalLangsir += $beratBersihCalc;

                                        // Hitung total Insentif BSM: jika averagescore < 1200 maka tambahkan beratBersih
                                        if(!empty($dt->averagescore) && $dt->averagescore < 1200) {
                                            $totalInsentifBSM += $beratBersihCalc;
                                        }
                                    }
                                @endphp
                                <tr class="bg-yellow-50 border-t-2 border-yellow-400 font-semibold">
                                    <td colspan="6" class="text-right font-bold">TOTAL {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}:</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('bruto')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('brkend')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('netto')) }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="font-bold">{{ number_format($totalPotKg) }}</td>
                                    <td class="font-bold">{{ number_format($totalBeratBersihHarian) }}</td>
                                    <td class="font-bold">{{ number_format($totalPremiumManual) }}</td>
                                    <td class="font-bold">{{ number_format($totalNonPremiumManual) }}</td>
                                    <td class="font-bold">{{ number_format($totalPremiumGL) }}</td>
                                    <td class="font-bold">{{ number_format($totalNonPremiumGL) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kendaraankontraktor', 1)->sum('netto')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kendaraankontraktor', 0)->sum('netto')) }}</td>
                                    <td class="font-bold">{{ number_format($totalBeratBersihHarian) }}</td>
                                    <td class="font-bold">{{ number_format($totalBeratBersihHarian) }}</td>
                                    <td class="font-bold">{{ number_format($totalTebuSulit) }}</td>
                                    <td class="font-bold">{{ number_format($totalLangsir) }}</td>
                                    <td class="font-bold">{{ number_format($totalInsentifBSM) }}</td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Summary Total Section -->
        <div class="mt-12 mb-16">
            <h2 class="text-xl font-bold text-gray-900 mb-6 text-center uppercase border-b-2 border-gray-300 pb-4">
                REKAPITULASI TOTAL PERIODE {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </h2>

            @php
                // Inisialisasi variabel total keseluruhan
                $grandTotalPremiumManual = 0;
                $grandTotalNonPremiumManual = 0;
                $grandTotalPremiumGL = 0;
                $grandTotalNonPremiumGL = 0;
                $grandTotalKirimKontraktor = 0;
                $grandTotalExtraFooding = 0;
                $grandTotalTebuTidakSeset = 0;
                $grandTotalTebuSulit = 0;
                $grandTotalLangsir = 0;
                $grandTotalInsentifBSM = 0;

                // Loop melalui semua data untuk menghitung total
                foreach ($data as $dt) {
                    $trashKebunCalc = ($dt->trash_percentage > 3) ? $dt->trash_percentage - 3 : 0;
                    $potKgCalc = ($trashKebunCalc > 0) ? round($dt->netto * $trashKebunCalc / 100, 0, PHP_ROUND_HALF_UP) : 0;
                    $beratBersihCalc = $dt->netto - $potKgCalc;

                    // Akumulasi berdasarkan kondisi
                    if($dt->kodetebang == 'Premium' && $dt->muatgl == '0') $grandTotalPremiumManual += $beratBersihCalc;
                    if($dt->kodetebang != 'Premium' && $dt->muatgl == '0') $grandTotalNonPremiumManual += $beratBersihCalc;
                    if($dt->kodetebang == 'Premium' && $dt->muatgl == '1') $grandTotalPremiumGL += $beratBersihCalc;
                    if($dt->kodetebang != 'Premium' && $dt->muatgl == '1') $grandTotalNonPremiumGL += $beratBersihCalc;
                    if($dt->kendaraankontraktor == 1) $grandTotalKirimKontraktor += $dt->netto;
                    if($dt->kendaraankontraktor == 0) $grandTotalExtraFooding += $dt->netto;
                    // Tebu Tidak Seset menggunakan berat bersih untuk semua data
                    $grandTotalTebuTidakSeset += $beratBersihCalc;
                    if($dt->tebusulit == 1) $grandTotalTebuSulit += $beratBersihCalc;
                    if($dt->langsir == 1) $grandTotalLangsir += $beratBersihCalc;
                    if(!empty($dt->averagescore) && $dt->averagescore < 1200) $grandTotalInsentifBSM += $beratBersihCalc;
                }
            @endphp

            <div class="overflow-x-auto">
                <table class="report-table min-w-full border-2 border-gray-400">
                    <thead>
                        <tr class="bg-blue-100 border-b-2 border-blue-400">
                            <th class="text-center font-bold text-sm uppercase tracking-wide py-3 px-4 border-r border-gray-300">No</th>
                            <th class="text-center font-bold text-sm uppercase tracking-wide py-3 px-4 border-r border-gray-300">Kategori</th>
                            <th class="text-center font-bold text-sm uppercase tracking-wide py-3 px-4 border-r border-gray-300">Berat (KG)</th>
                            <th class="text-center font-bold text-sm uppercase tracking-wide py-3 px-4 border-r border-gray-300">Tarif (Rp/KG)</th>
                            <th class="text-center font-bold text-sm uppercase tracking-wide py-3 px-4">Total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">1</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Tebang Muat Tebu Giling Manual (Premium)</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalPremiumManual) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format(($tabelharga[0]->manualtebang ?? 0) + ($tabelharga[0]->manualmuat ?? 0)) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalPremiumManual * (($tabelharga[0]->manualtebang ?? 0) + ($tabelharga[0]->manualmuat ?? 0))) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">2</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Tebang Muat Tebu Giling Manual (Non Premium)</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalNonPremiumManual) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format($tabelharga[0]->manualnonpremi ?? 0) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalNonPremiumManual * ($tabelharga[0]->manualnonpremi ?? 0)) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">3</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Tebang Muat Tebu Giling GL (Premium)</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalPremiumGL) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format(($tabelharga[0]->glkebuntebang ?? 0) + ($tabelharga[0]->glkebunmuat ?? 0)) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalPremiumGL * (($tabelharga[0]->glkebuntebang ?? 0) + ($tabelharga[0]->glkebunmuat ?? 0))) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">4</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Tebang Muat Tebu Giling GL (Non Premium)</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalNonPremiumGL) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format($tabelharga[0]->glkebunnonpremi ?? 0) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalNonPremiumGL * ($tabelharga[0]->glkebunnonpremi ?? 0)) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">5</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Kirim</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalKirimKontraktor) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">35</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalKirimKontraktor * 35) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">6</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Extra Fooding</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalExtraFooding) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format($tabelharga[0]->extrafooding ?? 0) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalExtraFooding * ($tabelharga[0]->extrafooding ?? 0)) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">7</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Tebu Tidak Di Seset</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalTebuTidakSeset) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format($tabelharga[0]->tebutdkseset ?? 0) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalTebuTidakSeset * ($tabelharga[0]->tebutdkseset ?? 0)) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">8</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Tebu Sulit</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalTebuSulit) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format($tabelharga[0]->manualtebusulit ?? 0) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalTebuSulit * ($tabelharga[0]->manualtebusulit ?? 0)) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">9</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Langsir</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalLangsir) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format($tabelharga[0]->langsir ?? 0) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalLangsir * ($tabelharga[0]->langsir ?? 0)) }}</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="text-center py-2 px-4 border-r border-gray-300">10</td>
                            <td class="text-left py-2 px-4 border-r border-gray-300 font-medium">Insentif BSM/P</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300 font-semibold">{{ number_format($grandTotalInsentifBSM) }}</td>
                            <td class="text-right py-2 px-4 border-r border-gray-300">{{ number_format($tabelharga[0]->manualbsm ?? 0) }}</td>
                            <td class="text-right py-2 px-4 font-bold text-green-700">{{ number_format($grandTotalInsentifBSM * ($tabelharga[0]->manualbsm ?? 0)) }}</td>
                        </tr>

                        <!-- Grand Total Row -->
                        @php
                            $finalTotal =
                                ($grandTotalPremiumManual * (($tabelharga[0]->manualtebang ?? 0) + ($tabelharga[0]->manualmuat ?? 0))) +
                                ($grandTotalNonPremiumManual * ($tabelharga[0]->manualnonpremi ?? 0)) +
                                ($grandTotalPremiumGL * (($tabelharga[0]->glkebuntebang ?? 0) + ($tabelharga[0]->glkebunmuat ?? 0))) +
                                ($grandTotalNonPremiumGL * ($tabelharga[0]->glkebunnonpremi ?? 0)) +
                                ($grandTotalKirimKontraktor * 35) +
                                ($grandTotalExtraFooding * ($tabelharga[0]->extrafooding ?? 0)) +
                                ($grandTotalTebuTidakSeset * ($tabelharga[0]->tebutdkseset ?? 0)) +
                                ($grandTotalTebuSulit * ($tabelharga[0]->manualtebusulit ?? 0)) +
                                ($grandTotalLangsir * ($tabelharga[0]->langsir ?? 0)) +
                                ($grandTotalInsentifBSM * ($tabelharga[0]->manualbsm ?? 0));
                        @endphp
                        <tr class="bg-blue-200 border-t-4 border-blue-600 font-bold text-lg">
                            <td colspan="4" class="text-center py-4 px-4 font-black uppercase text-blue-900">GRAND TOTAL</td>
                            <td class="text-right py-4 px-4 font-black text-blue-900 text-xl">{{ number_format($finalTotal) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary Info -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="font-semibold text-blue-900">Total Berat Bersih:</div>
                    <div class="text-lg font-bold text-blue-800">
                        @php
                            $totalBeratBersih = 0;
                            foreach ($data as $dt) {
                                $trashKebunCalc = ($dt->trash_percentage > 3) ? $dt->trash_percentage - 3 : 0;
                                $potKgCalc = ($trashKebunCalc > 0) ? round($dt->netto * $trashKebunCalc / 100, 0, PHP_ROUND_HALF_UP) : 0;
                                $totalBeratBersih += ($dt->netto - $potKgCalc);
                            }
                        @endphp
                        {{ number_format($totalBeratBersih) }} KG
                    </div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="font-semibold text-green-900">Total Netto:</div>
                    <div class="text-lg font-bold text-green-800">{{ number_format(collect($data)->sum('netto')) }} KG</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <div class="font-semibold text-yellow-900">Total Bruto:</div>
                    <div class="text-lg font-bold text-yellow-800">{{ number_format(collect($data)->sum('bruto')) }} KG</div>
                </div>
            </div>
        </div>

        <!-- Footer/Signature Section -->
        <div class="mt-16 print-break">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <!-- Kontraktor Signature -->
                <div>
                    <p class="text-sm font-semibold mb-16">Kontraktor</p>
                    <div class="border-t-2 border-gray-400 pt-2">
                        <p class="text-sm font-bold">{{ $kontraktor }}</p>
                    </div>
                </div>

                <!-- Manager Signature -->
                <div>
                    <p class="text-sm font-semibold mb-16">Mengetahui</p>
                    <div class="border-t-2 border-gray-400 pt-2">
                        <p class="text-sm font-bold">Manager Kebun</p>
                    </div>
                </div>

                <!-- Approval Signature -->
                <div>
                    <p class="text-sm font-semibold mb-16">Menyetujui</p>
                    <div class="border-t-2 border-gray-400 pt-2">
                        <p class="text-sm font-bold">General Manager</p>
                    </div>
                </div>
            </div>

            <!-- Footer Info -->
            <div class="mt-8 text-center text-xs text-gray-500 border-t border-gray-200 pt-4">
                <p>Dokumen ini digenerate secara otomatis pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
                <p>{{ session('companycode') ?? 'PT. PERKEBUNAN NUSANTARA' }}</p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl+P for print
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
            // Esc to go back
            if (e.key === 'Escape') {
                window.history.back();
            }
        });

        // Print event handlers
        window.addEventListener('beforeprint', function() {
            console.log('Preparing to print report...');
            document.title = 'Berita Acara Panen Tebu - {{ $kontraktor }} - {{ \Carbon\Carbon::parse($startDate)->format("d-m-Y") }} sd {{ \Carbon\Carbon::parse($endDate)->format("d-m-Y") }}';
        });

        window.addEventListener('afterprint', function() {
            console.log('Print dialog closed');
        });

        // Auto focus for better UX
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Report loaded successfully');
            console.log('Data received:', {
                kontraktor: '{{ $kontraktor }}',
                startDate: '{{ $startDate }}',
                endDate: '{{ $endDate }}'
            });
        });
    </script>

</body>
</html>
