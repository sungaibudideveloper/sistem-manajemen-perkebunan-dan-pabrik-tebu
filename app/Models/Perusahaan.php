<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    public $incrementing = false;
    // public $timestamps = false;
    protected $table = 'perusahaan';
    protected $primaryKey = 'kd_comp';
    protected $keyType = 'char';
    protected $fillable = ['kd_comp', 'nama', 'alamat', 'tgl', 'user_input', 'created_at', 'updated_at'];

    public function setCreatedAt($value)
    {
        $this->attributes['created_at'] = $value;
    }

    // Getter untuk mendapatkan nilai created_at dari created_at
    public function getCreatedAtAttribute()
    {
        return $this->attributes['created_at'];
    }

    public function mapping()
    {
        return $this->hasMany(Mapping::class, 'kd_comp', 'kd_comp');
    }

    public function usercomp()
    {
        return $this->belongsTo(Usercomp::class, 'kd_comp', 'kd_comp');
    }
}
