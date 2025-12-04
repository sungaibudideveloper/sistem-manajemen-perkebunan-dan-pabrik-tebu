<?php
// app/Models/Kendaraan.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Kendaraan extends Model
{
    protected $table = 'kendaraan';
    
    // Composite primary key
    protected $primaryKey = ['companycode', 'nokendaraan'];
    public $incrementing = false;
    protected $keyType = 'string';
    
    public $timestamps = false;
    
    protected $fillable = [
        'companycode',
        'idtenagakerja',
        'nokendaraan',
        'hourmeter',
        'jenis',
        'inputby',
        'createdat',
        'updateby',
        'updatedate',
        'isactive'
    ];

    protected $casts = [
        'hourmeter' => 'float',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedate' => 'datetime'
    ];

    /**
     * Relationship to TenagaKerja (Operator)
     */
    public function operator()
    {
        return $this->belongsTo(TenagaKerja::class, 'idtenagakerja', 'tenagakerjaid');
    }

    /**
     * Scope for active vehicles
     */
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    /**
     * Scope for specific company
     */
    public function scopeForCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    /**
     * Get the route key for the model (for composite keys)
     */
    public function getRouteKeyName()
    {
        return 'nokendaraan';
    }

    /**
     * Set the keys for a save update query (for composite keys)
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    /**
     * Get the primary key value for a save query (for composite keys)
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }
}