<?php

namespace App\Http\Controllers\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\Timbangan;

class PanenTebuController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }


    public function index(Request $request)
    {

        $title = "Report Panen Tebu";
        $nav = "Panen Tebu";

        $kontraktor = DB::table('kontraktor')->where('companycode', session('companycode'))->get();

        return view('report.panen-tebu.index', compact('title', 'nav', 'kontraktor'));
    }

    public function proses(Request $request){
        // dd(round(2.538, 0, PHP_ROUND_HALF_UP));

        $companycode = session('companycode');
        $timbangan = new Timbangan;

        $data = $timbangan->getData($companycode);
        // dd($data);

        $viewData = [
                'data' => collect($data),
                'kontraktor' => $request->idkontraktor,
                'startDate' => $request->start_date,
                'endDate' => $request->end_date,

            ];

         return view('report.panen-tebu.result', $viewData);
    }


}
