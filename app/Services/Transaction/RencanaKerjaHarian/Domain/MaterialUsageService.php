<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Domain;

use App\Repositories\Transaction\RencanaKerjaHarian\Domain\MaterialUsageRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\RkhRepository;
use App\Services\Transaction\RencanaKerjaHarian\Generator\MaterialUsageGeneratorService;

/**
 * MaterialUsageService
 * 
 * Orchestrates material usage business logic.
 * RULE: No DB queries. Only orchestration.
 */
class MaterialUsageService
{
    protected $materialRepo;
    protected $rkhRepo;

    public function __construct(
        MaterialUsageRepository $materialRepo,
        RkhRepository $rkhRepo
    ) {
        $this->materialRepo = $materialRepo;
        $this->rkhRepo = $rkhRepo;
    }

    /**
     * Get material usage data for RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function getMaterialUsageData($companycode, $rkhno)
    {
        $hasMaterial = $this->materialRepo->existsForRkhNo($companycode, $rkhno);
        
        if (!$hasMaterial) {
            return [
                'success' => true,
                'data' => collect(),
                'has_material_usage' => false
            ];
        }

        $materialUsage = $this->materialRepo->getUsageByRkhNo($companycode, $rkhno);
        
        return [
            'success' => true,
            'data' => $materialUsage,
            'has_material_usage' => true
        ];
    }

    /**
     * Build material usage API payload
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function buildMaterialUsagePayload($companycode, $rkhno)
    {
        $result = $this->getMaterialUsageData($companycode, $rkhno);
        
        if (!$result['success'] || !$result['has_material_usage']) {
            return [
                'success' => false,
                'message' => 'Tidak ada material usage untuk RKH ini',
                'material_groups' => [],
                'total_items' => 0
            ];
        }

        $groupedData = $this->groupMaterialUsageData($result['data']);
        $firstItem = $result['data']->first();

        return [
            'success' => true,
            'rkhno' => $rkhno,
            'totalluas' => $firstItem->totalluas ?? 0,
            'flagstatus' => $firstItem->flagstatus ?? 'N/A',
            'createdat' => $firstItem->createdat ?? null,
            'inputby' => $firstItem->inputby ?? 'N/A',
            'material_groups' => $groupedData,
            'total_items' => $result['data']->count()
        ];
    }

    /**
     * Generate material usage manually
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function generateMaterialUsage($companycode, $rkhno)
    {
        // Get RKH with approval check
        $rkh = $this->rkhRepo->getHeader($companycode, $rkhno);
        
        if (!$rkh) {
            return [
                'success' => false,
                'message' => 'RKH tidak ditemukan'
            ];
        }
        
        if (!$this->isRkhFullyApproved($rkh)) {
            return [
                'success' => false,
                'message' => 'RKH belum fully approved'
            ];
        }
        
        $materialUsageGenerator = new MaterialUsageGeneratorService();
        return $materialUsageGenerator->generateMaterialUsageFromRkh($rkhno);
    }

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Group material usage data by herbisida group
     */
    private function groupMaterialUsageData($materialUsage)
    {
        return $materialUsage->groupBy('herbisidagroupid')->map(function($items, $groupId) {
            $firstItem = $items->first();
            return [
                'herbisidagroupid' => $groupId,
                'herbisidagroupname' => $firstItem->herbisidagroupname ?? 'Unknown Group',
                'items' => $items->map(function($item) {
                    return [
                        'itemcode' => $item->itemcode,
                        'itemname' => $item->itemname,
                        'qty' => number_format($item->qty, 2),
                        'unit' => $item->unit,
                        'dosageperha' => number_format($item->dosageperha, 2)
                    ];
                })->toArray()
            ];
        })->values();
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
}