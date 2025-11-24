<?php
namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Arr;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\usematerialhdr;
use App\Models\usemateriallst;

class MapsController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Dashboard',
        ]);

    }
    public function index()
    {
      $title = "Dashboard Agronomi";
      $nav = "Agronomi";
      $header = DB::table('testgpshdr')->where('companycode', session('companycode'))->orderBy('plot')->get();
      //old
      //$list1 = DB::table('testgpslst')->where('companycode', session('companycode'))->whereIn('plot', Arr::pluck($header, 'plot'))->get();
      //new
      $list = DB::table('testgpslst as a')
      ->leftJoin('plot as b', function($join) {
          $join->on('a.plot', '=', 'b.plot')
               ->on('a.companycode', '=', 'b.companycode');
      })
      ->leftJoin('masterlist as c', function($join) {
          $join->on('b.plot', '=', 'c.plot')
               ->on('b.companycode', '=', 'c.companycode');
      })
      ->where('a.companycode', session('companycode'))
      ->whereIn('a.plot', Arr::pluck($header, 'plot'))
      ->select('a.plot', 'a.latitude', 'a.longitude', 'c.batchno', 'c.batchdate', 'c.batcharea', 'c.tanggalulangtahun', 'c.kodevarietas', 'c.kodestatus', 'c.jaraktanam', 'c.isactive', 'b.luasarea', 'b.jaraktanam as plot_jaraktanam', 'b.status')
      ->get();

      // $plotKodeStatus = collect($list)
      // ->whereNotNull('kodestatus')
      // ->where('kodestatus', '!=', '')
      // ->groupBy('plot')
      // ->map(function($items) {
      //     return $items->first()->kodestatus;
      // });
      
      //  dd($list, $header);
      return view('dashboard\maps\mapsfilter')->with([
        'title' => $title,
        'nav'   => $nav,
        'header' => $header,
        // 'plotKodeStatus' => $plotKodeStatus,
        'list' => $list
      ]);
    }

    public function indexapi(Request $request) 
    {
        $usematerialhdr = new usematerialhdr;
        $title = "Dashboard Agronomi";
        $nav = "Agronomi";
        
        $rkhno = $request->rkhno ?? 'RKH21050234';
        $details = $usematerialhdr->selectuse(session('companycode'), $rkhno, 1)->get();
        $detailsPlots = Arr::pluck($details, 'plot');
        
        $list = DB::table('testgpslst as a')
            ->leftJoin('plot as b', function($join) {
                $join->on('a.plot', '=', 'b.plot')
                     ->on('a.companycode', '=', 'b.companycode');
            })
            ->leftJoin('masterlist as c', function($join) {
                $join->on('b.plot', '=', 'c.plot')
                     ->on('b.companycode', '=', 'c.companycode');
            })
            ->leftJoin('testgpshdr as d', function($join) {
                $join->on('a.plot', '=', 'd.plot')
                     ->on('a.companycode', '=', 'd.companycode');
            })
            ->where('a.companycode', session('companycode'))
            ->whereIn('a.plot', $detailsPlots)
            ->select('a.companycode', 'a.plot', 'a.latitude', 'a.longitude', 'd.centerlatitude', 'd.centerlongitude', 'c.batchno', 'c.batchdate', 'c.batcharea', 'c.tanggalulangtahun', 'c.kodevarietas', 'c.kodestatus', 'c.jaraktanam', 'c.isactive', 'b.luasarea', 'b.jaraktanam as plot_jaraktanam', 'b.status')
            ->get();
        
        $header = $list->map(function($item) {
            return (object)[
                'companycode' => $item->companycode,
                'plot' => $item->plot,
                'centerlatitude' => $item->centerlatitude,
                'centerlongitude' => $item->centerlongitude
            ];
        })->unique('plot')->values();
        
        $data = [
            'title' => $title,
            'nav' => $nav,
            'header' => $header,
            'list' => $list,
            'rkhno' => $rkhno
        ];
        
        // Always return the HTML view (whether accessed directly or via API)
        return view('dashboard.maps.index', $data);
    }

    public function callmapsapi(Request $request) 
    {
        $rkhno = $request->rkhno ?? 'RKH21050234';
        
        try {
            // Generate the URL with the rkhno parameter
            $mapUrl = env('MAPS_API_URL', 'http://localhost/tebu/public/dashboard/mapsapi') . '?rkhno=' . $rkhno;
            
            return response()->json([
                'success' => true,
                'message' => 'Map URL generated successfully',
                'url' => $mapUrl,
                'rkhno' => $rkhno
            ], 200, [], JSON_UNESCAPED_SLASHES);
            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating map URL',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    public function upload(Request $request)
    {

      $gpxFile = simplexml_load_file($request->file('gpxFile')->getRealPath());
      $namespaces = $gpxFile->getNamespaces(true);
      $gpxFile->registerXPathNamespace('ogr', $namespaces['ogr']);

      foreach ($gpxFile->rte as $rte) {
          $extensions = $rte->extensions;

          // Ambil divisi dan plot
          $divisi = (string)$extensions->children($namespaces['ogr'])->DIVISI;
          $plot_baru = (string)$extensions->children($namespaces['ogr'])->PLOT_BARU;

          $latitudes = [];
          $longitudes = [];

          foreach ($rte->rtept as $rtept) {
              $lat = (float)$rtept['lat'];
              $lon = (float)$rtept['lon'];

              $latitudes[] = $lat;
              $longitudes[] = $lon;

              // Insert detail
              DB::table('testgpslst')->insert([
                  'companycode' => session('companycode'),
                  'plot' => $plot_baru,
                  'latitude' => $lat,
                  'longitude' => $lon
              ]);
          }

          // Hitung centroid
          $center_latitude = array_sum($latitudes) / count($latitudes);
          $center_longitude = array_sum($longitudes) / count($longitudes);

          // Insert header
          DB::table('testgpshdr')->insert([
              'companycode' => session('companycode'),
              'plot' => $plot_baru,
              'centerlatitude' => $center_latitude,
              'centerlongitude' => $center_longitude
          ]);
      }

      return redirect()->back()->with('success1', 'Data Imported Successfully');
    }

}
