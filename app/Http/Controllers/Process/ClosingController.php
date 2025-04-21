<?php

namespace App\Http\Controllers\Process;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ClosingController extends Controller
{
    public function closing()
    {
        $period = DB::table('company')->where('companycode', '=', session('companycode'))
            ->value('tgl');
        $now = Carbon::now()->toDateString();

        $headAgronomiQuery = DB::table('agrohdr')
            ->join('company', 'agrohdr.companycode', '=', 'company.companycode')
            ->where('agrohdr.companycode', '=', session('companycode'))
            ->whereBetween('agrohdr.createdat', [DB::raw('company.tgl'), now()])
            ->select('agrohdr.*');

        $listAgronomiQuery = DB::table('agrolst')
            ->join('company', 'agrolst.companycode', '=', 'company.companycode')
            ->where('agrolst.companycode', '=', session('companycode'))
            ->whereBetween('agrolst.createdat', [DB::raw('company.tgl'), now()])
            ->select('agrolst.*');

        $headHPTQuery = DB::table('hpthdr')
            ->join('company', 'hpthdr.companycode', '=', 'company.companycode')
            ->where('hpthdr.companycode', '=', session('companycode'))
            ->whereBetween('hpthdr.createdat', [DB::raw('company.tgl'), now()])
            ->select('hpthdr.*');

        $listHPTQuery = DB::table('hptlst')
            ->join('company', 'hptlst.companycode', '=', 'company.companycode')
            ->where('hptlst.companycode', '=', session('companycode'))
            ->whereBetween('hptlst.createdat', [DB::raw('company.tgl'), now()])
            ->select('hptlst.*');

        $headAgronomi = $headAgronomiQuery->get();
        $listAgronomi = $listAgronomiQuery->get();
        $headHPT = $headHPTQuery->get();
        $listHPT = $listHPTQuery->get();

        $log = DB::table('company')
            ->where('companycode', '=', session('companycode'))
            ->get();
        $close = DB::table('log_closing')
            ->where('companycode', '=', session('companycode'))
            ->where('tgl1', '=', now()->toDateString())
            ->exists();

        if ($headAgronomi && $listAgronomi && $headHPT && $listHPT && $log && !$close) {
            foreach ($headAgronomi as $head) {
                DB::table('closing_agrohdr')->insert((array) $head);
            }
            foreach ($listAgronomi as $row) {
                DB::table('closing_agrolst')->insert((array) $row);
            }
            foreach ($headHPT as $head) {
                DB::table('closing_hpthdr')->insert((array) $head);
            }
            foreach ($listHPT as $row) {
                DB::table('closing_hptlst')->insert((array) $row);
            }
            foreach ($log as $lo) {
                $logData = [
                    'companycode' => $lo->companycode,
                    'tgl1' => $lo->tgl,
                    'tgl2' => Carbon::now(),
                ];
                $comp = [
                    'tgl' => Carbon::now(),
                    'updatedat' => Carbon::now(),
                ];

                DB::table('log_closing')->insert($logData);
                DB::table('company')
                    ->where('companycode', '=', session('companycode'))
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
