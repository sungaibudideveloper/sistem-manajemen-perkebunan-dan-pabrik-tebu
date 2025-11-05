<?php

namespace App\Http\Controllers\Input;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

// Models
use App\Models\User;
use App\Models\RkhHdr;
use App\Models\Activity;
use App\Models\ActivityGroup;
use App\Models\Blok;
use App\Models\Masterlist;
use App\Models\Herbisidadosage;
use App\Models\Herbisidagroup;
use App\Models\AbsenHdr;
use App\Models\AbsenLst;
use App\Models\Lkhhdr;
use App\Models\LkhDetailPlot; // FIXED: Import new model
use App\Models\LkhDetailWorker; // FIXED: Import new model
use App\Models\LkhDetailMaterial; // FIXED: Import new model
use App\Models\Kendaraan;
use App\Models\TenagaKerja;

// Services
use App\Services\LkhGeneratorService;
use App\Services\MaterialUsageGeneratorService;
use App\Services\WageCalculationService;

/**
 * RencanaKerjaHarianController
 * 
 * Handles RKH (Rencana Kerja Harian) management with three main sections:
 * 1. RKH CRUD Operations
 * 2. LKH (Laporan Kegiatan Harian) Management
 * 3. DTH (Distribusi Tenaga Harian) Reports
 * 4. Material Usage Management
 */
class RencanaKerjaHarianController extends Controller
{
    // =====================================
    // SECTION 1: RKH CRUD OPERATIONS
    // =====================================

    /**
     * Display listing of RKH records with filtering and pagination
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search = $request->input('search');
        $filterApproval = $request->input('filter_approval');
        $filterStatus = $request->input('filter_status');
        $filterDate = $request->input('filter_date');
        $allDate = $request->input('all_date');
        
        $companycode = Session::get('companycode');

        // Build query with joins
        $query = $this->buildRkhIndexQuery($companycode);

        // Apply filters
        $query = $this->applyIndexFilters($query, $search, $filterApproval, $filterStatus, $filterDate, $allDate);

        $query->orderBy('r.rkhdate', 'desc')->orderBy('r.rkhno', 'desc');
        $rkhData = $query->paginate($perPage);

        // NEW: Add progress status untuk setiap RKH
        $rkhData->getCollection()->transform(function ($rkh) use ($companycode) {
            $rkh->lkh_progress_status = $this->getRkhProgressStatus($rkh->rkhno, $companycode);
            return $rkh;
        });

        // Get attendance data for modal
        $absenData = $this->getAttendanceData($companycode, $filterDate);

        return view('input.rencanakerjaharian.index', [
            'title' => 'Rencana Kerja Harian',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'perPage' => $perPage,
            'search' => $search,
            'filterApproval' => $filterApproval,
            'filterStatus' => $filterStatus,
            'filterDate' => $filterDate,
            'allDate' => $allDate,
            'rkhData' => $rkhData,
            'absentenagakerja' => $absenData,
        ]);
    }

    /**
     * Show create form for new RKH
     */
    public function create(Request $request)
    {
        $selectedDate = $request->input('date');
        
        if (!$selectedDate) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Silakan pilih tanggal terlebih dahulu');
        }

