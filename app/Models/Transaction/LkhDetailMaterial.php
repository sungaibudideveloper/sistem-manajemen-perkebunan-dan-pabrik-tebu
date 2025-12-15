<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class LkhDetailMaterial extends Model
{
    protected $table = 'lkhdetailmaterial';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'lkhno',
        'lkhhdrid',
        'plot',
        'itemcode',
        'qtyditerima',
        'qtysisa',
        'qtydigunakan',
        'keterangan',
        'inputby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'qtyditerima' => 'decimal:3',
        'qtysisa' => 'decimal:3',
        'qtydigunakan' => 'decimal:3',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function lkhheader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhhdrid', 'id');
    }
}