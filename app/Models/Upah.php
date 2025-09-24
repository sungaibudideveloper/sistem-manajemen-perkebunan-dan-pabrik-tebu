<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Upah extends Model
{
    protected $table = 'upah';
    protected $primaryKey = 'id';
    public $timestamps = false; // Karena menggunakan createdat/updatedat custom

    protected $fillable = [
        'companycode',
        'activitygroup', 
        'wagetype',
        'amount',
        'effectivedate',
        'enddate',
        'parameter',
        'inputby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effectivedate' => 'date',
        'enddate' => 'date',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Accessor untuk format rupiah
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    // Accessor untuk wage type description
    public function getWageTypeDescriptionAttribute()
    {
        $descriptions = [
            'DAILY' => 'Harian',
            'HOURLY' => 'Per Jam',
            'OVERTIME' => 'Lembur', 
            'WEEKEND_SATURDAY' => 'Weekend Sabtu',
            'WEEKEND_SUNDAY' => 'Weekend Minggu',
            'PER_HECTARE' => 'Per Hektar',
            'PER_KG' => 'Per Kilogram'
        ];

        return $descriptions[$this->wagetype] ?? $this->wagetype;
    }
}