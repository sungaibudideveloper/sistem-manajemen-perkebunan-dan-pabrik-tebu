<?php
// app\Models\AbsenLst.php - Updated for Individual Approval Flow

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // FIXED: Add Log import

class AbsenLst extends Model
{
    protected $table = 'absenlst';
   
    public $incrementing = false;
    public $timestamps = false;
   
    protected $fillable = [
        'absenno',
        'id',
        'tenagakerjaid',
        'absenmasuk',
        'keterangan',
        'fotoabsen',
        'lokasifotolat',
        'lokasifotolng',
        'approval_status',
        'approval_date',
        'approved_by',
        'rejection_reason',
        'rejection_date',
        'is_edited',
        'edit_count',
        'last_edited_at',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'absenmasuk' => 'datetime',
        'approval_date' => 'datetime',
        'rejection_date' => 'datetime',
        'last_edited_at' => 'datetime',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'lokasifotolat' => 'decimal:8',
        'lokasifotolng' => 'decimal:8',
        'is_edited' => 'boolean',
        'edit_count' => 'integer',
    ];

    // Relasi ke header absen
    public function absenHeader()
    {
        return $this->belongsTo(AbsenHdr::class, 'absenno', 'absenno');
    }

    // Relasi ke tenaga kerja
    public function tenagaKerja()
    {
        return $this->belongsTo(TenagaKerja::class, 'tenagakerjaid', 'tenagakerjaid');
    }

    // Get attendance by mandor and date - UPDATED for individual approval
    public static function getAttendanceByMandorAndDate($companyCode, $mandorId, $date)
    {
        return DB::table('absenlst as al')
            ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
            ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
            ->where('ah.companycode', $companyCode)
            ->where('ah.mandorid', $mandorId)
            ->whereDate('al.absenmasuk', $date)
            ->select([
                'al.absenno',
                'al.id',
                'al.tenagakerjaid',
                'al.absenmasuk',
                'al.fotoabsen',
                'al.lokasifotolat',
                'al.lokasifotolng',
                'al.approval_status',
                'al.approval_date',
                'al.approved_by',
                'al.rejection_reason',
                'al.rejection_date',
                'al.is_edited',
                'al.edit_count',
                'tk.nama',
                'tk.nik',
                'tk.gender',
                'tk.jenistenagakerja'
            ])
            ->orderBy('al.absenmasuk')
            ->get();
    }

    // Get only APPROVED attendance for LKH assignment
    public static function getApprovedAttendanceByMandorAndDate($companyCode, $mandorId, $date)
    {
        return DB::table('absenlst as al')
            ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
            ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
            ->where('ah.companycode', $companyCode)
            ->where('ah.mandorid', $mandorId)
            ->where('al.approval_status', 'APPROVED')
            ->whereDate('al.absenmasuk', $date)
            ->select([
                'al.tenagakerjaid',
                'tk.nama',
                'tk.nik',
                'tk.gender',
                'tk.jenistenagakerja'
            ])
            ->orderBy('tk.nama')
            ->get();
    }

    // Get pending attendance by mandor for approver
    public static function getPendingAttendanceByMandor($companyCode, $mandorId, $date = null)
    {
        $query = DB::table('absenlst as al')
            ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
            ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
            ->join('user as u', 'ah.mandorid', '=', 'u.userid')
            ->where('ah.companycode', $companyCode)
            ->where('al.approval_status', 'PENDING');

        if ($mandorId) {
            $query->where('ah.mandorid', $mandorId);
        }

        if ($date) {
            $query->whereDate('al.absenmasuk', $date);
        }

        return $query->select([
                'al.absenno',
                'al.id as absen_id',
                'al.tenagakerjaid',
                'al.absenmasuk',
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
            ])
            ->orderBy('al.absenmasuk', 'desc')
            ->get();
    }

    // Get rejected attendance by mandor
    public static function getRejectedAttendanceByMandor($companyCode, $mandorId, $date = null)
    {
        $query = DB::table('absenlst as al')
            ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
            ->join('tenagakerja as tk', 'al.tenagakerjaid', '=', 'tk.tenagakerjaid')
            ->where('ah.companycode', $companyCode)
            ->where('ah.mandorid', $mandorId)
            ->where('al.approval_status', 'REJECTED');

        if ($date) {
            $query->whereDate('al.absenmasuk', $date);
        }

        return $query->select([
                'al.absenno',
                'al.id as absen_id',
                'al.tenagakerjaid',
                'al.absenmasuk',
                'al.fotoabsen',
                'al.rejection_reason',
                'al.rejection_date',
                'al.is_edited',
                'al.edit_count',
                'tk.nama as pekerja_nama',
                'tk.nik as pekerja_nik',
            ])
            ->orderBy('al.rejection_date', 'desc')
            ->get();
    }

    // Check if worker already checked in today
    public static function hasCheckedInToday($companyCode, $mandorId, $tenagakerjaId, $date)
    {
        return DB::table('absenlst as al')
            ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
            ->where('ah.companycode', $companyCode)
            ->where('ah.mandorid', $mandorId)
            ->where('al.tenagakerjaid', $tenagakerjaId)
            ->whereDate('al.absenmasuk', $date)
            ->exists();
    }

    // Create attendance record with PENDING status
    public static function createRecord($data)
    {
        $data['approval_status'] = 'PENDING';
        $data['createdat'] = now();
        $data['updatedat'] = now();
        
        return DB::table('absenlst')->insert($data);
    }

    // Update photo with reset approval status
    public static function updatePhotoAndResetApproval($absenno, $absenId, $photoData, $lat = null, $lng = null)
    {
        $result = DB::table('absenlst')
            ->where('absenno', $absenno)
            ->where('id', $absenId)
            ->update([
                'fotoabsen' => $photoData,
                'lokasifotolat' => $lat,
                'lokasifotolng' => $lng,
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
        
        return $result;
    }

    // Approve individual attendance
    public static function approveAttendance($absenno, $absenId, $approvedBy, $notes = null)
    {
        return DB::table('absenlst')
            ->where('absenno', $absenno)
            ->where('id', $absenId)
            ->update([
                'approval_status' => 'APPROVED',
                'approval_date' => now(),
                'approved_by' => $approvedBy . ($notes ? ' - ' . $notes : ''),
                'rejection_reason' => null,
                'rejection_date' => null,
                'updatedat' => now()
            ]);
    }

    // Reject individual attendance
    public static function rejectAttendance($absenno, $absenId, $approvedBy, $reason)
    {
        return DB::table('absenlst')
            ->where('absenno', $absenno)
            ->where('id', $absenId)
            ->update([
                'approval_status' => 'REJECTED',
                'rejection_reason' => $reason,
                'rejection_date' => now(),
                'approved_by' => $approvedBy,
                'approval_date' => null,
                'updatedat' => now()
            ]);
    }

    // Get mandor list with pending attendance count
    public static function getMandorListWithPendingCount($companyCode, $date = null)
    {
        $query = DB::table('absenlst as al')
            ->join('absenhdr as ah', 'al.absenno', '=', 'ah.absenno')
            ->join('user as u', 'ah.mandorid', '=', 'u.userid')
            ->where('ah.companycode', $companyCode)
            ->where('al.approval_status', 'PENDING');

        if ($date) {
            $query->whereDate('al.absenmasuk', $date);
        }

        return $query->select([
                'ah.mandorid',
                'u.name as mandor_nama',
                DB::raw('COUNT(*) as pending_count')
            ])
            ->groupBy('ah.mandorid', 'u.name')
            ->orderBy('u.name')
            ->get();
    }
}