<?php

namespace App\Services\Transaction\RencanaKerjaHarian;

use App\Repositories\Transaction\RencanaKerjaHarian\DthReportRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\RekapReportRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\OperatorReportRepository;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * ReportService
 * 
 * Orchestrates all RKH/LKH report generation
 * Handles DTH (Distribusi Tenaga Harian), Rekap LKH, and Operator Reports
 */
class ReportService
{
    protected DthReportRepository $dthRepo;
    protected RekapReportRepository $rekapRepo;
    protected OperatorReportRepository $operatorRepo;

    public function __construct(
        DthReportRepository $dthRepo,
        RekapReportRepository $rekapRepo,
        OperatorReportRepository $operatorRepo
    ) {
        $this->dthRepo = $dthRepo;
        $this->rekapRepo = $rekapRepo;
        $this->operatorRepo = $operatorRepo;
    }

    // ==========================================
    // DTH REPORT (Distribusi Tenaga Harian)
    // ==========================================

    /**
     * Get DTH report data for specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getDthReport(string $companycode, string $date): array
    {
        try {
            $companyInfo = $this->dthRepo->getCompanyInfo($companycode);
            $rkhNumbers = $this->dthRepo->getRkhNumbersByDate($companycode, $date);
            $rkhApproval = $this->dthRepo->getRkhApprovalStats($companycode, $date);
            
            $dthData = [
                'harian' => $this->dthRepo->getHarianData($companycode, $date),
                'borongan' => $this->dthRepo->getBoronganData($companycode, $date),
                'alat' => $this->dthRepo->getAlatData($companycode, $date)
            ];

            // Calculate totals
            $totals = $this->calculateDthTotals($dthData);

            return [
                'success' => true,
                'company_info' => $companyInfo,
                'harian' => $dthData['harian']->toArray(),
                'borongan' => $dthData['borongan']->toArray(),
                'alat' => $dthData['alat']->toArray(),
                'rkh_numbers' => $rkhNumbers,
                'rkh_approval' => $rkhApproval,
                'totals' => $totals,
                'date' => $date,
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'debug' => [
                    'harian_count' => $dthData['harian']->count(),
                    'borongan_count' => $dthData['borongan']->count(),
                    'alat_count' => $dthData['alat']->count(),
                    'rkh_count' => count($rkhNumbers)
                ]
            ];
            
        } catch (\Exception $e) {
            \Log::error("DTH Report Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengambil data DTH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate DTH totals
     * 
     * @param array $dthData
     * @return array
     */
    private function calculateDthTotals(array $dthData): array
    {
        $harianTotal = [
            'total_workers' => $dthData['harian']->sum('jumlahtenagakerja'),
            'total_laki' => $dthData['harian']->sum('jumlahlaki'),
            'total_perempuan' => $dthData['harian']->sum('jumlahperempuan'),
            'total_area' => $dthData['harian']->sum('luasarea'),
        ];

        $boronganTotal = [
            'total_workers' => $dthData['borongan']->sum('jumlahtenagakerja'),
            'total_laki' => $dthData['borongan']->sum('jumlahlaki'),
            'total_perempuan' => $dthData['borongan']->sum('jumlahperempuan'),
            'total_area' => $dthData['borongan']->sum('luasarea'),
        ];

        $alatTotal = [
            'total_vehicles' => $dthData['alat']->count(),
            'total_operators' => $dthData['alat']->pluck('operatorid')->unique()->count(),
            'total_helpers' => $dthData['alat']->whereNotNull('helperid')->count(),
            'total_area' => $dthData['alat']->sum('luasarea'),
        ];

        return [
            'harian' => $harianTotal,
            'borongan' => $boronganTotal,
            'alat' => $alatTotal,
            'grand_total_workers' => $harianTotal['total_workers'] + $boronganTotal['total_workers'],
            'grand_total_area' => $harianTotal['total_area'] + $boronganTotal['total_area'] + $alatTotal['total_area']
        ];
    }

    // ==========================================
    // REKAP LKH REPORT
    // ==========================================

