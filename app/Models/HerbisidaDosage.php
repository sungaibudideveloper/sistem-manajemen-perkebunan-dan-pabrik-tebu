<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HerbisidaDosage extends Model
{
    protected $table = 'herbisida_dosage';

    public $timestamps = false;

    protected $fillable = [
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