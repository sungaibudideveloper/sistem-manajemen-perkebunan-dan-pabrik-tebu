<?php
namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\usematerialhdr;
use App\Models\MasterData\Batch;
use Arr;


class TimelineController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Dashboard',
        ]);

    } 
    public function index()
    {
      $title = "Dashboard Timeline";
      $nav = "Timeline";

      return view('dashboard.timeline.index')->with([
        'title' => $title,
        'nav'   => $nav
      ]);
    }
   
    public function plot(Request $request)
    {
    $companyCode = session('companycode');
    $fillFilter  = $request->get('fill', 'all');
    $cropType    = $request->get('crop', 'pc');

    $plotHeaders = DB::table('batch as b')  // ✅ GANTI: Mulai dari batch
    ->join('masterlist as m', function($join) {
        $join->on('b.batchno', '=', 'm.activebatchno')
            ->on('b.companycode', '=', 'm.companycode');
    })
    ->where('b.companycode', $companyCode)
    ->where('b.isactive', 1)  // ✅ FILTER: Hanya batch aktif
    ->select(
        'b.plot',  // ✅ Plot dari batch
        'b.batcharea',  // ✅ Luas dari batch
        'b.lifecyclestatus',
        'b.batchdate',
        'b.tanggalpanen',
        'b.isactive',
        DB::raw('DATEDIFF(CURDATE(), b.batchdate) as umur_hari')
    )
    ->orderBy('b.plot')
    ->get();

// ✅ Activity map DAN grouping berdasarkan crop type
if ($cropType === 'rc') {
    $activityMap = [
        '3.2.1'  => 'Trash Mulcher',
        '3.2.2'  => 'Cultivating',
        '3.2.4'  => 'Single dress fertilizing',
        '3.2.5'  => 'Pre Emergence',
        '3.2.7'  => 'Cultivating II',
        '3.2.8'  => 'Hand Weeding I',
        '3.2.10' => 'Hand Weeding II',
        '3.2.9'  => 'Post Emergence I',
        '3.2.11' => 'Post Emergence II',
        '3.2.6'  => 'Late Pre Emergence',
    ];
} elseif ($cropType === 'p') {
    $activityMap = [
        '4.3.3'  => 'Pengangkutan (P.Manual)',
        '4.4.3'  => 'Pengangkutan (P.Semi)',
        '4.5.2'  => 'Pengangkutan (P.Mekanis)',
    ];
} else { // pc (default)
    $activityMap = [
        '2.1.5'  => 'Brushing',
        '2.1.3'  => 'Soil sampling',
        '2.1.6'  => 'Lime applicating',
        '2.1.7'  => 'Ploughing I',
        '2.1.8'  => 'Harrowing I',
        '2.1.9'  => 'Ploughing II',
        '2.1.10' => 'Harrowing II',
        '2.1.11' => 'Ridging & Basalt dressing',
        '2.2.4'  => 'Seed placing',
        '2.2.6'  => 'Fungicide applicating',
        '2.2.7'  => 'Covering',
        '2.3.2'  => 'Post-covering irrigating',
        '3.1.1'  => 'Pre Emergence',
        '3.1.4'  => 'Cultivating',
        '3.1.5'  => 'Top dress fertilizing',
        '3.1.6'  => 'Weeding I',
        '3.1.8'  => 'Weeding II',
        '3.1.7'  => 'Post Emergence I',
        '3.1.9'  => 'Post Emergence II',
        '3.1.2'  => 'Late Pre Emergence',
    ];
}
    //validasi
    $activityFilter = $request->get('activity', 'all');
    if ($activityFilter !== 'all' && !array_key_exists($activityFilter, $activityMap)) {
        $activityFilter = 'all';
    }
    
    // ✅ Activity yang perlu digabung (berlaku untuk semua crop type)
    $activityGrouping = [
        '2.1.11' => ['2.1.11a', '2.1.11b'],
        '2.2.7'  => ['2.2.7a', '2.2.7b'],
        '3.1.1'  => ['3.1.1a', '3.1.1b'],
        '3.1.2'  => ['3.1.2a', '3.1.2b'],
        '3.1.5'  => ['3.1.5a', '3.1.5b'],
        '3.2.4'  => ['3.2.4a', '3.2.4b'],
        '3.2.5'  => ['3.2.5a', '3.2.5b'],
        '3.2.6'  => ['3.2.6a', '3.2.6b'],
    ];

    // ✅ Buat daftar semua activity codes (termasuk yang dipecah)
    $allActivityCodes = [];
    foreach (array_keys($activityMap) as $mainCode) {
        if (isset($activityGrouping[$mainCode])) {
            $allActivityCodes = array_merge($allActivityCodes, $activityGrouping[$mainCode]);
        } else {
            $allActivityCodes[] = $mainCode;
        }
    }

// ✅ Query 1: Aggregate untuk total LUAS dan AVG PERCENTAGE
$activityDataRaw = DB::table('lkhdetailplot as ldp')
->join('lkhhdr as lh', 'ldp.lkhno', '=', 'lh.lkhno')
->join('masterlist as m', function($join) {
    $join->on('ldp.plot', '=', 'm.plot')
         ->on('ldp.companycode', '=', 'm.companycode')
         ;
})
->join('batch as b', function($join) {
    $join->on('m.activebatchno', '=', 'b.batchno')
         ->on('m.companycode', '=', 'b.companycode')
         ->where('b.isactive', '=', 1);  // ✅ FILTER: Batch harus aktif
})
->where('ldp.companycode', $companyCode)
->whereRaw('ldp.batchno = m.activebatchno')  // ✅ FILTER: Hanya LKH dari batch aktif
->whereIn('lh.activitycode', $allActivityCodes)
->select(
    'ldp.plot', 
    'lh.activitycode', 
    DB::raw('SUM(ldp.luashasil) as total_luas'),
    DB::raw('(SUM(ldp.luashasil) / MAX(b.batcharea)) * 100 as avg_percentage'),  // ✅ Pakai batcharea
    DB::raw('MAX(lh.lkhdate) as tanggal_terbaru')
)
->groupBy('ldp.plot', 'lh.activitycode')
->get();

// ✅ Query 2: Detail per LKH dengan persentase
$activityDetailRaw = DB::table('lkhdetailplot as ldp')
->join('lkhhdr as lh', 'ldp.lkhno', '=', 'lh.lkhno')
->join('masterlist as m', function($join) {
    $join->on('ldp.plot', '=', 'm.plot')
         ->on('ldp.companycode', '=', 'm.companycode')
         ;
})
->join('batch as b', function($join) {
    $join->on('m.activebatchno', '=', 'b.batchno')
         ->on('m.companycode', '=', 'b.companycode')
         ->where('b.isactive', '=', 1);  // ✅ FILTER: Batch harus aktif
})
->where('ldp.companycode', $companyCode)
->whereRaw('ldp.batchno = m.activebatchno')  // ✅ FILTER: Hanya LKH dari batch aktif
->whereIn('lh.activitycode', $allActivityCodes)
->select(
    'ldp.plot', 
    'lh.activitycode',
    'lh.lkhno',
    'ldp.luashasil',
    'lh.lkhdate',
    DB::raw('(ldp.luashasil / b.batcharea) * 100 as percentage')  // ✅ Pakai batcharea
)
->orderBy('ldp.plot')
->orderBy('lh.activitycode')
->orderBy('lh.lkhdate', 'desc')
->get();

// ✅ Group detail by plot & activity dengan persentase
$lkhDetails = [];
foreach ($activityDetailRaw as $detail) {
$lkhDetails[$detail->plot][$detail->activitycode][] = [
    'lkhno' => $detail->lkhno,
    'luas_hasil' => (float)$detail->luashasil,
    'tanggal' => $detail->lkhdate,
    'percentage' => (float)$detail->percentage
];
}

    // ✅ Gabungkan activity yang dipecah
    $activityData = collect();
    
    foreach ($plotHeaders as $plot) {
        $plotActivities = collect();
        
        foreach (array_keys($activityMap) as $mainCode) {
            if (isset($activityGrouping[$mainCode])) {
                $subCodes = $activityGrouping[$mainCode];
                
                $combinedLuas = 0;
                $combinedPercentage = 0;
                $subCount = 0;
                $latestDate = null;
                
                foreach ($subCodes as $subCode) {
                    $subActivity = $activityDataRaw->first(function($item) use ($plot, $subCode) {
                        return $item->plot === $plot->plot && $item->activitycode === $subCode;
                    });
                    
                    if ($subActivity) {
                        $combinedLuas += $subActivity->total_luas;
                        $combinedPercentage += $subActivity->avg_percentage;
                        $subCount++;
                        
                        if ($subActivity->tanggal_terbaru) {
                            if (!$latestDate || $subActivity->tanggal_terbaru > $latestDate) {
                                $latestDate = $subActivity->tanggal_terbaru;
                            }
                        }
                    }
                }
                
                if ($combinedLuas > 0) {
                    $plotActivities->put($mainCode, (object)[
                        'activitycode' => $mainCode,
                        'total_luas' => $combinedLuas,
                        'avg_percentage' => $subCount > 0 ? $combinedPercentage / $subCount : 0,
                        'tanggal_terbaru' => $latestDate
                    ]);
                }
            } else {
                $activity = $activityDataRaw->first(function($item) use ($plot, $mainCode) {
                    return $item->plot === $plot->plot && $item->activitycode === $mainCode;
                });
                
                if ($activity) {
                    $plotActivities->put($mainCode, $activity);
                }
            }
        }
        
        if ($plotActivities->isNotEmpty()) {
            $activityData->put($plot->plot, $plotActivities);
        }
    }

    if ( $activityFilter !== 'all' ) {
        $plotHeaders = $plotHeaders->filter(function($plot) use ($activityData, $activityFilter) {
            return $activityData->has($plot->plot) && 
                   $activityData->get($plot->plot)->has($activityFilter);
        });
    }



    //export
$isExport = $request->has('export') && $request->get('export') === 'excel';

// ✅ CEK EXPORT - Taruh di AKHIR setelah semua data siap
if ($isExport) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // ========== HEADER ROW ==========
    $col = 'A';
    $sheet->setCellValue($col++ . '1', 'Blok');
    $sheet->setCellValue($col++ . '1', 'Plot');
    $sheet->setCellValue($col++ . '1', 'Saldo (HA)');
    
    foreach ($activityMap as $code => $label) {
        $sheet->setCellValue($col++ . '1', "$code - $label (HA)");
        $sheet->setCellValue($col++ . '1', "$code (%)");
        $sheet->setCellValue($col++ . '1', "$code (Tanggal)");
    }
    
    $sheet->setCellValue($col++ . '1', 'Realisasi Tanam (HA)');
    $sheet->setCellValue($col++ . '1', 'Persentase (%)');

    // ========== STYLE HEADER ==========
    $lastCol = chr(ord($col) - 1); // Kolom terakhir
    $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
    $sheet->getStyle("A1:{$lastCol}1")->getFill()
        ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
        ->getStartColor()->setRGB('166534');
    $sheet->getStyle("A1:{$lastCol}1")->getFont()->getColor()->setRGB('FFFFFF');
    $sheet->freezePane('A2');

    // ========== DATA ROWS ==========
    $row = 2;
    $blokPlots = $plotHeaders->groupBy(fn($item) => substr($item->plot, 0, 1));
    
    foreach ($blokPlots as $blok => $plots) {
        foreach ($plots as $index => $plot) {
            $col = 'A';
            
            // Blok (hanya di row pertama per blok)
            if ($index === 0) {
                $sheet->setCellValue($col . $row, $blok);
            }
            $col++;
            
            // Plot
            $sheet->setCellValue($col++ . $row, $plot->plot);
            
            // Saldo
            $sheet->setCellValue($col++ . $row, $plot->batcharea ? number_format($plot->batcharea, 2) : '-');
            
            $totalRealisasiPlot = 0;
            
            // Loop activity
            foreach ($activityMap as $activitycode => $label) {
                $activity = $activityData->get($plot->plot)?->get($activitycode);
                $value = $activity->total_luas ?? 0;
                $percentage = $activity->avg_percentage ?? 0;
                $tanggal = $activity->tanggal_terbaru ?? null;
                $totalRealisasiPlot += $value;
                
                // HA
                $sheet->setCellValue($col++ . $row, $value > 0 ? number_format($value, 2) : '-');
                
                // %
                $sheet->setCellValue($col++ . $row, $value > 0 ? number_format($percentage, 2) : '-');
                
                // Tanggal
                $sheet->setCellValue($col++ . $row, $tanggal ? \Carbon\Carbon::parse($tanggal)->format('d M y') : '-');
            }
            
            // Realisasi Tanam
            $sheet->setCellValue($col++ . $row, $totalRealisasiPlot > 0 ? number_format($totalRealisasiPlot, 2) : '-');
            
            // Avg Percentage
            $totalPercentage = 0;
            $activityCount = 0;
            
            foreach ($activityMap as $activitycode => $label) {
                $activity = $activityData->get($plot->plot)?->get($activitycode);
                if ($activity) {
                    $totalPercentage += $activity->avg_percentage ?? 0;
                    $activityCount++;
                }
            }
            
            $avgPersen = $activityCount > 0 ? $totalPercentage / $activityCount : 0;
            $sheet->setCellValue($col++ . $row, number_format($avgPersen, 2));
            
            $row++;
        }
    }

    // ========== AUTO SIZE COLUMNS ==========
    foreach (range('A', $lastCol) as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // ========== DOWNLOAD ==========
    $filename = "timeline_{$cropType}_" . now()->format('Ymd_His') . ".xlsx";
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    return response()->stream(
        function () use ($writer) {
            $writer->save('php://output');
        },
        200,
        [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]
    );
}
//export end


    $filteredPlots = $plotHeaders->pluck('plot')->toArray();

    // ✅ Query map data (tetap sama)
    $plotDataForMap = DB::table('testgpslst as a')
        ->leftJoin('testgpshdr as d', 'a.plot', '=', 'd.plot')
        ->where('a.companycode', $companyCode)
        ->whereIn('a.plot', $filteredPlots)
        ->select('a.plot', 'a.latitude', 'a.longitude', 'd.centerlatitude', 'd.centerlongitude')
        ->get();
    
    // ✅ GABUNG: Process plotHeadersForMap + plotActivityDetails sekaligus
    $plotHeadersForMap = [];
    $plotActivityDetails = [];
    
    foreach ($filteredPlots as $plotCode) {
        // Ambil center coordinates dari plotDataForMap
        $centerData = $plotDataForMap->firstWhere('plot', $plotCode);
        
        if ($centerData) {
            // Data untuk map markers
            $plotHeadersForMap[] = (object)[
                'plot' => $plotCode,
                'centerlatitude' => $centerData->centerlatitude,
                'centerlongitude' => $centerData->centerlongitude
            ];
        }
        
        // Data untuk activity details
$plotInfo = $plotHeaders->firstWhere('plot', $plotCode);
$activities = $activityData->get($plotCode);
$luasRkh = $plotInfo->batcharea ?? 0;  // ✅ GANTI: Pakai batcharea (bukan luasarea)

// ✅ Pindahkan ke luar if untuk efisiensi
$lifecycleStatus = $plotInfo->lifecyclestatus ?? '-';
$umurHari = $plotInfo->umur_hari ?? 0;

$hasPanen  = !empty($plotInfo->tanggalpanen);
$lastPanen = $plotInfo->tanggalpanen ?? null;

if ($activities && $luasRkh > 0) {
    $activityList = [];
    $totalPercentage = 0;
    $totalLuasHasil = 0;
    $activityCount = 0;
    $allComplete = true;
    $hasActivity = false;
    
    foreach ($activities as $actCode => $act) {
        $luasHasil = $act->total_luas ?? 0;
        $percentage = $act->avg_percentage ?? 0;
        
        $activityList[] = [
            'code' => $actCode,
            'label' => $activityMap[$actCode] ?? $actCode,
            'luas_hasil' => $luasHasil,
            'percentage' => $percentage,
            'tanggal' => $act->tanggal_terbaru ?? null,
            'lkh_details' => $lkhDetails[$plotCode][$actCode] ?? []
        ];
        
        $totalPercentage += $percentage;
        $totalLuasHasil += $luasHasil;
        $activityCount++;
        $hasActivity = true;
        
        if ($percentage < 100) {
            $allComplete = false;
        }
    }
    
    $avgPercentage = $activityCount > 0 ? ($totalPercentage / $activityCount) : 0;
    
    if (!$hasActivity || $avgPercentage == 0) {
        $markerColor = 'black';
    } elseif ($allComplete) {
        $markerColor = 'green';
    } else {
        $markerColor = 'orange';
    }
    
    $plotActivityDetails[$plotCode] = [
        'activities' => $activityList,
        'avg_percentage' => $avgPercentage,
        'marker_color' => $markerColor,
        'luas_rkh' => $luasRkh,
        'total_luas_hasil' => $totalLuasHasil,
        'lifecyclestatus' => $lifecycleStatus,  
        'umur_hari' => $umurHari,                
        'is_panen'                 => $hasPanen ? 1 : 0,          
        'tanggal_panen_terakhir'   => $lastPanen
    ];
} else {
    $plotActivityDetails[$plotCode] = [
        'activities' => [],
        'avg_percentage' => 0,
        'marker_color' => 'black',
        'luas_rkh' => $luasRkh,
        'total_luas_hasil' => 0,
        'lifecyclestatus' => $lifecycleStatus,  
        'umur_hari' => $umurHari,               
        'is_panen'                 => $hasPanen ? 1 : 0,          
        'tanggal_panen_terakhir'   => $lastPanen
    ];
}
}

    // Convert array ke collection untuk consistency
    $plotHeadersForMap = collect($plotHeadersForMap);
        
        return view('dashboard.timeline-plot.index', [
            'title'             => 'Timeline',
            'nav'               => 'Timeline',
            'navbar'            => 'Timeline',
            'plotHeaders'       => $plotHeaders,
            'plotHeadersForMap' => $plotHeadersForMap,
            'activityMap'       => $activityMap,
            'activityData'      => $activityData,
            'activityGrouping'  => $activityGrouping,
            'activityFilter'    => $activityFilter,
            'plotData'          => $plotDataForMap,
            'plotActivityDetails'=> $plotActivityDetails,
            'fillFilter'        => $fillFilter,
            'cropType'          => $cropType,
        ]);
    }






}
