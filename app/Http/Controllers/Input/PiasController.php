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
            'masterlist.kodestatus',
            'masterlist.batchno'
        )
        ->get();
        
        // dd($data);

        return view('input.pias.home')->with([
            'title'         => 'Pias',
            'data'        => $data
        ]);
    }

    function hitungPiasBulanan($plot_luas) {
        $pias_per_bulan = [
            1 => ['TJ' => 70 * $plot_luas, 'TC' => 30 * $plot_luas],
            2 => ['TJ' => 70 * $plot_luas, 'TC' => 30 * $plot_luas],
            3 => ['TJ' => 69 * $plot_luas, 'TC' => 40 * $plot_luas],
            4 => ['TJ' => 50 * $plot_luas, 'TC' => 50 * $plot_luas],
            5 => ['TJ' => 40 * $plot_luas, 'TC' => 60 * $plot_luas],
            6 => ['TJ' => 30 * $plot_luas, 'TC' => 70 * $plot_luas],
            7 => ['TJ' => 30 * $plot_luas, 'TC' => 70 * $plot_luas],
            8 => ['TJ' => 30 * $plot_luas, 'TC' => 70 * $plot_luas],
            9 => ['TJ' => 30 * $plot_luas, 'TC' => 70 * $plot_luas],
            10 => ['TJ' => 30 * $plot_luas, 'TC' => 70 * $plot_luas]
        ];
        
        return $pias_per_bulan;
    }


}
