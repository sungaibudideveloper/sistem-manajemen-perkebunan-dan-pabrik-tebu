<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mandor extends Model
{
    protected $table = 'mandor';

    public $timestamps = false;

    // Gunakan id sebagai primary key asli, tapi kita tetap menggunakan companycode+id sebagai logical composite key
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'id',
        'name',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function getCreatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    public function getUpdatedAtAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}
