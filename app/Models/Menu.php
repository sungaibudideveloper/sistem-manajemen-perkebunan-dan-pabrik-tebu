<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';
    public $timestamps = false;

    protected $primaryKey = 'menuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'menuid',
        'slug',
        'name',
        'updateby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];
}
