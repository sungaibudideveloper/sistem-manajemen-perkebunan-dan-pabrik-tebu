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

   
    // Controller
public function plot(Request $request)
{   
    $companyCode = session('companycode');
    
    // 1. Get plot headers (unique plots)
    $plotHeaders = DB::table('testgpslst as a')
        ->leftJoin('testgpshdr as d', 'a.plot', '=', 'd.plot')
        ->where('a.companycode', $companyCode)
        ->select('a.plot', 'd.centerlatitude', 'd.centerlongitude')
        ->distinct()
        ->orderBy('a.plot')
        ->get();
    
    // 2. Get activities untuk header kolom
    $activities = DB::table('activity')
        ->whereIn('activityname2', [
            'Saldo', 'Brushing', 'Plough I', 'Marrow', 
            'Plough II', 'Harrow', 'Ridger', 'Basalt', 
            'Selisih', 'Replanting', 'Pre Dressing', 
            'Spring', 'Multi', 'Top'
        ])
        ->select('activitycode', 'activityname2')
        ->get()
        ->keyBy('activityname2');
    
    // 3. Get activity data per plot (SUM luashasil)
    $activityData = DB::table('lkhdetailplot as ldp')
        ->join('lkhhdr as lh', 'ldp.lkhno', '=', 'lh.lkhno')
        ->join('activity as act', 'lh.activitycode', '=', 'act.activitycode')
        ->whereIn('ldp.plot', $plotHeaders->pluck('plot'))
        ->select(
            'ldp.plot',
            'act.activityname2',
            DB::raw('SUM(ldp.luashasil) as total_luas')
        )
        ->groupBy('ldp.plot', 'act.activityname2')
        ->get()
        ->groupBy('plot')
        ->map(function($items) {
            return $items->keyBy('activityname2');
        });
    
    // 4. Get plot data untuk map
    $plotData = DB::table('testgpslst as a')
        ->leftJoin('plot as b', 'a.plot', '=', 'b.plot')
        ->leftJoin('batch as c', 'b.plot', '=', 'c.plot')
        ->where('a.companycode', $companyCode)
        ->select('a.plot', 'a.latitude', 'a.longitude')
        ->get();
    
    dd($activities,$activityData);

    return view('dashboard.timeline-plot.index', [
        'title' => 'Dashboard Timeline',
        'nav' => 'Timeline',
        'navbar' => 'Timeline',
        'plotHeaders' => $plotHeaders,
        'activities' => $activities,
        'activityData' => $activityData,
        'plotData' => $plotData
    ]);
}


}
