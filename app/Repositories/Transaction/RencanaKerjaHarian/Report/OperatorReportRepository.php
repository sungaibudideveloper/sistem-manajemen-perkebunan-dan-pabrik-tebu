<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Report;

use Illuminate\Support\Facades\DB;

/**
 * OperatorReportRepository
 * 
 * Handles Operator report queries.
 * RULE: All queries here.
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
            ->join('lkhhdr as lh', 'lk.lkhno', '=', 'lh.lkhno')
            ->join('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3);
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
     * 
     * @param string $companycode
     * @param string $date
     * @param string $operatorId
     * @return \Illuminate\Support\Collection
     */
    public function getOperatorActivitiesForDate($companycode, $date, $operatorId)
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', 'lk.lkhno', '=', 'lh.lkhno')
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.lkhno', '=', 'ldp.lkhno')
                    ->on('lh.companycode', '=', 'ldp.companycode');
            })
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->where('lk.operatorid', $operatorId)
            ->select([
                'lk.jammulai',
                'lk.jamselesai',
                DB::raw('TIMEDIFF(lk.jamselesai, lk.jammulai) as durasi_kerja'),
                'ldp.blok',
                'ldp.plot',
                'lh.activitycode',
                'a.activityname',
                'ldp.luasrkh as luas_rencana_ha',
                'ldp.luashasil as luas_hasil_ha',
                'lk.solar',
                'lk.hourmeterstart',
                'lk.hourmeterend',
                'lh.lkhno',
                'lh.rkhno'
            ])
            ->orderBy('lk.jammulai')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Get operator basic info
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
            ->where('tk.jenistenagakerja', 3)
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