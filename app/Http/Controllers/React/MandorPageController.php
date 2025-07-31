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
                'materials_save_returns' => route('mandor.materials.save-returns'),
                'sync_offline_data' => route('mandor.sync-offline-data'),
                'lkh_assign' => route('mandor.lkh.assign', ['lkhno' => '__LKHNO__']),
            ],
            'initialData' => [
                'stats' => [
                    'total_workers' => 156,
                    'productivity' => '94%',
                    'active_areas' => 12,
                    'monitoring' => '24/7'
                ],
                'attendance_summary' => [
                    [
                        'name' => 'Ahmad Rizki',
                        'time' => '07:30',
                        'status' => 'Tepat Waktu',
                        'status_color' => 'text-green-600',
                        'id' => 1001,
                        'initials' => 'AR'
                    ]
                ],
                'attendance_stats' => [
                    'today_total' => 45,
                    'present' => 42,
                    'late' => 2,
                    'absent' => 1,
                    'percentage_present' => 93.3
                ],
                'field_activities' => [
                    [
                        'type' => 'Foto',
                        'location' => 'Blok A-12',
                        'time' => '2 jam lalu',
                        'status' => 'Selesai',
                        'icon' => 'camera'
                    ]
                ],
                'collection_stats' => [
                    [
                        'title' => 'Dokumentasi Foto',
                        'desc' => 'Pelacakan progres visual',
                        'stats' => '127 foto hari ini',
                        'icon' => 'camera',
                        'gradient' => 'from-neutral-700 to-neutral-900'
                    ]
                ]
            ]
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
     * Get ready LKH list for current mandor
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
            
            $readyLKH = DB::table('lkhhdr as lkh')
                ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->leftJoin('usematerialhdr as umh', 'lkh.rkhno', '=', 'umh.rkhno')
                ->join('rkhlst as rls', function($join) {
                    $join->on('lkh.rkhno', '=', 'rls.rkhno')
                         ->on('lkh.activitycode', '=', 'rls.activitycode');
                })
                ->where('lkh.companycode', $companyCode)
                ->where('lkh.mandorid', $mandorUserId)
                ->whereDate('lkh.lkhdate', $date)
                ->where('lkh.status', '!=', 'COMPLETED')
                ->select([
                    'lkh.lkhno',
                    'lkh.activitycode',
                    'act.description as activityname',
                    'lkh.blok',
                    'lkh.totalluasactual',
                    'lkh.jenistenagakerja',
                    'lkh.status as lkh_status',
                    'lkh.totalworkers as estimated_workers',
                    'rls.blok as rls_blok',
                    'rls.plot',
                    'rls.luasarea',
                    'rls.usingmaterial',
                    'umh.flagstatus as material_status'
                ])
                ->get();
                
            Log::info('Ready LKH raw result', [
                'count' => $readyLKH->count(),
                'data' => $readyLKH->toArray()
            ]);
            
            // Group by LKH and aggregate plot data
            $groupedLKH = $readyLKH->groupBy('lkhno')->map(function($lkhGroup, $lkhno) {
                $firstRecord = $lkhGroup->first();
                
                $needsMaterial = (bool) $firstRecord->usingmaterial;
                $hasMaterialRecord = !is_null($firstRecord->material_status);
                $materialsReady = true;
                
                if ($needsMaterial) {
                    if ($hasMaterialRecord) {
                        $materialsReady = ($firstRecord->material_status === 'SUBMITTED');
                    } else {
                        $materialsReady = false;
                    }
                }
                
                return [
                    'lkhno' => $lkhno,
                    'activitycode' => $firstRecord->activitycode,
                    'activityname' => $firstRecord->activityname,
                    'blok' => $firstRecord->blok,
                    'plot' => $lkhGroup->pluck('plot')->unique()->values()->toArray(),
                    'totalluasplan' => (float) $lkhGroup->sum('luasarea'),
                    'jenistenagakerja' => $this->getJenisTenagaKerjaName($firstRecord->jenistenagakerja),
                    'status' => $this->determineLKHStatus($firstRecord, $needsMaterial, $materialsReady),
                    'estimated_workers' => (int) $firstRecord->estimated_workers,
                    'materials_ready' => $materialsReady,
                    'needs_material' => $needsMaterial,
                ];
            });
            
            Log::info('Processed LKH result', [
                'count' => $groupedLKH->count(),
                'data' => $groupedLKH->values()->toArray()
            ]);
            
            return response()->json([
                'lkh_list' => $groupedLKH->values()->toArray(),
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
     * Show LKH Assignment Page
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
            
            // Get LKH data
            $lkhData = DB::table('lkhhdr as lkh')
                ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->leftJoin('rkhlst as rls', function($join) {
                    $join->on('lkh.rkhno', '=', 'rls.rkhno')
                         ->on('lkh.activitycode', '=', 'rls.activitycode');
                })
                ->where('lkh.companycode', $companyCode)
                ->where('lkh.mandorid', $mandorUserId)
                ->where('lkh.lkhno', $lkhno)
                ->select([
                    'lkh.lkhno',
                    'lkh.activitycode',
                    'act.description as activityname',
                    'lkh.blok',
                    'lkh.totalluasactual',
                    'lkh.jenistenagakerja',
                    'lkh.totalworkers as estimated_workers',
                    'lkh.rkhno',
                    'lkh.lkhdate',
                    'u.name as mandor_nama',
                    DB::raw('GROUP_CONCAT(DISTINCT rls.plot) as plots'),
                    DB::raw('SUM(rls.luasarea) as totalluasplan')
                ])
                ->groupBy([
                    'lkh.lkhno', 'lkh.activitycode', 'act.description', 'lkh.blok',
                    'lkh.totalluasactual', 'lkh.jenistenagakerja', 'lkh.totalworkers',
                    'lkh.rkhno', 'lkh.lkhdate', 'u.name'
                ])
                ->first();
            
            if (!$lkhData) {
                return redirect()->route('mandor.index')
                    ->with('error', 'LKH tidak ditemukan');
            }
            
            // Get vehicle info if applicable
            $vehicleInfo = $this->getVehicleInfoForLKH($lkhno);
            
            // Get available workers (from today's attendance)
            $availableWorkers = $this->getAvailableWorkersForAssignment($companyCode, $mandorUserId, $lkhData->lkhdate);
            
            return Inertia::render('lkh-assignment', [
                'title' => 'Assignment Pekerja - ' . $lkhno,
                'lkhData' => [
                    'lkhno' => $lkhData->lkhno,
                    'activitycode' => $lkhData->activitycode,
                    'activityname' => $lkhData->activityname,
                    'blok' => $lkhData->blok,
                    'plot' => $lkhData->plots ? explode(',', $lkhData->plots) : [],
                    'totalluasplan' => (float) ($lkhData->totalluasplan ?? 0),
                    'jenistenagakerja' => $this->getJenisTenagaKerjaName($lkhData->jenistenagakerja),
                    'estimated_workers' => (int) $lkhData->estimated_workers,
                    'rkhno' => $lkhData->rkhno,
                    'lkhdate' => $lkhData->lkhdate,
                    'mandor_nama' => $lkhData->mandor_nama
                ],
                'vehicleInfo' => $vehicleInfo,
                'availableWorkers' => $availableWorkers,
                'routes' => [
                    'lkh_save_assignment' => route('mandor.lkh.save-assignment', $lkhno),
                ],
                'csrf_token' => csrf_token(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in showLKHAssign', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno
            ]);
            
            return redirect()->route('mandor.index')
                ->with('error', 'Terjadi kesalahan saat membuka halaman assignment');
        }
    }

    /**
     * Save LKH Worker Assignment
     */
    public function saveLKHAssign(Request $request, $lkhno = null)
    {
        try {
            // Get lkhno from route parameter or request
            $lkhno = $lkhno ?? $request->input('lkhno');
            
            $request->validate([
                'assigned_workers' => 'required|array|min:1',
                'assigned_workers.*.tenagakerjaid' => 'required|string',
                'assigned_workers.*.nama' => 'required|string',
                'assigned_workers.*.nik' => 'required|string',
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $assignedWorkers = $request->input('assigned_workers');
            
            Log::info('Saving LKH worker assignment', [
                'lkhno' => $lkhno,
                'workers_count' => count($assignedWorkers)
            ]);
            
            DB::beginTransaction();
            
            try {
                // Store assignment in lkhhdr
                DB::table('lkhhdr')
                    ->where('lkhno', $lkhno)
                    ->update([
                        'assigned_workers' => json_encode($assignedWorkers),
                        'totalworkers' => count($assignedWorkers),
                        'updateby' => $user->name,
                        'updatedat' => now()
                    ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Assignment berhasil disimpan',
                    'data' => [
                        'lkhno' => $lkhno,
                        'total_assigned' => count($assignedWorkers)
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in saveLKHAssign', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show LKH Input Results Page
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
            
            // Get LKH data
            $lkhData = DB::table('lkhhdr as lkh')
                ->join('activity as act', 'lkh.activitycode', '=', 'act.activitycode')
                ->leftJoin('user as u', 'lkh.mandorid', '=', 'u.userid')
                ->leftJoin('rkhlst as rls', function($join) {
                    $join->on('lkh.rkhno', '=', 'rls.rkhno')
                         ->on('lkh.activitycode', '=', 'rls.activitycode');
                })
                ->where('lkh.companycode', $companyCode)
                ->where('lkh.mandorid', $mandorUserId)
                ->where('lkh.lkhno', $lkhno)
                ->select([
                    'lkh.lkhno',
                    'lkh.activitycode',
                    'act.description as activityname',
                    'lkh.blok',
                    'lkh.jenistenagakerja',
                    'lkh.rkhno',
                    'lkh.lkhdate',
                    'lkh.assigned_workers',
                    'u.name as mandor_nama',
                    DB::raw('GROUP_CONCAT(DISTINCT rls.plot) as plots'),
                    DB::raw('SUM(rls.luasarea) as totalluasplan')
                ])
                ->groupBy([
                    'lkh.lkhno', 'lkh.activitycode', 'act.description', 'lkh.blok',
                    'lkh.jenistenagakerja', 'lkh.rkhno', 'lkh.lkhdate',
                    'lkh.assigned_workers', 'u.name'
                ])
                ->first();
            
            if (!$lkhData) {
                return redirect()->route('mandor.index')
                    ->with('error', 'LKH tidak ditemukan');
            }
            
            // Check if workers are assigned
            if (!$lkhData->assigned_workers) {
                return redirect()->route('mandor.lkh.assign', $lkhno)
                    ->with('error', 'Silakan assign pekerja terlebih dahulu');
            }
            
            $assignedWorkers = json_decode($lkhData->assigned_workers, true);
            
            return Inertia::render('lkh-input', [
                'title' => 'Input Hasil - ' . $lkhno,
                'lkhData' => [
                    'lkhno' => $lkhData->lkhno,
                    'activitycode' => $lkhData->activitycode,
                    'activityname' => $lkhData->activityname,
                    'blok' => $lkhData->blok,
                    'plot' => $lkhData->plots ? explode(',', $lkhData->plots) : [],
                    'totalluasplan' => (float) ($lkhData->totalluasplan ?? 0),
                    'jenistenagakerja' => $this->getJenisTenagaKerjaName($lkhData->jenistenagakerja),
                    'rkhno' => $lkhData->rkhno,
                    'lkhdate' => $lkhData->lkhdate,
                    'mandor_nama' => $lkhData->mandor_nama
                ],
                'assignedWorkers' => $assignedWorkers,
                'routes' => [
                    'lkh_save_results' => route('mandor.lkh.save-results', $lkhno),
                ],
                'csrf_token' => csrf_token(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in showLKHInput', [
                'message' => $e->getMessage(),
                'lkhno' => $lkhno
            ]);
            
            return redirect()->route('mandor.index')
                ->with('error', 'Terjadi kesalahan saat membuka halaman input');
        }
    }

    /**
     * Save LKH Results (Team-based input distributed to workers)
     */
    public function saveLKHResults(Request $request, $lkhno = null)
    {
        try {
            // Get lkhno from route parameter or request
            $lkhno = $lkhno ?? $request->input('lkhno');
            
            $request->validate([
                'assigned_workers' => 'required|array|min:1',
                'plot_inputs' => 'required|array|min:1',
                'plot_inputs.*.plot' => 'required|string',
                'plot_inputs.*.hasil' => 'required|numeric|min:0',
                'plot_inputs.*.luasplot' => 'required|numeric|min:0',
                'plot_inputs.*.sisa' => 'nullable|numeric|min:0',
                'plot_inputs.*.materialused' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:500'
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $assignedWorkers = $request->input('assigned_workers');
            $plotInputs = $request->input('plot_inputs');
            $keterangan = $request->input('keterangan');
            
            Log::info('Saving LKH results', [
                'lkhno' => $lkhno,
                'workers_count' => count($assignedWorkers),
                'plots_count' => count($plotInputs)
            ]);
            
            DB::beginTransaction();
            
            try {
                // Clear existing records
                DB::table('lkhlst')->where('lkhno', $lkhno)->delete();
                
                $sequence = 1;
                $totalLuasActual = 0;
                $totalHasil = 0;
                $totalSisa = 0;
                $totalUpahAll = 0;
                
                // Get LKH info for calculations
                $lkhInfo = DB::table('lkhhdr')->where('lkhno', $lkhno)->first();
                
                // TEAM-BASED DISTRIBUTION: Create records for each worker-plot combination
                foreach ($assignedWorkers as $worker) {
                    foreach ($plotInputs as $plotInput) {
                        // Skip if no work done on this plot
                        if (($plotInput['hasil'] ?? 0) == 0 && ($plotInput['luasplot'] ?? 0) == 0) {
                            continue;
                        }
                        
                        // Distribute plot data among workers (equal distribution)
                        $workerCount = count($assignedWorkers);
                        $workerLuasplot = ($plotInput['luasplot'] ?? 0) / $workerCount;
                        $workerHasil = ($plotInput['hasil'] ?? 0) / $workerCount;
                        $workerSisa = ($plotInput['sisa'] ?? 0) / $workerCount;
                        $workerMaterial = ($plotInput['materialused'] ?? 0) / $workerCount;
                        
                        // Calculate upah based on jenis tenaga kerja
                        $upahharian = 0;
                        $totalupahharian = 0;
                        $costperha = 0;
                        $totalbiayaborongan = 0;
                        
                        if ($lkhInfo->jenistenagakerja == 1) {
                            // Harian
                            $upahharian = $this->calculateDailyWage($worker['tenagakerjaid'], $workerLuasplot);
                            $totalupahharian = $upahharian;
                            $totalUpahAll += $totalupahharian;
                        } else {
                            // Borongan
                            $costperha = $this->calculateCostPerHa($lkhInfo->activitycode);
                            $totalbiayaborongan = $workerHasil * $costperha;
                            $totalUpahAll += $totalbiayaborongan;
                        }
                        
                        DB::table('lkhlst')->insert([
                            'lkhno' => $lkhno,
                            'workersequence' => $sequence++,
                            'workername' => $worker['nama'],
                            'noktp' => $worker['nik'],
                            'blok' => $lkhInfo->blok,
                            'plot' => $plotInput['plot'],
                            'luasplot' => $workerLuasplot,
                            'hasil' => $workerHasil,
                            'sisa' => $workerSisa,
                            'materialused' => $workerMaterial,
                            'jammasuk' => '07:00:00',
                            'jamselesai' => '16:00:00',
                            'overtimehours' => 0,
                            'premi' => 0,
                            'upahharian' => $upahharian,
                            'totalupahharian' => $totalupahharian,
                            'costperha' => $costperha,
                            'totalbiayaborongan' => $totalbiayaborongan,
                            'createdat' => now(),
                            'updatedat' => now()
                        ]);
                        
                        // Accumulate totals (only once per plot, not per worker)
                        if ($worker === $assignedWorkers[0]) { // Only count once
                            $totalLuasActual += $plotInput['luasplot'] ?? 0;
                            $totalHasil += $plotInput['hasil'] ?? 0;
                            $totalSisa += $plotInput['sisa'] ?? 0;
                        }
                    }
                }
                
                // Update LKH header
                DB::table('lkhhdr')
                    ->where('lkhno', $lkhno)
                    ->update([
                        'totalworkers' => count($assignedWorkers),
                        'totalluasactual' => $totalLuasActual,
                        'totalhasil' => $totalHasil,
                        'totalsisa' => $totalSisa,
                        'totalupahall' => $totalUpahAll,
                        'status' => 'DRAFT',
                        'keterangan' => $keterangan,
                        'updateby' => $user->name,
                        'updatedat' => now(),
                        'mobileupdatedat' => now(),
                        'webreceivedat' => now()
                    ]);
                
                DB::commit();
                
                Log::info('LKH results saved successfully', [
                    'lkhno' => $lkhno,
                    'total_records' => $sequence - 1,
                    'total_luas_actual' => $totalLuasActual,
                    'total_workers' => count($assignedWorkers)
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data hasil pekerjaan berhasil disimpan! LKH sudah masuk ke sistem untuk review admin.',
                    'data' => [
                        'lkhno' => $lkhno,
                        'total_records_created' => $sequence - 1,
                        'total_luas_actual' => $totalLuasActual,
                        'total_workers' => count($assignedWorkers)
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in saveLKHResults', [
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
    | MATERIAL MANAGEMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Get available materials for current mandor
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
            
            $materials = DB::table('usemateriallst as uml')
                ->join('usematerialhdr as umh', function($join) {
                    $join->on('uml.companycode', '=', 'umh.companycode')
                         ->on('uml.rkhno', '=', 'umh.rkhno');
                })
                ->join('lkhhdr as lkh', 'umh.rkhno', '=', 'lkh.rkhno')
                ->where('uml.companycode', $companyCode)
                ->where('lkh.mandorid', $mandorUserId)
                ->whereDate('lkh.lkhdate', $date)
                ->select([
                    'uml.itemcode',
                    'uml.itemname',
                    'uml.qty',
                    'uml.qtyretur',
                    'uml.unit',
                    'uml.nouse',
                    'uml.noretur',
                    'umh.flagstatus as status'
                ])
                ->distinct()
                ->get()
                ->map(function($material) {
                    return [
                        'itemcode' => $material->itemcode,
                        'itemname' => $material->itemname,
                        'qty' => (float) $material->qty,
                        'qtyretur' => (float) $material->qtyretur,
                        'unit' => $material->unit,
                        'nouse' => $material->nouse,
                        'noretur' => $material->noretur,
                        'status' => $material->status
                    ];
                });
            
            Log::info('Found materials', [
                'count' => $materials->count(),
                'data' => $materials->toArray()
            ]);
            
            return response()->json([
                'materials' => $materials,
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
     * Save material returns
     */  
    public function saveMaterialReturns(Request $request)
    {
        try {
            $request->validate([
                'material_returns' => 'required|array',
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $materialReturns = $request->input('material_returns');
            
            Log::info('Saving material returns', [
                'returns_count' => count($materialReturns),
                'returns_data' => $materialReturns
            ]);
            
            DB::beginTransaction();
            
            try {
                foreach ($materialReturns as $itemcode => $returnQty) {
                    if ($returnQty > 0) {
                        $noRetur = $this->generateReturnNo();
                        
                        DB::table('usemateriallst')
                            ->where('itemcode', $itemcode)
                            ->update([
                                'qtyretur' => $returnQty,
                                'noretur' => $noRetur,
                                'returby' => $user->name,
                                'tglretur' => now()
                            ]);
                        
                        DB::table('usematerialhdr')
                            ->where('rkhno', function($query) use ($itemcode) {
                                $query->select('rkhno')
                                      ->from('usemateriallst')
                                      ->where('itemcode', $itemcode)
                                      ->limit(1);
                            })
                            ->update(['flagstatus' => 'RECEIVED']);
                        
                        Log::info('Material return updated', [
                            'itemcode' => $itemcode,
                            'return_qty' => $returnQty,
                            'noretur' => $noRetur
                        ]);
                    }
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Data retur material berhasil disimpan',
                    'data' => [
                        'returns_processed' => count(array_filter($materialReturns, function($qty) {
                            return $qty > 0;
                        }))
                    ]
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
                'error' => 'Internal server error: ' . $e->getMessage()
            ], 500);
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
     * Determine LKH status based on material requirements
     */
    private function determineLKHStatus($lkh, $needsMaterial, $materialsReady)
    {
        if (!$needsMaterial) {
            return 'READY';
        }
        
        if ($needsMaterial && !$materialsReady) {
            return 'WAITING_MATERIAL';
        }
        
        return 'READY';
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
     * Calculate cost per hectare for borongan
     */
    private function calculateCostPerHa($activitycode)
    {
        $cost = DB::table('upah')
            ->where('upahid', $activitycode)
            ->value('harga');
            
        return $cost ?? 100000; // Default cost per ha
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
}