<?php

namespace App\Models\MasterData;;

use Illuminate\Database\Eloquent\Model;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'idtenagakerja',
        'nokendaraan',
        'companygroup',
        'hourmeter',
        'jenis',
        'tahunterima',
        'statuskendaraan',
        'inputby',
        'createdat',
        'updateby',
        'updatedate',
        'isactive',
    ];

    protected $casts = [
        'hourmeter' => 'float',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedate' => 'datetime',
    ];

    public function operator()
    {
        return $this->belongsTo(TenagaKerja::class, 'idtenagakerja', 'tenagakerjaid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    public function lkhDetailKendaraans()
    {
        return $this->hasMany(LkhDetailKendaraan::class, 'kendaraanid', 'id');
    }

    public function rkhlstKendaraans()
    {
        return $this->hasMany(RkhlstKendaraan::class, 'kendaraanid', 'id');
    }
}