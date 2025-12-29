<?php

namespace App\Http\Controllers\Transaction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

// Import Services for post-approval actions
use App\Services\Transaction\RencanaKerjaHarian\Generator\LkhGeneratorService;
use App\Services\Transaction\RencanaKerjaHarian\Generator\MaterialUsageGeneratorService;
use App\Services\Transaction\RencanaKerjaHarian\Generator\GenerateNewBatchService;
use App\Services\SplitMergePlotService;

/**
 * ApprovalController
 * 
 * Handles mobile-friendly approval interface for:
 * 1. RKH (daily work plan)
 * 2. LKH (daily work report)
 * 3. Other Approvals (Split/Merge, Purchase Request, etc)
 */
class ApprovalController extends Controller
{
    /**
     * Show approval dashboard with date filter
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

        // Get pending approvals
        $pendingRKH = $this->getPendingRKHApprovals($companycode, $currentUser, $filterDate, $allDate);
        $pendingLKH = $this->getPendingLKHApprovals($companycode, $currentUser, $filterDate, $allDate);
        $pendingOther = $this->getPendingOtherApprovals($companycode, $currentUser, $filterDate, $allDate);

        return view('transaction.approval.index', [
            'title' => 'Approval Center',
            'navbar' => 'Input',
            'nav' => 'Approval',
            'pendingRKH' => $pendingRKH,
            'pendingLKH' => $pendingLKH,
            'pendingOther' => $pendingOther,
            'userInfo' => $this->getUserInfo($currentUser),
            'filterDate' => $filterDate,
            'allDate' => $allDate
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

            // Handle post-approval actions if fully approved
            if ($action === 'approve' && ($updateData['approvalstatus'] ?? null) === '1') {
                Log::info("Starting post-approval actions", [
                    'rkhno' => $rkhno,
                    'companycode' => $companycode
                ]);
                
                try {
                    $message = $this->handlePostApprovalActionsTransactional($rkhno, $message, $companycode);
                    Log::info("Post-approval actions completed successfully", ['rkhno' => $rkhno]);
                } catch (\Exception $e) {
                    Log::error("Post-approval actions failed", [
                        'rkhno' => $rkhno,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
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

            // Trigger batch generation setelah LKH fully approved
            if ($action === 'approve' && ($updateData['approvalstatus'] ?? null) === '1') {
                $batchService = new GenerateNewBatchService();
                $batchResult = $batchService->checkAndGenerate($lkhno, $companycode);
                
                if ($batchResult['success']) {
                    if (!empty($batchResult['transitions'])) {
                        foreach ($batchResult['transitions'] as $transition) {
                            if ($transition['success']) {
                                $message .= ". New Batch: {$transition['new_batchno']} ({$transition['lifecycle']}) for Plot {$transition['plot']}";
                            }
                        }
                    }
                    
                    if (!empty($batchResult['batches'])) {
                        foreach ($batchResult['batches'] as $batch) {
                            if ($batch['success']) {
                                $message .= ". New PC Batch: {$batch['batchno']} for Plot {$batch['plot']}";
                            }
                        }
                    }
                } else {
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

    /**
     * Process Other Approval (Split/Merge, Purchase Request, etc)
     * NEW METHOD
     */
    public function processOtherApproval(Request $request)
    {
        $request->validate([
            'approvalno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            $approvalno = $request->approvalno;
            $action = $request->action;
            $level = $request->level;

            DB::beginTransaction();

            // Get approval transaction
            $approval = DB::table('approvaltransaction as at')
                ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
                ->where('at.companycode', $companycode)
                ->where('at.approvalno', $approvalno)
                ->select(['at.*', 'am.category'])
                ->first();

            if (!$approval) {
                DB::rollBack();
                return back()->with('error', 'Approval tidak ditemukan');
            }

            // Validate approval authority
            $validationResult = $this->validateOtherApprovalAuthority($approval, $currentUser, $level);
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
                $tempApproval = clone $approval;
                $tempApproval->$approvalField = '1';
                
                if ($this->isOtherApprovalFullyApproved($tempApproval)) {
                    $updateData['approvalstatus'] = '1';
                } else {
                    $updateData['approvalstatus'] = null;
                }
            } else {
                $updateData['approvalstatus'] = '0';
            }

            DB::table('approvaltransaction')
                ->where('companycode', $companycode)
                ->where('approvalno', $approvalno)
                ->update($updateData);

            $message = "{$approval->category} [{$approval->transactionnumber}] berhasil " . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Execute post-approval actions if fully approved
            if ($action === 'approve' && ($updateData['approvalstatus'] ?? null) === '1') {
                $message = $this->executeOtherApprovalActions($approval, $message, $companycode);
            }

            DB::commit();
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Other approval process failed: " . $e->getMessage());
            return back()->with('error', 'Gagal memproses approval: ' . $e->getMessage());
        }
    }

