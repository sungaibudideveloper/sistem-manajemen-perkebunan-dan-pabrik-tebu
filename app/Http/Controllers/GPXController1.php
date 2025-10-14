<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GPXController extends Controller
{
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
                  'companycode' => $divisi,
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
              'companycode' => $divisi,
              'plot' => $plot_baru,
              'centerlatitude' => $center_latitude,
              'centerlongitude' => $center_longitude
          ]);
      }

      return redirect()->back()->with('success1', 'Data Imported Successfully');
    }

    public function export(Request $request)
    {
        if ($request->isMethod('post')) {
            $results = DB::table('gps_lst')
                ->select('fid', 'lon', 'lat')
                ->where('companycode', session('companycode'))
                ->orderBy('fid')
                ->get();

            if ($results->isEmpty()) {
                return response()->json(['message' => 'No data found in the database.'], 404);
            }

            $kmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $kmlContent .= "<kml xmlns=\"http://www.opengis.net/kml/2.2\">\n";
            $kmlContent .= "  <Document>\n";

            $currentFid = null;
            $coordinates = [];
            $currentCaption = '';

            foreach ($results as $row) {
                $fid = $row->fid;
                $latitude = $row->lat;
                $longitude = $row->lon;
                $caption = $row->fid;

                if ($fid !== $currentFid) {
                    if ($currentFid !== null) {
                        $kmlContent .= $this->generatePlacemark($currentCaption, $coordinates);
                        $kmlContent .= $this->generatePolygon($currentCaption, $coordinates);
                        $kmlContent .= $this->generatePoint($currentCaption, $coordinates);
                    }

                    $currentFid = $fid;
                    $currentCaption = $caption;
                    $coordinates = [];
                }

                $coordinates[] = "$longitude,$latitude";
            }

            if ($currentFid !== null) {
                $kmlContent .= $this->generatePlacemark($currentCaption, $coordinates);
                $kmlContent .= $this->generatePolygon($currentCaption, $coordinates);
                $kmlContent .= $this->generatePoint($currentCaption, $coordinates);
            }

            $kmlContent .= "  </Document>\n";
            $kmlContent .= "</kml>\n";

            $filePath = 'kml_files/output.kml';
            Storage::disk('local')->put($filePath, $kmlContent);

            return response()->download(storage_path("app/private/" . $filePath));
        }

        return response()->json(['message' => 'Invalid request method.'], 405);
    }

    private function generatePlacemark($caption, $coordinates)
    {
        $kml = "    <Placemark>\n";
        $kml .= "      <name>" . htmlspecialchars($caption) . "</name>\n";
        $kml .= "      <Style>\n";
        $kml .= "        <LineStyle>\n";
        $kml .= "          <color>ff0000ff</color>\n";
        $kml .= "          <width>2</width>\n";
        $kml .= "        </LineStyle>\n";
        $kml .= "      </Style>\n";
        $kml .= "      <LineString>\n";
        $kml .= "        <coordinates>\n" . implode("\n", $coordinates) . "\n        </coordinates>\n";
        $kml .= "      </LineString>\n";
        $kml .= "    </Placemark>\n";

        return $kml;
    }

    private function generatePolygon($caption, $coordinates)
    {
        $kml = "    <Placemark>\n";
        $kml .= "      <name>" . htmlspecialchars($caption) . "</name>\n";
        $kml .= "      <Style>\n";
        $kml .= "        <PolyStyle>\n";
        $kml .= "          <color>7f00ff00</color>\n";
        $kml .= "        </PolyStyle>\n";
        $kml .= "      </Style>\n";
        $kml .= "      <Polygon>\n";
        $kml .= "        <outerBoundaryIs>\n";
        $kml .= "          <LinearRing>\n";
        $kml .= "            <coordinates>\n" . implode("\n", $coordinates) . "\n            </coordinates>\n";
        $kml .= "          </LinearRing>\n";
        $kml .= "        </outerBoundaryIs>\n";
        $kml .= "      </Polygon>\n";
        $kml .= "    </Placemark>\n";

        return $kml;
    }

    private function generatePoint($caption, $coordinates)
    {
        $centroid = $this->calculateCentroid($coordinates);
        $kml = "    <Placemark>\n";
        $kml .= "      <name>" . htmlspecialchars($caption) . "</name>\n";
        $kml .= "      <Point>\n";
        $kml .= "        <coordinates>{$centroid['lon']},{$centroid['lat']}</coordinates>\n";
        $kml .= "      </Point>\n";
        $kml .= "    </Placemark>\n";

        return $kml;
    }

    private function calculateCentroid($coordinates)
    {
        $numCoords = count($coordinates);
        $x = 0;
        $y = 0;

        foreach ($coordinates as $coord) {
            list($lon, $lat) = explode(',', $coord);
            $x += $lon;
            $y += $lat;
        }

        return ['lon' => $x / $numCoords, 'lat' => $y / $numCoords];
    }
}
