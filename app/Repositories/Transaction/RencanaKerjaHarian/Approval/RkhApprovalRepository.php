<?php

namespace App\Repositories\Transaction\RencanaKerjaHarian\Approval;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * RkhApprovalRepository
 * 
 * Handles ALL database operations related to RKH approval process.
 * RULE: All queries here, no queries in service/controller.
 */
class RkhApprovalRepository
{
    /**
     * Get pending RKH approvals for specific jabatan
     * 
     * @param string $companycode
     * @param int $idjabatan
     * @return \Illuminate\Support\Collection
     */
    public function getPendingApprovals($companycode, $idjabatan)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where(function($query) use ($idjabatan) {
                $query->where(function($q) use ($idjabatan) {
                    $q->where('app.idjabatanapproval1', $idjabatan)->whereNull('r.approval1flag');
                })->orWhere(function($q) use ($idjabatan) {
                    $q->where('app.idjabatanapproval2', $idjabatan)->where('r.approval1flag', '1')->whereNull('r.approval2flag');
                })->orWhere(function($q) use ($idjabatan) {
                    $q->where('app.idjabatanapproval3', $idjabatan)->where('r.approval1flag', '1')->where('r.approval2flag', '1')->whereNull('r.approval3flag');
                });
            })
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                DB::raw('CASE 
                    WHEN app.idjabatanapproval1 = '.$idjabatan.' AND r.approval1flag IS NULL THEN 1
                    WHEN app.idjabatanapproval2 = '.$idjabatan.' AND r.approval1flag = "1" AND r.approval2flag IS NULL THEN 2
                    WHEN app.idjabatanapproval3 = '.$idjabatan.' AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('r.rkhdate', 'desc')
            ->get();
    }

    /**
     * Get RKH approval detail with all approval metadata
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function getApprovalDetail($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('user as u1', 'r.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'r.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'r.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'app.idjabatanapproval1', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'app.idjabatanapproval2', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'app.idjabatanapproval3', '=', 'j3.idjabatan')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2', 
                'app.idjabatanapproval3',
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
     * Get RKH with approval setting for processing
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return object|null
     */
    public function getRkhWithApprovalSetting($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*', 
                'app.jumlahapproval', 
                'app.idjabatanapproval1', 
                'app.idjabatanapproval2', 
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * Update approval flag for specific level
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param int $level
     * @param string $flag ('1' or '0')
     * @param string $userid
     * @param Carbon $now
     * @return int
     */
    public function updateApprovalFlag($companycode, $rkhno, $level, $flag, $userid, $now)
    {
        $approvalField = "approval{$level}flag";
        $approvalDateField = "approval{$level}date";
        $approvalUserField = "approval{$level}userid";
        
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update([
                $approvalField => $flag,
                $approvalDateField => $now,
                $approvalUserField => $userid,
                'updateby' => $userid,
                'updatedat' => $now
            ]);
    }

    /**
     * Set final approval status (0=rejected, 1=approved, null=pending)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param string|null $approvalstatus
     * @return int
     */
    public function setApprovalStatus($companycode, $rkhno, $approvalstatus)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update(['approvalstatus' => $approvalstatus]);
    }

    /**
     * Check if RKH needs material usage generation
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function needsMaterialUsage($companycode, $rkhno)
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->whereNotNull('herbisidagroupid')
            ->exists();
    }

    /**
     * Check if RKH has planting activities (2.2.7)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return bool
     */
    public function hasPlantingActivities($companycode, $rkhno)
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->exists();
    }

    /**
     * Get RKH progress status from LKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function getProgressStatusFromLkh($companycode, $rkhno)
    {
        $lkhData = DB::table('lkhhdr')
            ->where('rkhno', $rkhno)
            ->where('companycode', $companycode)
            ->get();
        
        if ($lkhData->isEmpty()) {
            return [
                'status' => 'no_lkh',
                'progress' => 'No LKH Created',
                'can_complete' => false,
                'color' => 'gray'
            ];
        }
        
        $totalLkh = $lkhData->count();
        $completedLkh = $lkhData->where('status', 'APPROVED')->count();
        
        if ($completedLkh === $totalLkh) {
            return [
                'status' => 'complete',
                'progress' => 'All Complete',
                'can_complete' => true,
                'color' => 'green'
            ];
        } else {
            return [
                'status' => 'in_progress',
                'progress' => "LKH In Progress ({$completedLkh}/{$totalLkh})",
                'can_complete' => false,
                'color' => 'yellow'
            ];
        }
    }

    /**
     * Update RKH status (Completed/In Progress)
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param string $status
     * @param string $userid
     * @param Carbon $now
     * @return int
     */
    public function updateStatus($companycode, $rkhno, $status, $userid, $now)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->update([
                'status' => $status,
                'updateby' => $userid,
                'updatedat' => $now
            ]);
    }
}