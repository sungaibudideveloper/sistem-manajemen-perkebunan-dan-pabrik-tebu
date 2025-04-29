<?php

namespace App\Http\Controllers\Input\KerjaHarian;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\RencanaKerjaHarian;

class RencanaKerjaHarianController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        return view('input.kerjaharian.rencanakerjaharian.index', [
            'title'     => 'Rencana Kerja Harian',
            'navbar'    => 'Input',
            'nav'       => 'Rencana Kerja Harian',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }

    public function store(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        return view('input.kerjaharian.rencanakerjaharian.create', [
            'title'     => 'Rencana Kerja Harian',
            'navbar'    => 'Input',
            'nav'       => 'Rencana Kerja Harian',
            'perPage'   => $perPage,
            'search'    => $search,
        ]);
    }
}