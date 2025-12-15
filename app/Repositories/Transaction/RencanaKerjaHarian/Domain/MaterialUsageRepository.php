<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Domain;

use Illuminate\Support\Facades\DB;

/**
 * MaterialUsageRepository
 * 
 * Handles material usage (usematerialhdr/lst) queries.
 * RULE: All queries here.
 */
class MaterialUsageRepository
{
    /**
     * Get material usage by RKH number
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return \Illuminate\Support\Collection
     */
    public function getUsageByRkhNo($companycode, $rkhno)
    {
        return DB::table('usematerialhdr as h')
            ->leftJoin('usemateriallst as l', function($join) {
                $join->on('h.companycode', '=', 'l.companycode')
                     ->on('h.rkhno', '=', 'l.rkhno');
            })
            ->leftJoin('herbisidagroup as hg', 'l.herbisidagroupid', '=', 'hg.herbisidagroupid')
            ->where('h.companycode', $companycode)
            ->where('h.rkhno', $rkhno)
            ->select([
                'h.rkhno',
                'h.totalluas',
                'h.flagstatus',
                'h.createdat',
                'h.inputby',
                'l.itemcode',
                'l.itemname',
                'l.qty',
                'l.unit',
                'l.dosageperha',
                'l.herbisidagroupid',
                'hg.herbisidagroupname'
            ])
            ->get();
    }

    /**
     * Check if material usage exists for RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function existsForRkhNo($companycode, $rkhno)
    {
        return DB::table('usematerialhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->exists();
    }
}