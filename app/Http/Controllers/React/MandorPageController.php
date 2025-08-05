<?php

namespace App\Http\Controllers\React;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

// Models
use App\Models\User;
use App\Models\TenagaKerja;
use App\Models\AbsenHdr;
use App\Models\AbsenLst;
use App\Models\Lkhhdr;
use App\Models\Lkhlst;
use App\Models\Rkhhdr;
use App\Models\Rkhlst;
use App\Models\Activity;
use App\Models\Kendaraan;
use App\Models\usematerialhdr;
use App\Models\usemateriallst;

class MandorPageController extends Controller
{
    // =============================================================================
    // MAIN DASHBOARD & ENTRY POINTS
    // =============================================================================

    /**
     * Main SPA Dashboard entry point
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        return Inertia::render('index', [
            'title' => 'Mandor Dashboard',
            'user' => [
                'id' => $user->userid,
                'name' => $user->name,
            ],
            'csrf_token' => csrf_token(),
            'routes' => [
                'logout' => route('logout'),
                'home' => route('home'),
                'mandor_index' => route('mandor.index'),
                
                // Attendance routes
                'workers' => route('mandor.workers'),
                'attendance_today' => route('mandor.attendance.today'),  
                'process_checkin' => route('mandor.attendance.process-checkin'),
                
                // Field Collection routes
                'lkh_ready' => route('mandor.lkh.ready'),
                'materials_available' => route('mandor.materials.available'),
                'lkh_vehicle_info' => route('mandor.lkh.vehicle-info'),
                'lkh_assign' => route('mandor.lkh.assign', ['lkhno' => '__LKHNO__']),
                
                // Material management routes
                'materials_save_returns' => route('mandor.materials.save-returns'),
                'material_confirm_pickup' => route('mandor.materials.confirm-pickup'),
                
                // Complete all LKH route
                'complete_all_lkh' => route('mandor.lkh.complete-all'),
                
                // Sync routes
                'sync_offline_data' => route('mandor.sync-offline-data'),
            ],
        ]);
    }

    // =============================================================================
    // WORKER & ATTENDANCE MANAGEMENT
    // =============================================================================

    /**
     * Get workers list for current mandor
     */
    public function getWorkersList()
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            
            $workers = TenagaKerja::where('mandoruserid', $user->userid)
                ->where('companycode', $user->companycode)
                ->where('isactive', 1)
                ->select(['tenagakerjaid', 'nama', 'nik', 'gender', 'jenistenagakerja'])
                ->orderBy('nama')
                ->get();
            
