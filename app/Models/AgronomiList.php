<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgronomiList extends Model
{
    public $incrementing = false;
    protected $table = 'agro_lst';
    protected $primaryKey = ['no_sample', 'kd_comp', 'tgltanam'];
    protected $fillable = [
        'no_sample',
        'kd_comp',
        'tgltanam',
        'no_urut',
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
        'user_input',
        'created_at'
    ];

    protected function setKeysForSaveQuery($query)
    {
        return $query->where('no_sample', $this->getAttribute('no_sample'))
            ->where('kd_comp', $this->getAttribute('kd_comp'))
            ->where('no_urut', $this->getAttribute('no_urut'));
    }

    public function header()
    {
        return $this->belongsTo(AgronomiHeader::class, 'no_sample', 'no_sample');
    }

    // public function header()
    // {
    //     return $this->belongsTo(AgronomiHeader::class)->where(function ($query) {
    //         $query->whereColumn('no_sample', 'no_sample')
    //             ->whereColumn('kd_comp', 'kd_comp')
    //             ->whereColumn('tgltanam', 'tgltanam');
    //     });
    // }

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

    public function mapping()
    {
        return $this->belongsTo(Mapping::class, 'kd_plotsample', 'kd_plotsample');
    }
    public function user()
    {
        return $this->belongsTo(Username::class);
    }
}
