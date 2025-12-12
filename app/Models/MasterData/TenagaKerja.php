<?php
// =====================================================
// FILE: app/Models/MasterData/TenagaKerja.php
// =====================================================
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class TenagaKerja extends Model
{
    protected $table = 'tenagakerja';
    protected $primaryKey = 'tenagakerjaid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

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

    protected $casts = [
        'jenistenagakerja' => 'integer',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];
}