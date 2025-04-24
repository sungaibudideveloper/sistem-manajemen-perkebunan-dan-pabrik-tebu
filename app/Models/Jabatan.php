<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatan';

    public $timestamps = false;

    protected $primaryKey = 'idjabatan';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'idjabatan',
        'namajabatan'
    ];

    protected $casts = [
        'idjabatan' => 'integer',
    ];
}