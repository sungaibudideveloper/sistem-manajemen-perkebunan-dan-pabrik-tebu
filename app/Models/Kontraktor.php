<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kontraktor extends Model
{
    protected $table = 'kontraktor';
    public $timestamps = false;
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'companycode',
        'namakontraktor',
        'isactive',
        'inputby',
        'updateby',
        'createdat',
        'updatedat',
    ];
}