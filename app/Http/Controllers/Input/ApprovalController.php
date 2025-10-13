<?php

namespace App\Http\Controllers\Input;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * ApprovalController
 * 
 * Handles mobile-friendly approval interface for RKH and LKH
 * Simplified version without modals - direct list and action
 */
class ApprovalController extends Controller
{
    /**
     * Show approval dashboard
     */
    public function index()
    {
        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        if (!$this->validateUserForApproval($currentUser)) {
            return redirect()->route('home')
                ->with('error', 'Anda tidak memiliki akses untuk approval');
        }

        // Get pending RKH approvals
        $pendingRKH = $this->getPendingRKHApprovals($companycode, $currentUser);
        
        // Get pending LKH approvals
        $pendingLKH = $this->getPendingLKHApprovals($companycode, $currentUser);

        return view('input.approval.index', [
            'title' => 'Approval Center',
            'navbar' => 'Input',
            'nav' => 'Approval',
            'pendingRKH' => $pendingRKH,
            'pendingLKH' => $pendingLKH,
            'userInfo' => $this->getUserInfo($currentUser)
        ]);
    }

    /**
     * Process RKH approval
     */
    public function processRKHApproval(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            $rkhno = $request->rkhno;
            $action = $request->action;
            $level = $request->level;

            DB::beginTransaction();

            $rkh = DB::table('rkhhdr as r')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('r.activitygroup', '=', 'app.activitygroup')
                        ->where('app.companycode', '=', $companycode);
                })
                ->where('r.companycode', $companycode)
                ->where('r.rkhno', $rkhno)
                ->select(['r.*', 'app.jumlahapproval', 'app.idjabatanapproval1', 'app.idjabatanapproval2', 'app.idjabatanapproval3'])
                ->first();

            if (!$rkh) {
                DB::rollBack();
                return back()->with('error', 'RKH tidak ditemukan');
            }

            $validationResult = $this->validateApprovalAuthority($rkh, $currentUser, $level);
            if (!$validationResult['success']) {
                DB::rollBack();
                return back()->with('error', $validationResult['message']);
            }

            // Process approval
            $approvalValue = $action === 'approve' ? '1' : '0';
            $approvalField = "approval{$level}flag";
            $approvalDateField = "approval{$level}date";
            $approvalUserField = "approval{$level}userid";
            
            $updateData = [
                $approvalField => $approvalValue,
                $approvalDateField => now(),
                $approvalUserField => $currentUser->userid,
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ];

            if ($action === 'approve') {
                $tempRkh = clone $rkh;
                $tempRkh->$approvalField = '1';
                
                if ($this->isRkhFullyApproved($tempRkh)) {
                    $updateData['approvalstatus'] = '1';
                } else {
                    $updateData['approvalstatus'] = null;
                }
            } else {
                $updateData['approvalstatus'] = '0';
            }

            DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->update($updateData);

            DB::commit();

            $message = 'RKH ' . $rkhno . ' berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approval process failed: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses approval: ' . $e->getMessage());
        }
    }

    /**
     * Process LKH approval
     */
    public function processLKHApproval(Request $request)
    {
        $request->validate([
            'lkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            $lkhno = $request->lkhno;
            $action = $request->action;
            $level = $request->level;

            DB::beginTransaction();

            $lkh = DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->first();

            if (!$lkh) {
                DB::rollBack();
                return back()->with('error', 'LKH tidak ditemukan');
            }

            $validationResult = $this->validateLkhApprovalAuthority($lkh, $currentUser, $level);
            if (!$validationResult['success']) {
                DB::rollBack();
                return back()->with('error', $validationResult['message']);
            }

            // Process approval
            $approvalValue = $action === 'approve' ? '1' : '0';
            $approvalField = "approval{$level}flag";
            $approvalDateField = "approval{$level}date";
            $approvalUserField = "approval{$level}userid";
            
            $updateData = [
                $approvalField => $approvalValue,
                $approvalDateField => now(),
                $approvalUserField => $currentUser->userid,
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ];

            if ($action === 'approve') {
                $tempLkh = clone $lkh;
                $tempLkh->$approvalField = '1';
                
                if ($this->isLKHFullyApproved($tempLkh)) {
                    $updateData['status'] = 'APPROVED';
                }
            }

            DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->update($updateData);

            DB::commit();

            $message = 'LKH ' . $lkhno . ' berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("LKH approval process failed: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses approval LKH: ' . $e->getMessage());
        }
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    private function validateUserForApproval($currentUser)
    {
        return $currentUser && $currentUser->idjabatan;
    }

    private function getPendingRKHApprovals($companycode, $currentUser)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where(function($query) use ($currentUser) {
                $query->where(function($q) use ($currentUser) {
                    $q->where('app.idjabatanapproval1', $currentUser->idjabatan)->whereNull('r.approval1flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('app.idjabatanapproval2', $currentUser->idjabatan)->where('r.approval1flag', '1')->whereNull('r.approval2flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('app.idjabatanapproval3', $currentUser->idjabatan)->where('r.approval1flag', '1')->where('r.approval2flag', '1')->whereNull('r.approval3flag');
                });
            })
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                DB::raw('CASE 
                    WHEN app.idjabatanapproval1 = '.$currentUser->idjabatan.' AND r.approval1flag IS NULL THEN 1
                    WHEN app.idjabatanapproval2 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag IS NULL THEN 2
                    WHEN app.idjabatanapproval3 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('r.rkhdate', 'desc')
            ->get();
    }

    private function getPendingLKHApprovals($companycode, $currentUser)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.issubmit', 1)
            ->where(function($query) use ($currentUser) {
                $query->where(function($q) use ($currentUser) {
                    $q->where('h.approval1idjabatan', $currentUser->idjabatan)->whereNull('h.approval1flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('h.approval2idjabatan', $currentUser->idjabatan)->where('h.approval1flag', '1')->whereNull('h.approval2flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('h.approval3idjabatan', $currentUser->idjabatan)->where('h.approval1flag', '1')->where('h.approval2flag', '1')->whereNull('h.approval3flag');
                });
            })
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                DB::raw('CASE 
                    WHEN h.approval1idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag IS NULL THEN 1
                    WHEN h.approval2idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag = "1" AND h.approval2flag IS NULL THEN 2
                    WHEN h.approval3idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag = "1" AND h.approval2flag = "1" AND h.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('h.lkhdate', 'desc')
            ->get();
    }

    private function getUserInfo($currentUser)
    {
        $jabatan = DB::table('jabatan')->where('idjabatan', $currentUser->idjabatan)->first();
        
        return [
            'userid' => $currentUser->userid,
            'name' => $currentUser->name,
            'idjabatan' => $currentUser->idjabatan,
            'jabatan_name' => $jabatan ? $jabatan->namajabatan : 'Unknown'
        ];
    }

    private function validateApprovalAuthority($rkh, $currentUser, $level)
    {
        $approvalJabatanField = "idjabatanapproval{$level}";
        $approvalField = "approval{$level}flag";

        if (!isset($rkh->$approvalJabatanField) || $rkh->$approvalJabatanField != $currentUser->idjabatan) {
            return ['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk approve level ini'];
        }

        if (isset($rkh->$approvalField) && $rkh->$approvalField !== null) {
            return ['success' => false, 'message' => 'Approval level ini sudah diproses sebelumnya'];
        }

        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($rkh->$prevApprovalField) || $rkh->$prevApprovalField !== '1') {
                return ['success' => false, 'message' => 'Approval level sebelumnya belum disetujui'];
            }
        }

        return ['success' => true];
    }

    private function validateLkhApprovalAuthority($lkh, $currentUser, $level)
    {
        $approvalJabatanField = "approval{$level}idjabatan";
        $approvalField = "approval{$level}flag";

        if (!isset($lkh->$approvalJabatanField) || $lkh->$approvalJabatanField != $currentUser->idjabatan) {
            return ['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk approve level ini'];
        }

        if (isset($lkh->$approvalField) && $lkh->$approvalField !== null) {
            return ['success' => false, 'message' => 'Approval level ini sudah diproses sebelumnya'];
        }

        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($lkh->$prevApprovalField) || $lkh->$prevApprovalField !== '1') {
                return ['success' => false, 'message' => 'Approval level sebelumnya belum disetujui'];
            }
        }

        return ['success' => true];
    }

    private function isRkhFullyApproved($rkh)
    {
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

        switch ($rkh->jumlahapproval) {
            case 1:
                return $rkh->approval1flag === '1';
            case 2:
                return $rkh->approval1flag === '1' && $rkh->approval2flag === '1';
            case 3:
                return $rkh->approval1flag === '1' && 
                       $rkh->approval2flag === '1' && 
                       $rkh->approval3flag === '1';
            default:
                return false;
        }
    }

    private function isLKHFullyApproved($lkh)
    {
        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return true;
        }

        switch ($lkh->jumlahapproval) {
            case 1:
                return $lkh->approval1flag === '1';
            case 2:
                return $lkh->approval1flag === '1' && $lkh->approval2flag === '1';
            case 3:
                return $lkh->approval1flag === '1' && 
                       $lkh->approval2flag === '1' && 
                       $lkh->approval3flag === '1';
            default:
                return false;
        }
    }
}