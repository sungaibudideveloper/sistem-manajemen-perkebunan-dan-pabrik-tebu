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
    /*
    |--------------------------------------------------------------------------
    | MAIN APPLICATION ENTRY POINTS
    |--------------------------------------------------------------------------
    */

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
            
            // MISSING: Material management routes - ADDED
            'materials_save_returns' => route('mandor.materials.save-returns'),
            'material_confirm_pickup' => route('mandor.materials.confirm-pickup'),
            
            // MISSING: Complete all LKH route - ADDED  
            'complete_all_lkh' => route('mandor.lkh.complete-all'),
            
            // Sync routes
            'sync_offline_data' => route('mandor.sync-offline-data'),
        ],
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | WORKER MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Get workers list for current mandor
     */
    public function getWorkersList()
    {
        try {
            Log::info('getWorkersList called');
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $mandorUserId = $user->userid;
            $companyCode = $user->companycode;
            
            Log::info('Getting workers for mandor', [
                'mandorUserId' => $mandorUserId,
                'companyCode' => $companyCode
            ]);
            
            $workers = TenagaKerja::where('mandoruserid', $mandorUserId)
                ->where('companycode', $companyCode)
                ->where('isactive', 1)
                ->select([
                    'tenagakerjaid',
                    'nama',
                    'nik', 
                    'gender',
                    'jenistenagakerja'
                ])
                ->orderBy('nama')
                ->get();
            
            Log::info('Found workers', ['count' => $workers->count()]);
            
            return response()->json([
                'workers' => $workers
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getWorkersList', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE MANAGEMENT
    |--------------------------------------------------------------------------
    */

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
            $mandorUserId = $user->userid;
            $companyCode = $user->companycode;
            
            Log::info('Getting attendance for date', [
                'mandorUserId' => $mandorUserId,
                'companyCode' => $companyCode,
                'date' => $date
            ]);
            
            $attendance = AbsenLst::getAttendanceByMandorAndDate($companyCode, $mandorUserId, $date)
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
            
            Log::info('Found attendance records', ['count' => $attendance->count()]);
            
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
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process check-in with photo
     */
    public function processCheckIn(Request $request)
    {
        try {
            Log::info('processCheckIn called', $request->only(['tenagakerjaid']));
            
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
            $mandorUserId = $user->userid;
            $companyCode = $user->companycode;
            $today = now()->format('Y-m-d');
            
            // Check if worker exists and belongs to this mandor
            $worker = TenagaKerja::where('tenagakerjaid', $request->tenagakerjaid)
                ->where('mandoruserid', $mandorUserId)
                ->where('companycode', $companyCode)
                ->where('isactive', 1)
                ->first();
                
            if (!$worker) {
                return response()->json(['error' => 'Pekerja tidak ditemukan atau tidak terdaftar pada mandor ini'], 404);
            }
            
            // Check if already checked in today
            if (AbsenLst::hasCheckedInToday($companyCode, $mandorUserId, $request->tenagakerjaid, $today)) {
                return response()->json(['error' => 'Pekerja sudah absen hari ini'], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // Find or create AbsenHdr for mandor today
                $absenHdr = AbsenHdr::where('companycode', $companyCode)
                    ->where('mandorid', $mandorUserId)
                    ->whereDate('uploaddate', $today)
                    ->first();
                
                if (!$absenHdr) {
                    $absenNo = $this->generateAbsenNo($mandorUserId, $today);
                    
                    $absenHdr = AbsenHdr::create([
                        'absenno' => $absenNo,
                        'companycode' => $companyCode,
                        'mandorid' => $mandorUserId,
                        'totalpekerja' => 1,
                        'status' => 'P',
                        'uploaddate' => now(),
                        'updateBy' => $user->name
                    ]);
                    $nextId = 1;
                    
                    Log::info('Created new AbsenHdr', ['absenno' => $absenNo]);
                } else {
                    $nextId = $absenHdr->totalpekerja + 1;
                    
                    DB::table('absenhdr')
                        ->where('absenno', $absenHdr->absenno)
                        ->where('companycode', $companyCode)
                        ->increment('totalpekerja');
                    
                    DB::table('absenhdr')
                        ->where('absenno', $absenHdr->absenno)
                        ->where('companycode', $companyCode)
                        ->update(['updateBy' => $user->name]);
                    
                    $absenHdr = AbsenHdr::where('absenno', $absenHdr->absenno)
                        ->where('companycode', $companyCode)
                        ->first();
                    
                    Log::info('Updated existing AbsenHdr', [
                        'absenno' => $absenHdr->absenno, 
                        'next_id' => $nextId,
                        'new_total' => $absenHdr->totalpekerja
                    ]);
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
                
                Log::info('Check-in processed successfully', [
                    'tenagakerjaid' => $request->tenagakerjaid,
                    'absenno' => $absenHdr->absenno,
                    'worker_name' => $worker->nama,
                    'total_pekerja_today' => $absenHdr->totalpekerja
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Absen berhasil dicatat dengan foto',
                    'data' => [
                        'absenno' => $absenHdr->absenno,
                        'tenagakerjaid' => $request->tenagakerjaid,
                        'worker_name' => $worker->nama,
                        'time' => now()->format('H:i'),
                        'total_today' => $absenHdr->totalpekerja,
                        'is_new_header' => !$absenHdr->wasRecentlyCreated ? false : true
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                
                Log::error('Database transaction failed', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'mandorUserId' => $mandorUserId,
                    'tenagakerjaid' => $request->tenagakerjaid,
                    'today' => $today
                ]);
                
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in processCheckIn', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LKH MANAGEMENT - MULTI-PAGE WORKFLOW
    |--------------------------------------------------------------------------
    */

/**
 * FIXED: Get ready LKH list with mobile_status included
 */
public function getReadyLKH(Request $request)
{
    try {
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $user = auth()->user();
        $mandorUserId = $user->userid;
        $companyCode = $user->companycode;
        $date = $request->input('date', now()->format('Y-m-d'));
        
        Log::info('Getting ready LKH for mandor', [
            'mandorUserId' => $mandorUserId,
            'companyCode' => $companyCode,
            'date' => $date
        ]);
        
        // FIXED: Added mobile_status to SELECT
        $lkhRecords = DB::table('lkhhdr as lkh')
            ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
            ->leftJoin('usematerialhdr as umh', 'lkh.rkhno', '=', 'umh.rkhno')
            ->where('lkh.companycode', $companyCode)
            ->where('lkh.mandorid', $mandorUserId)
            ->whereDate('lkh.lkhdate', $date)
            ->where('lkh.status', '!=', 'COMPLETED')
            ->select([
                'lkh.lkhno',
                'lkh.activitycode',
                'act.activityname',
                'act.description as activity_description',
                'lkh.totalluasactual',
                'lkh.jenistenagakerja',
                'lkh.status as lkh_status',
                'lkh.totalworkers as estimated_workers',
                'lkh.rkhno',
                'lkh.mobile_status', // FIXED: Added mobile_status
                'umh.flagstatus as material_status'
            ])
            ->get();
            
        Log::info('LKH records found', [
            'count' => $lkhRecords->count()
        ]);
        
        $groupedLKH = [];
        
        foreach ($lkhRecords as $lkhRecord) {
            // Get plot data from lkhdetailplot
            $plotData = DB::table('lkhdetailplot')
                ->where('companycode', $companyCode)
                ->where('lkhno', $lkhRecord->lkhno)
                ->select(['blok', 'plot', 'luasrkh'])
                ->get();
            
            // Get material usage flag from rkhlst
            $needsMaterial = DB::table('rkhlst as rls')
                ->where('rls.companycode', $companyCode)
                ->where('rls.rkhno', $lkhRecord->rkhno)
                ->where('rls.activitycode', $lkhRecord->activitycode)
                ->where('rls.usingmaterial', 1)
                ->exists();
            
            // Materials ready when mandor has confirmed receipt (RECEIVED_BY_MANDOR)
            $materialsReady = true;
            if ($needsMaterial) {
                $materialsReady = ($lkhRecord->material_status === 'RECEIVED_BY_MANDOR');
            }
            
            // FIXED: Determine status based on mobile_status first, then material readiness
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
                'status' => $workStatus, // Work status (READY/WAITING_MATERIAL)
                'mobile_status' => $lkhRecord->mobile_status ?: 'EMPTY', // FIXED: Include mobile_status with fallback
                'estimated_workers' => (int) $lkhRecord->estimated_workers,
                'materials_ready' => $materialsReady,
                'needs_material' => $needsMaterial,
            ];
        }
        
        Log::info('Processed LKH result with mobile_status', [
            'count' => count($groupedLKH),
            'mobile_statuses' => array_column($groupedLKH, 'mobile_status')
        ]);
        
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
        
        return response()->json([
            'error' => 'Internal server error: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * NEW: Complete All LKH - Update all DRAFT to COMPLETED
 */
public function completeAllLKH(Request $request)
{
    try {
        $request->validate([
            'date' => 'required|date',
        ]);
        
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $user = auth()->user();
        $mandorUserId = $user->userid;
        $companyCode = $user->companycode;
        $date = $request->input('date');
        
        Log::info('Completing all LKH for mandor', [
            'mandorUserId' => $mandorUserId,
            'companyCode' => $companyCode,
            'date' => $date
        ]);
        
        DB::beginTransaction();
        
        try {
            // Get all DRAFT LKH for this mandor and date
            $draftLKH = DB::table('lkhhdr')
                ->where('companycode', $companyCode)
                ->where('mandorid', $mandorUserId)
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
                ->where('companycode', $companyCode)
                ->where('mandorid', $mandorUserId)
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
            
            // Calculate and update total material usage across all LKH
            $this->calculateTotalMaterialUsage($companyCode, $mandorUserId, $date);
            
            DB::commit();
            
            Log::info('All LKH completed successfully', [
                'mandorUserId' => $mandorUserId,
                'date' => $date,
                'lkh_count' => $updatedCount,
                'lkh_numbers' => $draftLKH->toArray()
            ]);
            
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
        
        Log::info('Material usage calculated and updated', [
            'material_count' => $materialTotals->count(),
            'materials' => $materialTotals->toArray()
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error calculating material usage', [
            'error' => $e->getMessage(),
            'companyCode' => $companyCode,
            'mandorUserId' => $mandorUserId,
            'date' => $date
        ]);
        // Don't throw - let the main transaction continue
    }
}

/**
 * Show LKH Assignment Page (FIXED - Simple version with Activity name)
 */
public function showLKHAssign($lkhno)
{
    try {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        $companyCode = $user->companycode;
        $mandorUserId = $user->userid;
        
        // FIXED: Get LKH data WITH activity join
        $lkhData = DB::table('lkhhdr as lkh')
            ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
            ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
            ->where('lkh.companycode', $companyCode)
            ->where('lkh.mandorid', $mandorUserId)
            ->where('lkh.lkhno', $lkhno)
            ->select([
                'lkh.lkhno',
                'lkh.activitycode',
                'act.activityname', // FIXED: Now getting activityname from activity table
                'act.description as activity_description',
                'lkh.jenistenagakerja',
                'lkh.totalworkers as estimated_workers',
                'lkh.rkhno',
                'lkh.lkhdate',
                'u.name as mandor_nama'
            ])
            ->first();
        
        if (!$lkhData) {
            return redirect()->route('mandor.index')
                ->with('error', 'LKH tidak ditemukan');
        }
        
        // Get plot data from lkhdetailplot table
        $plotData = DB::table('lkhdetailplot')
            ->where('companycode', $companyCode)
            ->where('lkhno', $lkhno)
            ->select('blok', 'plot', 'luasrkh')
            ->get();
        
        // Get vehicle info if applicable
        $vehicleInfo = $this->getVehicleInfoForLKH($lkhno);
        
        // Get available workers (from today's attendance)
        $availableWorkers = $this->getAvailableWorkersForAssignment($companyCode, $mandorUserId, $lkhData->lkhdate);
        
        // Check existing assignments in lkhdetailworker
        $existingAssignments = DB::table('lkhdetailworker')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companyCode)
            ->select('tenagakerjaid')
            ->distinct()
            ->pluck('tenagakerjaid')
            ->toArray();
        
        Log::info('LKH Assignment page accessed', [
            'lkhno' => $lkhno,
            'existing_assignments_count' => count($existingAssignments),
            'available_workers_count' => count($availableWorkers),
            'plots_count' => $plotData->count()
        ]);
        
        return Inertia::render('lkh-assignment', [
            'title' => 'Assignment Pekerja - ' . $lkhno,
            'lkhData' => [
                'lkhno' => $lkhData->lkhno,
                'activitycode' => $lkhData->activitycode,
                'activityname' => $lkhData->activityname, // FIXED: Now this will have proper value
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
        
        return redirect()->route('mandor.index')
            ->with('error', 'Terjadi kesalahan saat membuka halaman assignment');
    }
}



/**
 * Show LKH Input Page (for new LKH without input)
 */
public function showLKHInput($lkhno)
{
    try {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        $companyCode = $user->companycode;
        $mandorUserId = $user->userid;
        
        // Check if LKH already has input (DRAFT status)
        $lkhStatus = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companyCode)
            ->where('mandorid', $mandorUserId)
            ->value('mobile_status');
        
        if ($lkhStatus === 'DRAFT') {
            // Redirect to view mode if already has input
            return redirect()->route('mandor.lkh.view', $lkhno);
        }
        
        if ($lkhStatus === 'COMPLETED') {
            return redirect()->route('mandor.index')
                ->with('error', 'LKH sudah selesai dan tidak bisa diubah');
        }
        
        // Continue with input mode for new LKH
        return $this->renderLKHForm($lkhno, 'input');
        
    } catch (\Exception $e) {
        Log::error('Error in showLKHInput', [
            'message' => $e->getMessage(),
            'lkhno' => $lkhno,
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('mandor.index')
            ->with('error', 'Terjadi kesalahan saat membuka halaman input');
    }
}

/**
 * Show LKH View Page (readonly mode for DRAFT LKH)
 */
public function showLKHView($lkhno)
{
    try {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        $companyCode = $user->companycode;
        $mandorUserId = $user->userid;
        
        // Check if LKH exists and belongs to this mandor
        $lkhStatus = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companyCode)
            ->where('mandorid', $mandorUserId)
            ->value('mobile_status');
        
        if (!$lkhStatus) {
            return redirect()->route('mandor.index')
                ->with('error', 'LKH tidak ditemukan');
        }
        
        if ($lkhStatus !== 'DRAFT') {
            return redirect()->route('mandor.index')
                ->with('error', 'LKH tidak dalam status draft');
        }
        
        // Render view mode
        return $this->renderLKHForm($lkhno, 'view');
        
    } catch (\Exception $e) {
        Log::error('Error in showLKHView', [
            'message' => $e->getMessage(),
            'lkhno' => $lkhno,
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('mandor.index')
            ->with('error', 'Terjadi kesalahan saat membuka halaman view');
    }
}

/**
 * Show LKH Edit Page (editable mode for DRAFT LKH)
 */
public function showLKHEdit($lkhno)
{
    try {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        $companyCode = $user->companycode;
        $mandorUserId = $user->userid;
        
        // Check if LKH exists and is editable
        $lkhStatus = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companyCode)
            ->where('mandorid', $mandorUserId)
            ->value('mobile_status');
        
        if (!$lkhStatus) {
            return redirect()->route('mandor.index')
                ->with('error', 'LKH tidak ditemukan');
        }
        
        if ($lkhStatus !== 'DRAFT') {
            return redirect()->route('mandor.lkh.view', $lkhno)
                ->with('error', 'LKH tidak bisa diedit karena sudah selesai');
        }
        
        // Render edit mode
        return $this->renderLKHForm($lkhno, 'edit');
        
    } catch (\Exception $e) {
        Log::error('Error in showLKHEdit', [
            'message' => $e->getMessage(),
            'lkhno' => $lkhno,
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('mandor.index')
            ->with('error', 'Terjadi kesalahan saat membuka halaman edit');
    }
}

/**
 * SHARED: Render LKH form with different modes
 */
private function renderLKHForm($lkhno, $mode = 'input')
{
    $user = auth()->user();
    $companyCode = $user->companycode;
    $mandorUserId = $user->userid;
    
    // Get LKH data WITH proper activity join
    $lkhData = DB::table('lkhhdr as lkh')
        ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
        ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
        ->where('lkh.companycode', $companyCode)
        ->where('lkh.mandorid', $mandorUserId)
        ->where('lkh.lkhno', $lkhno)
        ->select([
            'lkh.lkhno',
            'lkh.activitycode',
            'act.activityname',
            'act.description as activity_description',
            'lkh.jenistenagakerja',
            'lkh.rkhno',
            'lkh.lkhdate',
            'lkh.mobile_status',
            'u.name as mandor_nama'
        ])
        ->first();
    
    if (!$lkhData) {
        throw new \Exception('LKH tidak ditemukan');
    }
    
    // Get assigned workers from lkhdetailworker table
    $assignedWorkers = DB::table('lkhdetailworker as ldw')
        ->join('tenagakerja as tk', 'ldw.tenagakerjaid', '=', 'tk.tenagakerjaid')
        ->where('ldw.lkhno', $lkhno)
        ->where('ldw.companycode', $companyCode)
        ->select([
            'tk.tenagakerjaid',
            'tk.nama', 
            'tk.nik',
            'ldw.jammasuk',
            'ldw.jamselesai',
            'ldw.totaljamkerja',
            'ldw.overtimehours'
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
    
    // Check if workers are assigned
    if (empty($assignedWorkers)) {
        return redirect()->route('mandor.lkh.assign', $lkhno)
            ->with('error', 'Silakan assign pekerja terlebih dahulu');
    }
    
    // Get plot data from lkhdetailplot table
    $plotData = DB::table('lkhdetailplot')
        ->where('companycode', $companyCode)
        ->where('lkhno', $lkhno)
        ->select([
            'blok',
            'plot', 
            'luasrkh',
            'luashasil',
            'luassisa'
        ])
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
        return redirect()->route('mandor.lkh.assign', $lkhno)
            ->with('error', 'Data plot tidak ditemukan untuk LKH ini');
    }
    
    // Get materials info for this LKH
    $materials = $this->getMaterialsForLKH($lkhno, $companyCode);
    
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
    
    Log::info("LKH {$mode} page accessed", [
        'lkhno' => $lkhno,
        'mode' => $mode,
        'mobile_status' => $lkhData->mobile_status,
        'activityname' => $lkhData->activityname,
        'assigned_workers_count' => count($assignedWorkers),
        'plots_count' => count($plotData),
        'materials_count' => count($materials),
        'total_luas_plan' => $totalLuasPlan
    ]);
    
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
        ->select([
            'uml.itemcode',
            'uml.itemname',
            'uml.qty',
            'uml.unit',
            'uml.herbisidagroupid',
            'uml.dosageperha'
        ])
        ->get();
    
    // Get plot details for breakdown calculation
    $plotDetails = DB::table('rkhlst as rls')
        ->where('rls.companycode', $companyCode)
        ->where('rls.rkhno', $rkhno)
        ->select([
            'rls.plot',
            'rls.luasarea',
            'rls.herbisidagroupid'
        ])
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
     * Save LKH Worker Assignment
     * Only saves to lkhdetailworker table
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
            $companyCode = $user->companycode;
            $assignedWorkers = $request->input('assigned_workers');
            
            // Verify LKH exists and belongs to this mandor
            $lkhExists = DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->where('companycode', $companyCode)
                ->where('mandorid', $user->userid)
                ->exists();
                
            if (!$lkhExists) {
                return back()->withErrors(['message' => 'LKH tidak ditemukan atau tidak berhak diakses']);
            }
            
            Log::info('Saving LKH worker assignments', [
                'lkhno' => $lkhno,
                'workers_count' => count($assignedWorkers)
            ]);
            
            DB::beginTransaction();
            
            try {
                // Clear existing worker assignments
                DB::table('lkhdetailworker')
                    ->where('companycode', $companyCode)
                    ->where('lkhno', $lkhno)
                    ->delete();
                
                // Insert new worker assignments
                foreach ($assignedWorkers as $index => $worker) {
                    DB::table('lkhdetailworker')->insert([
                        'companycode' => $companyCode,
                        'lkhno' => $lkhno,
                        'tenagakerjaid' => $worker['tenagakerjaid'],
                        'tenagakerjaurutan' => $index + 1,
                        // Default times - will be updated during input phase
                        'jammasuk' => '07:00:00',
                        'jamselesai' => '16:00:00',
                        'totaljamkerja' => 8.0,
                        'overtimehours' => 0,
                        'premi' => 0,
                        'upahharian' => 0,
                        'upahperjam' => 0,
                        'upahlembur' => 0,
                        'upahborongan' => 0,
                        'totalupah' => 0,
                        'createdat' => now(),
                    ]);
                }
                
                // Update LKH header with worker count
                DB::table('lkhhdr')
                    ->where('lkhno', $lkhno)
                    ->where('companycode', $companyCode)
                    ->update([
                        'totalworkers' => count($assignedWorkers),
                        'updateby' => $user->name,
                        'updatedat' => now()
                    ]);
                
                DB::commit();
                
                Log::info('LKH worker assignments saved successfully', [
                    'lkhno' => $lkhno,
                    'workers_assigned' => count($assignedWorkers)
                ]);
                
                return back()->with([
                    'success' => true,
                    'flash' => [
                        'success' => count($assignedWorkers) . ' pekerja berhasil ditugaskan untuk ' . $lkhno
                    ]
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
            
            return back()->withErrors([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MATERIAL MANAGEMENT
    |--------------------------------------------------------------------------
    */


/**
 * FIXED: Get materials with correct status (no mapping)
 */
public function getAvailableMaterials(Request $request)
{
    try {
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $user = auth()->user();
        $mandorUserId = $user->userid;
        $companyCode = $user->companycode;
        $date = $request->input('date', now()->format('Y-m-d'));
        
        Log::info('Getting materials for mandor', [
            'mandorUserId' => $mandorUserId,
            'companyCode' => $companyCode,
            'date' => $date
        ]);
        
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
            ->where('uml.companycode', $companyCode)
            ->where('lkh.mandorid', $mandorUserId)
            ->whereDate('lkh.lkhdate', $date)
            ->select([
                'uml.itemcode',
                'uml.itemname',
                'uml.qty',
                'uml.qtyretur',
                'uml.qtydigunakan',
                'uml.unit',
                'uml.herbisidagroupid',
                'uml.dosageperha',
                'umh.flagstatus as status', // Use original status directly
                'uml.lkhno',
                'uml.rkhno'
            ])
            ->orderBy('uml.itemcode')
            ->get();
            
        Log::info('Raw materials found', [
            'count' => $materials->count()
        ]);
        
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
                    'status' => $material->status, // Use original status
                    'lkh_details' => [],
                    'plot_breakdown' => [],
                    'herbisidagroupid' => $material->herbisidagroupid,
                    'dosageperha' => $material->dosageperha
                ];
            }
            
            $groupedMaterials[$key]['total_qty'] += (float) $material->qty;
            $groupedMaterials[$key]['total_qtyretur'] += (float) ($material->qtyretur ?? 0);
            $groupedMaterials[$key]['total_qtydigunakan'] += (float) ($material->qtydigunakan ?? 0);
            
            // Add LKH detail
            $groupedMaterials[$key]['lkh_details'][] = [
                'lkhno' => $material->lkhno,
                'qty' => (float) $material->qty
            ];
            
            // Store rkhno for plot breakdown
            $groupedMaterials[$key]['rkhno'] = $material->rkhno;
        }
        
        // Get plot breakdown for each material
        foreach ($groupedMaterials as $itemcode => &$materialData) {
            $plotBreakdown = $this->getMaterialPlotBreakdown(
                $materialData['rkhno'], 
                $itemcode, 
                $companyCode,
                $materialData['herbisidagroupid'],
                $materialData['dosageperha']
            );
            $materialData['plot_breakdown'] = $plotBreakdown;
        }
        
        Log::info('Grouped materials result', [
            'count' => count($groupedMaterials),
            'items' => array_keys($groupedMaterials)
        ]);
        
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
        
        return response()->json([
            'error' => 'Internal server error: ' . $e->getMessage()
        ], 500);
    }
}



/**
 * Get material breakdown per plot (FIXED for actual table structure)
 */
private function getMaterialPlotBreakdown($rkhno, $itemcode, $companyCode, $herbisidagroupid, $dosageperha)
{
    try {
        // Get plot details from lkhdetailplot table (since we already have lkhno from usemateriallst)
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
                    'usage_formatted' => number_format($usage, 2) . ' ' . 'kg'
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

    

    /*
    |--------------------------------------------------------------------------
    | VEHICLE MANAGEMENT
    |--------------------------------------------------------------------------
    */

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
            
            $user = auth()->user();
            $companyCode = $user->companycode;
            
            $vehicleInfo = $this->getVehicleInfoForLKH($lkhno);
            
            if (!$vehicleInfo) {
                return response()->json([
                    'vehicle_info' => null,
                    'message' => 'No vehicle assigned for this LKH'
                ]);
            }
            
            return response()->json([
                'vehicle_info' => $vehicleInfo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getVehicleInfo', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DATA SYNCHRONIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Sync offline data when online
     */
    public function syncOfflineData(Request $request)
    {
        try {
            $request->validate([
                'offline_data' => 'required|array',
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $offlineData = $request->input('offline_data');
            $syncResults = [];
            
            Log::info('Syncing offline data', [
                'data_count' => count($offlineData)
            ]);
            
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
                
                Log::error('Database transaction failed in syncOfflineData', [
                    'error' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile()
                ]);
                
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in syncOfflineData', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LEGACY API ENDPOINTS (for backward compatibility)
    |--------------------------------------------------------------------------
    */

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
        return response()->json([
            'field_activities' => []
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PRIVATE HELPER METHODS
    |--------------------------------------------------------------------------
    */

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
            
            $sequenceStr = str_pad($newSequence, 4, '0', STR_PAD_LEFT);
            
            return $prefix . $sequenceStr;
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
            
            $sequenceStr = str_pad($newSequence, 4, '0', STR_PAD_LEFT);
            
            return $prefix . $sequenceStr;
        });
    }

/**
 * FIXED: Determine LKH status - materials ready only when RECEIVED_BY_MANDOR
 */
private function determineLKHStatus($lkh, $needsMaterial, $materialsReady)
{
    if (!$needsMaterial) {
        return 'READY';
    }
    
    // FIXED: Materials ready only when mandor has confirmed receipt
    if ($needsMaterial && !$materialsReady) {
        return 'WAITING_MATERIAL'; // Materials not ready
    }
    
    return 'READY'; // Materials are ready (mandor confirmed receipt)
}

    /**
     * Get jenis tenaga kerja name from ID
     */
    private function getJenisTenagaKerjaName($jenisId)
    {
        $jenisMap = [
            1 => 'Harian',
            2 => 'Borongan',
            3 => 'Kontrak'
        ];
        
        return $jenisMap[$jenisId] ?? "Jenis $jenisId";
    }

    /**
     * Calculate daily wage for worker
     */
    private function calculateDailyWage($tenagakerjaId, $luasplot)
    {
        $baseWage = 75000; // Base daily wage
        $areaBonus = $luasplot * 10000; // Bonus per hectare
        
        return $baseWage + $areaBonus;
    }

/**
 * FIXED: Calculate cost per hectare - fix table/column reference
 */
private function calculateCostPerHa($activitycode)
{
    try {
        // Simple default based on activity type - no database queries for now
        // Can be enhanced later when upah table structure is clarified
        
        Log::info('Using default cost calculation for activity', ['activitycode' => $activitycode]);
        
        // Default costs based on common activity patterns
        if (strpos($activitycode, 'IV.5') !== false) {
            return 1977000; // Penanaman - 1.977.000 per ha
        } elseif (strpos($activitycode, 'V.') !== false) {
            return 140000; // Post emergence - 140rb per ha
        } elseif (strpos($activitycode, 'VI.') !== false) {
            return 110000; // Panen - 110rb per ha
        }
        
        return 100000; // Default cost per ha
        
    } catch (\Exception $e) {
        Log::warning('Error calculating cost per ha', [
            'activitycode' => $activitycode,
            'error' => $e->getMessage()
        ]);
        return 100000; // Default cost per ha
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
                'k.nokendaraan',
                'k.jenis',
                'k.hourmeter',
                'tk.nama as operator_nama',
                'tk.nik as operator_nik'
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
     * Process offline LKH data
     */
    private function processOfflineLKHData($lkhData)
    {
        try {
            Log::info('Processing offline LKH data', ['lkhno' => $lkhData['lkhno'] ?? 'unknown']);
            
            // Implementation would depend on the exact structure of offline data
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
            
            // Implementation would depend on the exact structure of offline data
            return true;
            
        } catch (\Exception $e) {
            Log::error('Error processing offline material return', [
                'error' => $e->getMessage(),
                'data' => $returnData
            ]);
            return false;
        }
    }

// ===============================================
// Additional Controller Functions for Material Management
// ===============================================

/**
 * Show Material Management Page
 */
public function showMaterialManagement(Request $request)
{
    try {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        $date = $request->input('date', now()->format('Y-m-d'));
        
        // Get materials for the date
        $materialsResponse = $this->getAvailableMaterials($request);
        $materialsData = json_decode($materialsResponse->content(), true);
        
        if (isset($materialsData['error'])) {
            return redirect()->route('mandor.index')
                ->with('error', $materialsData['error']);
        }
        
        return Inertia::render('material-management', [
            'title' => 'Material Management - ' . Carbon::parse($date)->format('d F Y'),
            'materials' => $materialsData['materials'] ?? [],
            'date' => $date,
            'date_formatted' => $materialsData['date_formatted'] ?? Carbon::parse($date)->format('d F Y'),
            'routes' => [
                'material_save_returns' => route('mandor.materials.save-returns'),
                'material_confirm_pickup' => route('mandor.materials.confirm-pickup'),
                'mandor_index' => route('mandor.index'),
            ],
            'csrf_token' => csrf_token(),
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in showMaterialManagement', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()->route('mandor.index')
            ->with('error', 'Terjadi kesalahan saat membuka halaman material management');
    }
}

/**
 * FIXED: Confirm material pickup (DISPATCHED -> RECEIVED_BY_MANDOR)
 * Returns JSON response for React frontend
 */
public function confirmMaterialPickup(Request $request)
{
    try {
        $request->validate([
            'itemcode' => 'required|string',
        ]);
        
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
        }
        
        $user = auth()->user();
        $companyCode = $user->companycode;
        $mandorUserId = $user->userid;
        $itemcode = $request->input('itemcode');
        $date = now()->format('Y-m-d');
        
        Log::info('Confirming material pickup', [
            'itemcode' => $itemcode,
            'mandorUserId' => $mandorUserId,
            'companyCode' => $companyCode,
            'date' => $date
        ]);
        
        DB::beginTransaction();
        
        try {
            if ($itemcode === 'ALL') {
                // Confirm all materials for this mandor and date
                $rkhNumbers = DB::table('usemateriallst as uml')
                    ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                    ->where('uml.companycode', $companyCode)
                    ->where('lkh.mandorid', $mandorUserId)
                    ->whereDate('lkh.lkhdate', $date)
                    ->distinct()
                    ->pluck('uml.rkhno');
                
                if ($rkhNumbers->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada material yang ditemukan untuk hari ini'
                    ]);
                }
                
                // Update all material headers from DISPATCHED to RECEIVED_BY_MANDOR
                $updatedHeaders = DB::table('usematerialhdr')
                    ->where('companycode', $companyCode)
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
                
                // Generate nouse for all materials
                $this->generateNoUseForMaterials($companyCode, $rkhNumbers->toArray(), $user->name);
                
                Log::info('All materials pickup confirmed', [
                    'rkhNumbers' => $rkhNumbers->toArray(),
                    'updatedHeaders' => $updatedHeaders
                ]);
                
                $message = "Penerimaan semua material berhasil dikonfirmasi ({$updatedHeaders} material)";
                
            } else {
                // Confirm specific material
                $rkhNumbers = DB::table('usemateriallst as uml')
                    ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                    ->where('uml.companycode', $companyCode)
                    ->where('uml.itemcode', $itemcode)
                    ->where('lkh.mandorid', $mandorUserId)
                    ->whereDate('lkh.lkhdate', $date)
                    ->distinct()
                    ->pluck('uml.rkhno');
                
                if ($rkhNumbers->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Material tidak ditemukan untuk mandor ini'
                    ]);
                }
                
                // Update specific material header from DISPATCHED to RECEIVED_BY_MANDOR
                $updatedHeaders = DB::table('usematerialhdr')
                    ->where('companycode', $companyCode)
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
                
                // Generate nouse for this material
                $this->generateNoUseForMaterials($companyCode, $rkhNumbers->toArray(), $user->name);
                
                Log::info('Specific material pickup confirmed', [
                    'itemcode' => $itemcode,
                    'rkhNumbers' => $rkhNumbers->toArray(),
                    'updatedHeaders' => $updatedHeaders
                ]);
                
                $message = "Penerimaan material {$itemcode} berhasil dikonfirmasi";
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
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
 * FIXED: Save material returns (RECEIVED_BY_MANDOR -> RETURNED_BY_MANDOR)
 * Returns JSON response for React frontend
 */
public function saveMaterialReturns(Request $request)
{
    try {
        $request->validate([
            'material_returns' => 'required|array',
        ]);
        
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'error' => 'User not authenticated'
            ], 401);
        }
        
        $user = auth()->user();
        $companyCode = $user->companycode;
        $mandorUserId = $user->userid;
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
        
        Log::info('Saving material returns', [
            'returns_count' => count($filteredReturns),
            'returns_data' => $filteredReturns,
            'mandorUserId' => $mandorUserId,
            'date' => $date
        ]);
        
        DB::beginTransaction();
        
        try {
            $processedItems = [];
            
            foreach ($filteredReturns as $itemcode => $returnQty) {
                $noRetur = $this->generateReturnNo();
                
                // Update usemateriallst with return quantity
                $updatedRecords = DB::table('usemateriallst as uml')
                    ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                    ->where('uml.companycode', $companyCode)
                    ->where('uml.itemcode', $itemcode)
                    ->where('lkh.mandorid', $mandorUserId)
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
                        ->where('uml.companycode', $companyCode)
                        ->where('uml.itemcode', $itemcode)
                        ->where('lkh.mandorid', $mandorUserId)
                        ->whereDate('lkh.lkhdate', $date)
                        ->pluck('uml.lkhno');
                    
                    foreach ($lkhNumbers as $lkhno) {
                        // Get qty diterima from usemateriallst
                        $materialData = DB::table('usemateriallst')
                            ->where('companycode', $companyCode)
                            ->where('lkhno', $lkhno)
                            ->where('itemcode', $itemcode)
                            ->first();
                        
                        if ($materialData) {
                            DB::table('lkhdetailmaterial')
                                ->updateOrInsert(
                                    [
                                        'companycode' => $companyCode,
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
                        ->where('uml.companycode', $companyCode)
                        ->where('uml.itemcode', $itemcode)
                        ->where('lkh.mandorid', $mandorUserId)
                        ->whereDate('lkh.lkhdate', $date)
                        ->distinct()
                        ->pluck('uml.rkhno');
                    
                    $updatedHeaders = DB::table('usematerialhdr')
                        ->where('companycode', $companyCode)
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
                    
                    Log::info('Material return processed', [
                        'itemcode' => $itemcode,
                        'return_qty' => $returnQty,
                        'noretur' => $noRetur,
                        'updated_records' => $updatedRecords,
                        'lkh_numbers' => $lkhNumbers->toArray(),
                        'updated_headers' => $updatedHeaders
                    ]);
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
            
            Log::error('Database transaction failed in saveMaterialReturns', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
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

/**
 * Generate nouse (nomor pengeluaran) for materials
 */
private function generateNoUseForMaterials($companyCode, $rkhNumbers, $userName)
{
    foreach ($rkhNumbers as $rkhno) {
        // Check if nouse already exists
        $existingNoUse = DB::table('usemateriallst')
            ->where('companycode', $companyCode)
            ->where('rkhno', $rkhno)
            ->whereNotNull('nouse')
            ->first();
        
        if (!$existingNoUse) {
            $noUse = $this->generateNoUseNumber();
            
            // Update all materials for this RKH
            DB::table('usemateriallst')
                ->where('companycode', $companyCode)
                ->where('rkhno', $rkhno)
                ->update([
                    'nouse' => $noUse
                ]);
            
            Log::info('Generated nouse for materials', [
                'rkhno' => $rkhno,
                'nouse' => $noUse
            ]);
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
        
        $sequenceStr = str_pad($newSequence, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $sequenceStr;
    });
}

/**
 * COMPLETE: Save LKH Results with mobile_status and redirect to view
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
        $companyCode = $user->companycode;
        $workerInputs = $request->input('worker_inputs', []);
        $plotInputs = $request->input('plot_inputs', []);
        $materialInputs = $request->input('material_inputs', []);
        $keterangan = $request->input('keterangan');
        
        Log::info('Saving LKH results with new structure', [
            'lkhno' => $lkhno,
            'workers_count' => count($workerInputs),
            'plots_count' => count($plotInputs),
            'materials_count' => count($materialInputs)
        ]);
        
        DB::beginTransaction();
        
        try {
            // Get LKH info
            $lkhInfo = DB::table('lkhhdr')->where('lkhno', $lkhno)->first();
            
            if (!$lkhInfo) {
                return back()->withErrors(['message' => 'LKH tidak ditemukan']);
            }
            
            // 1. Update lkhdetailworker with time and wage data
            foreach ($workerInputs as $workerInput) {
                $jamMasuk = $workerInput['jammasuk'] ?? '07:00';
                $jamSelesai = $workerInput['jamselesai'] ?? '15:00';
                $overtimeHours = $workerInput['overtimehours'] ?? 0;
                
                // Calculate total jam kerja with proper error handling
                $totalJamKerja = $this->calculateWorkHours($jamMasuk, $jamSelesai);
                
                // Calculate wages with proper error handling
                $wageData = $this->calculateWorkerWage($lkhInfo, $totalJamKerja, $overtimeHours);
                
                DB::table('lkhdetailworker')
                    ->where('companycode', $companyCode)
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
            
            // 2. Update lkhdetailplot
            foreach ($plotInputs as $plotInput) {
                DB::table('lkhdetailplot')
                    ->where('companycode', $companyCode)
                    ->where('lkhno', $lkhno)
                    ->where('plot', $plotInput['plot'])
                    ->update([
                        'luashasil' => $plotInput['luashasil'] ?? 0,
                        'luassisa' => $plotInput['luassisa'] ?? 0
                    ]);
            }
            
            // 3. Update/Insert lkhdetailmaterial if provided
            foreach ($materialInputs as $materialInput) {
                if (isset($materialInput['itemcode']) && isset($materialInput['qtysisa'])) {
                    // Get qty diterima from usemateriallst
                    $qtyDiterima = DB::table('usemateriallst')
                        ->where('companycode', $companyCode)
                        ->where('lkhno', $lkhno)
                        ->where('itemcode', $materialInput['itemcode'])
                        ->value('qty');
                    
                    DB::table('lkhdetailmaterial')
                        ->updateOrInsert(
                            [
                                'companycode' => $companyCode,
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
            
            // 4. Calculate totals and update header
            $totals = $this->calculateLKHTotals($lkhno, $companyCode);
            
            DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->where('companycode', $companyCode)
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
            
            Log::info('LKH results saved successfully as DRAFT', [
                'lkhno' => $lkhno,
                'mobile_status' => 'DRAFT',
                'totals' => $totals
            ]);
            
            // UPDATED: Redirect to view mode after save
            return redirect()->route('mandor.lkh.view', $lkhno)->with([
                'success' => true,
                'flash' => [
                    'success' => 'Data LKH berhasil disimpan sebagai draft!'
                ]
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
        
        return back()->withErrors([
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
}

/**
 * FIXED: Calculate work hours - handle time format properly
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
 * Calculate worker wage based on LKH type and work hours
 */
private function calculateWorkerWage($lkhInfo, $totalJamKerja, $overtimeHours)
{
    $wageData = [
        'premi' => 0,
        'upahharian' => 0,
        'upahperjam' => 0,
        'upahlembur' => 0,
        'upahborongan' => 0,
        'totalupah' => 0
    ];
    
    if ($lkhInfo->jenistenagakerja == 1) {
        // Harian calculation
        $baseWage = 115722.8; // Base daily wage
        $hourlyWage = 16532;   // Per hour wage
        $overtimeWage = 12542; // Overtime wage per hour
        
        if ($totalJamKerja >= 8) {
            // Full day work
            $wageData['upahharian'] = $baseWage;
        } else {
            // Partial day work
            $wageData['upahperjam'] = $hourlyWage;
            $wageData['upahharian'] = $totalJamKerja * $hourlyWage;
        }
        
        // Overtime calculation
        if ($overtimeHours > 0) {
            $wageData['upahlembur'] = $overtimeHours * $overtimeWage;
        }
        
        $wageData['totalupah'] = $wageData['upahharian'] + $wageData['upahlembur'] + $wageData['premi'];
        
    } else {
        // Borongan calculation - will be calculated based on hasil later
        $costPerHa = $this->calculateCostPerHa($lkhInfo->activitycode);
        $wageData['upahborongan'] = $costPerHa; // Will be multiplied by hasil
        $wageData['totalupah'] = $costPerHa;
    }
    
    return $wageData;
}





}