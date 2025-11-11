<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Lkhhdr;
use App\Models\LkhDetailPlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * GenerateNewBatchService
 * 
 * Auto-generate new batch based on LKH approval:
 * 1. Panen completed (4.3.3/4.4.3/4.5.2) → PC→RC1, RC1→RC2, RC2→RC3
 * 2. Planting completed (2.2.7) → RC3→PC or new PC
 */
class GenerateNewBatchService
{
    const PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2'];
    const PLANTING_ACTIVITY = '2.2.7';
    const TOLERANCE = 1.0;
    
    /**
     * Main entry point - check LKH and trigger appropriate batch generation
     * Called after LKH approved
     */
    public function checkAndGenerate($lkhno, $companycode)
    {
        try {
            $lkh = Lkhhdr::where('lkhno', $lkhno)
                ->where('companycode', $companycode)
                ->first();
            
            if (!$lkh) {
                return ['success' => false, 'message' => 'LKH not found'];
            }
            
            // Route 1: Panen activities
            if (in_array($lkh->activitycode, self::PANEN_ACTIVITIES)) {
                return $this->generateFromPanen($lkhno, $companycode);
            }
            
            // Route 2: Planting activity
            if ($lkh->activitycode === self::PLANTING_ACTIVITY) {
                return $this->generateFromPlanting($lkhno, $companycode);
            }
            
            // Not a batch-generating activity
            return ['success' => true, 'message' => 'No batch generation needed'];
            
        } catch (\Exception $e) {
            Log::error("Batch generation check failed for LKH {$lkhno}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Generate next batch from panen completion (PC→RC1, RC1→RC2, RC2→RC3)
     */
    private function generateFromPanen($lkhno, $companycode)
    {
        try {
            // Get all batches from this panen LKH
            $batchNumbers = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->whereNotNull('batchno')
                ->pluck('batchno')
                ->unique();
            
            if ($batchNumbers->isEmpty()) {
                return ['success' => false, 'message' => 'No batch found in panen LKH'];
            }
            
            $results = [];
            
            foreach ($batchNumbers as $batchno) {
                if ($this->isPanenCompleted($batchno, $companycode)) {
                    $result = $this->createNextBatchFromPanen($batchno, $companycode);
                    $results[] = $result;
                }
            }
            
            if (empty($results)) {
                return ['success' => true, 'message' => 'Panen not yet completed'];
            }
            
            return [
                'success' => true,
                'message' => count($results) . ' batch(es) transitioned from panen',
                'transitions' => $results
            ];
            
        } catch (\Exception $e) {
            Log::error("Generate from panen failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Generate new PC batch from planting activity (RC3→PC or new plot)
     */
    private function generateFromPlanting($lkhno, $companycode)
    {
        try {
            // Get plots from planting LKH
            $plantingPlots = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->get();
            
            if ($plantingPlots->isEmpty()) {
                return ['success' => false, 'message' => 'No plots found in planting LKH'];
            }
            
            $createdBatches = [];
            
            foreach ($plantingPlots as $plotData) {
                $result = $this->createBatchFromPlanting($lkhno, $plotData, $companycode);
                if ($result['success']) {
                    $createdBatches[] = $result;
                }
            }
            
            return [
                'success' => true,
                'message' => count($createdBatches) . ' PC batch(es) created from planting',
                'batches' => $createdBatches
            ];
            
        } catch (\Exception $e) {
            Log::error("Generate from planting failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Check if panen completed (approved LKH only)
     */
    private function isPanenCompleted($batchno, $companycode)
    {
        $batch = Batch::where('batchno', $batchno)
            ->where('companycode', $companycode)
            ->first();
        
        if (!$batch) return false;
        
        $totalPanen = DB::table('lkhdetailplot as ldp')
            ->join('lkhhdr as lh', function($join) {
                $join->on('ldp.lkhno', '=', 'lh.lkhno')
                    ->on('ldp.companycode', '=', 'lh.companycode');
            })
            ->where('ldp.batchno', $batchno)
            ->where('lh.approvalstatus', '1')
            ->sum('ldp.luashasil') ?? 0;
        
        return $totalPanen >= ($batch->batcharea * self::TOLERANCE);
    }
    
    /**
     * Create next batch from panen (PC→RC1, RC1→RC2, RC2→RC3)
     */
    private function createNextBatchFromPanen($currentBatchNo, $companycode)
    {
        DB::beginTransaction();
        
        try {
            $currentBatch = Batch::where('batchno', $currentBatchNo)
                ->where('companycode', $companycode)
                ->lockForUpdate()
                ->first();
            
            if (!$currentBatch || !$currentBatch->isactive) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Batch already closed or not found'];
            }
            
            // Close current batch
            $currentBatch->update([
                'isactive' => 0,
                'closedat' => now()
            ]);
            
            // Determine next lifecycle
            $nextLifecycle = $this->getNextLifecycle($currentBatch->lifecyclestatus);
            
            // Generate new batch
            $newBatchNo = $this->generateBatchNo($companycode, now());
            
            $newBatch = Batch::create([
                'batchno' => $newBatchNo,
                'companycode' => $companycode,
                'plot' => $currentBatch->plot,
                'batcharea' => $currentBatch->batcharea,
                'batchdate' => now()->format('Y-m-d'),
                'lifecyclestatus' => $nextLifecycle,
                'previousbatchno' => $currentBatchNo,
                'plantinglkhno' => $currentBatch->plantinglkhno, // Copy dari PC
                'tanggalpanen' => null,
                'kontraktorid' => $currentBatch->kontraktorid,
                'kodevarietas' => $currentBatch->kodevarietas,
                'pkp' => $currentBatch->pkp,
                'plottype' => $currentBatch->plottype,
                'isactive' => 1,
                'inputby' => Auth::user()->userid ?? 'SYSTEM',
                'createdat' => now()
            ]);
            
            // Update masterlist
            DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $currentBatch->plot)
                ->update(['activebatchno' => $newBatchNo]);
            
            DB::commit();
            
            Log::info("Batch transitioned from panen", [
                'old_batch' => $currentBatchNo,
                'new_batch' => $newBatchNo,
                'lifecycle' => $nextLifecycle
            ]);
            
            return [
                'success' => true,
                'old_batchno' => $currentBatchNo,
                'new_batchno' => $newBatchNo,
                'lifecycle' => $nextLifecycle,
                'plot' => $currentBatch->plot
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create next batch from panen: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Create new PC batch from planting (RC3→PC or new plot)
     */
    private function createBatchFromPlanting($lkhno, $plotData, $companycode)
    {
        DB::beginTransaction();
        
        try {
            // Check previous batch
            $previousBatch = Batch::where('companycode', $companycode)
                ->where('plot', $plotData->plot)
                ->where('isactive', 0)
                ->orderBy('closedat', 'desc')
                ->first();
            
            // Close previous batch if still active (should not happen, but safety)
            Batch::where('companycode', $companycode)
                ->where('plot', $plotData->plot)
                ->where('isactive', 1)
                ->update(['isactive' => 0, 'closedat' => now()]);
            
            // Generate new PC batch
            $newBatchNo = $this->generateBatchNo($companycode, now());
            
            $newBatch = Batch::create([
                'batchno' => $newBatchNo,
                'companycode' => $companycode,
                'plot' => $plotData->plot,
                'batcharea' => $plotData->luasrkh,
                'batchdate' => now()->format('Y-m-d'),
                'lifecyclestatus' => 'PC',
                'previousbatchno' => $previousBatch ? $previousBatch->batchno : null,
                'plantinglkhno' => $lkhno, // ✅ Record planting LKH
                'tanggalpanen' => null,
                'kontraktorid' => $previousBatch ? $previousBatch->kontraktorid : null,
                'isactive' => 1,
                'inputby' => Auth::user()->userid ?? 'SYSTEM',
                'createdat' => now()
            ]);
            
            // Update masterlist
            DB::table('masterlist')
                ->updateOrInsert(
                    ['companycode' => $companycode, 'plot' => $plotData->plot],
                    ['activebatchno' => $newBatchNo, 'isactive' => 1]
                );
            
            DB::commit();
            
            Log::info("PC batch created from planting", [
                'lkhno' => $lkhno,
                'new_batch' => $newBatchNo,
                'plot' => $plotData->plot,
                'previous_batch' => $previousBatch ? $previousBatch->batchno : 'None'
            ]);
            
            return [
                'success' => true,
                'batchno' => $newBatchNo,
                'plot' => $plotData->plot,
                'lifecycle' => 'PC'
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create PC batch from planting: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get next lifecycle (PC→RC1→RC2→RC3)
     */
    private function getNextLifecycle($current)
    {
        return match($current) {
            'PC' => 'RC1',
            'RC1' => 'RC2',
            'RC2' => 'RC3',
            'RC3' => 'PC',
            default => 'PC'
        };
    }
    
    /**
     * Generate unique batch number
     */
    private function generateBatchNo($companycode, $date)
    {
        $dateStr = $date->format('ymd');
        
        $sequence = DB::table('batch')
            ->where('companycode', $companycode)
            ->whereDate('batchdate', $date)
            ->count() + 1;
        
        return "BATCH{$dateStr}" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}