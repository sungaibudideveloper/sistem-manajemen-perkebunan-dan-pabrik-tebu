<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class GPXController extends Controller
{
    public function upload(Request $request)
    {
        if (!$request->hasFile('gpxFile') || !$request->file('gpxFile')->isValid()) {
            return response()->json(['message' => 'Error: File upload failed.'], 400);
        }

        $file = $request->file('gpxFile');
        $allowedTypes = ['application/gpx+xml', 'text/xml'];
        $maxFileSize = 20 * 1024 * 1024; // 20MB

        if (!in_array($file->getMimeType(), $allowedTypes) && $file->getClientOriginalExtension() !== 'gpx') {
            return response()->json(['message' => 'Error: Invalid file type. Only GPX files are allowed.'], 400);
        }

        if ($file->getSize() > $maxFileSize) {
            return response()->json(['message' => 'Error: File size exceeds the maximum limit of 20MB.'], 400);
        }

        $file->move(public_path('uploads'), $file->getClientOriginalName());
        $xmlFile = public_path('uploads/' . $file->getClientOriginalName());


        if (!file_exists($xmlFile)) {
            return response()->json(['message' => 'The XML file does not exist.'], 400);
        }

        $xmlContent = simplexml_load_file($xmlFile);
        if ($xmlContent === false) {
            return response()->json(['message' => 'Failed to load XML file.'], 400);
        }

        $xmlContent->registerXPathNamespace('ogr', 'http://osgeo.org/gdal');
        $xmlContent->registerXPathNamespace('gpx', 'http://www.topografix.com/GPX/1/1');

        function safeGet($value)
        {
            return isset($value) ? htmlspecialchars($value) : 'N/A';
        }

        DB::beginTransaction();
        try {
            $hdrData = [];
            $lstData = [];

            foreach ($xmlContent->trk as $track) {
                $extensions = $track->extensions;
                $fid = safeGet($extensions->children('ogr', true)->fid);           

                $exists = DB::table('gps_hdr')->where('fid', $fid)->where('kd_comp', session('dropdown_value'))->exists();
                // dd($exists);
                if (!$exists) {
                    $hdrData[] = [
                        'fid' => $fid,
                        'petak' => safeGet($extensions->children('ogr', true)->PETAK),
                        'divisi' => safeGet($extensions->children('ogr', true)->DIVISI),
                        'blok' => safeGet($extensions->children('ogr', true)->BLOK),
                        'luas' => safeGet($extensions->children('ogr', true)->LUAS),
                        'shape_leng' => safeGet($extensions->children('ogr', true)->Shape_Leng),
                        'shape_area' => safeGet($extensions->children('ogr', true)->Shape_Area),
                        'kd_comp' => session('dropdown_value'),
                    ];

                    foreach ($track->trkseg->trkpt as $trkpt) {
                        $lstData[] = [
                            'fid' => $fid,
                            'lon' => safeGet($trkpt['lon']),
                            'lat' => safeGet($trkpt['lat']),
                            'kd_comp' => session('dropdown_value'),
                        ];
                    }
                }
            }

            if (!empty($hdrData)) {
                DB::table('gps_hdr')->insert($hdrData);
            }
            if (!empty($lstData)) {
                $chunks = array_chunk($lstData, 1000);
                foreach ($chunks as $chunk) {
                    DB::table('gps_lst')->insert($chunk);
                }
            }

            DB::commit();
            return redirect()->back()->with('success1', 'Data Imported Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        if ($request->isMethod('post')) {
            $results = DB::table('gps_lst')
                ->select('fid', 'lon', 'lat')
                ->where('kd_comp', session('dropdown_value'))
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
