<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapping extends Model
{
    public $incrementing = false;
    protected $table = 'mapping';
    protected $primaryKey = ['kd_plotsample', 'kd_blok', 'kd_plot', 'kd_comp'];
    protected $keyType = 'char';
    protected $fillable = ['kd_plotsample', 'kd_blok', 'kd_plot', 'kd_comp', 'usernm', 'created_at'];

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
        $query->where('kd_plotsample', $this->getAttribute('kd_plotsample'))
              ->where('kd_blok', $this->getAttribute('kd_blok'))
              ->where('kd_plot', $this->getAttribute('kd_plot'))
              ->where('kd_comp', $this->getAttribute('kd_comp'));

        return $query;
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'kd_comp', 'kd_comp');
    }

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'kd_blok', 'kd_blok');
    }

    public function plot()
    {
        return $this->belongsTo(Plotting::class, 'kd_plot', 'kd_plot');
    }
   
}
