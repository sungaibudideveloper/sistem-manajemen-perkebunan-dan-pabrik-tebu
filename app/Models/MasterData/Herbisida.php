<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Herbisida extends Model
{
    protected $table = 'herbisida';

    public $timestamps = false;

    protected $primaryKey = 'itemcode';
    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

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