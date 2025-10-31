<?php

namespace App\Services;

use App\Models\Rkhhdr;
use App\Models\Rkhlst;
use App\Models\Lkhhdr;
use App\Models\LkhDetailWorker;
use App\Models\LkhDetailPlot;
use App\Services\WageCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * CLEAN LkhGeneratorService - Unified Structure
 * 
 * Generate LKH from fully approved RKH using unified structure:
 * - ALL activities use: lkhdetailplot + lkhdetailworker + lkhdetailmaterial
 * - Panen activities: jenistenagakerja = 5 (kontraktor), with batchno & kodestatus in plot details
 * - Normal activities: jenistenagakerja = 1 (harian) or 2 (borongan)
 * 
 * NO MORE separate panen logic - everything flows the same way!
 */
class LkhGeneratorService
{
    protected $wageCalculationService;
    
    // Activity type constants
    const PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2'];
    const JENIS_HARIAN = 1;
    const JENIS_BORONGAN = 2;
    const JENIS_OPERATOR = 3;
    const JENIS_HELPER = 4;
    const JENIS_KONTRAKTOR = 5; // For panen

    public function __construct(WageCalculationService $wageCalculationService = null)
    {
        $this->wageCalculationService = $wageCalculationService ?: new WageCalculationService();
    }

    /**
     * Generate LKH from fully approved RKH
     * UNIFIED: Single flow for all activity types
     * 
     * @param string $rkhno
     * @return array
     */
    public function generateLkhFromRkh($rkhno)
    {
        try {
            DB::beginTransaction();

            // 1. Validate RKH exists and fully approved
            $rkh = Rkhhdr::where('rkhno', $rkhno)->first();
            if (!$rkh) {
                throw new \Exception("RKH {$rkhno} not found");
            }

            if (!$this->isRkhFullyApproved($rkh)) {
                throw new \Exception("RKH {$rkhno} belum fully approved");
            }

            // 2. Check if LKH already generated
            $existingLkh = Lkhhdr::where('rkhno', $rkhno)->exists();
            if ($existingLkh) {
                throw new \Exception("LKH untuk RKH {$rkhno} sudah pernah di-generate");
            }

            // 3. Get RKH activities
            $rkhActivities = Rkhlst::where('rkhno', $rkhno)
                ->where('companycode', $rkh->companycode)
                ->get();

            if ($rkhActivities->isEmpty()) {
                throw new \Exception("Tidak ada aktivitas ditemukan untuk RKH {$rkhno}");
            }

            // 4. Group activities by activitycode + jenistenagakerja
            // Panen activities will use jenistenagakerja from detection
            $groupedActivities = $this->groupActivitiesForLkh($rkhActivities);

            $generatedLkh = [];
            $lkhIndex = 1;

            // 5. Generate LKH for each group
            foreach ($groupedActivities as $groupKey => $groupActivities) {
                $firstActivity = $groupActivities->first();
                
                [$activitycode, $jenistenagakerja] = explode('|', $groupKey);
                
                $lkhno = $this->generateLkhNumber($rkh->rkhno, $lkhIndex);
                
                // Generate LKH Header
                $lkhHeaderResult = $this->createLkhHeader(
                    $rkh, 
                    $lkhno, 
                    $activitycode, 
                    $jenistenagakerja, 
                    $groupActivities
                );
                
                // Generate LKH Detail Plots (with batch info for panen)
                $plotResult = $this->createLkhDetailPlots(
                    $lkhno, 
                    $groupActivities, 
                    $rkh->companycode,
                    $activitycode
                );
                
                $isPanen = in_array($activitycode, self::PANEN_ACTIVITIES);
                
                $generatedLkh[] = [
                    'lkhno' => $lkhno,
                    'activitycode' => $activitycode,
                    'type' => $isPanen ? 'PANEN' : 'NORMAL',
                    'plots' => $lkhHeaderResult['plots_summary'],
                    'plots_count' => count($plotResult),
                    'jenistenagakerja' => $jenistenagakerja,
                    'jenis_label' => $this->getJenisLabel($jenistenagakerja),
                    'total_luas' => $lkhHeaderResult['total_luas'],
                    'planned_workers' => $lkhHeaderResult['planned_workers'],
                    'status' => 'DRAFT'
                ];

                $lkhIndex++;
            }

            DB::commit();

            Log::info("LKH auto-generated for RKH {$rkhno}", [
                'generated_lkh' => $generatedLkh,
                'total_lkh' => count($generatedLkh)
            ]);

            return [
                'success' => true,
                'message' => 'LKH berhasil di-generate otomatis',
                'generated_lkh' => $generatedLkh,
                'total_lkh' => count($generatedLkh)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to generate LKH for RKH {$rkhno}: " . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'generated_lkh' => [],
                'total_lkh' => 0
            ];
        }
    }

