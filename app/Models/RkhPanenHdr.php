<?php

// ============================================
// FILE 1: app/Models/RkhPanenHdr.php
// ============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RkhPanenHdr extends Model
{
    protected $table = 'rkhpanenhdr';
    
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhpanenno',
        'rkhdate',
        'mandorpanenid',
        'targettoday',
        'targetha',
        'keterangan',
        'status',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'rkhdate' => 'date',
        'targettoday' => 'decimal:2',
        'targetha' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'rkhpanenno';
    }

    /**
     * Relationship: RKH Panen has many Kontraktor Lists
     */
    public function kontraktors()
    {
        return $this->hasMany(RkhPanenLst::class, 'rkhpanenno', 'rkhpanenno')
            ->where('companycode', $this->companycode);
    }

    /**
     * Relationship: RKH Panen has many Results
     */
    public function results()
    {
        return $this->hasMany(RkhPanenResult::class, 'rkhpanenno', 'rkhpanenno')
            ->where('companycode', $this->companycode);
    }

    /**
     * Relationship: Belongs to Mandor (User)
     */
    public function mandor()
    {
        return $this->belongsTo(\App\Models\User::class, 'mandorpanenid', 'userid');
    }

    /**
     * Scope: Filter by date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('rkhdate', $date);
    }

    /**
     * Scope: Filter by company
     */
    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get total rencana netto from all kontraktors
     */
    public function getTotalRencanaNetto()
    {
        return $this->kontraktors()->sum('rencananetto');
    }

    /**
     * Get total rencana hektar from all kontraktors
     */
    public function getTotalRencanaHa()
    {
        return $this->kontraktors()->sum('rencanaha');
    }

    /**
     * Get total hasil HC from results
     */
    public function getTotalHasilHc()
    {
        return $this->results()->sum('hc');
    }

    /**
     * Get total field balance TON from results
     */
    public function getTotalFieldBalanceTon()
    {
        return $this->results()->sum('fbton');
    }

    /**
     * Get petak baru (haritebang = 1)
     */
    public function getPetakBaru()
    {
        return $this->results()->where('haritebang', 1)->get();
    }

    /**
     * Check if RKH has hasil input
     */
    public function hasHasil()
    {
        return $this->results()->exists();
    }

    /**
     * Get formatted RKH date
     */
    public function getFormattedDateAttribute()
    {
        return $this->rkhdate ? $this->rkhdate->format('d/m/Y') : '-';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'DRAFT' => 'bg-gray-500',
            'COMPLETED' => 'bg-green-500',
            default => 'bg-blue-500',
        };
    }
}