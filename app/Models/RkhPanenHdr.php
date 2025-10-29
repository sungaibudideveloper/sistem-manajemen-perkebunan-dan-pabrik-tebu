<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RkhPanenHdr extends Model
{
    protected $table = 'rkhpanenhdr';
    protected $primaryKey = 'rkhpanenno';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhpanenno',
        'rkhdate',
        'mandorpanenid',
        'keterangan',
        'status',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    // ✅ Relationship ke User (Mandor) - Keep this
    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorpanenid', 'userid');
    }

    // ✅ HAPUS eloquent relationship, pakai Query Builder
    // public function kontraktors() { ... } // DELETE INI

    // ✅ Relationship ke Results - Keep this (single key, no problem)
    public function results()
    {
        return $this->hasMany(RkhPanenResult::class, 'rkhpanenno', 'rkhpanenno')
                    ->where('rkhpanenresult.companycode', $this->companycode);
    }

    // ✅ NEW: Getter method pakai Query Builder
    public function getKontraktorsAttribute()
    {
        return DB::table('rkhpanenlst')
            ->where('companycode', $this->companycode)
            ->where('rkhpanenno', $this->rkhpanenno)
            ->get();
    }

    // ✅ Helper method untuk total rencana netto
    public function getTotalRencanaNetto()
    {
        return DB::table('rkhpanenlst')
            ->where('companycode', $this->companycode)
            ->where('rkhpanenno', $this->rkhpanenno)
            ->sum('rencananetto') ?? 0;
    }

    // ✅ Helper method untuk total rencana ha
    public function getTotalRencanaHa()
    {
        return DB::table('rkhpanenlst')
            ->where('companycode', $this->companycode)
            ->where('rkhpanenno', $this->rkhpanenno)
            ->sum('rencanaha') ?? 0;
    }

    // ✅ Helper method untuk count kontraktor
    public function getKontraktorCountAttribute()
    {
        return DB::table('rkhpanenlst')
            ->where('companycode', $this->companycode)
            ->where('rkhpanenno', $this->rkhpanenno)
            ->count();
    }
}