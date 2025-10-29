<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model RkhPanenResult (SIMPLIFIED)
 * 
 * PENTING: Model ini punya composite primary key (companycode, rkhpanenno, plot)
 * Jadi JANGAN pakai method Eloquent seperti save(), update(), delete()
 * Pakai Query Builder (DB::table('rkhpanenresult')) untuk semua operasi CRUD
 * 
 * Model ini HANYA untuk relationship dan accessor saja
 */
class RkhPanenResult extends Model
{
    protected $table = 'rkhpanenresult';
    public $incrementing = false;
    public $timestamps = false;

    // JANGAN pakai primaryKey untuk composite key
    protected $primaryKey = null;

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

    // =====================================
    // RELATIONSHIPS (Read-only)
    // =====================================

    public function header()
    {
        return $this->belongsTo(RkhPanenHdr::class, 'rkhpanenno', 'rkhpanenno')
                    ->where('companycode', $this->companycode);
    }

    public function blokInfo()
    {
        return $this->belongsTo(Blok::class, 'blok', 'blok')
                    ->where('companycode', $this->companycode);
    }

    public function plotInfo()
    {
        return $this->belongsTo(Masterlist::class, 'plot', 'plot')
                    ->where('companycode', $this->companycode);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchno', 'batchno');
    }

    // =====================================
    // ACCESSORS (Read-only attributes)
    // =====================================

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

    public function getPremiumBadgeAttribute()
    {
        return $this->ispremium ? 'Premium' : 'Non-Premium';
    }

    public function getPlotFullNameAttribute()
    {
        return "{$this->blok}-{$this->plot}";
    }

    public function isPetakBaru()
    {
        return $this->haritebang == 1;
    }

    public function hasData()
    {
        return !is_null($this->hc) && $this->hc > 0;
    }

    public function isEmpty()
    {
        return is_null($this->hc);
    }

    public function getCompletionPercentageAttribute()
    {
        if (is_null($this->stc) || $this->stc == 0) {
            return 0;
        }
        return round(($this->hc / $this->stc) * 100, 2);
    }

    public function getEstimatedProductivityAttribute()
    {
        if (is_null($this->hc) || $this->hc == 0) {
            return 0;
        }
        return round($this->fbton / $this->hc, 2);
    }

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
    // SCOPES (untuk Query Builder style)
    // =====================================

    public function scopeByBlok($query, $blok)
    {
        return $query->where('blok', $blok);
    }

    public function scopeByKodestatus($query, $kodestatus)
    {
        return $query->where('kodestatus', $kodestatus);
    }

    public function scopePremium($query)
    {
        return $query->where('ispremium', 1);
    }

    public function scopeNonPremium($query)
    {
        return $query->where('ispremium', 0);
    }

    public function scopePetakBaru($query)
    {
        return $query->where('haritebang', 1);
    }

    public function scopeWithData($query)
    {
        return $query->whereNotNull('hc')
                     ->where('hc', '>', 0);
    }

    public function scopeEmpty($query)
    {
        return $query->whereNull('hc');
    }

    public function scopeIncomplete($query)
    {
        return $query->where('bc', '>', 0);
    }

    public function scopeCompleted($query)
    {
        return $query->where('bc', '<=', 0);
    }
}