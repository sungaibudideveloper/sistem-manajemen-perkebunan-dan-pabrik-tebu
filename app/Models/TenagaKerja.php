<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenagaKerja extends Model
{
    // Nama tabel yang digunakan
    protected $table = 'tenagakerja';
    protected $primaryKey = 'tenagakerjaid';
    public $incrementing = false;
    protected $keyType = 'string';

    // Tidak ada kolom created_at / updated_at di tabel ini
    public $timestamps = false;
    // Kolom-kolom yang boleh diisi lewat mass assignment
    protected $fillable = [
        'tenagakerjaid',
        'mandoruserid',
        'companycode',
        'nama',
        'nik',
        'gender',
        'jenistenagakerja',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
        'isactive'
    ];
}
