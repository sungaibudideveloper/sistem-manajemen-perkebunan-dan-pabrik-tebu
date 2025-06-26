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
        'tenagakerjaid',
        'idmandor',
    ];

        public function getDataAbsenFull($companycode, $date, $mandorId = null)
{
    $sql = "SELECT
                a.companycode,
                a.absentime,
                a.idtenagakerja as id,
                a.idmandor,
                b.nama,
                b.gender,
                b.jenistenagakerja,
                m.name as mandor_nama,
                TIME(a.absentime) as jam_absen
            FROM absentenagakerja a
            JOIN tenagakerja b ON a.idtenagakerja = b.tenagakerjaid
            LEFT JOIN mandor m ON a.idmandor = m.id
            WHERE a.companycode = ?
            AND DATE(a.absentime) = ?";

    $params = [$companycode, $date];

    if ($mandorId) {
        $sql .= " AND a.idmandor = ?";
        $params[] = $mandorId;
    }

    $sql .= " ORDER BY a.absentime";

    return DB::select($sql, $params);
}

public function getMandorList($companycode, $date)
{
    return DB::select(
        "SELECT DISTINCT m.id, m.name
         FROM absentenagakerja a
         JOIN mandor m ON a.idmandor = m.id
         WHERE a.companycode = ? AND DATE(a.absentime) = ?
         ORDER BY m.name",
        [$companycode, $date]
    );
}

}
