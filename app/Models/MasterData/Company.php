<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';
    protected $primaryKey = 'companycode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'name',
        'address',
        'companyinventory',
        'inputby',
        'createdat',
        'updatedat',
        'companyperiod',
        'companygl',
        'companygroup'
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'companyperiod' => 'date'
    ];

    // Relationships
    public function users()
    {
        return $this->hasMany(User::class, 'companycode', 'companycode');
    }

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'companycode', 'companycode');
    }

    public function userActivities()
    {
        return $this->hasMany(UserActivity::class, 'companycode', 'companycode');
    }
}