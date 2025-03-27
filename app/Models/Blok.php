<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blok extends Model
{
    public $incrementing = false;
    protected $table = 'blok';
    protected $primaryKey = ['kd_blok', 'kd_comp'];
    protected $keyType = 'char';
    protected $fillable = ['kd_blok', 'nama', 'kd_comp', 'usernm', 'created_at'];

    public function setCreatedAt($value)
    {
        $this->attributes['created_at'] = $value;
    }

    // Getter untuk mendapatkan nilai created_at dari created_at
    public function getCreatedAtAttribute()
    {
        return $this->attributes['created_at'];
    }

    protected function setKeysForSaveQuery($query)
    {
        $query->where('kd_blok', $this->getAttribute('kd_blok'))
              ->where('kd_comp', $this->getAttribute('kd_comp'));

        return $query;
    }

    // public function kode()
    // {
    //     return $this->belongsTo(Perusahaan::class, 'kd_comp', 'kd_comp');
    // }
}
