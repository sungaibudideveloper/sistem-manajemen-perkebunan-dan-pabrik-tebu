<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Perusahaan;
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
        $company = Perusahaan::all();

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
                    ->whereColumn('agro_lst.kd_comp', '=', 'agro_hdr.kd_comp')
                    ->whereColumn('agro_lst.tgltanam', '=', 'agro_hdr.tgltanam');
            })
            ->leftJoin('perusahaan', function ($join) {
                $join->on('agro_hdr.kd_comp', '=', 'perusahaan.kd_comp');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('agro_hdr.kd_blok', '=', 'blok.kd_blok')
                    ->whereColumn('agro_hdr.kd_comp', '=', 'blok.kd_comp');
            })
            ->leftJoin('plotting', function ($join) {
                $join->on('agro_hdr.kd_plot', '=', 'plotting.kd_plot')
                    ->whereColumn('agro_hdr.kd_comp', '=', 'plotting.kd_comp');
            })
            ->where('agro_lst.kd_comp', session('dropdown_value'))
            ->where('agro_hdr.kd_comp', session('dropdown_value'))
            ->where('agro_hdr.status', '=', 'Posted')
            ->where('agro_lst.status', '=', 'Posted')
            ->select(
                'agro_lst.*',
                'agro_hdr.varietas',
                'agro_hdr.kat',
                'agro_hdr.tglamat',
                'perusahaan.nama as compName',
                'blok.kd_blok as blokName',
                'plotting.kd_plot as plotName',
                'plotting.luas_area',
                'plotting.jarak_tanam',
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
            $item->umur_tanam = Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now());
            $dateInput = Carbon::parse($item->created_at);
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
        $company = Perusahaan::all();

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
                    ->whereColumn('hpt_lst.kd_comp', '=', 'hpt_hdr.kd_comp')
                    ->whereColumn('hpt_lst.tgltanam', '=', 'hpt_hdr.tgltanam');
            })
            ->leftJoin('perusahaan', function ($join) {
                $join->on('hpt_hdr.kd_comp', '=', 'perusahaan.kd_comp');
            })
            ->leftJoin('blok', function ($join) {
                $join->on('hpt_hdr.kd_blok', '=', 'blok.kd_blok')
                    ->whereColumn('hpt_hdr.kd_comp', '=', 'blok.kd_comp');
            })
            ->leftJoin('plotting', function ($join) {
                $join->on('hpt_hdr.kd_plot', '=', 'plotting.kd_plot')
                    ->whereColumn('hpt_hdr.kd_comp', '=', 'plotting.kd_comp');
            })
            ->where('hpt_lst.kd_comp', session('dropdown_value'))
            ->where('hpt_hdr.kd_comp', session('dropdown_value'))
            ->where('hpt_hdr.status', '=', 'Posted')
            ->where('hpt_lst.status', '=', 'Posted')
            ->select(
                'hpt_lst.*',
                'hpt_hdr.varietas',
                'hpt_hdr.tglamat',
                'perusahaan.nama as compName',
                'blok.kd_blok as blokName',
                'plotting.kd_plot as plotName',
                'plotting.luas_area',
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
            $item->umur_tanam = Carbon::parse($item->tgltanam)->diffInMonths(Carbon::now());
            $dateInput = Carbon::parse($item->created_at);
            $item->bulanPengamatan = $dateInput->format('F');
        }

        foreach ($hpt as $index => $item) {
            $item->no = ($hpt->currentPage() - 1) * $hpt->perPage() + $index + 1;
        }

        return view('report.hpt.index', compact('company', 'nav', 'hpt', 'perPage', 'startDate', 'endDate', 'title'));
    }
}
