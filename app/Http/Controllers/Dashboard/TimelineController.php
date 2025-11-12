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
    
        // Activity map berdasarkan crop type
        if ($cropType === 'rc') {
            $activityMap = [
                '3.2.1'  => 'Trash Mulcher',
                '3.2.2'  => 'Cultivating',
                '3.2.4'  => 'Single dress fertilizing',
                '3.2.5'  => 'Pre Emergence',
                '3.2.6'  => 'Cultivating II',
                '3.2.7'  => 'Hand Weeding I',
                '3.2.9'  => 'Hand Weeding II',
                '3.2.8'  => 'Post Emergence I',
                '3.2.10'  => 'Post Emergence II',

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
    
        $activityCodes = array_keys($activityMap);
    
        // Agregasi hasil per plot x activitycode dengan tanggal terbaru
        $activityData = DB::table('lkhdetailplot as ldp')
        ->join('lkhhdr as lh', 'ldp.lkhno', '=', 'lh.lkhno')
        ->where('ldp.companycode', $companyCode)
        ->whereIn('lh.activitycode', $activityCodes)
        ->select(
            'ldp.plot', 
            'lh.activitycode', 
            DB::raw('SUM(ldp.luashasil) as total_luas'),
            DB::raw('MAX(lh.lkhdate) as tanggal_terbaru')
        )
        ->groupBy('ldp.plot', 'lh.activitycode')
        ->get()
        ->groupBy('plot')
        ->map(fn($items) => $items->keyBy('activitycode'));

            
    
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
            'title'             => 'Dashboard Timeline',
            'nav'               => 'Timeline',
            'navbar'            => 'Timeline',
            'plotHeaders'       => $plotHeaders,
            'plotHeadersForMap' => $plotHeadersForMap,
            'activityMap'       => $activityMap,
            'activityData'      => $activityData,
            'plotData'          => $plotDataForMap,
            'fillFilter'        => $fillFilter,
            'cropType'          => $cropType,
        ]);
    }






}
