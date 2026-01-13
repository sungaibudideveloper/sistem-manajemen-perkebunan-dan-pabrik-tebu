<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * WageCalculationService (FIXED VERSION)
 * 
 * Handles all wage calculations for different scenarios:
 * - Harian (daily wages with weekday/weekend/overtime rates) → reads from `upah` table
 * - Borongan (piece rate based on hectares or weight) → reads from `upahborongan` table
 * - Special conditions (sick leave, early departure, etc.)
 */
class WageCalculationService
{
    /**
     * Calculate wage for a single worker in LKH
     *
     * @param string $companycode
     * @param string $activitycode
     * @param int $jenistenagakerja (1=Harian, 2=Borongan, 3=Operator)
     * @param string $workDate
     * @param array $workerData
     * @param array $plotData (for borongan calculation)
     * @return array
     */
    public function calculateWorkerWage($companycode, $activitycode, $jenistenagakerja, $workDate, $workerData, $plotData = [])
    {
        try {
            // Get activity group from activity code
            $activitygroup = $this->getActivityGroup($activitycode);
            
            if (!$activitygroup) {
                throw new \Exception("Activity group not found for activity: {$activitycode}");
            }

            // Calculate based on jenis tenaga kerja
            if ($jenistenagakerja == 1 || $jenistenagakerja == 3) {
                // Harian or Operator (same calculation)
                return $this->calculateHarianWage($companycode, $activitygroup, $workDate, $workerData);
            } elseif ($jenistenagakerja == 2) {
                // Borongan
                return $this->calculateBoronganWage($companycode, $activitycode, $workDate, $workerData, $plotData);
            }

            throw new \Exception("Invalid jenistenagakerja: {$jenistenagakerja}");

        } catch (\Exception $e) {
            Log::error("Wage calculation error", [
                'companycode' => $companycode,
                'activitycode' => $activitycode,
                'jenistenagakerja' => $jenistenagakerja,
                'error' => $e->getMessage()
            ]);

            return $this->getErrorWageResult($e->getMessage());
        }
    }

    /**
     * Calculate Harian wage (daily/hourly with overtime)
     * ✅ READS FROM: `upah` table (by activitygroup + wagetype)
     *
     * @param string $companycode
     * @param string $activitygroup
     * @param string $workDate
     * @param array $workerData
     * @return array
     */
    private function calculateHarianWage($companycode, $activitygroup, $workDate, $workerData)
    {
        $dayType = $this->getDayType($workDate);
        $wageRates = $this->getHarianWageRates($companycode, $activitygroup, $workDate);
        
        $totalHours = $workerData['totaljamkerja'] ?? 0;
        $overtimeHours = $workerData['overtimehours'] ?? 0;
        $premi = $workerData['premi'] ?? 0;
        
        // Determine base wage calculation method
        if ($this->isFullDay($totalHours, $dayType)) {
            // Use full day rate
            $baseWage = $this->getFullDayRate($wageRates, $dayType);
            $calculationMethod = 'full_day';
        } else {
            // Use hourly rate
            $hourlyRate = $wageRates['HOURLY'] ?? 0;
            $baseWage = $hourlyRate * $totalHours;
            $calculationMethod = 'hourly';
        }
        
        // Calculate overtime
        $overtimeRate = $wageRates['OVERTIME'] ?? 0;
        $overtimeWage = $overtimeHours * $overtimeRate;
        
        // Total calculation
        $totalUpah = $baseWage + $overtimeWage + $premi;
        
        return [
            'success' => true,
            'calculation_method' => $calculationMethod,
            'day_type' => $dayType,
            'total_hours' => $totalHours,
            'overtime_hours' => $overtimeHours,
            'upahharian' => $baseWage,
            'upahperjam' => $calculationMethod === 'hourly' ? ($wageRates['HOURLY'] ?? 0) : 0,
            'upahlembur' => $overtimeWage,
            'premi' => $premi,
            'upahborongan' => 0,
            'totalupah' => $totalUpah,
            'rates_used' => $wageRates,
            'notes' => $this->generateHarianNotes($calculationMethod, $dayType, $totalHours, $overtimeHours)
        ];
    }

    /**
     * Calculate Borongan wage (piece rate based on area or weight)
     * ✅ READS FROM: `upahborongan` table (by activitycode)
     *
     * @param string $companycode
     * @param string $activitycode
     * @param string $workDate
     * @param array $workerData
     * @param array $plotData
     * @return array
     */
    private function calculateBoronganWage($companycode, $activitycode, $workDate, $workerData, $plotData)
    {
        $premi = $workerData['premi'] ?? 0;
        
        // Get borongan rate from upahborongan table
        $boronganRate = $this->getBoronganRate($companycode, $activitycode, $workDate);
        
        if ($boronganRate === 0) {
            Log::warning("No borongan rate found for activity", [
                'companycode' => $companycode,
                'activitycode' => $activitycode,
                'workDate' => $workDate
            ]);
        }
        
        // Determine calculation type based on activity group
        $activitygroup = $this->getActivityGroup($activitycode);
        
        if ($activitygroup === 'IV') {
            // Panen - calculate by weight (kg)
            return $this->calculatePanenWage($companycode, $activitycode, $workDate, $plotData, $premi);
        } else {
            // Regular borongan - calculate by area (hectare)
            return $this->calculatePerHectareWage($boronganRate, $plotData, $premi, $activitycode);
        }
    }