    /**
     * Group activities for LKH generation
     * Panen activities use jenistenagakerja = 5 (kontraktor)
     * Normal activities use their original jenistenagakerja
     * 
     * @param \Illuminate\Support\Collection $activities
     * @return \Illuminate\Support\Collection
     */
    private function groupActivitiesForLkh($activities)
    {
        return $activities->groupBy(function($item) {
            // Detect if panen activity
            $isPanen = in_array($item->activitycode, self::PANEN_ACTIVITIES);
            
            // For panen, always use jenistenagakerja = 5 (kontraktor)
            // For normal, use original jenistenagakerja
            $jenistenagakerja = $isPanen ? self::JENIS_KONTRAKTOR : $item->jenistenagakerja;
            
            return $item->activitycode . '|' . $jenistenagakerja;
        });
    }

    /**
     * Create LKH Header (unified for all activity types)
     * 
     * @param Rkhhdr $rkh
     * @param string $lkhno
     * @param string $activitycode
     * @param int $jenistenagakerja
     * @param \Illuminate\Support\Collection $activities
     * @return array
     */
    private function createLkhHeader($rkh, $lkhno, $activitycode, $jenistenagakerja, $activities)
    {
        $approvalData = $this->getApprovalRequirements($rkh->companycode, $activitycode);
        
        $totalLuas = $activities->sum(function($activity) {
            return (float) $activity->luasarea;
        });
        
        $totalWorkersPlanned = $activities->sum('jumlahtenagakerja');
        $plotList = $activities->pluck('plot')->unique()->join(', ');
        
        $lkhHeaderData = array_merge([
            'lkhno' => $lkhno,
            'rkhno' => $rkh->rkhno,
            'companycode' => $rkh->companycode,
            'activitycode' => $activitycode,
            'mandorid' => $rkh->mandorid,
            'lkhdate' => $rkh->rkhdate,
            'jenistenagakerja' => $jenistenagakerja,
            'totalworkers' => 0,
            'totalluasactual' => 0.00,
            'totalhasil' => 0.00,
            'totalsisa' => $totalLuas,
            'totalupahall' => 0.00,
            'status' => 'DRAFT', 
            'issubmit' => 0,
            'keterangan' => null,
            'inputby' => auth()->user()->userid ?? 'SYSTEM',
            'createdat' => now(),
        ], $approvalData);

        $lkhHeader = Lkhhdr::create($lkhHeaderData);

        return [
            'success' => true,
            'lkh_header' => $lkhHeader,
            'total_luas' => $totalLuas,
            'planned_workers' => $totalWorkersPlanned,
            'plots_summary' => $plotList
        ];
    }

    /**
     * Create LKH Detail Plot records
     * UNIFIED: Includes batch info for panen activities
     * 
     * @param string $lkhno
     * @param \Illuminate\Support\Collection $activities
     * @param string $companycode
     * @param string $activitycode
     * @return array
     */
    private function createLkhDetailPlots($lkhno, $activities, $companycode, $activitycode)
    {
        $plotDetails = [];
        $isPanenActivity = in_array($activitycode, self::PANEN_ACTIVITIES);
        
        foreach ($activities as $activity) {
            $luasArea = (float) $activity->luasarea;
            
            $plotDetail = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'blok' => $activity->blok,
                'plot' => $activity->plot,
                'luasrkh' => $luasArea,
                'luashasil' => 0.00,
                'luassisa' => $luasArea,
                'createdat' => now()
            ];
            
            if ($isPanenActivity) {
                $plotDetail['batchno'] = $activity->batchno ?? null;
                $plotDetail['kodestatus'] = $activity->lifecyclestatus ?? null; // GANTI ke lifecyclestatus
            }
            
            LkhDetailPlot::create($plotDetail);
            $plotDetails[] = $plotDetail;
        }
        
