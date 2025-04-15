<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgronomiList extends Model
{
    public $incrementing = false;
    protected $table = 'agrolst';
    protected $primaryKey = ['nosample', 'companycode', 'tanggaltanam'];
    protected $fillable = [
        'nosample',
        'companycode',
        'tanggaltanam',
        'nourut',
        'jumlahbatang',
        'pan_gap',
        'per_gap',
        'per_germinasi',
        'ph_tanah',
        'populasi',
        'ktk_gulma',
        'per_gulma',
        't_primer',
        't_sekunder',
        't_tersier',
        't_kuarter',
        'd_primer',
        'd_sekunder',
        'd_tersier',
        'd_kuarter',
        'inputby',
        'createdat',
        'updatedat'
    ];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('nosample', $this->getAttribute('nosample'))
            ->where('companycode', $this->getAttribute('companycode'))
            ->where('nourut', $this->getAttribute('nourut'));
    }

    public function setCreatedAt($value)
    {
        $this->attributes['createdat'] = $value;
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['createdat'];
    }

    public function header()
    {
        return $this->belongsTo(AgronomiHeader::class, 'nosample', 'nosample');
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
        return $this->belongsTo(Plot::class, 'plot', 'plot');
    }

    public function mapping()
    {
        return $this->belongsTo(Mapping::class, 'idblokplot', 'idblokplot');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
