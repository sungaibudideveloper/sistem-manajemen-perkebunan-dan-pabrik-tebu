<?php
namespace App\Http\Controllers\Report;
use App\Http\Controllers\Controller;

use Carbon\Carbon;
use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ReportController extends Controller
{
    public function __construct()
    {
        View::share([
            'navbar' => 'Report',
        ]);
    }
    public function agronomi(Request $request)
    {
        $title = "Report Agronomi";
        $nav = "Agronomi";
        $company = company::all();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $query = DB::table('agrolst')
            ->leftJoin('agrohdr', function ($join) {
                $join->on('agrolst.nosample', '=', 'agrohdr.nosample')
                    ->whereColumn('agrolst.companycode', '=', 'agrohdr.companycode')
                    ->whereColumn('agrolst.tanggaltanam', '=', 'agrohdr.tanggaltanam');
            })
            ->leftJoin('company', function ($join) {
                $join->on('agrohdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agrohdr.blok', '=', 'blok.blok')
                    ->whereColumn('agrohdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('agrohdr.plot', '=', 'plot.plot')
                    ->whereColumn('agrohdr.companycode', '=', 'plot.companycode');
            })
            ->where('agrolst.companycode', session('companycode'))
            ->where('agrohdr.companycode', session('companycode'))
            ->where('agrohdr.status', '=', 'Posted')
            ->where('agrolst.status', '=', 'Posted')
            ->select(
                'agrolst.*',
                'agrohdr.varietas',
                'agrohdr.kat',
                'agrohdr.tanggalpengamatan',
                'company.name as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
                'plot.jaraktanam',
            )
            ->orderBy('agrohdr.tanggalpengamatan', 'asc');

        if ($startDate) {
            $query->whereDate('agrohdr.tanggalpengamatan', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('agrohdr.tanggalpengamatan', '<=', $endDate);
        }

        $agronomi = $query->paginate($perPage);

        foreach ($agronomi as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            $dateInput = Carbon::parse($item->createdat);
            $item->bulanPengamatan = $dateInput->format('F');
        }

        foreach ($agronomi as $index => $item) {
            $item->no = ($agronomi->currentPage() - 1) * $agronomi->perPage() + $index + 1;
        }

        return view('report.agronomi.index', compact('company', 'nav', 'agronomi', 'perPage', 'startDate', 'endDate', 'title'));
    }

    public function hpt(Request $request)
    {
        $title = "Report HPT";
        $nav = "HPT";
        $company = company::all();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($request->isMethod('post')) {
            $request->validate([
                'perPage' => 'required|integer|min:1',
            ]);
            $request->session()->put('perPage', $request->input('perPage'));
        }

        $perPage = $request->session()->get('perPage', 10);

        $query = DB::table('hptlst')
            ->leftJoin('hpthdr', function ($join) {
                $join->on('hptlst.nosample', '=', 'hpthdr.nosample')
                    ->whereColumn('hptlst.companycode', '=', 'hpthdr.companycode')
                    ->whereColumn('hptlst.tanggaltanam', '=', 'hpthdr.tanggaltanam');
            })
            ->leftJoin('company', function ($join) {
                $join->on('hpthdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpthdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpthdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('hpthdr.plot', '=', 'plot.plot')
                    ->whereColumn('hpthdr.companycode', '=', 'plot.companycode');
            })
            ->where('hptlst.companycode', session('companycode'))
            ->where('hpthdr.companycode', session('companycode'))
            ->where('hpthdr.status', '=', 'Posted')
            ->where('hptlst.status', '=', 'Posted')
            ->select(
                'hptlst.*',
                'hpthdr.varietas',
                'hpthdr.tanggalpengamatan',
                'company.name as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
            )
            ->orderBy('hpthdr.tanggalpengamatan', 'desc');

        if ($startDate) {
            $query->whereDate('hpthdr.tanggalpengamatan', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('hpthdr.tanggalpengamatan', '<=', $endDate);
        }

        $hpt = $query->paginate($perPage);

        foreach ($hpt as $item) {
            $item->umur_tanam = Carbon::parse($item->tanggaltanam)->diffInMonths(Carbon::now());
            $dateInput = Carbon::parse($item->createdat);
            $item->bulanPengamatan = $dateInput->format('F');
        }

        foreach ($hpt as $index => $item) {
            $item->no = ($hpt->currentPage() - 1) * $hpt->perPage() + $index + 1;
        }

        return view('report.hpt.index', compact('company', 'nav', 'hpt', 'perPage', 'startDate', 'endDate', 'title'));
    }
}
