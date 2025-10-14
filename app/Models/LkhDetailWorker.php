<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * LkhDetailWorker Model
 * Handles worker assignments and wage calculations for LKH
 */
class LkhDetailWorker extends Model
{
    protected $table = 'lkhdetailworker';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false; // Using custom createdat/updatedat

    protected $fillable = [
        'companycode',
        'lkhno',
        'tenagakerjaid',
        'tenagakerjaurutan',
        'jammasuk',
        'jamselesai',
        'totaljamkerja',
        'overtimehours',
        'premi',
        'upahharian',
        'upahperjam',
        'upahlembur',
        'upahborongan',
        'totalupah',
        'keterangan',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'tenagakerjaurutan' => 'integer',
        'totaljamkerja' => 'decimal:2',
        'overtimehours' => 'decimal:2',
        'premi' => 'decimal:2',
        'upahharian' => 'decimal:2',
        'upahperjam' => 'decimal:2',
        'upahlembur' => 'decimal:2',
        'upahborongan' => 'decimal:2',
        'totalupah' => 'decimal:2',
        'jammasuk' => 'datetime:H:i:s',
        'jamselesai' => 'datetime:H:i:s',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Relations
    public function lkhheader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhno', 'lkhno');
    }

    public function tenagakerja()
    {
        return $this->belongsTo(TenagaKerja::class, 'tenagakerjaid', 'tenagakerjaid');
    }

    // Scopes
    public function scopeByLkh($query, $lkhno)
    {
        return $query->where('lkhno', $lkhno);
    }

    public function scopeByWorker($query, $tenagakerjaid)
    {
        return $query->where('tenagakerjaid', $tenagakerjaid);
    }

    public function scopeOrderBySequence($query)
    {
        return $query->orderBy('tenagakerjaurutan');
    }

    // Helper methods
    public function calculateTotalJamKerja()
    {
        if (!$this->jammasuk || !$this->jamselesai) {
            return 0;
        }
        
        $start = Carbon::createFromFormat('H:i:s', $this->jammasuk);
        $end = Carbon::createFromFormat('H:i:s', $this->jamselesai);
        
        // Handle case where work crosses midnight
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        return $start->diffInHours($end, true);
    }

    public function getFormattedJamKerja()
    {
        $hours = $this->calculateTotalJamKerja();
        $wholeHours = floor($hours);
        $minutes = ($hours - $wholeHours) * 60;
        
        return sprintf('%02d:%02d', $wholeHours, $minutes);
    }

    public function calculateTotalUpah($jenistenagakerja, $activitygroup, $companycode, $workDate)
    {
        if ($jenistenagakerja == 1) {
            // Harian calculation
            return $this->calculateHarianWage($activitygroup, $companycode, $workDate);
        } else {
            // Borongan calculation  
            return $this->calculateBoronganWage($activitygroup, $companycode, $workDate);
        }
    }

    private function calculateHarianWage($activitygroup, $companycode, $workDate)
    {
        $dayType = $this->getDayType($workDate);
        $wageRates = Upah::getWageRates($companycode, $activitygroup, $workDate);
        
        $totalHours = $this->totaljamkerja;
        $overtimeHours = $this->overtimehours;
        
        // Check if full day (8 hours) or hourly
        if ($totalHours >= 8) {
            $baseWage = $wageRates[$dayType] ?? $wageRates['DAILY'] ?? 0;
        } else {
            $hourlyRate = $wageRates['HOURLY'] ?? 0;
            $baseWage = $hourlyRate * $totalHours;
        }
        
        // Add overtime
        $overtimeRate = $wageRates['OVERTIME'] ?? 0;
        $overtimeWage = $overtimeHours * $overtimeRate;
        
        return $baseWage + $overtimeWage + $this->premi;
    }

    private function calculateBoronganWage($activitygroup, $companycode, $workDate)
    {
        // For borongan, wage is calculated based on area completed
        // This will be calculated from plot data
        return $this->upahborongan;
    }

    private function getDayType($workDate)
    {
        $date = Carbon::parse($workDate);
        
        if ($date->isSaturday()) {
            return 'WEEKEND_SATURDAY';
        } elseif ($date->isSunday()) {
            return 'WEEKEND_SUNDAY';
        }
        
        return 'DAILY';
    }
}