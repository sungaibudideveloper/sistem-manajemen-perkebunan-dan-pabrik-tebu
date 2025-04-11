<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgronomiHeader extends Model
{
    public $incrementing = false;
    protected $table = 'agrohdr';
    protected $primaryKey = ['nosample', 'companycode', 'tanggaltanam'];
    protected $fillable = ['nosample', 'companycode', 'tanggaltanam', 'tanggalpengamatan', 'blok', 'plot', 'idblokplot', 'varietas', 'kat', 'inputby', 'createdat'];

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
        return $this->hasMany(AgronomiList::class, 'nosample', 'nosample')->where(function ($query) {
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

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'blok', 'blok');
    }

    public function plot()
    {
        return $this->belongsTo(Plot::class, 'plot', 'plot');
    }

    public function userComp()
    {
        return $this->belongsTo(Usercompany::class, 'companycode', 'companycode');
    }
}
