<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Updated MaterialUsageGeneratorService
 * 
 * FIXED: Issues with material usage generation per LKH
 * - Fixed column name references (luasrkh vs luasplot)
 * - Added better error handling and debugging
 * - Fixed company code filtering consistency
 */
class MaterialUsageGeneratorService
{
    /**
     * Generate material usage data from approved RKH
     * UPDATED: Generate per LKH instead of per RKH (Option B)
     * 
     * @param string $rkhno
     * @return array
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
            
            // Get LKH list untuk RKH ini (CHANGED: process per LKH)
            $lkhList = DB::table('lkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->get();
                
            if ($lkhList->isEmpty()) {
                throw new \Exception("Tidak ada LKH ditemukan untuk RKH: {$rkhno}. Generate LKH terlebih dahulu.");
            }
            
            // Get RKH details that use material untuk reference
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
            
            // Create material usage header (still at RKH level for pickup reference)
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
            
            // Process each LKH (CHANGED: per LKH processing)
            foreach ($lkhList as $lkh) {
                try {
                    $itemsInserted = $this->processLkhMaterialUsage($lkh, $companycode, $rkhno, $rkhDetails);
                    $totalItemsInserted += $itemsInserted;
                    
                    
                } catch (\Exception $e) {
                    $errors[] = "Error processing LKH {$lkh->lkhno}: " . $e->getMessage();
                    Log::error("Error processing LKH material usage", [
                        'rkhno' => $rkhno,
                        'lkhno' => $lkh->lkhno,
                        'activitycode' => $lkh->activitycode,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            if ($totalItemsInserted === 0) {
                // Better error reporting
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
            Log::error("Error generating material usage for RKH {$rkhno}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal generate material usage: ' . $e->getMessage(),
                'total_items' => 0
            ];
        }
    }
    
    /**
     * Process individual LKH to generate material usage items
     * FIXED: Handle multiple herbisida groups dalam 1 LKH dan merge intersect items
     * 
     * @param object $lkh
     * @param string $companycode
     * @param string $rkhno
     * @param \Illuminate\Support\Collection $rkhDetails
     * @return int
     */
    private function processLkhMaterialUsage($lkh, $companycode, $rkhno, $rkhDetails)
    {
        // Find matching RKH details for this LKH activity
        $matchingRkhDetails = $rkhDetails->where('activitycode', $lkh->activitycode)
                                    ->where('jenistenagakerja', $lkh->jenistenagakerja);
        
        if ($matchingRkhDetails->isEmpty()) {
            return 0;
        }
        
        // Get plot details for this LKH untuk calculate total luas
        $lkhPlots = DB::table('lkhdetailplot')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkh->lkhno)
            ->get();
            
        if ($lkhPlots->isEmpty()) {
            throw new \Exception("Plot details tidak ditemukan untuk LKH: {$lkh->lkhno}");
        }
        
        $totalLuasLkh = $lkhPlots->sum('luasrkh');
        
        if ($totalLuasLkh <= 0) {
            Log::warning("Zero or negative area for LKH {$lkh->lkhno}", [
                'total_luas' => $totalLuasLkh,
                'plots' => $lkhPlots->toArray()
            ]);
            return 0;
        }
        
        $uniqueHerbisidaGroupIds = $matchingRkhDetails
            ->where('herbisidagroupid', '!=', null)
            ->pluck('herbisidagroupid')
            ->unique()
            ->values();
        
        if ($uniqueHerbisidaGroupIds->isEmpty()) {
            return 0;
        }
        
        $mergedItems = [];
        
