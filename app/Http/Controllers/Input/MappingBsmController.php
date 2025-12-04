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
use App\Models\Rkhhdr;

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
        // dd($request);
        $title = "Mapping BSM";
        $nav = "Mapping BSM";
        $companycode = session('companycode');
        
        // Initialize data variable
        $data = collect(); // Empty collection by default
        
        // Validate form inputs if submitted
        if ($request->isMethod('post')) {
            $request->validate([
                'tanggalawal' => 'required|date',
                'tanggalakhir' => 'required|date|after_or_equal:tanggalawal',
            ], [
                'tanggalawal.required' => 'Tanggal awal wajib diisi',
                'tanggalawal.date' => 'Format tanggal awal tidak valid',
                'tanggalakhir.required' => 'Tanggal akhir wajib diisi',
                'tanggalakhir.date' => 'Format tanggal akhir tidak valid',
                'tanggalakhir.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal',
            ]);

            // Get data from model when form is submitted
            $Rkhhdr = new Rkhhdr();
            $data = $Rkhhdr->getDataBsmSJ($companycode, $request->tanggalawal, $request->tanggalakhir);
            // dd($data);
            
            // Ensure data is a collection
            if (!$data instanceof \Illuminate\Support\Collection) {
                $data = collect($data);
            }
        }

        return view('input.mapping-bsm.index', compact('title', 'nav', 'data'));
    }

    public function getBsmDetail(Request $request)
    {
        $companycode = session('companycode');
        $rkhno = $request->get('rkhno');
        
        if (!$rkhno) {
            return response()->json([
                'success' => false,
                'message' => 'RKH number is required'
            ], 400);
        }
        
        try {
            $Rkhhdr = new Rkhhdr();
            $data = $Rkhhdr->getBsmDetailByRkh($companycode, $rkhno);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching BSM detail: ' . $e->getMessage()
            ], 500);
        }
    }


}
