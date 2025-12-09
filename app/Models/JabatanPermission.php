<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanPermission extends Model
{
    protected $table = 'jabatanpermission';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'idjabatan',
        'permissionid',
        'isactive',
        'grantedby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'idjabatan' => 'integer',
        'permissionid' => 'integer',
        'isactive' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships
    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'idjabatan', 'idjabatan');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissionid', 'id');
    }
}