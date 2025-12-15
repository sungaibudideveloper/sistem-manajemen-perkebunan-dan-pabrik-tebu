<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class RkhLstKendaraan extends Model
{
    protected $table = 'rkhlstkendaraan';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhhdrid',
        'activitycode',
        'nokendaraan',
        'kendaraanid',
        'operatorid',
        'usinghelper',
        'helperid',
        'urutan',
        'createdat',
    ];

    protected $casts = [
        'usinghelper' => 'boolean',
        'urutan' => 'integer',
        'createdat' => 'datetime',
    ];

    public function rkhHeader()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhhdrid', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraanid', 'id');
    }

    public function operator()
    {
        return $this->belongsTo(TenagaKerja::class, 'operatorid', 'tenagakerjaid');
    }

    public function helper()
    {
        return $this->belongsTo(TenagaKerja::class, 'helperid', 'tenagakerjaid');
    }
}