<?php

namespace App\Http\Controllers\Input\KerjaHarian;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\RkhHdr;
use App\Models\Mandor;
use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\Blok;
use App\Models\Masterlist;

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

    public function create()
    {
        $today = Carbon::today();
        $day = $today->format('d');
        $month = $today->format('m');
        $year = $today->format('y');

        // Database table: rkhhdr
        $lastRkh = DB::table('rkhhdr')
            ->whereDate('rkhdate', $today)
            ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc') // SQL Index Start From 1; CAST so the sorting is numeric
            ->first();

        if ($lastRkh) {
            $lastNumber = (int)substr($lastRkh->rkhno, 7, 2); // PHP Index Start From 0
            $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '01';
        }

        $generatedNoRkh = "RKH{$day}{$month}{$newNumber}{$year}";

        // Database table: mandor
        $mandors = Mandor::orderBy('companycode')->orderBy('id')->get();

        // Database table : activity
        $activities = Activity::with('group')->orderBy('activitycode')->get();

        // Database table : blok
        $bloks = Blok::orderBy('blok')->get();

        // Database table : masterlist
        $masterlist = Masterlist::orderBy('companycode')->orderBy('plot')->get();

        return view('input.kerjaharian.rencanakerjaharian.create', [
            'title' => 'Form RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhno' => $generatedNoRkh,
            'mandors'   => $mandors,
            'activities' => $activities,
            'bloks' => $bloks,
            'masterlist' => $masterlist,
        ]);
    }

}