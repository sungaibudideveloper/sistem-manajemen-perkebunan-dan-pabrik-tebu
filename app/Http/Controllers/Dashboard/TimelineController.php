<?php
namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\usematerialhdr;
use App\Models\Batch;
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

    // Header plot
    $plotHeaders = DB::table('plot as p')
        ->where('p.companycode', $companyCode)
        ->select('p.plot', 'p.luasarea')
        ->orderBy('p.plot')
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

    // Agregasi hasil per plot x activitycode dengan tanggal terbaru
    $activityDataRaw = DB::table('lkhdetailplot as ldp')
        ->join('lkhhdr as lh', 'ldp.lkhno', '=', 'lh.lkhno')
        ->where('ldp.companycode', $companyCode)
        ->whereIn('lh.activitycode', $allActivityCodes)
        ->select(
            'ldp.plot', 
            'lh.activitycode', 
            DB::raw('SUM(ldp.luashasil) as total_luas'),
            DB::raw('MAX(lh.lkhdate) as tanggal_terbaru')
        )
        ->groupBy('ldp.plot', 'lh.activitycode')
        ->get();

    // ✅ Gabungkan activity yang dipecah
    $activityData = collect();
    
    foreach ($plotHeaders as $plot) {
        $plotActivities = collect();
        
        foreach (array_keys($activityMap) as $mainCode) {
            if (isset($activityGrouping[$mainCode])) {
                $subCodes = $activityGrouping[$mainCode];
                
                $combinedLuas = 0;
                $latestDate = null;
                
                foreach ($subCodes as $subCode) {
                    $subActivity = $activityDataRaw->first(function($item) use ($plot, $subCode) {
                        return $item->plot === $plot->plot && $item->activitycode === $subCode;
                    });
                    
                    if ($subActivity) {
                        $combinedLuas += $subActivity->total_luas;
                        
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

    // Filter filled/empty
    if ($fillFilter === 'filled') {
        $plotHeaders = $plotHeaders->filter(fn($p) => $activityData->has($p->plot));
    } elseif ($fillFilter === 'empty') {
        $plotHeaders = $plotHeaders->filter(fn($p) => !$activityData->has($p->plot));
    }

    // Map data
    $plotDataForMap = DB::table('testgpslst as a')
        ->leftJoin('testgpshdr as d', 'a.plot', '=', 'd.plot')
        ->where('a.companycode', $companyCode)
        ->select('a.plot', 'a.latitude', 'a.longitude', 'd.centerlatitude', 'd.centerlongitude')
        ->get();

    $plotHeadersForMap = $plotDataForMap
        ->map(fn($it) => (object)[
            'plot' => $it->plot,
            'centerlatitude' => $it->centerlatitude,
            'centerlongitude' => $it->centerlongitude
        ])
        ->unique('plot')
        ->values();

    return view('dashboard.timeline-plot.index', [
        'title'             => 'Timeline',
        'nav'               => 'Timeline',
        'navbar'            => 'Timeline',
        'plotHeaders'       => $plotHeaders,
        'plotHeadersForMap' => $plotHeadersForMap,
        'activityMap'       => $activityMap,
        'activityData'      => $activityData,
        'activityGrouping'  => $activityGrouping,
        'plotData'          => $plotDataForMap,
        'fillFilter'        => $fillFilter,
        'cropType'          => $cropType,
    ]);
}






}
