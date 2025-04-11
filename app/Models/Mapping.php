<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapping extends Model
{
    public $incrementing = false;
    protected $table = 'mappingblokplot';
    protected $primaryKey = ['idblokplot', 'blok', 'plot', 'companycode'];
    protected $keyType = 'char';
    protected $fillable = ['idblokplot', 'blok', 'plot', 'companycode', 'inputby', 'createdat'];

    public function setCreatedAt($value)
    {
        $this->attributes['createdat'] = $value;
    }

    // Getter untuk mendapatkan nilai createdat dari createdat
    public function getCreatedAtAttribute()
    {
        return $this->attributes['createdat'];
    }

    protected function setKeysForSaveQuery($query)
    {
        $query->where('idblokplot', $this->getAttribute('idblokplot'))
              ->where('blok', $this->getAttribute('blok'))
              ->where('plot', $this->getAttribute('plot'))
              ->where('companycode', $this->getAttribute('companycode'));

        return $query;
    }

    public function company()
    {
        return $this->belongsTo(company::class, 'companycode', 'companycode');
    }

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'blok', 'blok');
    }

    public function plot()
    {
        return $this->belongsTo(Plotting::class, 'plot', 'plot');
    }

}
