<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lkhhdr extends Model
{
    protected $table = 'lkhhdr';
    protected $primaryKey = 'lkhno';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'lkhno',
        'rkhno',
        'companycode',
        'activitycode',
        'blok',
        'plot',
        'mandorid',
        'lkhdate',
        'jenistenagakerja',
        'totalworkers',
        'totalluasactual',
        'totalhasil',
        'totalsisa',
        'totalupahall',
        'jammulaikerja',
        'jamselesaikerja',
        'totalovertimehours',
        'status',
        'keterangan',
        'approval1idjabatan',
        'approval1userid',
        'approval1flag',
        'approval1date',
        'approval2idjabatan',
        'approval2userid',
        'approval2flag',
        'approval2date',
        'approval3idjabatan',
        'approval3userid',
        'approval3flag',
        'approval3date',
        'approval4idjabatan',
        'approval4userid',
        'approval4flag',
        'approval4date',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
        'mobilecreatedat',
        'mobileupdatedat',
        'webreceivedat',
    ];

    protected $casts = [
        'lkhdate' => 'date',
        'jenistenagakerja' => 'integer',
        'totalworkers' => 'integer',
        'totalluasactual' => 'decimal:2',
        'totalhasil' => 'decimal:2',
        'totalsisa' => 'decimal:2',
        'totalupahall' => 'decimal:2',
        'totalovertimehours' => 'decimal:2',
        'approval1idjabatan' => 'integer',
        'approval1date' => 'datetime',
        'approval2idjabatan' => 'integer',
        'approval2date' => 'datetime',
        'approval3idjabatan' => 'integer',
        'approval3date' => 'datetime',
        'approval4idjabatan' => 'integer',
        'approval4date' => 'datetime',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'mobilecreatedat' => 'datetime',
        'mobileupdatedat' => 'datetime',
        'webreceivedat' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Relations
    public function rkh()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhno', 'rkhno');
    }

    public function details()
    {
        return $this->hasMany(Lkhlst::class, 'lkhno', 'lkhno');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }

    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    // Helper methods
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'EMPTY' => 'gray',
            'DRAFT' => 'yellow',
            'COMPLETED' => 'blue',
            'SUBMITTED' => 'purple',
            'APPROVED' => 'green',
            default => 'gray'
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'EMPTY' => 'Kosong',
            'DRAFT' => 'Draft',
            'COMPLETED' => 'Selesai',
            'SUBMITTED' => 'Disubmit',
            'APPROVED' => 'Disetujui',
            default => 'Unknown'
        };
    }

    // Scope untuk filter
    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    public function scopeByMandor($query, $mandorid)
    {
        return $query->where('mandorid', $mandorid);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('lkhdate', $date);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}