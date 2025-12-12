<?php

namespace App\Services\Transaction\RencanaKerjaHarian;

use App\Repositories\Transaction\RencanaKerjaHarian\LkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\BatchRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * LkhService - FIXED
 * 
 * Business logic for LKH (Laporan Kegiatan Harian) operations
 * Orchestrates repositories and handles complex business rules
 */
class LkhService
{
    protected LkhRepository $lkhRepo;
    protected BatchRepository $batchRepo;

    public function __construct(
        LkhRepository $lkhRepo,
        BatchRepository $batchRepo
    ) {
        $this->lkhRepo = $lkhRepo;
        $this->batchRepo = $batchRepo;
    }

    /**
     * Get LKH list for specific RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function getLkhListForRkh(string $companycode, string $rkhno): array
    {
        $lkhData = $this->lkhRepo->getLkhByRkhNo($companycode, $rkhno);
        
        $canGenerateLkh = false;
        $generateMessage = '';
        
        if ($lkhData->isEmpty()) {
            // Check if RKH is fully approved
            $rkh = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
            
            if ($rkh && $rkh->approvalstatus === '1') {
                $canGenerateLkh = true;
                $generateMessage = 'RKH sudah fully approved. Klik untuk generate LKH.';
            } else {
                $generateMessage = 'RKH belum fully approved.';
            }
        } else {
            $generateMessage = 'LKH sudah di-generate untuk RKH ini.';
        }

        return [
            'lkh_data' => $lkhData->toArray(),
            'rkhno' => $rkhno,
            'can_generate_lkh' => $canGenerateLkh,
            'generate_message' => $generateMessage,
            'total_lkh' => $lkhData->count()
        ];
    }

    /**
     * Get LKH detail by lkhno
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return array
     */
    public function getLkhDetail(string $companycode, string $lkhno): array
    {
        $lkhData = $this->lkhRepo->findByLkhNo($companycode, $lkhno);
        
        if (!$lkhData) {
            throw new \Exception('LKH tidak ditemukan');
        }

        // Determine activity type
        $activityType = $this->determineActivityType($lkhData->activitycode);

        // Get appropriate details based on activity type
        $details = $this->getDetailsByActivityType($companycode, $lkhno, $activityType);

        return array_merge([
            'lkhData' => $lkhData,
            'activityType' => $activityType,
        ], $details);
    }

