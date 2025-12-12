<?php
// app/Repositories/Transaction/RencanaKerjaHarian/BatchRepository.php

namespace App\Repositories\Transaction\RencanaKerjaHarian;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\MasterData\Batch;

/**
 * BatchRepository
 * 
 * Handles all batch-related database queries
 * Includes business logic yang dipindah dari Model
 */
class BatchRepository
{
    // =====================================
    // BASIC CRUD
    // =====================================

    /**
     * Find batch by surrogate ID
     */
    public function findById(int $id): ?Batch
    {
        return Batch::find($id);
    }

    /**
     * Find batch by business key (batchno + companycode)
     */
    public function findByBusinessKey(string $batchno, string $companycode): ?Batch
    {
        return Batch::where('batchno', $batchno)
            ->where('companycode', $companycode)
            ->first();
    }

    /**
     * Get active batch for plot
     */
    public function getActiveBatchForPlot(string $companycode, string $plot): ?Batch
    {
        return Batch::where('companycode', $companycode)
            ->where('plot', $plot)
            ->where('isactive', 1)
            ->first();
    }

    // =====================================
    // QUERY SCOPES (Moved from Model)
    // =====================================

    /**
     * Get active batches
     */
    public function getActiveBatches(string $companycode): Collection
    {
        return Batch::where('companycode', $companycode)
            ->where('isactive', 1)
            ->get();
    }

    /**
     * Get closed batches
     */
    public function getClosedBatches(string $companycode): Collection
    {
        return Batch::where('companycode', $companycode)
            ->where('isactive', 0)
            ->get();
    }

    /**
     * Get batches by lifecycle status
     */
    public function getBatchesByLifecycle(string $companycode, string $lifecycleStatus): Collection
    {
        return Batch::where('companycode', $companycode)
            ->where('lifecyclestatus', $lifecycleStatus)
            ->get();
    }

    /**
     * Get batches by plot type
     */
    public function getBatchesByPlotType(string $companycode, string $plotType): Collection
    {
        return Batch::where('companycode', $companycode)
            ->where('plottype', $plotType)
            ->get();
    }

    /**
     * Get batches by kontraktor
     */
    public function getBatchesByKontraktor(string $companycode, string $kontraktorId): Collection
    {
        return Batch::where('companycode', $companycode)
            ->where('kontraktorid', $kontraktorId)
            ->get();
    }

    /**
     * Get batches yang sedang dalam proses panen (on panen)
     * 
     * Batch with tanggalpanen filled but area belum fully harvested
     */
    public function getOnPanenBatches(string $companycode): Collection
    {
        return Batch::where('companycode', $companycode)
            ->where('isactive', 1)
            ->whereNotNull('tanggalpanen')
            ->whereRaw('batcharea > (
                SELECT COALESCE(SUM(ldp.luashasil), 0)
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhhdrid = lh.id
                WHERE ldp.batchid = batch.id
                AND lh.approvalstatus = "1"
            )')
            ->get();
    }

    /**
     * Get batches yang panen sudah selesai
     */
    public function getPanenSelesaiBatches(string $companycode): Collection
    {
        return Batch::where('companycode', $companycode)
            ->where('isactive', 1)
            ->whereNotNull('tanggalpanen')
            ->whereRaw('batcharea <= (
                SELECT COALESCE(SUM(ldp.luashasil), 0)
                FROM lkhdetailplot ldp
                JOIN lkhhdr lh ON ldp.lkhhdrid = lh.id
                WHERE ldp.batchid = batch.id
                AND lh.approvalstatus = "1"
            )')
            ->get();
    }

    // =====================================
    // BUSINESS LOGIC (Moved from Model)
    // =====================================

    /**
     * Get total luas yang sudah dipanen (approved only)
     * 
     * @param int $batchId (surrogate ID)
     * @return float
     */
    public function getTotalPanen(int $batchId): float
    {
        return DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', 'ldp.lkhhdrid', '=', 'lh.id')
            ->where('ldp.batchid', $batchId)
            ->where('lh.approvalstatus', '1')
            ->sum('ldp.luashasil') ?? 0;
    }

    /**
     * Get luas sisa yang belum dipanen
     * 
     * @param int $batchId
     * @return float
     */
    public function getLuasSisa(int $batchId): float
    {
        $batch = $this->findById($batchId);
        if (!$batch) {
            return 0;
        }

        $totalPanen = $this->getTotalPanen($batchId);
        return max(0, $batch->batcharea - $totalPanen);
    }

    /**
     * Get progress panen dalam persentase
     * 
     * @param int $batchId
     * @return float (0-100)
     */
    public function getPanenProgress(int $batchId): float
    {
        $batch = $this->findById($batchId);
        if (!$batch || $batch->batcharea <= 0) {
            return 0;
        }

        $totalPanen = $this->getTotalPanen($batchId);
        return min(100, ($totalPanen / $batch->batcharea) * 100);
    }

    /**
     * Check if batch is harvestable
     * 
     * @param int $batchId
     * @return bool
     */
    public function isHarvestable(int $batchId): bool
    {
        $batch = $this->findById($batchId);
        if (!$batch) {
            return false;
        }

        return $batch->isactive && 
               in_array($batch->lifecyclestatus, ['PC', 'RC1', 'RC2', 'RC3']);
    }

    /**
     * Check if batch has been harvested (tanggalpanen filled)
     * 
     * @param int $batchId
     * @return bool
     */
    public function isHarvested(int $batchId): bool
    {
        $batch = $this->findById($batchId);
        return $batch && !is_null($batch->tanggalpanen);
    }

