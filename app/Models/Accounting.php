<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accounting extends Model
{
    protected $table = 'accounting';
    public $timestamps = false;

    protected $primaryKey = 'activitycode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'activitycode',
        'jurnalaccno',
        'jurnalacctype',
        'description',
        'inputby',
        'updateby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];
}
