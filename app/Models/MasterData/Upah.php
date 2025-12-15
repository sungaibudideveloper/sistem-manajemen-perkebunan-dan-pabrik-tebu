<?php

namespace App\Models\MasterData;

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


    // Scopes
    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    public function scopeByActivityGroup($query, $activitygroup)
    {
        return $query->where('activitygroup', $activitygroup);
    }

    public function scopeByWageType($query, $wagetype)
    {
        return $query->where('wagetype', $wagetype);
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('effectivedate', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('enddate')
                          ->orWhere('enddate', '>=', $date);
                    });
    }

    // Static methods
    public static function getWageRates($companycode, $activitygroup, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        
        $rates = self::byCompany($companycode)
                    ->byActivityGroup($activitygroup)
                    ->effectiveOn($date)
                    ->get();
        
        return $rates->pluck('amount', 'wagetype')->toArray();
    }

    public static function getCurrentRate($companycode, $activitygroup, $wagetype, $date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        
        return self::byCompany($companycode)
                  ->byActivityGroup($activitygroup)
                  ->byWageType($wagetype)
                  ->effectiveOn($date)
                  ->orderBy('effectivedate', 'desc')
                  ->value('amount') ?? 0;
    }

    public static function createWageRate($companycode, $activitygroup, $wagetype, $amount, $effectivedate, $parameter = null)
    {
        // End previous rate if exists
        self::byCompany($companycode)
            ->byActivityGroup($activitygroup)
            ->byWageType($wagetype)
            ->whereNull('enddate')
            ->update(['enddate' => Carbon::parse($effectivedate)->subDay()]);
        
        // Create new rate
        return self::create([
            'companycode' => $companycode,
            'activitygroup' => $activitygroup,
            'wagetype' => $wagetype,
            'amount' => $amount,
            'effectivedate' => $effectivedate,
            'parameter' => $parameter,
            'inputby' => auth()->user()->userid ?? 'SYSTEM',
            'createdat' => now(),
        ]);
    }

    // Helper methods
    public function isActive($date = null)
    {
        $date = $date ?? now()->format('Y-m-d');
        
        return $this->effectivedate <= $date && 
               (is_null($this->enddate) || $this->enddate >= $date);
    }

    public function getFormattedAmount()
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