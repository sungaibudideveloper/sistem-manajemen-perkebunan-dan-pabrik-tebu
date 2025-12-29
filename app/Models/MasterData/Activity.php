<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    
    protected $table = 'activity';
    protected $primaryKey = 'activitycode';
    protected $keyType = 'string';
    
    protected $fillable = [
        'activitycode',
        'activitygroup',
        'activityname',
        'description',
        'jurnalno',
        'accno',
        'jumlahvar',
        'var1',
        'satuan1',
        'var2',
        'satuan2',
        'var3',
        'satuan3',
        'var4',
        'satuan4',
        'var5',
        'satuan5',
        'usingmaterial',
        'usingvehicle',
        'jenistenagakerja',
        'isblokactivity',
        'createdat',
        'inputby',
        'updatedat',
        'updatedby',
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'jumlahvar' => 'integer',
        'usingmaterial' => 'integer',
        'usingvehicle' => 'integer',
        'jenistenagakerja' => 'integer',
        'isblokactivity' => 'integer',
    ];

    public function group()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }

    public function jenistenagakerjaRelation()
    {
        return $this->belongsTo(JenisTenagaKerja::class, 'jenistenagakerja', 'idjenistenagakerja');
    }

    public function accounting()
    {
        return $this->hasOne(Accounting::class, 'activitycode', 'activitycode');
    }
}