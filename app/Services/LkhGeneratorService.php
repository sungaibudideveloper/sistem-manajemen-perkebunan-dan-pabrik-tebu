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
 * UPDATED LkhGeneratorService - With Kendaraan Support
 * 
 * Generate LKH from fully approved RKH using unified structure:
 * - ALL activities use: lkhdetailplot + lkhdetailworker + lkhdetailmaterial + lkhdetailkendaraan
 * - Kendaraan assignments dari rkhlstkendaraan → lkhdetailkendaraan
 */
class LkhGeneratorService
{
    protected $wageCalculationService;
    
    // Activity type constants
    const PANEN_ACTIVITIES = ['4.3.3', '4.4.3', '4.5.2'];
    const BSM_ACTIVITY = '4.7';
    const JENIS_HARIAN = 1;
    const JENIS_BORONGAN = 2;
    const JENIS_OPERATOR = 3;
    const JENIS_HELPER = 4;

    public function __construct(WageCalculationService $wageCalculationService = null)
    {
        $this->wageCalculationService = $wageCalculationService ?: new WageCalculationService();
    }

    /**
     * Generate LKH from fully approved RKH
     * UPDATED: Include kendaraan generation
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
                
                // Generate LKH Detail Plots (SKIP untuk BSM activity)
                $plotResult = [];
                if ($activitycode !== self::BSM_ACTIVITY) {
                    $plotResult = $this->createLkhDetailPlots(
                        $lkhno, 
                        $groupActivities, 
                        $rkh->companycode,
                        $activitycode
                    );
                }
                
                // ✅ NEW: Generate LKH Detail Kendaraan
                $kendaraanResult = $this->generateLkhKendaraanRecords(
                    $rkh->rkhno,
                    $lkhno,
                    $activitycode,
                    $rkh->companycode,
                    $groupActivities
                );
                
                // Detect activity type
                $isPanen = in_array($activitycode, self::PANEN_ACTIVITIES);
                $isBsm = ($activitycode === self::BSM_ACTIVITY);
                
                // Base LKH data
                $lkhData = [
                    'lkhno' => $lkhno,
                    'activitycode' => $activitycode,
                    'type' => $isPanen ? 'PANEN' : ($isBsm ? 'BSM' : 'NORMAL'),
                    'plots' => $lkhHeaderResult['plots_summary'],
                    'plots_count' => count($plotResult),
                    'jenistenagakerja' => $jenistenagakerja,
                    'jenis_label' => $this->getJenisLabel($jenistenagakerja),
                    'total_luas' => $lkhHeaderResult['total_luas'],
                    'planned_workers' => $lkhHeaderResult['planned_workers'],
                    'kendaraan_count' => $kendaraanResult['total_vehicles'], // ✅ NEW
                    'status' => 'DRAFT'
                ];
                
                // SPECIAL HANDLING: Generate BSM placeholders if BSM activity
                if ($isBsm) {
                    $bsmResult = $this->createBsmPlaceholders($lkhno, $groupActivities, $rkh->companycode);
                    $lkhData['bsm_plots'] = $bsmResult['total_plots'];
                    $lkhData['bsm_status'] = 'PENDING_INPUT';
                }
                
                $generatedLkh[] = $lkhData;
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
     * 
     * @param \Illuminate\Support\Collection $activities
     * @return \Illuminate\Support\Collection
     */
    private function groupActivitiesForLkh($activities)
    {
        return $activities->groupBy(function($item) {
            return $item->activitycode . '|' . $item->jenistenagakerja;
        });
    }

