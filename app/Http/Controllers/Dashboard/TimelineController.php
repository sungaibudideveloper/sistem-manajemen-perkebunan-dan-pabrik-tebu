<?php
namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

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
    $title = "Dashboard Timeline";
    $nav = "Timeline";
    
    $rkhno = $request->rkhno ?? 'RKH21050234';
    
    // Get data from usematerialhdr
    $usematerialhdr = new usematerialhdr;
    $details = $usematerialhdr->selectuse(session('companycode'), $rkhno, 1)->get();
    $detailsPlots = Arr::pluck($details, 'plot');
    
    // Get GPS and plot data
    $plotData = DB::table('testgpslst as a')
        ->leftJoin('plot as b', 'a.plot', '=', 'b.plot')
        ->leftJoin('masterlist as c', 'b.plot', '=', 'c.plot')
        ->leftJoin('testgpshdr as d', 'a.plot', '=', 'd.plot')
        ->where('a.companycode', session('companycode'))
        ->whereIn('a.plot', $detailsPlots)
        ->select(
            'a.companycode', 'a.plot', 'a.latitude', 'a.longitude',
            'd.centerlatitude', 'd.centerlongitude',
            'c.batchno', 'c.batchdate', 'c.batcharea', 'c.tanggalulangtahun',
            'c.kodevarietas', 'c.kodestatus', 'c.jaraktanam', 'c.isactive',
            'b.luasarea', 'b.jaraktanam as plot_jaraktanam', 'b.status'
        )
        ->get();
    
    // Get unique plot headers (center coordinates)
    $plotHeaders = $plotData->map(function($item) {
        return (object)[
            'companycode' => $item->companycode,
            'plot' => $item->plot,
            'centerlatitude' => $item->centerlatitude,
            'centerlongitude' => $item->centerlongitude
        ];
    })->unique('plot')->values();
    
    // Group activities by PC/RC (dari details)
    $activities = $details->groupBy(function($item) {
        // Asumsi: ada field yang menandakan PC/RC, misal 'activity_type' atau 'kategori'
        // Sesuaikan dengan struktur data Anda
        return $item->activity_type ?? 'PC'; // default PC jika tidak ada
    });
    dd('a', $plotHeaders);
    return view('dashboard.timeline-plot.index', [
        'title' => $title,
        'nav' => $nav,
        'navbar' => 'Timeline',
        'rkhno' => $rkhno,
        'plotHeaders' => $plotHeaders,
        'plotData' => $plotData,
        'activities' => $activities,
        'details' => $details
    ]);
}


}
