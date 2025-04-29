<?php

namespace App\Http\Controllers\Input\KerjaHarian;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\DistribusiTenagaHarian;

class DistribusiTenagaHarianController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        return view('input.kerjaharian.distribusitenagaharian.index', [
            'title'     => 'Distribusi Tenaga Harian',
            'navbar'    => 'Input',
            'nav'       => 'Distribusi Tenaga Harian',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }
}