    /**
     * Check if batch can be closed
     * 
     * @param int $batchId
     * @return bool
     */
    public function canBeClosed(int $batchId): bool
    {
        $batch = $this->findById($batchId);
        if (!$batch) {
            return false;
        }

        return $batch->isactive && !is_null($batch->tanggalpanen);
    }

    /**
     * Get next lifecycle status
     * 
     * @param int $batchId
     * @return string|null
     */
    public function getNextLifecycleStatus(int $batchId): ?string
    {
        $batch = $this->findById($batchId);
        if (!$batch) {
            return null;
        }

        return match($batch->lifecyclestatus) {
            'PC' => 'RC1',
            'RC1' => 'RC2',
            'RC2' => 'RC3',
            'RC3' => 'PC',
            default => null
        };
    }

    /**
     * Check if batch is KBD type
     * 
     * @param int $batchId
     * @return bool
     */
    public function isKBD(int $batchId): bool
    {
        $batch = $this->findById($batchId);
        return $batch && $batch->plottype === 'KBD';
    }

    /**
     * Check if batch is KTG type
     * 
     * @param int $batchId
     * @return bool
     */
    public function isKTG(int $batchId): bool
    {
        $batch = $this->findById($batchId);
        return $batch && $batch->plottype === 'KTG';
    }

    /**
     * Get batch age in days
     * 
     * @param int $batchId
     * @return int
     */
    public function getAgeInDays(int $batchId): int
    {
        $batch = $this->findById($batchId);
        if (!$batch) {
            return 0;
        }

        return now()->diffInDays($batch->batchdate);
    }

    /**
     * Get batch age in months
     * 
     * @param int $batchId
     * @return int
     */
    public function getAgeInMonths(int $batchId): int
    {
        $batch = $this->findById($batchId);
        if (!$batch) {
            return 0;
        }

        return now()->diffInMonths($batch->batchdate);
    }

    /**
     * Get batch duration (from batchdate to closedat)
     * 
     * @param int $batchId
     * @return int|null (days)
     */
    public function getDurationDays(int $batchId): ?int
    {
        $batch = $this->findById($batchId);
        if (!$batch || !$batch->closedat) {
            return null;
        }

        return $batch->closedat->diffInDays($batch->batchdate);
    }

    // =====================================
    // AGGREGATE QUERIES
    // =====================================

    /**
     * Get total active batch area for company
     * 
     * @param string $companycode
     * @return float
     */
    public function getTotalActiveBatchArea(string $companycode): float
    {
        return Batch::where('companycode', $companycode)
            ->where('isactive', 1)
            ->sum('batcharea') ?? 0;
    }

    /**
     * Get batch statistics for company
     * 
     * @param string $companycode
     * @return array
     */
    public function getBatchStatistics(string $companycode): array
    {
        $stats = Batch::where('companycode', $companycode)
            ->selectRaw('
                COUNT(*) as total_batches,
                SUM(CASE WHEN isactive = 1 THEN 1 ELSE 0 END) as active_batches,
                SUM(CASE WHEN isactive = 0 THEN 1 ELSE 0 END) as closed_batches,
                SUM(CASE WHEN isactive = 1 THEN batcharea ELSE 0 END) as total_active_area,
                SUM(CASE WHEN lifecyclestatus = "PC" THEN 1 ELSE 0 END) as pc_batches,
                SUM(CASE WHEN lifecyclestatus = "RC1" THEN 1 ELSE 0 END) as rc1_batches,
                SUM(CASE WHEN lifecyclestatus = "RC2" THEN 1 ELSE 0 END) as rc2_batches,
                SUM(CASE WHEN lifecyclestatus = "RC3" THEN 1 ELSE 0 END) as rc3_batches,
                SUM(CASE WHEN plottype = "KBD" THEN 1 ELSE 0 END) as kbd_batches,
                SUM(CASE WHEN plottype = "KTG" THEN 1 ELSE 0 END) as ktg_batches
            ')
            ->first();

        return [
            'total_batches' => $stats->total_batches ?? 0,
            'active_batches' => $stats->active_batches ?? 0,
            'closed_batches' => $stats->closed_batches ?? 0,
            'total_active_area' => $stats->total_active_area ?? 0,
            'by_lifecycle' => [
                'PC' => $stats->pc_batches ?? 0,
                'RC1' => $stats->rc1_batches ?? 0,
                'RC2' => $stats->rc2_batches ?? 0,
                'RC3' => $stats->rc3_batches ?? 0,
            ],
            'by_plottype' => [
                'KBD' => $stats->kbd_batches ?? 0,
                'KTG' => $stats->ktg_batches ?? 0,
            ]
        ];
    }

    // =====================================
    // UPDATE OPERATIONS
    // =====================================

    /**
     * Close batch (set isactive = 0)
     * 
     * @param int $batchId
     * @param string $closedBy
     * @return bool
     */
    public function closeBatch(int $batchId, string $closedBy): bool
    {
        return DB::table('batch')
            ->where('id', $batchId)
            ->update([
                'isactive' => 0,
                'closedat' => now(),
            ]);
    }

    /**
     * Update tanggal panen
     * 
     * @param int $batchId
     * @param string $tanggalPanen
     * @return bool
     */
    public function setTanggalPanen(int $batchId, string $tanggalPanen): bool
    {
        return DB::table('batch')
            ->where('id', $batchId)
            ->update([
                'tanggalpanen' => $tanggalPanen,
            ]);
    }

    /**
     * Update lifecycle status
     * 
     * @param int $batchId
     * @param string $newStatus
     * @return bool
     */
    public function updateLifecycleStatus(int $batchId, string $newStatus): bool
    {
        return DB::table('batch')
            ->where('id', $batchId)
            ->update([
                'lifecyclestatus' => $newStatus,
            ]);
    }
}