    // =====================================
    // PRIVATE HELPER METHODS - RKH
    // =====================================

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

        return $results->map(function($rkh) use ($companycode) {
            $activities = DB::table('rkhlst as l')
                ->join('activity as a', 'l.activitycode', '=', 'a.activitycode')
                ->where('l.companycode', $companycode)
                ->where('l.rkhno', $rkh->rkhno)
                ->select('a.activityname')
                ->distinct()
                ->pluck('activityname')
                ->join(', ');

            $hasMaterial = DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkh->rkhno)
                ->where('usingmaterial', 1)
                ->exists();

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

    private function handlePostApprovalActionsTransactional($rkhno, $responseMessage, $companycode)
    {
        Log::info("STEP 0: Checking RKH approval status", ['rkhno' => $rkhno]);
        
        $updatedRkh = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->first();

        if (!$this->isRkhFullyApproved($updatedRkh)) {
            Log::warning("RKH not fully approved, skipping post-actions", ['rkhno' => $rkhno]);
            return $responseMessage;
        }

        // STEP 1: Generate LKH
        Log::info("STEP 1: Starting LKH generation", ['rkhno' => $rkhno]);
        try {
            $lkhGenerator = new LkhGeneratorService();
            $lkhResult = $lkhGenerator->generateLkhFromRkh($rkhno, $companycode);
            
            if (!$lkhResult['success']) {
                throw new \Exception("LKH auto-generation gagal: " . ($lkhResult['message'] ?? 'Unknown error'));
            }
            
            Log::info("STEP 1 SUCCESS: LKH generated", [
                'rkhno' => $rkhno,
                'total_lkh' => $lkhResult['total_lkh']
            ]);
            
            $responseMessage .= '. LKH auto-generated (' . $lkhResult['total_lkh'] . ' LKH)';
        } catch (\Exception $e) {
            Log::error("STEP 1 FAILED: LKH generation error", [
                'rkhno' => $rkhno,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
        
        // STEP 2: Handle Planting Activities
        Log::info("STEP 2: Checking planting activities", ['rkhno' => $rkhno]);
        if ($this->hasPlantingActivities($rkhno, $companycode)) {
            try {
                Log::info("STEP 2: Starting batch creation for planting", ['rkhno' => $rkhno]);
                $createdBatches = $this->handlePlantingActivity($rkhno, $companycode);
                if (!empty($createdBatches)) {
                    $responseMessage .= '. Batch penanaman dibuat: ' . implode(', ', $createdBatches);
                    Log::info("STEP 2 SUCCESS: Batches created", [
                        'rkhno' => $rkhno,
                        'batches' => $createdBatches
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("STEP 2 FAILED: Batch creation error", [
                    'rkhno' => $rkhno,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('Batch creation gagal: ' . $e->getMessage());
            }
        } else {
            Log::info("STEP 2 SKIPPED: No planting activities", ['rkhno' => $rkhno]);
        }
        
        // STEP 3: Generate Material Usage
        Log::info("STEP 3: Checking material usage requirements", ['rkhno' => $rkhno]);
        $needsMaterialUsage = $this->checkIfRkhNeedsMaterialUsage($rkhno, $companycode);
        
        if ($needsMaterialUsage) {
            try {
                Log::info("STEP 3: Starting material usage generation", ['rkhno' => $rkhno]);
                $materialUsageGenerator = new MaterialUsageGeneratorService();
                $materialResult = $materialUsageGenerator->generateMaterialUsageFromRkh($rkhno);
                
                if (!$materialResult['success']) {
                    throw new \Exception('Material usage auto-generation gagal: ' . $materialResult['message']);
                }
                
                if (($materialResult['total_items'] ?? 0) > 0) {
                    $responseMessage .= '. Material usage auto-generated (' . $materialResult['total_items'] . ' items)';
                    Log::info("STEP 3 SUCCESS: Material usage generated", [
                        'rkhno' => $rkhno,
                        'total_items' => $materialResult['total_items']
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("STEP 3 FAILED: Material usage generation error", [
                    'rkhno' => $rkhno,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        } else {
            Log::info("STEP 3 SKIPPED: No material usage needed", ['rkhno' => $rkhno]);
        }
        
        Log::info("All post-approval steps completed", ['rkhno' => $rkhno]);
        return $responseMessage;
    }

    private function checkIfRkhNeedsMaterialUsage($rkhno, $companycode)
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->whereNotNull('herbisidagroupid')
            ->exists();
    }

    private function hasPlantingActivities($rkhno, $companycode)
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->exists();
    }

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
            $batchNo = $this->generateBatchNo($companycode, $plotData->plot);
            
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
        }
        
        return $createdBatches;
    }

    private function generateBatchNo($companycode, $plot)
    {
        $date = date('dm');
        $year = date('y');
        
        $sequence = DB::table('batch')
            ->where('companycode', $companycode)
            ->whereDate('batchdate', date('Y-m-d'))
            ->count() + 1;
        
        return "BATCH{$date}{$plot}{$year}" . str_pad($sequence, 2, '0', STR_PAD_LEFT);
    }

    // =====================================
    // PRIVATE HELPER METHODS - LKH
    // =====================================

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

        return $results->map(function($lkh) use ($companycode) {
            $hasMaterial = DB::table('lkhdetailmaterial')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->exists();

            $hasKendaraan = DB::table('lkhdetailkendaraan')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->exists();

            $lkh->has_material = $hasMaterial;
            $lkh->has_kendaraan = $hasKendaraan;

            return $lkh;
        });
    }

    // =====================================
    // PRIVATE HELPER METHODS - OTHER APPROVALS
    // =====================================

    /**
     * Get pending other approvals (Split/Merge, Purchase Request, etc)
     */
    private function getPendingOtherApprovals($companycode, $currentUser, $filterDate = null, $allDate = false)
    {
        $query = DB::table('approvaltransaction as at')
            ->join('approval as am', 'at.approvalcategoryid', '=', 'am.id')
            ->leftJoin('plottransaction as pt', function($join) use ($companycode) {
                $join->on('at.transactionnumber', '=', 'pt.transactionnumber')
                    ->where('pt.companycode', '=', $companycode);
            })
            ->leftJoin('user as u', 'at.inputby', '=', 'u.userid')
            ->where('at.companycode', $companycode)
            ->where(function($query) use ($currentUser) {
                $query->where(function($q) use ($currentUser) {
                    $q->where('at.approval1idjabatan', $currentUser->idjabatan)->whereNull('at.approval1flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('at.approval2idjabatan', $currentUser->idjabatan)->where('at.approval1flag', '1')->whereNull('at.approval2flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('at.approval3idjabatan', $currentUser->idjabatan)->where('at.approval1flag', '1')->where('at.approval2flag', '1')->whereNull('at.approval3flag');
                });
            });

        if (!$allDate && $filterDate) {
            $query->whereDate('at.createdat', $filterDate);
        }

        $results = $query->select([
                'at.*',
                'am.category',
                'u.name as inputby_name',
                'pt.transactiontype',
                'pt.sourceplots',
                'pt.resultplots',
                'pt.sourcebatches',
                'pt.resultbatches',
                'pt.areamap',
                'pt.dominantplot',
                'pt.splitmergedreason',
                DB::raw("DATE_FORMAT(pt.transactiondate, '%d/%m/%Y') as formatted_date"),
                DB::raw('CASE 
                    WHEN at.approval1idjabatan = '.$currentUser->idjabatan.' AND at.approval1flag IS NULL THEN 1
                    WHEN at.approval2idjabatan = '.$currentUser->idjabatan.' AND at.approval1flag = "1" AND at.approval2flag IS NULL THEN 2
                    WHEN at.approval3idjabatan = '.$currentUser->idjabatan.' AND at.approval1flag = "1" AND at.approval2flag = "1" AND at.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('at.createdat', 'desc')
            ->get();

        // Decode JSON for display dan ambil area REAL dari batch
        return $results->map(function($approval) use ($companycode) {
            if ($approval->sourceplots) {
                $approval->sourceplots_array = json_decode($approval->sourceplots, true);
            }
            if ($approval->resultplots) {
                $approval->resultplots_array = json_decode($approval->resultplots, true);
            }
            if ($approval->sourcebatches) {
                $approval->sourcebatches_array = json_decode($approval->sourcebatches, true);
                
                // TAMBAHAN: Fetch REAL batch area dari database
                $batchAreas = [];
                foreach ($approval->sourcebatches_array as $batchno) {
                    $batch = DB::table('batch')
                        ->where('companycode', $companycode)
                        ->where('batchno', $batchno)
                        ->select('plot', 'batcharea')
                        ->first();
                    
                    if ($batch) {
                        $batchAreas[$batch->plot] = $batch->batcharea;
                    }
                }
                $approval->real_batch_areas = $batchAreas;
            }
            if ($approval->resultbatches) {
                $approval->resultbatches_array = json_decode($approval->resultbatches, true);
            }
            if ($approval->areamap) {
                $approval->areamap_array = json_decode($approval->areamap, true);
            }
            return $approval;
        });
    }

    /**
     * Validate other approval authority
     */
    private function validateOtherApprovalAuthority($approval, $currentUser, $level)
    {
        $approvalJabatanField = "approval{$level}idjabatan";
        $approvalField = "approval{$level}flag";

        if (!isset($approval->$approvalJabatanField) || $approval->$approvalJabatanField != $currentUser->idjabatan) {
            return ['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk approve level ini'];
        }

        if (isset($approval->$approvalField) && $approval->$approvalField !== null) {
            return ['success' => false, 'message' => 'Approval level ini sudah diproses sebelumnya'];
        }

        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($approval->$prevApprovalField) || $approval->$prevApprovalField !== '1') {
                return ['success' => false, 'message' => 'Approval level sebelumnya belum disetujui'];
            }
        }

        return ['success' => true];
    }

    /**
     * Check if other approval is fully approved
     */
    private function isOtherApprovalFullyApproved($approval)
    {
        if (!$approval->jumlahapproval || $approval->jumlahapproval == 0) {
            return true;
        }

        switch ($approval->jumlahapproval) {
            case 1:
                return $approval->approval1flag === '1';
            case 2:
                return $approval->approval1flag === '1' && $approval->approval2flag === '1';
            case 3:
                return $approval->approval1flag === '1' && 
                       $approval->approval2flag === '1' && 
                       $approval->approval3flag === '1';
            default:
                return false;
        }
    }

    /**
     * Execute post-approval actions for other approvals
     */
    private function executeOtherApprovalActions($approval, $responseMessage, $companycode)
    {
        // Get transaction data
        $transaction = DB::table('plottransaction')
            ->where('companycode', $companycode)
            ->where('transactionnumber', $approval->transactionnumber)
            ->first();

        if (!$transaction) {
            throw new \Exception("Transaction {$approval->transactionnumber} tidak ditemukan");
        }

        // Execute based on transaction type
        $service = new SplitMergePlotService();

        if ($transaction->transactiontype === 'SPLIT') {
            $result = $service->executeSplit($approval->transactionnumber, $companycode);
            if ($result['success']) {
                $responseMessage .= '. ' . $result['message'];
            } else {
                throw new \Exception($result['message']);
            }
        } elseif ($transaction->transactiontype === 'MERGE') {
            $result = $service->executeMerge($approval->transactionnumber, $companycode);
            if ($result['success']) {
                $responseMessage .= '. ' . $result['message'];
            } else {
                throw new \Exception($result['message']);
            }
        }

        return $responseMessage;
    }

    // =====================================
    // COMMON HELPER METHODS
    // =====================================

    private function validateUserForApproval($currentUser)
    {
        return $currentUser && $currentUser->idjabatan;
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