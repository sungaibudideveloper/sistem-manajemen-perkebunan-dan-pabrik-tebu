<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Approval;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * LkhApprovalRepository
 * 
 * Handles ALL database operations related to LKH approval process.
 * RULE: All queries here, no queries in service/controller.
 */
class LkhApprovalRepository
{
    /**
     * Get pending LKH approvals for specific jabatan
     * 
     * @param string $companycode
     * @param int $idjabatan
     * @return \Illuminate\Support\Collection
     */
    public function getPendingApprovals($companycode, $idjabatan)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.issubmit', 1)
            ->where(function($query) use ($idjabatan) {
                $query->where(function($q) use ($idjabatan) {
                    $q->where('h.approval1idjabatan', $idjabatan)->whereNull('h.approval1flag');
                })->orWhere(function($q) use ($idjabatan) {
                    $q->where('h.approval2idjabatan', $idjabatan)->where('h.approval1flag', '1')->whereNull('h.approval2flag');
                })->orWhere(function($q) use ($idjabatan) {
                    $q->where('h.approval3idjabatan', $idjabatan)->where('h.approval1flag', '1')->where('h.approval2flag', '1')->whereNull('h.approval3flag');
                });
            })
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                DB::raw('CASE 
                    WHEN h.approval1idjabatan = '.$idjabatan.' AND h.approval1flag IS NULL THEN 1
                    WHEN h.approval2idjabatan = '.$idjabatan.' AND h.approval1flag = "1" AND h.approval2flag IS NULL THEN 2
                    WHEN h.approval3idjabatan = '.$idjabatan.' AND h.approval1flag = "1" AND h.approval2flag = "1" AND h.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('h.lkhdate', 'desc')
            ->get();
    }

    /**
     * Get LKH approval detail with all approval metadata
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getApprovalDetail($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('user as u1', 'h.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'h.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'h.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'h.approval1idjabatan', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'h.approval2idjabatan', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'h.approval3idjabatan', '=', 'j3.idjabatan')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                'h.jumlahapproval',
                'h.approval1idjabatan',
                'h.approval2idjabatan', 
                'h.approval3idjabatan',
                'u1.name as approval1_user_name',
                'u2.name as approval2_user_name',
                'u3.name as approval3_user_name',
                'j1.namajabatan as jabatan1_name',
                'j2.namajabatan as jabatan2_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Get LKH with approval setting for processing
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return object|null
     */
    public function getLkhWithApprovalSetting($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*', 
                'h.jumlahapproval',
                'h.approval1idjabatan',
                'h.approval2idjabatan',
                'h.approval3idjabatan'
            ])
            ->first();
    }

    /**
     * Update approval flag for specific level
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param int $level
     * @param string $flag ('1' or '0')
     * @param string $userid
     * @param Carbon $now
     * @return int
     */
    public function updateApprovalFlag($companycode, $lkhno, $level, $flag, $userid, $now)
    {
        $approvalField = "approval{$level}flag";
        $approvalDateField = "approval{$level}date";
        $approvalUserField = "approval{$level}userid";
        
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update([
                $approvalField => $flag,
                $approvalDateField => $now,
                $approvalUserField => $userid,
                'updateby' => $userid,
                'updatedat' => $now
            ]);
    }

    /**
     * Set final approval status and LKH status
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param string $status ('APPROVED' or 'DECLINED')
     * @param string|null $approvalstatus ('1', '0', or null)
     * @return int
     */
    public function setStatusApprovedOrDeclined($companycode, $lkhno, $status, $approvalstatus)
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update([
                'status' => $status,
                'approvalstatus' => $approvalstatus
            ]);
    }
}