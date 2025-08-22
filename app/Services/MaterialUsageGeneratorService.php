<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Fixed MaterialUsageGeneratorService
 * 
 * FIXED: Removed herbisidagroupid from usemateriallst inserts
 * - Material items from different groups are merged by itemcode
 * - herbisidagroupid is not stored in usemateriallst table
 */
class MaterialUsageGeneratorService
{
    /**
     * Generate material usage data from approved RKH
     */
    public function generateMaterialUsageFromRkh($rkhno)
    {
        try {
            DB::beginTransaction();
            
            // Get RKH header data
            $rkhHeader = DB::table('rkhhdr')
                ->where('rkhno', $rkhno)
                ->first();
                
            if (!$rkhHeader) {
                throw new \Exception("RKH tidak ditemukan: {$rkhno}");
            }
            
            $companycode = $rkhHeader->companycode;
            
            // Check if material usage already exists
            $existingUsage = DB::table('usematerialhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->first();
                
            if ($existingUsage) {
                throw new \Exception("Material usage sudah pernah di-generate untuk RKH: {$rkhno}");
            }
            
            // Get LKH list untuk RKH ini
            $lkhList = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->get();
                
            if ($lkhList->isEmpty()) {
                throw new \Exception("Tidak ada LKH ditemukan untuk RKH: {$rkhno}. Generate LKH terlebih dahulu.");
            }
            
            // Get RKH details that use material
            $rkhDetails = DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->where('usingmaterial', 1)
                ->get();
                
            if ($rkhDetails->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Tidak ada aktivitas yang menggunakan material',
                    'total_items' => 0
                ];
            }
            
            // Calculate total luas for header
            $totalLuas = $rkhDetails->sum('luasarea');
            
            // Create material usage header
            DB::table('usematerialhdr')->insert([
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'totalluas' => $totalLuas,
                'flagstatus' => 'ACTIVE',
                'inputby' => auth()->user()->userid ?? 'system',
                'createdat' => now(),
                'updateby' => null,
                'updatedat' => null
            ]);
            
            $totalItemsInserted = 0;
            $errors = [];
            
            // Process each LKH
            foreach ($lkhList as $lkh) {
                try {
                    $itemsInserted = $this->processLkhMaterialUsage($lkh, $companycode, $rkhno, $rkhDetails);
                    $totalItemsInserted += $itemsInserted;
                    
                } catch (\Exception $e) {
                    $errors[] = "Error processing LKH {$lkh->lkhno}: " . $e->getMessage();
                }
            }
            
            if ($totalItemsInserted === 0) {
                $errorDetail = empty($errors) ? "No matching material configuration found" : implode('; ', $errors);
                throw new \Exception("Tidak ada item material yang berhasil di-generate. Details: " . $errorDetail);
            }
            
            DB::commit();
            
            $message = "Material usage berhasil di-generate per LKH ({$totalItemsInserted} items)";
            if (!empty($errors)) {
                $message .= ". Warnings: " . implode('; ', $errors);
            }
            
            return [
                'success' => true,
                'message' => $message,
                'total_items' => $totalItemsInserted,
                'total_lkh_processed' => $lkhList->count(),
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Gagal generate material usage: ' . $e->getMessage(),
                'total_items' => 0
            ];
        }
    }
    
    /**
     * Process individual LKH to generate material usage items
     * FIXED: Sum quantities by itemcode regardless of herbisidagroupid
     */
    private function processLkhMaterialUsage($lkh, $companycode, $rkhno, $rkhDetails)
    {
        // Find matching RKH details for this LKH activity
        $matchingRkhDetails = $rkhDetails->where('activitycode', $lkh->activitycode)
                                    ->where('jenistenagakerja', $lkh->jenistenagakerja);
        
        if ($matchingRkhDetails->isEmpty()) {
            return 0;
        }
        
        // Get plot details for this LKH
        $lkhPlots = DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkh->lkhno)
            ->get();
            
        if ($lkhPlots->isEmpty()) {
            throw new \Exception("Plot details tidak ditemukan untuk LKH: {$lkh->lkhno}");
        }
        
        $mergedItems = [];
        
