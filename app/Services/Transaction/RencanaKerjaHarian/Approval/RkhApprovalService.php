<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Approval;

use App\Repositories\Transaction\RencanaKerjaHarian\Approval\RkhApprovalRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use App\Services\LkhGeneratorService;
use App\Services\MaterialUsageGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * RkhApprovalService
 * 
 * Orchestrates RKH approval business logic.
 * RULE: No DB queries allowed. Only orchestration + business rules.
 */
class RkhApprovalService
{
    protected $rkhApprovalRepo;
    protected $masterDataRepo;

    public function __construct(
        RkhApprovalRepository $rkhApprovalRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->rkhApprovalRepo = $rkhApprovalRepo;
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
        $pendingRKH = $this->rkhApprovalRepo->getPendingApprovals($companycode, $currentUser->idjabatan);
        
        return $this->formatPendingApprovalsData($pendingRKH);
    }

    /**
     * Get approval detail for specific RKH
     * 
     * @param string $rkhno
     * @param string $companycode
     * @return array|null
     */
    public function getApprovalDetail($rkhno, $companycode)
    {
        $rkh = $this->rkhApprovalRepo->getApprovalDetail($companycode, $rkhno);

        if (!$rkh) {
            return null;
        }

        return $this->formatApprovalDetailData($rkh);
    }

    /**
     * Process approval (approve/decline)
     * 
     * @param string $rkhno
     * @param string $action
     * @param int $level
     * @param object $currentUser
     * @param string $companycode
     * @return array
     */
    public function processApproval($rkhno, $action, $level, $currentUser, $companycode)
    {
        try {
            DB::beginTransaction();

            // Get RKH with approval settings
            $rkh = $this->rkhApprovalRepo->getRkhWithApprovalSetting($companycode, $rkhno);

            if (!$rkh) {
                DB::rollBack();
                return ['success' => false, 'message' => 'RKH tidak ditemukan'];
            }

            // Validate authority
            $validationResult = $this->validateApprovalAuthority($rkh, $currentUser, $level);
            if (!$validationResult['success']) {
                DB::rollBack();
                return $validationResult;
            }

            // Update approval flag
            $approvalValue = $action === 'approve' ? '1' : '0';
            $now = now();
            
            $this->rkhApprovalRepo->updateApprovalFlag(
                $companycode, 
                $rkhno, 
                $level, 
                $approvalValue, 
                $currentUser->userid, 
                $now
            );

            // Determine final approval status
            $finalStatus = $this->determineFinalApprovalStatus($rkh, $level, $action);
            $this->rkhApprovalRepo->setApprovalStatus($companycode, $rkhno, $finalStatus);

            $responseMessage = 'RKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Handle post-approval actions if fully approved
            if ($action === 'approve' && $finalStatus === '1') {
                $responseMessage = $this->handlePostApprovalActions($rkhno, $companycode, $responseMessage);
            }

            DB::commit();
            return ['success' => true, 'message' => $responseMessage];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Approval process failed for RKH {$rkhno}", [
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

    /**
     * Update RKH status (Completed/In Progress)
     * 
     * @param string $rkhno
     * @param string $status
     * @param object $currentUser
     * @param string $companycode
     * @return array
     */
    public function updateRkhStatus($rkhno, $status, $currentUser, $companycode)
    {
        // Validate LKH completion if marking as Completed
        if ($status === 'Completed') {
            $progressStatus = $this->rkhApprovalRepo->getProgressStatusFromLkh($companycode, $rkhno);
            
            if (!$progressStatus['can_complete']) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat menandai RKH sebagai Completed. ' . 
                                $progressStatus['progress'] . '. Semua LKH harus diapprove terlebih dahulu.'
                ];
            }
        }
        
        $updated = $this->rkhApprovalRepo->updateStatus($companycode, $rkhno, $status, $currentUser->userid, now());

        if ($updated) {
            return [
                'success' => true, 
                'message' => 'Status RKH berhasil diupdate menjadi ' . $status
            ];
        } else {
            return ['success' => false, 'message' => 'RKH tidak ditemukan'];
        }
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Format pending approvals data
     */
    private function formatPendingApprovalsData($pendingRKH)
    {
        return $pendingRKH->map(function($rkh) {
            return [
                'rkhno' => $rkh->rkhno,
                'rkhdate' => $rkh->rkhdate,
                'rkhdate_formatted' => Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
                'mandor_nama' => $rkh->mandor_nama,
                'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
                'approval_level' => $rkh->approval_level,
                'total_luas' => $rkh->totalluas,
                'manpower' => $rkh->manpower
            ];
        })->toArray();
    }

    /**
     * Format approval detail data
     */
    private function formatApprovalDetailData($rkh)
    {
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $rkh->{"idjabatanapproval{$i}"};
            if (!$jabatanId) continue;

            $flagField = "approval{$i}flag";
            $dateField = "approval{$i}date";
            $userField = "approval{$i}_user_name";
            $jabatanField = "jabatan{$i}_name";

            $flag = $rkh->$flagField;
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
                'jabatan_name' => $rkh->$jabatanField ?? 'Unknown',
                'status' => $status,
                'status_text' => $statusText,
                'user_name' => $rkh->$userField ?? null,
                'date_formatted' => $rkh->$dateField ? Carbon::parse($rkh->$dateField)->format('d/m/Y H:i') : null
            ];
        }

        return [
            'rkhno' => $rkh->rkhno,
            'rkhdate' => $rkh->rkhdate,
            'rkhdate_formatted' => Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
            'mandor_nama' => $rkh->mandor_nama,
            'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
            'jumlah_approval' => $rkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }

    /**
     * Validate approval authority
     */
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

    /**
     * Determine final approval status based on current action
     */
    private function determineFinalApprovalStatus($rkh, $level, $action)
    {
        if ($action === 'decline') {
            return '0'; // Rejected
        }

        // Simulate approval to check if fully approved
        $tempRkh = clone $rkh;
        $approvalField = "approval{$level}flag";
        $tempRkh->$approvalField = '1';

        if ($this->isRkhFullyApproved($tempRkh)) {
            return '1'; // Fully approved
        }

        return null; // Still pending
    }

    /**
     * Check if RKH is fully approved
     */
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

    /**
     * Handle post-approval actions (LKH generation, material usage, batch creation)
     */
    private function handlePostApprovalActions($rkhno, $companycode, $responseMessage)
    {
        // STEP 1: Generate LKH (CRITICAL - HARD FAILURE)
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
        
        $responseMessage .= '. LKH auto-generated successfully (' . $lkhResult['total_lkh'] . ' LKH created)';
        
        // STEP 2: Check if material usage needed
        $needsMaterialUsage = $this->rkhApprovalRepo->needsMaterialUsage($companycode, $rkhno);
        
        if ($needsMaterialUsage) {
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
        } else {
            $responseMessage .= '. No material usage required for this RKH';
        }
        
        return $responseMessage;
    }
}