            return response()->json(['workers' => $workers]);
            
        } catch (\Exception $e) {
            Log::error('Error in getWorkersList', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get attendance for specific date
     */
    public function getTodayAttendance(Request $request)
    {
        try {
            $date = $request->input('date', now()->format('Y-m-d'));
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            
            $attendance = AbsenLst::getAttendanceByMandorAndDate($user->companycode, $user->userid, $date)
                ->map(function($record) {
                    return [
                        'tenagakerjaid' => $record->tenagakerjaid,
                        'absenmasuk' => $record->absenmasuk,
                        'foto_base64' => $record->fotoabsen,
                        'lokasi_lat' => $record->lokasifotolat,
                        'lokasi_lng' => $record->lokasifotolng,
                        'tenaga_kerja' => [
                            'nama' => $record->nama,
                            'nik' => $record->nik,
                            'gender' => $record->gender,
                            'jenistenagakerja' => $record->jenistenagakerja
                        ]
                    ];
                });
            
            return response()->json([
                'attendance' => $attendance->toArray(),
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getTodayAttendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process check-in with photo
     */
    public function processCheckIn(Request $request)
    {
        try {
            $request->validate([
                'tenagakerjaid' => 'required|string',
                'photo' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $today = now()->format('Y-m-d');
            
            // Check if worker exists and belongs to this mandor
            $worker = TenagaKerja::where('tenagakerjaid', $request->tenagakerjaid)
                ->where('mandoruserid', $user->userid)
                ->where('companycode', $user->companycode)
                ->where('isactive', 1)
                ->first();
                
            if (!$worker) {
                return response()->json(['error' => 'Pekerja tidak ditemukan atau tidak terdaftar pada mandor ini'], 404);
            }
            
            // Check if already checked in today
            if (AbsenLst::hasCheckedInToday($user->companycode, $user->userid, $request->tenagakerjaid, $today)) {
                return response()->json(['error' => 'Pekerja sudah absen hari ini'], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // Find or create AbsenHdr for mandor today
                $absenHdr = AbsenHdr::where('companycode', $user->companycode)
                    ->where('mandorid', $user->userid)
                    ->whereDate('uploaddate', $today)
                    ->first();
                
                if (!$absenHdr) {
                    $absenNo = $this->generateAbsenNo($user->userid, $today);
                    
                    $absenHdr = AbsenHdr::create([
                        'absenno' => $absenNo,
                        'companycode' => $user->companycode,
                        'mandorid' => $user->userid,
                        'totalpekerja' => 1,
                        'status' => 'P',
                        'uploaddate' => now(),
                        'updateBy' => $user->name
                    ]);
                    $nextId = 1;
                } else {
                    $nextId = $absenHdr->totalpekerja + 1;
                    
                    DB::table('absenhdr')
                        ->where('absenno', $absenHdr->absenno)
                        ->where('companycode', $user->companycode)
                        ->increment('totalpekerja');
                    
                    DB::table('absenhdr')
                        ->where('absenno', $absenHdr->absenno)
                        ->where('companycode', $user->companycode)
                        ->update(['updateBy' => $user->name]);
                    
                    $absenHdr->refresh();
                }
                
                // Create AbsenLst record
                DB::table('absenlst')->insert([
                    'absenno' => $absenHdr->absenno,
                    'id' => $nextId,
                    'tenagakerjaid' => $request->tenagakerjaid,
                    'absenmasuk' => now(),
                    'keterangan' => 'Absen dengan foto via mobile app',
                    'fotoabsen' => $request->photo,
                    'lokasifotolat' => $request->latitude,
                    'lokasifotolng' => $request->longitude,
                    'createdat' => now(),
                    'updatedat' => now()
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Absen berhasil dicatat dengan foto',
                    'data' => [
                        'absenno' => $absenHdr->absenno,
                        'tenagakerjaid' => $request->tenagakerjaid,
                        'worker_name' => $worker->nama,
                        'time' => now()->format('H:i'),
                        'total_today' => $absenHdr->totalpekerja,
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in processCheckIn', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    // =============================================================================
    // LKH MANAGEMENT - DATA RETRIEVAL
    // =============================================================================

    /**
     * Get ready LKH list with mobile_status included
     */
    public function getReadyLKH(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date', now()->format('Y-m-d'));
            
            $lkhRecords = DB::table('lkhhdr as lkh')
                ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->leftJoin('usematerialhdr as umh', 'lkh.rkhno', '=', 'umh.rkhno')
                ->where('lkh.companycode', $user->companycode)
                ->where('lkh.mandorid', $user->userid)
                ->whereDate('lkh.lkhdate', $date)
                ->where('lkh.status', '!=', 'COMPLETED')
                ->select([
                    'lkh.lkhno', 'lkh.activitycode', 'act.activityname', 'act.description as activity_description',
                    'lkh.totalluasactual', 'lkh.jenistenagakerja', 'lkh.status as lkh_status',
                    'lkh.totalworkers as estimated_workers', 'lkh.rkhno', 'lkh.mobile_status',
                    'umh.flagstatus as material_status'
                ])
                ->get();
            
            $groupedLKH = [];
            
            foreach ($lkhRecords as $lkhRecord) {
                // Get plot data
                $plotData = DB::table('lkhdetailplot')
                    ->where('companycode', $user->companycode)
                    ->where('lkhno', $lkhRecord->lkhno)
                    ->select(['blok', 'plot', 'luasrkh'])
                    ->get();
                
                // Check if needs material
                $needsMaterial = DB::table('rkhlst as rls')
                    ->where('rls.companycode', $user->companycode)
                    ->where('rls.rkhno', $lkhRecord->rkhno)
                    ->where('rls.activitycode', $lkhRecord->activitycode)
                    ->where('rls.usingmaterial', 1)
                    ->exists();
                
                // Materials ready when mandor has confirmed receipt
                $materialsReady = true;
                if ($needsMaterial) {
                    $materialsReady = ($lkhRecord->material_status === 'RECEIVED_BY_MANDOR');
                }
                
                // Determine work status
                $workStatus = 'READY';
                if ($needsMaterial && !$materialsReady) {
                    $workStatus = 'WAITING_MATERIAL';
                }
                
                $groupedLKH[] = [
                    'lkhno' => $lkhRecord->lkhno,
                    'activitycode' => $lkhRecord->activitycode,
                    'activityname' => $lkhRecord->activityname,
                    'blok' => $plotData->isNotEmpty() ? $plotData->first()->blok : 'N/A',
                    'plot' => $plotData->pluck('plot')->unique()->values()->toArray(),
                    'totalluasplan' => (float) $plotData->sum('luasrkh'),
                    'jenistenagakerja' => $this->getJenisTenagaKerjaName($lkhRecord->jenistenagakerja),
                    'status' => $workStatus,
                    'mobile_status' => $lkhRecord->mobile_status ?: 'EMPTY',
                    'estimated_workers' => (int) $lkhRecord->estimated_workers,
                    'materials_ready' => $materialsReady,
                    'needs_material' => $needsMaterial,
                ];
            }
            
            return response()->json([
                'lkh_list' => $groupedLKH,
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getReadyLKH', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Complete All LKH - Update all DRAFT to COMPLETED
     */
    public function completeAllLKH(Request $request)
    {
        try {
            $request->validate(['date' => 'required|date']);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date');
            
            DB::beginTransaction();
            
            try {
                // Get all DRAFT LKH for this mandor and date
                $draftLKH = DB::table('lkhhdr')
                    ->where('companycode', $user->companycode)
                    ->where('mandorid', $user->userid)
                    ->whereDate('lkhdate', $date)
                    ->where('mobile_status', 'DRAFT')
                    ->pluck('lkhno');
                
                if ($draftLKH->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada LKH dengan status DRAFT untuk diselesaikan'
                    ]);
                }
                
                // Update all DRAFT LKH to COMPLETED
                $updatedCount = DB::table('lkhhdr')
                    ->where('companycode', $user->companycode)
                    ->where('mandorid', $user->userid)
                    ->whereDate('lkhdate', $date)
                    ->where('mobile_status', 'DRAFT')
                    ->update([
                        'mobile_status' => 'COMPLETED',
                        'issubmit' => 1,
                        'submitby' => $user->name,
                        'submitat' => now(),
                        'updateby' => $user->name,
                        'mobileupdatedat' => now()
                    ]);
                
                // Calculate and update total material usage
                $this->calculateTotalMaterialUsage($user->companycode, $user->userid, $date);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menyelesaikan {$updatedCount} LKH",
                    'completed_lkh' => $draftLKH->toArray()
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in completeAllLKH', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================================================
    // LKH MANAGEMENT - PAGE RENDERING
    // =============================================================================

    /**
     * Show LKH Assignment Page
     */
    public function showLKHAssign($lkhno)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            
            // Get LKH data with activity join
            $lkhData = DB::table('lkhhdr as lkh')
                ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->where('lkh.companycode', $user->companycode)
                ->where('lkh.mandorid', $user->userid)
                ->where('lkh.lkhno', $lkhno)
                ->select([
                    'lkh.lkhno', 'lkh.activitycode', 'act.activityname', 'act.description as activity_description',
                    'lkh.jenistenagakerja', 'lkh.totalworkers as estimated_workers', 'lkh.rkhno',
                    'lkh.lkhdate', 'u.name as mandor_nama'
                ])
                ->first();
            
            if (!$lkhData) {
                return redirect()->route('mandor.index')->with('error', 'LKH tidak ditemukan');
            }
            
            // Get plot data
            $plotData = DB::table('lkhdetailplot')
                ->where('companycode', $user->companycode)
                ->where('lkhno', $lkhno)
                ->select('blok', 'plot', 'luasrkh')
                ->get();
            
            // Get vehicle info and available workers
            $vehicleInfo = $this->getVehicleInfoForLKH($lkhno);
            $availableWorkers = $this->getAvailableWorkersForAssignment($user->companycode, $user->userid, $lkhData->lkhdate);
            
            // Check existing assignments
            $existingAssignments = DB::table('lkhdetailworker')
                ->where('lkhno', $lkhno)
                ->where('companycode', $user->companycode)
                ->distinct()
                ->pluck('tenagakerjaid')
                ->toArray();
            
            return Inertia::render('lkh-assignment', [
                'title' => 'Assignment Pekerja - ' . $lkhno,
                'lkhData' => [
                    'lkhno' => $lkhData->lkhno,
                    'activitycode' => $lkhData->activitycode,
                    'activityname' => $lkhData->activityname,
                    'blok' => $plotData->isNotEmpty() ? $plotData->first()->blok : 'N/A',
                    'plot' => $plotData->pluck('plot')->toArray(),
                    'totalluasplan' => (float) $plotData->sum('luasrkh'),
                    'jenistenagakerja' => $this->getJenisTenagaKerjaName($lkhData->jenistenagakerja),
                    'estimated_workers' => (int) $lkhData->estimated_workers,
                    'rkhno' => $lkhData->rkhno,
                    'lkhdate' => $lkhData->lkhdate,
                    'mandor_nama' => $lkhData->mandor_nama
                ],
                'vehicleInfo' => $vehicleInfo,
                'availableWorkers' => $availableWorkers,
                'existingAssignments' => $existingAssignments,
                'routes' => [
                    'lkh_save_assignment' => route('mandor.lkh.save-assignment', $lkhno),
                    'lkh_input' => route('mandor.lkh.input', $lkhno),
                    'mandor_index' => route('mandor.index'),
                ],
                'csrf_token' => csrf_token(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in showLKHAssign', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('mandor.index')->with('error', 'Terjadi kesalahan saat membuka halaman assignment');
        }
    }

    /**
     * Show LKH Input Page
     */
    public function showLKHInput($lkhno)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            
            // Check LKH status
            $lkhStatus = DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->where('companycode', $user->companycode)
                ->where('mandorid', $user->userid)
                ->value('mobile_status');
            
            if ($lkhStatus === 'DRAFT') {
                return redirect()->route('mandor.lkh.view', $lkhno);
            }
            
            if ($lkhStatus === 'COMPLETED') {
                return redirect()->route('mandor.index')->with('error', 'LKH sudah selesai dan tidak bisa diubah');
            }
            
            return $this->renderLKHForm($lkhno, 'input');
            
        } catch (\Exception $e) {
            Log::error('Error in showLKHInput', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('mandor.index')->with('error', 'Terjadi kesalahan saat membuka halaman input');
        }
    }

    /**
     * Show LKH View Page
     */
    public function showLKHView($lkhno)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            
            // Check if LKH exists and belongs to this mandor
            $lkhStatus = DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->where('companycode', $user->companycode)
                ->where('mandorid', $user->userid)
                ->value('mobile_status');
            
            if (!$lkhStatus) {
                return redirect()->route('mandor.index')->with('error', 'LKH tidak ditemukan');
            }
            
            if ($lkhStatus !== 'DRAFT') {
                return redirect()->route('mandor.index')->with('error', 'LKH tidak dalam status draft');
            }
            
            return $this->renderLKHForm($lkhno, 'view');
            
        } catch (\Exception $e) {
            Log::error('Error in showLKHView', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('mandor.index')->with('error', 'Terjadi kesalahan saat membuka halaman view');
        }
    }

    /**
     * Show LKH Edit Page
     */
    public function showLKHEdit($lkhno)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            
            // Check if LKH exists and is editable
            $lkhStatus = DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->where('companycode', $user->companycode)
                ->where('mandorid', $user->userid)
                ->value('mobile_status');
            
            if (!$lkhStatus) {
                return redirect()->route('mandor.index')->with('error', 'LKH tidak ditemukan');
            }
            
            if ($lkhStatus !== 'DRAFT') {
                return redirect()->route('mandor.lkh.view', $lkhno)->with('error', 'LKH tidak bisa diedit karena sudah selesai');
            }
            
            return $this->renderLKHForm($lkhno, 'edit');
            
        } catch (\Exception $e) {
            Log::error('Error in showLKHEdit', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('mandor.index')->with('error', 'Terjadi kesalahan saat membuka halaman edit');
        }
    }

    // =============================================================================
    // LKH MANAGEMENT - DATA PROCESSING
    // =============================================================================

    /**
     * Save LKH Worker Assignment
     */
    public function saveLKHAssign(Request $request, $lkhno)
    {
        try {
            $request->validate([
                'assigned_workers' => 'required|array|min:1',
                'assigned_workers.*.tenagakerjaid' => 'required|string',
                'assigned_workers.*.nama' => 'required|string',
                'assigned_workers.*.nik' => 'required|string',
            ]);
            
            if (!auth()->check()) {
                return back()->withErrors(['message' => 'User not authenticated']);
            }
            
            $user = auth()->user();
            $assignedWorkers = $request->input('assigned_workers');
            
            // Verify LKH exists and belongs to this mandor
            $lkhExists = DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->where('companycode', $user->companycode)
                ->where('mandorid', $user->userid)
                ->exists();
                
            if (!$lkhExists) {
                return back()->withErrors(['message' => 'LKH tidak ditemukan atau tidak berhak diakses']);
            }
            
            DB::beginTransaction();
            
            try {
                // Clear existing worker assignments
                DB::table('lkhdetailworker')
                    ->where('companycode', $user->companycode)
                    ->where('lkhno', $lkhno)
                    ->delete();
                
                // Insert new worker assignments
                foreach ($assignedWorkers as $index => $worker) {
                    DB::table('lkhdetailworker')->insert([
                        'companycode' => $user->companycode,
                        'lkhno' => $lkhno,
                        'tenagakerjaid' => $worker['tenagakerjaid'],
                        'tenagakerjaurutan' => $index + 1,
                        'jammasuk' => '07:00:00',
                        'jamselesai' => '16:00:00',
                        'totaljamkerja' => 8.0,
                        'overtimehours' => 0,
                        'premi' => 0, 'upahharian' => 0, 'upahperjam' => 0,
                        'upahlembur' => 0, 'upahborongan' => 0, 'totalupah' => 0,
                        'createdat' => now(),
                    ]);
                }
                
                // Update LKH header with worker count
                DB::table('lkhhdr')
                    ->where('lkhno', $lkhno)
                    ->where('companycode', $user->companycode)
                    ->update([
                        'totalworkers' => count($assignedWorkers),
                        'updateby' => $user->name,
                        'updatedat' => now()
                    ]);
                
                DB::commit();
                
                return back()->with([
                    'success' => true,
                    'flash' => ['success' => count($assignedWorkers) . ' pekerja berhasil ditugaskan untuk ' . $lkhno]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in saveLKHAssign', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Save LKH Results with mobile_status and redirect to view
     */
    public function saveLKHResults(Request $request, $lkhno = null)
    {
        try {
            $lkhno = $lkhno ?? $request->input('lkhno');
            
            $request->validate([
                'worker_inputs' => 'required|array|min:1',
                'worker_inputs.*.tenagakerjaid' => 'required|string',
                'worker_inputs.*.jammasuk' => 'nullable|string',
                'worker_inputs.*.jamselesai' => 'nullable|string',
                'worker_inputs.*.overtimehours' => 'nullable|numeric|min:0',
                'plot_inputs' => 'required|array|min:1',
                'plot_inputs.*.plot' => 'required|string',
                'plot_inputs.*.luashasil' => 'required|numeric|min:0',
                'plot_inputs.*.luassisa' => 'nullable|numeric|min:0',
                'material_inputs' => 'nullable|array',
                'material_inputs.*.itemcode' => 'required_with:material_inputs|string',
                'material_inputs.*.qtysisa' => 'required_with:material_inputs|numeric|min:0',
                'keterangan' => 'nullable|string|max:500'
            ]);
            
            if (!auth()->check()) {
                return back()->withErrors(['message' => 'User not authenticated']);
            }
            
            $user = auth()->user();
            $workerInputs = $request->input('worker_inputs', []);
            $plotInputs = $request->input('plot_inputs', []);
            $materialInputs = $request->input('material_inputs', []);
            $keterangan = $request->input('keterangan');
            
            DB::beginTransaction();
            
            try {
                // Get LKH info
                $lkhInfo = DB::table('lkhhdr')->where('lkhno', $lkhno)->first();
                
                if (!$lkhInfo) {
                    return back()->withErrors(['message' => 'LKH tidak ditemukan']);
                }
                
                // Update worker details
                foreach ($workerInputs as $workerInput) {
                    $jamMasuk = $workerInput['jammasuk'] ?? '07:00';
                    $jamSelesai = $workerInput['jamselesai'] ?? '15:00';
                    $overtimeHours = $workerInput['overtimehours'] ?? 0;
                    
                    $totalJamKerja = $this->calculateWorkHours($jamMasuk, $jamSelesai);
                    $wageData = $this->calculateWorkerWage($lkhInfo, $totalJamKerja, $overtimeHours);
                    
                    DB::table('lkhdetailworker')
                        ->where('companycode', $user->companycode)
                        ->where('lkhno', $lkhno)
                        ->where('tenagakerjaid', $workerInput['tenagakerjaid'])
                        ->update([
                            'jammasuk' => $jamMasuk,
                            'jamselesai' => $jamSelesai,
                            'totaljamkerja' => $totalJamKerja,
                            'overtimehours' => $overtimeHours,
                            'premi' => $wageData['premi'],
                            'upahharian' => $wageData['upahharian'],
                            'upahperjam' => $wageData['upahperjam'],
                            'upahlembur' => $wageData['upahlembur'],
                            'upahborongan' => $wageData['upahborongan'],
                            'totalupah' => $wageData['totalupah']
                        ]);
                }
                
                // Update plot details
                foreach ($plotInputs as $plotInput) {
                    DB::table('lkhdetailplot')
                        ->where('companycode', $user->companycode)
                        ->where('lkhno', $lkhno)
                        ->where('plot', $plotInput['plot'])
                        ->update([
                            'luashasil' => $plotInput['luashasil'] ?? 0,
                            'luassisa' => $plotInput['luassisa'] ?? 0
                        ]);
                }
                
                // Update material details if provided
                foreach ($materialInputs as $materialInput) {
                    if (isset($materialInput['itemcode']) && isset($materialInput['qtysisa'])) {
                        $qtyDiterima = DB::table('usemateriallst')
                            ->where('companycode', $user->companycode)
                            ->where('lkhno', $lkhno)
                            ->where('itemcode', $materialInput['itemcode'])
                            ->value('qty');
                        
                        DB::table('lkhdetailmaterial')
                            ->updateOrInsert(
                                [
                                    'companycode' => $user->companycode,
                                    'lkhno' => $lkhno,
                                    'itemcode' => $materialInput['itemcode']
                                ],
                                [
                                    'qtyditerima' => $qtyDiterima ?? 0,
                                    'qtysisa' => $materialInput['qtysisa'],
                                    'qtydigunakan' => ($qtyDiterima ?? 0) - $materialInput['qtysisa'],
                                    'keterangan' => $materialInput['keterangan'] ?? null,
                                    'inputby' => $user->name,
                                    'createdat' => now(),
                                    'updatedat' => now()
                                ]
                            );
                    }
                }
                
                // Calculate totals and update header
                $totals = $this->calculateLKHTotals($lkhno, $user->companycode);
                
                DB::table('lkhhdr')
                    ->where('lkhno', $lkhno)
                    ->where('companycode', $user->companycode)
                    ->update([
                        'totalworkers' => $totals['totalworkers'],
                        'totalhasil' => $totals['totalhasil'],
                        'totalsisa' => $totals['totalsisa'],
                        'totalupahall' => $totals['totalupah'],
                        'mobile_status' => 'DRAFT',
                        'keterangan' => $keterangan,
                        'updateby' => $user->name,
                        'mobileupdatedat' => now()
                    ]);
                
                DB::commit();
                
                return redirect()->route('mandor.lkh.view', $lkhno)->with([
                    'success' => true,
                    'flash' => ['success' => 'Data LKH berhasil disimpan sebagai draft!']
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in saveLKHResults', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // =============================================================================
    // MATERIAL MANAGEMENT
    // =============================================================================

    /**
     * Get available materials for mandor
     */
    public function getAvailableMaterials(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date', now()->format('Y-m-d'));
            
            // Query materials with original status
            $materials = DB::table('usemateriallst as uml')
                ->join('usematerialhdr as umh', function($join) {
                    $join->on('uml.companycode', '=', 'umh.companycode')
                         ->on('uml.rkhno', '=', 'umh.rkhno');
                })
                ->join('lkhhdr as lkh', function($join) {
                    $join->on('uml.companycode', '=', 'lkh.companycode')
                         ->on('uml.lkhno', '=', 'lkh.lkhno');
                })
                ->where('uml.companycode', $user->companycode)
                ->where('lkh.mandorid', $user->userid)
                ->whereDate('lkh.lkhdate', $date)
                ->select([
                    'uml.itemcode', 'uml.itemname', 'uml.qty', 'uml.qtyretur', 'uml.qtydigunakan',
                    'uml.unit', 'uml.herbisidagroupid', 'uml.dosageperha', 'umh.flagstatus as status',
                    'uml.lkhno', 'uml.rkhno'
                ])
                ->orderBy('uml.itemcode')
                ->get();
            
            // Group by itemcode and calculate totals
            $groupedMaterials = [];
            
            foreach ($materials as $material) {
                $key = $material->itemcode;
                
                if (!isset($groupedMaterials[$key])) {
                    $groupedMaterials[$key] = [
                        'itemcode' => $material->itemcode,
                        'itemname' => $material->itemname,
                        'total_qty' => 0,
                        'total_qtyretur' => 0,
                        'total_qtydigunakan' => 0,
                        'unit' => $material->unit,
                        'status' => $material->status,
                        'lkh_details' => [],
                        'plot_breakdown' => [],
                        'herbisidagroupid' => $material->herbisidagroupid,
                        'dosageperha' => $material->dosageperha
                    ];
                }
                
                $groupedMaterials[$key]['total_qty'] += (float) $material->qty;
                $groupedMaterials[$key]['total_qtyretur'] += (float) ($material->qtyretur ?? 0);
                $groupedMaterials[$key]['total_qtydigunakan'] += (float) ($material->qtydigunakan ?? 0);
                
                $groupedMaterials[$key]['lkh_details'][] = [
                    'lkhno' => $material->lkhno,
                    'qty' => (float) $material->qty
                ];
                
                $groupedMaterials[$key]['rkhno'] = $material->rkhno;
            }
            
            // Get plot breakdown for each material
            foreach ($groupedMaterials as $itemcode => &$materialData) {
                $plotBreakdown = $this->getMaterialPlotBreakdown(
                    $materialData['rkhno'], 
                    $itemcode, 
                    $user->companycode,
                    $materialData['herbisidagroupid'],
                    $materialData['dosageperha']
                );
                $materialData['plot_breakdown'] = $plotBreakdown;
            }
            
            return response()->json([
                'materials' => array_values($groupedMaterials),
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getAvailableMaterials', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Confirm material pickup (DISPATCHED -> RECEIVED_BY_MANDOR)
     */
    public function confirmMaterialPickup(Request $request)
    {
        try {
            $request->validate(['itemcode' => 'required|string']);
            
            if (!auth()->check()) {
                return response()->json(['success' => false, 'error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $itemcode = $request->input('itemcode');
            $date = now()->format('Y-m-d');
            
            DB::beginTransaction();
            
            try {
                if ($itemcode === 'ALL') {
                    // Confirm all materials for this mandor and date
                    $rkhNumbers = DB::table('usemateriallst as uml')
                        ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                        ->where('uml.companycode', $user->companycode)
                        ->where('lkh.mandorid', $user->userid)
                        ->whereDate('lkh.lkhdate', $date)
                        ->distinct()
                        ->pluck('uml.rkhno');
                    
                    if ($rkhNumbers->isEmpty()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak ada material yang ditemukan untuk hari ini'
                        ]);
                    }
                    
                    $updatedHeaders = DB::table('usematerialhdr')
                        ->where('companycode', $user->companycode)
                        ->whereIn('rkhno', $rkhNumbers)
                        ->where('flagstatus', 'DISPATCHED')
                        ->update([
                            'flagstatus' => 'RECEIVED_BY_MANDOR',
                            'updateby' => $user->name,
                            'updatedat' => now()
                        ]);
                    
                    if ($updatedHeaders === 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Material belum siap diambil atau sudah diterima'
                        ]);
                    }
                    
                    $this->generateNoUseForMaterials($user->companycode, $rkhNumbers->toArray(), $user->name);
                    $message = "Penerimaan semua material berhasil dikonfirmasi ({$updatedHeaders} material)";
                    
                } else {
                    // Confirm specific material
                    $rkhNumbers = DB::table('usemateriallst as uml')
                        ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                        ->where('uml.companycode', $user->companycode)
                        ->where('uml.itemcode', $itemcode)
                        ->where('lkh.mandorid', $user->userid)
                        ->whereDate('lkh.lkhdate', $date)
                        ->distinct()
                        ->pluck('uml.rkhno');
                    
                    if ($rkhNumbers->isEmpty()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Material tidak ditemukan untuk mandor ini'
                        ]);
                    }
                    
                    $updatedHeaders = DB::table('usematerialhdr')
                        ->where('companycode', $user->companycode)
                        ->whereIn('rkhno', $rkhNumbers)
                        ->where('flagstatus', 'DISPATCHED')
                        ->update([
                            'flagstatus' => 'RECEIVED_BY_MANDOR',
                            'updateby' => $user->name,
                            'updatedat' => now()
                        ]);
                    
                    if ($updatedHeaders === 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Material belum siap diambil atau sudah diterima'
                        ]);
                    }
                    
                    $this->generateNoUseForMaterials($user->companycode, $rkhNumbers->toArray(), $user->name);
                    $message = "Penerimaan material {$itemcode} berhasil dikonfirmasi";
                }
                
                DB::commit();
                
                return response()->json(['success' => true, 'message' => $message]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in confirmMaterialPickup', [
                'message' => $e->getMessage(),
                'itemcode' => $request->input('itemcode'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save material returns (RECEIVED_BY_MANDOR -> RETURNED_BY_MANDOR)
     */
    public function saveMaterialReturns(Request $request)
    {
        try {
            $request->validate(['material_returns' => 'required|array']);
            
            if (!auth()->check()) {
                return response()->json(['success' => false, 'error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $materialReturns = $request->input('material_returns');
            $date = now()->format('Y-m-d');
            
            // Filter out zero returns
            $filteredReturns = array_filter($materialReturns, function($qty) {
                return $qty > 0;
            });
            
            if (empty($filteredReturns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data retur untuk disimpan'
                ]);
            }
            
            DB::beginTransaction();
            
            try {
                $processedItems = [];
                
                foreach ($filteredReturns as $itemcode => $returnQty) {
                    $noRetur = $this->generateReturnNo();
                    
                    // Update usemateriallst with return quantity
                    $updatedRecords = DB::table('usemateriallst as uml')
                        ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                        ->where('uml.companycode', $user->companycode)
                        ->where('uml.itemcode', $itemcode)
                        ->where('lkh.mandorid', $user->userid)
                        ->whereDate('lkh.lkhdate', $date)
                        ->update([
                            'uml.qtyretur' => $returnQty,
                            'uml.qtydigunakan' => DB::raw('uml.qty - ' . $returnQty),
                            'uml.noretur' => $noRetur,
                            'uml.returby' => $user->name,
                            'uml.tglretur' => now()
                        ]);
                    
                    if ($updatedRecords > 0) {
                        // Update lkhdetailmaterial for each LKH
                        $lkhNumbers = DB::table('usemateriallst as uml')
                            ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                            ->where('uml.companycode', $user->companycode)
                            ->where('uml.itemcode', $itemcode)
                            ->where('lkh.mandorid', $user->userid)
                            ->whereDate('lkh.lkhdate', $date)
                            ->pluck('uml.lkhno');
                        
                        foreach ($lkhNumbers as $lkhno) {
                            $materialData = DB::table('usemateriallst')
                                ->where('companycode', $user->companycode)
                                ->where('lkhno', $lkhno)
                                ->where('itemcode', $itemcode)
                                ->first();
                            
                            if ($materialData) {
                                DB::table('lkhdetailmaterial')
                                    ->updateOrInsert(
                                        [
                                            'companycode' => $user->companycode,
                                            'lkhno' => $lkhno,
                                            'itemcode' => $itemcode
                                        ],
                                        [
                                            'qtyditerima' => $materialData->qty,
                                            'qtysisa' => $returnQty,
                                            'qtydigunakan' => $materialData->qty - $returnQty,
                                            'inputby' => $user->name,
                                            'createdat' => now(),
                                            'updatedat' => now()
                                        ]
                                    );
                            }
                        }
                        
                        // Update header status from RECEIVED_BY_MANDOR to RETURNED_BY_MANDOR
                        $rkhNumbers = DB::table('usemateriallst as uml')
                            ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                            ->where('uml.companycode', $user->companycode)
                            ->where('uml.itemcode', $itemcode)
                            ->where('lkh.mandorid', $user->userid)
                            ->whereDate('lkh.lkhdate', $date)
                            ->distinct()
                            ->pluck('uml.rkhno');
                        
                        $updatedHeaders = DB::table('usematerialhdr')
                            ->where('companycode', $user->companycode)
                            ->whereIn('rkhno', $rkhNumbers)
                            ->where('flagstatus', 'RECEIVED_BY_MANDOR')
                            ->update([
                                'flagstatus' => 'RETURNED_BY_MANDOR',
                                'updateby' => $user->name,
                                'updatedat' => now()
                            ]);
                        
                        $processedItems[] = [
                            'itemcode' => $itemcode,
                            'return_qty' => $returnQty,
                            'noretur' => $noRetur,
                            'lkh_count' => $lkhNumbers->count(),
                            'header_updated' => $updatedHeaders > 0
                        ];
                    }
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data retur material berhasil disimpan untuk ' . count($processedItems) . ' item',
                    'processed_items' => $processedItems
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in saveMaterialReturns', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================================================
    // UTILITY & VEHICLE MANAGEMENT
    // =============================================================================

    /**
     * Get vehicle info for specific LKH
     */
    public function getVehicleInfo(Request $request)
    {
        try {
            $lkhno = $request->input('lkhno');
            
            if (!$lkhno) {
                return response()->json(['error' => 'LKH number is required'], 400);
            }
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $vehicleInfo = $this->getVehicleInfoForLKH($lkhno);
            
            if (!$vehicleInfo) {
                return response()->json([
                    'vehicle_info' => null,
                    'message' => 'No vehicle assigned for this LKH'
                ]);
            }
            
            return response()->json(['vehicle_info' => $vehicleInfo]);
            
        } catch (\Exception $e) {
            Log::error('Error in getVehicleInfo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Sync offline data when online
     */
    public function syncOfflineData(Request $request)
    {
        try {
            $request->validate(['offline_data' => 'required|array']);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $offlineData = $request->input('offline_data');
            $syncResults = [];
            
            DB::beginTransaction();
            
            try {
                foreach ($offlineData as $dataType => $data) {
                    switch ($dataType) {
                        case 'lkh_results':
                            foreach ($data as $lkhData) {
                                $result = $this->processOfflineLKHData($lkhData);
                                $syncResults[] = [
                                    'type' => 'lkh_results',
                                    'id' => $lkhData['lkhno'] ?? 'unknown',
                                    'status' => $result ? 'success' : 'failed'
                                ];
                            }
                            break;
                            
                        case 'material_returns':
                            foreach ($data as $returnData) {
                                $result = $this->processOfflineMaterialReturn($returnData);
                                $syncResults[] = [
                                    'type' => 'material_returns',
                                    'id' => $returnData['itemcode'] ?? 'unknown',
                                    'status' => $result ? 'success' : 'failed'
                                ];
                            }
                            break;
                            
                        default:
                            Log::warning('Unknown offline data type', ['type' => $dataType]);
                    }
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data offline berhasil disinkronisasi',
                    'sync_results' => $syncResults
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in syncOfflineData', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    // =============================================================================
    // LEGACY API ENDPOINTS (for backward compatibility)
    // =============================================================================

    public function checkIn(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil',
            'timestamp' => now()->format('H:i')
        ]);
    }

    public function checkOut(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil', 
            'timestamp' => now()->format('H:i')
        ]);
    }

    public function getAttendanceData()
    {
        return response()->json([
            'attendance_summary' => [],
            'attendance_stats' => []
        ]);
    }

    public function getFieldActivities()
    {  
        return response()->json(['field_activities' => []]);
    }

    // =============================================================================
    // PRIVATE HELPER METHODS
    // =============================================================================

    /**
     * Shared LKH form renderer
     */
    private function renderLKHForm($lkhno, $mode = 'input')
    {
        $user = auth()->user();
        
        // Get LKH data with proper activity join
        $lkhData = DB::table('lkhhdr as lkh')
            ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
            ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
            ->where('lkh.companycode', $user->companycode)
            ->where('lkh.mandorid', $user->userid)
            ->where('lkh.lkhno', $lkhno)
            ->select([
                'lkh.lkhno', 'lkh.activitycode', 'act.activityname', 'act.description as activity_description',
                'lkh.jenistenagakerja', 'lkh.rkhno', 'lkh.lkhdate', 'lkh.mobile_status', 'u.name as mandor_nama'
            ])
            ->first();
        
        if (!$lkhData) {
            throw new \Exception('LKH tidak ditemukan');
        }
        
        // Get assigned workers
        $assignedWorkers = DB::table('lkhdetailworker as ldw')
            ->join('tenagakerja as tk', 'ldw.tenagakerjaid', '=', 'tk.tenagakerjaid')
            ->where('ldw.lkhno', $lkhno)
            ->where('ldw.companycode', $user->companycode)
            ->select([
                'tk.tenagakerjaid', 'tk.nama', 'tk.nik', 'ldw.jammasuk', 'ldw.jamselesai',
                'ldw.totaljamkerja', 'ldw.overtimehours'
            ])
            ->orderBy('ldw.tenagakerjaurutan')
            ->get()
            ->map(function($worker) {
                return [
                    'tenagakerjaid' => $worker->tenagakerjaid,
                    'nama' => $worker->nama,
                    'nik' => $worker->nik,
                    'jammasuk' => $worker->jammasuk,
                    'jamselesai' => $worker->jamselesai,
                    'totaljamkerja' => (float) $worker->totaljamkerja,
                    'overtimehours' => (float) $worker->overtimehours,
                    'assigned' => true
                ];
            })
            ->toArray();
        
        if (empty($assignedWorkers)) {
            return redirect()->route('mandor.lkh.assign', $lkhno)->with('error', 'Silakan assign pekerja terlebih dahulu');
        }
        
        // Get plot data
        $plotData = DB::table('lkhdetailplot')
            ->where('companycode', $user->companycode)
            ->where('lkhno', $lkhno)
            ->select(['blok', 'plot', 'luasrkh', 'luashasil', 'luassisa'])
            ->get()
            ->map(function($plot) {
                return [
                    'blok' => $plot->blok,
                    'plot' => $plot->plot,
                    'luasarea' => (float) $plot->luasrkh,
                    'luashasil' => (float) ($plot->luashasil ?? 0),
                    'luassisa' => (float) ($plot->luassisa ?? 0)
                ];
            })
            ->toArray();
        
        if (empty($plotData)) {
            return redirect()->route('mandor.lkh.assign', $lkhno)->with('error', 'Data plot tidak ditemukan untuk LKH ini');
        }
        
        // Get materials info for this LKH
        $materials = $this->getMaterialsForLKH($lkhno, $user->companycode);
        
        // Calculate total luas plan
        $totalLuasPlan = array_sum(array_column($plotData, 'luasarea'));
        
        // Determine page component and routes based on mode
        $pageComponent = $mode === 'view' ? 'lkh-view' : 'lkh-input';
        $pageTitle = $mode === 'view' ? 'Lihat Hasil - ' . $lkhno : 
                    ($mode === 'edit' ? 'Edit Hasil - ' . $lkhno : 'Input Hasil - ' . $lkhno);
        
        $routes = [
            'lkh_save_results' => route('mandor.lkh.save-results', $lkhno),
            'lkh_assign' => route('mandor.lkh.assign', $lkhno),
            'lkh_view' => route('mandor.lkh.view', $lkhno),
            'lkh_edit' => route('mandor.lkh.edit', $lkhno),
            'mandor_index' => route('mandor.index'),
        ];
        
        return Inertia::render($pageComponent, [
            'title' => $pageTitle,
            'mode' => $mode,
            'lkhData' => [
                'lkhno' => $lkhData->lkhno,
                'activitycode' => $lkhData->activitycode,  
                'activityname' => $lkhData->activityname,
                'blok' => $plotData[0]['blok'] ?? 'N/A',
                'plot' => array_column($plotData, 'plot'),
                'totalluasplan' => $totalLuasPlan,
                'jenistenagakerja' => $this->getJenisTenagaKerjaName($lkhData->jenistenagakerja),
                'rkhno' => $lkhData->rkhno,
                'lkhdate' => $lkhData->lkhdate,
                'mandor_nama' => $lkhData->mandor_nama,
                'mobile_status' => $lkhData->mobile_status,
                'needs_material' => count($materials) > 0
            ],
            'assignedWorkers' => $assignedWorkers,
            'plotData' => $plotData,
            'materials' => $materials,
            'routes' => $routes,
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Get materials for LKH with calculated breakdown per plot
     */
    private function getMaterialsForLKH($lkhno, $companyCode)
    {
        // Get RKH number from LKH
        $rkhno = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companyCode)
            ->value('rkhno');
        
        if (!$rkhno) {
            return [];
        }
        
        // Get materials from usemateriallst
        $materials = DB::table('usemateriallst as uml')
            ->where('uml.companycode', $companyCode)
            ->where('uml.rkhno', $rkhno)
            ->where('uml.lkhno', $lkhno)
            ->select(['uml.itemcode', 'uml.itemname', 'uml.qty', 'uml.unit', 'uml.herbisidagroupid', 'uml.dosageperha'])
            ->get();
        
        // Get plot details for breakdown calculation
        $plotDetails = DB::table('rkhlst as rls')
            ->where('rls.companycode', $companyCode)
            ->where('rls.rkhno', $rkhno)
            ->select(['rls.plot', 'rls.luasarea', 'rls.herbisidagroupid'])
            ->get();
        
        // Calculate breakdown per plot for each material
        $materialWithBreakdown = [];
        
        foreach ($materials as $material) {
            $plotBreakdown = [];
            $totalCalculated = 0;
            
            foreach ($plotDetails as $plot) {
                if ($plot->herbisidagroupid === $material->herbisidagroupid) {
                    $plotUsage = $plot->luasarea * $material->dosageperha;
                    $plotBreakdown[] = [
                        'plot' => $plot->plot,
                        'luasarea' => (float) $plot->luasarea,
                        'usage' => $plotUsage
                    ];
                    $totalCalculated += $plotUsage;
                }
            }
            
            $materialWithBreakdown[] = [
                'itemcode' => $material->itemcode,
                'itemname' => $material->itemname,
                'qty' => (float) $material->qty,
                'unit' => $material->unit,
                'plot_breakdown' => $plotBreakdown,
                'total_calculated' => $totalCalculated
            ];
        }
        
        return $materialWithBreakdown;
    }

    /**
     * Get material breakdown per plot
     */
    private function getMaterialPlotBreakdown($rkhno, $itemcode, $companyCode, $herbisidagroupid, $dosageperha)
    {
        try {
            // Get plot details from lkhdetailplot table
            $lkhnos = DB::table('usemateriallst')
                ->where('companycode', $companyCode)
                ->where('rkhno', $rkhno)
                ->where('itemcode', $itemcode)
                ->pluck('lkhno')
                ->unique();
            
            $breakdown = [];
            
            foreach ($lkhnos as $lkhno) {
                $plotDetails = DB::table('lkhdetailplot')
                    ->where('companycode', $companyCode)
                    ->where('lkhno', $lkhno)
                    ->select(['blok', 'plot', 'luasrkh'])
                    ->get();
                
                foreach ($plotDetails as $plot) {
                    $usage = $plot->luasrkh * $dosageperha;
                    $breakdown[] = [
                        'plot' => $plot->plot,
                        'blok' => $plot->blok,
                        'luasarea' => (float) $plot->luasrkh,
                        'usage' => $usage,
                        'usage_formatted' => number_format($usage, 2) . ' kg'
                    ];
                }
            }
            
            return $breakdown;
            
        } catch (\Exception $e) {
            Log::error('Error in getMaterialPlotBreakdown', [
                'rkhno' => $rkhno,
                'itemcode' => $itemcode,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Calculate total material usage across all completed LKH
     */
    private function calculateTotalMaterialUsage($companyCode, $mandorUserId, $date)
    {
        try {
            // Get all material usage for this mandor and date
            $materialTotals = DB::table('lkhdetailmaterial as ldm')
                ->join('lkhhdr as lkh', function($join) {
                    $join->on('ldm.companycode', '=', 'lkh.companycode')
                         ->on('ldm.lkhno', '=', 'lkh.lkhno');
                })
                ->where('ldm.companycode', $companyCode)
                ->where('lkh.mandorid', $mandorUserId)
                ->whereDate('lkh.lkhdate', $date)
                ->where('lkh.mobile_status', 'COMPLETED')
                ->select([
                    'ldm.itemcode',
                    DB::raw('SUM(ldm.qtydigunakan) as total_used'),
                    DB::raw('SUM(ldm.qtysisa) as total_returned')
                ])
                ->groupBy('ldm.itemcode')
                ->get();
            
            // Update usemateriallst with calculated totals
            foreach ($materialTotals as $materialTotal) {
                DB::table('usemateriallst as uml')
                    ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                    ->where('uml.companycode', $companyCode)
                    ->where('uml.itemcode', $materialTotal->itemcode)
                    ->where('lkh.mandorid', $mandorUserId)
                    ->whereDate('lkh.lkhdate', $date)
                    ->update([
                        'uml.qtydigunakan' => $materialTotal->total_used,
                        'uml.qtyretur' => $materialTotal->total_returned
                    ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error calculating material usage', [
                'error' => $e->getMessage(),
                'companyCode' => $companyCode,
                'mandorUserId' => $mandorUserId,
                'date' => $date
            ]);
        }
    }

    /**
     * Calculate LKH totals from detail tables
     */
    private function calculateLKHTotals($lkhno, $companyCode)
    {
        // Total workers
        $totalWorkers = DB::table('lkhdetailworker')
            ->where('companycode', $companyCode)
            ->where('lkhno', $lkhno)
            ->count();
        
        // Total hasil and sisa from plots
        $plotTotals = DB::table('lkhdetailplot')
            ->where('companycode', $companyCode)
            ->where('lkhno', $lkhno)
            ->selectRaw('
                SUM(COALESCE(luashasil, 0)) as totalhasil,
                SUM(COALESCE(luassisa, 0)) as totalsisa
            ')
            ->first();
        
        // Total upah from workers
        $totalUpah = DB::table('lkhdetailworker')
            ->where('companycode', $companyCode)
            ->where('lkhno', $lkhno)
            ->sum('totalupah');
        
        return [
            'totalworkers' => $totalWorkers,
            'totalhasil' => $plotTotals->totalhasil ?? 0,
            'totalsisa' => $plotTotals->totalsisa ?? 0,
            'totalupah' => $totalUpah ?? 0
        ];
    }

    /**
     * Calculate work hours - handle time format properly
     */
    private function calculateWorkHours($jamMasuk, $jamSelesai)
    {
        try {
            // Handle both H:i and H:i:s formats
            $jamMasukFormatted = strlen($jamMasuk) > 5 ? substr($jamMasuk, 0, 5) : $jamMasuk;
            $jamSelesaiFormatted = strlen($jamSelesai) > 5 ? substr($jamSelesai, 0, 5) : $jamSelesai;
            
            $start = Carbon::createFromFormat('H:i', $jamMasukFormatted);
            $end = Carbon::createFromFormat('H:i', $jamSelesaiFormatted);
            
            // Handle overnight work
            if ($end->lt($start)) {
                $end->addDay();
            }
            
            return $start->diffInHours($end);
        } catch (\Exception $e) {
            Log::warning('Error calculating work hours', [
                'jamMasuk' => $jamMasuk,
                'jamSelesai' => $jamSelesai,
                'error' => $e->getMessage()
            ]);
            return 8; // Default 8 hours
        }
    }

    /**
     * Calculate worker wage based on LKH type and work hours
     */
    private function calculateWorkerWage($lkhInfo, $totalJamKerja, $overtimeHours)
    {
        $wageData = [
            'premi' => 0, 'upahharian' => 0, 'upahperjam' => 0,
            'upahlembur' => 0, 'upahborongan' => 0, 'totalupah' => 0
        ];
        
        if ($lkhInfo->jenistenagakerja == 1) {
            // Harian calculation
            $baseWage = 115722.8;
            $hourlyWage = 16532;
            $overtimeWage = 12542;
            
            if ($totalJamKerja >= 8) {
                $wageData['upahharian'] = $baseWage;
            } else {
                $wageData['upahperjam'] = $hourlyWage;
                $wageData['upahharian'] = $totalJamKerja * $hourlyWage;
            }
            
            if ($overtimeHours > 0) {
                $wageData['upahlembur'] = $overtimeHours * $overtimeWage;
            }
            
            $wageData['totalupah'] = $wageData['upahharian'] + $wageData['upahlembur'] + $wageData['premi'];
            
        } else {
            // Borongan calculation
            $costPerHa = $this->calculateCostPerHa($lkhInfo->activitycode);
            $wageData['upahborongan'] = $costPerHa;
            $wageData['totalupah'] = $costPerHa;
        }
        
        return $wageData;
    }

    /**
     * Calculate cost per hectare based on activity
     */
    private function calculateCostPerHa($activitycode)
    {
        try {
            // Default costs based on common activity patterns
            if (strpos($activitycode, 'IV.5') !== false) {
                return 1977000; // Penanaman
            } elseif (strpos($activitycode, 'V.') !== false) {
                return 140000; // Post emergence
            } elseif (strpos($activitycode, 'VI.') !== false) {
                return 110000; // Panen
            }
            
            return 100000; // Default cost per ha
            
        } catch (\Exception $e) {
            Log::warning('Error calculating cost per ha', [
                'activitycode' => $activitycode,
                'error' => $e->getMessage()
            ]);
            return 100000;
        }
    }

    /**
     * Get vehicle info for specific LKH
     */
    private function getVehicleInfoForLKH($lkhno)
    {
        $vehicleInfo = DB::table('lkhhdr as lkh')
            ->join('rkhlst as rls', function($join) {
                $join->on('lkh.rkhno', '=', 'rls.rkhno')
                     ->on('lkh.activitycode', '=', 'rls.activitycode');
            })
            ->leftJoin('kendaraan as k', function($join) {
                $join->on('rls.operatorid', '=', 'k.idtenagakerja')
                     ->where('k.isactive', '=', 1);
            })
            ->leftJoin('tenagakerja as tk', function($join) {
                $join->on('k.idtenagakerja', '=', 'tk.tenagakerjaid')
                     ->where('tk.isactive', '=', 1);
            })
            ->where('lkh.lkhno', $lkhno)
            ->where('rls.usingvehicle', 1)
            ->select([
                'k.nokendaraan', 'k.jenis', 'k.hourmeter',
                'tk.nama as operator_nama', 'tk.nik as operator_nik'
            ])
            ->first();
        
        return $vehicleInfo ? [
            'nokendaraan' => $vehicleInfo->nokendaraan,
            'jenis' => $vehicleInfo->jenis,
            'hourmeter' => (float) $vehicleInfo->hourmeter,
            'operator_nama' => $vehicleInfo->operator_nama,
            'operator_nik' => $vehicleInfo->operator_nik
        ] : null;
    }

    /**
     * Get available workers for assignment
     */
    private function getAvailableWorkersForAssignment($companyCode, $mandorUserId, $date)
    {
        $attendance = AbsenLst::getAttendanceByMandorAndDate($companyCode, $mandorUserId, $date);
        
        return $attendance->map(function($record) {
            return [
                'tenagakerjaid' => $record->tenagakerjaid,
                'nama' => $record->nama,
                'nik' => $record->nik,
                'assigned' => false
            ];
        })->toArray();
    }

    /**
     * Get jenis tenaga kerja name from ID
     */
    private function getJenisTenagaKerjaName($jenisId)
    {
        $jenisMap = [1 => 'Harian', 2 => 'Borongan', 3 => 'Kontrak'];
        return $jenisMap[$jenisId] ?? "Jenis $jenisId";
    }

    /**
     * Generate absenno format: ABS{YYYYMMDD}{sequence}
     */
    private function generateAbsenNo($mandorUserId, $date)
    {
        $dateStr = str_replace('-', '', $date);
        $prefix = "ABS{$dateStr}";
        $companyCode = auth()->user()->companycode;
        
        return DB::transaction(function () use ($prefix, $companyCode) {
            $lastAbsen = AbsenHdr::where('companycode', $companyCode)
                ->where('absenno', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('absenno', 'desc')
                ->first();
            
            if ($lastAbsen) {
                $lastSequence = (int) substr($lastAbsen->absenno, -4);
                $newSequence = $lastSequence + 1;
            } else {
                $newSequence = 1;
            }
            
            return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Generate return number format: RET{YYYYMMDD}{sequence}
     */
    private function generateReturnNo()
    {
        $dateStr = now()->format('Ymd');
        $prefix = "RET{$dateStr}";
        
        return DB::transaction(function () use ($prefix) {
            $lastReturn = DB::table('usemateriallst')
                ->where('noretur', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('noretur', 'desc')
                ->first();
            
            if ($lastReturn) {
                $lastSequence = (int) substr($lastReturn->noretur, -4);
                $newSequence = $lastSequence + 1;
            } else {
                $newSequence = 1;
            }
            
            return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Generate nouse (nomor pengeluaran) for materials
     */
    private function generateNoUseForMaterials($companyCode, $rkhNumbers, $userName)
    {
        foreach ($rkhNumbers as $rkhno) {
            $existingNoUse = DB::table('usemateriallst')
                ->where('companycode', $companyCode)
                ->where('rkhno', $rkhno)
                ->whereNotNull('nouse')
                ->first();
            
            if (!$existingNoUse) {
                $noUse = $this->generateNoUseNumber();
                
                DB::table('usemateriallst')
                    ->where('companycode', $companyCode)
                    ->where('rkhno', $rkhno)
                    ->update(['nouse' => $noUse]);
            }
        }
    }

    /**
     * Generate nomor pengeluaran format: USE{YYYYMMDD}{sequence}
     */
    private function generateNoUseNumber()
    {
        $dateStr = now()->format('Ymd');
        $prefix = "USE{$dateStr}";
        
        return DB::transaction(function () use ($prefix) {
            $lastUse = DB::table('usemateriallst')
                ->where('nouse', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('nouse', 'desc')
                ->first();
            
            if ($lastUse) {
                $lastSequence = (int) substr($lastUse->nouse, -4);
                $newSequence = $lastSequence + 1;
            } else {
                $newSequence = 1;
            }
            
            return $prefix . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Process offline LKH data
     */
    private function processOfflineLKHData($lkhData)
    {
        try {
            Log::info('Processing offline LKH data', ['lkhno' => $lkhData['lkhno'] ?? 'unknown']);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error processing offline LKH data', [
                'error' => $e->getMessage(),
                'data' => $lkhData
            ]);
            return false;
        }
    }

    /**
     * Process offline material return data
     */
    private function processOfflineMaterialReturn($returnData)
    {
        try {
            Log::info('Processing offline material return', ['itemcode' => $returnData['itemcode'] ?? 'unknown']);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error processing offline material return', [
                'error' => $e->getMessage(),
                'data' => $returnData
            ]);
            return false;
        }
    }
}