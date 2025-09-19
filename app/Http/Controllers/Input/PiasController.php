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

class GudangController extends Controller
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
        dd( $data );

        
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
        $data = $rkhhdr
        ->leftJoin('rkh_lst', function($join) {
            $join->on('rkh_hdr.rkhno', '=', 'rkh_lst.rkhno')
                ->on('rkh_hdr.companycode', '=', 'rkh_lst.companycode');
        })->where('rkh_hdr.status', 'done')
        ->select('rkh_hdr.*', 'rkh_lst.blok', 'rkh_lst.plot', 'rkh_lst.luasarea')
        ->get();
        dd( $data );

        
        return view('input.pias.home')->with([
            'title'         => 'Pias',
            'usehdr'        => $usehdr,
            'perPage'       => $perPage
        ]);
    }


}