    /**
     * Create LKH Header (unchanged - no kendaraan info in header)
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
     * Create LKH Detail Plot records (unchanged)
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
            }
            
            LkhDetailPlot::create($plotDetail);
            $plotDetails[] = $plotDetail;
        }
        
        return $plotDetails;
    }

    /**
     * Generate LKH kendaraan records from RKH kendaraan assignments
     * 
     * Flow:
     * 1. Get kendaraan assignments dari rkhlstkendaraan untuk activity ini
     * 2. Get plots untuk activity ini dari rkhlst
     * 3. Generate lkhdetailkendaraan untuk setiap kombinasi kendaraan-plot
     * 
     * @param string $rkhno
     * @param string $lkhno
     * @param string $activitycode
     * @param string $companycode
     * @param \Illuminate\Support\Collection $activities
     * @return array
     */
    private function generateLkhKendaraanRecords($rkhno, $lkhno, $activitycode, $companycode, $activities)
    {
        try {
            // 1. Get kendaraan assignments dari RKH untuk activity ini
            $kendaraanAssignments = DB::table('rkhlstkendaraan')
                ->where('rkhno', $rkhno)
                ->where('activitycode', $activitycode)
                ->where('companycode', $companycode)
                ->orderBy('urutan')
                ->get();
            
            if ($kendaraanAssignments->isEmpty()) {
                Log::info("No kendaraan assignments for activity {$activitycode} in RKH {$rkhno}");
                return [
                    'success' => true,
                    'total_vehicles' => 0,
                    'records' => []
                ];
            }
            
            // 2. Get plots untuk activity ini
            $plots = $activities->pluck('plot')->unique()->values();
            
            if ($plots->isEmpty()) {
                Log::warning("No plots found for activity {$activitycode} in RKH {$rkhno}");
                return [
                    'success' => true,
                    'total_vehicles' => 0,
                    'records' => []
                ];
            }
            
            // 3. Generate lkhdetailkendaraan untuk setiap kombinasi kendaraan-plot
            $records = [];
            
            foreach ($kendaraanAssignments as $assignment) {
                foreach ($plots as $plot) {
                    $record = [
                        'companycode' => $companycode,
                        'lkhno' => $lkhno,
                        'nokendaraan' => $assignment->nokendaraan,
                        'operatorid' => $assignment->operatorid,
                        'helperid' => $assignment->helperid,
                        'jammulai' => null,
                        'jamselesai' => null,
                        'hourmeterstart' => null,
                        'hourmeterend' => null,
                        'solar' => null,
                        'status' => null,
                        'createdat' => now()
                    ];
                    
                    DB::table('lkhdetailkendaraan')->insert($record);
                    $records[] = $record;
                }
            }
            
            Log::info("LKH kendaraan records generated", [
                'lkhno' => $lkhno,
                'activitycode' => $activitycode,
                'total_vehicles' => $kendaraanAssignments->count(),
                'total_plots' => $plots->count(),
                'total_records' => count($records)
            ]);
            
            return [
                'success' => true,
                'total_vehicles' => $kendaraanAssignments->count(),
                'total_plots' => $plots->count(),
                'total_records' => count($records),
                'records' => $records
            ];
            
        } catch (\Exception $e) {
            Log::error("Error generating LKH kendaraan records", [
                'lkhno' => $lkhno,
                'activitycode' => $activitycode,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
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
     * UPDATED: Include kendaraan info
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
                $plots = DB::table('lkhdetailplot as ldp')
                    ->leftJoin('batch as b', 'ldp.batchno', '=', 'b.batchno')
                    ->where('ldp.companycode', $lkh->companycode)
                    ->where('ldp.lkhno', $lkh->lkhno)
                    ->select([
                        'ldp.blok',
                        'ldp.plot',
                        'ldp.luasrkh',
                        'ldp.batchno',
                        'b.lifecyclestatus' 
                    ])
                    ->get()
                    ->map(function($item) {
                        $plotInfo = $item->blok . '-' . $item->plot . ' (' . $item->luasrkh . ' ha)';
                        
                        if ($item->batchno) {
                            $plotInfo .= ' [' . $item->batchno . '-' . $item->lifecyclestatus . ']';
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

                // ✅ NEW: Get kendaraan count
                $kendaraanCount = DB::table('lkhdetailkendaraan')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->count();
                
                // ✅ NEW: Get kendaraan details
                $kendaraanList = DB::table('lkhdetailkendaraan as lk')
                    ->leftJoin('kendaraan as k', 'lk.nokendaraan', '=', 'k.nokendaraan')
                    ->leftJoin('tenagakerja as tk', 'lk.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('lk.companycode', $lkh->companycode)
                    ->where('lk.lkhno', $lkh->lkhno)
                    ->select([
                        'lk.nokendaraan',
                        'k.jenis as vehicle_type',
                        'tk.nama as operator_nama'
                    ])
                    ->get()
                    ->map(function($kendaraan) {
                        return $kendaraan->nokendaraan . ' (' . 
                               ($kendaraan->vehicle_type ?? 'Unknown') . ' - ' . 
                               ($kendaraan->operator_nama ?? 'No operator') . ')';
                    })
                    ->unique()
                    ->join(', ');

                return [
                    'lkhno' => $lkh->lkhno,
                    'activitycode' => $lkh->activitycode,
                    'activityname' => $lkh->activity->activityname ?? 'Unknown',
                    'jenis_label' => $this->getJenisLabel($lkh->jenistenagakerja),
                    'plots' => $plots ?: 'No plots assigned',
                    'status' => $lkh->status,
                    'workers_assigned' => $assignedWorkers,
                    'material_count' => $materialCount,
                    'kendaraan_count' => $kendaraanCount, // ✅ NEW
                    'kendaraan_list' => $kendaraanList ?: 'No kendaraan', // ✅ NEW
                    'totalhasil' => $lkh->totalhasil,
                    'totalsisa' => $lkh->totalsisa,
                    'totalupah' => $lkh->totalupahall
                ];
            })->toArray()
        ];

        return $summary;
    }

    /**
     * Calculate and update LKH wages (unchanged - no kendaraan impact)
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
     * UPDATED: Include kendaraan deletion
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
                
                // ✅ NEW: Delete kendaraan
                DB::table('lkhdetailkendaraan')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                // Delete BSM details if exists
                DB::table('lkhdetailbsm')
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

    /**
     * Create empty BSM placeholders for BSM activity (4.7)
     * Team mobile akan mengisi nilai B, S, M nanti
     * 
     * @param string $lkhno
     * @param \Illuminate\Support\Collection $activities
     * @param string $companycode
     * @return array
     */
    private function createBsmPlaceholders($lkhno, $activities, $companycode)
    {
        $bsmRecords = [];
        
        foreach ($activities as $activity) {
            // Get batch info for this plot
            $batchInfo = DB::table('masterlist')
                ->join('batch', 'masterlist.activebatchno', '=', 'batch.batchno')
                ->where('masterlist.companycode', $companycode)
                ->where('masterlist.plot', $activity->plot)
                ->where('masterlist.isactive', 1)
                ->where('batch.isactive', 1)
                ->select(['batch.batchno', 'batch.lifecyclestatus'])
                ->first();
            
            $bsmRecord = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'plot' => $activity->plot,
                'batchno' => $batchInfo ? $batchInfo->batchno : null,
                'nilaibersih' => null,
                'nilaisegar' => null,
                'nilaimanis' => null,
                'averagescore' => null,
                'grade' => null,
                'keterangan' => null,
                'inputby' => auth()->user()->userid ?? 'SYSTEM',
                'createdat' => now()
            ];
            
            DB::table('lkhdetailbsm')->insert($bsmRecord);
            $bsmRecords[] = $bsmRecord;
            
            Log::info("BSM placeholder created", [
                'lkhno' => $lkhno,
                'plot' => $activity->plot,
                'batchno' => $batchInfo ? $batchInfo->batchno : 'N/A'
            ]);
        }
        
        return [
            'success' => true,
            'total_plots' => count($bsmRecords),
            'records' => $bsmRecords
        ];
    }

    /**
     * Get BSM summary for specific LKH
     * 
     * @param string $lkhno
     * @return array
     */
    public function getBsmSummaryForLkh($lkhno)
    {
        $bsmRecords = DB::table('lkhdetailbsm as bsm')
            ->leftJoin('batch as b', 'bsm.batchno', '=', 'b.batchno')
            ->where('bsm.lkhno', $lkhno)
            ->select([
                'bsm.*',
                'b.lifecyclestatus'
            ])
            ->get();
        
        $completed = $bsmRecords->filter(function($record) {
            return $record->nilaibersih !== null && 
                $record->nilaisegar !== null && 
                $record->nilaimanis !== null;
        });
        
        $gradeDistribution = $completed->groupBy('grade')->map(function($group) {
            return $group->count();
        })->toArray();
        
        return [
            'total_plots' => $bsmRecords->count(),
            'completed' => $completed->count(),
            'pending' => $bsmRecords->count() - $completed->count(),
            'average_score_overall' => $completed->avg('averagescore'),
            'grade_distribution' => $gradeDistribution,
            'details' => $bsmRecords->map(function($record) {
                return [
                    'plot' => $record->plot,
                    'batchno' => $record->batchno,
                    'lifecyclestatus' => $record->lifecyclestatus ?? 'N/A',
                    'nilaibersih' => $record->nilaibersih,
                    'nilaisegar' => $record->nilaisegar,
                    'nilaimanis' => $record->nilaimanis,
                    'averagescore' => $record->averagescore,
                    'grade' => $record->grade,
                    'status' => $record->averagescore ? 'COMPLETED' : 'PENDING'
                ];
            })->toArray()
        ];
    }
}