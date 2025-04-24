<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatan';

    public $timestamps = false;

    protected $primaryKey = 'idjabatan';

    protected $fillable = [
        'namajabatan'
    ];

    protected $casts = [
        'idjabatan' => 'integer',
    ];
}