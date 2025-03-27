<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plotting extends Model
{
    public $incrementing = false;
    protected $table = 'plotting';
    protected $primaryKey = ['kd_plot', 'kd_comp'];
    protected $keyType = 'char';
    protected $fillable = ['kd_plot', 'nama', 'luas_area', 'jarak_tanam', 'kd_comp', 'usernm', 'created_at'];

    public function setCreatedAt($value)
    {
        $this->attributes['created_at'] = $value;
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['created_at'];
    }

    // protected function setKeysForSaveQuery($query)
    // {
    //     $query->where('kd_plot', $this->getAttribute('kd_plot'))
    //           ->where('kd_comp', $this->getAttribute('kd_comp'));

    //     return $query;
    // }

    // public function kode()
    // {
    //     return $this->belongsTo(Perusahaan::class, 'kd_comp');
    // }
}
