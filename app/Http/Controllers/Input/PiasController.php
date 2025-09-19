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
        $data = $rkhhdr->where('status','done')->get();

        
        return view('input.pias.home')->with([
            'title'         => 'Pias',
            'usehdr'        => $usehdr,
            'perPage'       => $perPage
        ]);
    }

    public function detail(Request $request)
    {   
        $rkhhdr = new rkhhdr;
        $rkhlst = new rkhlst;
        $lkhhdr = new rkhhdr;
        $lkhlst = new rkhlst;

        $rkhdata = $rkhhdr
        ->leftJoin('rkh_lst', function($join) {
            $join->on('rkh_hdr.rkhno', '=', 'rkh_lst.rkhno')
                ->on('rkh_hdr.companycode', '=', 'rkh_lst.companycode');
        })->where('rkh_hdr.status', 'done')->where('rkhno', $request->rkhno)
        ->select('rkh_hdr.*', 'rkh_lst.blok', 'rkh_lst.plot', 'rkh_lst.luasarea')
        ->get();

        $request->rkhno
        
        return view('input.pias.home')->with([
            'title'         => 'Pias',
            'usehdr'        => $usehdr,
            'perPage'       => $perPage
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