        return $plotDetails;
    }

    /**
     * Generate LKH number based on RKH number and index
     * Format: LKH{DDMM}{XX}{YY}-{INDEX}
     * 
     * @param string $rkhno
     * @param int $index
     * @return string
     */
    private function generateLkhNumber($rkhno, $index)
    {
        // RKH format: RKH04080125
        // LKH format: LKH04080125-1
        $rkhPart = substr($rkhno, 3); // Remove "RKH" prefix
        return "LKH{$rkhPart}-{$index}";
    }

    /**
     * Get jenis tenaga kerja label
     * 
     * @param int $jenistenagakerja
     * @return string
     */
    private function getJenisLabel($jenistenagakerja)
    {
        switch ($jenistenagakerja) {
            case self::JENIS_HARIAN:
                return 'Harian';
            case self::JENIS_BORONGAN:
                return 'Borongan';
            case self::JENIS_OPERATOR:
                return 'Operator';
            case self::JENIS_HELPER:
                return 'Helper';
            case self::JENIS_KONTRAKTOR:
                return 'Kontraktor (Panen)';
            default:
                return 'Unknown';
        }
    }

    /**
     * Check if RKH is fully approved
     * 
     * @param Rkhhdr $rkh
     * @return bool
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

    /**
     * Get approval requirements for activity
     * 
     * @param string $companycode
     * @param string $activitycode
     * @return array
     */
    private function getApprovalRequirements($companycode, $activitycode)
    {
        $activity = DB::table('activity')->where('activitycode', $activitycode)->first();
        
        if (!$activity || !$activity->activitygroup) {
            return [
                'jumlahapproval' => 0,
                'approval1idjabatan' => null,
                'approval2idjabatan' => null,
                'approval3idjabatan' => null,
            ];
        }

        $approvalSetting = DB::table('approval')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activity->activitygroup)
            ->first();

        if (!$approvalSetting) {
            return [
                'jumlahapproval' => 0,
                'approval1idjabatan' => null,
                'approval2idjabatan' => null,
                'approval3idjabatan' => null,
            ];
        }

        return [
            'jumlahapproval' => $approvalSetting->jumlahapproval ?? 0,
            'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
            'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
            'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
        ];
    }

    /**
     * Get LKH summary for specific RKH
     * UNIFIED: Works for all activity types
     * 
     * @param string $rkhno
     * @return array
     */
    public function getLkhSummaryForRkh($rkhno)
    {
        $lkhList = Lkhhdr::where('rkhno', $rkhno)
            ->with(['activity'])
            ->get();

        $summary = [
            'total_lkh' => $lkhList->count(),
            'by_status' => $lkhList->groupBy('status')->map(function ($group) {
                return $group->count();
            })->toArray(),
            'by_jenis' => $lkhList->groupBy('jenistenagakerja')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'label' => $this->getJenisLabel($group->first()->jenistenagakerja)
                ];
            })->toArray(),
            'details' => $lkhList->map(function ($lkh) {
                // Get plots (with batch info for panen)
                $plots = LkhDetailPlot::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->get()
                    ->map(function($item) {
                        $plotInfo = $item->blok . '-' . $item->plot . ' (' . $item->luasrkh . ' ha)';
                        
                        // Add batch info if exists (for panen)
                        if ($item->batchno) {
                            $plotInfo .= ' [' . $item->batchno . '-' . $item->kodestatus . ']';
                        }
                        
                        return $plotInfo;
                    })
                    ->join(', ');

                // Get workers count
                $assignedWorkers = LkhDetailWorker::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->count();

                // Get material count
                $materialCount = DB::table('lkhdetailmaterial')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->count();

                return [
                    'lkhno' => $lkh->lkhno,
                    'activitycode' => $lkh->activitycode,
                    'activityname' => $lkh->activity->activityname ?? 'Unknown',
                    'jenis_label' => $this->getJenisLabel($lkh->jenistenagakerja),
                    'plots' => $plots ?: 'No plots assigned',
                    'status' => $lkh->status,
                    'workers_assigned' => $assignedWorkers,
                    'material_count' => $materialCount,
                    'totalhasil' => $lkh->totalhasil,
                    'totalsisa' => $lkh->totalsisa,
                    'totalupah' => $lkh->totalupahall
                ];
            })->toArray()
        ];

        return $summary;
    }

    /**
     * Calculate and update LKH wages
     * Works for harian, borongan, and kontraktor
     * 
     * @param string $lkhno
     * @return array
     */
    public function calculateLkhWages($lkhno)
    {
        try {
            DB::beginTransaction();

            $lkh = Lkhhdr::where('lkhno', $lkhno)->first();
            if (!$lkh) {
                throw new \Exception("LKH {$lkhno} not found");
            }

            $workers = LkhDetailWorker::where('companycode', $lkh->companycode)
                ->where('lkhno', $lkhno)
                ->get();

            if ($workers->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No workers assigned to this LKH'
                ];
            }

            $plots = LkhDetailPlot::where('companycode', $lkh->companycode)
                ->where('lkhno', $lkhno)
                ->get();
            
            $plotsData = $plots->map(function($plot) {
                return [
                    'blok' => $plot->blok,
                    'plot' => $plot->plot,
                    'luashasil' => $plot->luashasil,
                    'luasrkh' => $plot->luasrkh
                ];
            })->toArray();

            $totalWages = 0;
            $calculatedWorkers = 0;

            foreach ($workers as $worker) {
                $workerData = [
                    'tenagakerjaid' => $worker->tenagakerjaid,
                    'totaljamkerja' => $worker->totaljamkerja,
                    'overtimehours' => $worker->overtimehours,
                    'premi' => $worker->premi
                ];

                $wageResult = $this->wageCalculationService->calculateWorkerWage(
                    $lkh->companycode,
                    $lkh->activitycode,
                    $lkh->jenistenagakerja,
                    $lkh->lkhdate,
                    $workerData,
                    $plotsData
                );

                if ($wageResult['success']) {
                    $worker->update([
                        'upahharian' => $wageResult['upahharian'],
                        'upahperjam' => $wageResult['upahperjam'],
                        'upahlembur' => $wageResult['upahlembur'],
                        'upahborongan' => $wageResult['upahborongan'],
                        'totalupah' => $wageResult['totalupah'],
                        'keterangan' => $wageResult['notes'] ?? $worker->keterangan,
                        'updatedat' => now()
                    ]);

                    $totalWages += $wageResult['totalupah'];
                    $calculatedWorkers++;
                }
            }

            $lkh->update([
                'totalworkers' => $workers->count(),
                'totalupahall' => $totalWages,
                'updatedat' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Wages calculated for {$calculatedWorkers} workers",
                'total_wages' => $totalWages,
                'workers_calculated' => $calculatedWorkers,
                'total_workers' => $workers->count(),
                'lkhno' => $lkhno
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Failed to calculate LKH wages for {$lkhno}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error calculating wages: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Bulk generate LKH for multiple RKH
     * 
     * @param array $rkhList
     * @return array
     */
    public function bulkGenerateLkh($rkhList)
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($rkhList as $rkhno) {
            $result = $this->generateLkhFromRkh($rkhno);
            $results[$rkhno] = $result;
            
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        return [
            'success' => $successCount > 0,
            'message' => "Bulk generate completed: {$successCount} success, {$failCount} failed",
            'results' => $results,
            'summary' => [
                'total_processed' => count($rkhList),
                'success_count' => $successCount,
                'fail_count' => $failCount
            ]
        ];
    }

    /**
     * Regenerate LKH (for special cases)
     * 
     * @param string $rkhno
     * @param bool $forceRegenerate
     * @return array
     */
    public function regenerateLkh($rkhno, $forceRegenerate = false)
    {
        if (!$forceRegenerate) {
            throw new \Exception("Regenerate LKH hanya bisa dilakukan dengan force flag");
        }

        try {
            DB::beginTransaction();

            // Delete existing LKH and all details
            $existingLkh = Lkhhdr::where('rkhno', $rkhno)->get();
            foreach ($existingLkh as $lkh) {
                // Delete workers
                LkhDetailWorker::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                // Delete plots
                LkhDetailPlot::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                // Delete materials
                DB::table('lkhdetailmaterial')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                // Delete header
                $lkh->delete();
            }

            DB::commit();

            // Generate new LKH
            return $this->generateLkhFromRkh($rkhno);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}