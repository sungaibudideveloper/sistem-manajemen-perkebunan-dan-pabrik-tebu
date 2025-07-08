<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbsenLst extends Model
{
    protected $table = 'absenlst';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    protected $fillable = [
        'absenno',
        'id',
        'tenagakerjaid',
        'absenmasuk',
        'absenpulang',
        'keterangan',
    ];

    protected $casts = [
        'absenmasuk' => 'datetime',
        'absenpulang' => 'datetime',
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

    // Get detail absen by absenno
    public static function getDetailsByAbsenNo($absenno)
    {
        return DB::table('absenlst as l')
            ->join('tenagakerja as t', 'l.tenagakerjaid', '=', 't.tenagakerjaid')
            ->where('l.absenno', $absenno)
            ->select([
                'l.*',
                't.nama',
                't.gender',
                't.jenistenagakerja',
                DB::raw('TIME(l.absenmasuk) as jam_masuk'),
                DB::raw('TIME(l.absenpulang) as jam_pulang')
            ])
            ->orderBy('l.id')
            ->get();
    }

    // Get absen summary by date and mandor (hanya yang approved)
    public static function getAbsenSummary($companycode, $date, $mandorId = null)
    {
        $query = DB::table('absenlst as l')
            ->join('absenhdr as h', 'l.absenno', '=', 'h.absenno')
            ->join('tenagakerja as t', 'l.tenagakerjaid', '=', 't.tenagakerjaid')
            ->where('h.companycode', $companycode)
            ->where('h.status', 'A') // Hanya ambil yang approved
            ->whereDate('h.uploaddate', $date);

        if ($mandorId) {
            $query->where('h.mandorid', $mandorId);
        }

        return $query->select([
            'h.mandorid',
            'h.absenno',
            't.jenistenagakerja',
            't.gender',
            DB::raw('COUNT(*) as jumlah_pekerja'),
            DB::raw('SUM(CASE WHEN t.gender = "L" THEN 1 ELSE 0 END) as laki_laki'),
            DB::raw('SUM(CASE WHEN t.gender = "P" THEN 1 ELSE 0 END) as perempuan'),
            DB::raw('MIN(l.absenmasuk) as jam_masuk_pertama'),
            DB::raw('MAX(l.absenpulang) as jam_pulang_terakhir')
        ])
        ->groupBy('h.mandorid', 'h.absenno', 't.jenistenagakerja', 't.gender')
        ->get();
    }
}