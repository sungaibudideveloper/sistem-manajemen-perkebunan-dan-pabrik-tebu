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
        'status',
        'plantingrkhno',
        'inputby',
        'createdat',
    ];
    
    protected $casts = [
        'batchdate' => 'date',
        'batcharea' => 'decimal:2',
        'createdat' => 'datetime',
    ];
    
    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }
    
    public function masterlist()
    {
        return $this->belongsTo(Masterlist::class, ['companycode', 'plot'], ['companycode', 'plot']);
    }
}