<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Varietas extends Model
{
    protected $table = 'varietas';
    public $timestamps = false;

    protected $primaryKey = 'kodevarietas';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kodevarietas',
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
