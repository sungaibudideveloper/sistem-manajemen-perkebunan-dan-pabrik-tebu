<?php

namespace App\Http\Controllers\Transaction\RencanaKerjaHarian;

use App\Http\Controllers\Controller;
use App\Services\Transaction\RencanaKerjaHarian\RkhService;
use App\Services\UserManagement\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

/**
 * RkhController
 * 
 * Slim controller for RKH (Rencana Kerja Harian) operations
 * Delegates all business logic to RkhService
 */
class RkhController extends Controller
{
    protected RkhService $rkhService;
    protected UserService $userService;

    public function __construct(
        RkhService $rkhService,
        UserService $userService
    ) {
        $this->rkhService = $rkhService;
        $this->userService = $userService;
    }


        /**
     * Display RKH list
     */
    public function index(Request $request)
    {
        $companycode = Session::get('companycode');
        $perPage = (int) $request->input('perPage', 10);
        
        $filters = [
            'search' => $request->input('search'),
            'filter_approval' => $request->input('filter_approval'),
            'filter_status' => $request->input('filter_status'),
            'filter_date' => $request->input('filter_date'),
            'all_date' => $request->input('all_date'),
        ];

        $rkhData = $this->rkhService->getRkhList($companycode, $filters, $perPage);
        
        // Get attendance data
        $absenData = [];
        if ($filters['filter_date']) {
            $absenData = DB::table('absenhdr as h')
                ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
                ->join('tenagakerja as t', function($join) use ($companycode) {
                    $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                         ->where('t.companycode', '=', $companycode);
                })
                ->where('h.companycode', $companycode)
                ->whereDate('h.uploaddate', $filters['filter_date'])
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
        }

        // Get mandors list for create modal
        $mandors = DB::table('user')
            ->where('companycode', $companycode)
            ->where('idjabatan', 5) // Mandor jabatan
            ->where('isactive', 1)
            ->select('userid', 'name')
            ->get();

        return view('transaction.rencanakerjaharian.index', [
            'title' => 'Rencana Kerja Harian',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhData' => $rkhData,
            'search' => $filters['search'],
            'filterApproval' => $filters['filter_approval'],
            'filterStatus' => $filters['filter_status'],
            'filterDate' => $filters['filter_date'] ?? date('Y-m-d'),
            'allDate' => $filters['all_date'],
            'absentenagakerja' => $absenData,
            'mandors' => $mandors,
        ]);
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $companycode = Session::get('companycode');
        $date = $request->query('date', date('Y-m-d'));
        $mandorId = $request->query('mandor_id');

        // Get form data from service
        $formData = $this->rkhService->getCreateFormData($companycode, $date);

        return view('transaction.rencanakerjaharian.create', array_merge([
            'title' => 'Create RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'tanggal' => $date,
            'mandor_id' => $mandorId,
        ], $formData));
    }

    /**
     * Store new RKH
     */
    public function store(Request $request)
    {
        $companycode = Session::get('companycode');

        // Validate request
        $this->validateRkhRequest($request);

        // Create RKH
        $result = $this->rkhService->createRkh($companycode, $request->all());

        return $this->handleResponse($request, $result, 'create');
    }

