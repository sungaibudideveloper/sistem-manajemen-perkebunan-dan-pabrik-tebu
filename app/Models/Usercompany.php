<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usercompany extends Model
{
    public $incrementing = false;
    protected $table = 'usercompany';
    // protected $primaryKey = ['usernm', 'companycode'];
    protected $keyType = 'char';
    protected $fillable = ['usernm', 'companycode', 'inputby', 'createdat', 'updatedat'];

    public function setCreatedAt($value)
    {
        $this->attributes['createdat'] = $value;
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['createdat'];
    }

    protected function setKeysForSaveQuery($query)
    {
        $query->where('usernm', $this->getAttribute('usernm'))
              ->where('companycode', $this->getAttribute('companycode'));

        return $query;
    }

    public function company()
    {
        return $this->belongsTo(company::class, 'companycode', 'companycode');
    }

}
