<?php

namespace App\Models;

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
        'timestamp',
    ];

    protected $casts = [
        'dosageperha' => 'decimal:2',
    ];
}