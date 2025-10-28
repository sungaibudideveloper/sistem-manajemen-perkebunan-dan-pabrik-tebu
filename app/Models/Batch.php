<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $table = 'batch';
    protected $primaryKey = 'batchno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'batchno',
        'companycode',
        'plot',
        'batchdate',
        'batcharea',
        'kodevarietas',
        'lifecyclestatus',
        'jaraktanam',
        'lastactivity',
        'isactive',
        'plantingrkhno',
        'tanggalpanenpc',
        'tanggalpanenrc1',
        'tanggalpanenrc2',
        'tanggalpanenrc3',
        'inputby',
        'createdat',
    ];

    protected $casts = [
        'batchdate' => 'date',
        'batcharea' => 'decimal:2',
        'jaraktanam' => 'integer',
        'isactive' => 'boolean',
        'tanggalpanenpc' => 'date',
        'tanggalpanenrc1' => 'date',
        'tanggalpanenrc2' => 'date',
        'tanggalpanenrc3' => 'date',
        'createdat' => 'datetime',
    ];

    // Relationship to company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    // Relationship to masterlist (plot)
    public function masterlist()
    {
        return $this->belongsTo(Masterlist::class, 'plot', 'plot')
                    ->where('companycode', $this->companycode);
    }

    // Accessor for cyclecount (computed from lifecyclestatus)
    public function getCyclecountAttribute(): int
    {
        return match($this->lifecyclestatus) {
            'PC' => 0,
            'RC1' => 1,
            'RC2' => 2,
            'RC3' => 3,
            default => 0
        };
    }

    // Accessor for status text
    public function getStatusTextAttribute(): string
    {
        return $this->isactive ? 'Active' : 'Closed';
    }

    // Accessor for lifecycle badge color
    public function getLifecycleBadgeColorAttribute(): string
    {
        return match($this->lifecyclestatus) {
            'PC' => 'bg-green-100 text-green-800',
            'RC1' => 'bg-blue-100 text-blue-800',
            'RC2' => 'bg-yellow-100 text-yellow-800',
            'RC3' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // Scope untuk filter batch aktif
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    // Scope untuk filter batch closed
    public function scopeClosed($query)
    {
        return $query->where('isactive', 0);
    }

    // Scope untuk filter by lifecycle status
    public function scopeLifecycle($query, $status)
    {
        return $query->where('lifecyclestatus', $status);
    }

    // Scope untuk filter by plot
    public function scopeByPlot($query, $plot)
    {
        return $query->where('plot', $plot);
    }

    // Check if batch is harvestable
    public function isHarvestable(): bool
    {
        return $this->isactive && in_array($this->lifecyclestatus, ['PC', 'RC1', 'RC2', 'RC3']);
    }

    // Check if specific cycle is harvested
    public function isCycleHarvested($cycle): bool
    {
        $field = 'tanggalpanen' . strtolower($cycle);
        return !is_null($this->$field);
    }

    // Get next lifecycle status
    public function getNextLifecycleStatus(): ?string
    {
        return match($this->lifecyclestatus) {
            'PC' => 'RC1',
            'RC1' => 'RC2',
            'RC2' => 'RC3',
            'RC3' => null, // No next status after RC3
            default => null
        };
    }

    // Check if batch can be closed
    public function canBeClosed(): bool
    {
        return $this->lifecyclestatus === 'RC3' && !is_null($this->tanggalpanenrc3);
    }

    // Get harvest date for current lifecycle
    public function getCurrentHarvestDate(): ?string
    {
        $field = 'tanggalpanen' . strtolower($this->lifecyclestatus);
        return $this->$field;
    }

    // Get all harvest dates as array
    public function getHarvestDatesAttribute(): array
    {
        return [
            'PC' => $this->tanggalpanenpc,
            'RC1' => $this->tanggalpanenrc1,
            'RC2' => $this->tanggalpanenrc2,
            'RC3' => $this->tanggalpanenrc3,
        ];
    }
}