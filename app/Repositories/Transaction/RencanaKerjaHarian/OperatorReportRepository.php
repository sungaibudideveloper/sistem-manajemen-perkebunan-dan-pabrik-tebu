<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * OperatorReportRepository
 * 
 * Handles Operator daily report queries
 * Shows operator activities, vehicle usage, fuel consumption
 */
class OperatorReportRepository
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
     * Get list of operators who worked on specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getOperatorsForDate(string $companycode, string $date): Collection
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhhdrid', '=', 'lh.id');
            })
            ->join('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                     ->where('tk.companycode', '=', $companycode)
                     ->where('tk.jenistenagakerja', '=', 3); // Operator
            })
            ->join('kendaraan as k', function($join) use ($companycode) {
                $join->on('lk.kendaraanid', '=', 'k.id')
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
     * Get operator basic info with vehicle
     * 
     * @param string $companycode
     * @param string $operatorId
     * @return object|null
     */
    public function getOperatorInfo(string $companycode, string $operatorId): ?object
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

    /**
     * Get operator activities for specific date
     * Includes time, location, area, fuel consumption
     * 
     * @param string $companycode
     * @param string $operatorId
     * @param string $date
     * @return Collection
     */
    public function getOperatorActivities(string $companycode, string $operatorId, string $date): Collection
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhhdrid', '=', 'lh.id');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid');
            })
            ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->where('lk.operatorid', $operatorId)
            ->select([
                // Time & Duration
                'lk.jammulai',
                'lk.jamselesai',
                DB::raw('TIMEDIFF(lk.jamselesai, lk.jammulai) as durasi_kerja'),
                
                // Location & Activity
                'ldp.blok',
                'ldp.plot',
                'lh.activitycode',
                'a.activityname',
                
                // Area Data
                'ldp.luasrkh as luas_rencana_ha',
                'ldp.luashasil as luas_hasil_ha',
                
                // Fuel Data
                'lk.solar',
                'lk.hourmeterstart',
                'lk.hourmeterend',
                
                // Reference
                'lh.lkhno',
                'lh.rkhno'
            ])
            ->orderBy('lk.jammulai')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Get operator activity summary for date
     * 
     * @param string $companycode
     * @param string $operatorId
     * @param string $date
     * @return object
     */
    public function getOperatorActivitySummary(string $companycode, string $operatorId, string $date): object
    {
        $summary = DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhhdrid', '=', 'lh.id');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid');
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->where('lk.operatorid', $operatorId)
            ->select([
                DB::raw('COUNT(DISTINCT ldp.plot) as total_plots'),
                DB::raw('SUM(ldp.luasrkh) as total_luas_rencana'),
                DB::raw('SUM(ldp.luashasil) as total_luas_hasil'),
                DB::raw('SUM(lk.solar) as total_solar'),
                DB::raw('MAX(lk.hourmeterend) - MIN(lk.hourmeterstart) as total_hourmeter')
            ])
            ->first();
        
        return $summary ?? (object)[
            'total_plots' => 0,
            'total_luas_rencana' => 0,
            'total_luas_hasil' => 0,
            'total_solar' => 0,
            'total_hourmeter' => 0
        ];
    }

    /**
     * Get operator work time summary
     * 
     * @param string $companycode
     * @param string $operatorId
     * @param string $date
     * @return object
     */
    public function getOperatorWorkTimeSummary(string $companycode, string $operatorId, string $date): object
    {
        $times = DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhhdrid', '=', 'lh.id');
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->where('lk.operatorid', $operatorId)
            ->select([
                DB::raw('MIN(lk.jammulai) as first_start'),
                DB::raw('MAX(lk.jamselesai) as last_end'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(lk.jamselesai, lk.jammulai)))) as total_duration')
            ])
            ->first();
        
        return $times ?? (object)[
            'first_start' => null,
            'last_end' => null,
            'total_duration' => '00:00:00'
        ];
    }

    /**
     * Get operator fuel efficiency (liter per hectare)
     * 
     * @param string $companycode
     * @param string $operatorId
     * @param string $date
     * @return float
     */
    public function getOperatorFuelEfficiency(string $companycode, string $operatorId, string $date): float
    {
        $data = DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhhdrid', '=', 'lh.id');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid');
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->where('lk.operatorid', $operatorId)
            ->select([
                DB::raw('SUM(lk.solar) as total_solar'),
                DB::raw('SUM(ldp.luashasil) as total_luas')
            ])
            ->first();
        
        if (!$data || $data->total_luas == 0) {
            return 0;
        }
        
        return round($data->total_solar / $data->total_luas, 2);
    }

    /**
     * Get operator monthly performance
     * 
     * @param string $companycode
     * @param string $operatorId
     * @param string $monthYear (format: Y-m)
     * @return Collection
     */
    public function getOperatorMonthlyPerformance(string $companycode, string $operatorId, string $monthYear): Collection
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhhdrid', '=', 'lh.id');
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid');
            })
            ->where('lk.companycode', $companycode)
            ->where('lk.operatorid', $operatorId)
            ->whereRaw('DATE_FORMAT(lh.lkhdate, "%Y-%m") = ?', [$monthYear])
            ->select([
                DB::raw('DATE(lh.lkhdate) as work_date'),
                DB::raw('COUNT(DISTINCT ldp.plot) as plots_worked'),
                DB::raw('SUM(ldp.luashasil) as total_area'),
                DB::raw('SUM(lk.solar) as total_fuel'),
                DB::raw('SEC_TO_TIME(SUM(TIME_TO_SEC(TIMEDIFF(lk.jamselesai, lk.jammulai)))) as total_hours')
            ])
            ->groupBy('work_date')
            ->orderBy('work_date')
            ->get();
    }

    /**
     * Get all operators performance comparison for date
     * 
     * @param string $companycode
     * @param string $date
     * @return Collection
     */
    public function getOperatorsComparison(string $companycode, string $date): Collection
    {
        return DB::table('lkhdetailkendaraan as lk')
            ->join('lkhhdr as lh', function($join) {
                $join->on('lk.lkhhdrid', '=', 'lh.id');
            })
            ->join('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('lk.operatorid', '=', 'tk.tenagakerjaid')
                     ->where('tk.companycode', '=', $companycode)
                     ->where('tk.jenistenagakerja', '=', 3);
            })
            ->join('lkhdetailplot as ldp', function($join) {
                $join->on('lh.id', '=', 'ldp.lkhhdrid');
            })
            ->where('lk.companycode', $companycode)
            ->whereDate('lh.lkhdate', $date)
            ->select([
                'tk.tenagakerjaid',
                'tk.nama as operator_name',
                DB::raw('COUNT(DISTINCT ldp.plot) as total_plots'),
                DB::raw('SUM(ldp.luashasil) as total_area'),
                DB::raw('SUM(lk.solar) as total_fuel'),
                DB::raw('ROUND(SUM(lk.solar) / SUM(ldp.luashasil), 2) as fuel_per_ha')
            ])
            ->groupBy('tk.tenagakerjaid', 'tk.nama')
            ->orderBy('total_area', 'desc')
            ->get();
    }
}