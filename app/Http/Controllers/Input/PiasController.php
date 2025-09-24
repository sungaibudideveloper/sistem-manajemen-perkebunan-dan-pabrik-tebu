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
        $piashdr = new piashdr;
        $piaslst = new piaslst;

    }
    


}
