<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Report;

use App\Repositories\Transaction\RencanaKerjaHarian\Report\RekapLkhReportRepository;

/**
 * RekapLkhReportService
 * 
 * Business logic for LKH Rekap report.
 * Updated to include summary statistics.
 */
class RekapLkhReportService
{
    protected $rekapRepo;

    public function __construct(RekapLkhReportRepository $rekapRepo)
    {
        $this->rekapRepo = $rekapRepo;
    }

    /**
     * Build rekap payload with grouped activities and summary stats
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function buildRekapPayload($companycode, $date)
    {
        // Get all approved LKH rows
        $allLkhData = $this->rekapRepo->getAllLkhRowsForDate($companycode, $date);
        
        // Get summary statistics
        $summary = $this->rekapRepo->getLkhSummaryForDate($companycode, $date);

        // Group data by activity group
        $grouped = [
            'pengolahan' => [],  // I, II
            'perawatan' => ['pc' => [], 'rc' => []], // III
            'panen' => [],       // IV
            'pias' => [],        // V
            'lainlain' => []     // VI, VII, VIII
        ];

        foreach ($allLkhData as $record) {
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

            // Route to correct section
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

        return [
            'pengolahan' => $grouped['pengolahan'],
            'perawatan' => $grouped['perawatan'],
            'panen' => $grouped['panen'],
            'pias' => $grouped['pias'],
            'lainlain' => $grouped['lainlain'],
            'summary' => $summary, // ✅ NEW: Add summary stats
        ];
    }
}