    /**
     * Get LKH Rekap report data for specific date
     * Groups activities by activity group (Pengolahan, Perawatan, Panen, etc)
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getRekapLkhReport(string $companycode, string $date): array
    {
        try {
            $companyInfo = $this->rekapRepo->getCompanyInfo($companycode);
            $lkhNumbers = $this->rekapRepo->getLkhNumbersByDate($companycode, $date);
            
            // Get all LKH data in one query
            $allLkhData = $this->rekapRepo->getAllLkhDataForDate($companycode, $date);
            
            // Group by activity group
            $grouped = $this->groupLkhDataByActivityGroup($allLkhData);
            
            // Get summary statistics
            $summary = $this->rekapRepo->getRekapSummary($companycode, $date);

            return [
                'success' => true,
                'company_info' => $companyInfo,
                'pengolahan' => $grouped['pengolahan'],
                'perawatan' => $grouped['perawatan'],
                'panen' => $grouped['panen'],
                'pias' => $grouped['pias'],
                'lainlain' => $grouped['lainlain'],
                'lkh_numbers' => $lkhNumbers,
                'summary' => $summary,
                'date' => $date,
                'generated_at' => now()->format('d/m/Y H:i:s')
            ];
            
        } catch (\Exception $e) {
            \Log::error("LKH Rekap Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal mengambil data LKH Rekap: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Group LKH data by activity group
     * 
     * @param Collection $allLkhData
     * @return array
     */
    private function groupLkhDataByActivityGroup(Collection $allLkhData): array
    {
        $grouped = [
            'pengolahan' => [],
            'perawatan' => ['pc' => [], 'rc' => []],
            'panen' => [],
            'pias' => [],
            'lainlain' => []
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

            switch ($activityGroup) {
                case 'I':
                case 'II':
                    // Pengolahan
                    if (!isset($grouped['pengolahan'][$activityCode])) {
                        $grouped['pengolahan'][$activityCode] = [];
                    }
                    $grouped['pengolahan'][$activityCode][] = $item;
                    break;

                case 'III':
                    // Perawatan - split by PC/RC
                    $type = (strpos($activityCode, '3.2.') === 0) ? 'rc' : 'pc';
                    if (!isset($grouped['perawatan'][$type][$activityCode])) {
                        $grouped['perawatan'][$type][$activityCode] = [];
                    }
                    $grouped['perawatan'][$type][$activityCode][] = $item;
                    break;

                case 'IV':
                    // Panen
                    if (!isset($grouped['panen'][$activityCode])) {
                        $grouped['panen'][$activityCode] = [];
                    }
                    $grouped['panen'][$activityCode][] = $item;
                    break;

                case 'V':
                    // Pias/Hama
                    if (!isset($grouped['pias'][$activityCode])) {
                        $grouped['pias'][$activityCode] = [];
                    }
                    $grouped['pias'][$activityCode][] = $item;
                    break;

                case 'VI':
                case 'VII':
                case 'VIII':
                    // Lain-lain
                    if (!isset($grouped['lainlain'][$activityCode])) {
                        $grouped['lainlain'][$activityCode] = [];
                    }
                    $grouped['lainlain'][$activityCode][] = $item;
                    break;
            }
        }

        return $grouped;
    }

    // ==========================================
    // OPERATOR REPORT
    // ==========================================

