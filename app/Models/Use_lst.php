<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Use_lst extends Model
{
    protected $table = 'Use_lst';
    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode', 'rkhno', 'itemcode', 'qty', 'qtyretur', 'unit'
    ];
    
    


}