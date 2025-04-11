<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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
                $join->on('agrolst.no_sample', '=', 'agrohdr.no_sample')
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
                'agrohdr.tglamat',
                'company.nama as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
                'plot.jaraktanam',
            )
            ->orderBy('agrohdr.tglamat', 'asc');

        if ($startDate) {
            $query->whereDate('agrohdr.tglamat', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('agrohdr.tglamat', '<=', $endDate);
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

        $query = DB::table('hpt_lst')
            ->leftJoin('hpt_hdr', function ($join) {
                $join->on('hpt_lst.no_sample', '=', 'hpt_hdr.no_sample')
                    ->whereColumn('hpt_lst.companycode', '=', 'hpt_hdr.companycode')
                    ->whereColumn('hpt_lst.tanggaltanam', '=', 'hpt_hdr.tanggaltanam');
            })
            ->leftJoin('company', function ($join) {
                $join->on('hpt_hdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpt_hdr.blok', '=', 'blok.blok')
                    ->whereColumn('hpt_hdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plot', function ($join) {
                $join->on('hpt_hdr.plot', '=', 'plot.plot')
                    ->whereColumn('hpt_hdr.companycode', '=', 'plot.companycode');
            })
            ->where('hpt_lst.companycode', session('companycode'))
            ->where('hpt_hdr.companycode', session('companycode'))
            ->where('hpt_hdr.status', '=', 'Posted')
            ->where('hpt_lst.status', '=', 'Posted')
            ->select(
                'hpt_lst.*',
                'hpt_hdr.varietas',
                'hpt_hdr.tglamat',
                'company.nama as compName',
                'blok.blok as blokName',
                'plot.plot as plotName',
                'plot.luasarea',
            )
            ->orderBy('hpt_hdr.tglamat', 'desc');

        if ($startDate) {
            $query->whereDate('hpt_hdr.tglamat', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('hpt_hdr.tglamat', '<=', $endDate);
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
