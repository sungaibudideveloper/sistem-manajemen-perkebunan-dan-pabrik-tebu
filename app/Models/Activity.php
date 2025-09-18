<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public $incrementing = false;
    protected $table = 'activity';
    protected $primaryKey = ['activitycode'];
    protected $fillable = [
        'activitycode',
        'activitygroup',
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
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }

    public function jenistenagakerja()
    {
        return $this->belongsTo(JenisTenagaKerja::class, 'jenistenagakerja', 'idjenistenagakerja');
    }

    public function accounting()
    {
        return $this->hasOne(Accounting::class, 'activitycode', 'activitycode');
    }

}
