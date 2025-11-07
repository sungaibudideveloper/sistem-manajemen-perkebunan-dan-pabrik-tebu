<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trash extends Model
{
    protected $table = 'trash';

    // Composite primary key
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = true;

    protected $fillable = [
        'suratjalanno',
        'companycode',
        'jenis',
        'pucuk',
        'daun_gulma',
        'sogolan',
        'siwilan',
        'tebumati',
        'tanah_etc',
        'total',
        'netto_trash'
        
    ];

    /**
     * Relasi ke User
     */
}
