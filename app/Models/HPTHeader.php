<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HPTHeader extends Model
{
    public $incrementing = false;
    protected $table = 'hpt_hdr';
    protected $primaryKey = ['no_sample', 'companycode', 'tanggaltanam'];
    protected $fillable = ['no_sample', 'companycode', 'blok', 'plot', 'idblokplot', 'varietas', 'tanggaltanam', 'tglamat', 'inputby'];


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
        return $this->hasMany(HPTList::class, 'no_sample', 'no_sample')->where(function ($query) {
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
        return $this->belongsTo(company::class, 'companycode', 'companycode');
    }
    public function userComp()
    {
        return $this->belongsTo(Usercomp::class, 'companycode', 'companycode');
    }
}
