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
     * Main SPA Dashboard entry point for Approver
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
                
                // Approval routes
                'pending_attendance' => route('approver.attendance.pending'),
                'attendance_detail' => route('approver.attendance.detail', ['absenno' => '__ABSENNO__']),
                'approve_attendance' => route('approver.attendance.approve'),
                'reject_attendance' => route('approver.attendance.reject'),
                'attendance_history' => route('approver.attendance.history'),
            ],
        ]);
    }

    // =============================================================================
    // ATTENDANCE APPROVAL MANAGEMENT
    // =============================================================================

    /**
     * Get pending attendance for approval
     */
    public function getPendingAttendance(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date', now()->format('Y-m-d'));
            
            // Get pending attendance records
            $pendingRecords = DB::table('absenhdr as ah')
                ->join('user as u', 'ah.mandorid', '=', 'u.userid')
                ->where('ah.companycode', $user->companycode)
                ->where('ah.status', 'PENDING')
                ->when($date, function($query, $date) {
                    return $query->whereDate('ah.uploaddate', $date);
                })
                ->select([
                    'ah.absenno', 'ah.mandorid', 'u.name as mandor_nama',
                    'ah.totalpekerja', 'ah.status', 'ah.uploaddate',
                    'ah.updateBy'
                ])
                ->orderBy('ah.uploaddate', 'desc')
                ->get();
            
            // Get additional info for each record
            $enrichedRecords = $pendingRecords->map(function($record) use ($user) {
                // Get worker details for this attendance
                $workerDetails = DB::table('absenlst as al')
                    ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
                    ->where('al.absenno', $record->absenno)
                    ->select([
                        'tk.nama', 'tk.nik', 'al.absenmasuk',
                        'al.keterangan', 'al.lokasifotolat', 'al.lokasifotolng'
                    ])
                    ->get();
                
                return [
                    'absenno' => $record->absenno,
                    'mandorid' => $record->mandorid,
                    'mandor_nama' => $record->mandor_nama,
                    'totalpekerja' => (int) $record->totalpekerja,
                    'status' => $record->status,
                    'uploaddate' => $record->uploaddate,
                    'updateBy' => $record->updateBy,
                    'upload_time' => Carbon::parse($record->uploaddate)->format('H:i'),
                    'upload_date_formatted' => Carbon::parse($record->uploaddate)->format('d M Y'),
                    'worker_count' => $workerDetails->count(),
                    'has_photos' => $workerDetails->where('fotoabsen', '!=', null)->count() > 0,
                    'has_location' => $workerDetails->where('lokasifotolat', '!=', null)->count() > 0,
                ];
            });
            
            return response()->json([
                'pending_attendance' => $enrichedRecords->toArray(),
                'date' => $date,
                'date_formatted' => Carbon::parse($date)->format('d F Y'),
                'total_pending' => $enrichedRecords->count(),
                'total_workers' => $enrichedRecords->sum('totalpekerja')
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
     * Approve attendance record
     */
    public function approveAttendance(Request $request)
    {
        try {
            $request->validate([
                'absenno' => 'required|string',
                'approval_notes' => 'nullable|string|max:500'
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $absenno = $request->input('absenno');
            $approvalNotes = $request->input('approval_notes');
            
            DB::beginTransaction();
            
            try {
                // Check if record exists and is pending
                $attendanceRecord = DB::table('absenhdr')
                    ->where('companycode', $user->companycode)
                    ->where('absenno', $absenno)
                    ->where('status', 'PENDING')
                    ->first();
                
                if (!$attendanceRecord) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Record tidak ditemukan atau sudah diproses'
                    ]);
                }
                
                // Update status to APPROVED
                DB::table('absenhdr')
                    ->where('companycode', $user->companycode)
                    ->where('absenno', $absenno)
                    ->update([
                        'status' => 'APPROVED',
                        'approvaldate' => now(),
                        'updateBy' => $user->name . ($approvalNotes ? ' - ' . $approvalNotes : '')
                    ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Absensi {$absenno} berhasil diapprove"
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in approveAttendance', [
                'message' => $e->getMessage(),
                'absenno' => $request->input('absenno'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject attendance record
     */
    public function rejectAttendance(Request $request)
    {
        try {
            $request->validate([
                'absenno' => 'required|string',
                'rejection_reason' => 'required|string|max:500'
            ]);
            
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $absenno = $request->input('absenno');
            $rejectionReason = $request->input('rejection_reason');
            
            DB::beginTransaction();
            
            try {
                // Check if record exists and is pending
                $attendanceRecord = DB::table('absenhdr')
                    ->where('companycode', $user->companycode)
                    ->where('absenno', $absenno)
                    ->where('status', 'PENDING')
                    ->first();
                
                if (!$attendanceRecord) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Record tidak ditemukan atau sudah diproses'
                    ]);
                }
                
                // Update status to REJECTED
                DB::table('absenhdr')
                    ->where('companycode', $user->companycode)
                    ->where('absenno', $absenno)
                    ->update([
                        'status' => 'REJECTED',
                        'rejectdate' => now(),
                        'updateBy' => $user->name . ' - REJECT: ' . $rejectionReason
                    ]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => "Absensi {$absenno} berhasil ditolak"
                ]);
                
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error in rejectAttendance', [
                'message' => $e->getMessage(),
                'absenno' => $request->input('absenno'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance history (approved/rejected)
     */
    public function getAttendanceHistory(Request $request)
    {
        try {
            if (!auth()->check()) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }
            
            $user = auth()->user();
            $date = $request->input('date');
            $status = $request->input('status', 'ALL'); // ALL, APPROVED, REJECTED
            $mandorId = $request->input('mandor_id');
            
            $query = DB::table('absenhdr as ah')
                ->join('user as u', 'ah.mandorid', '=', 'u.userid')
                ->where('ah.companycode', $user->companycode)
                ->whereIn('ah.status', ['APPROVED', 'REJECTED']);
            
            // Filter by date
            if ($date) {
                $query->whereDate('ah.uploaddate', $date);
            }
            
            // Filter by status
            if ($status !== 'ALL') {
                $query->where('ah.status', $status);
            }
            
            // Filter by mandor
            if ($mandorId) {
                $query->where('ah.mandorid', $mandorId);
            }
            
            $historyRecords = $query
                ->select([
                    'ah.absenno', 'ah.mandorid', 'u.name as mandor_nama',
                    'ah.totalpekerja', 'ah.status', 'ah.uploaddate',
                    'ah.approvaldate', 'ah.rejectdate', 'ah.updateBy'
                ])
                ->orderBy('ah.uploaddate', 'desc')
                ->limit(100)
                ->get();
            
            // Format records
            $formattedRecords = $historyRecords->map(function($record) {
                $processedDate = $record->status === 'APPROVED' ? $record->approvaldate : $record->rejectdate;
                
                return [
                    'absenno' => $record->absenno,
                    'mandorid' => $record->mandorid,
                    'mandor_nama' => $record->mandor_nama,
                    'totalpekerja' => (int) $record->totalpekerja,
                    'status' => $record->status,
                    'status_label' => $record->status === 'APPROVED' ? 'Disetujui' : 'Ditolak',
                    'uploaddate' => $record->uploaddate,
                    'upload_date_formatted' => Carbon::parse($record->uploaddate)->format('d M Y, H:i'),
                    'processed_date' => $processedDate,
                    'processed_date_formatted' => $processedDate ? Carbon::parse($processedDate)->format('d M Y, H:i') : null,
                    'updateBy' => $record->updateBy
                ];
            });
            
            return response()->json([
                'history' => $formattedRecords->toArray(),
                'summary' => [
                    'total_records' => $formattedRecords->count(),
                    'approved_count' => $formattedRecords->where('status', 'APPROVED')->count(),
                    'rejected_count' => $formattedRecords->where('status', 'REJECTED')->count(),
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
}