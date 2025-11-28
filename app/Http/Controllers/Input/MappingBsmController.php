<?php

namespace App\Http\Controllers\Input;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\Timbangan;

class MappingBsmController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Input',
        ]);
    }


    public function index(Request $request)
    {

        $title = "Mapping BSM";
        $nav = "Panen Tebu";

        $kontraktor = DB::table('kontraktor')->where('companycode', session('companycode'))->get();
        $tabel_harga = DB::table('hargapanentebu')->where('companycode', session('companycode'))->get();
        // dd($tabel_harga);

        return view('report.panen-tebu.index', compact('title', 'nav', 'kontraktor', 'tabel_harga'));
    }

    public function proses(Request $request){
        // dd(round(2.538, 0, PHP_ROUND_HALF_UP));
        // dd($request);
        $companycode = session('companycode');
        // dd($request);
        $timbangan = new Timbangan;


        $data = $timbangan->getData($companycode, $request->idkontraktor, $request->start_date, $request->end_date);
        $tabel_harga = DB::table('hargapanentebu')->where('companycode', session('companycode'))->where('kodeharga', $request->kode_harga)->get();
        // dd($data);

        $viewData = [
                'data' => collect($data),
                'kontraktor' => $request->idkontraktor,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,
                'tabelharga' => collect($tabel_harga)
            ];

         return view('report.panen-tebu.result', $viewData);
    }


}
