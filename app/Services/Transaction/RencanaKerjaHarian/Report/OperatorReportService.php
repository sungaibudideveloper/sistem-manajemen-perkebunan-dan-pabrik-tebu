<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Report;

use App\Repositories\Transaction\RencanaKerjaHarian\Report\OperatorReportRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Carbon\Carbon;

/**
 * OperatorReportService
 * 
 * Orchestrates Operator report business logic.
 * RULE: No DB queries. Only orchestration + formatting.
 */
class OperatorReportService
{
    protected $operatorRepo;
    protected $masterDataRepo;

    public function __construct(
        OperatorReportRepository $operatorRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->operatorRepo = $operatorRepo;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Get operators list for date
     */
    public function getOperatorsForDate($companycode, $date)
    {
        $operators = $this->operatorRepo->listOperatorsForDate($companycode, $date);

        return $operators->map(function($op) {
            return [
                'tenagakerjaid' => $op->tenagakerjaid,
                'nama' => $op->nama,
                'nokendaraan' => $op->nokendaraan,
                'jenis' => $op->jenis
            ];
        })->toArray();
    }

    /**
     * Build operator report payload
     */
    public function buildOperatorReportPayload($companycode, $date, $operatorId)
    {
        $operatorInfo = $this->operatorRepo->getOperatorInfo($companycode, $operatorId);
        
        if (!$operatorInfo) {
            return [
                'success' => false,
                'message' => 'Data operator tidak ditemukan'
            ];
        }

        $activities = $this->operatorRepo->getOperatorActivitiesForDate($companycode, $date, $operatorId);
        $formattedActivities = $this->formatActivities($activities);
        $totals = $this->calculateTotals($formattedActivities);
        $companyInfo = $this->masterDataRepo->getCompanyInfo($companycode);

        return [
            'success' => true,
            'date' => $date,
            'date_formatted' => Carbon::parse($date)->format('d F Y'),
            'company_info' => $companyInfo ? "{$companyInfo->companycode} - {$companyInfo->name}" : $companycode,
            'operator_info' => $operatorInfo,
            'activities' => $formattedActivities,
            'totals' => $totals,
            'generated_at' => now()->format('d/m/Y H:i:s')
        ];
    }

    /**
     * Format activities
     */
    private function formatActivities($activities)
    {
        return $activities->map(function($activity) {
            return [
                'jam_mulai' => substr($activity->jammulai, 0, 5),
                'jam_selesai' => substr($activity->jamselesai, 0, 5),
                'durasi_kerja' => $activity->durasi_kerja ? substr($activity->durasi_kerja, 0, 5) : '00:00',
                'plots_display' => $activity->plots_display ?: '-',
                'activitycode' => $activity->activitycode,
                'activityname' => $activity->activityname,
                'luas_rencana_ha' => number_format((float)$activity->luas_rencana_ha, 2),
                'luas_hasil_ha' => number_format((float)$activity->luas_hasil_ha, 2),
                'solar_liter' => $activity->solar ? number_format((float)$activity->solar, 1) : null,
                'solar_display' => $activity->solar ? number_format((float)$activity->solar, 1) . ' L' : 'Belum diinput',
                'hourmeter_start' => $activity->hourmeterstart ? number_format((float)$activity->hourmeterstart, 1) : null,
                'hourmeter_end' => $activity->hourmeterend ? number_format((float)$activity->hourmeterend, 1) : null,
                'lkhno' => $activity->lkhno,
                'rkhno' => $activity->rkhno
            ];
        })->toArray();
    }

    /**
     * Calculate totals
     */
    private function calculateTotals($formattedActivities)
    {
        $totalActivities = count($formattedActivities);
        $totalLuasRencana = 0;
        $totalLuasHasil = 0;
        $totalSolar = 0;
        $totalDurationMinutes = 0;

        foreach ($formattedActivities as $activity) {
            $totalLuasRencana += (float)str_replace(',', '', $activity['luas_rencana_ha']);
            $totalLuasHasil += (float)str_replace(',', '', $activity['luas_hasil_ha']);
            
            if ($activity['solar_liter']) {
                $totalSolar += (float)$activity['solar_liter'];
            }
            
            if ($activity['durasi_kerja'] && $activity['durasi_kerja'] !== '00:00') {
                list($hours, $minutes) = explode(':', $activity['durasi_kerja']);
                $totalDurationMinutes += ($hours * 60) + $minutes;
            }
        }

        return [
            'total_activities' => $totalActivities,
            'total_luas_rencana' => $totalLuasRencana,
            'total_luas_hasil' => $totalLuasHasil,
            'total_solar' => $totalSolar,
            'total_duration_minutes' => $totalDurationMinutes
        ];
    }
}