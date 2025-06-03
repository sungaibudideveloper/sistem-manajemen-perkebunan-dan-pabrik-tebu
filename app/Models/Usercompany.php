<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserCompany extends Model
{
    protected $table = 'usercompany';

    protected $primaryKey = 'userid';
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'userid',
        'companycode',
        'inputby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }


}
