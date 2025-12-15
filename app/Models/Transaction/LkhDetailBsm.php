<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

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
        'parentbsm',
    ];

    protected $casts = [
        'nilaibersih' => 'decimal:2',
        'nilaisegar' => 'decimal:2',
        'nilaimanis' => 'decimal:2',
        'averagescore' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function lkhheader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhhdrid', 'id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchid', 'id');
    }
}