    /**
     * Calculate Panen wage (harvest + transport)
     * UPDATED: Use new column names (luashasil for weight in panen)
     * ✅ READS FROM: `upahborongan` table
     *
     * @param string $companycode
     * @param string $activitycode
     * @param string $workDate
     * @param array $plotData
     * @param float $premi
     * @return array
     */
    private function calculatePanenWage($companycode, $activitycode, $workDate, $plotData, $premi)
    {
        // Get harvest & transport rates from upahborongan table
        // Assuming separate activity codes for harvest and transport
        $harvestRate = $this->getBoronganRate($companycode, $activitycode, $workDate);
        
        // Total weight from plot data - use luashasil for weight in panen
        $totalWeight = collect($plotData)->sum('luashasil');
        
        // Calculate wages (simplified - using single rate)
        $harvestWage = $totalWeight * $harvestRate;
        $transportWage = 0; // If needed, get from separate activity code
        $totalBorongan = $harvestWage + $transportWage;
        $totalUpah = $totalBorongan + $premi;
        
        return [
            'success' => true,
            'calculation_method' => 'panen_per_kg',
            'total_weight' => $totalWeight,
            'harvest_rate' => $harvestRate,
            'transport_rate' => 0,
            'harvest_wage' => $harvestWage,
            'transport_wage' => $transportWage,
            'upahharian' => 0,
            'upahperjam' => 0,
            'upahlembur' => 0,
            'premi' => $premi,
            'upahborongan' => $totalBorongan,
            'totalupah' => $totalUpah,
            'notes' => "Panen: {$totalWeight} kg × Rp " . number_format($harvestRate, 0, ',', '.') . " = Rp " . number_format($totalBorongan, 0, ',', '.')
        ];
    }

    /**
     * Calculate per hectare wage
     * UPDATED: Use new column names (luashasil instead of luasactual)
     * ✅ READS FROM: `upahborongan` table
     *
     * @param float $perHectareRate
     * @param array $plotData
     * @param float $premi
     * @param string $activitycode
     * @return array
     */
    private function calculatePerHectareWage($perHectareRate, $plotData, $premi, $activitycode)
    {
        // Total area from plot data - use luashasil
        $totalArea = collect($plotData)->sum('luashasil');
        
        // Calculate wage
        $boronganWage = $totalArea * $perHectareRate;
        $totalUpah = $boronganWage + $premi;
        
        return [
            'success' => true,
            'calculation_method' => 'per_hectare',
            'activity_code' => $activitycode,
            'total_area' => $totalArea,
            'per_hectare_rate' => $perHectareRate,
            'upahharian' => 0,
            'upahperjam' => 0,
            'upahlembur' => 0,
            'premi' => $premi,
            'upahborongan' => $boronganWage,
            'totalupah' => $totalUpah,
            'notes' => "Borongan [{$activitycode}]: {$totalArea} ha × Rp " . number_format($perHectareRate, 0, ',', '.') . " = Rp " . number_format($boronganWage, 0, ',', '.')
        ];
    }

    /**
     * Get Harian wage rates from `upah` table
     * ✅ Reads effectivedate & enddate to determine active wage
     *
     * @param string $companycode
     * @param string $activitygroup
     * @param string $workDate
     * @return array
     */
    private function getHarianWageRates($companycode, $activitygroup, $workDate)
    {
        $workDate = Carbon::parse($workDate)->format('Y-m-d');
        
        $rates = DB::table('upah')
            ->where('companycode', $companycode)
            ->where('activitygroup', $activitygroup)
            ->where('effectivedate', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', $workDate);
            })
            ->orderBy('effectivedate', 'DESC')
            ->get();
        
        $wageRates = [];
        
        foreach ($rates as $rate) {
            if (!isset($wageRates[$rate->wagetype])) {
                $wageRates[$rate->wagetype] = $rate->amount;
            }
        }
        
