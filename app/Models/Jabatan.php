<?php

// app/Models/Jabatan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatan';
    protected $primaryKey = 'idjabatan';
    public $timestamps = true;

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'namajabatan',
        'inputby',
        'updateby'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'idjabatan');
    }

    public function jabatanPermissions()
    {
        return $this->hasMany(JabatanPermission::class, 'idjabatan');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'jabatanpermissions', 'idjabatan', 'permissionid')
                    ->wherePivot('isactive', 1);
    }
}