<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    public $timestamps = false;

    protected $primaryKey = 'kodekategori';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kodekategori',
        'namakategori',
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
