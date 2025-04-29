<?php

namespace App\Http\Controllers\Input\KerjaHarian;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\LaporanKerjaHarian;

class LaporanKerjaHarianController extends Controller
{
    
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        return view('input.kerjaharian.laporankerjaharian.index', [
            'title'     => 'Laporan Kerja Harian',
            'navbar'    => 'Input',
            'nav'       => 'Laporan Kerja Harian',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }
}