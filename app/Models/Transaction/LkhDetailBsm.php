<?php
// =====================================================
// FILE: app/Models/Transaction/LkhDetailBsm.php
// =====================================================
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Batch;

class LkhDetailBsm extends Model
{
    protected $table = 'lkhdetailbsm';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'lkhno',
        'lkhhdrid',
        'suratjalanno',
        'plot',
        'kodetebang',
        'batchno',
        'batchid',
        'nilaibersih',
        'nilaisegar',
        'nilaimanis',
        'averagescore',
        'grade',
        'keterangan',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
        'parentbsm'
    ];

    protected $casts = [
        'nilaibersih' => 'decimal:2',
        'nilaisegar' => 'decimal:2',
        'nilaimanis' => 'decimal:2',
        'averagescore' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships (FK menggunakan surrogate ID)
    public function lkhHeader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhhdrid', 'id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchid', 'id');
    }
}