<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class piashdr extends Model
{
    protected $table = 'piashdr';
    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode', 'rkhno', 'generatedate', 'tj', 'tc', 'inputby' , 'updateby', 'tjstatus', 'tcstatus'
    ];
    


}