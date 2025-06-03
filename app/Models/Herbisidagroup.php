<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Herbisidagroup extends Model
{
    protected $table = 'herbisidagroup';
    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'herbisidagroupid',
        'herbisidagroupname',
        'activitycode',
        'description',
    ];

    


}