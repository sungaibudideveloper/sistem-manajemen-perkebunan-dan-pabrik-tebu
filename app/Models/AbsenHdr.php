<?php
// app\Models\AbsenHdr.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbsenHdr extends Model
{
    protected $table = 'absenhdr';
    
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

    public function absenDetails()
    {
        return $this->hasMany(AbsenLst::class, 'absenno', 'absenno');
    }

    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    // Get full absen data dengan detail pekerja (hanya yang approved)
    public function getDataAbsenFull($companycode, $date, $mandorId = null)
    {
        $query = DB::table('absenhdr as h')
            ->join('absenlst as l', 'h.absenno', '=', 'l.absenno')
            ->join('tenagakerja as t', 'l.tenagakerjaid', '=', 't.tenagakerjaid')
            ->leftJoin('user as m', 'h.mandorid', '=', 'm.userid')
            ->leftJoin('jenistenagakerja as jtk', 't.jenistenagakerja', '=', 'jtk.idjenistenagakerja')
            ->where('h.companycode', $companycode);

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
            'l.keterangan',
            't.nama',
            't.gender',
            't.jenistenagakerja',
            'jtk.nama as jenistenagakerja_nama',
            'm.name as mandor_nama',
            DB::raw('TIME(l.absenmasuk) as jam_absen')
        ])->orderBy('h.uploaddate')->get();
    }
}