<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actifity extends Model
{
    public $incrementing = false;
    protected $table = 'actifity';
    protected $primaryKey = ['actifitycode'];
    protected $fillable = [
        'actifitycode',
        'actifitygroup',
        'tanggaltanam',
        'description',
        'jurnalno',
        'jumlahvar',
        'var1',
        'satuan1',
        'var2',
        'satuan1',
        'var3',
        'satuan1',
        'var4',
        'satuan1',
        'var5',
        'satuan5',
        'createdat',
        'inputby',
        'updatedat',
        'updatedby',
        'accno'
    ];

    public function setCreatedAt($value)
    {
        $this->attributes['createdat'] = $value;
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['createdat'];
    }

    public function group()
    {
        return $this->belongsTo(ActifityGroup::class, 'actifitygroup', 'actifitygroup');
    }

}
