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
        'lifecyclestatus',
        'plantingrkhno',
        'batchdate',
        'tanggalpanen',
        'batcharea',
        'kodevarietas',
        'pkp',
        'lastactivity',
        'isactive',
        'inputby',
        'createdat',
    ];

    protected $casts = [
        'batchdate' => 'date',
        'tanggalpanen' => 'date',
        'batcharea' => 'decimal:2',
        'pkp' => 'integer',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
    ];

    // Relationship to company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    // Accessor for cyclecount
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    public function scopeClosed($query)
    {
        return $query->where('isactive', 0);
    }

    public function scopeLifecycle($query, $status)
    {
        return $query->where('lifecyclestatus', $status);
    }

    public function scopeByPlot($query, $plot)
    {
        return $query->where('plot', $plot);
    }

    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    // Helper methods
    public function isHarvestable(): bool
    {
        return $this->isactive && in_array($this->lifecyclestatus, ['PC', 'RC1', 'RC2', 'RC3']);
    }

    public function isHarvested(): bool
    {
        return !is_null($this->tanggalpanen);
    }

    public function getNextLifecycleStatus(): ?string
    {
        return match($this->lifecyclestatus) {
            'PC' => 'RC1',
            'RC1' => 'RC2',
            'RC2' => 'RC3',
            'RC3' => 'PC',
            default => null
        };
    }

    public function canBeClosed(): bool
    {
        return $this->isactive && !is_null($this->tanggalpanen);
    }

    public function getAgeInDaysAttribute(): int
    {
        return now()->diffInDays($this->batchdate);
    }

    public function getPlotTimeline($company){
        return \DB::select("
        SELECT 
            a.companycode, a.plot, a.latitude, a.longitude,
            d.centerlatitude, d.centerlongitude,
            c.batchno, c.batchdate, c.batcharea, c.tanggalpanen,
            c.kodevarietas, c.lifecyclestatus, c.pkp, c.isactive,
            b.luasarea, b.jaraktanam AS plot_jaraktanam, b.status
        FROM testgpslst AS a
        LEFT JOIN plot AS b ON a.plot = b.plot
        LEFT JOIN batch AS c ON b.plot = c.plot
        LEFT JOIN testgpshdr AS d ON a.plot = d.plot
        WHERE a.companycode = ?
        ",[$company]);
    }
}