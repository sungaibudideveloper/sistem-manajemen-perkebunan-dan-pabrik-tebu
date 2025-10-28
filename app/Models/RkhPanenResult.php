// ============================================
// FILE 3: app/Models/RkhPanenResult.php
// ============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RkhPanenResult extends Model
{
    protected $table = 'rkhpanenresult';
    
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhpanenno',
        'blok',
        'plot',
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
        'stc' => 'decimal:2',
        'hc' => 'decimal:2',
        'bc' => 'decimal:2',
        'fbrit' => 'integer',
        'fbton' => 'decimal:2',
        'ispremium' => 'boolean',
    ];

    /**
     * Relationship: Belongs to RKH Panen Header
     */
    public function rkhpanen()
    {
        return $this->belongsTo(RkhPanenHdr::class, 'rkhpanenno', 'rkhpanenno')
            ->where('companycode', $this->companycode);
    }

    /**
     * Relationship: Belongs to Plot (if you have Plot model)
     */
    public function plotDetail()
    {
        return $this->belongsTo(\App\Models\Plot::class, 'plot', 'plot')
            ->where('companycode', $this->companycode);
    }

    /**
     * Scope: Filter by petak baru (haritebang = 1)
     */
    public function scopePetakBaru($query)
    {
        return $query->where('haritebang', 1);
    }

    /**
     * Scope: Filter by blok
     */
    public function scopeByBlok($query, $blok)
    {
        return $query->where('blok', $blok);
    }

    /**
     * Scope: Filter by kode status
     */
    public function scopeByKodeStatus($query, $kodestatus)
    {
        return $query->where('kodestatus', $kodestatus);
    }

    /**
     * Scope: Premium only
     */
    public function scopePremium($query)
    {
        return $query->where('ispremium', 1);
    }

    /**
     * Scope: Non-premium only
     */
    public function scopeNonPremium($query)
    {
        return $query->where('ispremium', 0);
    }

    /**
     * Get formatted plot (Blok-Plot)
     */
    public function getFormattedPlotAttribute()
    {
        return $this->blok . '-' . $this->plot;
    }

    /**
     * Calculate BC (Balance Cutting) automatically
     * BC = STC - HC
     */
    public function calculateBc()
    {
        $this->bc = $this->stc - $this->hc;
        return $this->bc;
    }

    /**
     * Calculate FB TON automatically
     * FB TON = FB RIT × 5
     */
    public function calculateFbTon()
    {
        $this->fbton = $this->fbrit * 5;
        return $this->fbton;
    }

    /**
     * Auto-calculate before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-calculate BC if STC and HC are set
            if ($model->stc !== null && $model->hc !== null) {
                $model->bc = $model->stc - $model->hc;
            }

            // Auto-calculate FB TON if FB RIT is set
            if ($model->fbrit !== null) {
                $model->fbton = $model->fbrit * 5;
            }
        });
    }

    /**
     * Get status badge for premium/non-premium
     */
    public function getPremiumBadgeAttribute()
    {
        return $this->ispremium ? 
            '<span class="badge bg-yellow-500">Premium</span>' : 
            '<span class="badge bg-gray-500">Non-Premium</span>';
    }

    /**
     * Get kode status full name
     */
    public function getKodeStatusFullAttribute()
    {
        return match($this->kodestatus) {
            'PC' => 'Plant Cane',
            'RC1' => 'Ratoon Cane 1',
            'RC2' => 'Ratoon Cane 2',
            'RC3' => 'Ratoon Cane 3',
            default => $this->kodestatus,
        };
    }

    /**
     * Check if plot is petak baru
     */
    public function isPetakBaru()
    {
        return $this->haritebang === 1;
    }

    /**
     * Get progress percentage (HC / STC * 100)
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->stc <= 0) return 0;
        return round(($this->hc / $this->stc) * 100, 2);
    }

    /**
     * Check if plot sudah selesai
     */
    public function isCompleted()
    {
        return $this->bc <= 0;
    }
}