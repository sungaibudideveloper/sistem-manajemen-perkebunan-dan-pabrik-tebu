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
     * Get active vehicles by company
     */
    public static function getActiveVehiclesByCompany($companycode)
    {
        return self::where('companycode', $companycode)
                   ->where('isactive', 1)
                   ->orderBy('nokendaraan')
                   ->get();
    }

    /**
     * Get operators with their vehicles
     */
    public static function getOperatorsWithVehicles($companycode)
    {
        return DB::table('tenagakerja as t')
            ->join('kendaraan as k', function($join) {
                $join->on('t.tenagakerjaid', '=', 'k.idtenagakerja')
                    ->on('t.companycode', '=', 'k.companycode');
            })
            ->where('t.companycode', $companycode)
            ->where('t.isactive', 1)
            ->where('k.isactive', 1) // Hanya kendaraan aktif
            ->select([
                't.tenagakerjaid',
                't.nama',
                't.nik',
                't.jenistenagakerja',
                'k.nokendaraan',
                'k.jenis',
                'k.hourmeter',
                DB::raw('1 as hasVehicle')
            ])
            ->orderBy('t.nama')
            ->get();
    }

    /**
     * Get vehicle by operator ID
     */
    public static function getVehicleByOperator($companycode, $operatorId)
    {
        return DB::table('kendaraan as k')
            ->leftJoin('tenagakerja as t', function($join) {
                $join->on('k.idtenagakerja', '=', 't.tenagakerjaid')
                     ->on('k.companycode', '=', 't.companycode');
            })
            ->where('k.companycode', $companycode)
            ->where('k.idtenagakerja', $operatorId)
            ->where('k.isactive', 1)
            ->select([
                'k.*',
                't.nama as operator_nama',
                't.nik as operator_nik'
            ])
            ->first();
    }

    /**
     * Get available vehicles (not assigned to any operator or assigned but operator is not active)
     */
    public static function getAvailableVehicles($companycode)
    {
        return DB::table('kendaraan as k')
            ->leftJoin('tenagakerja as t', function($join) {
                $join->on('k.idtenagakerja', '=', 't.tenagakerjaid')
                     ->on('k.companycode', '=', 't.companycode');
            })
            ->where('k.companycode', $companycode)
            ->where('k.isactive', 1)
            ->where(function($query) {
                $query->whereNull('k.idtenagakerja')
                      ->orWhere('t.isactive', 0)
                      ->orWhereNull('t.tenagakerjaid');
            })
            ->select([
                'k.*',
                't.nama as current_operator_nama'
            ])
            ->orderBy('k.nokendaraan')
            ->get();
    }

    /**
     * Check if vehicle is currently in use (has active operator)
     */
    public function isInUse()
    {
        if (!$this->idtenagakerja) {
            return false;
        }

        return DB::table('tenagakerja')
            ->where('companycode', $this->companycode)
            ->where('tenagakerjaid', $this->idtenagakerja)
            ->where('isactive', 1)
            ->exists();
    }

    /**
     * Get vehicle usage statistics
     */
    public static function getVehicleUsageStats($companycode, $dateFrom = null, $dateTo = null)
    {
        $query = DB::table('kendaraan as k')
            ->leftJoin('tenagakerja as t', function($join) {
                $join->on('k.idtenagakerja', '=', 't.tenagakerjaid')
                     ->on('k.companycode', '=', 't.companycode');
            })
            ->where('k.companycode', $companycode)
            ->where('k.isactive', 1)
            ->select([
                'k.nokendaraan',
                'k.jenis',
                'k.hourmeter',
                't.nama as operator_nama',
                DB::raw('CASE WHEN t.isactive = 1 THEN "Active" ELSE "Inactive" END as operator_status')
            ]);

        return $query->orderBy('k.nokendaraan')->get();
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