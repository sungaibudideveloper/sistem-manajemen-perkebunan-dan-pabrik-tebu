<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upah extends Model
{
    protected $table = 'upah'; // Nama tabel
    public $timestamps = false;

    protected $primaryKey = 'jenisupah'; // ? Tambahkan baris ini
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'jenisupah',
        'harga',
        'tanggalefektif',
        'inputby',
        'createdat',
    ];

    protected $casts = [
        'createdat' => 'datetime',
    ];
}
