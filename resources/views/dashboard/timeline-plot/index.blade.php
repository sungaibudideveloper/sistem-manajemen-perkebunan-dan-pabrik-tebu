<x-layout>
    <x-slot:title>{{ $title }}</x-slot>
    <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
    <x-slot:nav>{{ $nav }}</x-slot:nav>
    
    <style>
        .table-timeline {
            font-size: 11px;
            border-collapse: collapse;
        }
        .table-timeline th, .table-timeline td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: center;
        }
        .table-timeline th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .header-main {
            background-color: #fff;
            border: 2px solid #333;
            padding: 10px;
            margin-bottom: 20px;
        }
        .status-ontime { color: #28a745; }
        .status-late { color: #dc3545; }
    </style>

    <div class="mx-auto px-6 py-4">
        <!-- Header Kegiatan -->
        <div class="header-main">
            <div class="flex justify-between items-start">
                <div>
                    <img src="/path/to/logo.png" alt="Logo" class="h-16">
                </div>
                <div class="text-center flex-1">
                    <h1 class="text-2xl font-bold">KEGIATAN PASCA PANEN PT. TBL</h1>
                    <p class="text-sm mt-2">BULAN: <strong>{{ date('F Y') }}</strong></p>
                    <p class="text-sm">DIVISI: <strong>1</strong></p>
                </div>
            </div>
        </div>

        <!-- Tabel Timeline -->
        <div class="overflow-x-auto">
            <table class="table-timeline w-full bg-white shadow-lg">
                <thead>
                    <!-- Header Kategori Kegiatan -->
                    <tr>
                        <th rowspan="3" class="w-20">PLOT</th>
                        <th colspan="6">REPLANTING</th>
                        <th colspan="8">KEGIATAN PANEN</th>
                        <th colspan="3">HASIL SORTANCE PK</th>
                        <th rowspan="2" colspan="2">SALAH</th>
                        <th rowspan="2" colspan="2">KEPRAS</th>
                        <th colspan="4">HASIL TTL</th>
                        <th colspan="2">REALISASI TANAM</th>
                        <th colspan="2">REALISASI PANEN</th>
                    </tr>
                    
                    <!-- Sub-header -->
                    <tr>
                        <!-- REPLANTING -->
                        <th>TUMBA</th>
                        <th>MENANAM</th>
                        <th>PLANNING</th>
                        <th>SUMPIT/BS</th>
                        <th>TUMBUK</th>
                        <th>MENEBAS</th>
                        
                        <!-- KEGIATAN PANEN -->
                        <th>BABAD</th>
                        <th>RAWAT PASAR</th>
                        <th>TANAM</th>
                        <th>PANEN</th>
                        <th>RAWAT PRODUKSI</th>
                        <th>EGREK</th>
                        <th>PANEN RENDAH</th>
                        <th>LATE PB</th>
                        
                        <!-- HASIL SORTANCE -->
                        <th>CUP</th>
                        <th>SALDO</th>
                        <th>KEPRAS</th>
                        
                        <!-- Lanjutan kolom -->
                        <th colspan="4">TTL 1-3</th>
                        <th>HA</th>
                        <th>%</th>
                        <th>HA</th>
                        <th>%</th>
                    </tr>
                    
                    <!-- Tanggal (bisa dinamis per hari) -->
                    <tr>
                        @for($i = 1; $i <= 29; $i++)
                            <th class="text-xs">{{ $i }}</th>
                        @endfor
                    </tr>
                </thead>
                
                <tbody>
                    @foreach($plotHeaders as $index => $plot)
                    <tr class="hover:bg-blue-50">
                        <td class="font-semibold text-left">{{ $plot->plot }}</td>
                        
                        <!-- Data kegiatan per tanggal (sample, sesuaikan dengan data real) -->
                        @php
                            // Ambil data kegiatan untuk plot ini
                            $plotActivities = $details->where('plot', $plot->plot);
                        @endphp
                        
                        @for($day = 1; $day <= 29; $day++)
                            <td class="text-xs">
                                @php
                                    // Cek apakah ada kegiatan di tanggal ini
                                    $activity = $plotActivities->first(function($item) use ($day) {
                                        return date('j', strtotime($item->tanggal ?? '')) == $day;
                                    });
                                @endphp
                                
                                @if($activity)
                                    <span class="status-ontime">✓</span>
                                @else
                                    -
                                @endif
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        <div class="mt-4 flex gap-4 text-sm">
            <div><span class="status-ontime">✓</span> = Tepat Waktu</div>
            <div><span class="status-late">✗</span> = Terlambat</div>
        </div>
    </div>
</x-layout>