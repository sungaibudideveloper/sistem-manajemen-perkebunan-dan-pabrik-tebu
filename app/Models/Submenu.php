<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submenu extends Model
{
    protected $table = 'submenu';
    public $timestamps = false;

    protected $primaryKey = 'submenuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'submenuid',
        'menuid',
        'parentid',
        'name',
        'slug',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'updatedat' => 'datetime',
    ];
}
