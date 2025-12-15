<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Approval;

use App\Repositories\Transaction\RencanaKerjaHarian\Approval\LkhApprovalRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\LkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * LkhApprovalService
 * 
 * Orchestrates LKH approval business logic.
 * RULE: No DB queries allowed. Only orchestration + business rules.
 */
class LkhApprovalService
{
    protected $lkhApprovalRepo;
    protected $lkhRepo;
    protected $masterDataRepo;

    public function __construct(
        LkhApprovalRepository $lkhApprovalRepo,
        LkhRepository $lkhRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->lkhApprovalRepo = $lkhApprovalRepo;
        $this->lkhRepo = $lkhRepo;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Get pending approvals for current user
     * 
     * @param object $currentUser
     * @param string $companycode
     * @return array
     */
    public function getPendingApprovals($currentUser, $companycode)
    {
        $pendingLKH = $this->lkhApprovalRepo->getPendingApprovals($companycode, $currentUser->idjabatan);
        
        // Batch-load plots to avoid N+1
        $lkhNos = $pendingLKH->pluck('lkhno')->toArray();
        $plotsByLkh = $this->lkhRepo->getPlotsByLkhNos($companycode, $lkhNos);
        
        return $this->formatPendingApprovalsData($pendingLKH, $plotsByLkh);
    }

    /**
     * Get approval detail for specific LKH
     * 
     * @param string $lkhno
     * @param string $companycode
     * @return array|null
     */
    public function getApprovalDetail($lkhno, $companycode)
    {
        $lkh = $this->lkhApprovalRepo->getApprovalDetail($companycode, $lkhno);

        if (!$lkh) {
            return null;
        }

        return $this->formatApprovalDetailData($lkh);
    }

    /**
     * Process approval (approve/decline)
     * 
     * @param string $lkhno
     * @param string $action
     * @param int $level
     * @param object $currentUser
     * @param string $companycode
     * @return array
     */
    public function processApproval($lkhno, $action, $level, $currentUser, $companycode)
    {
        try {
            DB::beginTransaction();

            // Get LKH with approval settings
            $lkh = $this->lkhApprovalRepo->getLkhWithApprovalSetting($companycode, $lkhno);

            if (!$lkh) {
                DB::rollBack();
                return ['success' => false, 'message' => 'LKH tidak ditemukan'];
            }

            // Validate authority
            $validationResult = $this->validateApprovalAuthority($lkh, $currentUser, $level);
            if (!$validationResult['success']) {
                DB::rollBack();
                return $validationResult;
            }

            // Update approval flag
            $approvalValue = $action === 'approve' ? '1' : '0';
            $now = now();
            
            $this->lkhApprovalRepo->updateApprovalFlag(
                $companycode, 
                $lkhno, 
                $level, 
                $approvalValue, 
                $currentUser->userid, 
                $now
            );

            // Determine final status
            $statusData = $this->determineFinalStatus($lkh, $level, $action);
            $this->lkhApprovalRepo->setStatusApprovedOrDeclined(
                $companycode, 
                $lkhno, 
                $statusData['status'], 
                $statusData['approvalstatus']
            );

            $responseMessage = 'LKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Handle post-approval actions if fully approved
            if ($action === 'approve' && $statusData['approvalstatus'] === '1') {
                $responseMessage = $this->handlePostApprovalActions($lkhno, $companycode, $responseMessage);
            }

            DB::commit();
            return ['success' => true, 'message' => $responseMessage];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("LKH approval process failed for {$lkhno}", [
                'user' => $currentUser->userid,
                'action' => $action,
                'level' => $level,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Proses approval gagal: ' . $e->getMessage()
            ];
        }
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Format pending approvals data
     */
    private function formatPendingApprovalsData($pendingLKH, $plotsByLkh)
    {
        return $pendingLKH->map(function($lkh) use ($plotsByLkh) {
            // Get plots from pre-loaded data
            $plots = ($plotsByLkh[$lkh->lkhno] ?? collect())
                ->map(function($item) {
                    return $item->blok . '-' . $item->plot;
                })
                ->join(', ');

            return [
                'lkhno' => $lkh->lkhno,
                'rkhno' => $lkh->rkhno,
                'lkhdate' => $lkh->lkhdate,
                'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
                'mandor_nama' => $lkh->mandor_nama,
                'activityname' => $lkh->activityname ?? 'Unknown Activity',
                'approval_level' => $lkh->approval_level,
                'status' => $lkh->status,
                'total_workers' => $lkh->totalworkers,
                'total_hasil' => $lkh->totalhasil,
                'plots' => $plots ?: 'No plots'
            ];
        })->toArray();
    }

    /**
     * Format approval detail data
     */
    private function formatApprovalDetailData($lkh)
    {
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $lkh->{"approval{$i}idjabatan"};
            if (!$jabatanId) continue;

            $flagField = "approval{$i}flag";
            $dateField = "approval{$i}date";
            $userField = "approval{$i}_user_name";
            $jabatanField = "jabatan{$i}_name";

            $flag = $lkh->$flagField;
            $status = 'waiting';
            $statusText = 'Waiting';

            if ($flag === '1') {
                $status = 'approved';
                $statusText = 'Approved';
            } elseif ($flag === '0') {
                $status = 'declined';
                $statusText = 'Declined';
            }

            $levels[] = [
                'level' => $i,
                'jabatan_name' => $lkh->$jabatanField ?? 'Unknown',
                'status' => $status,
                'status_text' => $statusText,
                'user_name' => $lkh->$userField ?? null,
                'date_formatted' => $lkh->$dateField ? Carbon::parse($lkh->$dateField)->format('d/m/Y H:i') : null
            ];
        }

        return [
            'lkhno' => $lkh->lkhno,
            'rkhno' => $lkh->rkhno,
            'lkhdate' => $lkh->lkhdate,
            'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
            'mandor_nama' => $lkh->mandor_nama,
            'activityname' => $lkh->activityname ?? 'Unknown Activity',
            'jumlah_approval' => $lkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }

    /**
     * Validate approval authority
     */
    private function validateApprovalAuthority($lkh, $currentUser, $level)
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

    /**
     * Determine final status based on current action
     */
    private function determineFinalStatus($lkh, $level, $action)
    {
        if ($action === 'decline') {
            return [
                'status' => 'DECLINED',
                'approvalstatus' => '0'
            ];
        }

        // Simulate approval to check if fully approved
        $tempLkh = clone $lkh;
        $approvalField = "approval{$level}flag";
        $tempLkh->$approvalField = '1';

        if ($this->isLkhFullyApproved($tempLkh)) {
            return [
                'status' => 'APPROVED',
                'approvalstatus' => '1'
            ];
        }

        // Still pending
        return [
            'status' => 'SUBMITTED',
            'approvalstatus' => null
        ];
    }

    /**
     * Check if LKH is fully approved
     */
    private function isLkhFullyApproved($lkh)
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

    /**
     * Handle post-approval actions (batch generation)
     */
    private function handlePostApprovalActions($lkhno, $companycode, $responseMessage)
    {
        // Trigger batch generation
        $batchService = new \App\Services\GenerateNewBatchService();
        $batchResult = $batchService->checkAndGenerate($lkhno, $companycode);
        
        if ($batchResult['success']) {
            // Handle panen transitions (PC→RC1, RC1→RC2, RC2→RC3)
            if (!empty($batchResult['transitions'])) {
                foreach ($batchResult['transitions'] as $transition) {
                    if ($transition['success']) {
                        $responseMessage .= ". New Batch: {$transition['new_batchno']} ({$transition['lifecycle']}) for Plot {$transition['plot']}";
                    }
                }
            }
            
            // Handle planting PC batches (RC3→PC or new plot)
            if (!empty($batchResult['batches'])) {
                foreach ($batchResult['batches'] as $batch) {
                    if ($batch['success']) {
                        $responseMessage .= ". New PC Batch: {$batch['batchno']} for Plot {$batch['plot']}";
                    }
                }
            }
        } else {
            // Log batch generation failure but don't block approval
            Log::warning("Batch generation failed for LKH {$lkhno}", [
                'message' => $batchResult['message'] ?? 'Unknown error'
            ]);
        }
        
        return $responseMessage;
    }
}