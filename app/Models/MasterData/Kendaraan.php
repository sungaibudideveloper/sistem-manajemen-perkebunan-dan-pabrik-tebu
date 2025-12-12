<?php
// =====================================================
// FILE: app/Models/MasterData/Kendaraan.php
// =====================================================
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\LkhDetailKendaraan;
use App\Models\Transaction\RkhLstKendaraan;

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
        'hourmeter',
        'jenis',
        'inputby',
        'createdat',
        'updateby',
        'updatedate',
        'isactive'
    ];

    protected $casts = [
        'hourmeter' => 'float',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedate' => 'datetime'
    ];

    // Relationships (FK menggunakan surrogate ID)
    public function operator()
    {
        return $this->belongsTo(TenagaKerja::class, 'idtenagakerja', 'tenagakerjaid');
    }

    public function lkhDetailKendaraan()
    {
        return $this->hasMany(LkhDetailKendaraan::class, 'kendaraanid', 'id');
    }

    public function rkhLstKendaraan()
    {
        return $this->hasMany(RkhLstKendaraan::class, 'kendaraanid', 'id');
    }

    // Finder by business key
    public static function findByBusinessKey(string $companycode, string $nokendaraan): ?self
    {
        return static::where('companycode', $companycode)
            ->where('nokendaraan', $nokendaraan)
            ->first();
    }
}