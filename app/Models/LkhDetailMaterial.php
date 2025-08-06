<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * LkhDetailMaterial Model
 * Handles material sisa tracking per LKH - input by mandor after work completion
 */
class LkhDetailMaterial extends Model
{
    protected $table = 'lkhdetailmaterial';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false; // Using custom createdat/updatedat

    protected $fillable = [
        'companycode',
        'lkhno',
        'itemcode',
        'qtyditerima',
        'qtysisa',
        'qtydigunakan',
        'keterangan',
        'inputby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'qtyditerima' => 'decimal:3',
        'qtysisa' => 'decimal:3',
        'qtydigunakan' => 'decimal:3',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    // Relations
    public function lkhheader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhno', 'lkhno');
    }


    // Scopes
    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    public function scopeByLkh($query, $lkhno)
    {
        return $query->where('lkhno', $lkhno);
    }

    public function scopeByItem($query, $itemcode)
    {
        return $query->where('itemcode', $itemcode);
    }

    // Helper methods
    public function calculateQtyDigunakan()
    {
        $this->qtydigunakan = $this->qtyditerima - $this->qtysisa;
        return $this->qtydigunakan;
    }

    public function getEfficiencyPercentage()
    {
        if ($this->qtyditerima <= 0) {
            return 0;
        }
        
        return ($this->qtydigunakan / $this->qtyditerima) * 100;
    }

    public function updateSisa($qtysisa, $keterangan = null, $inputby = null)
    {
        $this->update([
            'qtysisa' => $qtysisa,
            'qtydigunakan' => $this->qtyditerima - $qtysisa,
            'keterangan' => $keterangan ?? $this->keterangan,
            'inputby' => $inputby ?? $this->inputby,
            'updatedat' => now()
        ]);
    }
}