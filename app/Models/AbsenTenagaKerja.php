<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbsenTenagaKerja extends Model
{
    protected $table = 'absentenagakerja';

    protected $primaryKey = 'id';

    public $timestamps = false; // karena tidak ada kolom created_at dan updated_at

    protected $fillable = [
        'companycode',
        'absentime',
        'idtenagakerja',
        'idmandor',
    ];

        public function getDataAbsenFull($companycode,$date)
    {
        return DB::select(
            "SELECT 
                a.companycode,
                a.absentime,
                a.idtenagakerja,
                a.idmandor,
                b.nama,
                b.gender,
                b.jenistenagakerja
            FROM absentenagakerja a
            JOIN tenagakerja b ON a.idtenagakerja = b.idtenagakerja
            WHERE a.companycode = ?
            AND DATE(a.absentime) = ?",
            [$companycode,$date]
        );
    }

}
