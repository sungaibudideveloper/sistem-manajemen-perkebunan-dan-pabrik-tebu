<?php
namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Arr;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

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
      $list = DB::table('testgpslst')->where('companycode', session('companycode'))->whereIn('plot', Arr::pluck($header, 'plot'))->get();
      return view('dashboard\maps\maps')->with([
        'title' => $title,
        'nav'   => $nav,
        'header' => $header,
        'list' => $list
      ]);
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
