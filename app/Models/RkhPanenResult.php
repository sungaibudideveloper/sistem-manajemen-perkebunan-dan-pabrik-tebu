<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RkhPanenResult extends Model
{
    protected $table = 'rkhpanenresult';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhpanenno',
        'blok',
        'plot',
        'batchno',
        'luasplot',
        'kodestatus',
        'haritebang',
        'stc',
        'hc',
        'bc',
        'fbrit',
        'fbton',
        'ispremium',
        'keterangan',
    ];

    protected $casts = [
        'luasplot' => 'decimal:2',
        'haritebang' => 'integer',
        'stc' => 'decimal:2',
        'hc' => 'decimal:2',
        'bc' => 'decimal:2',
        'fbrit' => 'integer',
        'fbton' => 'decimal:2',
        'ispremium' => 'boolean',
    ];

    // Composite primary key
    protected $primaryKey = ['companycode', 'rkhpanenno', 'plot'];

    // =====================================
    // RELATIONSHIPS
    // =====================================

    /**
     * RKH Panen Header
     */
    public function header()
    {
        return $this->belongsTo(RkhPanenHdr::class, 'rkhpanenno', 'rkhpanenno')
                    ->where('companycode', $this->companycode);
    }

    /**
     * Blok Info
     */
    public function blokInfo()
    {
        return $this->belongsTo(Blok::class, 'blok', 'blok')
                    ->where('companycode', $this->companycode);
    }

    /**
     * Plot Info (from masterlist)
     */
    public function plotInfo()
    {
        return $this->belongsTo(Masterlist::class, 'plot', 'plot')
                    ->where('companycode', $this->companycode);
    }

    /**
     * Batch Info
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchno', 'batchno');
    }

    // =====================================
    // ACCESSORS & ATTRIBUTES
    // =====================================

    /**
     * Kodestatus Badge Color
     */
    public function getKodestatusColorAttribute()
    {
        return match($this->kodestatus) {
            'PC' => 'green',
            'RC1' => 'blue',
            'RC2' => 'yellow',
            'RC3' => 'red',
            default => 'gray',
        };
    }

    /**
     * Kodestatus Label
     */
    public function getKodestatusLabelAttribute()
    {
        return match($this->kodestatus) {
            'PC' => 'Plant Cane',
            'RC1' => 'Ratoon 1',
            'RC2' => 'Ratoon 2',
            'RC3' => 'Ratoon 3',
            default => 'Unknown',
        };
    }

    /**
     * Premium Badge
     */
    public function getPremiumBadgeAttribute()
    {
        return $this->ispremium ? 'Premium' : 'Non-Premium';
    }

    /**
     * Plot Full Name (Blok-Plot)
     */
    public function getPlotFullNameAttribute()
    {
        return "{$this->blok}-{$this->plot}";
    }

    /**
     * Is Petak Baru (hari tebang ke-1)
     */
    public function isPetakBaru()
    {
        return $this->haritebang == 1;
    }

    /**
     * Has Data (hasil sudah diinput)
     */
    public function hasData()
    {
        return !is_null($this->hc) && $this->hc > 0;
    }

    /**
     * Is Empty (waiting for input)
     */
    public function isEmpty()
    {
        return is_null($this->hc);
    }

    /**
     * Get completion percentage (hc/stc)
     */
    public function getCompletionPercentageAttribute()
    {
        if (is_null($this->stc) || $this->stc == 0) {
            return 0;
        }
        return round(($this->hc / $this->stc) * 100, 2);
    }

    /**
     * Calculate productivity (ton/ha)
     * Note: fbton adalah field balance di lapangan (belum sampai pabrik)
     * Untuk productivity aktual, nanti dari data timbangan
     */
    public function getEstimatedProductivityAttribute()
    {
        if (is_null($this->hc) || $this->hc == 0) {
            return 0;
        }
        return round($this->fbton / $this->hc, 2);
    }

    // =====================================
    // HELPER METHODS
    // =====================================

    /**
     * Auto-calculate BC (Balance Cutting)
     */
    public function calculateBc()
    {
        $this->bc = ($this->stc ?? 0) - ($this->hc ?? 0);
        return $this;
    }

    /**
     * Auto-calculate FBTon (Field Balance Ton)
     * 1 RIT = 5 ton (sesuai business flow)
     */
    public function calculateFbton()
    {
        $this->fbton = ($this->fbrit ?? 0) * 5;
        return $this;
    }

    /**
     * Get status indicator (empty/partial/complete)
     */
    public function getStatusIndicator()
    {
        if ($this->isEmpty()) {
            return [
                'label' => 'Belum Input',
                'color' => 'gray',
                'icon' => 'clock'
            ];
        }

        if ($this->bc > 0) {
            return [
                'label' => 'Partial',
                'color' => 'yellow',
                'icon' => 'refresh'
            ];
        }

        return [
            'label' => 'Selesai',
            'color' => 'green',
            'icon' => 'check'
        ];
    }

    // =====================================
    // SCOPES
    // =====================================

    /**
     * Scope: Filter by blok
     */
    public function scopeByBlok($query, $blok)
    {
        return $query->where('blok', $blok);
    }

    /**
     * Scope: Filter by kodestatus
     */
    public function scopeByKodestatus($query, $kodestatus)
    {
        return $query->where('kodestatus', $kodestatus);
    }

    /**
     * Scope: Only premium
     */
    public function scopePremium($query)
    {
        return $query->where('ispremium', 1);
    }

    /**
     * Scope: Only non-premium
     */
    public function scopeNonPremium($query)
    {
        return $query->where('ispremium', 0);
    }

    /**
     * Scope: Petak baru (hari tebang ke-1)
     */
    public function scopePetakBaru($query)
    {
        return $query->where('haritebang', 1);
    }

    /**
     * Scope: Has data (hasil sudah diinput)
     */
    public function scopeWithData($query)
    {
        return $query->whereNotNull('hc')
                     ->where('hc', '>', 0);
    }

    /**
     * Scope: Empty (waiting for input)
     */
    public function scopeEmpty($query)
    {
        return $query->whereNull('hc');
    }

    /**
     * Scope: Incomplete (still has balance)
     */
    public function scopeIncomplete($query)
    {
        return $query->where('bc', '>', 0);
    }

    /**
     * Scope: Completed (no balance)
     */
    public function scopeCompleted($query)
    {
        return $query->where('bc', '<=', 0);
    }
}