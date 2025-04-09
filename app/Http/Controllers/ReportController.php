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

        $query = DB::table('agro_lst')
            ->leftJoin('agro_hdr', function ($join) {
                $join->on('agro_lst.no_sample', '=', 'agro_hdr.no_sample')
                    ->whereColumn('agro_lst.companycode', '=', 'agro_hdr.companycode')
                    ->whereColumn('agro_lst.tanggaltanam', '=', 'agro_hdr.tanggaltanam');
            })
            ->leftJoin('company', function ($join) {
                $join->on('agro_hdr.companycode', '=', 'company.companycode');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agro_hdr.blok', '=', 'blok.blok')
                    ->whereColumn('agro_hdr.companycode', '=', 'blok.companycode');
            })
            ->leftJoin('plotting', function ($join) {
                $join->on('agro_hdr.plotcode', '=', 'plotting.plotcode')
                    ->whereColumn('agro_hdr.companycode', '=', 'plotting.companycode');
            })
            ->where('agro_lst.companycode', session('dropdown_value'))
            ->where('agro_hdr.companycode', session('dropdown_value'))
            ->where('agro_hdr.status', '=', 'Posted')
            ->where('agro_lst.status', '=', 'Posted')
            ->select(
                'agro_lst.*',
                'agro_hdr.varietas',
                'agro_hdr.kat',
                'agro_hdr.tglamat',
                'company.nama as compName',
                'blok.blok as blokName',
                'plotting.plotcode as plotName',
                'plotting.luasarea',
                'plotting.jaraktanam',
            )
            ->orderBy('agro_hdr.tglamat', 'asc');

        if ($startDate) {
            $query->whereDate('agro_hdr.tglamat', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('agro_hdr.tglamat', '<=', $endDate);
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
            ->leftJoin('plotting', function ($join) {
                $join->on('hpt_hdr.plotcode', '=', 'plotting.plotcode')
                    ->whereColumn('hpt_hdr.companycode', '=', 'plotting.companycode');
            })
            ->where('hpt_lst.companycode', session('dropdown_value'))
            ->where('hpt_hdr.companycode', session('dropdown_value'))
            ->where('hpt_hdr.status', '=', 'Posted')
            ->where('hpt_lst.status', '=', 'Posted')
            ->select(
                'hpt_lst.*',
                'hpt_hdr.varietas',
                'hpt_hdr.tglamat',
                'company.nama as compName',
                'blok.blok as blokName',
                'plotting.plotcode as plotName',
                'plotting.luasarea',
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
