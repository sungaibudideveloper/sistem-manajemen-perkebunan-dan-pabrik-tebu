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
 * UPDATED LkhGeneratorService - BSM Placeholder Generation REMOVED
 * 
 * BSM activity (4.7) generates only LKH header.
 * Android team will insert lkhdetailbsm records directly per SJ.
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
     * UPDATED: NO BSM placeholder generation
     * 
     * @param string $rkhno
     * @return array
     */
    public function generateLkhFromRkh($rkhno)
    {
        try {
            DB::beginTransaction();

            // ✅ Get companycode dari session jika tidak di-pass
            if (!$companycode) {
                $companycode = session('companycode');
            }

            if (!$companycode) {
                throw new \Exception("Company code tidak ditemukan");
            }

            // 1. Validate RKH exists and fully approved (WITH COMPANYCODE)
            $rkh = Rkhhdr::where('rkhno', $rkhno)
                ->where('companycode', $companycode)
                ->first();
                
            if (!$rkh) {
                throw new \Exception("RKH {$rkhno} not found for company {$companycode}");
            }

            if (!$this->isRkhFullyApproved($rkh)) {
                throw new \Exception("RKH {$rkhno} belum fully approved");
            }

            // 2. ✅ Check if LKH already generated (COMPOUND KEY CHECK)
            $existingLkh = Lkhhdr::where('rkhno', $rkhno)
                ->where('companycode', $companycode)
                ->exists();
            
            if ($existingLkh) {
                throw new \Exception("LKH untuk RKH {$rkhno} (company: {$companycode}) sudah pernah di-generate");
            }

            // 3. Get RKH activities (WITH COMPANYCODE)
            $rkhActivities = Rkhlst::where('rkhno', $rkhno)
                ->where('companycode', $companycode)
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
                $isBsm = ($activitycode === self::BSM_ACTIVITY);
                
                if (!$isBsm) {
                    $plotResult = $this->createLkhDetailPlots(
                        $lkhno, 
                        $groupActivities, 
                        $rkh->companycode,
                        $activitycode
                    );
                }
                
                // Generate LKH Detail Kendaraan
                $kendaraanResult = $this->generateLkhKendaraanRecords(
                    $rkh->rkhno,
                    $lkhno,
                    $activitycode,
                    $rkh->companycode,
                    $groupActivities
                );
                
                // Detect activity type
                $isPanen = in_array($activitycode, self::PANEN_ACTIVITIES);
                
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
                    'kendaraan_count' => $kendaraanResult['total_vehicles'],
                    'status' => 'DRAFT'
                ];
                
                // ✅ UPDATED: BSM activity - NO placeholder generation
                if ($isBsm) {
                    $lkhData['bsm_status'] = 'WAITING_ANDROID_INPUT';
                    $lkhData['bsm_note'] = 'Android will insert BSM records per SJ';
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
            'totalworkers' => null,
            'totalluasactual' => null,
            'totalhasil' => null,
            'totalsisa' => null,
            'totalupahall' => null,
            'status' => 'EMPTY', 
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
        $isBsmActivity = ($activitycode === self::BSM_ACTIVITY);
        
        $activity = DB::table('activity')->where('activitycode', $activitycode)->first();
        $isBlokActivity = $activity ? ($activity->isblokactivity == 1) : false;
        
        foreach ($activities as $activity) {
            $luasArea = (float) $activity->luasarea;
            $plotDetail = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'blok' => $activity->blok,
                'plot' => $isBlokActivity ? null : $activity->plot,
                'luasrkh' => null,
                'luashasil' => null,
                'luassisa' => null,
                'batchno' => $isBlokActivity ? null : ($activity->batchno ?? null),
                'createdat' => now()
            ];
            
            LkhDetailPlot::create($plotDetail);
            $plotDetails[] = $plotDetail;
            
            // Logging
            if ($isBlokActivity) {
                Log::info("Blok activity LKH detail created", [
                    'lkhno' => $lkhno,
                    'blok' => $activity->blok,
                    'plot' => 'NULL (blok activity)'
                ]);
            }
        }
        
        return $plotDetails;
    }

    /**
     * Generate LKH kendaraan records from RKH kendaraan assignments
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
            // Get kendaraan assignments dari RKH
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
            
            // Get plots
            $plots = $activities->pluck('plot')->unique()->values();
            
            if ($plots->isEmpty()) {
                Log::warning("No plots found for activity {$activitycode} in RKH {$rkhno}");
                return [
                    'success' => true,
                    'total_vehicles' => 0,
                    'records' => []
                ];
            }
            
            // Generate lkhdetailkendaraan
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
                'total_records' => count($records)
            ]);
            
            return [
                'success' => true,
                'total_vehicles' => $kendaraanAssignments->count(),
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
     * Generate LKH number
     * 
     * @param string $rkhno
     * @param int $index
     * @return string
     */
    private function generateLkhNumber($rkhno, $index)
    {
        $rkhPart = substr($rkhno, 3);
        return "LKH{$rkhPart}-{$index}";
    }

    /**
     * Get jenis label
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
     * Get approval requirements
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
     * Get BSM summary for specific LKH
     * UPDATED: Include suratjalanno and kodetebang
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
        
        if ($bsmRecords->isEmpty()) {
            return [
                'total_records' => 0,
                'completed' => 0,
                'pending' => 0,
                'message' => 'No BSM records found. Android will insert records per SJ.',
                'details' => []
            ];
        }
        
        $completed = $bsmRecords->filter(function($record) {
            return $record->nilaibersih !== null && 
                   $record->nilaisegar !== null && 
                   $record->nilaimanis !== null;
        });
        
        $gradeDistribution = $completed->groupBy('grade')->map(function($group) {
            return $group->count();
        })->toArray();
        
        $kodetebangDistribution = $bsmRecords->groupBy('kodetebang')->map(function($group) {
            return $group->count();
        })->toArray();
        
        return [
            'total_records' => $bsmRecords->count(),
            'total_sj' => $bsmRecords->pluck('suratjalanno')->unique()->count(),
            'completed' => $completed->count(),
            'pending' => $bsmRecords->count() - $completed->count(),
            'average_score_overall' => $completed->avg('averagescore'),
            'grade_distribution' => $gradeDistribution,
            'kodetebang_distribution' => $kodetebangDistribution,
            'details' => $bsmRecords->map(function($record) {
                return [
                    'suratjalanno' => $record->suratjalanno,
                    'plot' => $record->plot,
                    'kodetebang' => $record->kodetebang,
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

    /**
     * Calculate and update LKH wages
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
     * Regenerate LKH
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
                LkhDetailWorker::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                LkhDetailPlot::where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                DB::table('lkhdetailmaterial')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                DB::table('lkhdetailkendaraan')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
                // ⚠️ WARNING: This will delete BSM records inserted by Android
                DB::table('lkhdetailbsm')
                    ->where('companycode', $lkh->companycode)
                    ->where('lkhno', $lkh->lkhno)
                    ->delete();
                
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