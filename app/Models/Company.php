<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class company extends Model
{
    public $incrementing = false;
    // public $timestamps = false;
    protected $table = 'company';
    protected $primaryKey = 'companycode';
    protected $keyType = 'char';
    protected $fillable = ['companycode', 'nama', 'alamat', 'companyperiod', 'inputby', 'createdat', 'updatedat'];

    public function setCreatedAt($value)
    {
        $this->attributes['createdat'] = $value;
    }

    // Getter untuk mendapatkan nilai createdat dari createdat
    public function getCreatedAtAttribute()
    {
        return $this->attributes['createdat'];
    }

    public function mapping()
    {
        return $this->hasMany(Mapping::class, 'companycode', 'companycode');
    }

    public function usercomp()
    {
        return $this->belongsTo(Usercomp::class, 'companycode', 'companycode');
    }
}
