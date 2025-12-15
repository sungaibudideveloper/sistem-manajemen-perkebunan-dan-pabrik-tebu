<?php

namespace App\Models\MasterData;;

use Illuminate\Database\Eloquent\Model;

class Blok extends Model
{
    public $incrementing = false;
    protected $table = 'blok';
    protected $primaryKey = ['blok', 'companycode'];
    protected $keyType = 'char';
    protected $fillable = ['blok', 'nama', 'companycode', 'inputby', 'createdat'];

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
        $query->where('blok', $this->getAttribute('blok'))
              ->where('companycode', $this->getAttribute('companycode'));

        return $query;
    }

    // public function kode()
    // {
    //     return $this->belongsTo(company::class, 'companycode', 'companycode');
    // }
}
