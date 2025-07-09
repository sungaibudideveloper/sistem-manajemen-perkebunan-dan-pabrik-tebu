<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaterialUsageGeneratorService
{
    /**
     * Generate material usage data from approved RKH
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
            
            // Process each RKH detail
            foreach ($rkhDetails as $detail) {
                try {
                    $itemsInserted = $this->processRkhDetail($detail, $companycode, $rkhno);
                    $totalItemsInserted += $itemsInserted;
                } catch (\Exception $e) {
                    $errors[] = "Error processing detail {$detail->activitycode}: " . $e->getMessage();
                    Log::error("Error processing RKH detail", [
                        'rkhno' => $rkhno,
                        'activitycode' => $detail->activitycode,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            if ($totalItemsInserted === 0) {
                throw new \Exception("Tidak ada item material yang berhasil di-generate. Errors: " . implode('; ', $errors));
            }
            
            DB::commit();
            
            $message = "Material usage berhasil di-generate ({$totalItemsInserted} items)";
            if (!empty($errors)) {
                $message .= ". Warnings: " . implode('; ', $errors);
            }
            
            return [
                'success' => true,
                'message' => $message,
                'total_items' => $totalItemsInserted,
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error generating material usage for RKH {$rkhno}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal generate material usage: ' . $e->getMessage(),
                'total_items' => 0
            ];
        }
    }
    
    /**
     * Process individual RKH detail to generate material usage items
     * 
     * @param object $detail
     * @param string $companycode
     * @param string $rkhno
     * @return int
     */
    private function processRkhDetail($detail, $companycode, $rkhno)
    {
        // Get herbisida dosage data for this activity and group
        $herbisidaDosages = DB::table('herbisidadosage as hd')
            ->join('herbisidagroup as hg', function($join) {
                $join->on('hd.herbisidagroupid', '=', 'hg.herbisidagroupid');
            })
            ->join('herbisida as h', function($join) use ($companycode) {
                $join->on('hd.companycode', '=', 'h.companycode')
                     ->on('hd.itemcode', '=', 'h.itemcode');
            })
            ->where('hd.companycode', $companycode)
            ->where('hd.herbisidagroupid', $detail->herbisidagroupid)
            ->where('hg.activitycode', $detail->activitycode)
            ->select([
                'hd.itemcode',
                'hd.dosageperha',
                'hd.dosageunit',
                'h.itemname',
                'h.measure',
                'hd.herbisidagroupid'
            ])
            ->get();
            
        if ($herbisidaDosages->isEmpty()) {
            throw new \Exception("Data tidak ditemukan di herbisidadosage untuk activitycode: {$detail->activitycode}, herbisidagroupid: {$detail->herbisidagroupid}");
        }
        
        $itemsInserted = 0;
        
        foreach ($herbisidaDosages as $dosage) {
            // Calculate quantity: luasarea * dosageperha
            $qty = $detail->luasarea * $dosage->dosageperha;
            
            // Insert to usemateriallst
            DB::table('usemateriallst')->insert([
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'itemcode' => $dosage->itemcode,
                'qty' => $qty,
                'qtyretur' => 0,
                'unit' => $dosage->dosageunit ?: $dosage->measure,
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
            ]);
            
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
        
        // Check if has material usage
        $hasMaterialUsage = DB::table('rkhlst')
            ->where('companycode', $rkhHeader->companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->exists();
            
        return $hasMaterialUsage;
    }
}