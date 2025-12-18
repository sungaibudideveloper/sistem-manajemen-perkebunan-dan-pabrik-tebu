<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Report;

use Illuminate\Support\Facades\DB;

/**
 * OperatorRekapReportRepository
 * 
 * Handles all database queries for Operator Rekap Report (All operators summary).
 * RULE: All DB queries must be here, nowhere else.
 */
class OperatorRekapReportRepository
{

    public function getAllOperatorsWithActivities($companycode, $date)
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhno', '=', 'lh.lkhno')
                    ->on('lk.companycode', '=', 'lh.companycode');
            })
            ->join('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3);
            })
            ->join('kendaraan as k', function($join) use ($companycode) {
                $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', '=', $companycode);
            })
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            ->leftJoin(DB::raw('(
                SELECT 
                    lkhno,
                    companycode,
                    GROUP_CONCAT(DISTINCT CONCAT(blok, "-", plot) ORDER BY blok, plot SEPARATOR ", ") as plots_display,
                    SUM(luasrkh) as total_luas_rencana,
                    SUM(luashasil) as total_luas_hasil
                FROM lkhdetailplot
                GROUP BY lkhno, companycode
            ) as ldp'), function($join) {
                $join->on('lh.lkhno', '=', 'ldp.lkhno')
                    ->on('lh.companycode', '=', 'ldp.companycode');
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->select([
                'tk.tenagakerjaid',
                'tk.nama as operator_name',
                'tk.nik',
                'k.nokendaraan',
                'k.jenis as vehicle_type',
                
                // Detail aktivitas
                'lk.jammulai',
                'lk.jamselesai',
                DB::raw('TIMEDIFF(lk.jamselesai, lk.jammulai) as durasi_kerja'),
                'lh.activitycode',
                'a.activityname',
                'ldp.plots_display',
                'ldp.total_luas_rencana as luas_rencana_ha',
                'ldp.total_luas_hasil as luas_hasil_ha',
                'lk.solar',
                'lh.lkhno',
            ])
            ->orderBy('tk.nama')
            ->orderBy('lk.jammulai')
            ->get();
    }
}