<?php

namespace App\Http\Controllers\React;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

// Models
use App\Models\User;
use App\Models\TenagaKerja;
use App\Models\AbsenHdr;
use App\Models\AbsenLst;
use App\Models\Transaction\LkhHdr;
use App\Models\Lkhlst;
use App\Models\Transaction\RkhHdr;
use App\Models\Transaction\RkhLst;
use App\Models\MasterData\Activity;
use App\Models\Kendaraan;
use App\Models\usematerialhdr;
use App\Models\usemateriallst;
use App\Models\MasterData\Upah;

class MandorPageController extends Controller
{
    // =============================================================================
    // MAIN DASHBOARD & ENTRY POINTS
    // =============================================================================

   /**
     * Main SPA Dashboard entry point - FIXED: Include all required routes
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        return Inertia::render('index', [
            'title' => 'Mandor Dashboard',
            'user' => [
                'id' => $user->userid,
                'name' => $user->name,
                'userid' => $user->userid,
                'companycode' => $user->companycode,
                'company_name' => $user->company->name ?? null,
            ],
            'csrf_token' => csrf_token(),
            'routes' => [
                'logout' => route('logout'),
                'home' => route('home'),
                'mandor_index' => route('mandor.index'),
                
                // Attendance routes - FIXED: Add missing routes
                'workers' => route('mandor.workers'),
                'attendance_today' => route('mandor.attendance.today'),  
                'process_checkin' => route('mandor.attendance.process-checkin'),
                'update_photo' => route('mandor.attendance.update-photo'), // ADDED
                'rejected_attendance' => route('mandor.attendance.rejected'), // ADDED
                
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
     * Get attendance for specific date - FIXED for absentype support
     */
    public function getTodayAttendance(Request $request)
    {
        try {
            $date = $request->input('date', now()->format('Y-m-d'));
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            
            // FIXED: Check if columns exist before querying
            $attendance = DB::table('absenlst as al')
                ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
                ->where('ah.companycode', $user->companycode)
                ->where('ah.mandorid', $user->userid)
                ->whereDate('al.absenmasuk', $date)
                ->select([
                    'al.absenno',
                    'al.id',
                    'al.tenagakerjaid',
                    'al.absenmasuk',
                    // NEW: Check if column exists with DB::raw and COALESCE
                    DB::raw("COALESCE(al.absentype, 'HADIR') as absentype"),
                    DB::raw("al.checkintime"),
                    'al.fotoabsen',
                    'al.lokasifotolat',
                    'al.lokasifotolng',
                    DB::raw("COALESCE(al.approval_status, 'PENDING') as approval_status"),
                    'al.approval_date',
                    'al.approved_by',
                    'al.rejection_reason',
                    'al.rejection_date',
                    DB::raw("COALESCE(al.is_edited, 0) as is_edited"),
                    DB::raw("COALESCE(al.edit_count, 0) as edit_count"),
                    'tk.nama',
                    'tk.nik',
                    'tk.gender',
                    'tk.jenistenagakerja'
                ])
                ->orderBy('al.absenmasuk')
                ->get()
                ->map(function($record) {
                    return [
                        'absenno' => $record->absenno ?? 'N/A',
                        'absen_id' => $record->id ?? 0,
                        'tenagakerjaid' => $record->tenagakerjaid,
                        'absenmasuk' => $record->absenmasuk,
                        'absentype' => $record->absentype ?? 'HADIR',
                        'checkintime' => $record->checkintime,
                        'fotoabsen' => $record->fotoabsen,
                        'lokasifotolat' => $record->lokasifotolat,
                        'lokasifotolng' => $record->lokasifotolng,
                        'approval_status' => $record->approval_status ?? 'PENDING',
                        'approval_date' => $record->approval_date,
                        'approved_by' => $record->approved_by,
                        'rejection_reason' => $record->rejection_reason,
                        'rejection_date' => $record->rejection_date,
                        'is_edited' => (bool) ($record->is_edited ?? false),
                        'edit_count' => (int) ($record->edit_count ?? 0),
                        'tenaga_kerja' => [
                            'nama' => $record->nama,
                            'nik' => $record->nik,
                            'gender' => $record->gender,
                            'jenistenagakerja' => $record->jenistenagakerja
                        ]
                    ];
                });
            
            // Group by type
            $groupedByType = [
                'hadir' => $attendance->where('absentype', 'HADIR')->values(),
                'lokasi' => $attendance->where('absentype', 'LOKASI')->values(),
            ];
            
            return response()->json([
                'attendance' => $attendance->toArray(),
                'grouped_by_type' => $groupedByType,
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y'),
                'summary' => [
                    'total' => $attendance->count(),
                    'hadir_count' => $groupedByType['hadir']->count(),
                    'lokasi_count' => $groupedByType['lokasi']->count(),
                    'approved_count' => $attendance->where('approval_status', 'APPROVED')->count(),
                    'pending_count' => $attendance->where('approval_status', 'PENDING')->count(),
                    'rejected_count' => $attendance->where('approval_status', 'REJECTED')->count(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getTodayAttendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
                'attendance' => [] // Return empty array to prevent frontend crash
            ], 500);
        }
    }

    /**
     * Process check-in with photo - UPDATED: Support HADIR & LOKASI + File Storage
     */
    public function processCheckIn(Request $request)
    {
        try {
            $request->validate([
                'tenagakerjaid' => 'required|string',
                'photo' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'absentype' => 'required|in:HADIR,LOKASI', // NEW: Type validation
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $today = now()->format('Y-m-d');
            $absenType = $request->input('absentype', 'HADIR');
            
            // Check if worker exists and belongs to this mandor
            $worker = TenagaKerja::where('tenagakerjaid', $request->tenagakerjaid)
                ->where('mandoruserid', $user->userid)
                ->where('companycode', $user->companycode)
                ->where('isactive', 1)
                ->first();
                
            if (!$worker) {
                return response()->json(['error' => 'Pekerja tidak ditemukan atau tidak terdaftar pada mandor ini'], 404);
            }
            
            // UPDATED: Check based on type
            if ($absenType === 'HADIR') {
                // Check if already checked in HADIR today
                $existingHadir = DB::table('absenlst as al')
                    ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                    ->where('ah.companycode', $user->companycode)
                    ->where('ah.mandorid', $user->userid)
                    ->where('al.tenagakerjaid', $request->tenagakerjaid)
                    ->where('al.absentype', 'HADIR')
                    ->whereDate('al.absenmasuk', $today)
                    ->exists();
                
                if ($existingHadir) {
                    return response()->json(['error' => 'Pekerja sudah absen HADIR hari ini'], 400);
                }
            } else {
                // LOKASI: Validate GPS required
                if (!$request->latitude || !$request->longitude) {
                    return response()->json(['error' => 'GPS coordinates wajib untuk absen LOKASI'], 400);
                }
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
                
                // NEW: Save photo to file storage and get URL
                $photoUrl = $this->savePhotoToStorage(
                    $request->photo,
                    $request->tenagakerjaid,
                    $absenType
                );
                
                // NEW: Prepare checkintime
                $checkinTime = ($absenType === 'LOKASI') ? now() : null;
                
                // Create AbsenLst record with PENDING approval status
                DB::table('absenlst')->insert([
                    'absenno' => $absenHdr->absenno,
                    'id' => $nextId,
                    'tenagakerjaid' => $request->tenagakerjaid,
                    'absenmasuk' => now(), // Server timestamp
                    'absentype' => $absenType, // NEW
                    'checkintime' => $checkinTime, // NEW
                    'keterangan' => "Absen {$absenType} dengan foto via mobile app",
                    'fotoabsen' => $photoUrl, // NEW: URL instead of base64
                    'lokasifotolat' => $request->latitude,
                    'lokasifotolng' => $request->longitude,
                    'approval_status' => 'PENDING',
                    'createdat' => now(),
                    'updatedat' => now()
                ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Absen {$absenType} berhasil dicatat dengan foto (menunggu approval)",
                    'data' => [
                        'absenno' => $absenHdr->absenno,
                        'absen_id' => $nextId,
                        'tenagakerjaid' => $request->tenagakerjaid,
                        'worker_name' => $worker->nama,
                        'absentype' => $absenType,
                        'time' => now()->format('H:i'),
                        'server_timestamp' => now()->toIso8601String(), // NEW: Return server time
                        'approval_status' => 'PENDING',
                        'total_today' => $absenHdr->totalpekerja,
                        'photo_url' => $photoUrl, // NEW
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

    /**
     * Update attendance photo - UPDATED: Support file storage
     */
    public function updateAttendancePhoto(Request $request)
    {
        try {
            $request->validate([
                'absenno' => 'required|string',
                'absen_id' => 'required|integer',
                'tenagakerjaid' => 'required|string',
                'photo' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            
            // Verify the attendance record belongs to this mandor
            $attendanceRecord = DB::table('absenlst as al')
                ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                ->where('al.absenno', $request->absenno)
                ->where('al.id', $request->absen_id)
                ->where('al.tenagakerjaid', $request->tenagakerjaid)
                ->where('ah.mandorid', $user->userid)
                ->where('ah.companycode', $user->companycode)
                ->select(['al.approval_status', 'al.absenno', 'al.id', 'al.fotoabsen', 'al.absentype'])
                ->first();
            
            if (!$attendanceRecord) {
                return response()->json(['error' => 'Record absensi tidak ditemukan atau tidak berhak diakses'], 404);
            }
            
            // Check if already approved - don't allow edit if approved
            if ($attendanceRecord->approval_status === 'APPROVED') {
                return response()->json(['error' => 'Tidak dapat mengedit foto yang sudah diapprove'], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // NEW: Delete old photo file
                if ($attendanceRecord->fotoabsen) {
                    $this->deletePhotoFromStorage($attendanceRecord->fotoabsen);
                }
                
                // NEW: Save new photo to storage
                $photoUrl = $this->savePhotoToStorage(
                    $request->photo,
                    $request->tenagakerjaid,
                    $attendanceRecord->absentype
                );
                
                // Update photo and reset approval status
                $updated = DB::table('absenlst')
                    ->where('absenno', $request->absenno)
                    ->where('id', $request->absen_id)
                    ->update([
                        'fotoabsen' => $photoUrl, // NEW: URL instead of base64
                        'lokasifotolat' => $request->latitude,
                        'lokasifotolng' => $request->longitude,
                        'approval_status' => 'PENDING',
                        'approval_date' => null,
                        'approved_by' => null,
                        'rejection_reason' => null,
                        'rejection_date' => null,
                        'is_edited' => true,
                        'edit_count' => DB::raw('COALESCE(edit_count, 0) + 1'),
                        'last_edited_at' => now(),
                        'updatedat' => now()
                    ]);
                
                if (!$updated) {
                    return response()->json(['error' => 'Gagal mengupdate foto'], 500);
                }
                
                // Get worker name for response
                $worker = TenagaKerja::where('tenagakerjaid', $request->tenagakerjaid)->first();
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Foto absensi berhasil diupdate (status direset ke PENDING)',
                    'data' => [
                        'absenno' => $request->absenno,
                        'absen_id' => $request->absen_id,
                        'tenagakerjaid' => $request->tenagakerjaid,
                        'worker_name' => $worker->nama ?? 'Unknown',
                        'approval_status' => 'PENDING',
                        'photo_url' => $photoUrl, // NEW
                        'updated_at' => now()->format('Y-m-d H:i:s'),
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in updateAttendancePhoto', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    // =============================================================================
    // LKH MANAGEMENT - DATA RETRIEVAL
    // =============================================================================

    /**
     * Get ready LKH list with mobile_status included - FIXED VERSION
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
                ->where('lkh.companycode', $user->companycode)
                ->where('lkh.mandorid', $user->userid)
                ->whereDate('lkh.lkhdate', $date)
                ->where('lkh.status', '!=', 'COMPLETED')
                ->select([
                    'lkh.lkhno', 'lkh.activitycode', 'act.activityname', 'act.description as activity_description',
                    'lkh.totalluasactual', 'lkh.jenistenagakerja', 'lkh.status as lkh_status',
                    'lkh.totalworkers as estimated_workers', 'lkh.rkhno', 'lkh.mobile_status'
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
                
                // Materials ready check - FIXED LOGIC
                $materialsReady = true;
                if ($needsMaterial) {
                    // Get ALL material statuses for this RKH
                    $materialStatuses = DB::table('usematerialhdr')
                        ->where('companycode', $user->companycode)
                        ->where('rkhno', $lkhRecord->rkhno)
                        ->pluck('flagstatus');
                    
                    // Define ready statuses
                    $readyStatuses = ['RECEIVED_BY_MANDOR', 'RETURNED_BY_MANDOR', 'RETURN_RECEIVED', 'COMPLETED'];
                    
                    // Materials are ready if we have materials and ALL are in ready status
                    $materialsReady = $materialStatuses->isNotEmpty() && 
                        $materialStatuses->every(function($status) use ($readyStatuses) {
                            return in_array($status, $readyStatuses);
                        });
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
     * REFACTORED: Complete All LKH - Updated for per-plot material structure
     * Each LKH gets its own material return calculation per plot
     * Updated to work with new usemateriallst primary key: (companycode, lkhno, plot, itemcode)
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
                // Get all LKH for this mandor and date
                $allLKH = DB::table('lkhhdr')
                    ->where('companycode', $user->companycode)
                    ->where('mandorid', $user->userid)
                    ->whereDate('lkhdate', $date)
                    ->select(['lkhno', 'mobile_status', 'rkhno'])
                    ->get();
                
                if ($allLKH->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada LKH yang ditemukan untuk tanggal ini'
                    ]);
                }
                
                // STRICT CHECK: All must be DRAFT
                $draftLKH = $allLKH->where('mobile_status', 'DRAFT');
                $emptyLKH = $allLKH->where('mobile_status', 'EMPTY');
                
                if ($draftLKH->count() !== $allLKH->count()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Semua LKH harus diselesaikan terlebih dahulu. " .
                                    "Status: {$draftLKH->count()}/{$allLKH->count()} LKH sudah diinput. " .
                                    "Silakan selesaikan {$emptyLKH->count()} LKH yang belum dikerjakan."
                    ]);
                }
                
                // Process each DRAFT LKH individually
                $processedLKH = [];
                $materialUpdates = [];
                
                foreach ($draftLKH as $lkh) {
                    // 1. Update LKH status to COMPLETED
                    DB::table('lkhhdr')
                        ->where('lkhno', $lkh->lkhno)
                        ->where('companycode', $user->companycode)
                        ->update([
                            'mobile_status' => 'COMPLETED',
                            'status' => 'DRAFT',
                            'updateby' => $user->name,
                            'mobileupdatedat' => now()
                        ]);
                    
                    // 2. REFACTORED: Get material usage from lkhdetailmaterial per plot
                    $materialUsagePerPlot = DB::table('lkhdetailmaterial')
                        ->where('companycode', $user->companycode)
                        ->where('lkhno', $lkh->lkhno)
                        ->select(['itemcode', 'plot', 'qtyditerima', 'qtysisa', 'qtydigunakan'])
                        ->get();
                    
                    // 3. REFACTORED: Update usemateriallst PER PLOT (not aggregated per LKH)
                    foreach ($materialUsagePerPlot as $materialPlot) {
                        // Update each plot-specific record in usemateriallst
                        $updatedRecords = DB::table('usemateriallst')
                            ->where('companycode', $user->companycode)
                            ->where('lkhno', $lkh->lkhno)
                            ->where('plot', $materialPlot->plot) // NEW: Include plot in WHERE clause
                            ->where('itemcode', $materialPlot->itemcode)
                            ->update([
                                'qtydigunakan' => $materialPlot->qtydigunakan,
                                'qtyretur' => $materialPlot->qtysisa,
                                'returby' => $user->name,
                                'tglretur' => now()
                            ]);
                        
                        if ($updatedRecords > 0) {
                            $materialUpdates[] = [
                                'lkhno' => $lkh->lkhno,
                                'plot' => $materialPlot->plot, // NEW: Include plot in response
                                'itemcode' => $materialPlot->itemcode,
                                'qty_used' => (float) $materialPlot->qtydigunakan,
                                'qty_returned' => (float) $materialPlot->qtysisa,
                                'records_updated' => $updatedRecords
                            ];
                        }
                    }
                    
                    $processedLKH[] = $lkh->lkhno;
                }
                
                // 4. Update usematerialhdr status to RETURNED_BY_MANDOR
                $rkhNumbers = $allLKH->pluck('rkhno')->unique();
                $updatedHeaders = DB::table('usematerialhdr')
                    ->where('companycode', $user->companycode)
                    ->whereIn('rkhno', $rkhNumbers)
                    ->where('flagstatus', 'RECEIVED_BY_MANDOR')
                    ->update([
                        'flagstatus' => 'RETURNED_BY_MANDOR',
                        'updateby' => $user->name,
                        'updatedat' => now()
                    ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menyelesaikan {$draftLKH->count()} LKH dan menghitung material return per plot",
                    'data' => [
                        'completed_lkh' => $processedLKH,
                        'material_updates' => $materialUpdates,
                        'header_updates' => $updatedHeaders,
                        'total_lkh' => $allLKH->count(),
                        'completed_count' => $draftLKH->count(),
                        'total_plot_records_updated' => count($materialUpdates),
                        'aggregation_method' => 'per_plot_refactored' // NEW: Indicator of per-plot method
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in completeAllLKH with per-plot material structure', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->user()->userid ?? 'unknown',
                'date' => $request->input('date')
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    // =============================================================================
    // LKH MANAGEMENT - PAGE RENDERING
    // =============================================================================

   /**
     * Show LKH Assignment Page - FIXED: Proper route structure for navigation
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
                    'lkh.lkhdate', 'lkh.mobile_status', 'u.name as mandor_nama'
                ])
                ->first();
            
            if (!$lkhData) {
                return redirect()->route('mandor.index')->with('error', 'LKH tidak ditemukan');
            }
            
            // Check if LKH is already completed
            if ($lkhData->mobile_status === 'COMPLETED') {
                return redirect()->route('mandor.lkh.view', $lkhno)->with('info', 'LKH sudah selesai, dialihkan ke mode view');
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
                'user' => [
                    'id' => $user->userid,
                    'name' => $user->name,
                    'email' => $user->email ?? '',
                    'userid' => $user->userid,
                    'companycode' => $user->companycode,
                    'company_name' => $user->company->name ?? null,
                ],
                // FIXED: Single routes object with ALL required routes
                'routes' => [
                    'logout' => route('logout'),
                    'home' => route('home'),
                    'mandor_index' => route('mandor.index'),
                    'workers' => route('mandor.workers'),
                    'attendance_today' => route('mandor.attendance.today'),
                    'process_checkin' => route('mandor.attendance.process-checkin'),
                    'update_photo' => route('mandor.attendance.update-photo'),
                    'rejected_attendance' => route('mandor.attendance.rejected'),
                    'lkh_ready' => route('mandor.lkh.ready'),
                    'materials_available' => route('mandor.materials.available'),
                    'lkh_vehicle_info' => route('mandor.lkh.vehicle-info'),
                    'lkh_assign' => route('mandor.lkh.assign', ['lkhno' => '__LKHNO__']),
                    'materials_save_returns' => route('mandor.materials.save-returns'),
                    'material_confirm_pickup' => route('mandor.materials.confirm-pickup'),
                    'complete_all_lkh' => route('mandor.lkh.complete-all'),
                    'sync_offline_data' => route('mandor.sync-offline-data'),
                    // LKH specific routes
                    'lkh_save_assignment' => route('mandor.lkh.save-assignment', $lkhno),
                    'lkh_input' => route('mandor.lkh.input', $lkhno),
                    'lkh_view' => route('mandor.lkh.view', $lkhno),
                ],
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
                    'mandor_nama' => $lkhData->mandor_nama,
                    'mobile_status' => $lkhData->mobile_status
                ],
                'vehicleInfo' => $vehicleInfo,
                'availableWorkers' => $availableWorkers,
                'existingAssignments' => $existingAssignments,
                'csrf_token' => csrf_token(),
                'app' => [
                    'name' => config('app.name', 'Laravel'),
                    'url' => config('app.url', 'http://localhost'),
                    'logo_url' => asset('img/logo-tebu.png'),
                ],
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
     * Show LKH Input Page - Updated to handle dynamic URLs and status
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
            
            if (!$lkhStatus) {
                return redirect()->route('mandor.index')->with('error', 'LKH tidak ditemukan');
            }
            
            // UPDATED: Handle different statuses
            if ($lkhStatus === 'DRAFT') {
                // Already has input - redirect to view mode instead
                return redirect()->route('mandor.lkh.view', $lkhno)->with('info', 'LKH sudah diinput, dialihkan ke mode view');
            }
            
            if ($lkhStatus === 'COMPLETED') {
                // Completed - redirect to readonly view
                return redirect()->route('mandor.lkh.view', $lkhno)->with('info', 'LKH sudah selesai, dialihkan ke mode readonly');
            }
            
            // Status is EMPTY - proceed with input mode
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
 * Show LKH View Page - UPDATED: With Vehicle BBM Data
 */
public function showLKHView($lkhno)
{
    try {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        
        // Check if LKH exists and belongs to this mandor
        $lkhData = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $user->companycode)
            ->where('mandorid', $user->userid)
            ->select(['mobile_status', 'status', 'keterangan'])
            ->first();
        
        if (!$lkhData) {
            return redirect()->route('mandor.index')->with('error', 'LKH tidak ditemukan');
        }
        
        // Allow view for DRAFT, COMPLETED status
        if (!in_array($lkhData->mobile_status, ['DRAFT', 'COMPLETED'])) {
            return redirect()->route('mandor.index')->with('error', 'LKH tidak tersedia untuk dilihat');
        }
        
        // Determine mode based on mobile_status
        $mode = $lkhData->mobile_status === 'COMPLETED' ? 'view-readonly' : 'view';
        
        // Get vehicle BBM data from kendaraanbbm table
        $vehicleBBMData = $this->getVehicleBBMData($lkhno, $user->companycode);
        
        // Render the form with additional vehicleBBMData
        return $this->renderLKHForm($lkhno, $mode, $vehicleBBMData);
        
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
 * Get Vehicle BBM data from kendaraanbbm table
 */
private function getVehicleBBMData($lkhno, $companyCode)
{
    try {
        $bbmData = DB::table('kendaraanbbm as kb')
            ->join('kendaraan as k', function($join) use ($companyCode) {
                $join->on('kb.nokendaraan', '=', 'k.nokendaraan')
                     ->where('k.companycode', '=', $companyCode)
                     ->where('k.isactive', '=', 1);
            })
            ->leftJoin('tenagakerja as tk', function($join) use ($companyCode) {
                $join->on('kb.operatorid', '=', 'tk.tenagakerjaid')
                     ->where('tk.companycode', '=', $companyCode)
                     ->where('tk.isactive', '=', 1);
            })
            ->where('kb.companycode', $companyCode)
            ->where('kb.lkhno', $lkhno)
            ->select([
                'kb.nokendaraan',
                'k.jenis',
                'tk.nama as operator_nama',
                'kb.plot',
                'kb.jammulai',
                'kb.jamselesai',
                'kb.hourmeterstart',
                'kb.hourmeterend', 
                'kb.solar',
                'kb.createdat',
                'kb.adminupdatedat'
            ])
            ->orderBy('kb.nokendaraan')
            ->orderBy('kb.plot')
            ->get();
        
        if ($bbmData->isEmpty()) {
            return [];
        }
        
        // Group by vehicle and calculate work duration
        $groupedData = [];
        
        foreach ($bbmData as $record) {
            $key = $record->nokendaraan;
            
            // Calculate work duration
            $workDuration = $this->calculateWorkDuration($record->jammulai, $record->jamselesai);
            
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'nokendaraan' => $record->nokendaraan,
                    'jenis' => $record->jenis,
                    'operator_nama' => $record->operator_nama ?: 'Operator tidak ditemukan',
                    'plots' => [],
                    'jammulai' => $record->jammulai,
                    'jamselesai' => $record->jamselesai,
                    'work_duration' => $workDuration,
                    'solar' => $record->solar,
                    'hourmeterstart' => $record->hourmeterstart,
                    'hourmeterend' => $record->hourmeterend,
                    'is_completed' => !is_null($record->solar) && !is_null($record->hourmeterstart) && !is_null($record->hourmeterend),
                    'created_at' => $record->createdat,
                    'admin_updated_at' => $record->adminupdatedat
                ];
            }
            
            // Add plot to this vehicle
            $groupedData[$key]['plots'][] = $record->plot;
            
            // Use the earliest jam mulai and latest jam selesai if multiple plots
            if ($record->jammulai < $groupedData[$key]['jammulai']) {
                $groupedData[$key]['jammulai'] = $record->jammulai;
            }
            if ($record->jamselesai > $groupedData[$key]['jamselesai']) {
                $groupedData[$key]['jamselesai'] = $record->jamselesai;
                // Recalculate work duration with updated times
                $groupedData[$key]['work_duration'] = $this->calculateWorkDuration(
                    $groupedData[$key]['jammulai'], 
                    $groupedData[$key]['jamselesai']
                );
            }
        }
        
        return array_values($groupedData);
        
    } catch (\Exception $e) {
        Log::error('Error in getVehicleBBMData', [
            'lkhno' => $lkhno,
            'companyCode' => $companyCode,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return [];
    }
}

/**
 * Calculate work duration from time strings
 */
private function calculateWorkDuration($jamMulai, $jamSelesai)
{
    try {
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $jamMulai);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $jamSelesai);
        
        // Handle overnight work
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        return $start->diffInHours($end, true); // true for float result
        
    } catch (\Exception $e) {
        Log::warning('Error calculating work duration', [
            'jamMulai' => $jamMulai,
            'jamSelesai' => $jamSelesai,
            'error' => $e->getMessage()
        ]);
        
        return 8.0; // Default 8 hours
    }
}

    /**
     * Show LKH Edit Page - Updated to handle dynamic URLs and proper status validation
     */
    public function showLKHEdit($lkhno)
    {
        try {
            if (!auth()->check()) {
                return redirect()->route('login');
            }
            
            $user = auth()->user();
            
            // Check if LKH exists and is editable
            $lkhData = DB::table('lkhhdr')
                ->where('lkhno', $lkhno)
                ->where('companycode', $user->companycode)
                ->where('mandorid', $user->userid)
                ->select(['mobile_status', 'status'])
                ->first();
            
            if (!$lkhData) {
                return redirect()->route('mandor.index')->with('error', 'LKH tidak ditemukan');
            }
            
            // UPDATED: Only allow edit for DRAFT status
            if ($lkhData->mobile_status !== 'DRAFT') {
                if ($lkhData->mobile_status === 'COMPLETED') {
                    return redirect()->route('mandor.lkh.view', $lkhno)->with('error', 'LKH sudah selesai dan tidak bisa diedit');
                } else {
                    return redirect()->route('mandor.lkh.view', $lkhno)->with('error', 'LKH tidak dalam status yang bisa diedit');
                }
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
                        'jamselesai' => '15:00:00',
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
     * UPDATED: Save LKH Results with Vehicle BBM data and Per-Plot Material Support
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
                // UPDATED: Material validation for per-plot input
                'material_inputs' => 'nullable|array',
                'material_inputs.*.itemcode' => 'required_with:material_inputs|string',
                'material_inputs.*.plot' => 'required_with:material_inputs|string',
                'material_inputs.*.qtyditerima' => 'required_with:material_inputs|numeric|min:0',
                'material_inputs.*.qtysisa' => 'required_with:material_inputs|numeric|min:0',
                'material_inputs.*.qtydigunakan' => 'required_with:material_inputs|numeric|min:0',
                'vehicle_inputs' => 'nullable|array',
                'vehicle_inputs.*.nokendaraan' => 'required_with:vehicle_inputs|string',
                'vehicle_inputs.*.plot' => 'required_with:vehicle_inputs|string',
                'vehicle_inputs.*.jammulai' => 'required_with:vehicle_inputs|string',
                'vehicle_inputs.*.jamselesai' => 'required_with:vehicle_inputs|string',
                'keterangan' => 'nullable|string|max:500'
            ]);
            
            if (!auth()->check()) {
                return back()->withErrors(['message' => 'User not authenticated']);
            }
            
            $user = auth()->user();
            $workerInputs = $request->input('worker_inputs', []);
            $plotInputs = $request->input('plot_inputs', []);
            $materialInputs = $request->input('material_inputs', []);
            $vehicleInputs = $request->input('vehicle_inputs', []);
            $keterangan = $request->input('keterangan');
            
            DB::beginTransaction();
            
            try {
                // Get LKH info
                $lkhInfo = DB::table('lkhhdr')->where('lkhno', $lkhno)->first();
                
                if (!$lkhInfo) {
                    return back()->withErrors(['message' => 'LKH tidak ditemukan']);
                }
                
                // 1. Update worker details
                foreach ($workerInputs as $workerInput) {
                    $jamMasuk = $workerInput['jammasuk'] ?? '07:00:00';
                    $jamSelesai = $workerInput['jamselesai'] ?? '15:00:00';
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
                
                // 2. Update plot details
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
                
                // 3. UPDATED: Handle per-plot material input
                foreach ($materialInputs as $materialInput) {
                    if (isset($materialInput['itemcode']) && 
                        isset($materialInput['plot']) && 
                        isset($materialInput['qtysisa'])) {
                        
                        // Insert/Update lkhdetailmaterial with plot information
                        DB::table('lkhdetailmaterial')
                            ->updateOrInsert(
                                [
                                    'companycode' => $user->companycode,
                                    'lkhno' => $lkhno,
                                    'plot' => $materialInput['plot'], // NEW: Include plot for cost tracking
                                    'itemcode' => $materialInput['itemcode']
                                ],
                                [
                                    'qtyditerima' => $materialInput['qtyditerima'],
                                    'qtysisa' => $materialInput['qtysisa'],
                                    'qtydigunakan' => $materialInput['qtydigunakan'],
                                    'keterangan' => $materialInput['keterangan'] ?? null,
                                    'inputby' => $user->name,
                                    'createdat' => now(),
                                    'updatedat' => now()
                                ]
                            );
                    }
                }
                
                // 4. Insert vehicle BBM data
                foreach ($vehicleInputs as $vehicleInput) {
                    // Get operator ID from kendaraan table
                    $operatorId = DB::table('kendaraan')
                        ->where('companycode', $user->companycode)
                        ->where('nokendaraan', $vehicleInput['nokendaraan'])
                        ->where('isactive', 1)
                        ->value('idtenagakerja');
                    
                    // Clear existing vehicle data for this LKH and vehicle/plot combination
                    DB::table('kendaraanbbm')
                        ->where('companycode', $user->companycode)
                        ->where('lkhno', $lkhno)
                        ->where('nokendaraan', $vehicleInput['nokendaraan'])
                        ->where('plot', $vehicleInput['plot'])
                        ->delete();
                    
                    // Insert new vehicle BBM record
                    DB::table('kendaraanbbm')->insert([
                        'companycode' => $user->companycode,
                        'lkhno' => $lkhno,
                        'plot' => $vehicleInput['plot'],
                        'nokendaraan' => $vehicleInput['nokendaraan'],
                        'mandorid' => $user->userid,
                        'operatorid' => $operatorId,
                        'jammulai' => $vehicleInput['jammulai'],
                        'jamselesai' => $vehicleInput['jamselesai'],
                        'hourmeterstart' => null, // Will be filled by admin kendaraan
                        'hourmeterend' => null,   // Will be filled by admin kendaraan
                        'solar' => null,          // Will be filled by admin kendaraan
                        'inputby' => $user->name,
                        'createdat' => now()
                    ]);
                }
                
                // 5. Calculate totals and update header
                $totals = $this->calculateLKHTotals($lkhno, $user->companycode);
                
                // Update header with keterangan
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
                    'flash' => [
                        'success' => 'Data LKH berhasil disimpan sebagai draft!' . 
                                    (count($vehicleInputs) > 0 ? ' Data kendaraan juga tersimpan.' : '') .
                                    (count($materialInputs) > 0 ? ' Data material per plot tersimpan.' : '')
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
            
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // =============================================================================
    // MATERIAL MANAGEMENT
    // =============================================================================

   /**
     * CORRECTED: Get available materials - Fixed to show proper usage data from multiple sources
     */
    public function getAvailableMaterials(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date', now()->format('Y-m-d'));
            
            // Get LKH numbers for this mandor and date first
            $lkhNumbers = DB::table('lkhhdr')
                ->where('companycode', $user->companycode)
                ->where('mandorid', $user->userid)
                ->whereDate('lkhdate', $date)
                ->pluck('lkhno');
            
            if ($lkhNumbers->isEmpty()) {
                return response()->json([
                    'materials' => [],
                    'date' => $date,
                    'date_formatted' => Carbon::parse($date)->format('d F Y'),
                    'message' => 'No LKH found for this date'
                ]);
            }
            
            // CORRECTED: Get materials with proper usage calculation
            $materials = DB::table('usemateriallst as uml')
                ->leftJoin('herbisida as h', function($join) use ($user) {
                    $join->on('uml.companycode', '=', 'h.companycode')
                        ->on('uml.itemcode', '=', 'h.itemcode');
                })
                ->whereIn('uml.lkhno', $lkhNumbers)
                ->where('uml.companycode', $user->companycode)
                ->select([
                    'uml.itemcode', 
                    'uml.itemname', 
                    'uml.qty', 
                    'uml.qtyretur', 
                    'uml.qtydigunakan',
                    'h.measure as unit',
                    'uml.dosageperha',
                    'uml.lkhno', 
                    'uml.rkhno',
                    'uml.plot'
                ])
                ->orderBy('uml.itemcode')
                ->orderBy('uml.plot')
                ->get();
            
            if ($materials->isEmpty()) {
                return response()->json([
                    'materials' => [],
                    'date' => $date,
                    'date_formatted' => Carbon::parse($date)->format('d F Y'),
                    'message' => 'No materials found for these LKH'
                ]);
            }
            
            // Get material status from usematerialhdr
            $rkhNumbers = $materials->pluck('rkhno')->unique();
            $headerStatuses = DB::table('usematerialhdr')
                ->where('companycode', $user->companycode)
                ->whereIn('rkhno', $rkhNumbers)
                ->pluck('flagstatus', 'rkhno');
            
            // Process materials and group by itemcode with plot breakdown
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
                        'unit' => $material->unit ?: 'L',
                        'status' => $headerStatuses[$material->rkhno] ?? 'ACTIVE',
                        'dosageperha' => $material->dosageperha,
                        'rkhno' => $material->rkhno,
                        'lkh_details' => [],
                        'plot_breakdown' => [],
                        'plot_count' => 0,
                        'lkh_count' => 0
                    ];
                }
                
                // CORRECTED: Calculate usage properly
                $plannedQty = (float) $material->qty;
                
                // Check for actual input data from lkhdetailmaterial (mandor input)
                $actualInput = DB::table('lkhdetailmaterial')
                    ->where('companycode', $user->companycode)
                    ->where('lkhno', $material->lkhno)
                    ->where('plot', $material->plot)
                    ->where('itemcode', $material->itemcode)
                    ->select(['qtyditerima', 'qtysisa', 'qtydigunakan'])
                    ->first();
                
                if ($actualInput) {
                    // Use mandor input data
                    $actualUsed = (float) $actualInput->qtydigunakan;
                    $actualReturned = (float) $actualInput->qtysisa;
                } else {
                    // Use data from usemateriallst (from complete process or defaults)
                    $actualUsed = (float) ($material->qtydigunakan ?: 0);
                    $actualReturned = (float) ($material->qtyretur ?: 0);
                    
                    // If no usage recorded yet, show as unused (0 used, 0 returned)
                    if ($actualUsed == 0 && $actualReturned == 0) {
                        $actualUsed = 0; // Not yet used, show 0
                        $actualReturned = 0;
                    }
                }
                
                // Sum quantities
                $groupedMaterials[$key]['total_qty'] += $plannedQty;
                $groupedMaterials[$key]['total_qtyretur'] += $actualReturned;
                $groupedMaterials[$key]['total_qtydigunakan'] += $actualUsed;
                
                // Track unique LKH
                $lkhExists = false;
                foreach ($groupedMaterials[$key]['lkh_details'] as &$existingLkh) {
                    if ($existingLkh['lkhno'] === $material->lkhno) {
                        $existingLkh['qty'] += $plannedQty;
                        $lkhExists = true;
                        break;
                    }
                }
                
                if (!$lkhExists) {
                    $groupedMaterials[$key]['lkh_details'][] = [
                        'lkhno' => $material->lkhno,
                        'qty' => $plannedQty
                    ];
                    $groupedMaterials[$key]['lkh_count']++;
                }
                
                // Get plot area
                $plotArea = DB::table('lkhdetailplot')
                    ->where('companycode', $user->companycode)
                    ->where('lkhno', $material->lkhno)
                    ->where('plot', $material->plot)
                    ->value('luasrkh') ?: 0;
                
                // Add plot breakdown with actual usage
                $groupedMaterials[$key]['plot_breakdown'][] = [
                    'plot' => $material->plot,
                    'blok' => '',
                    'luasarea' => (float) $plotArea,
                    'usage' => $plannedQty,
                    'usage_formatted' => number_format($plannedQty, 3) . ' ' . ($material->unit ?: 'L'),
                    'planned_qty' => $plannedQty,
                    'dosage_per_ha' => (float) $material->dosageperha,
                    'unit' => $material->unit ?: 'L',
                    'actual_usage' => [
                        'qtyditerima' => $plannedQty,
                        'qtysisa' => $actualReturned,
                        'qtydigunakan' => $actualUsed,
                        'has_actual_data' => $actualInput ? true : ($actualUsed > 0 || $actualReturned > 0)
                    ]
                ];
                
                $groupedMaterials[$key]['plot_count']++;
            }
            
            // Convert to array format
            $finalMaterials = array_values($groupedMaterials);
            
            return response()->json([
                'materials' => $finalMaterials,
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y'),
                'summary' => [
                    'total_materials' => count($finalMaterials),
                    'total_plots' => array_sum(array_column($finalMaterials, 'plot_count')),
                    'total_lkh' => $lkhNumbers->count(),
                    'materials_with_returns' => count(array_filter($finalMaterials, function($m) {
                        return $m['total_qtyretur'] > 0;
                    })),
                    'materials_fully_used' => count(array_filter($finalMaterials, function($m) {
                        return $m['total_qtyretur'] == 0 && $m['total_qtydigunakan'] > 0;
                    }))
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getAvailableMaterials corrected', [
                'message' => $e->getMessage(),
                'user_id' => auth()->user()->userid ?? 'unknown',
                'date' => $request->input('date'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage(),
                'materials' => [],
                'date' => $date ?? now()->format('Y-m-d'),
                'date_formatted' => Carbon::parse($date ?? now())->format('d F Y')
            ], 500);
        }
    }






    /**
     * HELPER: Get actual material usage for specific plot from lkhdetailmaterial
     * NEW HELPER METHOD for per-plot material tracking
     */
    private function getActualMaterialUsageForPlot($companyCode, $lkhno, $plot, $itemcode)
    {
        try {
            $actualUsage = DB::table('lkhdetailmaterial')
                ->where('companycode', $companyCode)
                ->where('lkhno', $lkhno)
                ->where('plot', $plot) // NEW: Plot-specific lookup
                ->where('itemcode', $itemcode)
                ->select(['qtyditerima', 'qtysisa', 'qtydigunakan', 'createdat', 'updatedat'])
                ->first();
            
            if ($actualUsage) {
                return [
                    'qtyditerima' => (float) $actualUsage->qtyditerima,
                    'qtysisa' => (float) $actualUsage->qtysisa,
                    'qtydigunakan' => (float) $actualUsage->qtydigunakan,
                    'has_actual_data' => true,
                    'last_updated' => $actualUsage->updatedat ?: $actualUsage->createdat
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::warning('Error getting actual plot material usage', [
                'companyCode' => $companyCode,
                'lkhno' => $lkhno,
                'plot' => $plot,
                'itemcode' => $itemcode,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * HELPER: Get plot area for calculation
     * NEW HELPER METHOD
     */
    private function getPlotArea($companyCode, $lkhno, $plot)
    {
        try {
            return DB::table('lkhdetailplot')
                ->where('companycode', $companyCode)
                ->where('lkhno', $lkhno)
                ->where('plot', $plot)
                ->value('luasrkh') ?: 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * HELPER: Determine material status for specific plot
     * NEW HELPER METHOD
     */
    private function determinePlotMaterialStatus($material, $actualPlotUsage)
    {
        if (!$actualPlotUsage) {
            return 'not_used'; // No actual usage data
        }
        
        if ($actualPlotUsage['qtydigunakan'] > 0 && $actualPlotUsage['qtysisa'] == 0) {
            return 'fully_used';
        } elseif ($actualPlotUsage['qtydigunakan'] > 0 && $actualPlotUsage['qtysisa'] > 0) {
            return 'partially_used';
        } elseif ($actualPlotUsage['qtydigunakan'] == 0) {
            return 'unused';
        }
        
        return 'unknown';
    }

    /**
     * HELPER: Calculate usage efficiency for plot
     * NEW HELPER METHOD
     */
    private function calculateUsageEfficiency($material, $actualPlotUsage)
    {
        if (!$actualPlotUsage || $actualPlotUsage['qtyditerima'] == 0) {
            return 0;
        }
        
        return ($actualPlotUsage['qtydigunakan'] / $actualPlotUsage['qtyditerima']) * 100;
    }

    /**
     * FIXED: Get actual material usage from lkhdetailmaterial table
     * Returns aggregated totals per LKH per itemcode
     */
    private function getActualMaterialUsage($companyCode, $lkhno, $itemcode)
    {
        try {
            $actualUsage = DB::table('lkhdetailmaterial')
                ->where('companycode', $companyCode)
                ->where('lkhno', $lkhno)
                ->where('itemcode', $itemcode)
                ->select([
                    DB::raw('SUM(qtyditerima) as total_diterima'),
                    DB::raw('SUM(qtysisa) as total_sisa'),
                    DB::raw('SUM(qtydigunakan) as total_digunakan'),
                    DB::raw('COUNT(*) as plot_count')
                ])
                ->first();
            
            if ($actualUsage && $actualUsage->plot_count > 0) {
                return [
                    'total_diterima' => (float) $actualUsage->total_diterima,
                    'total_sisa' => (float) $actualUsage->total_sisa,
                    'total_digunakan' => (float) $actualUsage->total_digunakan,
                    'plot_count' => (int) $actualUsage->plot_count
                ];
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error getting actual material usage', [
                'companyCode' => $companyCode,
                'lkhno' => $lkhno,
                'itemcode' => $itemcode,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Get live usage data from lkhdetailmaterial
     */
    private function getLiveUsageData($companyCode, $lkhno, $itemcode)
    {
        return DB::table('lkhdetailmaterial')
            ->where('companycode', $companyCode)
            ->where('lkhno', $lkhno)
            ->where('itemcode', $itemcode)
            ->select(['qtyditerima', 'qtysisa', 'qtydigunakan'])
            ->first();
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
 * REFACTORED: Save material returns - Updated for per-plot material structure
 * (RECEIVED_BY_MANDOR -> RETURNED_BY_MANDOR)
 * Updated to work with new usemateriallst primary key: (companycode, lkhno, plot, itemcode)
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
                
                // REFACTORED: Get all plot-specific records for this itemcode and mandor
                $materialPlotRecords = DB::table('usemateriallst as uml')
                    ->join('lkhhdr as lkh', 'uml.lkhno', '=', 'lkh.lkhno')
                    ->where('uml.companycode', $user->companycode)
                    ->where('uml.itemcode', $itemcode)
                    ->where('lkh.mandorid', $user->userid)
                    ->whereDate('lkh.lkhdate', $date)
                    ->select([
                        'uml.lkhno', 
                        'uml.plot', // NEW: Include plot
                        'uml.itemcode',
                        'uml.qty as original_qty'
                    ])
                    ->get();
                
                if ($materialPlotRecords->isEmpty()) {
                    continue; // Skip if no records found
                }
                
                // Calculate proportional return per plot based on original quantity
                $totalOriginalQty = $materialPlotRecords->sum('original_qty');
                $updatedRecords = 0;
                
                foreach ($materialPlotRecords as $plotRecord) {
                    // Calculate proportional return for this plot
                    $plotProportion = $totalOriginalQty > 0 ? 
                        ($plotRecord->original_qty / $totalOriginalQty) : 
                        (1 / $materialPlotRecords->count());
                    
                    $plotReturnQty = $returnQty * $plotProportion;
                    $plotUsedQty = $plotRecord->original_qty - $plotReturnQty;
                    
                    // REFACTORED: Update usemateriallst per plot
                    $recordsUpdated = DB::table('usemateriallst')
                        ->where('companycode', $user->companycode)
                        ->where('lkhno', $plotRecord->lkhno)
                        ->where('plot', $plotRecord->plot) // NEW: Include plot in WHERE
                        ->where('itemcode', $itemcode)
                        ->update([
                            'qtyretur' => $plotReturnQty,
                            'qtydigunakan' => $plotUsedQty,
                            'noretur' => $noRetur,
                            'returby' => $user->name,
                            'tglretur' => now()
                        ]);
                    
                    $updatedRecords += $recordsUpdated;
                    
                    // REFACTORED: Update lkhdetailmaterial per plot  
                    DB::table('lkhdetailmaterial')
                        ->updateOrInsert(
                            [
                                'companycode' => $user->companycode,
                                'lkhno' => $plotRecord->lkhno,
                                'plot' => $plotRecord->plot, // NEW: Include plot
                                'itemcode' => $itemcode
                            ],
                            [
                                'qtyditerima' => $plotRecord->original_qty,
                                'qtysisa' => $plotReturnQty,
                                'qtydigunakan' => $plotUsedQty,
                                'inputby' => $user->name,
                                'createdat' => now(),
                                'updatedat' => now()
                            ]
                        );
                }
                
                if ($updatedRecords > 0) {
                    // Update header status from RECEIVED_BY_MANDOR to RETURNED_BY_MANDOR
                    $rkhNumbers = $materialPlotRecords->pluck('lkhno')
                        ->map(function($lkhno) use ($user) {
                            return DB::table('lkhhdr')
                                ->where('companycode', $user->companycode)
                                ->where('lkhno', $lkhno)
                                ->value('rkhno');
                        })
                        ->unique()
                        ->filter();
                    
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
                        'total_return_qty' => $returnQty,
                        'noretur' => $noRetur,
                        'plot_records_updated' => $updatedRecords, // NEW: Count of plot records
                        'lkh_count' => $materialPlotRecords->pluck('lkhno')->unique()->count(),
                        'plot_count' => $materialPlotRecords->count(), // NEW: Total plots affected
                        'header_updated' => $updatedHeaders > 0,
                        'plot_breakdown' => $materialPlotRecords->map(function($record) use ($returnQty, $totalOriginalQty) {
                            $plotProportion = $totalOriginalQty > 0 ? 
                                ($record->original_qty / $totalOriginalQty) : 
                                (1 / $materialPlotRecords->count());
                            return [
                                'plot' => $record->plot,
                                'lkhno' => $record->lkhno,
                                'original_qty' => $record->original_qty,
                                'return_qty' => $returnQty * $plotProportion,
                                'proportion' => $plotProportion
                            ];
                        })->toArray()
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Data retur material berhasil disimpan untuk ' . count($processedItems) . ' item dengan distribusi per plot',
                'processed_items' => $processedItems,
                'total_plot_records_updated' => array_sum(array_column($processedItems, 'plot_records_updated')),
                'processing_method' => 'per_plot_proportional' // NEW: Indicator of processing method
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        
    } catch (\Exception $e) {
        Log::error('Error in saveMaterialReturns with per-plot structure', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->user()->userid ?? 'unknown'
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

    /**
 * Get rejected attendance for mandor - NEW method
 */
public function getRejectedAttendance(Request $request)
{
    try {
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $user = auth()->user();
        $date = $request->input('date', now()->format('Y-m-d'));
        
        $rejectedRecords = AbsenLst::getRejectedAttendanceByMandor($user->companycode, $user->userid, $date);
        
        $formattedRecords = $rejectedRecords->map(function($record) {
            return [
                'absenno' => $record->absenno,
                'absen_id' => $record->absen_id,
                'tenagakerjaid' => $record->tenagakerjaid,
                'pekerja_nama' => $record->pekerja_nama,
                'pekerja_nik' => $record->pekerja_nik,
                'absenmasuk' => $record->absenmasuk,
                'absen_time' => Carbon::parse($record->absenmasuk)->format('H:i'),
                'absen_date_formatted' => Carbon::parse($record->absenmasuk)->format('d M Y'),
                'fotoabsen' => $record->fotoabsen,
                'rejection_reason' => $record->rejection_reason,
                'rejection_date' => $record->rejection_date,
                'rejection_date_formatted' => $record->rejection_date ? Carbon::parse($record->rejection_date)->format('d M Y, H:i') : null,
                'is_edited' => $record->is_edited,
                'edit_count' => $record->edit_count,
                'can_edit' => true // Always allow edit for rejected items
            ];
        });
        
        return response()->json([
            'rejected_attendance' => $formattedRecords->toArray(),
            'date' => $date,
            'date_formatted' => Carbon::parse($date)->format('d F Y'),
            'total_rejected' => $formattedRecords->count()
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in getRejectedAttendance', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
    }
}

    public function getFieldActivities()
    {  
        return response()->json(['field_activities' => []]);
    }

    // =============================================================================
    // PRIVATE HELPER METHODS
    // =============================================================================

    /**
     * Shared LKH form renderer - COMPLETE VERSION with proper component selection
     */
    private function renderLKHForm($lkhno, $mode = 'input', $vehicleBBMData = [])
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
                'lkh.jenistenagakerja', 'lkh.rkhno', 'lkh.lkhdate', 'lkh.mobile_status', 
                'lkh.keterangan', 'lkh.totalhasil', 'lkh.totalsisa', 'lkh.totalupahall',
                'u.name as mandor_nama'
            ])
            ->first();
        
        if (!$lkhData) {
            throw new \Exception('LKH tidak ditemukan');
        }
        
        // Get assigned workers - Remove totalupah to hide individual wages
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
        
        // ✅ FIXED: Pass mode parameter to getMaterialsForLKH
        $materials = $this->getMaterialsForLKH($lkhno, $user->companycode, $mode);
        
        // Get vehicle info 
        $vehicleInfo = $this->getVehicleInfoForLKH($lkhno);
        
        // Calculate total luas plan
        $totalLuasPlan = array_sum(array_column($plotData, 'luasarea'));
        
        // ✅ FIXED: Determine correct page component based on mode
        if ($mode === 'edit') {
            $pageComponent = 'lkh-edit';  // Use lkh-edit.tsx for edit mode
        } elseif ($mode === 'input') {
            $pageComponent = 'lkh-input'; // Use lkh-input.tsx for input mode  
        } else {
            $pageComponent = 'lkh-view';  // Use lkh-view.tsx for view modes
        }
        
        $isReadonly = ($mode === 'view-readonly') || ($lkhData->mobile_status === 'COMPLETED');
        $isCompleted = $lkhData->mobile_status === 'COMPLETED';
        
        $pageTitle = $isReadonly ? 'Lihat Hasil - ' . $lkhno : 
                    ($mode === 'edit' ? 'Edit Hasil - ' . $lkhno : 'Input Hasil - ' . $lkhno);
        if ($isCompleted) {
            $pageTitle = 'Hasil Selesai - ' . $lkhno;
        }
        
        // Build props array with conditional vehicleBBMData
        $props = [
            'title' => $pageTitle,
            'mode' => $mode,
            'readonly' => $isReadonly,
            'completed' => $isCompleted,
            'lkhData' => [
                'lkhno' => $lkhData->lkhno,
                'activitycode' => $lkhData->activitycode,  
                'activityname' => $lkhData->activityname,
                'blok' => $plotData[0]['blok'] ?? 'N/A',
                'plot' => array_column($plotData, 'plot'),
                'totalluasplan' => $totalLuasPlan,
                'totalhasil' => (float) ($lkhData->totalhasil ?? 0),
                'totalsisa' => (float) ($lkhData->totalsisa ?? 0),
                'jenistenagakerja' => $this->getJenisTenagaKerjaName($lkhData->jenistenagakerja),
                'rkhno' => $lkhData->rkhno,
                'lkhdate' => $lkhData->lkhdate,
                'mandor_nama' => $lkhData->mandor_nama,
                'mobile_status' => $lkhData->mobile_status,
                'keterangan' => $lkhData->keterangan,
                'is_completed' => $isCompleted,
                'needs_material' => count($materials) > 0
            ],
            'assignedWorkers' => $assignedWorkers,
            'plotData' => $plotData,
            'materials' => $materials,
            'vehicleInfo' => $vehicleInfo,
            'routes' => [
                'lkh_save_results' => route('mandor.lkh.save-results', $lkhno),
                'lkh_assign' => route('mandor.lkh.assign', $lkhno),
                'lkh_view' => route('mandor.lkh.view', $lkhno),
                'lkh_edit' => route('mandor.lkh.edit', $lkhno),
                'mandor_index' => route('mandor.index'),
            ],
            'csrf_token' => csrf_token(),
            'app' => [
                'name' => config('app.name', 'Laravel'),
                'url' => config('app.url', 'http://localhost'),
                'logo_url' => asset('img/logo-tebu.png'),
            ],
        ];
        
        // Add vehicleBBMData only for view pages
        if (in_array($mode, ['view', 'view-readonly']) && !empty($vehicleBBMData)) {
            $props['vehicleBBMData'] = $vehicleBBMData;
        }
        
        return Inertia::render($pageComponent, $props);
    }

    /**
     * CORRECTED: Get materials for LKH with per-plot breakdown - Fixed data aggregation
     */
    private function getMaterialsForLKH($lkhno, $companyCode, $mode = 'input')
    {
        // Get RKH number from LKH
        $rkhno = DB::table('lkhhdr')
            ->where('lkhno', $lkhno)
            ->where('companycode', $companyCode)
            ->value('rkhno');
        
        if (!$rkhno) {
            return [];
        }
        
        // CORRECTED: Get material data directly from usemateriallst per plot
        $materialPlotRecords = DB::table('usemateriallst as uml')
            ->leftJoin('lkhdetailplot as ldp', function($join) {
                $join->on('uml.lkhno', '=', 'ldp.lkhno')
                    ->on('uml.plot', '=', 'ldp.plot')
                    ->on('uml.companycode', '=', 'ldp.companycode');
            })
            ->where('uml.companycode', $companyCode)
            ->where('uml.lkhno', $lkhno)
            ->select([
                'uml.itemcode', 
                'uml.itemname', 
                'uml.unit', 
                'uml.dosageperha',
                'uml.plot',
                'uml.qty as planned_qty',
                'uml.qtydigunakan as current_used', // Get current used from usemateriallst
                'uml.qtyretur as current_returned',  // Get current returned from usemateriallst
                'ldp.luasrkh'
            ])
            ->orderBy('uml.itemcode')
            ->orderBy('uml.plot')
            ->get();

        if ($materialPlotRecords->isEmpty()) {
            return [];
        }

        // Group by itemcode and build plot breakdown
        $materials = [];
        
        foreach ($materialPlotRecords as $record) {
            $itemcode = $record->itemcode;
            
            // Initialize material group if not exists
            if (!isset($materials[$itemcode])) {
                $materials[$itemcode] = [
                    'itemcode' => $record->itemcode,
                    'itemname' => $record->itemname,
                    'unit' => $record->unit ?: 'L',
                    'plot_breakdown' => [],
                    'total_planned' => 0,
                    'total_sisa' => 0,
                    'total_digunakan' => 0
                ];
            }
            
            $plannedUsage = (float) $record->planned_qty;
            
            // CORRECTED: Check for actual input from lkhdetailmaterial first
            $actualUsage = DB::table('lkhdetailmaterial')
                ->where('companycode', $companyCode)
                ->where('lkhno', $lkhno)
                ->where('plot', $record->plot)
                ->where('itemcode', $record->itemcode)
                ->select(['qtyditerima', 'qtysisa', 'qtydigunakan'])
                ->first();
            
            if ($actualUsage) {
                // Use actual input data from lkhdetailmaterial (this is the mandor input)
                $qtyditerima = (float) $actualUsage->qtyditerima;
                $qtysisa = (float) $actualUsage->qtysisa;
                $qtydigunakan = (float) $actualUsage->qtydigunakan;
            } else {
                // CORRECTED: Use current data from usemateriallst if no manual input yet
                $qtyditerima = $plannedUsage;
                $qtysisa = (float) ($record->current_returned ?: 0);
                $qtydigunakan = (float) ($record->current_used ?: $plannedUsage);
                
                // If current_used is 0 but no actual input, use planned as default
                if ($qtydigunakan == 0 && $qtysisa == 0) {
                    $qtydigunakan = $plannedUsage;
                }
            }
            
            // Add plot breakdown
            $materials[$itemcode]['plot_breakdown'][] = [
                'plot' => $record->plot,
                'luasarea' => (float) ($record->luasrkh ?: 0),
                'dosage_per_ha' => (float) $record->dosageperha,
                'planned_usage' => $plannedUsage,
                'qtyditerima' => $qtyditerima,
                'qtysisa' => $qtysisa,
                'qtydigunakan' => $qtydigunakan
            ];
            
            // Update totals
            $materials[$itemcode]['total_planned'] += $plannedUsage;
            $materials[$itemcode]['total_sisa'] += $qtysisa;
            $materials[$itemcode]['total_digunakan'] += $qtydigunakan;
        }
        
        return array_values($materials);
    }

    /**
     * FIXED: Get material plot breakdown with actual usage data
     * Now uses correct join path: lkhdetailplot → lkhhdr → rkhlst → herbisidagroup → herbisidadosage
     */
    private function getMaterialPlotBreakdownWithActuals($rkhno, $itemcode, $companyCode, $dosageperha)
    {
        try {
            // FIXED: Get plot data that uses this specific material via herbisidagroup
            $plotData = DB::table('lkhdetailplot as ldp')
                ->join('lkhhdr as lkh', 'ldp.lkhno', '=', 'lkh.lkhno')
                ->join('rkhlst as rls', function($join) {
                    $join->on('lkh.rkhno', '=', 'rls.rkhno')
                        ->on('ldp.plot', '=', 'rls.plot')
                        ->on('ldp.companycode', '=', 'rls.companycode');
                })
                ->join('herbisidagroup as hg', 'rls.herbisidagroupid', '=', 'hg.herbisidagroupid')
                ->join('herbisidadosage as hd', function($join) use ($itemcode, $companyCode) {
                    $join->on('hg.herbisidagroupid', '=', 'hd.herbisidagroupid')
                        ->on('rls.companycode', '=', 'hd.companycode')
                        ->where('hd.itemcode', '=', $itemcode);
                })
                ->where('ldp.companycode', $companyCode)
                ->where('lkh.rkhno', $rkhno)
                ->where('rls.usingmaterial', 1)
                ->select([
                    'ldp.plot', 
                    'ldp.blok', 
                    'ldp.luasrkh',
                    'hd.dosageperha',
                    'ldp.lkhno' // Need this for actual usage lookup
                ])
                ->distinct()
                ->get();

            if ($plotData->isEmpty()) {
                return []; // This material is not used in any plots for this RKH
            }

            $breakdown = [];
            
            foreach ($plotData as $record) {
                // Use the dosage from herbisidadosage table (more accurate)
                $actualDosage = (float) $record->dosageperha;
                $plannedUsage = $record->luasrkh * $actualDosage;
                
                // Get actual usage for this specific plot and itemcode
                $actualPlotUsage = DB::table('lkhdetailmaterial')
                    ->where('companycode', $companyCode)
                    ->where('lkhno', $record->lkhno)
                    ->where('itemcode', $itemcode)
                    ->select(['qtyditerima', 'qtysisa', 'qtydigunakan'])
                    ->first();
                
                $breakdown[] = [
                    'plot' => $record->plot,
                    'blok' => $record->blok,
                    'luasarea' => (float) $record->luasrkh,
                    'usage' => $plannedUsage,
                    'usage_formatted' => number_format($plannedUsage, 3) . ' LTR', // Will get unit from herbisida table
                    'dosage_per_ha' => $actualDosage,
                    'actual_usage' => $actualPlotUsage ? [
                        'qtyditerima' => (float) $actualPlotUsage->qtyditerima,
                        'qtysisa' => (float) $actualPlotUsage->qtysisa,
                        'qtydigunakan' => (float) $actualPlotUsage->qtydigunakan
                    ] : null
                ];
            }
            
            return $breakdown;
            
        } catch (\Exception $e) {
            Log::error('Error in getMaterialPlotBreakdownWithActuals', [
                'rkhno' => $rkhno,
                'itemcode' => $itemcode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
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

    /** SECTION PERHITUNGAN UPAH -START-     */
    /**
     * Determine day type for wage calculation (weekday/saturday/sunday)
     */
    private function getDayType($workDate)
    {
        $dayOfWeek = \Carbon\Carbon::parse($workDate)->dayOfWeek;
        
        if ($dayOfWeek === \Carbon\Carbon::SATURDAY) {
            return 'WEEKEND_SATURDAY';
        } elseif ($dayOfWeek === \Carbon\Carbon::SUNDAY) {
            return 'WEEKEND_SUNDAY';
        }
        
        return 'DAILY';
    }

    /**
     * Get activity group from activity code
     * Helper method to extract activity group (e.g., "V" from "V.1.2.3")
     */
    private function getActivityGroupFromCode($activitycode)
    {
        // Extract the Roman numeral part from activity code
        // Examples: "V.1.2.3" -> "V", "IV.5.1" -> "IV"
        if (preg_match('/^([IVX]+)/', $activitycode, $matches)) {
            return $matches[1];
        }
        
        return 'V'; // Default fallback
    }

    /**
     * Determine borongan wage type based on activity code
     */
    private function getBoronganWageType($activitycode)
    {
        // Based on your database structure:
        // VI = Panen (PER_KG)
        // IV, V = PER_HECTARE
        
        if (strpos($activitycode, 'VI') === 0) {
            return 'PER_KG'; // Panen - per kilogram
        }
        
        return 'PER_HECTARE'; // Default for IV (Penanaman), V (Perawatan)
    }

    /**
     * UPDATED: Calculate worker wage using Upah model from database
     * Replaces hardcoded values with database-driven wage rates
     */
    private function calculateWorkerWage($lkhInfo, $totalJamKerja, $overtimeHours)
    {
        try {
            $user = auth()->user();
            $activityGroup = $this->getActivityGroupFromCode($lkhInfo->activitycode);
            $workDate = $lkhInfo->lkhdate ?? now()->format('Y-m-d');
            
            $wageData = [
                'premi' => 0,
                'upahharian' => 0,
                'upahperjam' => 0,
                'upahlembur' => 0,
                'upahborongan' => 0,
                'totalupah' => 0
            ];
            
            if ($lkhInfo->jenistenagakerja == 1) {
                // ===== HARIAN CALCULATION =====
                
                // Determine day type (weekday/saturday/sunday)
                $dayType = $this->getDayType($workDate);
                
                if ($totalJamKerja >= 8) {
                    // Full time work - use daily/weekend rate from database
                    $dailyRate = Upah::getCurrentRate(
                        $user->companycode, 
                        $activityGroup, 
                        $dayType,  // DAILY/WEEKEND_SATURDAY/WEEKEND_SUNDAY
                        $workDate
                    );
                    
                    $wageData['upahharian'] = $dailyRate ?: 115722.8; // Fallback to default if not found
                    
                } else {
                    // Part time work - use hourly rate from database
                    $hourlyRate = Upah::getCurrentRate(
                        $user->companycode, 
                        $activityGroup, 
                        'HOURLY', 
                        $workDate
                    );
                    
                    $wageData['upahperjam'] = $hourlyRate ?: 16532; // Fallback to default
                    $wageData['upahharian'] = $totalJamKerja * $wageData['upahperjam'];
                }
                
                // Overtime calculation
                if ($overtimeHours > 0) {
                    $overtimeRate = Upah::getCurrentRate(
                        $user->companycode, 
                        $activityGroup, 
                        'OVERTIME', 
                        $workDate
                    );
                    
                    $overtimeRateAmount = $overtimeRate ?: 12542; // Fallback to default
                    $wageData['upahlembur'] = $overtimeHours * $overtimeRateAmount;
                }
                
                // Calculate total for harian
                $wageData['totalupah'] = $wageData['upahharian'] + $wageData['upahlembur'] + $wageData['premi'];
                
            } else {
                // ===== BORONGAN CALCULATION =====
                
                $wageType = $this->getBoronganWageType($lkhInfo->activitycode);
                
                $boronganRate = Upah::getCurrentRate(
                    $user->companycode, 
                    $activityGroup, 
                    $wageType,  // PER_HECTARE or PER_KG
                    $workDate
                );
                
                // For borongan, the rate calculation depends on work results
                // This is base rate per unit, actual calculation done later with work results
                $wageData['upahborongan'] = $boronganRate ?: 140000; // Fallback default
                $wageData['totalupah'] = $wageData['upahborongan']; // Will be multiplied by area/quantity later
            }
            
            return $wageData;
            
        } catch (\Exception $e) {
            \Log::error('Error in calculateWorkerWage with database integration', [
                'message' => $e->getMessage(),
                'activitycode' => $lkhInfo->activitycode ?? 'unknown',
                'jenistenagakerja' => $lkhInfo->jenistenagakerja ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to hardcoded values if database fails
            return [
                'premi' => 0,
                'upahharian' => $lkhInfo->jenistenagakerja == 1 ? 115722.8 : 0,
                'upahperjam' => 0,
                'upahlembur' => 0,
                'upahborongan' => $lkhInfo->jenistenagakerja == 2 ? 140000 : 0,
                'totalupah' => $lkhInfo->jenistenagakerja == 1 ? 115722.8 : 140000
            ];
        }
    }
    /** SECTION PERHITUNGAN UPAH -END0     */






    /**
     * Get vehicle info for specific LKH - UPDATED: Include Helper Information
     */
    private function getVehicleInfoForLKH($lkhno)
    {
        try {
            // Get all vehicles associated with this LKH through RKHLST with helper info
            $vehicleInfo = DB::table('lkhhdr as lkh')
                ->join('rkhlst as rls', function($join) {
                    $join->on('lkh.rkhno', '=', 'rls.rkhno')
                        ->on('lkh.activitycode', '=', 'rls.activitycode');
                })
                ->leftJoin('kendaraan as k', function($join) {
                    $join->on('rls.operatorid', '=', 'k.idtenagakerja')
                        ->where('k.isactive', '=', 1);
                })
                ->leftJoin('tenagakerja as tk_operator', function($join) {
                    $join->on('k.idtenagakerja', '=', 'tk_operator.tenagakerjaid')
                        ->where('tk_operator.isactive', '=', 1);
                })
                // NEW: Join with helper information
                ->leftJoin('tenagakerja as tk_helper', function($join) {
                    $join->on('rls.helperid', '=', 'tk_helper.tenagakerjaid')
                        ->where('tk_helper.isactive', '=', 1);
                })
                ->where('lkh.lkhno', $lkhno)
                ->where('rls.usingvehicle', 1)
                ->whereNotNull('k.nokendaraan') // Ensure vehicle exists
                ->select([
                    'k.nokendaraan', 'k.jenis', 'k.hourmeter',
                    'tk_operator.nama as operator_nama', 
                    'tk_operator.nik as operator_nik',
                    // NEW: Helper information
                    'rls.usinghelper',
                    'rls.helperid',
                    'tk_helper.nama as helper_nama',
                    'tk_helper.nik as helper_nik',
                    'rls.plot', 'rls.luasarea' // Include plot info for context
                ])
                ->orderBy('k.nokendaraan')
                ->get();

            if ($vehicleInfo->isEmpty()) {
                return null;
            }

            // If only one vehicle, return single object for backward compatibility
            if ($vehicleInfo->count() === 1) {
                $vehicle = $vehicleInfo->first();
                return [
                    'nokendaraan' => $vehicle->nokendaraan,
                    'jenis' => $vehicle->jenis,
                    'hourmeter' => (float) $vehicle->hourmeter,
                    'operator_nama' => $vehicle->operator_nama,
                    'operator_nik' => $vehicle->operator_nik,
                    // NEW: Helper information for single vehicle
                    'helper_nama' => $vehicle->usinghelper ? $vehicle->helper_nama : null,
                    'helper_id' => $vehicle->usinghelper ? $vehicle->helperid : null,
                    'helper_nik' => $vehicle->usinghelper ? $vehicle->helper_nik : null,
                    'has_helper' => (bool) $vehicle->usinghelper,
                    'is_multiple' => false,
                    'plots' => [$vehicle->plot]
                ];
            }

            // Multiple vehicles - group by vehicle and include helper details
            $groupedVehicles = [];
            foreach ($vehicleInfo as $vehicle) {
                $key = $vehicle->nokendaraan;
                
                if (!isset($groupedVehicles[$key])) {
                    $groupedVehicles[$key] = [
                        'nokendaraan' => $vehicle->nokendaraan,
                        'jenis' => $vehicle->jenis,
                        'hourmeter' => (float) $vehicle->hourmeter,
                        'operator_nama' => $vehicle->operator_nama,
                        'operator_nik' => $vehicle->operator_nik,
                        // NEW: Helper information for multiple vehicles
                        'helper_nama' => $vehicle->usinghelper ? $vehicle->helper_nama : null,
                        'helper_id' => $vehicle->usinghelper ? $vehicle->helperid : null,
                        'helper_nik' => $vehicle->usinghelper ? $vehicle->helper_nik : null,
                        'has_helper' => (bool) $vehicle->usinghelper,
                        'plots' => [],
                        'total_luasarea' => 0
                    ];
                }
                
                $groupedVehicles[$key]['plots'][] = $vehicle->plot;
                $groupedVehicles[$key]['total_luasarea'] += (float) $vehicle->luasarea;
            }

            return [
                'is_multiple' => true,
                'vehicle_count' => count($groupedVehicles),
                'vehicles' => array_values($groupedVehicles)
            ];

        } catch (\Exception $e) {
            Log::error('Error in getVehicleInfoForLKH with helper info', [
                'lkhno' => $lkhno,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    /**
     * Get available workers for assignment - UPDATED to only include APPROVED attendance
     */
    private function getAvailableWorkersForAssignment($companyCode, $mandorUserId, $date)
    {
        // Only get workers with APPROVED attendance
        $approvedAttendance = AbsenLst::getApprovedAttendanceByMandorAndDate($companyCode, $mandorUserId, $date);
        
        return $approvedAttendance->map(function($record) {
            return [
                'tenagakerjaid' => $record->tenagakerjaid,
                'nama' => $record->nama,
                'nik' => $record->nik,
                'gender' => $record->gender,
                'jenistenagakerja' => $record->jenistenagakerja,
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



    // =============================================================================
    // PRIVATE HELPER METHODS - PHOTO STORAGE
    // =============================================================================

    /**
     * Save photo to file storage
     * Format: YYYYMMDD_absen_TYPE_TENAGAKERJAID_TIMESTAMP.jpg
     * 
     * @param string $photoBase64 Base64 encoded photo
     * @param string $tenagakerjaId Worker ID
     * @param string $type HADIR or LOKASI
     * @return string Photo URL
     */
    private function savePhotoToStorage($photoBase64, $tenagakerjaId, $type)
    {
        try {
            // Decode base64
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoBase64));
            
            // Generate filename with new format
            $date = now()->format('Ymd'); // YYYYMMDD
            $timestamp = now()->timestamp;
            $filename = "{$date}_absen_{$type}_{$tenagakerjaId}_{$timestamp}.jpg";
            
            // Path: attendance/YYYY-MM-DD/filename.jpg
            $datePath = now()->format('Y-m-d');
            $path = "attendance/{$datePath}/{$filename}";
            
            // Save to storage/app/public/attendance/
            Storage::disk('public')->put($path, $imageData);
            
            // Generate URL
            $photoUrl = Storage::disk('public')->url($path);
            
            Log::info('Photo saved to storage', [
                'path' => $path,
                'url' => $photoUrl,
                'type' => $type,
                'worker' => $tenagakerjaId
            ]);
            
            return $photoUrl;
            
        } catch (\Exception $e) {
            Log::error('Error saving photo to storage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Gagal menyimpan foto: ' . $e->getMessage());
        }
    }

    /**
     * Delete photo from storage
     * 
     * @param string $photoUrl Photo URL
     * @return bool
     */
    private function deletePhotoFromStorage($photoUrl)
    {
        try {
            // Extract path from URL
            // URL format: http://server/storage/attendance/2025-01-17/file.jpg
            // Need to get: attendance/2025-01-17/file.jpg
            
            $urlPath = parse_url($photoUrl, PHP_URL_PATH);
            $storagePath = str_replace('/storage/', '', $urlPath);
            
            if (Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
                
                Log::info('Photo deleted from storage', [
                    'url' => $photoUrl,
                    'path' => $storagePath
                ]);
                
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::warning('Error deleting photo from storage', [
                'url' => $photoUrl,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get attendance statistics by type
     * Helper untuk dashboard/reporting
     * 
     * @param string $companyCode
     * @param string $mandorId
     * @param string $date
     * @return array
     */
    private function getAttendanceStatsByType($companyCode, $mandorId, $date)
    {
        try {
            $stats = DB::table('absenlst as al')
                ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                ->where('ah.companycode', $companyCode)
                ->where('ah.mandorid', $mandorId)
                ->whereDate('al.absenmasuk', $date)
                ->select([
                    'al.absentype',
                    DB::raw('COUNT(*) as total'),
                    DB::raw("COUNT(CASE WHEN al.approval_status = 'APPROVED' THEN 1 END) as approved"),
                    DB::raw("COUNT(CASE WHEN al.approval_status = 'PENDING' THEN 1 END) as pending"),
                    DB::raw("COUNT(CASE WHEN al.approval_status = 'REJECTED' THEN 1 END) as rejected")
                ])
                ->groupBy('al.absentype')
                ->get()
                ->keyBy('absentype');
            
            return [
                'hadir' => [
                    'total' => $stats->get('HADIR')->total ?? 0,
                    'approved' => $stats->get('HADIR')->approved ?? 0,
                    'pending' => $stats->get('HADIR')->pending ?? 0,
                    'rejected' => $stats->get('HADIR')->rejected ?? 0,
                ],
                'lokasi' => [
                    'total' => $stats->get('LOKASI')->total ?? 0,
                    'approved' => $stats->get('LOKASI')->approved ?? 0,
                    'pending' => $stats->get('LOKASI')->pending ?? 0,
                    'rejected' => $stats->get('LOKASI')->rejected ?? 0,
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting attendance stats by type', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'hadir' => ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0],
                'lokasi' => ['total' => 0, 'approved' => 0, 'pending' => 0, 'rejected' => 0]
            ];
        }
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