        // Process each plot
        foreach ($lkhPlots as $plot) {
            // Find corresponding RKH detail for this specific plot
            $rkhForPlot = $matchingRkhDetails->where('blok', $plot->blok)
                                        ->where('plot', $plot->plot)
                                        ->first();
            
            if (!$rkhForPlot || !$rkhForPlot->herbisidagroupid) {
                continue;
            }
            
            $plotLuas = (float)$plot->luasrkh;
            
            // Get herbisida dosage data for this plot's herbisida group
            $herbisidaDosages = DB::table('herbisidadosage as hd')
                ->join('herbisidagroup as hg', function($join) {
                    $join->on('hd.herbisidagroupid', '=', 'hg.herbisidagroupid');
                })
                ->join('herbisida as h', function($join) use ($companycode) {
                    $join->on('hd.companycode', '=', 'h.companycode')
                        ->on('hd.itemcode', '=', 'h.itemcode');
                })
                ->where('hd.companycode', $companycode)
                ->where('hd.herbisidagroupid', $rkhForPlot->herbisidagroupid)
                ->where('hg.activitycode', $lkh->activitycode)
                ->select([
                    'hd.itemcode',
                    'hd.dosageperha',
                    'h.itemname',
                    'h.measure'
                ])
                ->get();
                
            if ($herbisidaDosages->isEmpty()) {
                continue;
            }
            
            // Calculate quantity for each item in this plot
            foreach ($herbisidaDosages as $dosage) {
                $qtyForThisPlot = $plotLuas * $dosage->dosageperha;
                
                // Sum by itemcode regardless of herbisidagroupid
                if (isset($mergedItems[$dosage->itemcode])) {
                    $mergedItems[$dosage->itemcode]['qty'] += $qtyForThisPlot;
                } else {
                    $mergedItems[$dosage->itemcode] = [
                        'companycode' => $companycode,
                        'rkhno' => $rkhno,
                        'lkhno' => $lkh->lkhno,
                        'itemcode' => $dosage->itemcode,
                        'qty' => $qtyForThisPlot,
                        'qtyretur' => 0,
                        'unit' => $dosage->measure,
                        'nouse' => null,
                        'noretur' => null,
                        'itemname' => $dosage->itemname,
                        'dosageperha' => $dosage->dosageperha,
                        'returby' => null,
                        'tglretur' => null,
                        'tglterimaretur' => null,
                        'terimareturby' => null,
                        'qtydigunakan' => null
                    ];
                }
            }
        }
        
        // Insert merged items
        $itemsInserted = 0;
        foreach ($mergedItems as $item) {
            DB::table('usemateriallst')->insert($item);
            $itemsInserted++;
        }
        
        return $itemsInserted;
    }
    
    /**
     * Check if material usage can be generated for RKH
     * 
     * @param string $rkhno
     * @return bool
     */
    public function canGenerateMaterialUsage($rkhno)
    {
        // Check if RKH exists
        $rkhHeader = DB::table('rkhhdr')->where('rkhno', $rkhno)->first();
        if (!$rkhHeader) {
            return false;
        }
        
        // Check if already generated
        $existingUsage = DB::table('usematerialhdr')
            ->where('companycode', $rkhHeader->companycode)
            ->where('rkhno', $rkhno)
            ->exists();
            
        if ($existingUsage) {
            return false;
        }
        
        // Check if LKH exists
        $hasLkh = DB::table('lkhhdr')
            ->where('companycode', $rkhHeader->companycode)
            ->where('rkhno', $rkhno)
            ->exists();
            
        if (!$hasLkh) {
            return false;
        }
        
        // Check if has material usage
        $hasMaterialUsage = DB::table('rkhlst')
            ->where('companycode', $rkhHeader->companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->exists();
            
        return $hasMaterialUsage;
    }
    
    /**
     * Get material usage summary per LKH
     * 
     * @param string $lkhno
     * @return array
     */
    public function getLkhMaterialUsageSummary($lkhno)
    {
        try {
            $materialUsage = DB::table('usemateriallst as uml')
                ->leftJoin('herbisida as h', function($join) {
                    $join->on('uml.companycode', '=', 'h.companycode')
                         ->on('uml.itemcode', '=', 'h.itemcode');
                })
                ->where('uml.lkhno', $lkhno)
                ->select([
                    'uml.*',
                    'h.unitprice',
                    DB::raw('(uml.qty * COALESCE(h.unitprice, 0)) as total_cost')
                ])
                ->get();
                
            $totalCost = $materialUsage->sum('total_cost');
            $totalQty = $materialUsage->sum('qty');
            
            return [
                'success' => true,
                'lkhno' => $lkhno,
                'materials' => $materialUsage,
                'total_items' => $materialUsage->count(),
                'total_qty' => $totalQty,
                'total_cost' => $totalCost
            ];
            
        } catch (\Exception $e) {
            Log::error("Error getting LKH material usage summary: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error getting material usage summary: ' . $e->getMessage(),
                'materials' => collect(),
                'total_cost' => 0
            ];
        }
    }
    
    /**
     * Debug method to check data availability
     * 
     * @param string $rkhno
     * @return array
     */
    public function debugMaterialUsageData($rkhno)
    {
        try {
            $rkhHeader = DB::table('rkhhdr')->where('rkhno', $rkhno)->first();
            if (!$rkhHeader) {
                return ['error' => 'RKH not found'];
            }
            
            $companycode = $rkhHeader->companycode;
            
            // Check RKH details with material
            $rkhDetails = DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->get();
                
            $rkhDetailsWithMaterial = $rkhDetails->where('usingmaterial', 1);
            
            // Check LKH list
            $lkhList = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->get();
            
            // Check herbisida groups
            $herbisidaGroups = DB::table('herbisidagroup')
                ->where('companycode', $companycode)
                ->get();
                
            // Check herbisida dosages
            $herbisidaDosages = DB::table('herbisidadosage')
                ->where('companycode', $companycode)
                ->get();
            
            return [
                'rkh_header' => $rkhHeader,
                'rkh_details_total' => $rkhDetails->count(),
                'rkh_details_with_material' => $rkhDetailsWithMaterial->count(),
                'rkh_details_with_material_data' => $rkhDetailsWithMaterial->toArray(),
                'lkh_count' => $lkhList->count(),
                'lkh_data' => $lkhList->toArray(),
                'herbisida_groups_count' => $herbisidaGroups->count(),
                'herbisida_dosages_count' => $herbisidaDosages->count()
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
}