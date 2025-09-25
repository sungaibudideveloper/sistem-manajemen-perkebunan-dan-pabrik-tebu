<?php
// app/Models/UserPermission.php  
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = 'userpermission';
    public $incrementing = false;
    public $timestamps = true;

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'userid',
        'companycode',
        'permission',
        'permissionid',
        'permissiontype',
        'isactive',
        'reason',
        'grantedby'
    ];

    protected $casts = [
        'isactive' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    public function permissionModel()
    {
        return $this->belongsTo(Permission::class, 'permissionid');
    }
}