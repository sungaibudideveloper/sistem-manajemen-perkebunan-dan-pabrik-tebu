<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * RekapReportRepository
 * 
 * Handles LKH Rekap report queries
 * Groups activities by activity group (Pengolahan, Perawatan, Panen, Pias, Lain-lain)
 */
class RekapReportRepository
{
    /**
     * Get company info
     * 
     * @param string $companycode
     * @return string
     */
    public function getCompanyInfo(string $companycode): string
    {
        $company = DB::table('company')
            ->where('companycode', $companycode)
            ->select('companycode', 'name')
            ->first();
        
        return $company ? "{$company->companycode} - {$company->name}" : $companycode;
    }

    /**
     * Get LKH numbers for specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getLkhNumbersByDate(string $companycode, string $date): array
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('lkhdate', $date)
            ->pluck('lkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get ALL LKH data for date (one query)
     * Returns raw data to be grouped by service layer
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getAllLkhDataForDate(string $companycode, string $date): Collection
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('lkhdetailplot as ldp', function($join) {
                $join->on('h.id', '=', 'ldp.lkhhdrid');
            })
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('rkhlstkendaraan as rk', function($join) {
                $join->on('h.rkhno', '=', 'rk.rkhno')
                     ->on('h.activitycode', '=', 'rk.activitycode')
                     ->on('h.companycode', '=', 'rk.companycode');
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

    /**
     * Get LKH data by activity group (alternative: separate queries per group)
     * 
     * @param string $companycode
     * @param string $date
     * @param array $activityGroups (e.g., ['I', 'II'])
     * @return Collection
     */
    public function getLkhDataByActivityGroups(string $companycode, string $date, array $activityGroups): Collection
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('lkhdetailplot as ldp', function($join) {
                $join->on('h.id', '=', 'ldp.lkhhdrid');
            })
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('plot as p', function($join) use ($companycode) {
                $join->on('ldp.plot', '=', 'p.plot')
                     ->where('p.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->whereIn('a.activitygroup', $activityGroups)
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
                'u.name as mandor_nama'
            ])
            ->orderBy('h.activitycode')
            ->get();
    }

    /**
     * Get Pengolahan data (Activity Group I, II)
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getPengolahanData(string $companycode, string $date): Collection
    {
        return $this->getLkhDataByActivityGroups($companycode, $date, ['I', 'II']);
    }

    /**
     * Get Perawatan data (Activity Group III)
     * Split by PC/RC activities
     * 
     * @param string $companycode
     * @param string $date
     * @return array ['pc' => Collection, 'rc' => Collection]
     */
    public function getPerawatanData(string $companycode, string $date): array
    {
        $allPerawatan = $this->getLkhDataByActivityGroups($companycode, $date, ['III']);
        
        return [
            'pc' => $allPerawatan->filter(function($item) {
                return strpos($item->activitycode, '3.2.') !== 0; // NOT RC
            }),
            'rc' => $allPerawatan->filter(function($item) {
                return strpos($item->activitycode, '3.2.') === 0; // IS RC (3.2.x)
            })
        ];
    }

    /**
     * Get Panen data (Activity Group IV)
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getPanenData(string $companycode, string $date): Collection
    {
        return $this->getLkhDataByActivityGroups($companycode, $date, ['IV']);
    }

    /**
     * Get Pias/Hama data (Activity Group V)
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getPiasData(string $companycode, string $date): Collection
    {
        return $this->getLkhDataByActivityGroups($companycode, $date, ['V']);
    }

    /**
     * Get Lain-lain data (Activity Group VI, VII, VIII)
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getLainLainData(string $companycode, string $date): Collection
    {
        return $this->getLkhDataByActivityGroups($companycode, $date, ['VI', 'VII', 'VIII']);
    }

    /**
     * Get summary statistics for date
     * 
     * @param string $companycode
     * @param string $date
     * @return object
     */
    public function getRekapSummary(string $companycode, string $date): object
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->select([
                DB::raw('COUNT(DISTINCT h.lkhno) as total_lkh'),
                DB::raw('SUM(h.totalworkers) as total_workers'),
                DB::raw('SUM(h.totalhasil) as total_luas'),
                DB::raw('SUM(h.totalupahall) as total_upah'),
                DB::raw('COUNT(DISTINCT h.mandorid) as total_mandor'),
                DB::raw('COUNT(DISTINCT a.activitygroup) as total_activity_groups')
            ])
            ->first() ?? (object)[
                'total_lkh' => 0,
                'total_workers' => 0,
                'total_luas' => 0,
                'total_upah' => 0,
                'total_mandor' => 0,
                'total_activity_groups' => 0
            ];
    }
}