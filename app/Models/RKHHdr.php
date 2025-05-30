<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Rkhhdr extends Model
{
    protected $table = 'rkhhdr';

    protected $primaryKey = 'rkhno';
    public $incrementing = false; // karena primary key bukan auto increment
    public $timestamps = false; // karena pakai createdat & updatedat custom

    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhdate',
        'manpower',
        'totalluas',
        'mandorid',
        'approval',
        'status',
        'inputby',
        'createdat',
        'updateby',
        'updatedat'
    ];

    protected $casts = [
        'rkhdate'   => 'date',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'manpower'  => 'integer',
        'totalluas' => 'float',
    ];

    // Gunakan Carbon instance
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

}
