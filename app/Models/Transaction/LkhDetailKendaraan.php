<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Kendaraan;
use App\Models\MasterData\TenagaKerja;

class LkhDetailKendaraan extends Model
{
    protected $table = 'lkhdetailkendaraan';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'lkhno',
        'lkhhdrid',
        'nokendaraan',
        'kendaraanid',
        'operatorid',
        'hourmeterstart',
        'hourmeterend',
        'hourmeterusage',
        'fuelused',
        'keterangan',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'hourmeterstart' => 'decimal:2',
        'hourmeterend' => 'decimal:2',
        'hourmeterusage' => 'decimal:2',
        'fuelused' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    // Relationships (FK menggunakan surrogate ID)
    public function lkhHeader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhhdrid', 'id');
    }

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraanid', 'id');
    }

    public function operator()
    {
        return $this->belongsTo(TenagaKerja::class, 'operatorid', 'tenagakerjaid');
    }
}