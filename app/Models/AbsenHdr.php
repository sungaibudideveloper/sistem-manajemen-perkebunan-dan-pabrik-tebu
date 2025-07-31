<?php
// app\Models\AbsenHdr.php - FIXED
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbsenHdr extends Model
{
    protected $table = 'absenhdr';
    
    // FIXED: Use single primary key instead of composite
    protected $primaryKey = 'absenno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'absenno',
        'companycode',
        'mandorid',
        'totalpekerja',
        'status',
        'uploaddate',
        'approvaldate',
        'rejectdate',
        'updateBy',
    ];

    protected $casts = [
        'uploaddate' => 'datetime',
        'approvaldate' => 'datetime',
        'rejectdate' => 'datetime',
    ];

    // Relasi ke detail absen
    public function absenDetails()
    {
        return $this->hasMany(AbsenLst::class, 'absenno', 'absenno');
    }

    // Relasi ke mandor
    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    // Custom method to safely increment totalpekerja
    public function incrementTotalPekerja()
    {
        return DB::table('absenhdr')
            ->where('absenno', $this->absenno)
            ->where('companycode', $this->companycode)
            ->increment('totalpekerja');
    }

    // Custom method to safely update
    public function updateSafely($data)
    {
        return DB::table('absenhdr')
            ->where('absenno', $this->absenno)
            ->where('companycode', $this->companycode)
            ->update($data);
    }

    // Get absen data dengan filter (hanya yang approved)
    public function getAbsenData($companycode, $date = null, $mandorId = null)
    {
        $query = DB::table('absenhdr as h')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->where('h.companycode', $companycode)
            ->where('h.status', 'A'); // Hanya ambil yang approved

        if ($date) {
            $query->whereDate('h.uploaddate', $date);
        }

        if ($mandorId) {
            $query->where('h.mandorid', $mandorId);
        }

        return $query->select([
            'h.*',
            'm.name as mandor_nama'
        ])->get();
    }

    // Get full absen data dengan detail pekerja (hanya yang approved)
    public function getDataAbsenFull($companycode, $date, $mandorId = null)
    {
        $query = DB::table('absenhdr as h')
            ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
            ->join('tenagakerja as t', 'l.tenagakerjaid', '=', 't.tenagakerjaid')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('jenistenagakerja as jtk', 't.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->where('h.companycode', $companycode)
            ->where('h.status', 'A'); // Hanya ambil yang approved

        if ($date) {
            $query->whereDate('h.uploaddate', $date);
        }

        if ($mandorId) {
            $query->where('h.mandorid', $mandorId);
        }

        return $query->select([
            'h.absenno',
            'h.companycode',
            'h.mandorid',
            'h.uploaddate as absentime',
            'l.tenagakerjaid as id',
            'l.absenmasuk',
            'l.absenpulang',
            'l.keterangan',
            't.nama',
            't.gender',
            't.jenistenagakerja',
            'jtk.nama as jenistenagakerja_nama',
            'm.name as mandor_nama',
            DB::raw('TIME(l.absenmasuk) as jam_absen')
        ])->orderBy('h.uploaddate')->get();
    }

    // Get mandor list yang sudah absen approved
    public function getMandorList($companycode, $date)
    {
        return DB::table('absenhdr as h')
            ->join('user as m', 'h.mandorid', '=', 'm.userid')
            ->where('h.companycode', $companycode)
            ->where('h.status', 'A') // Hanya ambil yang approved
            ->whereDate('h.uploaddate', $date)
            ->select('m.userid as id', 'm.name')
            ->distinct()
            ->orderBy('m.name')
            ->get();
    }
}