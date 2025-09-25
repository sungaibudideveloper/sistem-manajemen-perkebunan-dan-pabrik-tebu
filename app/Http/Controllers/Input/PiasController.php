<?php

namespace App\Http\Controllers\Input;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\usematerialhdr;
use App\Models\usemateriallst;
use App\Models\HerbisidaDosage;
use App\Models\Herbisida;
use App\Models\Rkhhdr;
use App\Models\Rkhlst;
use App\Models\piashdr;
use App\Models\piaslst;

class PiasController extends Controller
{
 
    public function __construct()
    {
        View::share([
            'navbar' => 'Input',
            'nav' => 'gudang',
            'routeName' => route('input.gudang.index'),
        ]);
    }

    public function home(Request $request)
    {   
        $rkhhdr = new rkhhdr;
        $data = $rkhhdr
        ->leftJoin('user as u', 'u.userid', '=', 'rkhhdr.mandorid')
        ->where('approvalstatus', 1)
        ->select(
            'rkhhdr.*', 
            'u.name as mandor_name'
        )
        ->paginate(15);
        return view('input.pias.home')->with([
            'title'       => 'Pias',
            'data'        => $data
        ]);
    }

    public function detail(Request $request)
    {   
        $rkhhdr = new rkhhdr;
        $rkhlst = new rkhlst;
        $lkhhdr = new rkhhdr;
        $lkhlst = new rkhlst;

        $data = $rkhhdr
        ->leftJoin('user as u', 'u.userid', '=', 'rkhhdr.mandorid')
        ->leftJoin('lkhhdr', 'lkhhdr.rkhno', '=', 'rkhhdr.rkhno')
        ->leftJoin('lkhdetailplot','lkhdetailplot.lkhno', '=', 'lkhhdr.lkhno')
        ->leftJoin('masterlist', function($join) {
            $join->on('masterlist.companycode', '=', 'rkhhdr.companycode')
                 ->on('masterlist.blok', '=', 'lkhdetailplot.blok')
                 ->on('masterlist.plot', '=', 'lkhdetailplot.plot');
        })
        ->where('rkhhdr.rkhno', $request->input('rkhno'))
        ->where('approvalstatus', 1)
        ->select(
            'rkhhdr.*', 
            'u.name as mandor_name',
            'lkhhdr.lkhno',
            'lkhdetailplot.blok',
            'lkhdetailplot.plot',
            'lkhdetailplot.luasrkh',
            'masterlist.tanggalulangtahun',
            'masterlist.kodevarietas',
            'masterlist.kodestatus',
            'masterlist.batchno'
        )
        ->get();
        
        // dd($data);

        return view('input.pias.detail')->with([
            'title'         => 'Pias',
            'data'        => $data
        ]);
    }

    public function submit(Request $request) 
    {
        $request->validate([
            'inputTJ' => 'required|numeric|min:0',
            'inputTC' => 'required|numeric|min:0',
            'rkhno' => 'required|string'
        ]);
    
        // Data yang diterima dari frontend
        $stokTJ = $request->inputTJ;
        $stokTC = $request->inputTC;
        $rkhno = $request->rkhno;
        $allocations = $request->allocations; // Array hasil perhitungan JS
    
        DB::beginTransaction();
        
        try {
            // 1. Simpan ke tabel piashdr (header)
            $piashdr = new piashdr();
            $piashdr->rkhno = $rkhno;
            $piashdr->companycode = auth()->user()->companycode ?? 'DEFAULT';
            $piashdr->generateddate = now();
            $piashdr->total_tj = $stokTJ;
            $piashdr->total_tc = $stokTC;
            $piashdr->inputby = auth()->user()->name ?? 'System';
            $piashdr->updateby = auth()->user()->name ?? 'System';
            $piashdr->save();
    
            // 2. Simpan ke tabel piaslst (detail per plot)
            foreach ($allocations as $allocation) {
                $piaslst = new piaslst();
                $piaslst->rkhno = $rkhno;
                $piaslst->blok = $allocation['blok'];
                $piaslst->plot = $allocation['plot'];
                $piaslst->alokasi_tj = $allocation['tj'];
                $piaslst->alokasi_tc = $allocation['tc'];
                $piaslst->kebutuhan_tj = $allocation['needTJ'];
                $piaslst->kebutuhan_tc = $allocation['needTC'];
                $piaslst->luas = $allocation['luas'];
                $piaslst->umur_hari = $allocation['umur'];
                $piaslst->save();
            }
    
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data pias berhasil disimpan'
            ]);
    
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
    


}
