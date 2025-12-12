<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Herbisida extends Model
{
    protected $table = 'herbisida';
    protected $primaryKey = 'itemcode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'itemcode',
        'itemname',
        'measure',
        'dosageperha',
        'companycode',
        'inputby',
        'updateby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'dosageperha' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];
}