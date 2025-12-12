<?php

// =====================================================
// FILE: app/Models/MasterData/Masterlist.php
// =====================================================
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Masterlist extends Model
{
    protected $table = 'masterlist';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'plot',
        'blok',
        'activebatchno',
        'isactive',
    ];

    protected $casts = [
        'isactive' => 'boolean',
    ];

    public function activeBatch()
    {
        return $this->belongsTo(Batch::class, 'activebatchno', 'batchno');
    }
}