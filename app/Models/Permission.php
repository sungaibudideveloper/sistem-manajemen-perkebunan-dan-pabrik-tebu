<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'module',
        'resource',
        'action',
        'displayname',
        'description',
        'isactive',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships
    public function jabatanPermissions()
    {
        return $this->hasMany(JabatanPermission::class, 'permissionid', 'id');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'permissionid', 'id');
    }

    // Accessor for full permission name (module.resource.action)
    public function getFullNameAttribute(): string
    {
        return "{$this->module}.{$this->resource}.{$this->action}";
    }
}