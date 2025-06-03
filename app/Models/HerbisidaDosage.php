<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HerbisidaDosage extends Model
{
    protected $table = 'herbisidadosage';

    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'herbisidagroupid',
        'itemcode',
        'dosageperha',
        'dosageunit',
        'inputby',
        'updateby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'dosageperha' => 'decimal:2',
        'createdat'   => 'datetime',
        'updatedat'   => 'datetime',
    ];

}