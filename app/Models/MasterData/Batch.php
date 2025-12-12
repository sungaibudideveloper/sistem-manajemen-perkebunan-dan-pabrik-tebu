<?php
// =====================================================
// MASTER DATA MODELS
// =====================================================

// =====================================================
// FILE: app/Models/MasterData/Batch.php
// =====================================================
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\LkhDetailPlot;
use App\Models\Transaction\LkhDetailBsm;
use App\Models\Transaction\Rkhlst;

class Batch extends Model
{
    protected $table = 'batch';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'batchno',
        'companycode',
        'plot',
        'batcharea',
        'batchdate',
        'tanggalulangtahun',
        'lifecyclestatus',
        'previousbatchno',
        'plantinglkhno',
        'tanggalpanen',
        'kontraktorid',
        'kodevarietas',
        'pkp',
        'lastactivity',
        'isactive',
        'closedat',
        'inputby',
        'createdat',
        'plottype',
    ];

    protected $casts = [
        'batchdate' => 'date',
        'tanggalulangtahun' => 'date',
        'tanggalpanen' => 'date',
        'batcharea' => 'decimal:2',
        'pkp' => 'integer',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'closedat' => 'datetime',
    ];

    // Relationships (FK menggunakan surrogate ID)
    public function lkhDetailPlots()
    {
        return $this->hasMany(LkhDetailPlot::class, 'batchid', 'id');
    }

    public function lkhDetailBsm()
    {
        return $this->hasMany(LkhDetailBsm::class, 'batchid', 'id');
    }

    public function rkhLst()
    {
        return $this->hasMany(Rkhlst::class, 'batchid', 'id');
    }

    // Finder by business key
    public static function findByBusinessKey(string $batchno, string $companycode): ?self
    {
        return static::where('batchno', $batchno)
            ->where('companycode', $companycode)
            ->first();
    }
}