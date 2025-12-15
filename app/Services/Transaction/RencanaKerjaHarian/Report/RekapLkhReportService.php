<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Report;

use App\Repositories\Transaction\RencanaKerjaHarian\Report\RekapLkhReportRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\LkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;

/**
 * RekapLkhReportService
 * 
 * Orchestrates Rekap LKH report business logic.
 * RULE: No DB queries. Only orchestration + grouping logic.
 */
class RekapLkhReportService
{
    protected $rekapRepo;
    protected $lkhRepo;
    protected $masterDataRepo;

    public function __construct(
        RekapLkhReportRepository $rekapRepo,
        LkhRepository $lkhRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->rekapRepo = $rekapRepo;
        $this->lkhRepo = $lkhRepo;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Build Rekap LKH payload
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function buildRekapPayload($companycode, $date)
    {
        $companyInfo = $this->masterDataRepo->getCompanyInfo($companycode);
        $lkhNumbers = $this->getLkhNumbers($companycode, $date);
        
        $allRows = $this->rekapRepo->getAllLkhRowsForDate($companycode, $date);
        $grouped = $this->groupByActivityGroup($allRows);

        return [
            'company_info' => $companyInfo ? "{$companyInfo->companycode} - {$companyInfo->name}" : $companycode,
            'pengolahan' => $grouped['pengolahan'],
            'perawatan' => $grouped['perawatan'],
            'panen' => $grouped['panen'],
            'pias' => $grouped['pias'],
            'lainlain' => $grouped['lainlain'],
            'lkh_numbers' => $lkhNumbers,
            'date' => $date,
            'generated_at' => now()->format('d/m/Y H:i:s')
        ];
    }

    /**
     * Get LKH numbers for date
     */
    private function getLkhNumbers($companycode, $date)
    {
        return \DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('lkhdate', $date)
            ->pluck('lkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Group data by activity group
     */
    private function groupByActivityGroup($allRows)
    {
        $grouped = [
            'pengolahan' => [],
            'perawatan' => ['pc' => [], 'rc' => []],
            'panen' => [],
            'pias' => [],
            'lainlain' => []
        ];

        foreach ($allRows as $record) {
            $item = (object)[
                'lkhno' => $record->lkhno,
                'activitycode' => $record->activitycode,
                'activityname' => $record->activityname,
                'totalworkers' => $record->totalworkers,
                'totalhasil' => $record->totalhasil,
                'totalupahall' => $record->totalupahall,
                'plot' => $record->plot,
                'luasarea' => $record->luasarea ?: 0,
                'mandor_nama' => $record->mandor_nama ?: '-',
                'operator_nama' => $record->operator_nama ?: '-',
            ];

            $activityGroup = $record->activitygroup;
            $activityCode = $record->activitycode;

            switch ($activityGroup) {
                case 'I':
                case 'II':
                    if (!isset($grouped['pengolahan'][$activityCode])) {
                        $grouped['pengolahan'][$activityCode] = [];
                    }
                    $grouped['pengolahan'][$activityCode][] = $item;
                    break;

                case 'III':
                    $type = (strpos($activityCode, '3.2.') === 0) ? 'rc' : 'pc';
                    if (!isset($grouped['perawatan'][$type][$activityCode])) {
                        $grouped['perawatan'][$type][$activityCode] = [];
                    }
                    $grouped['perawatan'][$type][$activityCode][] = $item;
                    break;

                case 'IV':
                    if (!isset($grouped['panen'][$activityCode])) {
                        $grouped['panen'][$activityCode] = [];
                    }
                    $grouped['panen'][$activityCode][] = $item;
                    break;

                case 'V':
                    if (!isset($grouped['pias'][$activityCode])) {
                        $grouped['pias'][$activityCode] = [];
                    }
                    $grouped['pias'][$activityCode][] = $item;
                    break;

                case 'VI':
                case 'VII':
                case 'VIII':
                    if (!isset($grouped['lainlain'][$activityCode])) {
                        $grouped['lainlain'][$activityCode] = [];
                    }
                    $grouped['lainlain'][$activityCode][] = $item;
                    break;
            }
        }

        return $grouped;
    }
}