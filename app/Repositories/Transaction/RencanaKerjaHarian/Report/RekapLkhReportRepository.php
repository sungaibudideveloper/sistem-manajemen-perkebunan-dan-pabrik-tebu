<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Report;

use Illuminate\Support\Facades\DB;

/**
 * RekapLkhReportRepository
 * 
 * Handles LKH Rekap report queries.
 * FIXED: 
 * - Only show APPROVED LKH (approvalstatus = '1')
 * - Remove dependency on deleted 'plot' table
 * - Add summary statistics for header
 */
class RekapLkhReportRepository
{
    /**
     * Get all LKH rows for specific date (ONLY APPROVED)
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
            // ✅ Get luasarea from batch via lkhdetailplot
            ->leftJoin('batch as b', function($join) use ($companycode) {
                $join->on('ldp.batchid', '=', 'b.id')
                    ->where('b.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            // ✅ CRITICAL: Only show APPROVED LKH
            ->where('h.approvalstatus', '1')
            ->select([
                'h.lkhno',
                'h.activitycode',
                'h.totalworkers',
                'ldp.luashasil as totalhasil',
                'h.totalupahall',
                'a.activityname',
                'a.activitygroup',
                'ldp.plot',
                // ✅ Get luasarea from batch.batcharea
                'b.batcharea as luasarea',
                'u.name as mandor_nama',
                'tk.nama as operator_nama'
            ])
            ->orderBy('h.activitycode')
            ->orderBy('h.lkhno')
            ->get();
    }

    /**
     * Get LKH summary statistics for specific date
     * Returns: total LKH (all), approved LKH, percentage, total hasil, total workers
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getLkhSummaryForDate($companycode, $date)
    {
        // Total LKH pada hari ini (semua status)
        $totalAllLkh = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('lkhdate', $date)
            ->count();

        // Total LKH yang sudah approved
        $totalApprovedLkh = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('lkhdate', $date)
            ->where('approvalstatus', '1')
            ->count();

        // Calculate percentage
        $approvalPercentage = $totalAllLkh > 0 
            ? round(($totalApprovedLkh / $totalAllLkh) * 100, 1)
            : 0;

        // Total hasil & workers (only approved)
        $approvedStats = DB::table('lkhhdr as h')
            ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                $join->on('h.lkhno', '=', 'ldp.lkhno')
                    ->where('ldp.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->where('h.approvalstatus', '1')
            ->select([
                DB::raw('SUM(COALESCE(ldp.luashasil, 0)) as total_hasil'),
                DB::raw('SUM(h.totalworkers) as total_workers')
            ])
            ->first();

        return [
            'total_all_lkh' => $totalAllLkh,
            'total_approved_lkh' => $totalApprovedLkh,
            'approval_percentage' => $approvalPercentage,
            'total_hasil' => (float) ($approvedStats->total_hasil ?? 0),
            'total_workers' => (int) ($approvedStats->total_workers ?? 0),
        ];
    }
}