    /**
     * Update LKH
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param array $data
     * @return array
     */
    public function updateLkh(string $companycode, string $lkhno, array $data): array
    {
        try {
            DB::beginTransaction();

            $lkh = $this->lkhRepo->findByLkhNo($companycode, $lkhno);
            
            if (!$lkh) {
                throw new \Exception('LKH tidak ditemukan');
            }

            // Security check
            if ($lkh->issubmit) {
                throw new \Exception('LKH sudah disubmit dan tidak dapat diedit');
            }

            // Update LKH header
            $this->lkhRepo->updateHeader($companycode, $lkhno, [
                'keterangan' => $data['keterangan'] ?? null,
                'updateby' => Auth::user()->userid,
                'updatedat' => now()
            ]);

            // Update plots if provided
            if (!empty($data['plots'])) {
                $this->updateLkhPlots($companycode, $lkhno, $data['plots']);
            }

            // Update workers if provided
            if (!empty($data['workers'])) {
                $this->updateLkhWorkers($companycode, $lkhno, $data['workers']);
            }

            // Recalculate totals
            $this->recalculateLkhTotals($companycode, $lkhno);

            DB::commit();

            return [
                'success' => true,
                'message' => 'LKH berhasil diupdate'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Update LKH error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal update LKH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Submit LKH for approval
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return array
     */
    public function submitLkh(string $companycode, string $lkhno): array
    {
        try {
            DB::beginTransaction();

            $lkh = $this->lkhRepo->findByLkhNo($companycode, $lkhno);
            
            if (!$lkh) {
                throw new \Exception('LKH tidak ditemukan');
            }

            if ($lkh->issubmit) {
                throw new \Exception('LKH sudah disubmit sebelumnya');
            }

            // Validate LKH is complete
            if (!$this->isLkhComplete($companycode, $lkhno)) {
                throw new \Exception('LKH belum lengkap, mohon lengkapi data terlebih dahulu');
            }

            // Update submit status
            $this->lkhRepo->updateHeader($companycode, $lkhno, [
                'issubmit' => 1,
                'status' => 'SUBMITTED',
                'submitby' => Auth::user()->userid,
                'submitat' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'LKH berhasil disubmit untuk approval'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Submit LKH error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal submit LKH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process LKH approval
     * 
     * @param string $companycode
     * @param string $lkhno
     * @param int $level
     * @param string $action (approve/decline)
     * @param object $currentUser
     * @return array
     */
    public function processApproval(
        string $companycode,
        string $lkhno,
        int $level,
        string $action,
        object $currentUser
    ): array {
        try {
            DB::beginTransaction();

            $lkh = $this->lkhRepo->getLkhApprovalDetail($companycode, $lkhno);
            
            if (!$lkh) {
                throw new \Exception('LKH tidak ditemukan');
            }

            // Validate approval authority
            $this->validateApprovalAuthority($lkh, $currentUser, $level);

            // Build update data
            $updateData = $this->buildApprovalUpdateData($lkh, $level, $action, $currentUser);

            // Update LKH
            $this->lkhRepo->updateHeader($companycode, $lkhno, $updateData);

            DB::commit();

            $message = "LKH approval level {$level} berhasil " . 
                      ($action === 'approve' ? 'disetujui' : 'ditolak');

            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("LKH approval process error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal proses approval: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get pending LKH approvals for user
     * 
     * @param string $companycode
     * @param object $currentUser
     * @return Collection
     */
    public function getPendingApprovalsForUser(string $companycode, object $currentUser): Collection
    {
        if (!$currentUser->idjabatan) {
            return collect([]);
        }

        $pendingLkh = $this->lkhRepo->getPendingApprovals($companycode, $currentUser->idjabatan);

        return $pendingLkh->map(function($lkh) {
            return [
                'lkhno' => $lkh->lkhno,
                'rkhno' => $lkh->rkhno,
                'lkhdate' => $lkh->lkhdate,
                'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
                'mandor_nama' => $lkh->mandor_nama,
                'activity_name' => $lkh->activity_name ?? 'Unknown',
                'approval_level' => $lkh->approval_level,
                'total_workers' => $lkh->totalworkers,
                'total_luas' => $lkh->totalluasactual
            ];
        });
    }

    /**
     * Get surat jalan for plot (Panen BSM)
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $subkontraktorId
     * @param string $lkhno
     * @return Collection
     */
    public function getSuratJalanForPlot(
        string $companycode,
        string $plot,
        string $subkontraktorId,
        string $lkhno
    ): Collection {
        return $this->lkhRepo->getSuratJalanList($companycode, $plot, $subkontraktorId, $lkhno);
    }

    /**
     * Check if LKH is complete
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return bool
     */
    public function isLkhComplete(string $companycode, string $lkhno): bool
    {
        $lkh = $this->lkhRepo->findByLkhNo($companycode, $lkhno);
        
        if (!$lkh) {
            return false;
        }

        // Check if has workers
        $hasWorkers = DB::table('lkhdetailworker')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->exists();

        if (!$hasWorkers) {
            return false;
        }

        // Check if has plot details
        $hasPlots = DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->exists();

        return $hasPlots;
    }

    /**
     * Check if LKH is fully approved
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return bool
     */
    public function isFullyApproved(string $companycode, string $lkhno): bool
    {
        $lkh = $this->lkhRepo->findByLkhNo($companycode, $lkhno);
        
        if (!$lkh) {
            return false;
        }

        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return true; // No approval required
        }

        for ($i = 1; $i <= $lkh->jumlahapproval; $i++) {
            $approvalField = "approval{$i}flag";
            if (!isset($lkh->$approvalField) || $lkh->$approvalField !== '1') {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if LKH can be edited
     * 
     * @param string $companycode
     * @param string $lkhno
     * @return bool
     */
    public function canBeEdited(string $companycode, string $lkhno): bool
    {
        $lkh = $this->lkhRepo->findByLkhNo($companycode, $lkhno);
        
        if (!$lkh) {
            return false;
        }

        return !$lkh->issubmit && !$this->isFullyApproved($companycode, $lkhno);
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Determine activity type
     */
    private function determineActivityType(string $activitycode): string
    {
        if ($activitycode === '4.7') {
            return 'bsm';
        }
        
        if (in_array($activitycode, ['4.3.3', '4.4.3', '4.5.2'])) {
            return 'panen';
        }

        return 'normal';
    }

    /**
     * Get details by activity type
     */
    private function getDetailsByActivityType(string $companycode, string $lkhno, string $activityType): array
    {
        $plots = $this->lkhRepo->getPlotDetails($companycode, $lkhno);
        $workers = $this->lkhRepo->getWorkerDetails($companycode, $lkhno);

        $details = [
            'plots' => $plots,
            'workers' => $workers
        ];

        if ($activityType === 'bsm') {
            $details['bsm_details'] = $this->lkhRepo->getBsmDetails($companycode, $lkhno);
        }

        if ($activityType === 'panen') {
            $details['kendaraan'] = $this->lkhRepo->getKendaraanDetails($companycode, $lkhno);
        }

        return $details;
    }

    /**
     * Update LKH plots
     */
    private function updateLkhPlots(string $companycode, string $lkhno, array $plots): void
    {
        foreach ($plots as $plot) {
            DB::table('lkhdetailplot')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->where('plot', $plot['plot'])
                ->update([
                    'luashasil' => $plot['luashasil'] ?? null,
                    'luassisa' => $plot['luassisa'] ?? null,
                    'updatedat' => now()
                ]);
        }
    }

    /**
     * Update LKH workers
     */
    private function updateLkhWorkers(string $companycode, string $lkhno, array $workers): void
    {
        foreach ($workers as $worker) {
            DB::table('lkhdetailworker')
                ->where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->where('tenagakerjaid', $worker['tenagakerjaid'])
                ->update([
                    'jammasuk' => $worker['jammasuk'] ?? null,
                    'jamselesai' => $worker['jamselesai'] ?? null,
                    'totaljamkerja' => $worker['totaljamkerja'] ?? null,
                    'updatedat' => now()
                ]);
        }
    }

    /**
     * Recalculate LKH totals
     */
    private function recalculateLkhTotals(string $companycode, string $lkhno): void
    {
        $plotTotals = DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->selectRaw('SUM(luashasil) as total_luas_actual, SUM(luassisa) as total_sisa')
            ->first();

        $workerCount = DB::table('lkhdetailworker')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->count();

        $this->lkhRepo->updateHeader($companycode, $lkhno, [
            'totalluasactual' => $plotTotals->total_luas_actual ?? 0,
            'totalsisa' => $plotTotals->total_sisa ?? 0,
            'totalworkers' => $workerCount
        ]);
    }

    /**
     * Validate approval authority
     */
    private function validateApprovalAuthority(object $lkh, object $currentUser, int $level): void
    {
        $approvalJabatanField = "idjabatanapproval{$level}";
        $approvalField = "approval{$level}flag";

        if (!isset($lkh->$approvalJabatanField) || $lkh->$approvalJabatanField != $currentUser->idjabatan) {
            throw new \Exception('Anda tidak memiliki wewenang untuk approve level ini');
        }

        if (isset($lkh->$approvalField) && $lkh->$approvalField !== null) {
            throw new \Exception('Approval level ini sudah diproses sebelumnya');
        }

        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($lkh->$prevApprovalField) || $lkh->$prevApprovalField !== '1') {
                throw new \Exception('Approval level sebelumnya belum disetujui');
            }
        }
    }

    /**
     * Build approval update data
     */
    private function buildApprovalUpdateData(object $lkh, int $level, string $action, object $currentUser): array
    {
        $updateData = [
            "approval{$level}flag" => $action === 'approve' ? '1' : '0',
            "approval{$level}date" => now(),
            "approval{$level}userid" => $currentUser->userid,
        ];

        // Check if all approvals are complete
        $allApproved = true;
        for ($i = 1; $i <= $lkh->jumlahapproval; $i++) {
            if ($i === $level) {
                if ($action !== 'approve') {
                    $allApproved = false;
                    break;
                }
            } else {
                $approvalField = "approval{$i}flag";
                if (!isset($lkh->$approvalField) || $lkh->$approvalField !== '1') {
                    $allApproved = false;
                    break;
                }
            }
        }

        if ($allApproved) {
            $updateData['status'] = 'APPROVED';
        } elseif ($action === 'decline') {
            $updateData['status'] = 'DECLINED';
        }

        return $updateData;
    }
}