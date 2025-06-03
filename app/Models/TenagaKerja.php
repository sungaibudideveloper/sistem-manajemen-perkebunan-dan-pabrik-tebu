<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenagaKerja extends Model
{
    // Nama tabel yang digunakan
    protected $table = 'tenagakerja';

    // Laravel tidak mendukung primary key komposit secara bawaan.
    // Jika Anda ingin menggunakan kedua kolom sebagai primary key, Anda
    // perlu melakukan penanganan manual atau menggunakan package tambahan.
    // Di contoh ini, kita tetapkan primaryKey hanya 'idtenagakerja' dan
    // mematikan auto-increment karena ID bertipe varchar.
    protected $primaryKey = 'idtenagakerja';
    public $incrementing = false;
    protected $keyType = 'string';

    // Tidak ada kolom created_at / updated_at di tabel ini
    public $timestamps = false;

    // Kolom-kolom yang boleh diisi lewat mass assignment
    protected $fillable = [
        'idtenagakerja',
        'companycode',
        'nama',
        'nik',
        'gender',
        'jenistenagakerja',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];
}
