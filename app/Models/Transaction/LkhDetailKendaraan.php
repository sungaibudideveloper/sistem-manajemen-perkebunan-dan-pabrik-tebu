<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

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
        'helperid',
        'jammulai',
        'jamselesai',
        'hourmeterstart',
        'hourmeterend',
        'solar',
        'adminupdateby',
        'adminupdatedat',
        'ordernumber',
        'printedby',
        'printedat',
        'status',
        'gudangconfirm',
        'gudangconfirmedby',
        'gudangconfirmedat',
        'createdat',
    ];

    protected $casts = [
        'hourmeterstart' => 'decimal:2',
        'hourmeterend' => 'decimal:2',
        'solar' => 'decimal:3',
        'gudangconfirm' => 'boolean',
        'adminupdatedat' => 'datetime',
        'printedat' => 'datetime',
        'gudangconfirmedat' => 'datetime',
        'createdat' => 'datetime',
    ];

    public function lkhheader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhhdrid', 'id');
    }

    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'kendaraanid', 'id');
    }
}