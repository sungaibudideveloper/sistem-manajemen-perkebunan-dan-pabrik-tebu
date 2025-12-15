<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Report;

use Illuminate\Support\Facades\DB;

/**
 * RekapLkhReportRepository
 * 
 * Handles LKH Rekap report queries.
 * RULE: All queries here.
 */
class RekapLkhReportRepository
{
    /**
     * Get all LKH rows for specific date
     * Single query to get all activity groups
     * 
     * @param string $companycode
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    public function getAllLkhRowsForDate($companycode, $date)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                $join->on('h.lkhno', '=', 'ldp.lkhno')
                    ->where('ldp.companycode', '=', $companycode);
            })
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('rkhlstkendaraan as rk', function($join) use ($companycode) {
                $join->on('h.rkhno', '=', 'rk.rkhno')
                    ->on('h.activitycode', '=', 'rk.activitycode')
                    ->where('rk.companycode', '=', $companycode);
            })
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('rk.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3);
            })
            ->leftJoin('plot as p', function($join) use ($companycode) {
                $join->on('ldp.plot', '=', 'p.plot')
                    ->where('p.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->select([
                'h.lkhno',
                'h.activitycode',
                'h.totalworkers',
                'ldp.luashasil as totalhasil',
                'h.totalupahall',
                'a.activityname',
                'a.activitygroup',
                'ldp.plot',
                'p.luasarea',
                'u.name as mandor_nama',
                'tk.nama as operator_nama'
            ])
            ->orderBy('h.activitycode')
            ->orderBy('h.lkhno')
            ->get();
    }
}