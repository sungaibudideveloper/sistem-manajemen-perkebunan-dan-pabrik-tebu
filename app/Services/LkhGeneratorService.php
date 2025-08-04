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
 * FIXED LkhGeneratorService
 * 
 * Generate LKH from fully approved RKH using new split table design:
 * - lkhdetailplot (plot assignments & areas) - Generated immediately
 * - lkhdetailworker (worker assignments & wages) - Assigned by mandor via handheld
 * 
 * FIXED: Proper area calculation and data type handling
 */
class LkhGeneratorService
{
    protected $wageCalculationService;

    public function __construct(WageCalculationService $wageCalculationService = null)
    {
        $this->wageCalculationService = $wageCalculationService ?: new WageCalculationService();
    }

    /**
     * Generate LKH from fully approved RKH
     * NEW LOGIC: 1 LKH = 1 Mandor = 1 Kegiatan = Many Plot
     * UPDATED: Using split tables - Only plots generated, workers assigned by mandor
     * 
     * @param string $rkhno
     * @return array
     */
    public function generateLkhFromRkh($rkhno)
    {
        try {
            DB::beginTransaction();

            // 1. Validasi RKH exist dan sudah fully approved
            $rkh = Rkhhdr::where('rkhno', $rkhno)->first();
            if (!$rkh) {
                throw new \Exception("RKH {$rkhno} not found");
            }

            if (!$this->isRkhFullyApproved($rkh)) {
                throw new \Exception("RKH {$rkhno} belum fully approved");
            }

            // 2. Cek apakah LKH sudah pernah di-generate
            $existingLkh = Lkhhdr::where('rkhno', $rkhno)->exists();
            if ($existingLkh) {
                throw new \Exception("LKH untuk RKH {$rkhno} sudah pernah di-generate");
            }

            // 3. Ambil detail aktivitas dari RKH dan group by activitycode + jenistenagakerja
            $rkhActivities = Rkhlst::where('rkhno', $rkhno)
                ->where('companycode', $rkh->companycode)
                ->get();

            if ($rkhActivities->isEmpty()) {
                throw new \Exception("Tidak ada aktivitas ditemukan untuk RKH {$rkhno}");
            }

            // 4. Group aktivitas berdasarkan activitycode + jenistenagakerja
            $groupedActivities = $rkhActivities->groupBy(function($item) {
                return $item->activitycode . '|' . $item->jenistenagakerja;
            });

            $generatedLkh = [];
            $lkhIndex = 1;

            // 5. Generate LKH untuk setiap group (1 LKH per kegiatan + jenis tenaga kerja)
            foreach ($groupedActivities as $groupKey => $activities) {
                $firstActivity = $activities->first();
                
                // Parse group key
                [$activitycode, $jenistenagakerja] = explode('|', $groupKey);
                
                $lkhno = $this->generateLkhNumber($rkhno, $lkhIndex);
                
                // Generate LKH Header
                $lkhHeaderResult = $this->createLkhHeader($rkh, $lkhno, $activitycode, $jenistenagakerja, $activities);
                
                // Generate LKH Detail Plots (Workers akan diassign oleh mandor)
                $plotResult = $this->createLkhDetailPlots($lkhno, $activities, $rkh->companycode);
                
                $generatedLkh[] = [
                    'lkhno' => $lkhno,
                    'activitycode' => $activitycode,
                    'plots' => $lkhHeaderResult['plots_summary'],
                    'plots_count' => count($plotResult),
                    'jenistenagakerja' => $jenistenagakerja,
                    'total_luas' => $lkhHeaderResult['total_luas'],
                    'planned_workers' => $lkhHeaderResult['planned_workers'],
                    'status' => 'EMPTY'
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
                'message' => 'LKH berhasil di-generate otomatis (plots only, workers assigned by mandor)',
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
     * Create LKH Header
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
        // Get approval requirements untuk activity ini
        $approvalData = $this->getApprovalRequirements($rkh->companycode, $activitycode);
        
        // Calculate totals dari semua plot dalam group ini - FIXED: ensure proper numeric conversion
        $totalLuas = $activities->sum(function($activity) {
            return (float) $activity->luasarea;
        });
        $totalWorkersPlanned = $activities->sum('jumlahtenagakerja');
        $plotList = $activities->pluck('plot')->unique()->join(', ');
        $blokList = $activities->pluck('blok')->unique()->join(', ');
        
        // Buat LKH Header dengan approval requirements
        $lkhHeaderData = array_merge([
            'lkhno' => $lkhno,
            'rkhno' => $rkh->rkhno,
            'companycode' => $rkh->companycode,
            'activitycode' => $activitycode,
            'mandorid' => $rkh->mandorid,
            'lkhdate' => $rkh->rkhdate,
            'jenistenagakerja' => $jenistenagakerja,
            'totalworkers' => 0, // Will be updated when mandor assigns workers
            'totalluasactual' => 0.00, // Will be updated when work is completed
            'totalhasil' => 0.00, // Will be updated when work is completed
            'totalsisa' => $totalLuas, // Sisa = total luas area awal dari semua plot
            'totalupahall' => 0.00, // Will be calculated when wages are computed
            'jammulaikerja' => null,
            'jamselesaikerja' => null,
            'totalovertimehours' => 0.00,
            'status' => 'EMPTY',
            'issubmit' => 0,
            'keterangan' => "Auto-generated from RKH {$rkh->rkhno} - Plots: {$plotList} - Planned workers: {$totalWorkersPlanned}",
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
     * FIXED: Proper area conversion and data type handling
     * 
     * @param string $lkhno
     * @param \Illuminate\Support\Collection $activities
     * @param string $companycode
     * @return array
     */
    private function createLkhDetailPlots($lkhno, $activities, $companycode)
    {
        $plotDetails = [];
        
        foreach ($activities as $activity) {
            // FIXED: Ensure proper numeric conversion and validation
            $luasArea = (float) $activity->luasarea;
            
            $plotDetail = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'blok' => $activity->blok,
                'plot' => $activity->plot,
                'luasrkh' => $luasArea,  // FIXED: use converted float value
                'luashasil' => 0.00,
                'luassisa' => $luasArea, // FIXED: initialize with luasarea, not null
                'createdat' => now()
            ];
            
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
     * Check if RKH is fully approved
     * 
     * @param Rkhhdr $rkh
     * @return bool
     */
    private function isRkhFullyApproved($rkh)
    {
        // Jika tidak ada requirement approval, anggap sudah approved
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

        // Check berdasarkan jumlah approval yang diperlukan
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
     * Get approval requirements untuk activity
     */
    private function getApprovalRequirements($companycode, $activitycode)
    {
        // Get activity group dari activity code
        $activity = DB::table('activity')->where('activitycode', $activitycode)->first();
        
        if (!$activity || !$activity->activitygroup) {
            return [
                'jumlahapproval' => 0,
                'approval1idjabatan' => null,
                'approval2idjabatan' => null,
                'approval3idjabatan' => null,
            ];
        }

        // Get approval settings berdasarkan activity group
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
     * UPDATED: Use new column names (luasrkh instead of luasplot)
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
            'by_jenistenaga' => $lkhList->groupBy('jenistenagakerja')->map(function ($group) {
                return $group->count();
            })->toArray(),
            'details' => $lkhList->map(function ($lkh) {
                // Get plots for this LKH from lkhdetailplot
                $plots = LkhDetailPlot::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->select('blok', 'plot', 'luasrkh')
                    ->get()
                    ->map(function($item) {
                        return $item->blok . '-' . $item->plot . ' (' . $item->luasrkh . ' ha)';
                    })
                    ->join(', ');

                // Get worker assignments count (akan diisi oleh mandor)
                $assignedWorkers = LkhDetailWorker::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->count();

                return [
                    'lkhno' => $lkh->lkhno,
                    'activitycode' => $lkh->activitycode,
                    'activityname' => $lkh->activity->activityname ?? 'Unknown',
                    'plots' => $plots ?: 'No plots assigned',
                    'status' => $lkh->status,
                    'jenistenagakerja' => $lkh->jenistenagakerja,
                    'workers_assigned' => $assignedWorkers,
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
     * Using WageCalculationService for complex calculations
     * Called when mandor completes work assignment via handheld
     * UPDATED: Use new column names (luashasil, luasrkh)
     * 
     * @param string $lkhno
     * @return array
     */
    public function calculateLkhWages($lkhno)
    {
        try {
            DB::beginTransaction();

            // Get LKH data
            $lkh = Lkhhdr::where('lkhno', $lkhno)->first();
            if (!$lkh) {
                throw new \Exception("LKH {$lkhno} not found");
            }

            // Get assigned workers (assigned by mandor via handheld)
            $workers = LkhDetailWorker::where('companycode', $lkh->companycode)
                ->where('lkhno', $lkhno)
                ->get();

            if ($workers->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No workers assigned to this LKH by mandor'
                ];
            }

            // Get plot data for borongan calculations - UPDATED: filter by company
            $plots = LkhDetailPlot::where('companycode', $lkh->companycode)
                ->where('lkhno', $lkhno)
                ->get();
            $plotsData = $plots->map(function($plot) {
                return [
                    'blok' => $plot->blok,
                    'plot' => $plot->plot,
                    'luashasil' => $plot->luashasil,    // CHANGED: was luasactual
                    'luasrkh' => $plot->luasrkh         // CHANGED: was luastargeted
                ];
            })->toArray();

            $totalWages = 0;
            $calculatedWorkers = 0;

            // Calculate wages for each worker
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
                    // Update worker record with calculated wages
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

            // Update LKH header with totals
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
     * Regenerate LKH (untuk kasus khusus)
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

            // Hapus LKH yang sudah ada beserta detail tables
            $existingLkh = Lkhhdr::where('rkhno', $rkhno)->get();
            foreach ($existingLkh as $lkh) {
                // Hapus detail workers (if any assigned by mandor)
                LkhDetailWorker::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                // Hapus detail plots
                LkhDetailPlot::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                // Hapus header
                $lkh->delete();
            }

            DB::commit();

            // Generate ulang
            return $this->generateLkhFromRkh($rkhno);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}