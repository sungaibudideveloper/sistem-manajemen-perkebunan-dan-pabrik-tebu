<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AgronomiHeader extends Model
{
    public $incrementing = false;
    protected $table = 'agro_hdr';
    protected $primaryKey = ['no_sample', 'kd_comp', 'tgltanam'];
    protected $fillable = ['no_sample', 'kd_comp', 'tgltanam', 'tglamat', 'kd_blok', 'kd_plot', 'kd_plotsample', 'varietas', 'kat', 'user_input', 'created_at'];

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
        $query->where('no_sample', $this->getAttribute('no_sample'))
            ->where('kd_comp', $this->getAttribute('kd_comp'))
            ->where('tgltanam', $this->getAttribute('tgltanam'));

        return $query;
    }

    public function lists()
    {
        return $this->hasMany(AgronomiList::class, 'no_sample', 'no_sample')->where(function ($query) {
            $query->whereColumn('kd_comp', 'kd_comp')
                ->whereColumn('tgltanam', 'tgltanam');
        });
    }


    public function mapping()
    {
        return $this->belongsTo(Mapping::class, 'kd_plotsample', 'kd_plotsample');
    }

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

    public function userComp()
    {
        return $this->belongsTo(Usercomp::class, 'kd_comp', 'kd_comp');
    }
}
