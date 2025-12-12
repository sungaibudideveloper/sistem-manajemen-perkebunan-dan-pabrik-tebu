<?php

namespace App\Services\Transaction\RencanaKerjaHarian;

use App\Repositories\Transaction\RencanaKerjaHarian\RkhRepository;
use App\Repositories\Transaction\RencanaKerjaHarian\BatchRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * RkhService
 * 
 * Business logic for RKH (Rencana Kerja Harian) operations
 * Orchestrates repositories and handles complex business rules
 */
class RkhService
{
    protected RkhRepository $rkhRepo;
    protected BatchRepository $batchRepo;

    public function __construct(
        RkhRepository $rkhRepo,
        BatchRepository $batchRepo
    ) {
        $this->rkhRepo = $rkhRepo;
        $this->batchRepo = $batchRepo;
    }

    /**
     * Get RKH list with filters
     * 
     * @param string $companycode
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRkhList(string $companycode, array $filters = [], int $perPage = 10)
    {
        $query = $this->rkhRepo->getIndexQuery($companycode, $filters);
        $rkhData = $query->paginate($perPage);

        // Enhance with LKH progress
        $rkhData = $this->enhanceWithLkhProgress($rkhData, $companycode);

        return $rkhData;
    }

    /**
     * Get RKH detail by rkhno
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function getRkhDetail(string $companycode, string $rkhno): array
    {
        $header = $this->rkhRepo->findByRkhNo($companycode, $rkhno);
        
        if (!$header) {
            throw new \Exception('RKH tidak ditemukan');
        }

        $details = $this->rkhRepo->getDetails($companycode, $rkhno);
        $workers = $this->rkhRepo->getWorkersByActivity($companycode, $rkhno);
        $kendaraan = $this->rkhRepo->getKendaraanByActivity($companycode, $rkhno);

        return [
            'header' => $header,
            'details' => $details,
            'workers' => $workers,
            'kendaraan' => $kendaraan
        ];
    }

    /**
     * Create new RKH
     * 
     * @param string $companycode
     * @param array $data
     * @return array ['success' => bool, 'rkhno' => string, 'message' => string]
     */
    public function createRkh(string $companycode, array $data): array
    {
        try {
            DB::beginTransaction();

            // Generate unique RKH number with lock
            $rkhno = $this->generateUniqueRkhNo($companycode, $data['tanggal']);

            // Prepare header data
            $headerData = $this->prepareRkhHeaderData($companycode, $rkhno, $data);
            
            // Create RKH header
            $rkhId = $this->rkhRepo->create($headerData);

            // Create details (rkhlst)
            $details = $this->buildRkhDetails($companycode, $rkhno, $data['rows'], $data['tanggal']);
            $this->rkhRepo->createDetails($details);

            // Create worker assignments
            $workers = $this->buildWorkerAssignments($companycode, $rkhno, $data['workers']);
            $this->rkhRepo->createWorkers($workers);

            // Create kendaraan assignments
            if (!empty($data['kendaraan'])) {
                $kendaraan = $this->buildKendaraanAssignments($companycode, $rkhno, $data['kendaraan']);
                $this->rkhRepo->createKendaraan($kendaraan);
            }

            DB::commit();

            return [
                'success' => true,
                'rkhno' => $rkhno,
                'message' => "RKH berhasil dibuat dengan nomor: {$rkhno}"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Create RKH Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal membuat RKH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update existing RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param array $data
     * @return array
     */
    public function updateRkh(string $companycode, string $rkhno, array $data): array
    {
        try {
            // Security check: cannot edit if approved
            $rkh = $this->rkhRepo->findByRkhNo($companycode, $rkhno);
            
            if (!$rkh) {
                throw new \Exception('RKH tidak ditemukan');
            }

            if ($this->isRkhApproved($rkh)) {
                throw new \Exception('RKH tidak dapat diedit karena sudah disetujui');
            }

            DB::beginTransaction();

            // Update header
            $headerData = $this->prepareRkhHeaderDataForUpdate($data);
            $this->rkhRepo->updateByRkhNo($companycode, $rkhno, $headerData);

            // Update details (delete + recreate)
            $this->rkhRepo->deleteDetails($companycode, $rkhno);
            $details = $this->buildRkhDetails($companycode, $rkhno, $data['rows'], $data['tanggal']);
            $this->rkhRepo->createDetails($details);

            // Update workers
            $this->rkhRepo->deleteWorkers($companycode, $rkhno);
            $workers = $this->buildWorkerAssignments($companycode, $rkhno, $data['workers']);
            $this->rkhRepo->createWorkers($workers);

            // Update kendaraan
            $this->rkhRepo->deleteKendaraan($companycode, $rkhno);
            if (!empty($data['kendaraan'])) {
                $kendaraan = $this->buildKendaraanAssignments($companycode, $rkhno, $data['kendaraan']);
                $this->rkhRepo->createKendaraan($kendaraan);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'RKH berhasil diupdate'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Update RKH Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal update RKH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete RKH
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function deleteRkh(string $companycode, string $rkhno): array
    {
        try {
            // Security check
            $rkh = $this->rkhRepo->findByRkhNo($companycode, $rkhno);
            
            if (!$rkh) {
                throw new \Exception('RKH tidak ditemukan');
            }

            if ($this->isRkhApproved($rkh)) {
                throw new \Exception('RKH tidak dapat dihapus karena sudah disetujui');
            }

            DB::beginTransaction();

            // Delete in order (foreign key constraints)
            $this->rkhRepo->deleteKendaraan($companycode, $rkhno);
            $this->rkhRepo->deleteWorkers($companycode, $rkhno);
            $this->rkhRepo->deleteDetails($companycode, $rkhno);
            $this->rkhRepo->deleteByRkhNo($companycode, $rkhno);

            DB::commit();

            return [
                'success' => true,
                'message' => 'RKH berhasil dihapus'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Delete RKH Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal menghapus RKH: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update RKH status
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param string $status
     * @return array
     */
    public function updateStatus(string $companycode, string $rkhno, string $status): array
    {
        try {
            // Validate status transition
            if ($status === 'Completed') {
                $progressStatus = $this->getRkhProgressStatus($rkhno, $companycode);
                
                if (!$progressStatus['can_complete']) {
                    throw new \Exception(
                        'Tidak dapat menandai RKH sebagai Completed. ' . 
                        $progressStatus['progress'] . 
                        '. Semua LKH harus diapprove terlebih dahulu.'
                    );
                }
            }

            $updateData = [
                'status' => $status,
                'updateby' => Auth::user()->userid,
                'updatedat' => now()
            ];

            $this->rkhRepo->updateByRkhNo($companycode, $rkhno, $updateData);

            return [
                'success' => true,
                'message' => "Status RKH berhasil diupdate menjadi {$status}"
            ];

        } catch (\Exception $e) {
            \Log::error("Update RKH status error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal update status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process RKH approval
     * 
     * @param string $companycode
     * @param string $rkhno
     * @param int $level
     * @param string $action (approve/decline)
     * @param object $currentUser
     * @return array
     */
    public function processApproval(string $companycode, string $rkhno, int $level, string $action, object $currentUser): array
    {
        try {
            DB::beginTransaction();

            $rkh = $this->rkhRepo->getApprovalDetail($companycode, $rkhno);

            if (!$rkh) {
                throw new \Exception('RKH tidak ditemukan');
            }

            // Validate approval authority
            $this->validateApprovalAuthority($rkh, $currentUser, $level);

            // Process approval
            $approvalValue = $action === 'approve' ? '1' : '0';
            $approvalField = "approval{$level}flag";
            $approvalDateField = "approval{$level}date";
            $approvalUserField = "approval{$level}userid";
            
            $updateData = [
                $approvalField => $approvalValue,
                $approvalDateField => now(),
                $approvalUserField => $currentUser->userid,
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ];

            // Update approvalstatus
            if ($action === 'approve') {
                $tempRkh = clone $rkh;
                $tempRkh->$approvalField = '1';
                
                if ($this->isRkhFullyApproved($tempRkh)) {
                    $updateData['approvalstatus'] = '1';
                } else {
                    $updateData['approvalstatus'] = null;
                }
            } else {
                $updateData['approvalstatus'] = '0';
            }

            $this->rkhRepo->updateByRkhNo($companycode, $rkhno, $updateData);

            $message = 'RKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Handle post-approval actions
            if ($action === 'approve' && ($updateData['approvalstatus'] ?? null) === '1') {
                $message = $this->handlePostApprovalActions($companycode, $rkhno, $message);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $message
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Approval process error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal proses approval: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get pending approvals for user
     * 
     * @param string $companycode
     * @param object $currentUser
     * @return Collection
     */
    public function getPendingApprovalsForUser(string $companycode, object $currentUser): Collection
    {
        if (!$currentUser->idjabatan) {
            return collect([]);
        }

        $pendingRKH = $this->rkhRepo->getPendingApprovals($companycode, $currentUser->idjabatan);

        return $pendingRKH->map(function($rkh) {
            return [
                'rkhno' => $rkh->rkhno,
                'rkhdate' => $rkh->rkhdate,
                'rkhdate_formatted' => Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
                'mandor_nama' => $rkh->mandor_nama,
                'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
                'approval_level' => $rkh->approval_level,
                'total_luas' => $rkh->totalluas,
                'manpower' => $rkh->manpower
            ];
        });
    }

    /**
     * Check if mandor has outstanding RKH
     * 
     * @param string $companycode
     * @param string $mandorId
     * @return array
     */
    public function checkOutstandingRkh(string $companycode, string $mandorId): array
    {
        $outstanding = $this->rkhRepo->getOutstandingRkh($companycode, $mandorId);

        if (!$outstanding) {
            return [
                'hasOutstanding' => false,
                'message' => 'Mandor tidak memiliki RKH outstanding'
            ];
        }

        return [
            'hasOutstanding' => true,
            'message' => 'Mandor masih memiliki RKH yang belum diselesaikan',
            'details' => [
                'rkhno' => $outstanding->rkhno,
                'rkhdate' => Carbon::parse($outstanding->rkhdate)->format('d/m/Y'),
                'status' => $outstanding->status
            ]
        ];
    }

    /**
     * Validate plots for activities
     * 
     * @param array $rows
     * @param string $companycode
     * @return array (errors)
     */
    public function validatePlots(array $rows, string $companycode): array
    {
        $errors = [];

        // Validate plot requirement
        $errors = array_merge($errors, $this->validatePlotRequirement($rows));

        // Validate planting plots
        $errors = array_merge($errors, $this->validatePlantingPlots($rows, $companycode));

        // Validate panen plots
        $errors = array_merge($errors, $this->validatePanenPlots($rows, $companycode));

        return $errors;
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Generate unique RKH number with lock
     */
    private function generateUniqueRkhNo(string $companycode, string $date): string
    {
        $carbonDate = Carbon::parse($date);
        $day = $carbonDate->format('d');
        $month = $carbonDate->format('m');
        $year = $carbonDate->format('y');
        $prefix = "RKH{$day}{$month}";

        return DB::transaction(function () use ($companycode, $carbonDate, $prefix, $year) {
            $lastRkh = $this->rkhRepo->getLastRkhNoForDate($companycode, $carbonDate->format('Y-m-d'), $prefix);

            if ($lastRkh) {
                $lastNumber = (int)substr($lastRkh->rkhno, 7, 2);
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '01';
            }

            $rkhno = "{$prefix}{$newNumber}{$year}";

            // Safety check
            if ($this->rkhRepo->exists($companycode, $rkhno)) {
                throw new \Exception("Duplicate RKH number: {$rkhno}");
            }

            return $rkhno;
        });
    }

    /**
     * Prepare RKH header data for create
     */
    private function prepareRkhHeaderData(string $companycode, string $rkhno, array $data): array
    {
        $totalLuas = collect($data['rows'])->sum('luas');
        $totalManpower = collect($data['workers'])->sum('total');
        
        $primaryActivityGroup = $this->getPrimaryActivityGroup($data['rows']);
        $approvalData = $this->getApprovalData($companycode, $primaryActivityGroup);

        return array_merge([
            'companycode' => $companycode,
            'rkhno' => $rkhno,
            'rkhdate' => $data['tanggal'],
            'totalluas' => $totalLuas,
            'manpower' => $totalManpower,
            'mandorid' => $data['mandor_id'],
            'activitygroup' => $primaryActivityGroup,
            'keterangan' => $data['keterangan'] ?? null,
            'inputby' => Auth::user()->userid,
            'createdat' => now(),
        ], $approvalData);
    }

    /**
     * Prepare RKH header data for update
     */
    private function prepareRkhHeaderDataForUpdate(array $data): array
    {
        $totalLuas = collect($data['rows'])->sum('luas');
        $totalManpower = collect($data['workers'])->sum('total');

        return [
            'rkhdate' => $data['tanggal'],
            'totalluas' => $totalLuas,
            'manpower' => $totalManpower,
            'mandorid' => $data['mandor_id'],
            'keterangan' => $data['keterangan'] ?? null,
            'updateby' => Auth::user()->userid,
            'updatedat' => now(),
            // Reset approval flags
            'approval1flag' => null,
            'approval2flag' => null,
            'approval3flag' => null,
            'approval1date' => null,
            'approval2date' => null,
            'approval3date' => null,
            'approval1userid' => null,
            'approval2userid' => null,
            'approval3userid' => null,
        ];
    }

    /**
     * Build RKH details records
     */
    private function buildRkhDetails(string $companycode, string $rkhno, array $rows, string $tanggal): array
    {
        $details = [];
        
        foreach ($rows as $row) {
            $activity = DB::table('activity')->where('activitycode', $row['nama'])->first();
            $jenistenagakerja = $activity ? $activity->jenistenagakerja : null;
            $isBlokActivity = $activity ? ($activity->isblokactivity == 1) : false;

            $batchInfo = null;
            if (!$isBlokActivity && !empty($row['plot'])) {
                $batchInfo = $this->batchRepo->getActiveBatchForPlot($companycode, $row['plot']);
            }

            $details[] = [
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'rkhdate' => $tanggal,
                'blok' => $row['blok'],
                'plot' => $isBlokActivity ? null : $row['plot'],
                'activitycode' => $row['nama'],
                'luasarea' => $row['luas'] ?? null,
                'jenistenagakerja' => $jenistenagakerja,
                'usingmaterial' => !empty($row['material_group_id']) ? 1 : 0,
                'herbisidagroupid' => !empty($row['material_group_id']) ? (int) $row['material_group_id'] : null,
                'batchid' => $batchInfo ? $batchInfo->id : null,
            ];
        }
        
        return $details;
    }

    /**
     * Build worker assignments
     */
    private function buildWorkerAssignments(string $companycode, string $rkhno, array $workers): array
    {
        $records = [];
        
        foreach ($workers as $activityCode => $worker) {
            $laki = (int) ($worker['laki'] ?? 0);
            $perempuan = (int) ($worker['perempuan'] ?? 0);
            
            $records[] = [
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'activitycode' => $activityCode,
                'jumlahlaki' => $laki,
                'jumlahperempuan' => $perempuan,
                'jumlahtenagakerja' => $laki + $perempuan,
                'createdat' => now()
            ];
        }
        
        return $records;
    }

    /**
     * Build kendaraan assignments
     */
    private function buildKendaraanAssignments(string $companycode, string $rkhno, array $kendaraan): array
    {
        $records = [];
        
        foreach ($kendaraan as $activityCode => $vehicles) {
            foreach ($vehicles as $index => $vehicle) {
                if (empty($vehicle['nokendaraan']) || empty($vehicle['operatorid'])) {
                    continue;
                }

                // Get kendaraan ID
                $kendaraanData = DB::table('kendaraan')
                    ->where('companycode', $companycode)
                    ->where('nokendaraan', $vehicle['nokendaraan'])
                    ->first();

                if (!$kendaraanData) {
                    continue;
                }

                $records[] = [
                    'companycode' => $companycode,
                    'rkhno' => $rkhno,
                    'activitycode' => $activityCode,
                    'kendaraanid' => $kendaraanData->id,
                    'operatorid' => $vehicle['operatorid'],
                    'usinghelper' => $vehicle['usinghelper'] ?? 0,
                    'helperid' => $vehicle['helperid'] ?? null,
                    'urutan' => $index + 1,
                    'createdat' => now()
                ];
            }
        }
        
        return $records;
    }

    /**
     * Get primary activity group from rows
     */
    private function getPrimaryActivityGroup(array $rows): ?string
    {
        foreach ($rows as $row) {
            if (!empty($row['nama'])) {
                $activity = DB::table('activity')->where('activitycode', $row['nama'])->first();
                if ($activity && $activity->activitygroup) {
                    return $activity->activitygroup;
                }
            }
        }
        return null;
    }

    /**
     * Get approval data from settings
     */
    private function getApprovalData(string $companycode, ?string $activityGroup): array
    {
        if (!$activityGroup) {
            return [];
        }
        
        $approvalSetting = DB::table('approval')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activityGroup)
            ->first();
        
        if (!$approvalSetting) {
            return [];
        }

        return [
            'jumlahapproval' => $approvalSetting->jumlahapproval,
            'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
            'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
            'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
        ];
    }

    /**
     * Check if RKH is approved
     */
    private function isRkhApproved(object $rkh): bool
    {
        return $rkh->approval1flag === '1' || 
               $rkh->approval2flag === '1' || 
               $rkh->approval3flag === '1';
    }

    /**
     * Check if RKH is fully approved
     */
    private function isRkhFullyApproved(object $rkh): bool
    {
        if (!$rkh->jumlahapproval || $rkh->jumlahapproval == 0) {
            return true;
        }

        return match($rkh->jumlahapproval) {
            1 => $rkh->approval1flag === '1',
            2 => $rkh->approval1flag === '1' && $rkh->approval2flag === '1',
            3 => $rkh->approval1flag === '1' && $rkh->approval2flag === '1' && $rkh->approval3flag === '1',
            default => false
        };
    }

    /**
     * Get RKH progress status
     */
    private function getRkhProgressStatus(string $rkhno, string $companycode): array
    {
        $lkhData = DB::table('lkhhdr')
            ->where('rkhno', $rkhno)
            ->where('companycode', $companycode)
            ->get();
        
        if ($lkhData->isEmpty()) {
            return [
                'can_complete' => false,
                'progress' => 'No LKH Created'
            ];
        }
        
        $totalLkh = $lkhData->count();
        $completedLkh = $lkhData->where('status', 'APPROVED')->count();
        
        return [
            'can_complete' => ($completedLkh === $totalLkh),
            'progress' => "LKH Progress ({$completedLkh}/{$totalLkh})"
        ];
    }

    /**
     * Enhance RKH data with LKH progress
     */
    private function enhanceWithLkhProgress($rkhData, string $companycode)
    {
        $lkhProgress = DB::table('lkhhdr')
            ->whereIn('rkhno', $rkhData->pluck('rkhno'))
            ->where('companycode', $companycode)
            ->select('rkhno', 'status')
            ->get()
            ->groupBy('rkhno')
            ->map(function($lkhs) {
                if ($lkhs->isEmpty()) {
                    return [
                        'status' => 'no_lkh',
                        'progress' => 'No LKH Created',
                        'can_complete' => false,
                        'color' => 'gray'
                    ];
                }
                
                $totalLkh = $lkhs->count();
                $completedLkh = $lkhs->where('status', 'APPROVED')->count();
                
                if ($completedLkh === $totalLkh) {
                    return [
                        'status' => 'complete',
                        'progress' => 'All Complete',
                        'can_complete' => true,
                        'color' => 'green'
                    ];
                }
                
                return [
                    'status' => 'in_progress',
                    'progress' => "LKH In Progress ({$completedLkh}/{$totalLkh})",
                    'can_complete' => false,
                    'color' => 'yellow'
                ];
            });

        $rkhData->getCollection()->transform(function ($rkh) use ($lkhProgress) {
            $rkh->lkh_progress_status = $lkhProgress[$rkh->rkhno] ?? [
                'status' => 'no_lkh',
                'progress' => 'No LKH Created',
                'can_complete' => false,
                'color' => 'gray'
            ];
            return $rkh;
        });

        return $rkhData;
    }

    /**
     * Validate approval authority
     */
    private function validateApprovalAuthority(object $rkh, object $currentUser, int $level): void
    {
        $approvalJabatanField = "idjabatanapproval{$level}";
        $approvalField = "approval{$level}flag";

        if (!isset($rkh->$approvalJabatanField) || $rkh->$approvalJabatanField != $currentUser->idjabatan) {
            throw new \Exception('Anda tidak memiliki wewenang untuk approve level ini');
        }

        if (isset($rkh->$approvalField) && $rkh->$approvalField !== null) {
            throw new \Exception('Approval level ini sudah diproses sebelumnya');
        }

        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($rkh->$prevApprovalField) || $rkh->$prevApprovalField !== '1') {
                throw new \Exception('Approval level sebelumnya belum disetujui');
            }
        }
    }

    /**
     * Handle post-approval actions (LKH generation, batch creation, material usage)
     */
    private function handlePostApprovalActions(string $companycode, string $rkhno, string $message): string
    {
        // This will be implemented when we create LkhGeneratorService
        // For now, just return the message
        return $message;
    }

    /**
     * Validate plot requirement based on activity type
     */
    private function validatePlotRequirement(array $rows): array
    {
        $errors = [];
        
        foreach ($rows as $index => $row) {
            $activityCode = $row['nama'] ?? '';
            $plot = $row['plot'] ?? '';
            
            if (empty($activityCode)) {
                continue;
            }
            
            $activity = DB::table('activity')->where('activitycode', $activityCode)->first();
            
            if (!$activity) {
                $errors[] = "Baris " . ($index + 1) . ": Activity code '{$activityCode}' tidak ditemukan.";
                continue;
            }
            
            $isBlokActivity = ($activity->isblokactivity == 1);
            
            if ($isBlokActivity && !empty($plot)) {
                $errors[] = "Baris " . ($index + 1) . ": Activity '{$activityCode}' adalah blok activity, tidak boleh memiliki plot spesifik.";
            } elseif (!$isBlokActivity && empty($plot)) {
                $errors[] = "Baris " . ($index + 1) . ": Activity '{$activityCode}' memerlukan plot. Plot tidak boleh kosong.";
            }
        }
        
        return $errors;
    }

    /**
     * Validate planting plots
     */
    private function validatePlantingPlots(array $rows, string $companycode): array
    {
        $errors = [];
        
        foreach ($rows as $index => $row) {
            if (($row['nama'] ?? '') === '2.2.7') {
                $plot = $row['plot'] ?? '';
                
                if ($plot && $this->batchRepo->plotHasActiveBatch($companycode, $plot)) {
                    $errors[] = "Baris " . ($index + 1) . ": Plot {$plot} masih memiliki batch aktif. Tidak dapat ditanam ulang.";
                }
            }
        }
        
        return $errors;
    }

    /**
     * Validate panen plots
     */
    private function validatePanenPlots(array $rows, string $companycode): array
    {
        $errors = [];
        $panenActivities = ['4.3.3', '4.4.3', '4.5.2'];
        
        foreach ($rows as $index => $row) {
            if (!in_array($row['nama'] ?? '', $panenActivities)) {
                continue;
            }
            
            $plot = $row['plot'] ?? '';
            $luas = (float) ($row['luas'] ?? 0);
            
            if (!$plot || $luas <= 0) {
                continue;
            }
            
            $batch = $this->batchRepo->getActiveBatchForPlot($companycode, $plot);
            
            if (!$batch) {
                $errors[] = "Baris " . ($index + 1) . ": Plot {$plot} tidak memiliki batch aktif untuk dipanen.";
                continue;
            }
            
            $totalSudahPanen = $this->batchRepo->getTotalPanenForBatch($companycode, $batch->id, now()->format('Y-m-d'));
            $luasSisa = $batch->batcharea - $totalSudahPanen;
            
            if (round($luas, 2) > round($luasSisa, 2)) {
                $errors[] = "Baris " . ($index + 1) . ": Luas panen ({$luas} Ha) melebihi luas sisa (" . number_format($luasSisa, 2) . " Ha) untuk plot {$plot}.";
            }
        }
        
        return $errors;
    }

    /**
     * Get approval detail formatted for frontend
     * 
     * @param string $companycode
     * @param string $rkhno
     * @return array
     */
    public function getApprovalDetailFormatted(string $companycode, string $rkhno): array
    {
        $rkh = $this->rkhRepo->getApprovalDetail($companycode, $rkhno);
        
        if (!$rkh) {
            throw new \Exception('RKH tidak ditemukan');
        }

        $levels = [];
        $jumlahApproval = $rkh->jumlahapproval ?? 0;

        for ($i = 1; $i <= 3; $i++) {
            $jabatanField = "idjabatanapproval{$i}";
            $approvalField = "approval{$i}flag";
            $dateField = "approval{$i}date";
            $userField = "approval{$i}userid";
            $jabatanNameField = "jabatan{$i}_name";
            $userNameField = "user{$i}_name";

            if ($i > $jumlahApproval) {
                // Not required
                $levels[] = [
                    'level' => $i,
                    'status' => 'not_required',
                    'status_text' => 'Not Required',
                    'jabatan_name' => '-',
                    'user_name' => null,
                    'date_formatted' => null
                ];
                continue;
            }

            $approvalFlag = $rkh->$approvalField ?? null;
            
            if ($approvalFlag === '1') {
                $status = 'approved';
                $statusText = 'Approved';
            } elseif ($approvalFlag === '0') {
                $status = 'declined';
                $statusText = 'Declined';
            } else {
                $status = 'waiting';
                $statusText = 'Waiting for Approval';
            }

            $levels[] = [
                'level' => $i,
                'status' => $status,
                'status_text' => $statusText,
                'jabatan_name' => $rkh->$jabatanNameField ?? "Level {$i}",
                'user_name' => $rkh->$userNameField ?? null,
                'date_formatted' => $rkh->$dateField ? 
                    Carbon::parse($rkh->$dateField)->format('d/m/Y H:i') : null
            ];
        }

        return [
            'rkhno' => $rkh->rkhno,
            'rkhdate' => $rkh->rkhdate,
            'rkhdate_formatted' => Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
            'mandor_nama' => $rkh->mandor_nama ?? 'Unknown',
            'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
            'jumlahapproval' => $jumlahApproval,
            'levels' => $levels
        ];
    }

    /**
     * Get plot info for activity
     * 
     * @param string $companycode
     * @param string $plot
     * @param string $activitycode
     * @return array
     */
    public function getPlotInfo(string $companycode, string $plot, string $activitycode): array
    {
        // Get masterlist info
        $masterlist = DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('plot', $plot)
            ->where('isactive', 1)
            ->first();

        if (!$masterlist) {
            throw new \Exception("Plot {$plot} tidak ditemukan");
        }

        // Get active batch if exists
        $activeBatch = null;
        if ($masterlist->activebatchno) {
            $activeBatch = DB::table('batch')
                ->where('companycode', $companycode)
                ->where('batchno', $masterlist->activebatchno)
                ->first();
        }

        return [
            'plot' => $plot,
            'blok' => $masterlist->blok,
            'activebatchno' => $masterlist->activebatchno,
            'batch_info' => $activeBatch ? [
                'batchno' => $activeBatch->batchno,
                'kategori' => $activeBatch->kategori,
                'batcharea' => $activeBatch->batcharea,
                'tanggaltanam' => $activeBatch->tanggaltanam
            ] : null
        ];
    }

    /**
     * Get create form data
     * 
     * @param string $companycode
     * @param string $date
     * @return array
     */
    public function getCreateFormData(string $companycode, string $date): array
    {
        // Get activities
        $activities = DB::table('activity')
            ->join('activitygroup', 'activity.activitygroup', '=', 'activitygroup.activitygroup')
            ->select([
                'activity.activitycode',
                'activity.activityname',
                'activity.activitygroup',
                'activity.jenistenagakerja',
                'activity.isblokactivity',
                'activitygroup.groupname'
            ])
            ->orderBy('activitygroup.groupname')
            ->orderBy('activity.activityname')
            ->get()
            ->groupBy('groupname');

        // Get bloks
        $bloks = DB::table('blok')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->select('blok', 'luas')
            ->orderBy('blok')
            ->get();

        // Get herbisida groups
        $herbisidaGroups = DB::table('herbisidagroup')
            ->where('companycode', $companycode)
            ->select('herbisidagroupid', 'herbisidagroupname')
            ->get();

        // Get attendance data
        $absenData = DB::table('absenhdr as h')
            ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
            ->join('tenagakerja as t', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                    ->where('t.companycode', '=', $companycode)
                    ->where('t.isactive', '=', 1);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.uploaddate', $date)
            ->where('l.approval_status', 'APPROVED')
            ->select([
                'h.absenno',
                'h.mandorid',
                'l.tenagakerjaid',
                't.nama',
                't.nik',
                't.gender',
                't.jenistenagakerja',
                'l.approval_status'
            ])
            ->get();

        // Get kendaraan (operators)
        $kendaraan = DB::table('kendaraan')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->select('nokendaraan', 'jenis')
            ->orderBy('nokendaraan')
            ->get();

        // Get operators (tenaga kerja with jenistenagakerja = 3)
        $operators = DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('jenistenagakerja', 3) // Operator
            ->where('isactive', 1)
            ->select('tenagakerjaid', 'nama', 'nik')
            ->orderBy('nama')
            ->get();

        // Get helpers (tenaga kerja with jenistenagakerja = 4)
        $helpers = DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('jenistenagakerja', 4) // Helper
            ->where('isactive', 1)
            ->select('tenagakerjaid', 'nama', 'nik')
            ->orderBy('nama')
            ->get();

        return [
            'activities' => $activities,
            'bloks' => $bloks,
            'herbisidagroups' => $herbisidaGroups,
            'absentenagakerja' => $absenData,
            'kendaraan' => $kendaraan,
            'operators' => $operators,
            'helpers' => $helpers,
        ];
    }
}