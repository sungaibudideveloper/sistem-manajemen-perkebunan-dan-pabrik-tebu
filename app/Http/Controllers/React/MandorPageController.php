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

class MandorPageController extends Controller
{
    /**
     * Main SPA entry point
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
                'workers' => route('mandor.workers'),
                'attendance_today' => route('mandor.attendance.today'),  
                'process_checkin' => route('mandor.attendance.process-checkin'),
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

    /**
     * Get workers list for current mandor - USING ELOQUENT
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
            
            // ELOQUENT QUERY
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

    /**
     * Get attendance for specific date - USING ELOQUENT
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
            
            // ELOQUENT QUERY with RELATIONSHIPS
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
     * Process check-in with photo - FIXED LOGIC
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
                // FIXED LOGIC: Cari atau buat 1 AbsenHdr per mandor per hari
                $absenHdr = AbsenHdr::where('companycode', $companyCode)
                    ->where('mandorid', $mandorUserId)
                    ->whereDate('uploaddate', $today)
                    ->first();
                
                if (!$absenHdr) {
                    // Buat AbsenHdr PERTAMA untuk mandor ini hari ini
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
                    // UPDATE AbsenHdr yang sudah ada - USE DB UPDATE to avoid primary key issues
                    $nextId = $absenHdr->totalpekerja + 1; // Get next ID BEFORE increment
                    
                    // Use DB facade instead of Eloquent methods
                    DB::table('absenhdr')
                        ->where('absenno', $absenHdr->absenno)
                        ->where('companycode', $companyCode)
                        ->increment('totalpekerja');
                    
                    DB::table('absenhdr')
                        ->where('absenno', $absenHdr->absenno)
                        ->where('companycode', $companyCode)
                        ->update(['updateBy' => $user->name]);
                    
                    // Refresh model to get updated totalpekerja
                    $absenHdr = AbsenHdr::where('absenno', $absenHdr->absenno)
                        ->where('companycode', $companyCode)
                        ->first();
                    
                    Log::info('Updated existing AbsenHdr', [
                        'absenno' => $absenHdr->absenno, 
                        'next_id' => $nextId,
                        'new_total' => $absenHdr->totalpekerja
                    ]);
                }
                
                // Create AbsenLst record - USE DB INSERT to avoid Eloquent primary key issues
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

    // API endpoints lainnya
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

    /**
     * Generate absenno format: ABS{YYYYMMDD}{sequence} - USING ELOQUENT
     */
    private function generateAbsenNo($mandorUserId, $date)
    {
        $dateStr = str_replace('-', '', $date);
        $prefix = "ABS{$dateStr}";
        $companyCode = auth()->user()->companycode;
        
        return DB::transaction(function () use ($prefix, $companyCode) {
            // ELOQUENT QUERY with LOCK
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
}