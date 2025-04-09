<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgronomiList extends Model
{
    public $incrementing = false;
    protected $table = 'agro_lst';
    protected $primaryKey = ['no_sample', 'companycode', 'tanggaltanam'];
    protected $fillable = [
        'no_sample',
        'companycode',
        'tanggaltanam',
        'nourut',
        'jm_batang',
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
        'createdat'
    ];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('no_sample', $this->getAttribute('no_sample'))
            ->where('companycode', $this->getAttribute('companycode'))
            ->where('nourut', $this->getAttribute('nourut'));
    }

    public function header()
    {
        return $this->belongsTo(AgronomiHeader::class, 'no_sample', 'no_sample');
    }

    // public function header()
    // {
    //     return $this->belongsTo(AgronomiHeader::class)->where(function ($query) {
    //         $query->whereColumn('no_sample', 'no_sample')
    //             ->whereColumn('companycode', 'companycode')
    //             ->whereColumn('tanggaltanam', 'tanggaltanam');
    //     });
    // }

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

    public function mapping()
    {
        return $this->belongsTo(Mapping::class, 'plotcodesample', 'plotcodesample');
    }
    public function user()
    {
        return $this->belongsTo(Username::class);
    }
}
