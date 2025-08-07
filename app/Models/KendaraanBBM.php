<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KendaraanBBM extends Model
{
    protected $table = 'kendaraanbbm';
    
    // No auto-incrementing primary key
    public $incrementing = false;
    
    // Composite primary key - handled manually
    protected $primaryKey = ['companycode', 'lkhno', 'plot', 'nokendaraan'];
    
    // Disable timestamps since we use custom timestamp fields
    public $timestamps = false;
    
    protected $fillable = [
        'companycode',
        'lkhno', 
        'plot',
        'nokendaraan',
        'mandorid',
        'operatorid',
        'jammulai',
        'jamselesai',
        'hourmeterstart',
        'hourmeterend',
        'solar',
        'inputby',
        'createdat',
        'adminupdateby',
        'adminupdatedat'
    ];

    protected $casts = [
        'hourmeterstart' => 'decimal:2',
        'hourmeterend' => 'decimal:2',
        'solar' => 'decimal:3',
        'createdat' => 'datetime',
        'adminupdatedat' => 'datetime'
    ];

    // =============================================================================
    // RELATIONSHIPS
    // =============================================================================

    /**
     * Relationship to LKH Header
     */
    public function lkhHeader()
    {
        return $this->belongsTo(Lkhhdr::class, ['companycode', 'lkhno'], ['companycode', 'lkhno']);
    }

    /**
     * Relationship to Kendaraan
     */
    public function kendaraan()
    {
        return $this->belongsTo(Kendaraan::class, 'nokendaraan', 'nokendaraan')
                    ->where('kendaraan.companycode', $this->companycode);
    }

    /**
     * Relationship to Mandor (User)
     */
    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid')
                    ->where('user.companycode', $this->companycode);
    }

    /**
     * Relationship to Operator (TenagaKerja)
     */
    public function operator()
    {
        return $this->belongsTo(TenagaKerja::class, 'operatorid', 'tenagakerjaid')
                    ->where('tenagakerja.companycode', $this->companycode);
    }

    // =============================================================================
    // QUERY SCOPES
    // =============================================================================

    /**
     * Scope by company
     */
    public function scopeByCompany($query, $companyCode)
    {
        return $query->where('companycode', $companyCode);
    }

    /**
     * Scope by mandor
     */
    public function scopeByMandor($query, $mandorId)
    {
        return $query->where('mandorid', $mandorId);
    }

    /**
     * Scope by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween(DB::raw('DATE(createdat)'), [$startDate, $endDate]);
    }

    /**
     * Scope pending solar input (admin kendaraan belum input)
     */
    public function scopePendingSolarInput($query)
    {
        return $query->whereNull('solar');
    }

    /**
     * Scope completed solar input (ready for BBM admin)
     */
    public function scopeCompletedSolarInput($query)
    {
        return $query->whereNotNull('solar')
                    ->whereNotNull('hourmeterstart')
                    ->whereNotNull('hourmeterend');
    }

    // =============================================================================
    // STATIC METHODS FOR ADMIN OPERATIONS
    // =============================================================================

    /**
     * Get BBM data for Admin Kendaraan by date
     */
    public static function getForAdminKendaraan($companyCode, $date)
    {
        return static::byCompany($companyCode)
            ->byDateRange($date, $date)
            ->pendingSolarInput()
            ->with(['lkhHeader', 'kendaraan', 'mandor', 'operator'])
            ->orderBy('nokendaraan')
            ->orderBy('lkhno')
            ->get();
    }

    /**
     * Get BBM data for Admin BBM (completed solar input)
     */
    public static function getForAdminBBM($companyCode, $startDate, $endDate)
    {
        return static::byCompany($companyCode)
            ->byDateRange($startDate, $endDate)
            ->completedSolarInput()
            ->with(['lkhHeader', 'kendaraan', 'mandor', 'operator'])
            ->orderBy('createdat', 'desc')
            ->get();
    }

    /**
     * Update solar data by Admin Kendaraan
     */
    public static function updateSolarData($companyCode, $lkhno, $plot, $nokendaraan, $solarData, $adminUser)
    {
        return static::where('companycode', $companyCode)
            ->where('lkhno', $lkhno)
            ->where('plot', $plot)
            ->where('nokendaraan', $nokendaraan)
            ->update([
                'hourmeterstart' => $solarData['hourmeterstart'],
                'hourmeterend' => $solarData['hourmeterend'], 
                'solar' => $solarData['solar'],
                'adminupdateby' => $adminUser,
                'adminupdatedat' => now()
            ]);
    }

    // =============================================================================
    // HELPER METHODS
    // =============================================================================

    /**
     * Calculate work duration in hours
     */
    public function getWorkDurationAttribute()
    {
        try {
            $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->jammulai);
            $end = \Carbon\Carbon::createFromFormat('H:i:s', $this->jamselesai);
            
            // Handle overnight work
            if ($end->lt($start)) {
                $end->addDay();
            }
            
            return $start->diffInHours($end, true);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Calculate hour meter difference
     */
    public function getHourMeterDiffAttribute()
    {
        if ($this->hourmeterstart && $this->hourmeterend) {
            return $this->hourmeterend - $this->hourmeterstart;
        }
        return null;
    }

    /**
     * Check if solar data is complete
     */
    public function getIsCompletedAttribute()
    {
        return !is_null($this->solar) && 
               !is_null($this->hourmeterstart) && 
               !is_null($this->hourmeterend);
    }

    /**
     * Get formatted work time
     */
    public function getWorkTimeFormattedAttribute()
    {
        return substr($this->jammulai, 0, 5) . ' - ' . substr($this->jamselesai, 0, 5);
    }

    // =============================================================================
    // OVERRIDES FOR COMPOSITE PRIMARY KEY
    // =============================================================================

    /**
     * Set the keys for a save update query.
     */
    protected function setKeysForSaveQuery($query)
    {
        foreach ($this->primaryKey as $key) {
            $query->where($key, '=', $this->getKeyForSaveQuery($key));
        }
        return $query;
    }

    /**
     * Get the primary key value for a save query.
     */
    protected function getKeyForSaveQuery($key = null)
    {
        if (is_null($key)) {
            $key = $this->getKeyName();
        }

        return $this->original[$key] ?? $this->getAttribute($key);
    }
}