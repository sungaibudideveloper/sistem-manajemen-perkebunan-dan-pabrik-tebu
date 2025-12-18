<?php

namespace App\Services\Transaction\RencanaKerjaHarian\Report;

use App\Repositories\Transaction\RencanaKerjaHarian\Report\OperatorRekapReportRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\Shared\MasterDataRepository;
use Carbon\Carbon;

/**
 * OperatorRekapReportService
 * 
 * Orchestrates Operator Rekap report business logic (all operators summary).
 * RULE: No DB queries. Only orchestration + formatting.
 */
class OperatorRekapReportService
{
    protected $operatorRekapRepo;
    protected $masterDataRepo;

    public function __construct(
        OperatorRekapReportRepository $operatorRekapRepo,
        MasterDataRepository $masterDataRepo
    ) {
        $this->operatorRekapRepo = $operatorRekapRepo;
        $this->masterDataRepo = $masterDataRepo;
    }

    /**
     * Build operator rekap report payload
     */
    // OperatorRekapReportService.php

    public function buildOperatorRekapReportPayload($companycode, $date)
    {
        $allData = $this->operatorRekapRepo->getAllOperatorsWithActivities($companycode, $date);
        
        if ($allData->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada data operator untuk tanggal ini'
            ];
        }

        // Group by operator (HANYA untuk hitung grand totals)
        $groupedByOperator = $allData->groupBy('tenagakerjaid');
        
        $allActivities = [];
        $totalOperators = $groupedByOperator->count(); // Hitung jumlah operator
        
        // Initialize grand totals
        $totalLuasRencana = 0;
        $totalLuasHasil = 0;
        $totalSolar = 0;
        $totalDurationMinutes = 0;
        
        foreach ($groupedByOperator as $operatorId => $activities) {
            $firstActivity = $activities->first();
            
            // Loop semua aktivitas operator ini
            foreach ($activities as $activity) {
                // Check if values are NULL or 0
                $jamMulai = $activity->jammulai && $activity->jammulai !== '00:00:00' 
                    ? substr($activity->jammulai, 0, 5) 
                    : null;
                
                $jamSelesai = $activity->jamselesai && $activity->jamselesai !== '00:00:00' 
                    ? substr($activity->jamselesai, 0, 5) 
                    : null;
                
                $durasiKerja = $activity->durasi_kerja && $activity->durasi_kerja !== '00:00:00' 
                    ? substr($activity->durasi_kerja, 0, 5) 
                    : null;
                
                $luasHasil = $activity->luas_hasil_ha && (float)$activity->luas_hasil_ha > 0 
                    ? number_format((float)$activity->luas_hasil_ha, 2) 
                    : null;
                
                $solarLiter = $activity->solar && (float)$activity->solar > 0 
                    ? number_format((float)$activity->solar, 1) 
                    : null;
                
                // Add to activities array
                $allActivities[] = [
                    'operator_name' => $firstActivity->operator_name,
                    'nokendaraan' => $firstActivity->nokendaraan,
                    'vehicle_type' => $firstActivity->vehicle_type,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'durasi_kerja' => $durasiKerja,
                    'activitycode' => $activity->activitycode,
                    'activityname' => $activity->activityname,
                    'plots_display' => $activity->plots_display ?: '-',
                    'luas_rencana_ha' => number_format((float)$activity->luas_rencana_ha, 2),
                    'luas_hasil_ha' => $luasHasil,
                    'solar_liter' => $solarLiter,
                    'lkhno' => $activity->lkhno,
                ];
                
                // ✅ Calculate grand totals SEKALIGUS
                $totalLuasRencana += (float)$activity->luas_rencana_ha;
                
                if (!is_null($luasHasil)) {
                    $totalLuasHasil += (float)str_replace(',', '', $luasHasil);
                }
                
                if (!is_null($solarLiter)) {
                    $totalSolar += (float)str_replace(',', '', $solarLiter);
                }
                
                if (!is_null($durasiKerja)) {
                    $durationParts = explode(':', $durasiKerja);
                    $hours = (int)$durationParts[0];
                    $minutes = (int)$durationParts[1];
                    $totalDurationMinutes += ($hours * 60) + $minutes;
                }
            }
        }
        
        // Format total duration
        $totalHours = floor($totalDurationMinutes / 60);
        $totalMinutes = $totalDurationMinutes % 60;
        
        $companyInfo = $this->masterDataRepo->getCompanyInfo($companycode);
        
        return [
            'success' => true,
            'date' => $date,
            'date_formatted' => Carbon::parse($date)->format('d F Y'),
            'company_info' => $companyInfo ? "{$companyInfo->companycode} - {$companyInfo->name}" : $companycode,
            'all_activities' => $allActivities,
            'grand_totals' => [
                'total_operators' => $totalOperators,
                'total_activities' => count($allActivities),
                'total_luas_rencana' => $totalLuasRencana,
                'total_luas_rencana_formatted' => number_format($totalLuasRencana, 2),
                'total_luas_hasil' => $totalLuasHasil,
                'total_luas_hasil_formatted' => $totalLuasHasil > 0 ? number_format($totalLuasHasil, 2) : null,
                'total_solar' => $totalSolar,
                'total_solar_formatted' => $totalSolar > 0 ? number_format($totalSolar, 1) : null,
                'total_duration_minutes' => $totalDurationMinutes,
                'total_duration_hours' => $totalHours,
                'total_duration_minutes_remainder' => $totalMinutes,
            ],
            'generated_at' => now()->format('d/m/Y H:i:s')
        ];
    }
}