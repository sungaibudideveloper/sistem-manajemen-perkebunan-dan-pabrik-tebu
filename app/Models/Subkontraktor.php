<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subkontraktor extends Model
{
    protected $table = 'subkontraktor';
    public $timestamps = false;
    public $incrementing = false;
    
    protected $fillable = [
        'id',
        'companycode',
        'kontraktorid',
        'namasubkontraktor',
        'isactive',
        'inputby',
        'updateby',
        'createdat',
        'updatedat',
    ];
}