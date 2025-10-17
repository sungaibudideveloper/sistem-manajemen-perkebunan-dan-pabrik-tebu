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

class ApproverPageController extends Controller
{
    // =============================================================================
    // MAIN DASHBOARD & ENTRY POINTS
    // =============================================================================

    /**
     * Main SPA Dashboard entry point for Approver - FIXED: Remove attendance_detail route
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        return Inertia::render('approver/index', [
            'title' => 'Approver Dashboard',
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
                'approver_index' => route('approver.index'),
                'dashboard_stats' => route('approver.dashboard.stats'),
                'pending_attendance' => route('approver.attendance.pending'),
                'approve_attendance' => route('approver.attendance.approve'),
                'reject_attendance' => route('approver.attendance.reject'),
                'attendance_history' => route('approver.attendance.history'),
                'mandors_pending' => route('approver.mandors.pending'),
            ],
        ]);
    }

    /**
     * Get dashboard statistics for approver
     * Returns real-time data for dashboard stats
     */
    public function getDashboardStats(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $companyCode = $user->companycode;
            $today = Carbon::today()->format('Y-m-d');
            
            $baseQuery = DB::table('absenlst as al')
                ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                ->where('ah.companycode', $companyCode)
                ->whereDate('ah.uploaddate', $today);
            
            // Main stats
            $pendingCount = (clone $baseQuery)->where('al.approval_status', 'PENDING')->count();
            $approvedToday = (clone $baseQuery)->where('al.approval_status', 'APPROVED')->whereDate('al.approval_date', $today)->count();
            $rejectedToday = (clone $baseQuery)->where('al.approval_status', 'REJECTED')->whereDate('al.rejection_date', $today)->count();
            $totalWorkersToday = (clone $baseQuery)->count();
            $mandorCount = DB::table('absenhdr as ah')->where('ah.companycode', $companyCode)->whereDate('ah.uploaddate', $today)->distinct()->count('ah.mandorid');
            
            // NEW: HADIR/LOKASI breakdown
            $hadirPending = (clone $baseQuery)->where('al.approval_status', 'PENDING')->where(DB::raw("COALESCE(al.absentype, 'HADIR')"), 'HADIR')->count();
            $lokasiPending = (clone $baseQuery)->where('al.approval_status', 'PENDING')->where(DB::raw("COALESCE(al.absentype, 'HADIR')"), 'LOKASI')->count();
            
            $hadirApproved = (clone $baseQuery)->where('al.approval_status', 'APPROVED')->whereDate('al.approval_date', $today)->where(DB::raw("COALESCE(al.absentype, 'HADIR')"), 'HADIR')->count();
            $lokasiApproved = (clone $baseQuery)->where('al.approval_status', 'APPROVED')->whereDate('al.approval_date', $today)->where(DB::raw("COALESCE(al.absentype, 'HADIR')"), 'LOKASI')->count();
            
            $hadirRejected = (clone $baseQuery)->where('al.approval_status', 'REJECTED')->whereDate('al.rejection_date', $today)->where(DB::raw("COALESCE(al.absentype, 'HADIR')"), 'HADIR')->count();
            $lokasiRejected = (clone $baseQuery)->where('al.approval_status', 'REJECTED')->whereDate('al.rejection_date', $today)->where(DB::raw("COALESCE(al.absentype, 'HADIR')"), 'LOKASI')->count();
            
            return response()->json([
                'success' => true,
                'date' => $today,
                'date_formatted' => Carbon::parse($today)->format('d F Y'),
                'stats' => [
                    'pending_count' => (int) $pendingCount,
                    'approved_today' => (int) $approvedToday,
                    'rejected_today' => (int) $rejectedToday,
                    'total_workers_today' => (int) $totalWorkersToday,
                    'mandor_count' => (int) $mandorCount,
                    // NEW: HADIR/LOKASI breakdown
                    'hadir_pending' => (int) $hadirPending,
                    'lokasi_pending' => (int) $lokasiPending,
                    'hadir_approved' => (int) $hadirApproved,
                    'lokasi_approved' => (int) $lokasiApproved,
                    'hadir_rejected' => (int) $hadirRejected,
                    'lokasi_rejected' => (int) $lokasiRejected
                ],
                'generated_at' => now()->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getDashboardStats', [
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
    // ATTENDANCE APPROVAL MANAGEMENT
    // =============================================================================

    /**
     * Get pending attendance for approval - UPDATED with absentype support
     */
    public function getPendingAttendance(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date', now()->format('Y-m-d'));
            $mandorId = $request->input('mandor_id'); // Filter by specific mandor
            
            // UPDATED: Get pending with absentype
            $query = DB::table('absenlst as al')
                ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
                ->join('user as u', 'ah.mandorid', '=', 'u.userid')
                ->where('ah.companycode', $user->companycode)
                ->where('al.approval_status', 'PENDING')
                ->whereDate('al.absenmasuk', $date);
            
            if ($mandorId) {
                $query->where('ah.mandorid', $mandorId);
            }
            
            $pendingRecords = $query->select([
                'al.absenno',
                'al.id as absen_id',
                'al.tenagakerjaid',
                'al.absenmasuk',
                DB::raw("COALESCE(al.absentype, 'HADIR') as absentype"), // NEW
                'al.checkintime', // NEW
                'al.fotoabsen',
                'al.lokasifotolat',
                'al.lokasifotolng',
                'al.keterangan',
                'al.approval_status',
                'ah.mandorid',
                'u.name as mandor_nama',
                'tk.nama as pekerja_nama',
                'tk.nik as pekerja_nik',
                'tk.gender as pekerja_gender',
                'tk.jenistenagakerja'
            ])->get();
            
            // Group by mandor for better organization
            $groupedByMandor = $pendingRecords->groupBy('mandorid')->map(function ($records, $mandorId) {
                $mandorName = $records->first()->mandor_nama;
                
                return [
                    'mandorid' => $mandorId,
                    'mandor_nama' => $mandorName,
                    'pending_count' => $records->count(),
                    'workers' => $records->map(function ($record) {
                        return [
                            'absenno' => $record->absenno,
                            'absen_id' => $record->absen_id,
                            'tenagakerjaid' => $record->tenagakerjaid,
                            'pekerja_nama' => $record->pekerja_nama,
                            'pekerja_nik' => $record->pekerja_nik,
                            'pekerja_gender' => $record->pekerja_gender === 'L' ? 'Laki-laki' : 'Perempuan',
                            'jenistenagakerja' => $this->getJenisTenagaKerjaName($record->jenistenagakerja),
                            'absenmasuk' => $record->absenmasuk,
                            'absen_time' => Carbon::parse($record->absenmasuk)->format('H:i'),
                            'absen_date_formatted' => Carbon::parse($record->absenmasuk)->format('d M Y'),
                            'absentype' => $record->absentype ?? 'HADIR', // NEW
                            'checkintime' => $record->checkintime, // NEW
                            'has_photo' => !empty($record->fotoabsen),
                            'has_location' => !empty($record->lokasifotolat),
                            'fotoabsen' => $record->fotoabsen,
                            'lokasifotolat' => $record->lokasifotolat,
                            'lokasifotolng' => $record->lokasifotolng,
                            'keterangan' => $record->keterangan,
                            'approval_status' => $record->approval_status
                        ];
                    })->toArray()
                ];
            })->values();
            
            // Get mandor list with pending counts
            $mandorList = DB::table('absenlst as al')
                ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                ->join('user as u', 'ah.mandorid', '=', 'u.userid')
                ->where('ah.companycode', $user->companycode)
                ->where('al.approval_status', 'PENDING')
                ->whereDate('al.absenmasuk', $date)
                ->select([
                    'ah.mandorid',
                    'u.name as mandor_nama',
                    DB::raw('COUNT(*) as pending_count')
                ])
                ->groupBy('ah.mandorid', 'u.name')
                ->orderBy('u.name')
                ->get();
            
            return response()->json([
                'pending_by_mandor' => $groupedByMandor->toArray(),
                'mandor_list' => $mandorList->toArray(),
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y'),
                'total_pending' => $pendingRecords->count(),
                'total_mandors' => $groupedByMandor->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getPendingAttendance', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get detailed attendance data for specific absenno
     */
    public function getAttendanceDetail(Request $request, $absenno)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            
            // Get header information
            $headerInfo = DB::table('absenhdr as ah')
                ->join('user as u', 'ah.mandorid', '=', 'u.userid')
                ->where('ah.companycode', $user->companycode)
                ->where('ah.absenno', $absenno)
                ->select([
                    'ah.absenno', 'ah.mandorid', 'u.name as mandor_nama',
                    'ah.totalpekerja', 'ah.status', 'ah.uploaddate',
                    'ah.approvaldate', 'ah.rejectdate', 'ah.updateBy'
                ])
                ->first();
            
            if (!$headerInfo) {
                return response()->json(['error' => 'Attendance record not found'], 404);
            }
            
            // Get detailed worker list
            $workerDetails = DB::table('absenlst as al')
                ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
                ->where('al.absenno', $absenno)
                ->select([
                    'al.id', 'al.tenagakerjaid', 'tk.nama', 'tk.nik', 'tk.gender',
                    'tk.jenistenagakerja', 'al.absenmasuk', 'al.keterangan',
                    'al.fotoabsen', 'al.lokasifotolat', 'al.lokasifotolng',
                    'al.createdat'
                ])
                ->orderBy('al.id')
                ->get();
            
            // Format worker details
            $formattedWorkers = $workerDetails->map(function($worker) {
                return [
                    'id' => $worker->id,
                    'tenagakerjaid' => $worker->tenagakerjaid,
                    'nama' => $worker->nama,
                    'nik' => $worker->nik,
                    'gender' => $worker->gender === 'L' ? 'Laki-laki' : 'Perempuan',
                    'jenistenagakerja' => $this->getJenisTenagaKerjaName($worker->jenistenagakerja),
                    'absenmasuk' => $worker->absenmasuk,
                    'absen_time' => Carbon::parse($worker->absenmasuk)->format('H:i'),
                    'keterangan' => $worker->keterangan,
                    'has_photo' => !empty($worker->fotoabsen),
                    'fotoabsen' => $worker->fotoabsen,
                    'has_location' => !empty($worker->lokasifotolat),
                    'lokasifotolat' => $worker->lokasifotolat,
                    'lokasifotolng' => $worker->lokasifotolng,
                    'createdat' => $worker->createdat
                ];
            });
            
            return response()->json([
                'header' => [
                    'absenno' => $headerInfo->absenno,
                    'mandorid' => $headerInfo->mandorid,
                    'mandor_nama' => $headerInfo->mandor_nama,
                    'totalpekerja' => (int) $headerInfo->totalpekerja,
                    'status' => $headerInfo->status,
                    'uploaddate' => $headerInfo->uploaddate,
                    'upload_date_formatted' => Carbon::parse($headerInfo->uploaddate)->format('d F Y, H:i'),
                    'approvaldate' => $headerInfo->approvaldate,
                    'rejectdate' => $headerInfo->rejectdate,
                    'updateBy' => $headerInfo->updateBy
                ],
                'workers' => $formattedWorkers->toArray(),
                'summary' => [
                    'total_workers' => $formattedWorkers->count(),
                    'with_photos' => $formattedWorkers->where('has_photo', true)->count(),
                    'with_location' => $formattedWorkers->where('has_location', true)->count(),
                    'earliest_checkin' => $formattedWorkers->min('absen_time'),
                    'latest_checkin' => $formattedWorkers->max('absen_time')
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getAttendanceDetail', [
                'message' => $e->getMessage(),
                'absenno' => $absenno,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve individual attendance record - UPDATED for individual approval
     */
    public function approveAttendance(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.absenno' => 'required|string',
                'items.*.absen_id' => 'required|integer',
                'items.*.tenagakerjaid' => 'required|string',
                'approval_notes' => 'nullable|string|max:500'
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $items = $request->input('items');
            $approvalNotes = $request->input('approval_notes');
            
            DB::beginTransaction();
            
            try {
                $approvedCount = 0;
                $errors = [];
                
                foreach ($items as $item) {
                    // Verify record exists and is pending
                    $attendanceRecord = DB::table('absenlst as al')
                        ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                        ->where('ah.companycode', $user->companycode)
                        ->where('al.absenno', $item['absenno'])
                        ->where('al.id', $item['absen_id'])
                        ->where('al.tenagakerjaid', $item['tenagakerjaid'])
                        ->where('al.approval_status', 'PENDING')
                        ->first();
                    
                    if (!$attendanceRecord) {
                        $errors[] = "Record {$item['absenno']}-{$item['absen_id']} tidak ditemukan atau sudah diproses";
                        continue;
                    }
                    
                    // Approve the individual record
                    $updated = AbsenLst::approveAttendance(
                        $item['absenno'],
                        $item['absen_id'],
                        $user->name,
                        $approvalNotes
                    );
                    
                    if ($updated) {
                        $approvedCount++;
                    } else {
                        $errors[] = "Gagal approve record {$item['absenno']}-{$item['absen_id']}";
                    }
                }
                
                if ($approvedCount === 0) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada record yang berhasil diapprove',
                        'errors' => $errors
                    ]);
                }
                
                DB::commit();
                
                $message = "{$approvedCount} absensi berhasil diapprove";
                if (count($errors) > 0) {
                    $message .= ", {$count($errors)} gagal";
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'approved_count' => $approvedCount,
                    'errors' => $errors
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in approveAttendance', [
                'message' => $e->getMessage(),
                'items' => $request->input('items'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Reject individual attendance record - UPDATED for individual approval
 */
public function rejectAttendance(Request $request)
{
    try {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.absenno' => 'required|string',
            'items.*.absen_id' => 'required|integer',
            'items.*.tenagakerjaid' => 'required|string',
            'rejection_reason' => 'required|string|max:500'
        ]);
        
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $user = auth()->user();
        $items = $request->input('items');
        $rejectionReason = $request->input('rejection_reason');
        
        DB::beginTransaction();
        
        try {
            $rejectedCount = 0;
            $errors = [];
            
            foreach ($items as $item) {
                // Verify record exists and is pending
                $attendanceRecord = DB::table('absenlst as al')
                    ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                    ->where('ah.companycode', $user->companycode)
                    ->where('al.absenno', $item['absenno'])
                    ->where('al.id', $item['absen_id'])
                    ->where('al.tenagakerjaid', $item['tenagakerjaid'])
                    ->where('al.approval_status', 'PENDING')
                    ->first();
                
                if (!$attendanceRecord) {
                    $errors[] = "Record {$item['absenno']}-{$item['absen_id']} tidak ditemukan atau sudah diproses";
                    continue;
                }
                
                // Reject the individual record
                $updated = AbsenLst::rejectAttendance(
                    $item['absenno'],
                    $item['absen_id'],
                    $user->name,
                    $rejectionReason
                );
                
                if ($updated) {
                    $rejectedCount++;
                } else {
                    $errors[] = "Gagal reject record {$item['absenno']}-{$item['absen_id']}";
                }
            }
            
            if ($rejectedCount === 0) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada record yang berhasil direject',
                    'errors' => $errors
                ]);
            }
            
            DB::commit();
            
            $message = "{$rejectedCount} absensi berhasil direject";
            if (count($errors) > 0) {
                $message .= ", " . count($errors) . " gagal";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'rejected_count' => $rejectedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
        
    } catch (\Exception $e) {
        Log::error('Error in rejectAttendance', [
            'message' => $e->getMessage(),
            'items' => $request->input('items'),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'error' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

   /**
     * Get attendance history - UPDATED for individual records
     */
    public function getAttendanceHistory(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date');
            $status = $request->input('status', 'ALL');
            $mandorId = $request->input('mandor_id');
            
            $query = DB::table('absenlst as al')
                ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
                ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
                ->join('user as u', 'ah.mandorid', '=', 'u.userid')
                ->where('ah.companycode', $user->companycode)
                ->whereIn('al.approval_status', ['APPROVED', 'REJECTED']);
            
            if ($date) {
                $query->whereDate('al.absenmasuk', $date);
            }
            
            if ($status !== 'ALL') {
                $query->where('al.approval_status', $status);
            }
            
            if ($mandorId) {
                $query->where('ah.mandorid', $mandorId);
            }
            
            $historyRecords = $query
                ->select([
                    'al.absenno', 'al.id as absen_id', 'al.tenagakerjaid',
                    'al.absenmasuk', 'al.approval_status', 'al.approval_date', 
                    'al.rejection_date', 'al.approved_by', 'al.rejection_reason',
                    'al.is_edited', 'al.edit_count',
                    DB::raw("COALESCE(al.absentype, 'HADIR') as absentype"), // NEW
                    'al.checkintime', // NEW
                    'ah.mandorid', 'u.name as mandor_nama',
                    'tk.nama as pekerja_nama', 'tk.nik as pekerja_nik'
                ])
                ->orderBy('al.absenmasuk', 'desc')
                ->limit(100)
                ->get();
            
            $formattedRecords = $historyRecords->map(function($record) {
                $processedDate = $record->approval_status === 'APPROVED' ? $record->approval_date : $record->rejection_date;
                
                return [
                    'absenno' => $record->absenno,
                    'absen_id' => $record->absen_id,
                    'tenagakerjaid' => $record->tenagakerjaid,
                    'pekerja_nama' => $record->pekerja_nama,
                    'pekerja_nik' => $record->pekerja_nik,
                    'mandorid' => $record->mandorid,
                    'mandor_nama' => $record->mandor_nama,
                    'absenmasuk' => $record->absenmasuk,
                    'absen_time' => Carbon::parse($record->absenmasuk)->format('H:i'),
                    'absen_date_formatted' => Carbon::parse($record->absenmasuk)->format('d M Y'),
                    'absentype' => $record->absentype ?? 'HADIR', // NEW
                    'checkintime' => $record->checkintime, // NEW
                    'approval_status' => $record->approval_status,
                    'status_label' => $record->approval_status === 'APPROVED' ? 'Disetujui' : 'Ditolak',
                    'processed_date' => $processedDate,
                    'processed_date_formatted' => $processedDate ? Carbon::parse($processedDate)->format('d M Y, H:i') : null,
                    'approved_by' => $record->approved_by,
                    'rejection_reason' => $record->rejection_reason,
                    'is_edited' => $record->is_edited,
                    'edit_count' => $record->edit_count
                ];
            });
            
            return response()->json([
                'history' => $formattedRecords->toArray(),
                'summary' => [
                    'total_records' => $formattedRecords->count(),
                    'approved_count' => $formattedRecords->where('approval_status', 'APPROVED')->count(),
                    'rejected_count' => $formattedRecords->where('approval_status', 'REJECTED')->count(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in getAttendanceHistory', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
        }
    }

    // =============================================================================
    // PRIVATE HELPER METHODS
    // =============================================================================

    /**
     * Get jenis tenaga kerja name from ID
     */
    private function getJenisTenagaKerjaName($jenisId)
    {
        $jenisMap = [1 => 'Harian', 2 => 'Borongan', 3 => 'Kontrak'];
        return $jenisMap[$jenisId] ?? "Jenis $jenisId";
    }

    /**
 * Get mandor list with pending counts - NEW method
 */
public function getMandorListWithPending(Request $request)
{
    try {
        if (!auth()->check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        
        $user = auth()->user();
        $date = $request->input('date', now()->format('Y-m-d'));
        
        $mandorList = AbsenLst::getMandorListWithPendingCount($user->companycode, $date);
        
        return response()->json([
            'mandor_list' => $mandorList->toArray(),
            'date' => $date,
            'date_formatted' => Carbon::parse($date)->format('d F Y')
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in getMandorListWithPending', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json(['error' => 'Internal server error: ' . $e->getMessage()], 500);
    }
}
}