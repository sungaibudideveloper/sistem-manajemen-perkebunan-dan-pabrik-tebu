<?php
// app\Models\AbsenLst.php - FIXED
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbsenLst extends Model
{
    protected $table = 'absenlst';
    
    // FIXED: Remove problematic primary key settings
    public $incrementing = false;
    public $timestamps = false;
    
    // Don't set primaryKey to null - let Eloquent handle it
    // protected $primaryKey = null; // REMOVE THIS LINE

    protected $fillable = [
        'absenno',
        'id',
        'tenagakerjaid',
        'absenmasuk',
        'absenpulang',
        'keterangan',
        'fotoabsen',
        'lokasifotolat',
        'lokasifotolng',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'absenmasuk' => 'datetime',
        'absenpulang' => 'datetime',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'lokasifotolat' => 'decimal:8',
        'lokasifotolng' => 'decimal:8',
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

    // Get attendance by mandor and date with photos
    public static function getAttendanceByMandorAndDate($companyCode, $mandorId, $date)
    {
        return self::join('absenhdr as ah', 'absenlst.absenno', '=', 'ah.absenno')
            ->join('tenagakerja as tk', 'absenlst.tenagakerjaid', '=', 'tk.tenagakerjaid')
            ->where('ah.companycode', $companyCode)
            ->where('ah.mandorid', $mandorId)
            ->whereDate('absenlst.absenmasuk', $date)
            ->select([
                'absenlst.tenagakerjaid',
                'absenlst.absenmasuk',
                'absenlst.fotoabsen',
                'absenlst.lokasifotolat',
                'absenlst.lokasifotolng',
                'tk.nama',
                'tk.nik',
                'tk.gender',
                'tk.jenistenagakerja'
            ])
            ->orderBy('absenlst.absenmasuk')
            ->get();
    }

    // Check if worker already checked in today
    public static function hasCheckedInToday($companyCode, $mandorId, $tenagakerjaId, $date)
    {
        return self::join('absenhdr as ah', 'absenlst.absenno', '=', 'ah.absenno')
            ->where('ah.companycode', $companyCode)
            ->where('ah.mandorid', $mandorId) 
            ->where('absenlst.tenagakerjaid', $tenagakerjaId)
            ->whereDate('absenlst.absenmasuk', $date)
            ->exists();
    }

    // ALTERNATIVE: Use DB facade for insert to avoid Eloquent primary key issues
    public static function createRecord($data)
    {
        return DB::table('absenlst')->insert($data);
    }
}