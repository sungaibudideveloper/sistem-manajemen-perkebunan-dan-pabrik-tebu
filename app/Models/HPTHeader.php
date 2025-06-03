<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HPTHeader extends Model
{
    public $incrementing = false;
    protected $table = 'hpthdr';
    protected $primaryKey = ['nosample', 'companycode', 'tanggaltanam'];
    protected $fillable = ['nosample', 'companycode', 'blok', 'plot', 'idblokplot', 'varietas', 'tanggaltanam', 'tanggalpengamatan', 'inputby','createdat','updatedat'];


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
        $query->where('nosample', $this->getAttribute('nosample'))
            ->where('companycode', $this->getAttribute('companycode'))
            ->where('tanggaltanam', $this->getAttribute('tanggaltanam'));

        return $query;
    }

    public function lists()
    {
        return $this->hasMany(HPTList::class, 'nosample', 'nosample')->where(function ($query) {
            $query->whereColumn('companycode', 'companycode')
                ->whereColumn('tanggaltanam', 'tanggaltanam');
        });
    }

    public function mapping()
    {
        return $this->belongsTo(Mapping::class, 'idblokplot', 'idblokplot');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }
    public function userComp()
    {
        return $this->belongsTo(UserCompany::class, 'companycode', 'companycode');
    }
}
