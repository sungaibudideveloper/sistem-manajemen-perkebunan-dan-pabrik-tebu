<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lkhlst extends Model
{
    protected $table = 'lkhlst';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false; // Using custom createdat/updatedat

    protected $fillable = [
        'lkhno',
        'workersequence',
        'workername',
        'noktp',
        'blok',
        'plot',
        'luasplot',
        'hasil',
        'sisa',
        'materialused',
        'jammasuk',
        'jamselesai',
        'overtimehours',
        'premi',
        'upahharian',
        'totalupahharian',
        'costperha',
        'totalbiayaborongan',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'workersequence' => 'integer',
        'luasplot' => 'decimal:2',
        'hasil' => 'decimal:2',
        'sisa' => 'decimal:2',
        'overtimehours' => 'decimal:2',
        'premi' => 'decimal:2',
        'upahharian' => 'decimal:2',
        'totalupahharian' => 'decimal:2',
        'costperha' => 'decimal:2',
        'totalbiayaborongan' => 'decimal:2',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Relations
    public function lkhheader()
    {
        return $this->belongsTo(Lkhhdr::class, 'lkhno', 'lkhno');
    }

    // Scopes
    public function scopeByLkh($query, $lkhno)
    {
        return $query->where('lkhno', $lkhno);
    }

    public function scopeByWorker($query, $workername)
    {
        return $query->where('workername', $workername);
    }

    public function scopeOrderBySequence($query)
    {
        return $query->orderBy('workersequence');
    }

    // Helper methods
    public function getTotalJamKerja()
    {
        if (!$this->jammasuk || !$this->jamselesai) {
            return 0;
        }
        
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->jammasuk);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $this->jamselesai);
        
        // Handle case where work crosses midnight
        if ($end->lt($start)) {
            $end->addDay();
        }
        
        return $start->diffInHours($end, true);
    }

    public function getFormattedJamKerja()
    {
        $hours = $this->getTotalJamKerja();
        $wholeHours = floor($hours);
        $minutes = ($hours - $wholeHours) * 60;
        
        return sprintf('%02d:%02d', $wholeHours, $minutes);
    }
}