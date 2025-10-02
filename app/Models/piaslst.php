<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class piaslst extends Model
{
    protected $table = 'piaslst';
    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode', 'rkhno', 'lkhno', 'blok', 'plot', 'tj', 'tc','needtj','needtc'
    ];
    


}