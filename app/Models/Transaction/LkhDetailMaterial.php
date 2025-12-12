<?php
// =====================================================
// FILE: app/Models/Transaction/LkhDetailMaterial.php
// =====================================================
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

    // Relationships (FK menggunakan surrogate ID)
    public function lkhHeader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhhdrid', 'id');
    }
}
