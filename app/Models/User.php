<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens; // Laravel Sanctum
    
    protected $table = 'user';
    protected $primaryKey = 'userid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'userid',
        'companycode',
        'name',
        'idjabatan',
        'password',
        'mpassword',
        'inputby',
        'createdat',
        'updatedat',
        'divisionid',
        'isactive'
    ];

    protected $hidden = [
        'password',
        'mpassword'
    ];

    protected $casts = [
        'idjabatan' => 'integer',
        'divisionid' => 'integer',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'idjabatan', 'idjabatan');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'userid', 'userid');
    }

    public function userActivities()
    {
        return $this->hasMany(UserActivity::class, 'userid', 'userid');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'userid', 'userid');
    }
}