    /**
     * Get list of operators who worked on specific date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getOperatorsForDate(string $companycode, string $date): array
    {
        try {
            $operators = $this->operatorRepo->getOperatorsForDate($companycode, $date);

            return [
                'success' => true,
                'operators' => $operators->toArray(),
                'date' => $date,
                'total_operators' => $operators->count()
            ];

        } catch (\Exception $e) {
            \Log::error("Error getting operators: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal memuat data operator: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get operator daily report
     * 
     * @param string $companycode
     * @param string $operatorId
     * @param string $date
     * @return array
     */
    public function getOperatorReport(string $companycode, string $operatorId, string $date): array
    {
        try {
            $companyInfo = $this->operatorRepo->getCompanyInfo($companycode);
            $operatorInfo = $this->operatorRepo->getOperatorInfo($companycode, $operatorId);

            if (!$operatorInfo) {
                return [
                    'success' => false,
                    'message' => 'Data operator tidak ditemukan'
                ];
            }

            // Get activities with formatted data
            $activities = $this->operatorRepo->getOperatorActivities($companycode, $operatorId, $date);
            $formattedActivities = $this->formatOperatorActivities($activities);

            // Get summaries
            $activitySummary = $this->operatorRepo->getOperatorActivitySummary($companycode, $operatorId, $date);
            $workTimeSummary = $this->operatorRepo->getOperatorWorkTimeSummary($companycode, $operatorId, $date);
            $fuelEfficiency = $this->operatorRepo->getOperatorFuelEfficiency($companycode, $operatorId, $date);

            // Calculate total duration in readable format
            $totalDurationMinutes = $this->calculateTotalDurationMinutes($activities);
            $totalDurationFormatted = $this->formatMinutesToHours($totalDurationMinutes);

            return [
                'success' => true,
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y'),
                'company_info' => $companyInfo,
                'operator_info' => $operatorInfo,
                'activities' => $formattedActivities,
                'summary' => [
                    'total_plots' => $activitySummary->total_plots,
                    'total_luas_rencana' => number_format((float)$activitySummary->total_luas_rencana, 2),
                    'total_luas_hasil' => number_format((float)$activitySummary->total_luas_hasil, 2),
                    'total_solar' => number_format((float)$activitySummary->total_solar, 1),
                    'total_hourmeter' => number_format((float)$activitySummary->total_hourmeter, 1),
                    'fuel_efficiency' => number_format($fuelEfficiency, 2) . ' L/Ha',
                    'work_time' => [
                        'first_start' => $workTimeSummary->first_start ? substr($workTimeSummary->first_start, 0, 5) : '-',
                        'last_end' => $workTimeSummary->last_end ? substr($workTimeSummary->last_end, 0, 5) : '-',
                        'total_duration' => $totalDurationFormatted
                    ]
                ],
                'generated_at' => now()->format('d/m/Y H:i:s')
            ];

        } catch (\Exception $e) {
            \Log::error("Operator report error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal memuat data laporan operator: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get operator monthly performance
     * 
     * @param string $companycode
     * @param string $operatorId
     * @param string $monthYear (format: Y-m)
     * @return array
     */
    public function getOperatorMonthlyPerformance(string $companycode, string $operatorId, string $monthYear): array
    {
        try {
            $operatorInfo = $this->operatorRepo->getOperatorInfo($companycode, $operatorId);

            if (!$operatorInfo) {
                return [
                    'success' => false,
                    'message' => 'Data operator tidak ditemukan'
                ];
            }

            $performance = $this->operatorRepo->getOperatorMonthlyPerformance($companycode, $operatorId, $monthYear);
            
            // Calculate monthly totals
            $monthlyTotals = [
                'total_days_worked' => $performance->count(),
                'total_plots' => $performance->sum('plots_worked'),
                'total_area' => $performance->sum('total_area'),
                'total_fuel' => $performance->sum('total_fuel'),
                'avg_fuel_per_day' => $performance->count() > 0 ? $performance->sum('total_fuel') / $performance->count() : 0,
                'avg_area_per_day' => $performance->count() > 0 ? $performance->sum('total_area') / $performance->count() : 0,
            ];

            return [
                'success' => true,
                'operator_info' => $operatorInfo,
                'month_year' => $monthYear,
                'month_name' => Carbon::createFromFormat('Y-m', $monthYear)->format('F Y'),
                'daily_performance' => $performance->map(function($day) {
                    return [
                        'date' => Carbon::parse($day->work_date)->format('d/m/Y'),
                        'plots_worked' => $day->plots_worked,
                        'total_area' => number_format((float)$day->total_area, 2),
                        'total_fuel' => number_format((float)$day->total_fuel, 1),
                        'total_hours' => $day->total_hours,
                    ];
                })->toArray(),
                'monthly_totals' => [
                    'total_days_worked' => $monthlyTotals['total_days_worked'],
                    'total_plots' => $monthlyTotals['total_plots'],
                    'total_area' => number_format($monthlyTotals['total_area'], 2),
                    'total_fuel' => number_format($monthlyTotals['total_fuel'], 1),
                    'avg_fuel_per_day' => number_format($monthlyTotals['avg_fuel_per_day'], 1),
                    'avg_area_per_day' => number_format($monthlyTotals['avg_area_per_day'], 2),
                ]
            ];

        } catch (\Exception $e) {
            \Log::error("Operator monthly performance error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal memuat data performance bulanan: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get operators comparison for date
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getOperatorsComparison(string $companycode, string $date): array
    {
        try {
            $comparison = $this->operatorRepo->getOperatorsComparison($companycode, $date);

            return [
                'success' => true,
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d/m/Y'),
                'operators' => $comparison->map(function($operator) {
                    return [
                        'operator_id' => $operator->tenagakerjaid,
                        'operator_name' => $operator->operator_name,
                        'total_plots' => $operator->total_plots,
                        'total_area' => number_format((float)$operator->total_area, 2),
                        'total_fuel' => number_format((float)$operator->total_fuel, 1),
                        'fuel_per_ha' => number_format((float)$operator->fuel_per_ha, 2),
                        'efficiency_rating' => $this->calculateEfficiencyRating($operator->fuel_per_ha)
                    ];
                })->toArray(),
                'total_operators' => $comparison->count()
            ];

        } catch (\Exception $e) {
            \Log::error("Operators comparison error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal memuat data perbandingan operator: ' . $e->getMessage()
            ];
        }
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Format operator activities
     */
    private function formatOperatorActivities(Collection $activities): array
    {
        return $activities->map(function($activity) {
            return [
                'jam_mulai' => substr($activity->jammulai, 0, 5),
                'jam_selesai' => substr($activity->jamselesai, 0, 5),
                'durasi_kerja' => $activity->durasi_kerja ? substr($activity->durasi_kerja, 0, 5) : '00:00',
                'blok' => $activity->blok,
                'plot' => $activity->plot,
                'plot_display' => $activity->blok . '-' . $activity->plot,
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
     * Calculate total duration in minutes
     */
    private function calculateTotalDurationMinutes(Collection $activities): int
    {
        $totalMinutes = 0;
        
        foreach ($activities as $activity) {
            if ($activity->durasi_kerja && $activity->durasi_kerja !== '00:00:00') {
                list($hours, $minutes) = explode(':', $activity->durasi_kerja);
                $totalMinutes += ($hours * 60) + $minutes;
            }
        }
        
        return $totalMinutes;
    }

    /**
     * Format minutes to hours (HH:MM)
     */
    private function formatMinutesToHours(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Calculate efficiency rating based on fuel consumption
     * 
     * @param float $fuelPerHa
     * @return string
     */
    private function calculateEfficiencyRating(float $fuelPerHa): string
    {
        // Benchmark values (can be adjusted)
        if ($fuelPerHa < 10) {
            return 'Excellent';
        } elseif ($fuelPerHa < 15) {
            return 'Good';
        } elseif ($fuelPerHa < 20) {
            return 'Average';
        } else {
            return 'Poor';
        }
    }
}