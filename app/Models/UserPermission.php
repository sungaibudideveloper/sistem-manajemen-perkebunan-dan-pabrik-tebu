<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'userpermission';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'userid',
        'companycode',
        'permissionid',
        'permissiontype',
        'isactive',
        'reason',
        'grantedby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'permissionid' => 'integer',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissionid', 'id');
    }
}