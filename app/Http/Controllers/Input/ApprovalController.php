<?php

namespace App\Http\Controllers\Input;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// ? ADDED: Import Services for post-approval actions
use App\Services\LkhGeneratorService;
use App\Services\MaterialUsageGeneratorService;
use App\Services\GenerateNewBatchService;

/**
 * ApprovalController
 * 
 * Handles mobile-friendly approval interface for RKH and LKH
 * Simplified version without modals - direct list and action
 * 
 * FIXED VERSION: Now includes ALL post-approval actions from RencanaKerjaHarianController
 */
class ApprovalController extends Controller
{
    /**
     * Show approval dashboard with date filter
     * UPDATED: Added date filter support
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        if (!$this->validateUserForApproval($currentUser)) {
            return redirect()->route('home')
                ->with('error', 'Anda tidak memiliki akses untuk approval');
        }

        // Get filter parameters
        $filterDate = $request->input('filter_date');
        $allDate = $request->input('all_date', false);

        // Get pending RKH approvals with date filter
        $pendingRKH = $this->getPendingRKHApprovals($companycode, $currentUser, $filterDate, $allDate);
        
        // Get pending LKH approvals with date filter
        $pendingLKH = $this->getPendingLKHApprovals($companycode, $currentUser, $filterDate, $allDate);

        return view('input.approval.index', [
            'title' => 'Approval Center',
            'navbar' => 'Input',
            'nav' => 'Approval',
            'pendingRKH' => $pendingRKH,
            'pendingLKH' => $pendingLKH,
            'userInfo' => $this->getUserInfo($currentUser),
            'filterDate' => $filterDate,
            'allDate' => $allDate
        ]);
    }

    /**
     * Process RKH approval
     * FIXED: Now includes COMPLETE post-approval actions (LKH generation, Material usage, Batch creation)
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

            // ? CRITICAL: Wrap everything in transaction
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

            // Update approvalstatus based on action
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

            $message = 'RKH ' . $rkhno . ' berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // ? FIXED: Handle post-approval actions if fully approved (NOW COMPLETE)
            if ($action === 'approve' && ($updateData['approvalstatus'] ?? null) === '1') {
                $message = $this->handlePostApprovalActionsTransactional($rkhno, $message, $companycode);
            }

            DB::commit();
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
                    $updateData['approvalstatus'] = '1';
                } else {
                    $updateData['approvalstatus'] = null;
                }
            } else {
                $updateData['status'] = 'DECLINED';
                $updateData['approvalstatus'] = '0';
            }

            DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->update($updateData);

            $message = 'LKH ' . $lkhno . ' berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // ✅ NEW: Trigger batch generation setelah LKH fully approved
            if ($action === 'approve' && ($updateData['approvalstatus'] ?? null) === '1') {
                $batchService = new GenerateNewBatchService();
                $batchResult = $batchService->checkAndGenerate($lkhno, $companycode);
                
                if ($batchResult['success']) {
                    // Handle panen transitions (PC→RC1, RC1→RC2, RC2→RC3)
                    if (!empty($batchResult['transitions'])) {
                        foreach ($batchResult['transitions'] as $transition) {
                            if ($transition['success']) {
                                $message .= ". New Batch: {$transition['new_batchno']} ({$transition['lifecycle']}) for Plot {$transition['plot']}";
                            }
                        }
                    }
                    
                    // Handle planting PC batches (RC3→PC or new plot)
                    if (!empty($batchResult['batches'])) {
                        foreach ($batchResult['batches'] as $batch) {
                            if ($batch['success']) {
                                $message .= ". New PC Batch: {$batch['batchno']} for Plot {$batch['plot']}";
                            }
                        }
                    }
                } else {
                    // Log batch generation failure but don't block approval
                    Log::warning("Batch generation failed for LKH {$lkhno}", [
                        'message' => $batchResult['message'] ?? 'Unknown error'
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("LKH approval process failed: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses approval LKH: ' . $e->getMessage());
        }
    }

    // =====================================
    // ? FIXED: POST-APPROVAL ACTIONS - NOW COMPLETE
    // =====================================

    /**
     * Handle post-approval actions within existing transaction
     * FIXED: Now includes ALL 3 steps from RencanaKerjaHarianController
     */
    private function handlePostApprovalActionsTransactional($rkhno, $responseMessage, $companycode)
    {
        // Get updated RKH data
        $updatedRkh = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->first();

        if (!$this->isRkhFullyApproved($updatedRkh)) {
            return $responseMessage;
        }

        // STEP 1: Generate LKH (PASS COMPANYCODE)
        $lkhGenerator = new LkhGeneratorService();
        $lkhResult = $lkhGenerator->generateLkhFromRkh($rkhno, $companycode);
        
        if (!$lkhResult['success']) {
            $errorMsg = $lkhResult['message'] ?? 'Unknown error';
            
            Log::error("LKH auto-generation failed", [
                'rkhno' => $rkhno,
                'companycode' => $companycode,
                'error' => $errorMsg
            ]);
            
            throw new \Exception("LKH auto-generation gagal: {$errorMsg}. Approval dibatalkan untuk menjaga konsistensi data.");
        }
        
        $responseMessage .= '. LKH auto-generated (' . $lkhResult['total_lkh'] . ' LKH)';
        
        // STEP 2: Handle Planting Activities (Create Batch) - FIXED: NOW INCLUDED
        if ($this->hasPlantingActivities($rkhno, $companycode)) {
            try {
                $createdBatches = $this->handlePlantingActivity($rkhno, $companycode);
                if (!empty($createdBatches)) {
                    $responseMessage .= '. Batch penanaman dibuat: ' . implode(', ', $createdBatches);
                }
            } catch (\Exception $e) {
                Log::error("Batch creation failed", [
                    'rkhno' => $rkhno,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('Batch creation gagal: ' . $e->getMessage() . '. Approval dibatalkan untuk menjaga konsistensi data.');
            }
        }
        
        // STEP 3: Check if RKH needs material usage generation - FIXED: NOW INCLUDED
        $needsMaterialUsage = $this->checkIfRkhNeedsMaterialUsage($rkhno, $companycode);
        
        if (!$needsMaterialUsage) {
            $responseMessage .= '. No material usage required for this RKH';
            return $responseMessage;
        }

        // STEP 4: Generate Material Usage (CRITICAL - HARD FAILURE) - FIXED: NOW INCLUDED
        $materialUsageGenerator = new MaterialUsageGeneratorService();
        $materialResult = $materialUsageGenerator->generateMaterialUsageFromRkh($rkhno);
        
        if (!$materialResult['success']) {
            Log::error("Material usage auto-generation failed", [
                'rkhno' => $rkhno,
                'error' => $materialResult['message'] ?? 'Unknown error'
            ]);
            throw new \Exception('Material usage auto-generation gagal: ' . $materialResult['message'] . '. Approval dibatalkan untuk menjaga konsistensi data.');
        }
        
        if (($materialResult['total_items'] ?? 0) > 0) {
            $responseMessage .= '. Material usage auto-generated (' . $materialResult['total_items'] . ' items created)';
        } else {
            $responseMessage .= '. Material usage processed (no items needed)';
        }
        
        return $responseMessage;
    }

    /**
     * Check if RKH needs material usage generation
     * FIXED: NOW INCLUDED (copied from RencanaKerjaHarianController)
     */
    private function checkIfRkhNeedsMaterialUsage($rkhno, $companycode)
    {
        $hasMaterialUsage = DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->whereNotNull('herbisidagroupid')
            ->exists();
        
        return $hasMaterialUsage;
    }

    /**
     * Check if RKH has planting activities
     * FIXED: NOW INCLUDED (copied from RencanaKerjaHarianController)
     */
    private function hasPlantingActivities($rkhno, $companycode)
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->exists();
    }

    /**
     * Handle batch creation for planting activities
     * FIXED: NOW INCLUDED (copied from RencanaKerjaHarianController)
     */
    private function handlePlantingActivity($rkhno, $companycode)
    {
        $plantingPlots = DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->get();
        
        if ($plantingPlots->isEmpty()) {
            return [];
        }
        
        $createdBatches = [];
        
        foreach ($plantingPlots as $plotData) {
            try {
                // Generate batch number
                $batchNo = $this->generateBatchNo($companycode, $plotData->plot);
                
                // Create batch record
                DB::table('batch')->insert([
                    'batchno' => $batchNo,
                    'companycode' => $companycode,
                    'plot' => $plotData->plot,
                    'batchdate' => $plotData->rkhdate,
                    'batcharea' => $plotData->luasarea,
                    'plantingrkhno' => $rkhno,
                    'inputby' => Auth::user()->userid,
                    'createdat' => now()
                ]);
                
                // Update masterlist
                DB::table('masterlist')->updateOrInsert(
                    ['companycode' => $companycode, 'plot' => $plotData->plot],
                    [
                        'batchno' => $batchNo,
                        'batchdate' => $plotData->rkhdate,
                        'batcharea' => $plotData->luasarea,
                        'kodestatus' => 'PC',
                        'cyclecount' => DB::raw('COALESCE(cyclecount, 0) + 1'),
                        'isactive' => 1
                    ]
                );
                
                $createdBatches[] = $batchNo;
                
                Log::info("Batch created successfully", [
                    'batchno' => $batchNo,
                    'plot' => $plotData->plot,
                    'rkhno' => $rkhno
                ]);
                
            } catch (\Exception $e) {
                Log::error("Failed to create batch for plot {$plotData->plot}: " . $e->getMessage());
                throw $e;
            }
        }
        
        return $createdBatches;
    }

    /**
     * Generate unique batch number
     * FIXED: NOW INCLUDED (copied from RencanaKerjaHarianController)
     */
    private function generateBatchNo($companycode, $plot)
    {
        $date = date('dm');
        $year = date('y');
        
        // Get sequence for today
        $sequence = DB::table('batch')
            ->where('companycode', $companycode)
            ->whereDate('batchdate', date('Y-m-d'))
            ->count() + 1;
        
        return "BATCH{$date}{$plot}{$year}" . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    private function validateUserForApproval($currentUser)
    {
        return $currentUser && $currentUser->idjabatan;
    }

    /**
     * Get pending RKH approvals for current user
     * UPDATED: Include activity details, material usage, and kendaraan info
     */
    private function getPendingRKHApprovals($companycode, $currentUser, $filterDate = null, $allDate = false)
    {
        $query = DB::table('rkhhdr as r')
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
            });

        if (!$allDate && $filterDate) {
            $query->whereDate('r.rkhdate', $filterDate);
        }

        $results = $query->select([
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

        // ✅ Enrich with activity details, material & kendaraan info
        return $results->map(function($rkh) use ($companycode) {
            // Get activities dari rkhlst
            $activities = DB::table('rkhlst as l')
                ->join('activity as a', 'l.activitycode', '=', 'a.activitycode')
                ->where('l.companycode', $companycode)
                ->where('l.rkhno', $rkh->rkhno)
                ->select('a.activityname')
                ->distinct()
                ->pluck('activityname')
                ->join(', ');

            // Check material usage
            $hasMaterial = DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkh->rkhno)
                ->where('usingmaterial', 1)
                ->exists();

            // Check kendaraan usage
            $hasKendaraan = DB::table('rkhlstkendaraan')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkh->rkhno)
                ->exists();

            $rkh->activities_list = $activities ?: '-';
            $rkh->has_material = $hasMaterial;
            $rkh->has_kendaraan = $hasKendaraan;

            return $rkh;
        });
    }

    /**
     * Get pending LKH approvals for current user
     * UPDATED: Include activity name and material/kendaraan info
     */
    private function getPendingLKHApprovals($companycode, $currentUser, $filterDate = null, $allDate = false)
    {
        $query = DB::table('lkhhdr as h')
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
            });

        if (!$allDate && $filterDate) {
            $query->whereDate('h.lkhdate', $filterDate);
        }

        $results = $query->select([
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

        // ✅ Enrich with material & kendaraan info
        return $results->map(function($lkh) use ($companycode) {
            // Check material usage
            $hasMaterial = DB::table('lkhdetailmaterial')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->exists();

            // Check kendaraan usage
            $hasKendaraan = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->exists();

            $lkh->has_material = $hasMaterial;
            $lkh->has_kendaraan = $hasKendaraan;

            return $lkh;
        });
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