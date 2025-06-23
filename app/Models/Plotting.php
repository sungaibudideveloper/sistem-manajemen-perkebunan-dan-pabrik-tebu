<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    public $incrementing = false;
    protected $table = 'plot';
    protected $primaryKey = 'plot';
    protected $keyType = 'char';
    protected $fillable = ['plot', 'luasarea', 'jaraktanam', 'companycode', 'inputby', 'createdat'];

    public function setCreatedAt($value)
    {
        $this->attributes['createdat'] = $value;
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['createdat'];
    }

    // protected function setKeysForSaveQuery($query)
    // {
    //     $query->where('plot', $this->getAttribute('plot'))
    //           ->where('companycode', $this->getAttribute('companycode'));

    //     return $query;
    // }

    // public function kode()
    // {
    //     return $this->belongsTo(company::class, 'companycode');
    // }
}
