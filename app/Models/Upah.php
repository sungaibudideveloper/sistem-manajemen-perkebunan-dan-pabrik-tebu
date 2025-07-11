<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upah extends Model
{
    protected $table = 'upah'; // Nama tabel
    public $timestamps = false;

    protected $primaryKey = 'upahid'; // ? Tambahkan baris ini
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'upahid',
        'harga',
        'tanggalefektif',
        'inputby',
        'createdat',
        'companycode',
    ];

    protected $casts = [
        'createdat' => 'datetime',
    ];
}
