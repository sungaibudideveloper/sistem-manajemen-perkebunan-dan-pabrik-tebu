<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'userid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'userid',
        'companycode',
        'name',
        'idjabatan',
        'password',
        'permissions',
        'inputby',
        'createdat',
        'updatedat',
        'divisionid',
        'token_login',
        'isactive'
    ];

    protected $casts = [
        'createdat' => 'date',
        'updatedat' => 'date',
        'isactive' => 'boolean'
    ];

    // Relationships
    public function userComp()
    {
        return $this->hasOne(UserCompany::class, 'userid', 'userid');
    }

    public function userCompanies()
    {
        return $this->hasMany(UserCompany::class, 'userid', 'userid')
                    ->where('isactive', 1);
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'idjabatan', 'idjabatan');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'userid', 'userid')
                    ->where('isactive', 1);
    }

    // Permission-related methods
    public function hasPermission(string $permissionName): bool
    {
        // Use the CheckPermission middleware logic
        $middleware = new \App\Http\Middleware\CheckPermission();
        return $middleware->checkUserPermission($this, $permissionName);
    }

    public function getEffectivePermissions(): array
    {
        return \App\Http\Middleware\CheckPermission::getUserEffectivePermissions($this);
    }

    public function getJabatanPermissions()
    {
        if (!$this->idjabatan) {
            return collect([]);
        }

        return JabatanPermission::join('permissions', 'jabatanpermissions.permissionid', '=', 'permissions.permissionid')
                               ->where('jabatanpermissions.idjabatan', $this->idjabatan)
                               ->where('jabatanpermissions.isactive', 1)
                               ->where('permissions.isactive', 1)
                               ->select('permissions.*', 'jabatanpermissions.grantedby', 'jabatanpermissions.createdat')
                               ->get();
    }

    public function getAccessibleCompanies()
    {
        return $this->userCompanies()->with('company')->get();
    }

    // Existing methods
    public static function getMandorByCompany($companyCode)
    {
        return self::select(['userid', 'name', 'companycode', 'idjabatan'])
                ->where('companycode', $companyCode)
                ->where('idjabatan', 5)
                ->orderBy('userid')
                ->get();
    }

    // Scope for active users
    public function scopeActive($query)
    {
        return $query->where('isactive', 1);
    }

    // Scope for specific jabatan
    public function scopeJabatan($query, $jabatanId)
    {
        return $query->where('idjabatan', $jabatanId);
    }

    // Get user full info with relations
    public function getFullUserInfo()
    {
        return $this->load([
            'jabatan',
            'userCompanies',
            'userPermissions.permissionModel'
        ]);
    }
}