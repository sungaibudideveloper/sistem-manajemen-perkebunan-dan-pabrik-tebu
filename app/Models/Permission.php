<?php

// app/Models/Permission.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    protected $primaryKey = 'permissionid';
    public $timestamps = false;

    protected $fillable = [
        'permissionname',
        'category', 
        'description',
        'isactive'
    ];

    protected $casts = [
        'isactive' => 'boolean'
    ];

    public function jabatanPermissions()
    {
        return $this->hasMany(JabatanPermission::class, 'permissionid');
    }

    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'permissionid');
    }
}