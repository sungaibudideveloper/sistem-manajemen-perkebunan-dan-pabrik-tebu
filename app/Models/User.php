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
        // 'permissions' => 'array', << tambahin ini kalau mau gaperlu pake json_encode pas manggil
        'createdat' => 'date',
        'updatedat' => 'date',
    ];

    public function userComp()
    {
        return $this->hasOne(UserCompany::class, 'userid', 'userid');
    }

    public static function getMandorByCompany($companyCode)
    {
        return self::select(['userid', 'name', 'companycode', 'idjabatan'])
                ->where('companycode', $companyCode)
                ->where('idjabatan', 5)
                ->orderBy('userid')
                ->get();
    }
}
