<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HerbisidaDosage extends Model
{
    protected $table = 'herbisidadosage';

    public $timestamps = false;

    protected $primaryKey = 'activitycode';
    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'activitycode',
        'itemcode',
        'time',
        'description',
        'totaldosage',
        'dosageunit',
    ];

    protected $casts = [
        'totaldosage' => 'decimal:2',
    ];
}