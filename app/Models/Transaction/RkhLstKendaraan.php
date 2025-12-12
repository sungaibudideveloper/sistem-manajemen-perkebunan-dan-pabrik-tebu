<?php
namespace App\Models\Transaction;
// =====================================================
// FILE: app/Models/Transaction/RkhLstKendaraan.php
// =====================================================



use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Kendaraan;
use App\Models\MasterData\TenagaKerja;
use App\Models\MasterData\Activity;

class RkhLstKendaraan extends Model
{
    protected $table = 'rkhlstkendaraan';
    protected $primaryKey = 'id';
    public $incrementing = true;
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

    // Relationships (FK menggunakan surrogate ID)
    public function rkhHeader()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhhdrid', 'id');
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

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }
}