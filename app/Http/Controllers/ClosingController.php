<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ClosingController extends Controller
{
    public function closing()
    {
        $period = DB::table('perusahaan')->where('kd_comp', '=', session('dropdown_value'))
            ->value('tgl');
        $now = Carbon::now()->toDateString();

        $headAgronomiQuery = DB::table('agro_hdr')
            ->join('perusahaan', 'agro_hdr.kd_comp', '=', 'perusahaan.kd_comp')
            ->where('agro_hdr.kd_comp', '=', session('dropdown_value'))
            ->whereBetween('agro_hdr.created_at', [DB::raw('perusahaan.tgl'), now()])
            ->select('agro_hdr.*');

        $listAgronomiQuery = DB::table('agro_lst')
            ->join('perusahaan', 'agro_lst.kd_comp', '=', 'perusahaan.kd_comp')
            ->where('agro_lst.kd_comp', '=', session('dropdown_value'))
            ->whereBetween('agro_lst.created_at', [DB::raw('perusahaan.tgl'), now()])
            ->select('agro_lst.*');

        $headHPTQuery = DB::table('hpt_hdr')
            ->join('perusahaan', 'hpt_hdr.kd_comp', '=', 'perusahaan.kd_comp')
            ->where('hpt_hdr.kd_comp', '=', session('dropdown_value'))
            ->whereBetween('hpt_hdr.created_at', [DB::raw('perusahaan.tgl'), now()])
            ->select('hpt_hdr.*');

        $listHPTQuery = DB::table('hpt_lst')
            ->join('perusahaan', 'hpt_lst.kd_comp', '=', 'perusahaan.kd_comp')
            ->where('hpt_lst.kd_comp', '=', session('dropdown_value'))
            ->whereBetween('hpt_lst.created_at', [DB::raw('perusahaan.tgl'), now()])
            ->select('hpt_lst.*');

        $headAgronomi = $headAgronomiQuery->get();
        $listAgronomi = $listAgronomiQuery->get();
        $headHPT = $headHPTQuery->get();
        $listHPT = $listHPTQuery->get();

        $log = DB::table('perusahaan')
            ->where('kd_comp', '=', session('dropdown_value'))
            ->get();
        $close = DB::table('log_closing')
            ->where('kd_comp', '=', session('dropdown_value'))
            ->where('tgl1', '=', now()->toDateString())
            ->exists();

        if ($headAgronomi && $listAgronomi && $headHPT && $listHPT && $log && !$close) {
            foreach ($headAgronomi as $head) {
                DB::table('closing_agro_hdr')->insert((array) $head);
            }
            foreach ($listAgronomi as $row) {
                DB::table('closing_agro_lst')->insert((array) $row);
            }
            foreach ($headHPT as $head) {
                DB::table('closing_hpt_hdr')->insert((array) $head);
            }
            foreach ($listHPT as $row) {
                DB::table('closing_hpt_lst')->insert((array) $row);
            }
            foreach ($log as $lo) {
                $logData = [
                    'kd_comp' => $lo->kd_comp,
                    'tgl1' => $lo->tgl,
                    'tgl2' => Carbon::now(),
                ];
                $comp = [
                    'tgl' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                DB::table('log_closing')->insert($logData);
                DB::table('perusahaan')
                    ->where('kd_comp', '=', session('dropdown_value'))
                    ->update($comp);
            }

            $headAgronomiQuery->delete();
            $listAgronomiQuery->delete();
            $headHPTQuery->delete();
            $listHPTQuery->delete();

            return redirect()->back()->with('success1', 'Berhasil Melakukan Closing periode ' . $period . ' s.d ' . $now . '.');
        } else if ($close) {
            return back()->withErrors(['duplicateClosing' => 'Anda sudah melakukan Closing hari ini, silahkan coba kembali nanti.']);
        }


        return redirect()->back()->with('error', 'Data tidak ditemukan.');
    }
}