        return $wageRates;
    }

    /**
     * Get Borongan rate from `upahborongan` table
     * ✅ Reads effectivedate & enddate to determine active wage
     *
     * @param string $companycode
     * @param string $activitycode
     * @param string $workDate
     * @return float
     */
    private function getBoronganRate($companycode, $activitycode, $workDate)
    {
        $workDate = Carbon::parse($workDate)->format('Y-m-d');
        
        $rate = DB::table('upahborongan')
            ->where('companycode', $companycode)
            ->where('activitycode', $activitycode)
            ->where('effectivedate', '<=', $workDate)
            ->where(function ($q) use ($workDate) {
                $q->whereNull('enddate')
                    ->orWhere('enddate', '>=', $workDate);
            })
            ->orderBy('effectivedate', 'DESC')
            ->value('amount');
        
        return $rate ?? 0;
    }

    /**
     * Bulk calculate wages for multiple workers
     *
     * @param string $companycode
     * @param string $activitycode
     * @param int $jenistenagakerja
     * @param string $workDate
     * @param array $workersData
     * @param array $plotsData
     * @return array
     */
    public function calculateBulkWages($companycode, $activitycode, $jenistenagakerja, $workDate, $workersData, $plotsData = [])
    {
        $results = [];
        $totalWages = 0;
        $successCount = 0;
        $errorCount = 0;

        foreach ($workersData as $workerData) {
            $result = $this->calculateWorkerWage(
                $companycode, 
                $activitycode, 
                $jenistenagakerja, 
                $workDate, 
                $workerData, 
                $plotsData
            );
            
            $results[] = array_merge($workerData, $result);
            
            if ($result['success']) {
                $totalWages += $result['totalupah'];
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return [
            'success' => $errorCount === 0,
            'workers' => $results,
            'summary' => [
                'total_workers' => count($workersData),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_wages' => $totalWages
            ]
        ];
    }

    /**
     * Get activity group from activity code
     *
     * @param string $activitycode
     * @return string|null
     */
    private function getActivityGroup($activitycode)
    {
        return DB::table('activity')
            ->where('activitycode', $activitycode)
            ->value('activitygroup');
    }

    /**
     * Determine day type for wage calculation
     *
     * @param string $workDate
     * @return string
     */
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

    /**
     * Check if working hours qualify for full day rate
     *
     * @param float $totalHours
     * @param string $dayType
     * @return bool
     */
    private function isFullDay($totalHours, $dayType)
    {
        // For weekend, might have different full day criteria
        if (in_array($dayType, ['WEEKEND_SATURDAY', 'WEEKEND_SUNDAY'])) {
            return $totalHours >= 8; // Still 8 hours for full day
        }
        
        // Regular weekday - 8 hours for full day
        return $totalHours >= 8;
    }

    /**
     * Get full day rate based on day type
     *
     * @param array $wageRates
     * @param string $dayType
     * @return float
     */
    private function getFullDayRate($wageRates, $dayType)
    {
        switch ($dayType) {
            case 'WEEKEND_SATURDAY':
                return $wageRates['WEEKEND_SATURDAY'] ?? $wageRates['DAILY'] ?? 0;
            case 'WEEKEND_SUNDAY':
                return $wageRates['WEEKEND_SUNDAY'] ?? $wageRates['DAILY'] ?? 0;
            default:
                return $wageRates['DAILY'] ?? 0;
        }
    }

    /**
     * Generate notes for harian calculation
     *
     * @param string $calculationMethod
     * @param string $dayType
     * @param float $totalHours
     * @param float $overtimeHours
     * @return string
     */
    private function generateHarianNotes($calculationMethod, $dayType, $totalHours, $overtimeHours)
    {
        $notes = [];
        
        if ($calculationMethod === 'full_day') {
            $notes[] = "Full day rate ({$dayType})";
        } else {
            $notes[] = "Hourly rate: {$totalHours} hours";
        }
        
        if ($overtimeHours > 0) {
            $notes[] = "Overtime: {$overtimeHours} hours";
        }
        
        return implode(', ', $notes);
    }

    /**
     * Get error wage result
     *
     * @param string $errorMessage
     * @return array
     */
    private function getErrorWageResult($errorMessage)
    {
        return [
            'success' => false,
            'error' => $errorMessage,
            'upahharian' => 0,
            'upahperjam' => 0,
            'upahlembur' => 0,
            'premi' => 0,
            'upahborongan' => 0,
            'totalupah' => 0,
            'notes' => 'Error in wage calculation'
        ];
    }

    /**
     * Validate wage calculation parameters
     *
     * @param array $params
     * @return array
     */
    public function validateWageParameters($params)
    {
        $errors = [];
        
        if (empty($params['companycode'])) {
            $errors[] = 'Company code is required';
        }
        
        if (empty($params['activitycode'])) {
            $errors[] = 'Activity code is required';
        }
        
        if (!in_array($params['jenistenagakerja'], [1, 2, 3])) {
            $errors[] = 'Invalid jenis tenaga kerja';
        }
        
        if (empty($params['workDate'])) {
            $errors[] = 'Work date is required';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}