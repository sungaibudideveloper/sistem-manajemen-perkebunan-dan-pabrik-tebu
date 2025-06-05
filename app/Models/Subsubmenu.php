<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subsubmenu extends Model
{
    protected $table = 'subsubmenu';
    public $timestamps = false;

    protected $primaryKey = 'subsubmenuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'subsubmenuid',
        'submenuid',
        'name',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'updatedat' => 'datetime',
    ];
}