        foreach ($uniqueHerbisidaGroupIds as $herbisidaGroupId) {
            // Get herbisida dosage data for this group
            $herbisidaDosages = DB::table('herbisidadosage as hd')
                ->join('herbisidagroup as hg', function($join) {
                    $join->on('hd.herbisidagroupid', '=', 'hg.herbisidagroupid');
                })
                ->join('herbisida as h', function($join) use ($companycode) {
                    $join->on('hd.companycode', '=', 'h.companycode')
                        ->on('hd.itemcode', '=', 'h.itemcode');
                })
                ->where('hd.companycode', $companycode)
                ->where('hd.herbisidagroupid', $herbisidaGroupId)
                ->where('hg.activitycode', $lkh->activitycode)
                ->select([
                    'hd.itemcode',
                    'hd.dosageperha',
                    'h.itemname',
                    'h.measure',
                    'hd.herbisidagroupid'
                ])
                ->get();
                
            if ($herbisidaDosages->isEmpty()) {
                Log::warning("Herbisida dosage not found", [
                    'company' => $companycode,
                    'herbisidagroupid' => $herbisidaGroupId,
                    'activitycode' => $lkh->activitycode
                ]);
                continue;
            }
            
            foreach ($herbisidaDosages as $dosage) {
                $qtyForThisGroup = $totalLuasLkh * $dosage->dosageperha;
                
                // Check if item already exists in merged array
                if (isset($mergedItems[$dosage->itemcode])) {
                    $oldQty = $mergedItems[$dosage->itemcode]['qty'];
                    $mergedItems[$dosage->itemcode]['qty'] += $qtyForThisGroup;
                    
                    Log::info("Merged intersect item", [
                        'itemcode' => $dosage->itemcode,
                        'old_qty' => $oldQty,
                        'added_qty' => $qtyForThisGroup,
                        'new_total_qty' => $mergedItems[$dosage->itemcode]['qty']
                    ]);
                } else {
                    $mergedItems[$dosage->itemcode] = [
                        'companycode' => $companycode,
                        'rkhno' => $rkhno,
                        'lkhno' => $lkh->lkhno,
                        'itemcode' => $dosage->itemcode,
                        'qty' => $qtyForThisGroup,
                        'qtyretur' => 0,
                        'unit' => $dosage->measure,
                        'nouse' => null,
                        'noretur' => null,
                        'itemname' => $dosage->itemname,
                        'herbisidagroupid' => $dosage->herbisidagroupid,
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
        
        $itemsInserted = 0;
        foreach ($mergedItems as $item) {
            DB::table('usemateriallst')->insert($item);
            $itemsInserted++;
        }
        
        Log::info("LKH Material Usage processed", [
            'lkhno' => $lkh->lkhno,
            'herbisida_groups_processed' => $uniqueHerbisidaGroupIds->count(),
            'total_items_inserted' => $itemsInserted,
            'herbisida_group_ids' => $uniqueHerbisidaGroupIds->toArray()
        ]);
        
        return $itemsInserted;
    }
    
    /**
     * Debug method to check data availability
     * NEW METHOD: For troubleshooting material generation issues
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
                'herbisida_dosages_count' => $herbisidaDosages->count(),
                'sample_herbisida_groups' => $herbisidaGroups->take(5)->toArray(),
                'sample_herbisida_dosages' => $herbisidaDosages->take(5)->toArray()
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }
    }
    
    // ... rest of the methods remain the same ...
    
    /**
     * Check if material usage can be generated for RKH
     * UPDATED: Check for LKH existence
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
        
        // Check if LKH exists (ADDED: LKH requirement)
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
     * NEW METHOD: Get material breakdown per LKH for cost analysis
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
     * Calculate total material cost per plot
     * NEW METHOD: For cost per plot analysis
     * 
     * @param string $blok
     * @param string $plot
     * @param string $dateFrom
     * @param string $dateTo
     * @return array
     */
    public function getPlotMaterialCostAnalysis($blok, $plot, $dateFrom, $dateTo)
    {
        try {
            $materialCosts = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', 'ldp.lkhno', '=', 'lh.lkhno')
                ->leftJoin('usemateriallst as uml', 'ldp.lkhno', '=', 'uml.lkhno')
                ->leftJoin('herbisida as h', function($join) {
                    $join->on('uml.companycode', '=', 'h.companycode')
                         ->on('uml.itemcode', '=', 'h.itemcode');
                })
                ->where('ldp.blok', $blok)
                ->where('ldp.plot', $plot)
                ->whereBetween('lh.lkhdate', [$dateFrom, $dateTo])
                ->select([
                    'lh.lkhdate',
                    'lh.lkhno',
                    'lh.activitycode',
                    'uml.itemname',
                    'uml.qty',
                    'uml.unit',
                    'h.unitprice',
                    DB::raw('(uml.qty * COALESCE(h.unitprice, 0)) as material_cost'),
                    'ldp.luasrkh',
                    'ldp.luashasil'
                ])
                ->get();
                
            $totalMaterialCost = $materialCosts->sum('material_cost');
            
            return [
                'success' => true,
                'blok' => $blok,
                'plot' => $plot,
                'period' => "{$dateFrom} to {$dateTo}",
                'material_details' => $materialCosts,
                'total_material_cost' => $totalMaterialCost
            ];
            
        } catch (\Exception $e) {
            Log::error("Error getting plot material cost analysis: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error getting plot material cost: ' . $e->getMessage(),
                'total_material_cost' => 0
            ];
        }
    }
}