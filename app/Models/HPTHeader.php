<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HPTHeader extends Model
{
    public $incrementing = false;
    protected $table = 'hpt_hdr';
    protected $primaryKey = ['no_sample', 'kd_comp', 'tgltanam'];
    protected $fillable = ['no_sample', 'kd_comp', 'kd_blok', 'kd_plot', 'kd_plotsample', 'varietas', 'tgltanam', 'tglamat', 'user_input'];


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
        return $this->hasMany(HPTList::class, 'no_sample', 'no_sample')->where(function ($query) {
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
    public function userComp()
    {
        return $this->belongsTo(Usercomp::class, 'kd_comp', 'kd_comp');
    }
}
