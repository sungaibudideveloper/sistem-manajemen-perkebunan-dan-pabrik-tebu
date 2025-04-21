<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Herbisida extends Model
{
    protected $table = 'herbisida';

    protected $fillable = [
        'itemcode',
        'itemname',
        'measure',
        'dosageperha',
        'company_code',
        'timestamp',
    ];

    protected $casts = [
        'dosageperha' => 'decimal:2',
    ];
}
