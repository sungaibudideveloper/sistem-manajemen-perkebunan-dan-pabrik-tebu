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
        'adminupdatedat',
        // TAMBAHAN BARU untuk fitur print & gudang
        'printedby',
        'printedat', 
        'ordernumber',
        'status',
        'gudangconfirm',
        'gudangconfirmedby',
        'gudangconfirmedat'
    ];

    protected $casts = [
        'hourmeterstart' => 'decimal:2',
        'hourmeterend' => 'decimal:2',
        'solar' => 'decimal:3',
        'createdat' => 'datetime',
        'adminupdatedat' => 'datetime',
        'printedat' => 'datetime',        // TAMBAHAN BARU
        'gudangconfirmedat' => 'datetime', // TAMBAHAN BARU
        'gudangconfirm' => 'boolean'       // TAMBAHAN BARU
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
        return $query->whereNull('solar')
                    ->whereNull('status'); // Status masih null
    }

    /**
     * Scope completed solar input (sudah input, ready for print)
     */
    public function scopeReadyForPrint($query)
    {
        return $query->whereNotNull('solar')
                    ->whereNotNull('hourmeterstart')
                    ->whereNotNull('hourmeterend')
                    ->where('status', 'INPUTTED');
    }

    /**
     * Scope printed orders (ready for gudang BBM confirmation)
     */
    public function scopeReadyForGudangConfirm($query)
    {
        return $query->where('status', 'PRINTED')
                    ->where('gudangconfirm', 0)
                    ->whereNotNull('ordernumber');
    }

    /**
     * Scope confirmed by gudang
     */
    public function scopeConfirmedByGudang($query)
    {
        return $query->where('status', 'PRINTED')
                    ->where('gudangconfirm', 1);
    }

    /**
     * Scope by order number
     */
    public function scopeByOrderNumber($query, $orderNumber)
    {
        return $query->where('ordernumber', $orderNumber);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // =============================================================================
    // STATIC METHODS FOR ADMIN OPERATIONS (UPDATED)
    // =============================================================================

    /**
     * Get BBM data for Admin Kendaraan by date
     */
    public static function getForAdminKendaraan($companyCode, $date)
    {
        return static::byCompany($companyCode)
            ->byDateRange($date, $date)
            ->with(['lkhHeader.activity', 'kendaraan', 'mandor', 'operator'])
            ->orderBy('createdat', 'desc')
            ->orderBy('nokendaraan')
            ->get();
    }

    /**
     * Get BBM data for Admin BBM (printed orders ready for confirmation)
     */
    public static function getForAdminBBM($companyCode, $startDate, $endDate)
    {
        return static::byCompany($companyCode)
            ->byDateRange($startDate, $endDate)
            ->readyForGudangConfirm()
            ->with(['lkhHeader.activity', 'kendaraan', 'mandor', 'operator'])
            ->orderBy('printedat', 'desc')
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
                'status' => 'INPUTTED',
                'adminupdateby' => $adminUser,
                'adminupdatedat' => now()
            ]);
    }

    /**
     * Mark as printed and generate order number
     */
    public static function markAsPrinted($companyCode, $lkhno, $orderNumber, $adminUser)
    {
        return static::where('companycode', $companyCode)
            ->where('lkhno', $lkhno)
            ->where('status', 'INPUTTED')
            ->update([
                'status' => 'PRINTED',
                'ordernumber' => $orderNumber,
                'printedby' => $adminUser,
                'printedat' => now(),
                'adminupdateby' => $adminUser,
                'adminupdatedat' => now()
            ]);
    }

    /**
     * Confirm by gudang BBM
     */
    public static function confirmByGudang($companyCode, $orderNumber, $adminUser)
    {
        return static::where('companycode', $companyCode)
            ->where('ordernumber', $orderNumber)
            ->where('status', 'PRINTED')
            ->where('gudangconfirm', 0)
            ->update([
                'gudangconfirm' => 1,
                'gudangconfirmedby' => $adminUser,
                'gudangconfirmedat' => now(),
                'adminupdateby' => $adminUser,
                'adminupdatedat' => now()
            ]);
    }

    // =============================================================================
    // HELPER METHODS (UPDATED)
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
     * Check if ready for print
     */
    public function getIsReadyForPrintAttribute()
    {
        return $this->is_completed && $this->status === 'INPUTTED';
    }

    /**
     * Check if already printed
     */
    public function getIsPrintedAttribute()
    {
        return $this->status === 'PRINTED' && !is_null($this->ordernumber);
    }

    /**
     * Check if confirmed by gudang
     */
    public function getIsConfirmedAttribute()
    {
        return $this->is_printed && $this->gudangconfirm;
    }

    /**
     * Get formatted work time
     */
    public function getWorkTimeFormattedAttribute()
    {
        return substr($this->jammulai, 0, 5) . ' - ' . substr($this->jamselesai, 0, 5);
    }

    /**
     * Get status badge
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'INPUTTED':
                return ['text' => 'Sudah Input', 'class' => 'bg-green-100 text-green-700'];
            case 'PRINTED':
                if ($this->gudangconfirm) {
                    return ['text' => 'Confirmed', 'class' => 'bg-blue-100 text-blue-700'];
                }
                return ['text' => 'Printed', 'class' => 'bg-gray-100 text-gray-700'];
            default:
                return ['text' => 'Belum Input', 'class' => 'bg-yellow-100 text-yellow-700'];
        }
    }

    // =============================================================================
    // OVERRIDES FOR COMPOSITE PRIMARY KEY (TIDAK BERUBAH)
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