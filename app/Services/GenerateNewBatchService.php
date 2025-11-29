<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Lkhhdr;
use App\Models\LkhDetailPlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * GenerateNewBatchService - FIXED VERSION
 * 
 * Auto-generate new batch based on LKH approval:
 * 1. Panen completed (4.3.3/4.4.3/4.5.2) → PC→RC1, RC1→RC2, RC2→RC3, RC3→PC (empty)
 * 2. Planting completed (2.2.7) → UPDATE existing PC batch (set tanggalulangtahun & plantinglkhno)
 * 3. Set tanggalpanen on first panen LKH approval
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
                // Set tanggalpanen first (before transition check)
                $this->setTanggalPanen($lkhno, $companycode, $lkh);
                
                // Then check for batch transition
                return $this->generateFromPanen($lkhno, $companycode);
            }
            
            // Route 2: Planting activity - UPDATE existing PC batch (NOT create new)
            if ($lkh->activitycode === self::PLANTING_ACTIVITY) {
                return $this->updatePCBatchFromPlanting($lkhno, $companycode, $lkh);
            }
            
            // Not a batch-generating activity
            return ['success' => true, 'message' => 'No batch generation needed'];
            
        } catch (\Exception $e) {
            Log::error("Batch generation check failed for LKH {$lkhno}: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Set tanggalpanen for first panen LKH approval
     * 
     * @param string $lkhno
     * @param string $companycode
     * @param Lkhhdr $lkh
     * @return void
     */
    private function setTanggalPanen($lkhno, $companycode, $lkh)
    {
        try {
            // Get all batches from this panen LKH
            $plots = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->whereNotNull('batchno')
                ->select('batchno', 'plot')
                ->distinct()
                ->get();
            
            if ($plots->isEmpty()) {
                Log::warning("No plots found for panen LKH {$lkhno}");
                return;
            }
            
            foreach ($plots as $plot) {
                $batch = Batch::where('batchno', $plot->batchno)
                    ->where('companycode', $companycode)
                    ->first();
                
                if (!$batch) {
                    Log::warning("Batch {$plot->batchno} not found for plot {$plot->plot}");
                    continue;
                }
                
                // ONLY set tanggalpanen if NULL (first panen)
                if ($batch->tanggalpanen === null) {
                    $batch->update([
                        'tanggalpanen' => $lkh->lkhdate,
                        'lastactivity' => $lkh->activitycode,
                        'updatedat' => now()
                    ]);
                    
                    Log::info("Set tanggalpanen for batch {$plot->batchno}", [
                        'plot' => $plot->plot,
                        'tanggalpanen' => $lkh->lkhdate,
                        'lkhno' => $lkhno,
                        'activitycode' => $lkh->activitycode
                    ]);
                } else {
                    // Just update lastactivity for subsequent panen
                    $batch->update([
                        'lastactivity' => $lkh->activitycode,
                        'updatedat' => now()
                    ]);
                    
                    Log::info("Updated lastactivity for batch {$plot->batchno}", [
                        'plot' => $plot->plot,
                        'existing_tanggalpanen' => $batch->tanggalpanen,
                        'lkhno' => $lkhno
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to set tanggalpanen for LKH {$lkhno}: " . $e->getMessage());
            // Don't throw - allow batch generation to continue
        }
    }
    
    /**
     * Generate next batch from panen completion (PC→RC1, RC1→RC2, RC2→RC3, RC3→PC empty)
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
     * UPDATE existing PC batch from planting activity (NOT create new batch)
     * NEW LOGIC: Just update tanggalulangtahun & plantinglkhno
     */
    private function updatePCBatchFromPlanting($lkhno, $companycode, $lkh)
    {
        DB::beginTransaction();
        
        try {
            // Get plots from planting LKH
            $plantingPlots = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkhno)
                ->get();
            
            if ($plantingPlots->isEmpty()) {
                DB::rollBack();
                return ['success' => false, 'message' => 'No plots found in planting LKH'];
            }
            
            $updatedBatches = [];
            
            foreach ($plantingPlots as $plotData) {
                // Get active PC batch for this plot
                $pcBatch = Batch::where('companycode', $companycode)
                    ->where('plot', $plotData->plot)
                    ->where('lifecyclestatus', 'PC')
                    ->where('isactive', 1)
                    ->whereNull('tanggalulangtahun') // PC yang belum di-tanam
                    ->first();
                
                if (!$pcBatch) {
                    Log::warning("No empty PC batch found for plot {$plotData->plot}, skipping planting update");
                    continue;
                }
                
                // UPDATE PC batch with planting info
                $pcBatch->update([
                    'tanggalulangtahun' => $lkh->lkhdate,
                    'plantinglkhno' => $lkhno,
                    'lastactivity' => self::PLANTING_ACTIVITY,
                    'updateby' => Auth::user()->userid ?? 'SYSTEM',
                    'updatedat' => now()
                ]);
                
                $updatedBatches[] = [
                    'batchno' => $pcBatch->batchno,
                    'plot' => $plotData->plot,
                    'tanggalulangtahun' => $lkh->lkhdate
                ];
                
                Log::info("PC batch updated with planting info", [
                    'batchno' => $pcBatch->batchno,
                    'plot' => $plotData->plot,
                    'lkhno' => $lkhno,
                    'tanggalulangtahun' => $lkh->lkhdate
                ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => count($updatedBatches) . ' PC batch(es) updated with planting info',
                'batches' => $updatedBatches
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update PC batch from planting: " . $e->getMessage());
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
     * Create next batch from panen
     * PC→RC1, RC1→RC2, RC2→RC3, RC3→PC (empty, tanggalulangtahun=NULL)
     */
    private function createNextBatchFromPanen($currentBatchNo, $companycode)
    {
        DB::beginTransaction();
        
        try {
            $currentBatch = Batch::where('batchno', $currentBatchNo)
                ->where('companycode', $companycode)
                ->lockForUpdate()
                ->first();
            
            if (!$currentBatch) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Batch not found'];
            }
            
            // ✅ CHECK: Batch sudah closed sebelumnya?
            if (!$currentBatch->isactive) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Batch already transitioned'];
            }
            
            // ✅ NEW: Close current batch SETELAH dipastikan completed
            $currentBatch->update([
                'isactive' => 0,
                'closedat' => now()
            ]);
            
            // Determine next lifecycle
            $nextLifecycle = $this->getNextLifecycle($currentBatch->lifecyclestatus);
            
            // Generate new batch
            $newBatchNo = $this->generateBatchNo($companycode, now());
            
            // Prepare batch data
            $batchData = [
                'batchno' => $newBatchNo,
                'companycode' => $companycode,
                'plot' => $currentBatch->plot,
                'batcharea' => $currentBatch->batcharea,
                'batchdate' => now()->format('Y-m-d'),
                'lifecyclestatus' => $nextLifecycle,
                'previousbatchno' => $currentBatchNo,
                'tanggalpanen' => null,
                'kontraktorid' => $currentBatch->kontraktorid,
                'kodevarietas' => $currentBatch->kodevarietas,
                'pkp' => $currentBatch->pkp,
                'plottype' => $currentBatch->plottype,
                'isactive' => 1,
                'inputby' => Auth::user()->userid ?? 'SYSTEM',
                'createdat' => now()
            ];
            
            // ✅ LOGIC 1: tanggalulangtahun (HANYA NULL kalau RC3→PC)
            if ($currentBatch->lifecyclestatus === 'RC3' && $nextLifecycle === 'PC') {
                $batchData['tanggalulangtahun'] = null;
            } else {
                $batchData['tanggalulangtahun'] = now()->format('Y-m-d');
            }
            
            // ✅ LOGIC 2: plantinglkhno (INDEPENDENT, copy kalau ada)
            $batchData['plantinglkhno'] = $currentBatch->plantinglkhno ?? null;
            
            // ✅ SPECIAL: RC3→PC set plantinglkhno = NULL (paksa)
            if ($currentBatch->lifecyclestatus === 'RC3' && $nextLifecycle === 'PC') {
                $batchData['plantinglkhno'] = null;
            }
            
            $newBatch = Batch::create($batchData);
            
            // Update masterlist
            DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $currentBatch->plot)
                ->update(['activebatchno' => $newBatchNo]);
            
            DB::commit();
            
            Log::info("Batch transitioned from panen", [
                'old_batch' => $currentBatchNo,
                'old_lifecycle' => $currentBatch->lifecyclestatus,
                'new_batch' => $newBatchNo,
                'new_lifecycle' => $nextLifecycle,
                'tanggalulangtahun' => $batchData['tanggalulangtahun'],
                'plantinglkhno' => $batchData['plantinglkhno']
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
     * Get next lifecycle (PC→RC1→RC2→RC3→PC empty)
     */
    private function getNextLifecycle($current)
    {
        return match($current) {
            'PC' => 'RC1',
            'RC1' => 'RC2',
            'RC2' => 'RC3',
            'RC3' => 'PC', // Empty PC (tanggalulangtahun=NULL)
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