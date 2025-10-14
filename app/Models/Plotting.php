<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    protected $table = 'plot';
    protected $primaryKey = ['plot', 'companycode'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'plot', 
        'blok',
        'luasarea', 
        'jaraktanam', 
        'status',
        'companycode', 
        'inputby', 
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'luasarea' => 'decimal:2',
        'jaraktanam' => 'integer',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Accessor untuk format luas area
    public function getFormattedLuasAreaAttribute()
    {
        return number_format($this->luasarea, 2) . ' Ha';
    }

    // Accessor untuk status description
    public function getStatusDescriptionAttribute()
    {
        return match($this->status) {
            'KTG' => 'Kategang',
            'RPL' => 'Replanting', 
            'KBD' => 'Kebun Dewasa',
            default => $this->status
        };
    }
}