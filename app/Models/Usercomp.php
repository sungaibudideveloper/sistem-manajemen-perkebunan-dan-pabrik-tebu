<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usercomp extends Model
{
    public $incrementing = false;
    protected $table = 'usercomp';
    // protected $primaryKey = ['usernm', 'kd_comp'];
    protected $keyType = 'char';
    protected $fillable = ['usernm', 'kd_comp', 'user_input', 'created_at', 'updated_at'];

    public function setCreatedAt($value)
    {
        $this->attributes['created_at'] = $value;
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['created_at'];
    }

    protected function setKeysForSaveQuery($query)
    {
        $query->where('usernm', $this->getAttribute('usernm'))
              ->where('kd_comp', $this->getAttribute('kd_comp'));

        return $query;
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'kd_comp', 'kd_comp');
    }

}
