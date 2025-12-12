<?php
// =====================================================
// FILE: app/Models/Transaction/LkhDetailPlot.php
// =====================================================
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Batch;

class LkhDetailPlot extends Model
{
    protected $table = 'lkhdetailplot';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'lkhno',
        'lkhhdrid',
        'blok',
        'plot',
        'luasrkh',
        'luashasil',
        'luassisa',
        'batchno',
        'batchid',
        'fieldbalancerit',
        'fieldbalanceton',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'luasrkh' => 'decimal:2',
        'luashasil' => 'decimal:2',
        'luassisa' => 'decimal:2',
        'fieldbalancerit' => 'decimal:2',
        'fieldbalanceton' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
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