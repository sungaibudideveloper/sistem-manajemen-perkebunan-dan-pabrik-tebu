<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Report;

use Illuminate\Support\Facades\DB;

/**
 * OperatorReportRepository
 * 
 * Handles all database queries for Operator Report.
 * RULE: All DB queries must be here, nowhere else.
 */
class OperatorReportRepository
{
    /**
     * Get list of operators who worked on specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return \Illuminate\Support\Collection
     */
    public function listOperatorsForDate($companycode, $date)
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhno', '=', 'lh.lkhno')
                     ->on('lk.companycode', '=', 'lh.companycode');
            })
            ->join('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3); // 3 = Operator
            })
            ->join('kendaraan as k', function($join) use ($companycode) {
                $join->on('lk.nokendaraan', '=', 'k.nokendaraan')
                    ->where('k.companycode', '=', $companycode);
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->select([
                'tk.tenagakerjaid',
                'tk.nama',
                'k.nokendaraan',
                'k.jenis'
            ])
            ->distinct()
            ->orderBy('tk.nama')
            ->get();
    }

    /**
     * Get operator activities for specific date
     * Uses aggregation to prevent duplicate rows (1 LKH = 1 row)
     * 
     * @param string $companycode
     * @param string $date
     * @param string $operatorId
     * @return \Illuminate\Support\Collection
     */
    public function getOperatorActivitiesForDate($companycode, $date, $operatorId)
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhno', '=', 'lh.lkhno')
                     ->on('lk.companycode', '=', 'lh.companycode');
            })
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            
            // ✅ LEFT JOIN with aggregated plot data per LKH
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
            ->where('lk.operatorid', $operatorId)
            
            ->select([
                // Time data (from lkhdetailkendaraan)
                'lk.jammulai',
                'lk.jamselesai',
                DB::raw('TIMEDIFF(lk.jamselesai, lk.jammulai) as durasi_kerja'),
                
                // Activity data
                'lh.activitycode',
                'a.activityname',
                
                // ✅ Plot data (AGGREGATED per LKH)
                'ldp.plots_display',
                'ldp.total_luas_rencana as luas_rencana_ha',
                'ldp.total_luas_hasil as luas_hasil_ha',
                
                // Fuel & hourmeter data
                'lk.solar',
                'lk.hourmeterstart',
                'lk.hourmeterend',
                
                // References
                'lh.lkhno',
                'lh.rkhno'
            ])
            ->orderBy('lk.jammulai')
            ->get();
    }

    /**
     * Get operator basic info (name, NIK, vehicle)
     * 
     * @param string $companycode
     * @param string $operatorId
     * @return object|null
     */
    public function getOperatorInfo($companycode, $operatorId)
    {
        return DB::table('tenagakerja as tk')
            ->join('kendaraan as k', function($join) use ($companycode) {
                $join->on('tk.tenagakerjaid', '=', 'k.idtenagakerja')
                    ->where('k.companycode', '=', $companycode);
            })
            ->where('tk.tenagakerjaid', $operatorId)
            ->where('tk.companycode', $companycode)
            ->where('tk.jenistenagakerja', 3) // 3 = Operator
            ->select([
                'tk.tenagakerjaid',
                'tk.nama as operator_name',
                'tk.nik',
                'k.nokendaraan',
                'k.jenis as vehicle_type'
            ])
            ->first();
    }
}