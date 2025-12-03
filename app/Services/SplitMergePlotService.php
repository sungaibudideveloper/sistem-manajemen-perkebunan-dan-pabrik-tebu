<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * SplitMergePlotService
 * 
 * Handles execution of split and merge operations after approval
 * Logic extracted from SplitMergePlotController
 */
class SplitMergePlotService
{
    /**
     * Execute split operation based on transaction number
     * 
     * @param string $transactionNumber
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function executeSplit($transactionNumber, $companycode)
    {
        try {
            // Get transaction data
            $transaction = DB::table('plottransaction')
                ->where('companycode', $companycode)
                ->where('transactionnumber', $transactionNumber)
                ->first();
            
            if (!$transaction) {
                throw new \Exception("Transaction {$transactionNumber} tidak ditemukan");
            }
            
            if ($transaction->transactiontype !== 'SPLIT') {
                throw new \Exception("Transaction bukan tipe SPLIT");
            }
            
            // Decode JSON data
            $sourcePlots = json_decode($transaction->sourceplots, true);
            $resultPlots = json_decode($transaction->resultplots, true);
            $sourceBatches = json_decode($transaction->sourcebatches, true);
            $resultBatches = json_decode($transaction->resultbatches, true);
            $areaMap = json_decode($transaction->areamap, true);
            
            if (!$sourceBatches || count($sourceBatches) !== 1) {
                throw new \Exception("Split harus memiliki tepat 1 source batch");
            }
            
            $sourceBatchNo = $sourceBatches[0];
            
            // Get source batch
            $sourceBatch = DB::table('batch')
                ->where('companycode', $companycode)
                ->where('batchno', $sourceBatchNo)
                ->first();
            
            if (!$sourceBatch) {
                throw new \Exception("Source batch {$sourceBatchNo} tidak ditemukan");
            }
            
            if ($sourceBatch->isactive != 1) {
                throw new \Exception("Source batch {$sourceBatchNo} sudah tidak aktif");
            }
            
            // Get blok from source plot
            $sourceMasterlist = DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $sourceBatch->plot)
                ->first();
            
            // Close source batch
            DB::table('batch')
                ->where('companycode', $companycode)
                ->where('batchno', $sourceBatchNo)
                ->update([
                    'isactive' => 0,
                    'closedat' => now(),
                    'splitmergedreason' => $transaction->splitmergedreason
                ]);
            
            // Create new batches
            $newBatches = [];
            
            foreach ($resultPlots as $index => $newPlot) {
                $newBatchNo = $resultBatches[$index] ?? $this->generateBatchNo($companycode, now());
                $area = $areaMap[$newPlot] ?? 0;
                
                // Ensure plot exists in masterlist
                $existingPlot = DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $newPlot)
                    ->first();
                
                if (!$existingPlot) {
                    DB::table('masterlist')->insert([
                        'companycode' => $companycode,
                        'plot' => $newPlot,
                        'blok' => $sourceMasterlist->blok ?? null,
                        'activebatchno' => null,
                        'isactive' => 1
                    ]);
                }
                
                // Create new batch
                DB::table('batch')->insert([
                    'batchno' => $newBatchNo,
                    'companycode' => $companycode,
                    'plot' => $newPlot,
                    'batcharea' => $area,
                    'batchdate' => now()->format('Y-m-d'),
                    'lifecyclestatus' => $sourceBatch->lifecyclestatus,
                    'tanggalpanen' => $sourceBatch->tanggalpanen,
                    'tanggalulangtahun' => $sourceBatch->tanggalulangtahun,
                    'previousbatchno' => $sourceBatch->previousbatchno,
                    'plantinglkhno' => $sourceBatch->plantinglkhno,
                    'kontraktorid' => $sourceBatch->kontraktorid,
                    'kodevarietas' => $sourceBatch->kodevarietas,
                    'pkp' => $sourceBatch->pkp,
                    'plottype' => $sourceBatch->plottype,
                    'lastactivity' => $sourceBatch->lastactivity,
                    'splitfrombatchno' => $sourceBatchNo,
                    'splitmergedreason' => $transaction->splitmergedreason,
                    'isactive' => 1,
                    'inputby' => $transaction->inputby,
                    'createdat' => now()
                ]);
                
                // Update masterlist
                DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $newPlot)
                    ->update(['activebatchno' => $newBatchNo]);
                
                $newBatches[] = $newBatchNo;
                
                // Insert genealogy
                $this->insertGenealogy($companycode, $newBatchNo, $sourceBatchNo, 'SPLIT');
            }
            
            // Deactivate source plot if not in result plots
            if (!in_array($sourceBatch->plot, $resultPlots)) {
                DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $sourceBatch->plot)
                    ->update([
                        'isactive' => 0,
                        'activebatchno' => null
                    ]);
            }
            
            Log::info("Split executed successfully", [
                'transaction_number' => $transactionNumber,
                'source_batch' => $sourceBatchNo,
                'new_batches' => $newBatches
            ]);
            
            return [
                'success' => true,
                'message' => "Split berhasil dieksekusi. {$sourceBatchNo} → " . implode(', ', $newBatches),
                'data' => [
                    'source_batch' => $sourceBatchNo,
                    'new_batches' => $newBatches,
                    'result_plots' => $resultPlots
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error("Split execution failed", [
                'transaction_number' => $transactionNumber,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Execute merge operation based on transaction number
     * 
     * @param string $transactionNumber
     * @param string $companycode
     * @return array ['success' => bool, 'message' => string, 'data' => array]
     */
    public function executeMerge($transactionNumber, $companycode)
    {
        try {
            // Get transaction data
            $transaction = DB::table('plottransaction')
                ->where('companycode', $companycode)
                ->where('transactionnumber', $transactionNumber)
                ->first();
            
            if (!$transaction) {
                throw new \Exception("Transaction {$transactionNumber} tidak ditemukan");
            }
            
            if ($transaction->transactiontype !== 'MERGE') {
                throw new \Exception("Transaction bukan tipe MERGE");
            }
            
            // Decode JSON data
            $sourcePlots = json_decode($transaction->sourceplots, true);
            $resultPlots = json_decode($transaction->resultplots, true);
            $sourceBatches = json_decode($transaction->sourcebatches, true);
            $resultBatches = json_decode($transaction->resultbatches, true);
            $areaMap = json_decode($transaction->areamap, true);
            
            if (count($sourceBatches) < 2) {
                throw new \Exception("Merge memerlukan minimal 2 source batch");
            }
            
            if (count($resultPlots) !== 1 || count($resultBatches) !== 1) {
                throw new \Exception("Merge harus menghasilkan tepat 1 plot dan 1 batch");
            }
            
            $resultPlot = $resultPlots[0];
            $newBatchNo = $resultBatches[0];
            $totalArea = array_sum($areaMap);
            
            // Get and validate source batches
            $sourceBatchesData = [];
            foreach ($sourceBatches as $batchNo) {
                $batch = DB::table('batch')
                    ->where('companycode', $companycode)
                    ->where('batchno', $batchNo)
                    ->first();
                
                if (!$batch) {
                    throw new \Exception("Batch {$batchNo} tidak ditemukan");
                }
                
                if ($batch->isactive != 1) {
                    throw new \Exception("Batch {$batchNo} sudah tidak aktif");
                }
                
                $sourceBatchesData[] = $batch;
            }
            
            // Get dominant batch data
            $dominantBatch = collect($sourceBatchesData)->firstWhere('plot', $transaction->dominantplot);
            if (!$dominantBatch) {
                throw new \Exception("Dominant plot {$transaction->dominantplot} tidak ditemukan dalam source batches");
            }
            
            // Close all source batches
            foreach ($sourceBatches as $batchNo) {
                DB::table('batch')
                    ->where('companycode', $companycode)
                    ->where('batchno', $batchNo)
                    ->update([
                        'isactive' => 0,
                        'closedat' => now(),
                        'mergedtobatchno' => $newBatchNo,
                        'splitmergedreason' => $transaction->splitmergedreason
                    ]);
            }
            
            // Ensure result plot exists in masterlist
            $existingResultPlot = DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $resultPlot)
                ->first();
            
            if (!$existingResultPlot) {
                $dominantMasterlist = DB::table('masterlist')
                    ->where('companycode', $companycode)
                    ->where('plot', $transaction->dominantplot)
                    ->first();
                
                DB::table('masterlist')->insert([
                    'companycode' => $companycode,
                    'plot' => $resultPlot,
                    'blok' => $dominantMasterlist->blok ?? null,
                    'activebatchno' => null,
                    'isactive' => 1
                ]);
            }
            
            // Create merged batch
            DB::table('batch')->insert([
                'batchno' => $newBatchNo,
                'companycode' => $companycode,
                'plot' => $resultPlot,
                'batcharea' => $totalArea,
                'batchdate' => now()->format('Y-m-d'),
                'lifecyclestatus' => $dominantBatch->lifecyclestatus,
                'tanggalpanen' => $dominantBatch->tanggalpanen,
                'tanggalulangtahun' => $dominantBatch->tanggalulangtahun,
                'previousbatchno' => $dominantBatch->previousbatchno,
                'plantinglkhno' => $dominantBatch->plantinglkhno,
                'kontraktorid' => $dominantBatch->kontraktorid,
                'kodevarietas' => $dominantBatch->kodevarietas,
                'pkp' => $dominantBatch->pkp,
                'plottype' => $dominantBatch->plottype,
                'lastactivity' => $dominantBatch->lastactivity,
                'mergedtobatchno' => null,
                'splitmergedreason' => $transaction->splitmergedreason,
                'isactive' => 1,
                'inputby' => $transaction->inputby,
                'createdat' => now()
            ]);
            
            // Update masterlist for result plot
            DB::table('masterlist')
                ->where('companycode', $companycode)
                ->where('plot', $resultPlot)
                ->update(['activebatchno' => $newBatchNo]);
            
            // Deactivate old plots in masterlist
            foreach ($sourcePlots as $oldPlot) {
                if ($oldPlot !== $resultPlot) {
                    DB::table('masterlist')
                        ->where('companycode', $companycode)
                        ->where('plot', $oldPlot)
                        ->update([
                            'isactive' => 0,
                            'activebatchno' => null
                        ]);
                }
            }
            
            // Insert genealogy for all source batches
            foreach ($sourceBatches as $sourceBatchNo) {
                $this->insertGenealogy($companycode, $newBatchNo, $sourceBatchNo, 'MERGE');
            }
            
            Log::info("Merge executed successfully", [
                'transaction_number' => $transactionNumber,
                'source_batches' => $sourceBatches,
                'new_batch' => $newBatchNo,
                'result_plot' => $resultPlot
            ]);
            
            return [
                'success' => true,
                'message' => "Merge berhasil dieksekusi. " . implode(' + ', $sourcePlots) . " → {$resultPlot} ({$newBatchNo})",
                'data' => [
                    'source_batches' => $sourceBatches,
                    'source_plots' => $sourcePlots,
                    'new_batch' => $newBatchNo,
                    'result_plot' => $resultPlot,
                    'total_area' => $totalArea
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error("Merge execution failed", [
                'transaction_number' => $transactionNumber,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
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
    
    /**
     * Insert genealogy record
     */
    private function insertGenealogy($companycode, $childBatchNo, $parentBatchNo, $relationshipType)
    {
        // Get generation level from parent
        $parentGeneration = DB::table('batchgenealogy')
            ->where('companycode', $companycode)
            ->where('childbatchno', $parentBatchNo)
            ->max('generationlevel') ?? 0;
        
        DB::table('batchgenealogy')->insert([
            'companycode' => $companycode,
            'childbatchno' => $childBatchNo,
            'parentbatchno' => $parentBatchNo,
            'relationshiptype' => $relationshipType,
            'generationlevel' => $parentGeneration + 1,
            'createdat' => now()
        ]);
    }
}