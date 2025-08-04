<?php

namespace App\Http\Controllers\Input;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
use App\Models\Lkhlst;
use App\Models\Kendaraan;
use App\Models\TenagaKerja;

// Services
use App\Services\LkhGeneratorService;
use App\Services\MaterialUsageGeneratorService;

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
     * Display specific RKH record
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
        
        return view('input.rencanakerjaharian.show', [
            'title' => 'Detail RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhHeader' => $rkhHeader,
            'rkhDetails' => $rkhDetails,
            'absentenagakerja' => $absenData,
            'operatorsData' => $operatorsWithVehicles,
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
            
            DB::table('rkhlst')->where('companycode', $companycode)->where('rkhno', $rkhno)->delete();
            $deleted = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->delete();
            
            if ($deleted) {
                DB::commit();
                return response()->json(['success' => true, 'message' => 'RKH berhasil dihapus']);
            } else {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'RKH tidak ditemukan'], 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus RKH: ' . $e->getMessage()], 500);
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
            
            $updated = DB::table('rkhhdr')
                ->where('companycode', $companycode)
                ->where('rkhno', $request->rkhno)
                ->update([
                    'status' => $request->status,
                    'updateby' => Auth::user()->userid,
                    'updatedat' => now()
                ]);

            if ($updated) {
                return response()->json(['success' => true, 'message' => 'Status RKH berhasil diupdate']);
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
     */
    public function getLKHData($rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            \Log::info("Getting LKH data for RKH: {$rkhno}, Company: {$companycode}");
            
            $lkhList = $this->buildLkhDataQuery($companycode, $rkhno)->get();

            \Log::info("Found {$lkhList->count()} LKH records for RKH {$rkhno}");

            $formattedData = $this->formatLkhData($lkhList);
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

            $lkhDetails = $this->getLkhDetailsForShow($lkhno);
            $approvals = $this->getLkhApprovalsData($lkhData);

            return view('input.rencanakerjaharian.lkh-report', [
                'title' => 'Laporan Kegiatan Harian (LKH)',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
                'lkhData' => $lkhData,
                'lkhDetails' => $lkhDetails,
                'approvals' => $approvals
            ]);

        } catch (\Exception $e) {
            \Log::error("Error showing LKH: " . $e->getMessage());
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat menampilkan LKH: ' . $e->getMessage());
        }
    }

    /**
     * Show LKH edit form
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

            $lkhDetails = $this->getLkhDetailsForEdit($lkhno);
            $formData = $this->loadLkhEditFormData($companycode);

            return view('input.rencanakerjaharian.edit-lkh', array_merge([
                'title' => 'Edit LKH',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
                'lkhData' => $lkhData,
                'lkhDetails' => $lkhDetails,
            ], $formData));

        } catch (\Exception $e) {
            \Log::error("Error editing LKH: " . $e->getMessage());
            return redirect()->route('input.rencanakerjaharian.index')
                ->with('error', 'Terjadi kesalahan saat membuka edit LKH: ' . $e->getMessage());
        }
    }

    /**
     * Update LKH record
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
            
            // Update LKH
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
            $formattedData = $this->formatPendingLkhApprovalsData($pendingLKH);

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
     * Get LKH Rekap data
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
                'perawatan_manual' => [
                    'pc' => $this->getPerawatanManualPCData($companycode, $date),
                    'rc' => $this->getPerawatanManualRCData($companycode, $date)
                ]
            ];

            return response()->json([
                'success' => true,
                'company_info' => $companyInfo,
                'pengolahan' => $rekapData['pengolahan'],
                'perawatan_manual' => $rekapData['perawatan_manual'],
                'lkh_numbers' => $lkhNumbers,
                'date' => $date,
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'debug' => [
                    'pengolahan_count' => count($rekapData['pengolahan']),
                    'perawatan_pc_count' => count($rekapData['perawatan_manual']['pc']),
                    'perawatan_rc_count' => count($rekapData['perawatan_manual']['rc']),
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
     * Get Pengolahan data (Activity II, III, IV) - UPDATED: no blok in header
     */
    private function getPengolahanData($companycode, $date)
    {
        $data = DB::table('lkhhdr as h')
            ->leftJoin('lkhlst as l', 'h.lkhno', '=', 'l.lkhno')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as tk', 'l.idtenagakerja', '=', 'tk.tenagakerjaid')
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->where(function($query) {
                $query->where('h.activitycode', 'like', 'II.%')
                    ->orWhere('h.activitycode', 'like', 'III.%')
                    ->orWhere('h.activitycode', 'like', 'IV.%');
            })
            ->select([
                'h.lkhno',
                'h.activitycode',
                // REMOVED: 'h.blok',
                'h.totalworkers',
                'h.totalluasactual',
                'h.totalhasil',
                'u.name as mandor_nama',
                'a.activityname',
                'tk.nama as operator',
                'l.blok', // NOW from lkhlst
                'l.plot'
            ])
            ->orderBy('h.activitycode')
            ->orderBy('h.lkhno')
            ->get();

        // Group by activity code
        return $data->groupBy('activitycode')->toArray();
    }

    /**
     * Get Perawatan Manual PC data - UPDATED: blok from lkhlst
     */
    private function getPerawatanManualPCData($companycode, $date)
    {
        $data = DB::table('lkhhdr as h')
            ->leftJoin('lkhlst as l', 'h.lkhno', '=', 'l.lkhno')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as tk', 'l.idtenagakerja', '=', 'tk.tenagakerjaid')
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->where('h.activitycode', 'like', 'V.%')
            ->where('l.blok', 'like', '%PC%') // NOW from lkhlst
            ->select([
                'h.lkhno',
                'h.activitycode',
                'h.totalworkers',
                'h.totalluasactual',
                'h.totalhasil',
                'u.name as mandor_nama',
                'a.activityname',
                'tk.nama as operator',
                'l.blok', // NOW from lkhlst
                'l.plot'
            ])
            ->orderBy('h.activitycode')
            ->orderBy('h.lkhno')
            ->get();

        return $data->groupBy('activitycode')->toArray();
    }

    /**
     * Get Perawatan Manual RC data - UPDATED: blok from lkhlst
     */
    private function getPerawatanManualRCData($companycode, $date)
    {
        $data = DB::table('lkhhdr as h')
            ->leftJoin('lkhlst as l', 'h.lkhno', '=', 'l.lkhno')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'h.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as tk', 'l.idtenagakerja', '=', 'tk.tenagakerjaid')
            ->where('h.companycode', $companycode)
            ->whereDate('h.lkhdate', $date)
            ->where('h.activitycode', 'like', 'V.%')
            ->where('l.blok', 'like', '%RC%') // NOW from lkhlst
            ->select([
                'h.lkhno',
                'h.activitycode',
                'h.totalworkers',
                'h.totalluasactual',
                'h.totalhasil',
                'u.name as mandor_nama',
                'a.activityname',
                'tk.nama as operator',
                'l.blok', // NOW from lkhlst
                'l.plot'
            ])
            ->orderBy('h.activitycode')
            ->orderBy('h.lkhno')
            ->get();

        return $data->groupBy('activitycode')->toArray();
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
     * Load attendance by date
     */
    public function loadAbsenByDate(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $mandorId = $request->query('mandor_id');
        $companycode = Session::get('companycode');
        
        $absenModel = new AbsenHdr;
        $absenData = $absenModel->getDataAbsenFull($companycode, Carbon::parse($date), $mandorId);
        $mandorList = $absenModel->getMandorList($companycode, Carbon::parse($date));

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
                DB::raw('CASE 
                    WHEN app.jumlahapproval IS NULL OR app.jumlahapproval = 0 THEN "No Approval Required"
                    WHEN r.approval1flag IS NULL AND app.idjabatanapproval1 IS NOT NULL THEN "Waiting"
                    WHEN r.approval1flag = "0" THEN "Declined"
                    WHEN r.approval1flag = "1" AND app.idjabatanapproval2 IS NOT NULL AND r.approval2flag IS NULL THEN "Waiting"
                    WHEN r.approval2flag = "0" THEN "Declined"
                    WHEN r.approval2flag = "1" AND app.idjabatanapproval3 IS NOT NULL AND r.approval3flag IS NULL THEN "Waiting"
                    WHEN r.approval3flag = "0" THEN "Declined"
                    WHEN (app.jumlahapproval = 1 AND r.approval1flag = "1") OR
                         (app.jumlahapproval = 2 AND r.approval1flag = "1" AND r.approval2flag = "1") OR
                         (app.jumlahapproval = 3 AND r.approval1flag = "1" AND r.approval2flag = "1" AND r.approval3flag = "1") THEN "Approved"
                    ELSE "Waiting"
                END as approval_status'),
                DB::raw('CASE 
                    WHEN r.status = "Done" THEN "Done"
                    ELSE "On Progress"
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
        if ($filterStatus == 'Done') {
            $query->where('r.status', 'Done');
        } else {
            $query->where(function($q) {
                $q->where('r.status', '!=', 'Done')->orWhereNull('r.status');
            });
        }

        return $query;
    }

    /**
     * Get attendance data for forms
     */
    private function getAttendanceData($companycode, $date)
    {
        $absenModel = new AbsenHdr;
        return $absenModel->getDataAbsenFull(
            $companycode,
            Carbon::parse($date ?? Carbon::today())
        );
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
            'activities' => Activity::with(['group', 'jenistenagakerja'])->orderBy('activitycode')->get(),
            'bloks' => Blok::orderBy('blok')->get(),
            'masterlist' => Masterlist::orderBy('companycode')->orderBy('plot')->get(),
            'plots' => DB::table('plot')->where('companycode', $companycode)->get(),
            'absentenagakerja' => $absenModel->getDataAbsenFull($companycode, $targetDate),
            'herbisidagroups' => $herbisidadosages->getFullHerbisidaGroupData($companycode),
            'bloksData' => Blok::orderBy('blok')->get(),
            'masterlistData' => Masterlist::orderBy('companycode')->orderBy('plot')->get(),
            'plotsData' => DB::table('plot')->where('companycode', $companycode)->get(),
            'operatorsData' => $this->getOperatorsWithVehicles($companycode),
            'helpersData' => TenagaKerja::where('companycode', $companycode)
                ->where('jenistenagakerja', 4)
                ->where('isactive', 1)
                ->select(['tenagakerjaid', 'nama', 'nik'])
                ->orderBy('nama')
                ->get(),
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
            'rows.*.laki_laki'       => 'required|integer|min:0',
            'rows.*.perempuan'       => 'required|integer|min:0',
            'rows.*.usingvehicle'    => 'required|boolean',
            'rows.*.usinghelper'     => 'required|boolean',
            'rows.*.helperid'        => 'nullable|string',
            'rows.*.material_group_id' => 'nullable|integer',
        ]);
    }

    /**
     * Create RKH record in database
     */
    private function createRkhRecord($request)
    {
        $companycode = Session::get('companycode');
        $tanggal = Carbon::parse($request->input('tanggal'))->format('Y-m-d');

        $rkhno = $this->generateUniqueRkhNoWithLock($tanggal);

        $totalLuas = collect($request->rows)->sum('luas');
        $totalManpower = collect($request->rows)->sum(function ($row) {
            return ((int) ($row['laki_laki'] ?? 0)) + ((int) ($row['perempuan'] ?? 0));
        });

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

        $totalLuas = collect($request->rows)->sum('luas');
        $totalManpower = collect($request->rows)->sum(function ($row) {
            return ((int) ($row['laki_laki'] ?? 0)) + ((int) ($row['perempuan'] ?? 0));
        });

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
        foreach ($rows as $row) {
            $laki = (int) ($row['laki_laki'] ?? 0);
            $perempuan = (int) ($row['perempuan'] ?? 0);
            $activity = Activity::where('activitycode', $row['nama'])->first();
            $jenistenagakerja = $activity ? $activity->jenistenagakerja : null;

            $details[] = [
                'companycode'         => $companycode,
                'rkhno'               => $rkhno,
                'rkhdate'             => $tanggal,
                'blok'                => $row['blok'],
                'plot'                => $row['plot'],
                'activitycode'        => $row['nama'],
                'luasarea'            => $row['luas'],
                'jumlahlaki'          => $laki,
                'jumlahperempuan'     => $perempuan,
                'jumlahtenagakerja'   => $laki + $perempuan,
                'jenistenagakerja'    => $jenistenagakerja,
                'usingmaterial'       => !empty($row['material_group_id']) ? 1 : 0,
                'herbisidagroupid'    => !empty($row['material_group_id']) ? (int) $row['material_group_id'] : null,
                'usingvehicle'        => $row['usingvehicle'],
                'operatorid'          => !empty($row['operatorid']) ? $row['operatorid'] : null,
                'usinghelper'         => $row['usinghelper'] ?? 0,
                'helperid'            => !empty($row['helperid']) ? $row['helperid'] : null,
            ];
        }
        return $details;
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
     */
    private function getRkhDetails($companycode, $rkhno)
    {
        return DB::table('rkhlst as r')
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as tk', 'r.operatorid', '=', 'tk.tenagakerjaid')
            ->leftJoin('tenagakerja as tk_helper', 'r.helperid', '=', 'tk_helper.tenagakerjaid')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*', 
                'hg.herbisidagroupname', 
                'a.activityname', 
                'a.jenistenagakerja',
                'tk.nama as operator_name',
                'tk.nik as operator_nik',
                'tk_helper.nama as helper_name'
            ])
            ->get();
    }

    /**
     * Get RKH details for edit
     */
    private function getRkhDetailsForEdit($companycode, $rkhno)
    {
        return DB::table('rkhlst as r')
            ->leftJoin('herbisidagroup as hg', function($join) {
                $join->on('r.herbisidagroupid', '=', 'hg.herbisidagroupid')
                    ->on('r.activitycode', '=', 'hg.activitycode');
            })
            ->leftJoin('activity as a', 'r.activitycode', '=', 'a.activitycode')
            ->leftJoin('tenagakerja as tk_operator', 'r.operatorid', '=', 'tk_operator.tenagakerjaid')
            ->leftJoin('tenagakerja as tk_helper', 'r.helperid', '=', 'tk_helper.tenagakerjaid')
            ->where('r.companycode', $companycode)
            ->where('r.rkhno', $rkhno)
            ->select([
                'r.*', 
                'hg.herbisidagroupname', 
                'a.activityname', 
                'a.jenistenagakerja',
                'tk_operator.nama as operator_name',
                'tk_helper.nama as helper_name'
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
     * Execute approval process
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
            return ['success' => false, 'message' => 'RKH tidak ditemukan'];
        }

        // Validate approval authority
        $validationResult = $this->validateApprovalAuthority($rkh, $currentUser, $level);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        // Process approval
        $approvalValue = $action === 'approve' ? '1' : '0';
        $approvalField = "approval{$level}flag";
        $approvalDateField = "approval{$level}date";
        $approvalUserField = "approval{$level}userid";
        
        DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->update([
            $approvalField => $approvalValue,
            $approvalDateField => now(),
            $approvalUserField => $currentUser->userid,
            'updateby' => $currentUser->userid,
            'updatedat' => now()
        ]);

        $responseMessage = 'RKH berhasil ' . ($action === 'approve' ? 'disetujui' : 'ditolak');

        // Handle post-approval actions
        if ($action === 'approve') {
            $responseMessage = $this->handlePostApprovalActions($rkhno, $responseMessage);
        }

        return ['success' => true, 'message' => $responseMessage];
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
     * Handle post-approval actions (LKH and Material generation)
     */
    private function handlePostApprovalActions($rkhno, $responseMessage)
    {
        $companycode = Session::get('companycode');
        $updatedRkh = DB::table('rkhhdr')->where('companycode', $companycode)->where('rkhno', $rkhno)->first();

        if ($this->isRkhFullyApproved($updatedRkh)) {
            // Generate LKH
            try {
                $lkhGenerator = new LkhGeneratorService();
                $lkhResult = $lkhGenerator->generateLkhFromRkh($rkhno);
                
                if ($lkhResult['success']) {
                    $responseMessage .= '. LKH telah di-generate otomatis (' . $lkhResult['total_lkh'] . ' LKH)';
                } else {
                    $responseMessage .= '. WARNING: Gagal auto-generate LKH - ' . $lkhResult['message'];
                }
            } catch (\Exception $e) {
                \Log::error("Exception during LKH auto-generation for RKH {$rkhno}: " . $e->getMessage());
                $responseMessage .= '. WARNING: Error saat auto-generate LKH';
            }
            
            // Generate Material Usage
            try {
                $materialUsageGenerator = new MaterialUsageGeneratorService();
                $materialResult = $materialUsageGenerator->generateMaterialUsageFromRkh($rkhno);
                
                if ($materialResult['success']) {
                    if ($materialResult['total_items'] > 0) {
                        $responseMessage .= '. Material usage berhasil di-generate (' . $materialResult['total_items'] . ' items)';
                    } else {
                        $responseMessage .= '. Info: Tidak ada material yang perlu di-generate';
                    }
                } else {
                    $responseMessage .= '. WARNING: Gagal generate material usage - ' . $materialResult['message'];
                }
            } catch (\Exception $e) {
                \Log::error("Exception during Material Usage generation for RKH {$rkhno}: " . $e->getMessage());
                $responseMessage .= '. WARNING: Error saat generate material usage';
            }
        }

        return $responseMessage;
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
     * Build LKH data query - UPDATED: no blok in header
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
                // REMOVED: 'h.blok',
                'h.jenistenagakerja',
                'h.status',
                'h.lkhdate',
                'h.totalworkers',
                'h.totalhasil',
                'h.totalsisa',
                'h.createdat',
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
     * Format LKH data for response - UPDATED: show plots from lkhlst
     */
    private function formatLkhData($lkhList)
    {
        return $lkhList->map(function($lkh) {
            $approvalStatus = $this->calculateLKHApprovalStatus($lkh);
            $canEdit = !$lkh->issubmit && !$this->isLKHFullyApproved($lkh);
            $canSubmit = !$lkh->issubmit && in_array($lkh->status, ['COMPLETED', 'DRAFT']) && !$this->isLKHFullyApproved($lkh);

            // Get plots for this LKH from lkhlst
            $plots = DB::table('lkhlst')
                ->where('lkhno', $lkh->lkhno)
                ->select('blok', 'plot')
                ->get()
                ->map(function($item) {
                    return $item->blok . '-' . $item->plot;
                })
                ->unique()
                ->join(', ');

            return [
                'lkhno' => $lkh->lkhno,
                'activity' => $lkh->activitycode . ' - ' . ($lkh->activityname ?? 'Unknown Activity'),
                'plots' => $plots ?: 'No plots assigned', // CHANGED: from single blok to multiple plots
                'jenis_tenaga' => $lkh->jenistenagakerja == 1 ? 'Harian' : 'Borongan',
                'status' => $lkh->status ?? 'EMPTY',
                'approval_status' => $approvalStatus,
                'issubmit' => (bool) $lkh->issubmit,
                'date_formatted' => $lkh->lkhdate ? Carbon::parse($lkh->lkhdate)->format('d/m/Y') : '-',
                'created_at' => $lkh->createdat ? Carbon::parse($lkh->createdat)->format('d/m/Y H:i') : '-',
                'submit_info' => $lkh->submitat ? 'Submitted at ' . Carbon::parse($lkh->submitat)->format('d/m/Y H:i') : null,
                'can_edit' => $canEdit,
                'can_submit' => $canSubmit,
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
     * Get LKH details for show
     */
    private function getLkhDetailsForShow($lkhno)
    {
        return DB::table('lkhlst as l')
            ->leftJoin('tenagakerja as t', 'l.idtenagakerja', '=', 't.tenagakerjaid')
            ->where('l.lkhno', $lkhno)
            ->select([
                'l.*',
                't.nama as workername',
                't.nik as noktp'
            ])
            ->orderBy('l.tenagakerjaurutan')
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
     * Get LKH details for edit
     */
    private function getLkhDetailsForEdit($lkhno)
    {
        return DB::table('lkhlst as l')
            ->leftJoin('tenagakerja as t', 'l.idtenagakerja', '=', 't.tenagakerjaid')
            ->where('l.lkhno', $lkhno)
            ->select([
                'l.*',
                't.nama as workername',
                't.nik as noktp'
            ])
            ->orderBy('l.tenagakerjaurutan')
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
     */
    private function validateLkhUpdateRequest($request)
    {
        $request->validate([
            'keterangan' => 'nullable|string|max:500',
            'workers' => 'required|array|min:1',
            'workers.*.tenagakerjaid' => 'required|string',
            'workers.*.blok' => 'required|string',
            'workers.*.plot' => 'required|string',
            'workers.*.luasplot' => 'required|numeric|min:0',
            'workers.*.hasil' => 'required|numeric|min:0',
            'workers.*.sisa' => 'required|numeric|min:0',
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
     * Update LKH record
     */
    private function updateLkhRecord($request, $lkhno, $lkhData, $currentUser)
    {
        $companycode = Session::get('companycode');
        
        // Calculate totals
        $totalWorkers = count($request->workers);
        $totalHasil = collect($request->workers)->sum('hasil');
        $totalSisa = collect($request->workers)->sum('sisa');
        $totalUpah = $this->calculateTotalUpah($request->workers, $lkhData);

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

        // Update details
        DB::table('lkhlst')->where('lkhno', $lkhno)->delete();
        $details = $this->buildLkhDetails($request->workers, $lkhno, $lkhData);
        DB::table('lkhlst')->insert($details);
    }

    /**
     * Calculate total upah for LKH
     */
    private function calculateTotalUpah($workers, $lkhData)
    {
        $totalUpah = 0;

        foreach ($workers as $worker) {
            if ($lkhData->jenistenagakerja == 1) {
                // Harian: upah harian + premi + overtime
                $upahHarian = $worker['upahharian'] ?? 0;
                $premi = $worker['premi'] ?? 0;
                $totalUpah += $upahHarian + $premi;
            } else {
                // Borongan: hasil * cost per ha
                $hasil = $worker['hasil'] ?? 0;
                $costPerHa = $worker['costperha'] ?? 0;
                $totalUpah += $hasil * $costPerHa;
            }
        }

        return $totalUpah;
    }

    /**
     * Build LKH detail records
     */
    private function buildLkhDetails($workers, $lkhno, $lkhData)
    {
        $details = [];
        foreach ($workers as $index => $worker) {
            $detail = [
                'lkhno' => $lkhno,
                'tenagakerjaurutan' => $index + 1,
                'tenagakerjaid' => $worker['tenagakerjaid'],
                'blok' => $worker['blok'],
                'plot' => $worker['plot'],
                'luasplot' => $worker['luasplot'],
                'hasil' => $worker['hasil'],
                'sisa' => $worker['sisa'],
                'materialused' => $worker['materialused'] ?? null,
                'createdat' => now()
            ];

            if ($lkhData->jenistenagakerja == 1) {
                // Tenaga Harian fields
                $detail['jammasuk'] = $worker['jammasuk'] ?? null;
                $detail['jamselesai'] = $worker['jamselesai'] ?? null;
                $detail['overtimehours'] = $worker['overtimehours'] ?? 0;
                $detail['premi'] = $worker['premi'] ?? 0;
                $detail['upahharian'] = $worker['upahharian'] ?? 0;
                $detail['totalupahharian'] = ($worker['upahharian'] ?? 0) + ($worker['premi'] ?? 0);
                $detail['costperha'] = 0;
                $detail['totalbiayaborongan'] = 0;
            } else {
                // Tenaga Borongan fields
                $detail['jammasuk'] = null;
                $detail['jamselesai'] = null;
                $detail['overtimehours'] = 0;
                $detail['premi'] = 0;
                $detail['upahharian'] = 0;
                $detail['totalupahharian'] = 0;
                $detail['costperha'] = $worker['costperha'] ?? 0;
                $detail['totalbiayaborongan'] = ($worker['hasil'] ?? 0) * ($worker['costperha'] ?? 0);
            }

            $details[] = $detail;
        }

        return $details;
    }

    /**
     * Execute LKH submission
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
            'status' => 'SUBMITTED',
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
     */
    private function formatPendingLkhApprovalsData($pendingLKH)
    {
        return $pendingLKH->map(function($lkh) {
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
                'blok' => $lkh->blok,
                'plot' => $lkh->plot
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
            'blok' => $lkh->blok,
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
        return DB::table('rkhhdr as h')
            ->join('rkhlst as l', 'h.rkhno', '=', 'l.rkhno')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->whereIn('l.jenistenagakerja', [1, 3]) // Harian + Operator
            ->select([
                'l.rkhno',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.jumlahlaki',
                'l.jumlahperempuan',
                'l.jumlahtenagakerja',
                'l.jenistenagakerja',
                'u.name as mandor_nama',
                'a.activityname'
            ])
            ->get()
            ->toArray();
    }

    /**
     * Get Borongan data for DTH
     */
    private function getBoronganData($companycode, $date)
    {
        return DB::table('rkhhdr as h')
            ->join('rkhlst as l', 'h.rkhno', '=', 'l.rkhno')
            ->leftJoin('user as u', 'h.mandorid', '=', 'u.userid')
            ->leftJoin('activity as a', 'l.activitycode', '=', 'a.activitycode')
            ->where('h.companycode', $companycode)
            ->whereDate('h.rkhdate', $date)
            ->where('l.jenistenagakerja', 2) // Borongan
            ->select([
                'l.rkhno',
                'l.blok',
                'l.plot',
                'l.luasarea',
                'l.jumlahlaki',
                'l.jumlahperempuan',
                'l.jumlahtenagakerja',
                'l.jenistenagakerja',
                'u.name as mandor_nama',
                'a.activityname'
            ])
            ->get()
            ->toArray();
    }

    /**
     * Get Alat data for DTH
     */
    private function getAlatData($companycode, $date)
    {
        return DB::table('rkhhdr as h')
            ->join('rkhlst as l', 'h.rkhno', '=', 'l.rkhno')
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
}