        // Validate date range
        if (!$this->validateDateRange($selectedDate)) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Tanggal harus dalam rentang hari ini sampai 7 hari ke depan');
        }

        $targetDate = Carbon::parse($selectedDate);
        $companycode = Session::get('companycode');

        // Generate preview RKH number
        $previewRkhNo = $this->generatePreviewRkhNo($targetDate, $companycode);

        // Load form data
        $formData = $this->loadCreateFormData($companycode, $targetDate);

        return view('input.rencanakerjaharian.create', array_merge([
            'title' => 'Form RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhno' => $previewRkhNo,
            'selectedDate' => $targetDate->format('Y-m-d'),
            'oldInput' => old(),
        ], $formData));
    }

    /**
     * Store new RKH record
     */
    public function store(Request $request)
    {
        // Filter and validate input
        $filteredRows = $this->filterValidRows($request->input('rows', []));
        $request->merge(['rows' => $filteredRows]);

        try {
            $this->validateRkhRequest($request);

            $rkhno = null;
            
            DB::transaction(function () use ($request, &$rkhno) {
                $rkhno = $this->createRkhRecord($request);
            });

            return $this->handleStoreResponse($request, $rkhno, true);

        } catch (\Exception $e) {
            \Log::error("Store RKH Error: " . $e->getMessage());
            return $this->handleStoreResponse($request, null, false, $e->getMessage());
        }
    }

    /**
     * Display specific RKH record - Updated dengan data herbisida
     */
    public function show($rkhno)
    {
        $companycode = Session::get('companycode');
        
        $rkhHeader = $this->getRkhHeader($companycode, $rkhno);
        
        if (!$rkhHeader) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Data RKH tidak ditemukan');
        }
        
        $rkhDetails = $this->getRkhDetails($companycode, $rkhno);
        $absenData = $this->getAttendanceData($companycode, $rkhHeader->rkhdate);
        $operatorsWithVehicles = $this->getOperatorsWithVehicles($companycode);
        
        $herbisidadosages = new Herbisidadosage;
        $herbisidaData = $herbisidadosages->getFullHerbisidaGroupData($companycode);
        
        // ✅ UPDATED: Add JOIN to jenistenagakerja
        $workersByActivity = DB::table('rkhlstworker as w')
            ->leftJoin('activity as a', 'w.activitycode', '=', 'a.activitycode')
            ->leftJoin('jenistenagakerja as j', 'a.jenistenagakerja', '=', 'j.idjenistenagakerja') // ✅ NEW
            ->where('w.companycode', $companycode)
            ->where('w.rkhno', $rkhno)
            ->select([
                'w.activitycode',
                'a.activityname',
                'a.jenistenagakerja',
                'j.nama as jenis_nama', // ✅ NEW
                'w.jumlahlaki',
                'w.jumlahperempuan',
                'w.jumlahtenagakerja'
            ])
            ->orderBy('w.activitycode')
            ->get();
        
        return view('input.rencanakerjaharian.show', [
            'title' => 'Detail RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhHeader' => $rkhHeader,
            'rkhDetails' => $rkhDetails,
            'workersByActivity' => $workersByActivity,
            'absentenagakerja' => $absenData,
            'operatorsData' => $operatorsWithVehicles,
            'herbisidagroups' => $herbisidaData,
        ]);
    }

    /**
     * Show edit form for RKH
     */
    public function edit($rkhno)
    {
        $companycode = Session::get('companycode');
        
        $rkhHeader = $this->getRkhHeaderForEdit($companycode, $rkhno);
        
        if (!$rkhHeader) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Data RKH tidak ditemukan');
        }

        // Security check - prevent editing approved RKH
        if ($this->isRkhApproved($rkhHeader)) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'RKH tidak dapat diedit karena sudah disetujui');
        }
        
        $rkhDetails = $this->getRkhDetailsForEdit($companycode, $rkhno);
        $formData = $this->loadEditFormData($companycode, $rkhHeader->rkhdate);
        
        return view('input.rencanakerjaharian.edit', array_merge([
            'title' => 'Edit RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhHeader' => $rkhHeader,
            'rkhDetails' => $rkhDetails,
            'existingWorkers' => $rkhDetails->groupBy('activitycode')->map(function($items) {
                $first = $items->first();
                return [
                    'activitycode' => $first->activitycode,
                    'activityname' => $first->activityname,
                    'jumlahlaki' => $first->jumlahlaki ?? 0,
                    'jumlahperempuan' => $first->jumlahperempuan ?? 0,
                    'jumlahtenagakerja' => $first->jumlahtenagakerja ?? 0
                ];
            })->values(),
            'oldInput' => old(),
        ], $formData));
    }

    /**
     * Update existing RKH record
     */
    public function update(Request $request, $rkhno)
    {   
        // Security check
        $rkhHeader = DB::table('rkhhdr')
            ->where('companycode', Session::get('companycode'))
            ->where('rkhno', $rkhno)
            ->first();
            
        if ($this->isRkhApproved($rkhHeader)) {
            return response()->json([
                'success' => false,
                'message' => 'RKH tidak dapat diubah karena sudah disetujui'
            ], 403);
        }

        // Filter and validate input
        $filteredRows = $this->filterValidRows($request->input('rows', []));
        $request->merge(['rows' => $filteredRows]);

        try {
            $this->validateRkhRequest($request);

            DB::transaction(function () use ($request, $rkhno) {
                $this->updateRkhRecord($request, $rkhno);
            });

            return $this->handleUpdateResponse($request, $rkhno, true);

        } catch (\Exception $e) {
            \Log::error("Update RKH Error: " . $e->getMessage());
            return $this->handleUpdateResponse($request, $rkhno, false, $e->getMessage());
        }
    }

    /**
     * Delete RKH record
     */
    public function destroy($rkhno)
    {   
        // Security check
        $rkhHeader = DB::table('rkhhdr')
            ->where('companycode', Session::get('companycode'))
            ->where('rkhno', $rkhno)
            ->first();
        
        if ($this->isRkhApproved($rkhHeader)) {
            return response()->json([
                'success' => false, 
                'message' => 'RKH tidak dapat dihapus karena sudah disetujui'
            ], 403);
        }

        $companycode = Session::get('companycode');
        
        try {
            DB::beginTransaction();
            
            // Delete from rkhlstworker first (foreign key constraint)
            DB::table('rkhlstworker')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            // Then delete from rkhlst
            DB::table('rkhlst')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            // Finally delete from rkhhdr
            $deleted = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $rkhno)
                ->delete();
            
            if ($deleted) {
                DB::commit();
                return response()->json([
                    'success' => true, 
                    'message' => 'RKH dan semua data terkait berhasil dihapus'
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false, 
                    'message' => 'RKH tidak ditemukan'
                ], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Delete RKH Error: " . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus RKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update RKH status
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'status' => 'required|string'
        ]);

        try {
            $companycode = Session::get('companycode');
            $rkhno = $request->rkhno;
            
            // Validate LKH completion before allowing status update
            if ($request->status === 'Completed') {
                $progressStatus = $this->getRkhProgressStatus($rkhno, $companycode);
                
                if (!$progressStatus['can_complete']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat menandai RKH sebagai Completed. ' . $progressStatus['progress'] . '. Semua LKH harus diapprove terlebih dahulu.'
                    ], 400);
                }
            }
            
            $updateData = [
                'status' => $request->status,
                'updateby' => Auth::user()->userid,
                'updatedat' => now()
            ];
            
            $updated = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $request->rkhno)
                ->update($updateData);

            if ($updated) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Status RKH berhasil diupdate menjadi ' . $request->status
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan'], 404);
            }

        } catch (\Exception $e) {
            \Log::error("Error updating RKH status: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getRkhProgressStatus($rkhno, $companycode) 
    {
        $lkhData = DB::table('lkhhdr')
            ->where('rkhno', $rkhno)
            ->where('companycode', $companycode)
            ->get();
        
        if ($lkhData->isEmpty()) {
            return [
                'status' => 'no_lkh',
                'progress' => 'No LKH Created',
                'can_complete' => false,
                'color' => 'gray'
            ];
        }
        
        $totalLkh = $lkhData->count();
        $completedLkh = $lkhData->where('status', 'APPROVED')->count();
        
        if ($completedLkh === $totalLkh) {
            return [
                'status' => 'complete',
                'progress' => 'All Complete',
                'can_complete' => true,
                'color' => 'green'
            ];
        } else {
            return [
                'status' => 'in_progress', 
                'progress' => "LKH In Progress ({$completedLkh}/{$totalLkh})",
                'can_complete' => false,
                'color' => 'yellow'
            ];
        }
    }

    /**
     * Group rows by activity and extract worker data
     * NEW METHOD - Worker extraction logic
     */
    private function groupRowsByActivity($workers)
    {
        $activities = [];

        foreach ($workers as $activityCode => $worker) {
            // $workers already keyed by activitycode from frontend
            // Format: workers[activityCode] = {laki, perempuan, total, activityname}
            
            $laki = (int) ($worker['laki'] ?? 0);
            $perempuan = (int) ($worker['perempuan'] ?? 0);
            
            $activities[$activityCode] = [
                'activitycode' => $activityCode,
                'jumlahlaki' => $laki,
                'jumlahperempuan' => $perempuan,
                'jumlahtenagakerja' => $laki + $perempuan,
            ];
        }

        return $activities;
    }

    /**
     * Create worker assignments in rkhlstworker table
     * NEW METHOD - Worker insertion
     */
    private function createWorkerAssignments($activitiesWorkers, $companycode, $rkhno)
    {
        $workerRecords = [];
        
        foreach ($activitiesWorkers as $worker) {
            $workerRecords[] = [
                'companycode' => $companycode,
                'rkhno' => $rkhno,
                'activitycode' => $worker['activitycode'],
                'jumlahlaki' => $worker['jumlahlaki'],
                'jumlahperempuan' => $worker['jumlahperempuan'],
                'jumlahtenagakerja' => $worker['jumlahtenagakerja'],
                'createdat' => now()
            ];
        }
        
        if (!empty($workerRecords)) {
            DB::table('rkhlstworker')->insert($workerRecords);
        }
    }

    /**
     * Update worker assignments in rkhlstworker table
     * NEW METHOD - Worker update logic
     */
    private function updateWorkerAssignments($activitiesWorkers, $companycode, $rkhno)
    {
        // Delete existing worker records for this RKH
        DB::table('rkhlstworker')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->delete();
        
        // FIX: Only re-insert if there are workers
        if (!empty($activitiesWorkers)) {
            $this->createWorkerAssignments($activitiesWorkers, $companycode, $rkhno);
        }
    }

    /**
     * Get worker data for specific RKH
     * NEW METHOD - Worker retrieval
     */
    private function getRkhWorkers($companycode, $rkhno)
    {
        return DB::table('rkhlstworker')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->get()
            ->keyBy('activitycode'); // Key by activity for easy lookup
    }

    // =====================================
    // SECTION 2: RKH APPROVAL MANAGEMENT
    // =====================================

    /**
     * Get pending RKH approvals for current user
     */
    public function getPendingApprovals(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            if (!$this->validateUserForApproval($currentUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            $pendingRKH = $this->buildPendingApprovalsQuery($companycode, $currentUser)->get();
            $formattedData = $this->formatPendingApprovalsData($pendingRKH);

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'user_info' => $this->getUserInfo($currentUser)
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting pending approvals: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process RKH approval (approve/decline)
     */
    public function processApproval(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $result = $this->executeApprovalProcess($request);
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error processing approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get RKH approval detail
     */
    public function getApprovalDetail($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $rkh = $this->getRkhApprovalDetail($companycode, $rkhno);

            if (!$rkh) {
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan']);
            }

            $formattedData = $this->formatApprovalDetailData($rkh);
            return response()->json(['success' => true, 'data' => $formattedData]);

        } catch (\Exception $e) {
            \Log::error("Error getting approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================
    // SECTION 3: LKH MANAGEMENT
    // =====================================

    /**
     * Get LKH data for specific RKH
     * FIXED: Updated to use new table structure
     */
    public function getLKHData($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $lkhList = $this->buildLkhDataQuery($companycode, $rkhno)->get();

            $formattedData = $this->formatLkhData($lkhList, $companycode);
            $generateInfo = $this->getLkhGenerateInfo($companycode, $rkhno, $lkhList);

            return response()->json([
                'success' => true,
                'lkh_data' => $formattedData->values()->toArray(),
                'rkhno' => $rkhno,
                'can_generate_lkh' => $generateInfo['can_generate'],
                'generate_message' => $generateInfo['message'],
                'total_lkh' => $lkhList->count()
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH data for RKH {$rkhno}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data LKH: ' . $e->getMessage(),
                'lkh_data' => [],
                'total_lkh' => 0
            ], 500);
        }
    }

    /**
     * Show LKH report
     * UPDATED: Support both normal and panen activities
     */
    public function showLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $lkhData = $this->getLkhDataForShow($companycode, $lkhno);

            if (!$lkhData) {
                return redirect()->route('input.rencanakerjaharian.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }

            // Detect if this is panen activity
            $panenActivities = ['4.3.3', '4.4.3', '4.5.2'];
            $isPanenActivity = in_array($lkhData->activitycode, $panenActivities);

            if ($isPanenActivity) {
                // Load panen data from NEW UNIFIED STRUCTURE
                $lkhPanenDetails = $this->getLkhPanenDetailsForShow($companycode, $lkhno);
                $approvals = $this->getLkhApprovalsData($lkhData);

                return view('input.rencanakerjaharian.lkh-report-panen', [
                    'title' => 'Laporan Kegiatan Harian (LKH) - Panen',
                    'navbar' => 'Input',
                    'nav' => 'Rencana Kerja Harian',
                    'lkhData' => $lkhData,
                    'lkhPanenDetails' => $lkhPanenDetails,
                    'approvals' => $approvals
                ]);
            } else {
                // Load normal activity data (existing code)
                $lkhPlotDetails = $this->getLkhPlotDetailsForShow($companycode, $lkhno);
                $lkhWorkerDetails = $this->getLkhWorkerDetailsForShow($companycode, $lkhno);
                $lkhMaterialDetails = $this->getLkhMaterialDetailsForShow($companycode, $lkhno);
                $approvals = $this->getLkhApprovalsData($lkhData);

                return view('input.rencanakerjaharian.lkh-report', [
                    'title' => 'Laporan Kegiatan Harian (LKH)',
                    'navbar' => 'Input',
                    'nav' => 'Rencana Kerja Harian',
                    'lkhData' => $lkhData,
                    'lkhPlotDetails' => $lkhPlotDetails,
                    'lkhWorkerDetails' => $lkhWorkerDetails,
                    'lkhMaterialDetails' => $lkhMaterialDetails,
                    'approvals' => $approvals
                ]);
            }

        } catch (\Exception $e) {
            \Log::error("Error showing LKH: " . $e->getMessage());
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan LKH: ' . $e->getMessage());
        }
    }

    /**
     * Get LKH panen details for show
     * @param string $companycode
     * @param string $lkhno
     * @return \Illuminate\Support\Collection
     */
    private function getLkhPanenDetailsForShow($companycode, $lkhno)
    {
        $lkhDate = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->value('lkhdate');
        
        return DB::table('lkhdetailplot as ldp')
            ->leftJoin('batch as b', 'ldp.batchno', '=', 'b.batchno')
            ->leftJoin('subkontraktor as sk', function($join) use ($companycode) {
                $join->on('ldp.subkontraktorid', '=', 'sk.id')
                    ->where('sk.companycode', '=', $companycode);
            })
            ->where('ldp.companycode', $companycode)
            ->where('ldp.lkhno', $lkhno)
            ->select([
                'ldp.plot',
                'ldp.blok',
                'ldp.batchno',
                'ldp.kodestatus',
                'ldp.luasrkh',
                'ldp.luashasil',
                'ldp.createdat',
                'ldp.subkontraktorid',
                'ldp.fieldbalancerit',
                'ldp.fieldbalanceton',
                'sk.namasubkontraktor as subkontraktor_nama',
                'b.batcharea',
                'b.tanggalpanenpc',
                'b.tanggalpanenrc1',
                'b.tanggalpanenrc2',
                'b.tanggalpanenrc3',
                
                // STC = Luas batch - Total sudah dipanen sampai KEMARIN
                // (bukan termasuk hari ini, karena HC hari ini belum dikurangi)
                DB::raw("(
                    COALESCE(b.batcharea, 0) - 
                    COALESCE((
                        SELECT SUM(ldp2.luashasil)
                        FROM lkhdetailplot ldp2
                        JOIN lkhhdr lh2 ON ldp2.lkhno = lh2.lkhno AND ldp2.companycode = lh2.companycode
                        WHERE ldp2.companycode = ldp.companycode
                        AND ldp2.batchno = ldp.batchno
                        AND ldp2.kodestatus = ldp.kodestatus
                        AND lh2.lkhdate < '{$lkhDate}'
                    ), 0)
                ) as stc"),
                
                // HC = Hasil panen hari ini
                DB::raw('COALESCE(ldp.luashasil, 0) as hc'),
                
                // BC = STC - HC (hitung manual, JANGAN pakai luassisa)
                DB::raw("(
                    (
                        COALESCE(b.batcharea, 0) - 
                        COALESCE((
                            SELECT SUM(ldp2.luashasil)
                            FROM lkhdetailplot ldp2
                            JOIN lkhhdr lh2 ON ldp2.lkhno = lh2.lkhno AND ldp2.companycode = lh2.companycode
                            WHERE ldp2.companycode = ldp.companycode
                            AND ldp2.batchno = ldp.batchno
                            AND ldp2.kodestatus = ldp.kodestatus
                            AND lh2.lkhdate < '{$lkhDate}'
                        ), 0)
                    ) - COALESCE(ldp.luashasil, 0)
                ) as bc"),
                
                // Hari tebang calculation
                DB::raw("CASE 
                    WHEN ldp.kodestatus = 'PC' AND b.tanggalpanenpc IS NOT NULL 
                        THEN DATEDIFF('{$lkhDate}', b.tanggalpanenpc) + 1
                    WHEN ldp.kodestatus = 'RC1' AND b.tanggalpanenrc1 IS NOT NULL 
                        THEN DATEDIFF('{$lkhDate}', b.tanggalpanenrc1) + 1
                    WHEN ldp.kodestatus = 'RC2' AND b.tanggalpanenrc2 IS NOT NULL 
                        THEN DATEDIFF('{$lkhDate}', b.tanggalpanenrc2) + 1
                    WHEN ldp.kodestatus = 'RC3' AND b.tanggalpanenrc3 IS NOT NULL 
                        THEN DATEDIFF('{$lkhDate}', b.tanggalpanenrc3) + 1
                    ELSE NULL
                END as haritebang")
            ])
            ->orderBy('ldp.blok')
            ->orderBy('ldp.plot')
            ->get();
    }

    /**
     * Show LKH edit form
     * FIXED: Updated to use new table structure
     */
    public function editLKH($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $lkhData = $this->getLkhDataForEdit($companycode, $lkhno);

            if (!$lkhData) {
                return redirect()->route('input.rencanakerjaharian.index')
                    ->with('error', 'Data LKH tidak ditemukan');
            }

            // Security check
            if ($lkhData->issubmit) {
                return redirect()->route('input.rencanakerjaharian.index')
                    ->with('error', 'LKH sudah disubmit dan tidak dapat diedit');
            }

            // FIXED: Get details from new tables
            $lkhPlotDetails = $this->getLkhPlotDetailsForEdit($companycode, $lkhno);
            $lkhWorkerDetails = $this->getLkhWorkerDetailsForEdit($companycode, $lkhno);
            $lkhMaterialDetails = $this->getLkhMaterialDetailsForEdit($companycode, $lkhno);
            $formData = $this->loadLkhEditFormData($companycode);

            return view('input.rencanakerjaharian.edit-lkh', array_merge([
                'title' => 'Edit LKH',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
                'lkhData' => $lkhData,
                'lkhPlotDetails' => $lkhPlotDetails,
                'lkhWorkerDetails' => $lkhWorkerDetails,
                'lkhMaterialDetails' => $lkhMaterialDetails,
            ], $formData));

        } catch (\Exception $e) {
            \Log::error("Error editing LKH: " . $e->getMessage());
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat membuka edit LKH: ' . $e->getMessage());
        }
    }

    /**
     * Update LKH record
     * FIXED: Updated to use new table structure
     */
    public function updateLKH(Request $request, $lkhno)
    {
        try {
            $this->validateLkhUpdateRequest($request);

            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            DB::beginTransaction();

            // Security checks
            $lkhData = $this->validateLkhForUpdate($companycode, $lkhno);
            
            // Update LKH using new structure
            $this->updateLkhRecord($request, $lkhno, $lkhData, $currentUser);

            DB::commit();

            return redirect()->route('input.rencanakerjaharian.showLKH', $lkhno)
                ->with('success', 'LKH berhasil diupdate');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating LKH: " . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengupdate LKH: ' . $e->getMessage());
        }
    }

    /**
     * Submit LKH for approval
     */
    public function submitLKH(Request $request)
    {
        $request->validate(['lkhno' => 'required|string']);

        try {
            $result = $this->executeLkhSubmission($request);
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error submitting LKH: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate LKH manually
     */
    public function manualGenerateLkh(Request $request, $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $rkh = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();
                
            if (!$rkh) {
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan']);
            }

            $lkhGenerator = new LkhGeneratorService();
            $result = $lkhGenerator->generateLkhFromRkh($rkhno);

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Manual generate LKH error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    // =====================================
    // SECTION 4: LKH APPROVAL MANAGEMENT
    // =====================================

    /**
     * Get pending LKH approvals
     */
    public function getPendingLKHApprovals(Request $request)
    {
        try {
            $companycode = Session::get('companycode');
            $currentUser = Auth::user();
            
            if (!$this->validateUserForApproval($currentUser)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak memiliki jabatan yang valid'
                ]);
            }

            $pendingLKH = $this->buildPendingLkhApprovalsQuery($companycode, $currentUser)->get();
            $formattedData = $this->formatPendingLkhApprovalsData($pendingLKH, $companycode);

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'user_info' => $this->getUserInfo($currentUser)
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting pending LKH approvals: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get LKH approval detail
     */
    public function getLkhApprovalDetail($lkhno)
    {
        try {
            $companycode = Session::get('companycode');
            $lkh = $this->getLkhApprovalDetailData($companycode, $lkhno);

            if (!$lkh) {
                return response()->json(['success' => false, 'message' => 'LKH tidak ditemukan']);
            }

            $formattedData = $this->formatLkhApprovalDetailData($lkh);
            return response()->json(['success' => true, 'data' => $formattedData]);

        } catch (\Exception $e) {
            \Log::error("Error getting LKH approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process LKH approval
     */
    public function processLKHApproval(Request $request)
    {
        $request->validate([
            'lkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        try {
            $result = $this->executeLkhApprovalProcess($request);
            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error processing LKH approval: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses approval LKH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate LKH Rekap report
     */
    public function generateRekapLKH(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $url = route('input.rencanakerjaharian.rekap-lkh-report', ['date' => $request->date]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan Rekap LKH...',
            'redirect_url' => $url
        ]);
    }

    /**
     * Show LKH Rekap report view
     */
    public function showRekapLKHReport(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('input.rencanakerjaharian.lkh-rekap', ['date' => $date]);
    }

    /**
     * Get LKH Rekap data - UPDATED VERSION
     */
    public function getLKHRekapData(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $companycode = Session::get('companycode');

        try {
            $companyInfo = $this->getCompanyInfo($companycode);
            $lkhNumbers = $this->getLkhNumbersForDate($companycode, $date);
            
            $rekapData = [
                'pengolahan' => $this->getPengolahanData($companycode, $date),
                'perawatan' => $this->getPerawatanData($companycode, $date) // UPDATED: Combined manual & mekanis
            ];

            return response()->json([
                'success' => true,
                'company_info' => $companyInfo,
                'pengolahan' => $rekapData['pengolahan'],
                'perawatan' => $rekapData['perawatan'], // UPDATED: Single perawatan section
                'lkh_numbers' => $lkhNumbers,
                'date' => $date,
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'debug' => [
                    'pengolahan_count' => count($rekapData['pengolahan']),
                    'perawatan_pc_count' => count($rekapData['perawatan']['pc'] ?? []),
                    'perawatan_rc_count' => count($rekapData['perawatan']['rc'] ?? []),
                    'total_lkh' => count($lkhNumbers)
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("LKH Rekap Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data LKH Rekap: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getPerawatanData($companycode, $date)
    {
        $perawatanData = DB::table('lkhhdr as h')
            ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                $join->on('h.lkhno', '=', 'ldp.lkhno')
                    ->where('ldp.companycode', '=', $companycode);
            })
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('rkhlst as rls', function($join) use ($companycode) {
                $join->on('h.rkhno', '=', 'rls.rkhno')
                    ->on('h.activitycode', '=', 'rls.activitycode')
                    ->on('ldp.plot', '=', 'rls.plot')
                    ->where('rls.companycode', '=', $companycode);
            })
            ->leftJoin('tenagakerja as tk', function($join) use ($companycode) {
                $join->on('rls.operatorid', '=', 'tk.tenagakerjaid')
                    ->where('tk.companycode', '=', $companycode)
                    ->where('tk.jenistenagakerja', '=', 3); // Only operators
            })
            ->leftJoin('plot as p', function($join) use ($companycode) {
                $join->on('ldp.plot', '=', 'p.plot')
                    ->where('p.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->where('a.activitygroup', 'III') // All Activity V using activitygroup
            ->select([
                'h.lkhno',
                'h.activitycode',
                'h.totalworkers',
                'ldp.luashasil as totalhasil',
                'h.totalupahall',
                'a.activityname',
                'ldp.plot',
                'p.luasarea',
                'tk.nama as operator_nama',
                'rls.usingvehicle'
            ])
            ->orderBy('h.activitycode')
            ->orderBy('h.lkhno')
            ->get();
        
        // Group by PC/RC based on activity code pattern
        $result = ['pc' => [], 'rc' => []];
        
        foreach ($perawatanData as $record) {
            // Determine PC or RC based on activity code pattern (without romawi prefix)
            $type = 'pc'; // default
            if (strpos($record->activitycode, 'III.2.') !== false) {
                $type = 'rc';
            } elseif (strpos($record->activitycode, 'III.1.') !== false) {
                $type = 'pc';
            }
            
            $activityCode = $record->activitycode;
            
            if (!isset($result[$type][$activityCode])) {
                $result[$type][$activityCode] = [];
            }
            
            $result[$type][$activityCode][] = (object)[
                'lkhno' => $record->lkhno,
                'activitycode' => $record->activitycode,
                'activityname' => $record->activityname,
                'totalworkers' => $record->totalworkers,
                'totalhasil' => $record->totalhasil,
                'totalupahall' => $record->totalupahall,
                'plot' => $record->plot,
                'luasarea' => $record->luasarea ?: 0,
                'operator_nama' => $record->usingvehicle && $record->operator_nama ? $record->operator_nama : '-',
            ];
        }
        
        return $result;
    }

    // =====================================
    // PRIVATE HELPER METHODS FOR LKH (SECTIONS 3-4)
    // =====================================

    /**
     * Build LKH data query
     * FIXED: Updated column references
     */
    private function buildLkhDataQuery($companycode, $rkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->where('h.rkhno', $rkhno)
            ->select([
                'h.lkhno',
                'h.activitycode',
                'a.activityname',
                'h.jenistenagakerja',
                'h.lkhdate',
                'h.totalworkers',
                'h.totalhasil',
                'h.totalsisa',
                'h.totalupahall',
                'h.createdat',
                'h.status',
                'h.issubmit',
                'h.submitby',
                'h.submitat',
                'h.jumlahapproval',
                'h.approval1flag',
                'h.approval2flag',
                'h.approval3flag',
                'app.jumlahapproval as required_approvals'
            ])
            ->orderBy('h.lkhno');
    }

    /**
     * Format LKH data for response
     */
    private function formatLkhData($lkhList, $companycode)
    {
        return $lkhList->map(function($lkh) use ($companycode) {
            $approvalStatus = $this->calculateLKHApprovalStatus($lkh);
            
            // FIXED: Logic yang lebih sederhana dan benar
            $canEdit = !$lkh->issubmit;  // Bisa edit kalau belum di-submit
            $canSubmit = !$lkh->issubmit && $lkh->status === 'DRAFT';  // Bisa submit kalau belum di-submit dan status DRAFT

            // FIXED: Get plots for this LKH from lkhdetailplot table - HANYA PLOT
            $plots = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->select('blok', 'plot', 'luasrkh')
                ->get()
                ->map(function($item) {
                    return $item->plot; // HANYA plot saja, format: B002
                })
                ->unique()
                ->join(', ');

            // FIXED: Get workers count from lkhdetailworker table
            $workersAssigned = LkhDetailWorker::where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->count();

            // FIXED: Get material count from lkhdetailmaterial table
            $materialCount = LkhDetailMaterial::where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->count();

            return [
                'lkhno' => $lkh->lkhno,
                'activitycode' => $lkh->activitycode,
                'activityname' => $lkh->activityname ?? 'Unknown Activity',
                'plots' => $plots ?: 'No plots assigned',
                'jenistenagakerja' => $lkh->jenistenagakerja, // Add this for jenis detection
                'jenis_tenaga' => $lkh->jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                'status' => $lkh->status ?? 'EMPTY',
                'approval_status' => $approvalStatus,
                'workers_assigned' => $workersAssigned,
                'material_count' => $materialCount,
                'totalhasil' => $lkh->totalhasil,
                'totalsisa' => $lkh->totalsisa,
                'totalupah' => $lkh->totalupahall ?? 0,
                'issubmit' => (bool) $lkh->issubmit,
                'date_formatted' => $lkh->lkhdate ? Carbon::parse($lkh->lkhdate)->format('d/m/Y') : '-',
                'created_at' => $lkh->createdat ? Carbon::parse($lkh->createdat)->format('d/m/Y H:i') : '-',
                'submit_info' => $lkh->submitat ? 'Submitted at ' . Carbon::parse($lkh->submitat)->format('d/m/Y H:i') : null,
                'can_edit' => $canEdit,
                'can_submit' => $canSubmit,  // FIXED: Logic yang benar
                'view_url' => route('input.rencanakerjaharian.showLKH', $lkh->lkhno),
                'edit_url' => route('input.rencanakerjaharian.editLKH', $lkh->lkhno)
            ];
        });
    }

    /**
     * Get LKH generate info
     */
    private function getLkhGenerateInfo($companycode, $rkhno, $lkhList)
    {
        $canGenerateLkh = false;
        $generateMessage = '';
        
        $rkhData = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();
            
        if ($rkhData) {
            if ($this->isRkhFullyApproved($rkhData)) {
                if ($lkhList->isEmpty()) {
                    $canGenerateLkh = true;
                    $generateMessage = 'RKH sudah approved, LKH bisa di-generate';
                } else {
                    $generateMessage = 'LKH sudah pernah di-generate';
                }
            } else {
                $generateMessage = 'RKH belum fully approved';
            }
        }

        return [
            'can_generate' => $canGenerateLkh,
            'message' => $generateMessage
        ];
    }

    /**
     * Calculate LKH approval status
     * FIXED: Handle status SUBMITTED
     */
    private function calculateLKHApprovalStatus($lkh)
    {
        if (!$lkh->issubmit) {
            return 'Not Yet Submitted';
        }

        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return 'No Approval Required';
        }

        if ($this->isLKHFullyApproved($lkh)) {
            return 'Approved';
        }

        if ($lkh->approval1flag === '0' || $lkh->approval2flag === '0' || $lkh->approval3flag === '0') {
            return 'Declined';
        }

        $completed = 0;
        if ($lkh->approval1flag === '1') $completed++;
        if ($lkh->approval2flag === '1') $completed++;
        if ($lkh->approval3flag === '1') $completed++;

        return "Waiting ({$completed} / {$lkh->jumlahapproval})";
    }

    /**
     * Check if LKH is fully approved
     */
    private function isLKHFullyApproved($lkh)
    {
        if (!$lkh->jumlahapproval || $lkh->jumlahapproval == 0) {
            return true;
        }

        switch ($lkh->jumlahapproval) {
            case 1:
                return $lkh->approval1flag === '1';
            case 2:
                return $lkh->approval1flag === '1' && $lkh->approval2flag === '1';
            case 3:
                return $lkh->approval1flag === '1' && 
                       $lkh->approval2flag === '1' && 
                       $lkh->approval3flag === '1';
            default:
                return false;
        }
    }

    /**
     * Get LKH data for show
     */
    private function getLkhDataForShow($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandornama',
                'a.activityname',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3'
            ])
            ->first();
    }

    /**
     * Get LKH plot details for show
     * NEW METHOD: Use lkhdetailplot table
     */
    private function getLkhPlotDetailsForShow($companycode, $lkhno)
    {
        return LkhDetailPlot::where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->orderBy('blok')
            ->orderBy('plot')
            ->get();
    }

    /**
     * Get LKH worker details for show
     * NEW METHOD: Use lkhdetailworker table
     */
    private function getLkhWorkerDetailsForShow($companycode, $lkhno)
    {
        return LkhDetailWorker::where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->with('tenagakerja')
            ->orderBy('tenagakerjaurutan')
            ->get();
    }

    /** 
     * Get LKH material details for show
     * UPDATED: Add join to herbisida table to get measure (satuan) and itemname, FIXED: Include plot field
     */
    private function getLkhMaterialDetailsForShow($companycode, $lkhno)
    {
        return DB::table('lkhdetailmaterial as ldm')
            ->leftJoin('herbisida as h', function($join) use ($companycode) {
                $join->on('ldm.itemcode', '=', 'h.itemcode')
                     ->where('h.companycode', '=', $companycode);
            })
            ->where('ldm.companycode', $companycode)
            ->where('ldm.lkhno', $lkhno)
            ->select([
                'ldm.id',
                'ldm.plot', // FIXED: Add plot field
                'ldm.itemcode',
                'ldm.qtyditerima',
                'ldm.qtysisa', 
                'ldm.qtydigunakan',
                'ldm.keterangan',
                'ldm.inputby',
                'ldm.createdat',
                'ldm.updatedat',
                'h.itemname',
                'h.measure as satuan'
            ])
            ->orderBy('ldm.plot') // FIXED: Order by plot for better display
            ->orderBy('ldm.itemcode')
            ->get()
            ->map(function($material) {
                return (object)[
                    'id' => $material->id,
                    'plot' => (string)($material->plot ?? '-'), // FIXED: Include plot in mapping
                    'itemcode' => (string)($material->itemcode ?? ''),
                    'itemname' => (string)($material->itemname ?? 'Unknown Item'),
                    'qtyditerima' => floatval($material->qtyditerima ?? 0),
                    'qtysisa' => floatval($material->qtysisa ?? 0),
                    'qtydigunakan' => floatval($material->qtydigunakan ?? 0),
                    'satuan' => (string)($material->satuan ?? '-'),
                    'keterangan' => (string)($material->keterangan ?? ''),
                    'inputby' => (string)($material->inputby ?? ''),
                    'createdat' => $material->createdat,
                    'updatedat' => $material->updatedat,
                ];
            });
    }

    /**
     * Get LKH material details for edit
     * UPDATED: Add join to herbisida table to get measure (satuan) and itemname, FIXED: Include plot field and consistent ordering
     */
    private function getLkhMaterialDetailsForEdit($companycode, $lkhno)
    {
        return DB::table('lkhdetailmaterial as ldm')
            ->leftJoin('herbisida as h', function($join) use ($companycode) {
                $join->on('ldm.itemcode', '=', 'h.itemcode')
                    ->where('h.companycode', '=', $companycode);
            })
            ->where('ldm.companycode', $companycode)
            ->where('ldm.lkhno', $lkhno)
            ->select([
                'ldm.id',
                'ldm.plot', // FIXED: Include plot field for consistency
                'ldm.itemcode',
                'ldm.qtyditerima',
                'ldm.qtysisa', 
                'ldm.qtydigunakan',
                'ldm.keterangan',
                'ldm.inputby',
                'ldm.createdat',
                'ldm.updatedat',
                'h.itemname',
                'h.measure as satuan'
            ])
            ->orderBy('ldm.plot') // FIXED: Order by plot for better display
            ->orderBy('ldm.itemcode')
            ->get();
    }

    /**
     * Get LKH approvals data
     */
    private function getLkhApprovalsData($lkhData)
    {
        $approvals = new \stdClass();
        if ($lkhData->jumlahapproval > 0) {
            $jabatanData = DB::table('jabatan')
                ->whereIn('idjabatan', array_filter([
                    $lkhData->idjabatanapproval1,
                    $lkhData->idjabatanapproval2,
                    $lkhData->idjabatanapproval3
                ]))
                ->pluck('namajabatan', 'idjabatan');

            $approvals->jabatan1name = $jabatanData[$lkhData->idjabatanapproval1] ?? null;
            $approvals->jabatan2name = $jabatanData[$lkhData->idjabatanapproval2] ?? null;
            $approvals->jabatan3name = $jabatanData[$lkhData->idjabatanapproval3] ?? null;
            $approvals->jabatan4name = null;
        }

        return $approvals;
    }

    /**
     * Get LKH data for edit
     */
    private function getLkhDataForEdit($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandornama',
                'a.activityname'
            ])
            ->first();
    }

    /**
     * Get LKH plot details for edit
     * NEW METHOD: Use lkhdetailplot table
     */
    private function getLkhPlotDetailsForEdit($companycode, $lkhno)
    {
        return LkhDetailPlot::where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->orderBy('blok')
            ->orderBy('plot')
            ->get();
    }

    /**
     * Get LKH worker details for edit
     * NEW METHOD: Use lkhdetailworker table
     */
    private function getLkhWorkerDetailsForEdit($companycode, $lkhno)
    {
        return LkhDetailWorker::where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->with('tenagakerja')
            ->orderBy('tenagakerjaurutan')
            ->get();
    }


    /**
     * Load LKH edit form data
     */
    private function loadLkhEditFormData($companycode)
    {
        return [
            'tenagaKerja' => DB::table('tenagakerja')
                ->where('companycode', $companycode)
                ->where('isactive', 1)
                ->select(['tenagakerjaid', 'nama', 'nik', 'jenistenagakerja'])
                ->orderBy('nama')
                ->get(),
            'bloks' => Blok::orderBy('blok')->get(),
            'masterlist' => Masterlist::orderBy('companycode')->orderBy('plot')->get(),
            'plots' => DB::table('plot')->where('companycode', $companycode)->get(),
            'bloksData' => Blok::orderBy('blok')->get(),
            'masterlistData' => Masterlist::orderBy('companycode')->orderBy('plot')->get(),
            'plotsData' => DB::table('plot')->where('companycode', $companycode)->get()
        ];
    }

    /**
     * Validate LKH update request
     * FIXED: Updated for new structure
     */
    private function validateLkhUpdateRequest($request)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:500',
            'plots' => 'nullable|array',
            'plots.*.blok' => 'required_with:plots|string',
            'plots.*.plot' => 'required_with:plots|string',
            'plots.*.luasrkh' => 'required_with:plots|numeric|min:0',
            'plots.*.luashasil' => 'required_with:plots|numeric|min:0',
            'plots.*.luassisa' => 'required_with:plots|numeric|min:0',
            'workers' => 'nullable|array',
            'workers.*.tenagakerjaid' => 'required_with:workers|string',
            'workers.*.jammasuk' => 'nullable|date_format:H:i',
            'workers.*.jamselesai' => 'nullable|date_format:H:i',
            'workers.*.totaljamkerja' => 'nullable|numeric|min:0',
            'workers.*.overtimehours' => 'nullable|numeric|min:0',
            'workers.*.premi' => 'nullable|numeric|min:0',
            'workers.*.upahharian' => 'nullable|numeric|min:0',
            'workers.*.upahborongan' => 'nullable|numeric|min:0',
            'workers.*.totalupah' => 'nullable|numeric|min:0',
            'materials' => 'nullable|array',
            'materials.*.itemcode' => 'required_with:materials|string',
            'materials.*.qtyditerima' => 'required_with:materials|numeric|min:0',
            'materials.*.qtysisa' => 'required_with:materials|numeric|min:0',
        ]);
    }

    /**
     * Validate LKH for update
     */
    private function validateLkhForUpdate($companycode, $lkhno)
    {
        $lkhData = DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->first();

        if (!$lkhData) {
            throw new \Exception('LKH tidak ditemukan');
        }

        if ($lkhData->issubmit) {
            throw new \Exception('LKH sudah disubmit dan tidak dapat diedit');
        }

        return $lkhData;
    }

    /**
     * Update LKH record using new table structure
     * NEW METHOD: Handle 3 separate detail tables
     */
    private function updateLkhRecord($request, $lkhno, $lkhData, $currentUser)
    {
        $companycode = Session::get('companycode');
        
        // Calculate totals from new data structure
        $totalWorkers = count($request->workers ?? []);
        $totalPlots = count($request->plots ?? []);
        $totalMaterials = count($request->materials ?? []);
        
        // Calculate total hasil and sisa from plots
        $totalHasil = collect($request->plots ?? [])->sum('luashasil');
        $totalSisa = collect($request->plots ?? [])->sum('luassisa');
        
        // Calculate total upah from workers
        $totalUpah = $this->calculateTotalUpah($request->workers ?? [], $lkhData);

        // Update header
        DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->where('lkhno', $lkhno)
            ->update([
                'totalworkers' => $totalWorkers,
                'totalhasil' => $totalHasil,
                'totalsisa' => $totalSisa,
                'totalupahall' => $totalUpah,
                'keterangan' => $request->keterangan,
                'updateby' => $currentUser->userid,
                'updatedat' => now()
            ]);

        // Update plot details
        if (!empty($request->plots)) {
            LkhDetailPlot::where('companycode', $companycode)->where('lkhno', $lkhno)->delete();
            $plotDetails = $this->buildLkhPlotDetails($request->plots, $lkhno, $companycode);
            foreach ($plotDetails as $plotDetail) {
                LkhDetailPlot::create($plotDetail);
            }
        }

        // Update worker details
        if (!empty($request->workers)) {
            LkhDetailWorker::where('companycode', $companycode)->where('lkhno', $lkhno)->delete();
            $workerDetails = $this->buildLkhWorkerDetails($request->workers, $lkhno, $lkhData, $companycode);
            foreach ($workerDetails as $workerDetail) {
                LkhDetailWorker::create($workerDetail);
            }
        }

        // Update material details
        if (!empty($request->materials)) {
            LkhDetailMaterial::where('companycode', $companycode)->where('lkhno', $lkhno)->delete();
            $materialDetails = $this->buildLkhMaterialDetails($request->materials, $lkhno, $companycode, $currentUser->userid);
            foreach ($materialDetails as $materialDetail) {
                LkhDetailMaterial::create($materialDetail);
            }
        }
    }

    /**
     * Calculate total upah for new structure
     * NEW METHOD
     */
    private function calculateTotalUpah($workers, $lkhData)
    {
        $totalUpah = 0;

        foreach ($workers as $worker) {
            if ($lkhData->jenistenagakerja == 1) {
                // Harian: upah harian + premi + overtime
                $upahHarian = $worker['upahharian'] ?? 0;
                $premi = $worker['premi'] ?? 0;
                $upahlembur = $worker['upahlembur'] ?? 0;
                $totalUpah += $upahHarian + $premi + $upahlembur;
            } else {
                // Borongan: upah borongan
                $upahBorongan = $worker['upahborongan'] ?? 0;
                $totalUpah += $upahBorongan;
            }
        }

        return $totalUpah;
    }

    /**
     * Build LKH plot detail records
     * NEW METHOD
     */
    private function buildLkhPlotDetails($plots, $lkhno, $companycode)
    {
        $details = [];
        foreach ($plots as $plot) {
            $details[] = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'blok' => $plot['blok'],
                'plot' => $plot['plot'],
                'luasrkh' => $plot['luasrkh'] ?? 0,
                'luashasil' => $plot['luashasil'] ?? 0,
                'luassisa' => $plot['luassisa'] ?? 0,
                'createdat' => now()
            ];
        }
        return $details;
    }

    /**
     * Build LKH worker detail records
     * NEW METHOD
     */
    private function buildLkhWorkerDetails($workers, $lkhno, $lkhData, $companycode)
    {
        $details = [];
        foreach ($workers as $index => $worker) {
            $detail = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'tenagakerjaid' => $worker['tenagakerjaid'],
                'tenagakerjaurutan' => $index + 1,
                'jammasuk' => $worker['jammasuk'] ?? null,
                'jamselesai' => $worker['jamselesai'] ?? null,
                'totaljamkerja' => $worker['totaljamkerja'] ?? 0,
                'overtimehours' => $worker['overtimehours'] ?? 0,
                'premi' => $worker['premi'] ?? 0,
                'upahharian' => $worker['upahharian'] ?? 0,
                'upahperjam' => $worker['upahperjam'] ?? 0,
                'upahlembur' => $worker['upahlembur'] ?? 0,
                'upahborongan' => $worker['upahborongan'] ?? 0,
                'totalupah' => $worker['totalupah'] ?? 0,
                'keterangan' => $worker['keterangan'] ?? null,
                'createdat' => now()
            ];

            $details[] = $detail;
        }

        return $details;
    }

    /**
     * Build LKH material detail records
     * NEW METHOD
     */
    private function buildLkhMaterialDetails($materials, $lkhno, $companycode, $inputby)
    {
        $details = [];
        foreach ($materials as $material) {
            $details[] = [
                'companycode' => $companycode,
                'lkhno' => $lkhno,
                'itemcode' => $material['itemcode'],
                'qtyditerima' => $material['qtyditerima'] ?? 0,
                'qtysisa' => $material['qtysisa'] ?? 0,
                'qtydigunakan' => ($material['qtyditerima'] ?? 0) - ($material['qtysisa'] ?? 0),
                'keterangan' => $material['keterangan'] ?? null,
                'inputby' => $inputby,
                'createdat' => now()
            ];
        }
        return $details;
    }

    /**
     * Execute LKH submission
     * FIXED: Update status ke SUBMITTED setelah submit
     */
    private function executeLkhSubmission($request)
    {
        $companycode = Session::get('companycode');
        $lkhno = $request->lkhno;
        $currentUser = Auth::user();

        DB::beginTransaction();

        $lkh = DB::table('lkhhdr as h')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select(['h.*', 'a.activitygroup'])
            ->first();

        if (!$lkh) {
            return ['success' => false, 'message' => 'LKH tidak ditemukan'];
        }

        if ($lkh->issubmit) {
            return ['success' => false, 'message' => 'LKH sudah disubmit sebelumnya'];
        }

        // FIXED: Validasi status harus DRAFT
        if ($lkh->status !== 'DRAFT') {
            return ['success' => false, 'message' => 'LKH harus berstatus DRAFT untuk bisa disubmit'];
        }

        $approvalSetting = null;
        if ($lkh->activitygroup) {
            $approvalSetting = DB::table('approval')
                ->where('companycode', $companycode)
                ->where('activitygroup', $lkh->activitygroup)
                ->first();
        }

        $updateData = [
            'issubmit' => 1,
            'submitby' => $currentUser->userid,
            'submitat' => now(),
            'status' => 'SUBMITTED',  // FIXED: Status jadi SUBMITTED setelah submit
            'updateby' => $currentUser->userid,
            'updatedat' => now()
        ];

        if ($approvalSetting) {
            $updateData = array_merge($updateData, [
                'jumlahapproval' => $approvalSetting->jumlahapproval,
                'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
            ]);
        }

        DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->update($updateData);

        DB::commit();

        return [
            'success' => true,
            'message' => 'LKH berhasil disubmit dan masuk ke proses approval'
        ];
    }

    /**
     * Build pending LKH approvals query
     */
    private function buildPendingLkhApprovalsQuery($companycode, $currentUser)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->where('h.issubmit', 1)
            ->where(function($query) use ($currentUser) {
                $query->where(function($q) use ($currentUser) {
                    $q->where('h.approval1idjabatan', $currentUser->idjabatan)->whereNull('h.approval1flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('h.approval2idjabatan', $currentUser->idjabatan)->where('h.approval1flag', '1')->whereNull('h.approval2flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('h.approval3idjabatan', $currentUser->idjabatan)->where('h.approval1flag', '1')->where('h.approval2flag', '1')->whereNull('h.approval3flag');
                });
            })
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                DB::raw('CASE 
                    WHEN h.approval1idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag IS NULL THEN 1
                    WHEN h.approval2idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag = "1" AND h.approval2flag IS NULL THEN 2
                    WHEN h.approval3idjabatan = '.$currentUser->idjabatan.' AND h.approval1flag = "1" AND h.approval2flag = "1" AND h.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('h.lkhdate', 'desc');
    }

    /**
     * Format pending LKH approvals data
     * FIXED: Updated to use new table structure
     */
    private function formatPendingLkhApprovalsData($pendingLKH, $companycode)
    {
        return $pendingLKH->map(function($lkh) use ($companycode) {
            // Get plots for this LKH
            $plots = LkhDetailPlot::where('companycode', $companycode)
                ->where('lkhno', $lkh->lkhno)
                ->select('blok', 'plot')
                ->get()
                ->map(function($item) {
                    return $item->blok . '-' . $item->plot;
                })
                ->join(', ');

            return [
                'lkhno' => $lkh->lkhno,
                'rkhno' => $lkh->rkhno,
                'lkhdate' => $lkh->lkhdate,
                'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
                'mandor_nama' => $lkh->mandor_nama,
                'activityname' => $lkh->activityname ?? 'Unknown Activity',
                'approval_level' => $lkh->approval_level,
                'status' => $lkh->status,
                'total_workers' => $lkh->totalworkers,
                'total_hasil' => $lkh->totalhasil,
                'plots' => $plots ?: 'No plots'
            ];
        });
    }

    /**
     * Get LKH approval detail data
     */
    private function getLkhApprovalDetailData($companycode, $lkhno)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('a.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('user as u1', 'h.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'h.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'h.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'h.approval1idjabatan', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'h.approval2idjabatan', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'h.approval3idjabatan', '=', 'j3.idjabatan')
            ->where('h.companycode', $companycode)
            ->where('h.lkhno', $lkhno)
            ->select([
                'h.*',
                'm.name as mandor_nama',
                'a.activityname',
                'h.jumlahapproval',
                'h.approval1idjabatan',
                'h.approval2idjabatan', 
                'h.approval3idjabatan',
                'u1.name as approval1_user_name',
                'u2.name as approval2_user_name',
                'u3.name as approval3_user_name',
                'j1.namajabatan as jabatan1_name',
                'j2.namajabatan as jabatan2_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Format LKH approval detail data
     */
    private function formatLkhApprovalDetailData($lkh)
    {
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $lkh->{"approval{$i}idjabatan"};
            if (!$jabatanId) continue;

            $flagField = "approval{$i}flag";
            $dateField = "approval{$i}date";
            $userField = "approval{$i}_user_name";
            $jabatanField = "jabatan{$i}_name";

            $flag = $lkh->$flagField;
            $status = 'waiting';
            $statusText = 'Waiting';

            if ($flag === '1') {
                $status = 'approved';
                $statusText = 'Approved';
            } elseif ($flag === '0') {
                $status = 'declined';
                $statusText = 'Declined';
            }

            $levels[] = [
                'level' => $i,
                'jabatan_name' => $lkh->$jabatanField ?? 'Unknown',
                'status' => $status,
                'status_text' => $statusText,
                'user_name' => $lkh->$userField ?? null,
                'date_formatted' => $lkh->$dateField ? Carbon::parse($lkh->$dateField)->format('d/m/Y H:i') : null
            ];
        }

        return [
            'lkhno' => $lkh->lkhno,
            'rkhno' => $lkh->rkhno,
            'lkhdate' => $lkh->lkhdate,
            'lkhdate_formatted' => Carbon::parse($lkh->lkhdate)->format('d/m/Y'),
            'mandor_nama' => $lkh->mandor_nama,
            'activityname' => $lkh->activityname ?? 'Unknown Activity',
            'jumlah_approval' => $lkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }

    /**
     * Execute LKH approval process
     */
    private function executeLkhApprovalProcess($request)
    {
        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        $lkhno = $request->lkhno;
        $action = $request->action;
        $level = $request->level;

        if (!$this->validateUserForApproval($currentUser)) {
            return [
                'success' => false,
                'message' => 'User tidak memiliki jabatan yang valid'
            ];
        }

        $lkh = DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->first();

        if (!$lkh) {
            return ['success' => false, 'message' => 'LKH tidak ditemukan'];
        }

        $canApprove = $this->validateLkhApprovalAuthority($lkh, $currentUser, $level);
        if (!$canApprove['success']) {
            return $canApprove;
        }

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

        if ($action === 'approve') {
            $tempLkh = clone $lkh;
            $tempLkh->$approvalField = '1';
            
            if ($this->isLKHFullyApproved($tempLkh)) {
                $updateData['status'] = 'APPROVED';
            }
        }

        DB::table('lkhhdr')->where('companycode', $companycode)->where('lkhno', $lkhno)->update($updateData);

        $responseMessage = 'LKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

        return ['success' => true, 'message' => $responseMessage];
    }

    /**
     * Validate LKH approval authority
     */
    private function validateLkhApprovalAuthority($lkh, $currentUser, $level)
    {
        $canApprove = false;

        switch ($level) {
            case 1:
                if ($lkh->approval1idjabatan == $currentUser->idjabatan && is_null($lkh->approval1flag)) {
                    $canApprove = true;
                }
                break;
            case 2:
                if ($lkh->approval2idjabatan == $currentUser->idjabatan && 
                    $lkh->approval1flag == '1' && is_null($lkh->approval2flag)) {
                    $canApprove = true;
                }
                break;
            case 3:
                if ($lkh->approval3idjabatan == $currentUser->idjabatan && 
                    $lkh->approval1flag == '1' && $lkh->approval2flag == '1' && is_null($lkh->approval3flag)) {
                    $canApprove = true;
                }
                break;
        }

        if (!$canApprove) {
            return [
                'success' => false,
                'message' => 'Anda tidak memiliki hak untuk melakukan approval pada level ini'
            ];
        }

        return ['success' => true];
    }

    /**
     * Get LKH numbers for specific date
     */
    private function getLkhNumbersForDate($companycode, $date)
    {
        return DB::table('lkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('lkhdate', $date)
            ->pluck('lkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get Pengolahan data (Activity II, III, IV) - FIXED: Use luashasil per plot  
     * UPDATED: using lkhdetailplot and proper mandor from header with correct hasil per plot
     */
    private function getPengolahanData($companycode, $date)
    {
        return DB::table('lkhhdr as h')
            ->leftJoin('lkhdetailplot as ldp', function($join) use ($companycode) {
                $join->on('h.lkhno', '=', 'ldp.lkhno')
                    ->where('ldp.companycode', '=', $companycode);
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->whereIn('a.activitygroup', ['II', 'III', 'IV']) // ? Pakai activitygroup
            ->select([
                'h.lkhno',
                'h.activitycode',
                'h.totalworkers',
                'ldp.luashasil as totalhasil',
                'h.totalupahall',
                'u.name as mandor_nama',
                'a.activityname',
                'ldp.plot'
            ])
            ->orderBy('h.activitycode')
            ->orderBy('h.lkhno')
            ->get()
            ->groupBy('activitycode')
            ->toArray();
    }


    // =====================================
    // SECTION 5: DTH REPORT MANAGEMENT
    // =====================================

    /**
     * Get DTH (Distribusi Tenaga Harian) data
     */
    public function getDTHData(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $companycode = Session::get('companycode');

        try {
            $companyInfo = $this->getCompanyInfo($companycode);
            $rkhNumbers = $this->getRkhNumbersForDate($companycode, $date);
            
            $dthData = [
                'harian' => $this->getHarianData($companycode, $date),
                'borongan' => $this->getBoronganData($companycode, $date),
                'alat' => $this->getAlatData($companycode, $date)
            ];

            return response()->json([
                'success' => true,
                'company_info' => $companyInfo,
                'harian' => $dthData['harian'],
                'borongan' => $dthData['borongan'],
                'alat' => $dthData['alat'],
                'rkh_numbers' => $rkhNumbers,
                'date' => $date,
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'debug' => [
                    'harian_count' => count($dthData['harian']),
                    'borongan_count' => count($dthData['borongan']),
                    'alat_count' => count($dthData['alat']),
                    'rkh_count' => count($rkhNumbers)
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error("DTH Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data DTH: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show DTH report view
     */
    public function showDTHReport(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        return view('input.rencanakerjaharian.dth-report', ['date' => $date]);
    }


    /**
     * Generate DTH report
     */
    public function generateDTH(Request $request)
    {
        $request->validate(['date' => 'required|date']);
        $url = route('input.rencanakerjaharian.dth-report', ['date' => $request->date]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan DTH...',
            'redirect_url' => $url
        ]);
    }

    // =====================================
    // SECTION 6: MATERIAL USAGE MANAGEMENT
    // =====================================

    /**
     * Get material usage data for RKH
     */
    public function getMaterialUsageData($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $materialUsage = $this->buildMaterialUsageQuery($companycode, $rkhno)->get();
                
            return [
                'success' => true,
                'data' => $materialUsage,
                'has_material_usage' => !$materialUsage->isEmpty()
            ];
            
        } catch (\Exception $e) {
            \Log::error("Error getting material usage data for RKH {$rkhno}: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Gagal memuat data material usage: ' . $e->getMessage(),
                'data' => collect(),
                'has_material_usage' => false
            ];
        }
    }

    /**
     * API endpoint to get material usage data
     */
    public function getMaterialUsageApi($rkhno)
    {
        $result = $this->getMaterialUsageData($rkhno);
        
        if ($result['success']) {
            $groupedData = $this->groupMaterialUsageData($result['data']);
            
            return response()->json([
                'success' => true,
                'rkhno' => $rkhno,
                'totalluas' => $result['data']->first()->totalluas ?? 0,
                'flagstatus' => $result['data']->first()->flagstatus ?? 'N/A',
                'createdat' => $result['data']->first()->createdat ?? null,
                'inputby' => $result['data']->first()->inputby ?? 'N/A',
                'material_groups' => $groupedData,
                'total_items' => $result['data']->count()
            ]);
        } else {
            return response()->json($result, 500);
        }
    }

    /**
     * Generate material usage manually
     */
    public function generateMaterialUsage(Request $request)
    {
        $request->validate(['rkhno' => 'required|string']);
        
        try {
            $result = $this->executeGenerateMaterialUsage($request);
            return response()->json($result);
            
        } catch (\Exception $e) {
            \Log::error("Error manual generate material usage: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate material usage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if RKH has material usage
     */
    public function hasMaterialUsage($rkhno)
    {
        $companycode = Session::get('companycode');
        
        return DB::table('usematerialhdr')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->exists();
    }

    // =====================================
    // SECTION 7: UTILITY METHODS
    // =====================================

    /**
 * Load attendance by date - FIXED
 */
public function loadAbsenByDate(Request $request)
{
    $date = $request->query('date', date('Y-m-d'));
    $mandorId = $request->query('mandor_id');
    $companycode = Session::get('companycode');
    
    // Query data absen yang sudah APPROVED saja
    $absenData = DB::table('absenhdr as h')
        ->join('absenlst as l', 'h.absenno', '=', 'l.absenno') // REMOVED companycode join
        ->join('tenagakerja as t', function($join) use ($companycode) {
            $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                 ->where('t.companycode', '=', $companycode)
                 ->where('t.isactive', '=', 1);
        })
        ->where('h.companycode', $companycode)
        ->whereDate('h.uploaddate', $date)
        ->where('l.approval_status', 'APPROVED') // HANYA yang APPROVED
        ->when($mandorId, function($query) use ($mandorId) {
            return $query->where('h.mandorid', $mandorId);
        })
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

    // Get mandor list
    $mandorList = DB::table('absenhdr as h')
        ->join('user as u', 'h.mandorid', '=', 'u.userid')
        ->where('h.companycode', $companycode)
        ->whereDate('h.uploaddate', $date)
        ->select('h.mandorid', 'u.name as mandor_name')
        ->distinct()
        ->get();

    return response()->json([
        'success' => true,
        'data' => $absenData,
        'mandor_list' => $mandorList
    ]);
}

    // =====================================
    // PRIVATE HELPER METHODS
    // =====================================

    /**
     * Build base query for RKH index
     */
    private function buildRkhIndexQuery($companycode)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                'r.approvalstatus', // NEW: Include approvalstatus
                DB::raw('CASE 
                    WHEN r.approvalstatus = "1" THEN "Approved"
                    WHEN r.approvalstatus = "0" THEN "Rejected"
                    WHEN app.jumlahapproval IS NULL OR app.jumlahapproval = 0 THEN "No Approval Required"
                    WHEN r.approval1flag IS NULL AND app.idjabatanapproval1 IS NOT NULL THEN "Waiting"
                    WHEN r.approval1flag = "0" THEN "Declined"
                    WHEN r.approval1flag = "1" AND app.idjabatanapproval2 IS NOT NULL AND r.approval2flag IS NULL THEN "Waiting"
                    WHEN r.approval2flag = "0" THEN "Declined"
                    WHEN r.approval2flag = "1" AND app.idjabatanapproval3 IS NOT NULL AND r.approval3flag IS NULL THEN "Waiting"
                    WHEN r.approval3flag = "0" THEN "Declined"
                    ELSE "Waiting"
                END as approval_status'),
                DB::raw('CASE 
                    WHEN r.status = "Completed" THEN "Completed"
                    ELSE "In Progress"
                END as current_status')
            ]);
    }

    /**
     * Apply filters to index query
     */
    private function applyIndexFilters($query, $search, $filterApproval, $filterStatus, $filterDate, $allDate)
    {
        if ($search) {
            $query->where('r.rkhno', 'like', '%' . $search . '%');
        }

        if ($filterApproval) {
            $query = $this->applyApprovalFilter($query, $filterApproval);
        }

        if ($filterStatus) {
            $query = $this->applyStatusFilter($query, $filterStatus);
        }

        if (empty($allDate)) {
            $dateToFilter = $filterDate ?: Carbon::today()->format('Y-m-d');
            $query->whereDate('r.rkhdate', $dateToFilter);
        }

        return $query;
    }

    /**
     * Apply approval status filter
     */
    private function applyApprovalFilter($query, $filterApproval)
    {
        switch ($filterApproval) {
            case 'Approved':
                $query->where(function($q) {
                    $q->where(function($subq) {
                        $subq->where('app.jumlahapproval', 1)->where('r.approval1flag', '1');
                    })->orWhere(function($subq) {
                        $subq->where('app.jumlahapproval', 2)->where('r.approval1flag', '1')->where('r.approval2flag', '1');
                    })->orWhere(function($subq) {
                        $subq->where('app.jumlahapproval', 3)->where('r.approval1flag', '1')->where('r.approval2flag', '1')->where('r.approval3flag', '1');
                    })->orWhere(function($subq) {
                        $subq->whereNull('app.jumlahapproval')->orWhere('app.jumlahapproval', 0);
                    });
                });
                break;
            case 'Waiting':
                $query->where(function($q) {
                    $q->where(function($subq) {
                        $subq->whereNotNull('app.idjabatanapproval1')->whereNull('r.approval1flag');
                    })->orWhere(function($subq) {
                        $subq->whereNotNull('app.idjabatanapproval2')->where('r.approval1flag', '1')->whereNull('r.approval2flag');
                    })->orWhere(function($subq) {
                        $subq->whereNotNull('app.idjabatanapproval3')->where('r.approval1flag', '1')->where('r.approval2flag', '1')->whereNull('r.approval3flag');
                    });
                });
                break;
            case 'Decline':
                $query->where(function($q) {
                    $q->where('r.approval1flag', '0')->orWhere('r.approval2flag', '0')->orWhere('r.approval3flag', '0');
                });
                break;
        }

        return $query;
    }

    /**
     * Apply status filter
     */
    private function applyStatusFilter($query, $filterStatus)
    {
        if ($filterStatus == 'Completed') {
            $query->where('r.status', 'Completed');
        } elseif ($filterStatus == 'In Progress') {
            $query->where(function($q) {
                $q->where('r.status', '!=', 'Completed')->orWhereNull('r.status');
            });
        }

        return $query;
    }

    /**
     * Get attendance data for forms - FIXED
     */
    private function getAttendanceData($companycode, $date)
    {
        return DB::table('absenhdr as h')
            ->join('absenlst as l', 'h.absenno', '=', 'l.absenno') // REMOVED companycode join
            ->join('tenagakerja as t', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                    ->where('t.companycode', '=', $companycode)
                    ->where('t.isactive', '=', 1);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.uploaddate', Carbon::parse($date ?? Carbon::today()))
            ->where('l.approval_status', 'APPROVED') // HANYA yang APPROVED
            ->select([
                'h.mandorid',
                'l.tenagakerjaid', 
                't.nama',
                't.nik',
                't.gender',
                't.jenistenagakerja'
            ])
            ->get();
    }

    /**
     * Validate date range for RKH creation
     */
    private function validateDateRange($selectedDate)
    {
        $targetDate = Carbon::parse($selectedDate);
        $today = Carbon::today();
        $maxDate = Carbon::today()->addDays(7);
        
        return $targetDate->gte($today) && $targetDate->lte($maxDate);
    }

    /**
     * Generate preview RKH number
     */
    private function generatePreviewRkhNo($targetDate, $companycode)
    {
        $day = $targetDate->format('d');
        $month = $targetDate->format('m');
        $year = $targetDate->format('y');

        $lastRkh = DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $targetDate)
            ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
            ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();

        $newNumber = $lastRkh ? str_pad(((int)substr($lastRkh->rkhno, 7, 2)) + 1, 2, '0', STR_PAD_LEFT) : '01';
        return "RKH{$day}{$month}{$newNumber}{$year}";
    }

    /**
     * Load form data for create form
     */
    private function loadCreateFormData($companycode, $targetDate)
    {
        $herbisidadosages = new Herbisidadosage;
        $absenModel = new AbsenHdr;

        return [
            'mandors' => User::getMandorByCompany($companycode),
            'activities' => Activity::with(['group', 'jenistenagakerja'])->where('active', 1)->orderBy('activitycode')->get(),
            'bloks' => Blok::where('companycode', $companycode)->orderBy('blok')->get(),

            'masterlist' => DB::table('masterlist as m')->leftJoin('batch as b', 'm.activebatchno', '=', 'b.batchno')->where('m.companycode', $companycode)->select([
                'm.companycode',
                'm.plot',
                'm.blok',
                'm.activebatchno',
                'm.isactive',
                'b.lifecyclestatus',
                'b.batcharea'
            ])
            ->orderBy('m.plot')
            ->get(),

            'plots' => DB::table('plot')->where('companycode', $companycode)->get(),
            'absentenagakerja' => $absenModel->getDataAbsenFull($companycode, $targetDate),
            'herbisidagroups' => $herbisidadosages->getFullHerbisidaGroupData($companycode),
            'bloksData' => Blok::where('companycode', $companycode)->orderBy('blok')->get(),

            'masterlistData' => DB::table('masterlist as m')
            ->leftJoin('batch as b', 'm.activebatchno', '=', 'b.batchno')
            ->where('m.companycode', $companycode)
            ->select([
                'm.companycode',
                'm.plot',
                'm.blok',
                'm.activebatchno',
                'm.isactive',
                'b.lifecyclestatus',
                'b.batcharea'
            ])
            ->orderBy('m.plot')
            ->get(),

            'plotsData' => DB::table('plot')->where('companycode', $companycode)->get(),
            'operatorsData' => $this->getOperatorsWithVehicles($companycode),
            'helpersData' => TenagaKerja::where('companycode', $companycode)
                ->where('jenistenagakerja', 4)
                ->where('isactive', 1)
                ->select(['tenagakerjaid', 'nama', 'nik'])
                ->orderBy('nama')
                ->get(),
            'kontraktorData' => DB::table('kontraktor')
                ->where('companycode', $companycode)
                ->where('isactive', 1)
                ->orderBy('namakontraktor')
                ->get(),
            'batchData' => [],
        ];
    }

    /**
     * Load form data for edit form
     */
    private function loadEditFormData($companycode, $rkhdate)
    {
        return $this->loadCreateFormData($companycode, Carbon::parse($rkhdate));
    }

    /**
     * Filter valid rows from input
     */
    private function filterValidRows($rows)
    {
        return collect($rows)
            ->filter(function ($row) {
                return !empty($row['blok']);
            })
            ->map(function ($row) {
                return array_map(function ($value) {
                    return $value ?? '';
                }, $row);
            })
            ->values()
            ->toArray();
    }

    /**
     * Validate RKH request
     * UPDATED: Add panen validation
     */
    private function validateRkhRequest($request)
    {
        $request->validate([
            'mandor_id'              => 'required|exists:user,userid',
            'tanggal'                => 'required|date',
            'keterangan'             => 'nullable|string|max:500',
            'rows'                   => 'required|array|min:1',
            'rows.*.blok'            => 'required|string',
            'rows.*.plot'            => 'required|string',
            'rows.*.nama'            => 'required|string',
            'rows.*.luas'            => 'required|numeric|min:0',
            'rows.*.batchno'         => 'nullable|string',
            'rows.*.kodestatus'      => 'nullable|string|in:PC,RC1,RC2,RC3',
            'rows.*.usingvehicle'    => 'required|in:0,1',
            'rows.*.usinghelper'     => 'required|in:0,1',
            'rows.*.helperid'        => 'nullable|string',
            'rows.*.operatorid'      => 'nullable|string',
            'rows.*.material_group_id' => 'nullable|integer',
            'workers'                  => 'required|array|min:0',
            'workers.*.laki'           => 'nullable|integer|min:0',
            'workers.*.perempuan'      => 'nullable|integer|min:0',
            'workers.*.total'          => 'required|integer|min:0',
        ]);

        // Existing validations...
        $plantingErrors = $this->validatePlantingPlots($request->input('rows', []), Session::get('companycode'));
        if (!empty($plantingErrors)) {
            throw ValidationException::withMessages([
                'planting_validation' => $plantingErrors
            ]);
        }
        
        $panenErrors = $this->validatePanenPlots($request->input('rows', []), Session::get('companycode'));
        if (!empty($panenErrors)) {
            throw ValidationException::withMessages([
                'panen_validation' => $panenErrors
            ]);
        }
    }

    /**
     * Create RKH record in database
     */
    private function createRkhRecord($request)
    {
        $companycode = Session::get('companycode');
        $tanggal = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

        $rkhno = $this->generateUniqueRkhNoWithLock($tanggal);

        $activitiesWorkers = $this->groupRowsByActivity($request->workers);

        $totalLuas = collect($request->rows)->sum('luas');
        $totalManpower = collect($activitiesWorkers)->sum('jumlahtenagakerja');

        $primaryActivityGroup = $this->getPrimaryActivityGroup($request->rows);
        $approvalData = $this->getApprovalData($companycode, $primaryActivityGroup);

        $headerData = array_merge([
            'companycode' => $companycode,
            'rkhno'       => $rkhno,
            'rkhdate'     => $tanggal,
            'totalluas'   => $totalLuas,
            'manpower'    => $totalManpower,
            'mandorid'    => $request->input('mandor_id'),
            'activitygroup' => $primaryActivityGroup,
            'keterangan'  => $request->input('keterangan'),
            'inputby'     => Auth::user()->userid,
            'createdat'   => now(),
        ], $approvalData);

        RkhHdr::create($headerData);

        $this->createWorkerAssignments($activitiesWorkers, $companycode, $rkhno);

        $details = $this->buildRkhDetails($request->rows, $companycode, $rkhno, $tanggal);
        DB::table('rkhlst')->insert($details);

        return $rkhno;
    }

    /**
     * Update RKH record in database
     */
    private function updateRkhRecord($request, $rkhno)
    {
        $companycode = Session::get('companycode');
        $tanggal = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

        // ✅ NEW: Group workers by activity
        $activitiesWorkers = $this->groupRowsByActivity($request->workers);

        $totalLuas = collect($request->rows)->sum('luas');
        $totalManpower = collect($activitiesWorkers)->sum('jumlahtenagakerja');

        $primaryActivityGroup = $this->getPrimaryActivityGroup($request->rows);
        $approvalData = $this->getApprovalDataForUpdate($companycode, $primaryActivityGroup);

        $updateData = array_merge([
            'rkhdate'     => $tanggal,
            'totalluas'   => $totalLuas,
            'manpower'    => $totalManpower,
            'mandorid'    => $request->input('mandor_id'),
            'keterangan'  => $request->input('keterangan'),
            'updateby'    => Auth::user()->userid,
            'updatedat'   => now(),
        ], $approvalData);

        DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->update($updateData);
        
        // ✅ NEW: Update worker assignments
        $this->updateWorkerAssignments($activitiesWorkers, $companycode, $rkhno);
        
        DB::table('rkhlst')->where('companycode', $companycode)->where('rkhno', $rkhno)->delete();

        $details = $this->buildRkhDetails($request->rows, $companycode, $rkhno, $tanggal);
        DB::table('rkhlst')->insert($details);
    }

    /**
     * Get primary activity group from rows
     */
    private function getPrimaryActivityGroup($rows)
    {
        foreach ($rows as $row) {
            if (!empty($row['nama'])) {
                $activity = Activity::where('activitycode', $row['nama'])->first();
                if ($activity && $activity->activitygroup) {
                    return $activity->activitygroup;
                }
            }
        }
        return null;
    }

    /**
     * Get approval data for create
     */
    private function getApprovalData($companycode, $primaryActivityGroup)
    {
        if (!$primaryActivityGroup) return [];
        
        $approvalSetting = DB::table('approval')
            ->where('companycode', $companycode)
            ->where('activitygroup', $primaryActivityGroup)
            ->first();
        
        if ($approvalSetting) {
            return [
                'jumlahapproval' => $approvalSetting->jumlahapproval,
                'approval1idjabatan' => $approvalSetting->idjabatanapproval1,
                'approval2idjabatan' => $approvalSetting->idjabatanapproval2,
                'approval3idjabatan' => $approvalSetting->idjabatanapproval3,
            ];
        }

        return [];
    }

    /**
     * Get approval data for update (resets flags)
     */
    private function getApprovalDataForUpdate($companycode, $primaryActivityGroup)
    {
        $approvalData = $this->getApprovalData($companycode, $primaryActivityGroup);
        
        if (!empty($approvalData)) {
            $approvalData = array_merge($approvalData, [
                'activitygroup' => $primaryActivityGroup,
                'approval1flag' => null,
                'approval2flag' => null,
                'approval3flag' => null,
                'approval1date' => null,
                'approval2date' => null,
                'approval3date' => null,
                'approval1userid' => null,
                'approval2userid' => null,
                'approval3userid' => null,
            ]);
        }

        return $approvalData;
    }

    /**
     * Build RKH detail records
     */
    private function buildRkhDetails($rows, $companycode, $rkhno, $tanggal)
    {
        $details = [];
        $panenActivities = ['4.3.3', '4.4.3', '4.5.2'];
        
        foreach ($rows as $row) {
            $activity = Activity::where('activitycode', $row['nama'])->first();
            $jenistenagakerja = $activity ? $activity->jenistenagakerja : null;

            $detailData = [
                'companycode'         => $companycode,
                'rkhno'               => $rkhno,
                'rkhdate'             => $tanggal,
                'blok'                => $row['blok'],
                'plot'                => $row['plot'],
                'activitycode'        => $row['nama'],
                'luasarea'            => $row['luas'],
                'jenistenagakerja'    => $jenistenagakerja,
                'usingmaterial'       => !empty($row['material_group_id']) ? 1 : 0,
                'herbisidagroupid'    => !empty($row['material_group_id']) ? (int) $row['material_group_id'] : null,
                'usingvehicle'        => $row['usingvehicle'],
                'operatorid'          => !empty($row['operatorid']) ? $row['operatorid'] : null,
                'usinghelper'         => $row['usinghelper'] ?? 0,
                'helperid'            => !empty($row['helperid']) ? $row['helperid'] : null,
            ];

            // FIXED: Batch data for panen activities using new structure
            if (in_array($row['nama'], $panenActivities)) {
                $batchInfo = $this->getBatchInfoForPlot($companycode, $row['plot']);
                
                if ($batchInfo) {
                    $detailData['batchno'] = $batchInfo->batchno;
                    $detailData['kodestatus'] = $batchInfo->lifecyclestatus; // ✅ Ambil dari lifecyclestatus
                    
                    \Log::info("Batch info attached to RKH detail", [
                        'plot' => $row['plot'],
                        'batchno' => $batchInfo->batchno,
                        'lifecyclestatus' => $batchInfo->lifecyclestatus,
                        'batcharea' => $batchInfo->batcharea
                    ]);
                } else {
                    \Log::warning("Plot {$row['plot']} tidak memiliki batch info untuk panen activity {$row['nama']}");
                    $detailData['batchno'] = null;
                    $detailData['kodestatus'] = null;
                }
            }
            
            $details[] = $detailData;
        }
        
        return $details;
    }

    /**
     * Get batch info for specific plot (for panen activities)
     * NEW METHOD: Get active batch and lifecycle status
     * 
     * @param string $companycode
     * @param string $plot
     * @return object|null
     */
    private function getBatchInfoForPlot($companycode, $plot)
    {
        return DB::table('masterlist')
            ->join('batch', 'masterlist.activebatchno', '=', 'batch.batchno')
            ->where('masterlist.companycode', $companycode)
            ->where('masterlist.plot', $plot)
            ->where('masterlist.isactive', 1)
            ->where('batch.isactive', 1)
            ->select([
                'batch.batchno',
                'batch.lifecyclestatus',
                'batch.batcharea',
                'batch.tanggalpanen'
            ])
            ->first();
    }

    /**
     * Generate unique RKH number with database lock
     */
    private function generateUniqueRkhNoWithLock($date)
    {
        $carbonDate = Carbon::parse($date);
        $day = $carbonDate->format('d');
        $month = $carbonDate->format('m');
        $year = $carbonDate->format('y');
        $companycode = Session::get('companycode');

        return DB::transaction(function () use ($carbonDate, $day, $month, $year, $companycode) {
            $lastRkh = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->whereDate('rkhdate', $carbonDate)
                ->where('rkhno', 'like', "RKH{$day}{$month}%" . $year)
                ->lockForUpdate()
                ->orderBy(DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
                ->first();

            if ($lastRkh) {
                $lastNumber = (int)substr($lastRkh->rkhno, 7, 2);
                $newNumber = str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '01';
            }

            return "RKH{$day}{$month}{$newNumber}{$year}";
        });
    }

    /**
     * Handle store response
     */
    private function handleStoreResponse($request, $rkhno, $success, $errorMessage = null)
    {
        if ($request->ajax() || $request->wantsJson()) {
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "Data berhasil disimpan dengan nomor RKH: <strong>{$rkhno}</strong>",
                    'rkhno' => $rkhno,
                    'redirect_url' => route('input.rencanakerjaharian.index')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $errorMessage
                ], 500);
            }
        }

        if ($success) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('success', 'RKH berhasil disimpan!');
        } else {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Terjadi kesalahan: ' . $errorMessage);
        }
    }

    /**
     * Handle update response
     */
    private function handleUpdateResponse($request, $rkhno, $success, $errorMessage = null)
    {
        if ($request->ajax() || $request->wantsJson()) {
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "RKH berhasil diupdate!",
                    'rkhno' => $rkhno,
                    'redirect_url' => route('input.rencanakerjaharian.index')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $errorMessage
                ], 500);
            }
        }

        if ($success) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('success', 'RKH berhasil diupdate!');
        } else {
            return redirect()->back()
                ->withInput($request->all())
                ->with('error', 'Terjadi kesalahan: ' . $errorMessage);
        }
    }

    /**
     * Get RKH header for display
     */
    private function getRkhHeader($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                    ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                DB::raw('CASE 
                    WHEN app.jumlahapproval IS NULL OR app.jumlahapproval = 0 THEN "No Approval Required"
                    WHEN r.approval1flag IS NULL AND app.idjabatanapproval1 IS NOT NULL THEN "Waiting Level 1"
                    WHEN r.approval1flag = "0" THEN "Declined Level 1"
                    WHEN r.approval1flag = "1" AND app.idjabatanapproval2 IS NOT NULL AND r.approval2flag IS NULL THEN "Waiting Level 2"
                    WHEN r.approval2flag = "0" THEN "Declined Level 2"
                    WHEN r.approval2flag = "1" AND app.idjabatanapproval3 IS NOT NULL AND r.approval3flag IS NULL THEN "Waiting Level 3"
                    WHEN r.approval3flag = "0" THEN "Declined Level 3"
                    WHEN (app.jumlahapproval = 1 AND r.approval1flag = "1") OR
                        (app.jumlahapproval = 2 AND r.approval1flag = "1" AND r.approval2flag = "1") OR
                        (app.jumlahapproval = 3 AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag = "1") THEN "Approved"
                    ELSE "Waiting"
                END as approval_status'),
                DB::raw('CASE 
                    WHEN r.status = "Done" THEN "Done"
                    ELSE "On Progress"
                END as current_status')
            ])
            ->first();
    }

    /**
     * Get RKH header for edit
     */
    private function getRkhHeaderForEdit($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select(['r.*', 'm.name as mandor_nama'])
            ->first();
    }

    /**
     * Get RKH details for display
     * UPDATED: Include batch data for panen activities
     */
    private function getRkhDetails($companycode, $rkhno)
    {
        return DB::table('rkhlst as r')
            ->leftJoin('rkhlstworker as w', function($join) {
                $join->on('r.companycode', '=', 'w.companycode')
                    ->on('r.rkhno', '=', 'w.rkhno')
                    ->on('r.activitycode', '=', 'w.activitycode');
            })
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as tk', 'r.operatorid', '=', 'tk.tenagakerjaid')
            ->leftJoin('tenagakerja as tk_helper', 'r.helperid', '=', 'tk_helper.tenagakerjaid')
            // NEW: Join batch for panen activities
            ->leftJoin('batch as b', 'r.batchno', '=', 'b.batchno')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*', 
                'w.jumlahlaki',
                'w.jumlahperempuan',
                'w.jumlahtenagakerja',
                'hg.herbisidagroupname', 
                'a.activityname', 
                'a.jenistenagakerja',
                'tk.nama as operator_name',
                'tk.nik as operator_nik',
                'tk_helper.nama as helper_name',
                // NEW: Batch info
                'b.batchno as batch_number',
                'b.lifecyclestatus as batch_lifecycle',
                'b.batcharea'
            ])
            ->get();
    }

    /**
     * Get RKH details for edit
     */
    private function getRkhDetailsForEdit($companycode, $rkhno)
    {
        return DB::table('rkhlst as r')
            ->leftJoin('rkhlstworker as w', function($join) {
                $join->on('r.companycode', '=', 'w.companycode')
                    ->on('r.rkhno', '=', 'w.rkhno')
                    ->on('r.activitycode', '=', 'w.activitycode');
            })
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as tk_operator', 'r.operatorid', '=', 'tk_operator.tenagakerjaid')
            ->leftJoin('tenagakerja as tk_helper', 'r.helperid', '=', 'tk_helper.tenagakerjaid')
            ->leftJoin('batch as b', 'r.batchno', '=', 'b.batchno')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*', 
                'w.jumlahlaki',
                'w.jumlahperempuan',
                'w.jumlahtenagakerja',
                'hg.herbisidagroupname', 
                'a.activityname', 
                'a.jenistenagakerja',
                'tk_operator.nama as operator_name',
                'tk_helper.nama as helper_name',
                'b.batchno as batch_number',
                'b.lifecyclestatus as batch_lifecycle',
                'b.batcharea'
            ])
            ->get();
    }

    /**
     * Check if RKH is approved (any level)
     */
    private function isRkhApproved($rkh)
    {
        if (!$rkh || !$rkh->jumlahapproval) return false;
        
        return $rkh->approval1flag === '1' || 
            $rkh->approval2flag === '1' || 
            $rkh->approval3flag === '1';
    }

    /**
     * Check if RKH is fully approved
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
     * Validate user for approval process
     */
    private function validateUserForApproval($currentUser)
    {
        return $currentUser && $currentUser->idjabatan;
    }

    /**
     * Build pending approvals query
     */
    private function buildPendingApprovalsQuery($companycode, $currentUser)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->where('r.companycode', $companycode)
            ->where(function($query) use ($currentUser) {
                $query->where(function($q) use ($currentUser) {
                    $q->where('app.idjabatanapproval1', $currentUser->idjabatan)->whereNull('r.approval1flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('app.idjabatanapproval2', $currentUser->idjabatan)->where('r.approval1flag', '1')->whereNull('r.approval2flag');
                })->orWhere(function($q) use ($currentUser) {
                    $q->where('app.idjabatanapproval3', $currentUser->idjabatan)->where('r.approval1flag', '1')->where('r.approval2flag', '1')->whereNull('r.approval3flag');
                });
            })
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2',
                'app.idjabatanapproval3',
                DB::raw('CASE 
                    WHEN app.idjabatanapproval1 = '.$currentUser->idjabatan.' AND r.approval1flag IS NULL THEN 1
                    WHEN app.idjabatanapproval2 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag IS NULL THEN 2
                    WHEN app.idjabatanapproval3 = '.$currentUser->idjabatan.' AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag IS NULL THEN 3
                    ELSE 0
                END as approval_level')
            ])
            ->orderBy('r.rkhdate', 'desc');
    }

    /**
     * Format pending approvals data
     */
    private function formatPendingApprovalsData($pendingRKH)
    {
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
     * Get user info for response
     */
    private function getUserInfo($currentUser)
    {
        return [
            'userid' => $currentUser->userid,
            'name' => $currentUser->name,
            'idjabatan' => $currentUser->idjabatan,
            'jabatan_name' => $this->getJabatanName($currentUser->idjabatan)
        ];
    }

    /**
     * Execute approval process with proper transaction handling
     * FIXED: Wrap entire approval + post-actions in single transaction
     */
    private function executeApprovalProcess($request)
    {
        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        $rkhno = $request->rkhno;
        $action = $request->action;
        $level = $request->level;

        if (!$this->validateUserForApproval($currentUser)) {
            return [
                'success' => false,
                'message' => 'User tidak memiliki jabatan yang valid'
            ];
        }

        try {
            DB::beginTransaction();

            $rkh = DB::table('rkhhdr as r')
                ->leftJoin('approval as app', function($join) use ($companycode) {
                    $join->on('r.activitygroup', '=', 'app.activitygroup')
                        ->where('app.companycode', '=', $companycode);
                })
                ->where('r.companycode', $companycode)
                ->where('r.rkhno', $rkhno)
                ->select(['r.*', 'app.jumlahapproval', 'app.idjabatanapproval1', 'app.idjabatanapproval2', 'app.idjabatanapproval3'])
                ->first();

            if (!$rkh) {
                DB::rollBack();
                return ['success' => false, 'message' => 'RKH tidak ditemukan'];
            }

            $validationResult = $this->validateApprovalAuthority($rkh, $currentUser, $level);
            if (!$validationResult['success']) {
                DB::rollBack();
                return $validationResult;
            }

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

            // NEW: Update approvalstatus based on action
            if ($action === 'approve') {
                // Check if this approval makes it fully approved
                $tempRkh = clone $rkh;
                $tempRkh->$approvalField = '1';
                
                if ($this->isRkhFullyApproved($tempRkh)) {
                    $updateData['approvalstatus'] = '1';
                } else {
                    $updateData['approvalstatus'] = null; // Still in progress
                }
            } else {
                // If rejected at any level
                $updateData['approvalstatus'] = '0';
            }

            DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->update($updateData);

            $responseMessage = 'RKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

            // Handle post-approval actions within same transaction
            if ($action === 'approve' && ($updateData['approvalstatus'] ?? null) === '1') {
                $responseMessage = $this->handlePostApprovalActionsTransactional($rkhno, $responseMessage, $companycode);
            }

            DB::commit();
            return ['success' => true, 'message' => $responseMessage];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Approval process failed for RKH {$rkhno}: " . $e->getMessage(), [
                'user' => $currentUser->userid,
                'action' => $action,
                'level' => $level,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Proses approval gagal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate approval authority
     */
    private function validateApprovalAuthority($rkh, $currentUser, $level)
    {
        $approvalJabatanField = "idjabatanapproval{$level}";
        $approvalField = "approval{$level}flag";

        if (!isset($rkh->$approvalJabatanField) || $rkh->$approvalJabatanField != $currentUser->idjabatan) {
            return ['success' => false, 'message' => 'Anda tidak memiliki wewenang untuk approve level ini'];
        }

        if (isset($rkh->$approvalField) && $rkh->$approvalField !== null) {
            return ['success' => false, 'message' => 'Approval level ini sudah diproses sebelumnya'];
        }

        if ($level > 1) {
            $prevLevel = $level - 1;
            $prevApprovalField = "approval{$prevLevel}flag";
            if (!isset($rkh->$prevApprovalField) || $rkh->$prevApprovalField !== '1') {
                return ['success' => false, 'message' => 'Approval level sebelumnya belum disetujui'];
            }
        }

        return ['success' => true];
    }

    /**
     * Handle post-approval actions within existing transaction
     */
    private function handlePostApprovalActionsTransactional($rkhno, $responseMessage, $companycode)
    {
        // Get updated RKH data
        $updatedRkh = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();

        if (!$this->isRkhFullyApproved($updatedRkh)) {
            return $responseMessage;
        }

        // STEP 1: Generate LKH (CRITICAL - HARD FAILURE)
        $lkhGenerator = new LkhGeneratorService();
        $lkhResult = $lkhGenerator->generateLkhFromRkh($rkhno);
        
        if (!$lkhResult['success']) {
            \Log::error("LKH auto-generation failed", [
                'rkhno' => $rkhno,
                'error' => $lkhResult['message'] ?? 'Unknown error'
            ]);
            throw new \Exception('LKH auto-generation gagal: ' . $lkhResult['message'] . '. Approval dibatalkan untuk menjaga konsistensi data.');
        }
        
        $responseMessage .= '. LKH auto-generated successfully (' . $lkhResult['total_lkh'] . ' LKH created)';
        
        // STEP 2: NEW - Handle Planting Activities (Create Batch)
        if ($this->hasPlantingActivities($rkhno, $companycode)) {
            try {
                $createdBatches = $this->handlePlantingActivity($rkhno, $companycode);
                if (!empty($createdBatches)) {
                    $responseMessage .= '. Batch penanaman dibuat: ' . implode(', ', $createdBatches);
                }
            } catch (\Exception $e) {
                \Log::error("Batch creation failed", [
                    'rkhno' => $rkhno,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('Batch creation gagal: ' . $e->getMessage() . '. Approval dibatalkan untuk menjaga konsistensi data.');
            }
        }
        
        // STEP 3: Check if RKH needs material usage generation
        $needsMaterialUsage = $this->checkIfRkhNeedsMaterialUsage($rkhno, $companycode);
        
        if (!$needsMaterialUsage) {
            $responseMessage .= '. No material usage required for this RKH';
            return $responseMessage;
        }

        // STEP 4: Generate Material Usage (CRITICAL - HARD FAILURE)
        $materialUsageGenerator = new MaterialUsageGeneratorService();
        $materialResult = $materialUsageGenerator->generateMaterialUsageFromRkh($rkhno);
        
        if (!$materialResult['success']) {
            \Log::error("Material usage auto-generation failed", [
                'rkhno' => $rkhno,
                'error' => $materialResult['message'] ?? 'Unknown error'
            ]);
            throw new \Exception('Material usage auto-generation gagal: ' . $materialResult['message'] . '. Approval dibatalkan untuk menjaga konsistensi data.');
        }
        
        if (($materialResult['total_items'] ?? 0) > 0) {
            $responseMessage .= '. Material usage auto-generated (' . $materialResult['total_items'] . ' items created)';
        } else {
            $responseMessage .= '. Material usage processed (no items needed)';
        }
        
        return $responseMessage;
    }

    /**
     * Check if RKH needs material usage generation
     * NEW METHOD: Prevent unnecessary material generation attempts
     */
    private function checkIfRkhNeedsMaterialUsage($rkhno, $companycode)
    {
        // Check if any RKH details use material
        $hasMaterialUsage = DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('usingmaterial', 1)
            ->whereNotNull('herbisidagroupid')
            ->exists();
        
        \Log::info("Checking material usage need", [
            'rkhno' => $rkhno,
            'has_material_usage' => $hasMaterialUsage
        ]);
        
        return $hasMaterialUsage;
    }

    /**
     * Get RKH approval detail
     */
    private function getRkhApprovalDetail($companycode, $rkhno)
    {
        return DB::table('rkhhdr as r')
            ->leftJoin('user as m', 'r.mandorid', '=', 'm.userid')
            ->leftJoin('approval as app', function($join) use ($companycode) {
                $join->on('r.activitygroup', '=', 'app.activitygroup')
                     ->where('app.companycode', '=', $companycode);
            })
            ->leftJoin('activitygroup as ag', 'r.activitygroup', '=', 'ag.activitygroup')
            ->leftJoin('user as u1', 'r.approval1userid', '=', 'u1.userid')
            ->leftJoin('user as u2', 'r.approval2userid', '=', 'u2.userid')
            ->leftJoin('user as u3', 'r.approval3userid', '=', 'u3.userid')
            ->leftJoin('jabatan as j1', 'app.idjabatanapproval1', '=', 'j1.idjabatan')
            ->leftJoin('jabatan as j2', 'app.idjabatanapproval2', '=', 'j2.idjabatan')
            ->leftJoin('jabatan as j3', 'app.idjabatanapproval3', '=', 'j3.idjabatan')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*',
                'm.name as mandor_nama',
                'ag.groupname as activity_group_name',
                'app.jumlahapproval',
                'app.idjabatanapproval1',
                'app.idjabatanapproval2', 
                'app.idjabatanapproval3',
                'u1.name as approval1_user_name',
                'u2.name as approval2_user_name',
                'u3.name as approval3_user_name',
                'j1.namajabatan as jabatan1_name',
                'j2.namajabatan as jabatan2_name',
                'j3.namajabatan as jabatan3_name'
            ])
            ->first();
    }

    /**
     * Format approval detail data
     */
    private function formatApprovalDetailData($rkh)
    {
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $rkh->{"idjabatanapproval{$i}"};
            if (!$jabatanId) continue;

            $flagField = "approval{$i}flag";
            $dateField = "approval{$i}date";
            $userField = "approval{$i}_user_name";
            $jabatanField = "jabatan{$i}_name";

            $flag = $rkh->$flagField;
            $status = 'waiting';
            $statusText = 'Waiting';

            if ($flag === '1') {
                $status = 'approved';
                $statusText = 'Approved';
            } elseif ($flag === '0') {
                $status = 'declined';
                $statusText = 'Declined';
            }

            $levels[] = [
                'level' => $i,
                'jabatan_name' => $rkh->$jabatanField ?? 'Unknown',
                'status' => $status,
                'status_text' => $statusText,
                'user_name' => $rkh->$userField ?? null,
                'date_formatted' => $rkh->$dateField ? Carbon::parse($rkh->$dateField)->format('d/m/Y H:i') : null
            ];
        }

        return [
            'rkhno' => $rkh->rkhno,
            'rkhdate' => $rkh->rkhdate,
            'rkhdate_formatted' => Carbon::parse($rkh->rkhdate)->format('d/m/Y'),
            'mandor_nama' => $rkh->mandor_nama,
            'activity_group_name' => $rkh->activity_group_name ?? 'Unknown',
            'jumlah_approval' => $rkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }


    /**
     * Get company info for DTH
     */
    private function getCompanyInfo($companycode)
    {
        $companyInfo = DB::table('company')
            ->where('companycode', $companycode)
            ->select('companycode', 'name')
            ->first();
        
        return $companyInfo ? 
            "{$companyInfo->companycode} - {$companyInfo->name}" : 
            $companycode;
    }

    /**
     * Get RKH numbers for specific date
     */
    private function getRkhNumbersForDate($companycode, $date)
    {
        return DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $date)
            ->pluck('rkhno')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get Harian data for DTH
     */
    private function getHarianData($companycode, $date)
    {
        // Step 1: Get all rkhlst records with harian/operator jenis
        $rkhPlots = DB::table('rkhhdr as h')
            ->join('rkhlst as l', function($join) {
                $join->on('h.rkhno', '=', 'l.rkhno')
                    ->on('h.companycode', '=', 'l.companycode');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->whereIn('l.jenistenagakerja', [1, 3]) // Harian (1) + Operator (3)
            ->select([
                'h.rkhno',
                'h.mandorid',
                'u.name as mandor_nama',
                'l.activitycode',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.jenistenagakerja',
                'a.activityname'
            ])
            ->get();

        // Step 2: For each plot, get worker data from rkhlstworker
        $result = [];
        
        foreach ($rkhPlots as $plot) {
            // Get worker counts for this activity
            $workerData = DB::table('rkhlstworker')
                ->where('companycode', $companycode)
                ->where('rkhno', $plot->rkhno)
                ->where('activitycode', $plot->activitycode)
                ->first();
            
            // Combine plot data with worker data
            $result[] = (object)[
                'rkhno' => $plot->rkhno,
                'blok' => $plot->blok,
                'plot' => $plot->plot,
                'luasarea' => $plot->luasarea,
                'jumlahlaki' => $workerData ? ($workerData->jumlahlaki ?? 0) : 0,
                'jumlahperempuan' => $workerData ? ($workerData->jumlahperempuan ?? 0) : 0,
                'jumlahtenagakerja' => $workerData ? ($workerData->jumlahtenagakerja ?? 0) : 0,
                'jenistenagakerja' => $plot->jenistenagakerja,
                'mandor_nama' => $plot->mandor_nama,
                'activityname' => $plot->activityname
            ];
        }
        
        return $result;
    }

    /**
     * Get Borongan data for DTH
     */
    private function getBoronganData($companycode, $date)
    {
        // Step 1: Get all rkhlst records with borongan jenis
        $rkhPlots = DB::table('rkhhdr as h')
            ->join('rkhlst as l', function($join) {
                $join->on('h.rkhno', '=', 'l.rkhno')
                    ->on('h.companycode', '=', 'l.companycode');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->where('l.jenistenagakerja', 2) // Borongan
            ->select([
                'h.rkhno',
                'h.mandorid',
                'u.name as mandor_nama',
                'l.activitycode',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.jenistenagakerja',
                'a.activityname'
            ])
            ->get();

        // Step 2: For each plot, get worker data from rkhlstworker
        $result = [];
        
        foreach ($rkhPlots as $plot) {
            // Get worker counts for this activity
            $workerData = DB::table('rkhlstworker')
                ->where('companycode', $companycode)
                ->where('rkhno', $plot->rkhno)
                ->where('activitycode', $plot->activitycode)
                ->first();
            
            // Combine plot data with worker data
            $result[] = (object)[
                'rkhno' => $plot->rkhno,
                'blok' => $plot->blok,
                'plot' => $plot->plot,
                'luasarea' => $plot->luasarea,
                'jumlahlaki' => $workerData ? ($workerData->jumlahlaki ?? 0) : 0,
                'jumlahperempuan' => $workerData ? ($workerData->jumlahperempuan ?? 0) : 0,
                'jumlahtenagakerja' => $workerData ? ($workerData->jumlahtenagakerja ?? 0) : 0,
                'jenistenagakerja' => $plot->jenistenagakerja,
                'mandor_nama' => $plot->mandor_nama,
                'activityname' => $plot->activityname
            ];
        }
        
        return $result;
    }

    /**
     * Get Alat data for DTH
     */
    private function getAlatData($companycode, $date)
    {
        return DB::table('rkhhdr as h')
            ->join('rkhlst as l', function($join) {
                $join->on('h.rkhno', '=', 'l.rkhno')
                    ->on('h.companycode', '=', 'l.companycode');
            })
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as operator', function($join) use ($companycode) {
                $join->on('l.operatorid', '=', 'operator.tenagakerjaid')
                    ->where('operator.companycode', '=', $companycode);
            })
            ->leftJoin('tenagakerja as helper', function($join) use ($companycode) {
                $join->on('l.helperid', '=', 'helper.tenagakerjaid')
                    ->where('helper.companycode', '=', $companycode);
            })
            ->leftJoin('kendaraan as k', function($join) use ($companycode) {
                $join->on('l.operatorid', '=', 'k.idtenagakerja')
                    ->where('k.companycode', '=', $companycode)
                    ->where('k.isactive', '=', 1);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->where('l.usingvehicle', 1)
            ->select([
                'l.rkhno',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.operatorid',
                'l.helperid',
                'u.name as mandor_nama',
                'a.activityname',
                'operator.nama as operator_nama',
                'helper.nama as helper_nama',
                'k.nokendaraan',
                'k.jenis'
            ])
            ->get()
            ->toArray();
    }

    /**
     * Build material usage query
     */
    private function buildMaterialUsageQuery($companycode, $rkhno)
    {
        return DB::table('usematerialhdr as h')
            ->leftJoin('usemateriallst as l', function($join) {
                $join->on('h.companycode', '=', 'l.companycode')
                     ->on('h.rkhno', '=', 'l.rkhno');
            })
            ->leftJoin('herbisidagroup as hg', 'l.herbisidagroupid', '=', 'hg.herbisidagroupid')
            ->where('h.companycode', $companycode)
            ->where('h.rkhno', $rkhno)
            ->select([
                'h.rkhno',
                'h.totalluas',
                'h.flagstatus',
                'h.createdat',
                'h.inputby',
                'l.itemcode',
                'l.itemname',
                'l.qty',
                'l.unit',
                'l.dosageperha',
                'l.herbisidagroupid',
                'hg.herbisidagroupname'
            ]);
    }

    /**
     * Group material usage data
     */
    private function groupMaterialUsageData($materialUsage)
    {
        return $materialUsage->groupBy('herbisidagroupid')->map(function($items, $groupId) {
            $firstItem = $items->first();
            return [
                'herbisidagroupid' => $groupId,
                'herbisidagroupname' => $firstItem->herbisidagroupname ?? 'Unknown Group',
                'items' => $items->map(function($item) {
                    return [
                        'itemcode' => $item->itemcode,
                        'itemname' => $item->itemname,
                        'qty' => number_format($item->qty, 2),
                        'unit' => $item->unit,
                        'dosageperha' => number_format($item->dosageperha, 2)
                    ];
                })->toArray()
            ];
        })->values();
    }

    /**
     * Execute generate material usage
     */
    private function executeGenerateMaterialUsage($request)
    {
        $rkhno = $request->rkhno;
        $companycode = Session::get('companycode');
        
        // Check if RKH exists and is fully approved
        $rkh = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();
        
        if (!$rkh) {
            return [
                'success' => false,
                'message' => 'RKH tidak ditemukan'
            ];
        }
        
        if (!$this->isRkhFullyApproved($rkh)) {
            return [
                'success' => false,
                'message' => 'RKH belum fully approved'
            ];
        }
        
        $materialUsageGenerator = new MaterialUsageGeneratorService();
        return $materialUsageGenerator->generateMaterialUsageFromRkh($rkhno);
    }

    /**
     * Get jabatan name by ID
     */
    private function getJabatanName($idjabatan)
    {
        $jabatan = DB::table('jabatan')->where('idjabatan', $idjabatan)->first();
        return $jabatan ? $jabatan->namajabatan : 'Unknown';
    }

    /**
     * Get operators with vehicle data
     */
    private function getOperatorsWithVehicles($companycode)
    {
        return Kendaraan::getOperatorsWithVehicles($companycode);
    }

    // Section, Report Operators LKH

    /**
     * Get operators who worked on specific date
     */
    public function getOperatorsForDate(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $companycode = Session::get('companycode');

        try {
            $operators = DB::table('kendaraanbbm as kb')
                ->join('lkhhdr as lh', 'kb.lkhno', '=', 'lh.lkhno')
                ->join('tenagakerja as tk', function($join) use ($companycode) {
                    $join->on('kb.operatorid', '=', 'tk.tenagakerjaid')
                        ->where('tk.companycode', '=', $companycode)
                        ->where('tk.jenistenagakerja', '=', 3); // Operator type
                })
                ->join('kendaraan as k', function($join) use ($companycode) {
                    $join->on('kb.nokendaraan', '=', 'k.nokendaraan')
                        ->where('k.companycode', '=', $companycode);
                })
                ->where('lh.companycode', $companycode)
                ->whereDate('lh.lkhdate', $date)
                ->select([
                    'tk.tenagakerjaid',
                    'tk.nama',
                    'k.nokendaraan',
                    'k.jenis'
                ])
                ->distinct()
                ->orderBy('tk.nama')
                ->get();

            return response()->json([
                'success' => true,
                'operators' => $operators,
                'date' => $date,
                'total_operators' => $operators->count()
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting operators for date {$date}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data operator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate operator report
     */
    public function generateOperatorReport(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'operator_id' => 'required|string'
        ]);
        
        $url = route('input.rencanakerjaharian.operator-report', [
            'date' => $request->date,
            'operator_id' => $request->operator_id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Membuka laporan operator...',
            'redirect_url' => $url
        ]);
    }

    /**
     * Show operator report view
     */
    public function showOperatorReport(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $operatorId = $request->query('operator_id');
        
        if (!$operatorId) {
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Operator ID tidak ditemukan');
        }
        
        return view('input.rencanakerjaharian.lkh-report-operator', [
            'date' => $date,
            'operator_id' => $operatorId
        ]);
    }

    /**
     * Get operator report data
     */
    public function getOperatorReportData(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $operatorId = $request->query('operator_id');
        $companycode = Session::get('companycode');

        try {
            // Get operator basic info
            $operatorInfo = DB::table('tenagakerja as tk')
                ->join('kendaraan as k', function($join) use ($companycode) {
                    $join->on('tk.tenagakerjaid', '=', 'k.idtenagakerja')
                        ->where('k.companycode', '=', $companycode);
                })
                ->where('tk.tenagakerjaid', $operatorId)
                ->where('tk.companycode', $companycode)
                ->where('tk.jenistenagakerja', 3)
                ->select([
                    'tk.tenagakerjaid',
                    'tk.nama as operator_name',
                    'tk.nik',
                    'k.nokendaraan',
                    'k.jenis as vehicle_type'
                ])
                ->first();

            if (!$operatorInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data operator tidak ditemukan'
                ]);
            }

            // Get activities for this operator on this date
            $activities = DB::table('kendaraanbbm as kb')
                ->join('lkhhdr as lh', 'kb.lkhno', '=', 'lh.lkhno')
                ->join('lkhdetailplot as ldp', function($join) {
                    $join->on('lh.lkhno', '=', 'ldp.lkhno')
                        ->on('kb.plot', '=', 'ldp.plot');
                })
                ->join('activity as a', 'lh.activitycode', '=', 'a.activitycode')
                ->where('lh.companycode', $companycode)
                ->whereDate('lh.lkhdate', $date)
                ->where('kb.operatorid', $operatorId)
                ->select([
                    // Time & Duration
                    'kb.jammulai',
                    'kb.jamselesai',
                    DB::raw('TIMEDIFF(kb.jamselesai, kb.jammulai) as durasi_kerja'),
                    
                    // Location & Activity
                    'ldp.blok',
                    'kb.plot',
                    'lh.activitycode',
                    'a.activityname',
                    
                    // Area Data
                    'ldp.luasrkh as luas_rencana_ha',
                    'ldp.luashasil as luas_hasil_ha',
                    
                    // Fuel Data
                    'kb.solar',
                    'kb.hourmeterstart',
                    'kb.hourmeterend',
                    
                    // Reference
                    'lh.lkhno',
                    'lh.rkhno'
                ])
                ->orderBy('kb.jammulai')
                ->orderBy('kb.plot')
                ->get()
                ->map(function($activity) {
                    return [
                        'jam_mulai' => substr($activity->jammulai, 0, 5), // HH:MM format
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
                });

            // Calculate totals
            $totals = [
                'total_activities' => $activities->count(),
                'total_luas_rencana' => $activities->sum(function($activity) {
                    return (float)str_replace(',', '', $activity['luas_rencana_ha']);
                }),
                'total_luas_hasil' => $activities->sum(function($activity) {
                    return (float)str_replace(',', '', $activity['luas_hasil_ha']);
                }),
                'total_solar' => $activities->where('solar_liter', '!=', null)->sum('solar_liter'),
                'total_duration_minutes' => $this->calculateTotalDurationMinutes($activities)
            ];

            // Company info
            $companyInfo = DB::table('company')
                ->where('companycode', $companycode)
                ->select('companycode', 'name')
                ->first();

            return response()->json([
                'success' => true,
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y'),
                'company_info' => $companyInfo ? $companyInfo->companycode . ' - ' . $companyInfo->name : $companycode,
                'operator_info' => $operatorInfo,
                'activities' => $activities->toArray(),
                'totals' => $totals,
                'generated_at' => now()->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting operator report data: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data laporan operator: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate total duration in minutes from activities
     */
    private function calculateTotalDurationMinutes($activities)
    {
        $totalMinutes = 0;
        
        foreach ($activities as $activity) {
            if ($activity['durasi_kerja'] && $activity['durasi_kerja'] !== '00:00') {
                list($hours, $minutes) = explode(':', $activity['durasi_kerja']);
                $totalMinutes += ($hours * 60) + $minutes;
            }
        }
        
        return $totalMinutes;
    }


    /**
     * Handle batch creation for planting activities
     * NEW METHOD: Add to bottom of controller class
     */
    private function handlePlantingActivity($rkhno, $companycode)
    {
        $plantingPlots = DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->get();
        
        if ($plantingPlots->isEmpty()) {
            return [];
        }
        
        $createdBatches = [];
        
        foreach ($plantingPlots as $plotData) {
            try {
                // Tentukan lifecycle status dari plot sebelumnya
                $previousBatch = DB::table('batch')
                    ->where('companycode', $companycode)
                    ->where('plot', $plotData->plot)
                    ->where('isactive', 0)
                    ->orderBy('createdat', 'desc')
                    ->first();
                
                $newLifecycleStatus = 'PC'; // Default
                if ($previousBatch) {
                    $newLifecycleStatus = match($previousBatch->lifecyclestatus) {
                        'PC' => 'RC1',
                        'RC1' => 'RC2',
                        'RC2' => 'RC3',
                        'RC3' => 'PC',
                        default => 'PC'
                    };
                }
                
                // FIXED: Generate batch number dengan batchdate, bukan today
                $batchNo = $this->generateBatchNo($companycode, $plotData->rkhdate);
                
                // Create batch record
                DB::table('batch')->insert([
                    'batchno' => $batchNo,
                    'companycode' => $companycode,
                    'plot' => $plotData->plot,
                    'lifecyclestatus' => $newLifecycleStatus,
                    'plantingrkhno' => $rkhno,
                    'batchdate' => $plotData->rkhdate, // ✅ Pakai rkhdate
                    'tanggalpanen' => null,
                    'batcharea' => $plotData->luasarea,
                    'kodevarietas' => null,
                    'pkp' => null,
                    'lastactivity' => '2.2.7',
                    'isactive' => 1,
                    'inputby' => Auth::user()->userid,
                    'createdat' => now()
                ]);
                
                // Update masterlist
                DB::table('masterlist')->updateOrInsert(
                    ['companycode' => $companycode, 'plot' => $plotData->plot],
                    [
                        'activebatchno' => $batchNo,
                        'isactive' => 1
                    ]
                );
                
                $createdBatches[] = "{$batchNo} (Plot: {$plotData->plot})";
                
                \Log::info("Batch created successfully", [
                    'batchno' => $batchNo,
                    'plot' => $plotData->plot,
                    'lifecycle' => $newLifecycleStatus,
                    'batchdate' => $plotData->rkhdate,
                    'rkhno' => $rkhno
                ]);
                
            } catch (\Exception $e) {
                \Log::error("Failed to create batch for plot {$plotData->plot}: " . $e->getMessage());
                throw $e;
            }
        }
        
        return $createdBatches;
    }

    /**
     * Generate unique batch number
     * Format: BATCH[YYMMDD][SEQUENCE]
     * Example: BATCH240801001
     * Sequence resets per tanggal (batchdate, bukan createdat)
     * 
     * @param string $companycode
     * @param string $batchdate (Format: Y-m-d)
     * @param string $plot (not used, kept for backward compatibility)
     * @return string
     */
    private function generateBatchNo($companycode, $batchdate, $plot = null)
    {
        $date = date('ymd', strtotime($batchdate)); // YYMMDD format
        
        // FIXED: Get sequence for THIS SPECIFIC DATE (not today)
        $sequence = DB::table('batch')
            ->where('companycode', $companycode)
            ->whereDate('batchdate', $batchdate) // ✅ Per batchdate
            ->count() + 1;
        
        // Format: BATCH + YYMMDD + 3-digit sequence
        return "BATCH{$date}" . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check if RKH has planting activities
     * NEW METHOD: Add to bottom of controller class
     */
    private function hasPlantingActivities($rkhno, $companycode)
    {
        return DB::table('rkhlst')
            ->where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->where('activitycode', '2.2.7')
            ->exists();
    }

    /**
     * NEW: Validate plots for planting activities
     * Location: Add to bottom of controller class
     */
    private function validatePlantingPlots($rows, $companycode)
    {
        $errors = [];
        
        foreach ($rows as $index => $row) {
            if (($row['nama'] ?? '') === '2.2.7') {
                $plot = $row['plot'] ?? '';
                
                if ($plot) {
                    // Check if plot already has active batch
                    $activeBatch = DB::table('batch')
                        ->where('companycode', $companycode)
                        ->where('plot', $plot)
                        ->where('status', 'ACTIVE')
                        ->exists();
                    
                    if ($activeBatch) {
                        $errors[] = "Baris " . ($index + 1) . ": Plot {$plot} masih memiliki batch aktif. Tidak dapat ditanam ulang.";
                    }
                }
            }
        }
        
        return $errors;
    }

    /**
     * Get panen info for specific plot
     * API Endpoint untuk info panen batch saat create RKH
     * 
     * @param string $plot
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPanenInfo($plot)
    {
        try {
            $companycode = Session::get('companycode');
            
            // Get active batch for this plot
            $batch = DB::table('masterlist')
                ->join('batch', 'masterlist.activebatchno', '=', 'batch.batchno')
                ->where('masterlist.companycode', $companycode)
                ->where('masterlist.plot', $plot)
                ->where('masterlist.isactive', 1)
                ->where('batch.isactive', 1)
                ->select([
                    'batch.batchno',
                    'batch.lifecyclestatus',
                    'batch.batcharea',
                    'batch.tanggalpanen' // Single field
                ])
                ->first();
            
            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plot tidak memiliki batch aktif'
                ]);
            }
            
            // Calculate STC = batcharea - total sudah panen SAMPAI KEMARIN
            $totalSudahPanen = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lh', function($join) {
                    $join->on('ldp.lkhno', '=', 'lh.lkhno')
                        ->on('ldp.companycode', '=', 'lh.companycode');
                })
                ->where('ldp.companycode', $companycode)
                ->where('ldp.batchno', $batch->batchno)
                ->whereDate('lh.lkhdate', '<', now()->format('Y-m-d'))
                ->sum('ldp.luashasil');
            
            $luasSisa = $batch->batcharea - ($totalSudahPanen ?? 0);
            
            return response()->json([
                'success' => true,
                'batchno' => $batch->batchno,
                'lifecyclestatus' => $batch->lifecyclestatus,
                'tanggalpanen' => $batch->tanggalpanen ? Carbon::parse($batch->tanggalpanen)->format('d/m/Y') : 'Belum Panen',
                'batcharea' => number_format($batch->batcharea, 2),
                'totalsudahpanen' => number_format($totalSudahPanen ?? 0, 2),
                'luassisa' => number_format($luasSisa, 2),
                'plot' => $plot
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Error getting panen info for plot {$plot}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat info panen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate panen plots
     * Check if luas input <= luas sisa and batch exists
     * 
     * @param array $rows
     * @param string $companycode
     * @return array
     */
    private function validatePanenPlots($rows, $companycode)
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
            
            // Get active batch
            $batch = DB::table('masterlist')
                ->join('batch', 'masterlist.activebatchno', '=', 'batch.batchno')
                ->where('masterlist.companycode', $companycode)
                ->where('masterlist.plot', $plot)
                ->where('masterlist.isactive', 1)
                ->where('batch.isactive', 1)
                ->first();
            
            if (!$batch) {
                $errors[] = "Baris " . ($index + 1) . ": Plot {$plot} tidak memiliki batch aktif untuk dipanen.";
                continue;
            }
            
            // Calculate luas sisa (STC)
            $totalSudahPanen = DB::table('lkhdetailplot')
                ->join('lkhhdr', function($join) {
                    $join->on('lkhdetailplot.lkhno', '=', 'lkhhdr.lkhno')
                        ->on('lkhdetailplot.companycode', '=', 'lkhhdr.companycode');
                })
                ->where('lkhdetailplot.companycode', $companycode)
                ->where('lkhdetailplot.batchno', $batch->batchno)
                ->whereDate('lkhhdr.lkhdate', '<', now()->format('Y-m-d'))
                ->sum('lkhdetailplot.luashasil');
            
            $luasSisa = $batch->batcharea - ($totalSudahPanen ?? 0);
            
            if ($luas > $luasSisa) {
                $errors[] = "Baris " . ($index + 1) . ": Luas panen ({$luas} Ha) melebihi luas sisa (" . number_format($luasSisa, 2) . " Ha) untuk plot {$plot}.";
            }
        }
        
        return $errors;
    }
}