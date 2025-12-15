<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Report;

use App\Repositories\Transaction\RencanaKerjaHarian\Report\DthReportRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\RkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;

/**
 * DthReportService
 * 
 * Orchestrates DTH report business logic.
 * RULE: No DB queries. Only orchestration.
 */
class DthReportService
{
    protected $dthRepo;
    protected $rkhRepo;
    protected $masterDataRepo;

    public function __construct(
        DthReportRepository $dthRepo,
        RkhRepository $rkhRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->dthRepo = $dthRepo;
        $this->rkhRepo = $rkhRepo;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Build DTH report payload
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function buildDthPayload($companycode, $date)
    {
        $companyInfo = $this->masterDataRepo->getCompanyInfo($companycode);
        $rkhNumbers = $this->rkhRepo->getRkhNumbersByDate($companycode, $date);
        $approvalSummary = $this->dthRepo->getRkhApprovalSummary($companycode, $date);
        
        $harianData = $this->dthRepo->getHarianData($companycode, $date);
        $boronganData = $this->dthRepo->getBoronganData($companycode, $date);
        $alatData = $this->dthRepo->getAlatData($companycode, $date);

        return [
            'company_info' => $companyInfo ? "{$companyInfo->companycode} - {$companyInfo->name}" : $companycode,
            'harian' => $harianData->toArray(),
            'borongan' => $boronganData->toArray(),
            'alat' => $alatData->toArray(),
            'rkh_numbers' => $rkhNumbers,
            'rkh_approval' => $approvalSummary,
            'date' => $date,
            'generated_at' => now()->format('d/m/Y H:i:s'),
            'debug' => [
                'harian_count' => $harianData->count(),
                'borongan_count' => $boronganData->count(),
                'alat_count' => $alatData->count(),
                'rkh_count' => count($rkhNumbers)
            ]
        ];
    }
}