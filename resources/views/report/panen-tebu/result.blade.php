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
                <div class="mb-8 @if(!$loop->last) print-break @endif">
                    <!-- Header untuk setiap tanggal -->
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 text-center uppercase border-b border-gray-200 pb-2">
                        Detail Panen Harian ({{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }})
                    </h2>
                    
                    <div class="overflow-x-auto">
                        <table class="report-table min-w-full">
                            <thead>
                                <!-- First row of headers (Main headers) -->
                                <tr class="bg-gray-100">
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">No</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Sub Kontraktor</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Nama Sopir</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">No Polisi</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">No SJL</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Plot</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Bruto (KG)</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Tarra (KG)</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Netto (KG)</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Trash %</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Trash %</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Pot (KG)</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Berat Bersih (KG)</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" colspan="2">Tebang Muat Tebu Giling Manual</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" colspan="2">Tebang Muat Tebu Giling GL</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Kirim <br><br> Rp 35/kg</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Extra Fooding <br><br> Rp 35/kg</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Tebu Tdk Diseset <br><br> Rp 35/kg</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Fee Kontraktor/P <br><br> Rp 35/kg</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Tebu Sulit <br><br> Rp 35/kg</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Langsir <br><br> Rp 35/kg</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Insentif BSM/P <br><br> Rp 35/kg</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide" rowspan="2">Ket Kg <br>(BSM)</th>
                                </tr>
                                
                                <!-- Second row of headers (Sub headers) -->
                                <tr class="bg-gray-100">
                                    <!-- Sub-columns for "Tebang Muat Tebu Giling Manual" -->
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide">Premium</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide">Non Premium</th>
                                    <!-- Sub-columns for "Tebang Muat Tebu Giling GL" -->
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide">Premium GL</th>
                                    <th class="text-center font-semibold text-xs uppercase tracking-wide">Non Premium GL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dataPerTanggal as $dt)
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
                                        @if($dt->trash_percentage > 0)
                                            {{ number_format($dt->trash_percentage, 2) }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>trash %</td>
                                    <td>{{number_format($dt->traf)}}</td>
                                    <td>{{number_format($dt->beratbersih)}}</td>
                                    <td>@if($dt->kodetebang == 'Premium' && $dt->muatgl == '0') {{number_format($dt->beratbersih)}} @endif</td>
                                    <td>@if($dt->kodetebang != 'Premium' && $dt->muatgl == '0') {{number_format($dt->beratbersih)}} @endif</td>
                                    <td>@if($dt->kodetebang == 'Premium' && $dt->muatgl == '1') {{number_format($dt->beratbersih)}} @endif</td>
                                    <td>@if($dt->kodetebang != 'Premium' && $dt->muatgl == '1') {{number_format($dt->beratbersih)}} @endif</td>
                                    <td>@if($dt->kendaraankontraktor == 1) {{number_format($dt->netto)}}  @endif</td>
                                    <td>@if($dt->kendaraankontraktor == 0) {{number_format($dt->netto)}}  @endif</td>
                                    <td>{{number_format($dt->beratbersih)}}</td>
                                    <td>{{number_format($dt->beratbersih)}}</td>
                                    <td>@if($dt->tebusulit == 1) {{number_format($dt->beratbersih)}} @endif</td>
                                    <td>@if($dt->langsir == 1) {{number_format($dt->beratbersih)}} @endif</td>
                                    <td>Coming Soon!</td>
                                    <td>Coming Soon!</td>
                                </tr>
                                @endforeach
                                
                                <!-- Summary row untuk setiap tanggal -->
                                <tr class="bg-yellow-50 border-t-2 border-yellow-400 font-semibold">
                                    <td colspan="6" class="text-right font-bold">TOTAL {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}:</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('bruto')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('brkend')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('netto')) }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('traf')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kodetebang', 'Premium')->where('muatgl', '0')->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kodetebang', '!=', 'Premium')->where('muatgl', '0')->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kodetebang', 'Premium')->where('muatgl', '1')->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kodetebang', '!=', 'Premium')->where('muatgl', '1')->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kendaraankontraktor', 1)->sum('netto')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('kendaraankontraktor', 0)->sum('netto')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('tebusulit', 1)->sum('beratbersih')) }}</td>
                                    <td class="font-bold">{{ number_format($dataPerTanggal->where('langsir', 1)->sum('beratbersih')) }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
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