    /**
     * Show RKH detail
     */
    public function show(string $rkhno)
    {
        $companycode = Session::get('companycode');
        
        try {
            $rkhData = $this->rkhService->getRkhDetail($companycode, $rkhno);
            
            // Get additional data
            $absenData = DB::table('absenhdr as h')
                ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
                ->join('tenagakerja as t', function($join) use ($companycode) {
                    $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                         ->where('t.companycode', '=', $companycode);
                })
                ->where('h.companycode', $companycode)
                ->whereDate('h.uploaddate', $rkhData['header']->rkhdate)
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

            $herbisidaData = DB::table('herbisidagroup')
                ->where('companycode', $companycode)
                ->select('herbisidagroupid', 'herbisidagroupname')
                ->get();

            return view('transaction.rencanakerjaharian.show', [
                'title' => 'Detail RKH',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
                'rkhHeader' => $rkhData['header'],
                'rkhDetails' => $rkhData['details'],
                'workersByActivity' => $rkhData['workers'],
                'kendaraanByActivity' => $rkhData['kendaraan'],
                'absentenagakerja' => $absenData,
                'herbisidagroups' => $herbisidaData,
            ]);

        } catch (\Exception $e) {
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(string $rkhno)
    {
        $companycode = Session::get('companycode');
        
        try {
            $rkhData = $this->rkhService->getRkhDetail($companycode, $rkhno);
            
            // Check if RKH can be edited
            if (!$this->canEditRkh($rkhData['header'])) {
                return redirect()->route('transaction.rencanakerjaharian.index')
                    ->with('error', 'RKH tidak dapat diedit karena sudah disetujui');
            }

            // Get form data
            $formData = $this->rkhService->getCreateFormData($companycode, $rkhData['header']->rkhdate);

            return view('transaction.rencanakerjaharian.edit', array_merge([
                'title' => 'Edit RKH',
                'navbar' => 'Input',
                'nav' => 'Rencana Kerja Harian',
                'rkhHeader' => $rkhData['header'],
                'rkhDetails' => $rkhData['details'],
                'workersByActivity' => $rkhData['workers'],
                'kendaraanByActivity' => $rkhData['kendaraan'],
            ], $formData));

        } catch (\Exception $e) {
            return redirect()->route('transaction.rencanakerjaharian.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update RKH
     */
    public function update(Request $request, string $rkhno)
    {
        $companycode = Session::get('companycode');

        // Validate request
        $this->validateRkhRequest($request);

        // Update RKH
        $result = $this->rkhService->updateRkh($companycode, $rkhno, $request->all());

        return $this->handleResponse($request, $result, 'update', $rkhno);
    }

    /**
     * Delete RKH
     */
    public function destroy(string $rkhno)
    {
        $companycode = Session::get('companycode');
        
        $result = $this->rkhService->deleteRkh($companycode, $rkhno);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get approval detail
     */
    public function getApprovalDetail(string $rkhno)
    {
        try {
            $companycode = Session::get('companycode');
            
            $detail = $this->rkhService->getApprovalDetailFormatted($companycode, $rkhno);

            return response()->json([
                'success' => true,
                'data' => $detail
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting RKH approval detail: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check outstanding RKH
     */
    public function checkOutstandingRKH(Request $request)
    {
        $request->validate([
            'mandor_id' => 'required|string',
            'date' => 'required|date'
        ]);

        $companycode = Session::get('companycode');
        
        $result = $this->rkhService->checkOutstandingRkh(
            $companycode, 
            $request->mandor_id
        );

        return response()->json($result, $result['success'] ? 200 : 403);
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

        $companycode = Session::get('companycode');
        
        $result = $this->rkhService->updateStatus(
            $companycode, 
            $request->rkhno, 
            $request->status
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get pending approvals - FIXED
     */
    public function getPendingApprovals(Request $request)
    {
        $companycode = Session::get('companycode');
        $currentUser = Auth::user();
        
        if (!$currentUser->idjabatan) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak memiliki jabatan yang valid'
            ]);
        }

        $pendingRkh = $this->rkhService->getPendingApprovalsForUser($companycode, $currentUser);

        // Get jabatan name
        $jabatan = DB::table('jabatan')
            ->where('idjabatan', $currentUser->idjabatan)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $pendingRkh->toArray(),
            'user_info' => [
                'userid' => $currentUser->userid,
                'name' => $currentUser->name,
                'idjabatan' => $currentUser->idjabatan,
                'jabatan_name' => $jabatan ? $jabatan->namajabatan : 'Unknown' // ✅ FIXED
            ]
        ]);
    }

    /**
     * Process approval
     */
    public function processApproval(Request $request)
    {
        $request->validate([
            'rkhno' => 'required|string',
            'action' => 'required|in:approve,decline',
            'level' => 'required|integer|between:1,3'
        ]);

        $companycode = Session::get('companycode');
        $currentUser = Auth::user();

        $result = $this->rkhService->processApproval(
            $companycode,
            $request->rkhno,
            $request->level,
            $request->action,
            $currentUser
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get plot info
     */
    public function getPlotInfo(string $plot, string $activitycode)
    {
        try {
            $companycode = Session::get('companycode');
            
            $result = $this->rkhService->getPlotInfo($companycode, $plot, $activitycode);

            return response()->json([
                'success' => true,
                'plot' => $result['plot'] ?? null,
                'blok' => $result['blok'] ?? null,
                'activebatchno' => $result['activebatchno'] ?? null,
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting plot info for {$plot}/{$activitycode}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat info plot: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load absen by date
     */
    public function loadAbsenByDate(Request $request)
    {
        $date = $request->query('date', date('Y-m-d'));
        $mandorId = $request->query('mandor_id');
        $companycode = Session::get('companycode');
        
        // Query data absen yang sudah APPROVED saja
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

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Validate RKH request
     */
    private function validateRkhRequest(Request $request): void
    {
        $request->validate([
            'tanggal' => 'required|date',
            'mandor_id' => 'required|string',
            'keterangan' => 'nullable|string|max:500',
            'rows' => 'required|array|min:1',
            'rows.*.blok' => 'required|string',
            'rows.*.plot' => 'nullable|string',
            'rows.*.nama' => 'required|string',
            'rows.*.luas' => 'required|numeric|min:0',
            'workers' => 'required|array',
        ]);
    }

    /**
     * Handle response
     */
    private function handleResponse(Request $request, array $result, string $action, ?string $rkhno = null)
    {
        if ($request->expectsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            $redirectRoute = $action === 'create' 
                ? route('transaction.rencanakerjaharian.show', $result['rkhno'])
                : route('transaction.rencanakerjaharian.show', $rkhno);

            return redirect($redirectRoute)->with('success', $result['message']);
        }

        return redirect()->back()
            ->withInput()
            ->with('error', $result['message']);
    }

    /**
     * Check if RKH can be edited
     */
    private function canEditRkh(object $rkhHeader): bool
    {
        return !($rkhHeader->approval1flag === '1' || 
                 $rkhHeader->approval2flag === '1' || 
                 $rkhHeader->approval3flag === '1');
    }


    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Filter valid rows
     */
    private function filterValidRows(array $rows): array
    {
        return collect($rows)
            ->filter(function ($row) {
                return !empty($row['blok']);
            })
            ->values()
            ->toArray();
    }


    /**
     * Load create form data
     */
    private function loadCreateFormData(string $companycode, string $selectedDate, string $mandorId): array
    {
        $targetDate = \Carbon\Carbon::parse($selectedDate);
        
        // Generate preview RKH number
        $previewRkhNo = $this->generatePreviewRkhNo($targetDate, $companycode);

        $selectedMandor = \DB::table('user')->where('userid', $mandorId)->first();

        return [
            'title' => 'Form RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhno' => $previewRkhNo,
            'selectedDate' => $targetDate->format('Y-m-d'),
            'selectedMandor' => $selectedMandor,
            'oldInput' => old(),
            // Add other form data (activities, plots, etc)
            'activities' => $this->getActivities(),
            'bloks' => $this->getBloks($companycode),
            'masterlist' => $this->getMasterlist($companycode),
            'tenagaKerja' => $this->getTenagaKerja($companycode),
            'vehiclesData' => $this->getVehicles($companycode),
            'herbisidagroups' => $this->getHerbisidaData($companycode),
            'absentenagakerja' => $this->getAttendanceData($companycode, $selectedDate),
        ];
    }

    /**
     * Load edit form data
     */
    private function loadEditFormData(string $companycode, array $rkhData): array
    {
        return [
            'title' => 'Edit RKH',
            'navbar' => 'Input',
            'nav' => 'Rencana Kerja Harian',
            'rkhHeader' => $rkhData['header'],
            'rkhDetails' => $rkhData['details'],
            'existingWorkers' => $this->formatWorkersForEdit($rkhData['workers']),
            'existingKendaraan' => $rkhData['kendaraan'],
            'oldInput' => old(),
            // Add form data
            'activities' => $this->getActivities(),
            'bloks' => $this->getBloks($companycode),
            'masterlist' => $this->getMasterlist($companycode),
            'tenagaKerja' => $this->getTenagaKerja($companycode),
            'vehiclesData' => $this->getVehicles($companycode),
            'herbisidagroups' => $this->getHerbisidaData($companycode),
        ];
    }

    /**
     * Generate preview RKH number
     */
    private function generatePreviewRkhNo(\Carbon\Carbon $targetDate, string $companycode): string
    {
        $day = $targetDate->format('d');
        $month = $targetDate->format('m');
        $year = $targetDate->format('y');

        $lastRkh = \DB::table('rkhhdr')
            ->where('companycode', $companycode)
            ->whereDate('rkhdate', $targetDate)
            ->where('rkhno', 'like', "RKH{$day}{$month}%{$year}")
            ->orderBy(\DB::raw('CAST(SUBSTRING(rkhno, 8, 2) AS UNSIGNED)'), 'desc')
            ->first();

        $newNumber = $lastRkh 
            ? str_pad(((int)substr($lastRkh->rkhno, 7, 2)) + 1, 2, '0', STR_PAD_LEFT)
            : '01';
            
        return "RKH{$day}{$month}{$newNumber}{$year}";
    }

    /**
     * Get attendance data
     */
    private function getAttendanceData(string $companycode, ?string $date): \Illuminate\Support\Collection
    {
        $date = $date ?? now()->format('Y-m-d');
        
        return \DB::table('absenhdr as h')
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
     * Get attendance data with mandor list
     */
    private function getAttendanceDataWithMandor(string $companycode, string $date, ?string $mandorId): array
    {
        $query = \DB::table('absenhdr as h')
            ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
            ->join('tenagakerja as t', function($join) use ($companycode) {
                $join->on('l.tenagakerjaid', '=', 't.tenagakerjaid')
                     ->where('t.companycode', '=', $companycode)
                     ->where('t.isactive', '=', 1);
            })
            ->where('h.companycode', $companycode)
            ->whereDate('h.uploaddate', $date)
            ->where('l.approval_status', 'APPROVED');
        
        if ($mandorId) {
            $query->where('h.mandorid', $mandorId);
        }

        $absenData = $query->select([
            'h.absenno',
            'h.mandorid', 
            'l.tenagakerjaid',
            't.nama',
            't.nik',
            't.gender',
            't.jenistenagakerja',
            'l.approval_status'
        ])->get();

        $mandorList = \DB::table('absenhdr as h')
            ->join('user as u', 'h.mandorid', '=', 'u.userid')
            ->where('h.companycode', $companycode)
            ->whereDate('h.uploaddate', $date)
            ->select('h.mandorid', 'u.name as mandor_name')
            ->distinct()
            ->get();

        return [
            'data' => $absenData,
            'mandor_list' => $mandorList
        ];
    }

    /**
     * Get activities
     */
    private function getActivities()
    {
        return \DB::table('activity')
            ->where('active', 1)
            ->orderBy('activitycode')
            ->get();
    }

    /**
     * Get bloks
     */
    private function getBloks(string $companycode)
    {
        return \DB::table('blok')
            ->where('companycode', $companycode)
            ->orderBy('blok')
            ->get();
    }

    /**
     * Get masterlist
     */
    private function getMasterlist(string $companycode)
    {
        return \DB::table('masterlist')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->orderBy('plot')
            ->get();
    }

    /**
     * Get tenaga kerja
     */
    private function getTenagaKerja(string $companycode)
    {
        return \DB::table('tenagakerja')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->orderBy('nama')
            ->get();
    }

    /**
     * Get vehicles
     */
    private function getVehicles(string $companycode)
    {
        return \DB::table('kendaraan')
            ->where('companycode', $companycode)
            ->where('isactive', 1)
            ->orderBy('nokendaraan')
            ->get();
    }

    /**
     * Get herbisida data
     */
    private function getHerbisidaData(string $companycode)
    {
        return \DB::table('herbisidagroup')
            ->where('companycode', $companycode)
            ->get();
    }

    /**
     * Format workers for edit
     */
    private function formatWorkersForEdit($workers): array
    {
        return $workers->map(function($worker) {
            return [
                'activitycode' => $worker->activitycode,
                'activityname' => $worker->activityname,
                'jenistenagakerja' => $worker->jenistenagakerja,
                'jenis_nama' => $worker->jenis_nama,
                'jumlahlaki' => $worker->jumlahlaki,
                'jumlahperempuan' => $worker->jumlahperempuan,
                'jumlahtenagakerja' => $worker->jumlahtenagakerja
            ];
        })->toArray();
    }

    /**
     * Format approval detail
     */
    private function formatApprovalDetail(object $rkh): array
    {
        $levels = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $jabatanId = $rkh->{"approval{$i}idjabatan"} ?? null;
            if (!$jabatanId) continue;

            $flag = $rkh->{"approval{$i}flag"} ?? null;
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
                'jabatan_id' => $jabatanId,
                'status' => $status,
                'status_text' => $statusText,
                'date' => $rkh->{"approval{$i}date"} ?? null,
                'user_id' => $rkh->{"approval{$i}userid"} ?? null
            ];
        }

        return [
            'rkhno' => $rkh->rkhno,
            'rkhdate' => $rkh->rkhdate,
            'mandor_nama' => $rkh->mandor_nama ?? '-',
            'activity_group_name' => $rkh->activity_group_name ?? '-',
            'jumlah_approval' => $rkh->jumlahapproval ?? 0,
            'levels' => $levels
        ];
    }



}
