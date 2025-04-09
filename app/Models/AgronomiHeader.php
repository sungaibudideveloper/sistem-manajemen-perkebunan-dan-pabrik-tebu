<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgronomiHeader extends Model
{
    public $incrementing = false;
    protected $table = 'agro_hdr';
    protected $primaryKey = ['no_sample', 'companycode', 'tanggaltanam'];
    protected $fillable = ['no_sample', 'companycode', 'tanggaltanam', 'tglamat', 'blok', 'plotcode', 'plotcodesample', 'varietas', 'kat', 'inputby', 'createdat'];

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
        $query->where('no_sample', $this->getAttribute('no_sample'))
            ->where('companycode', $this->getAttribute('companycode'))
            ->where('tanggaltanam', $this->getAttribute('tanggaltanam'));

        return $query;
    }

    public function lists()
    {
        return $this->hasMany(AgronomiList::class, 'no_sample', 'no_sample')->where(function ($query) {
            $query->whereColumn('companycode', 'companycode')
                ->whereColumn('tanggaltanam', 'tanggaltanam');
        });
    }


    public function mapping()
    {
        return $this->belongsTo(Mapping::class, 'plotcodesample', 'plotcodesample');
    }

    public function company()
    {
        return $this->belongsTo(company::class, 'companycode', 'companycode');
    }

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'blok', 'blok');
    }

    public function plot()
    {
        return $this->belongsTo(Plotting::class, 'plotcode', 'plotcode');
    }

    public function userComp()
    {
        return $this->belongsTo(Usercomp::class, 'companycode', 'companycode');
    }
}
