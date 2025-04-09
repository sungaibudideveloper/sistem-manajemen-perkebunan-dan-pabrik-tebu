<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'userid';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['userid', 'name', 'password', 'permissions', 'token_login'];

    public function userComp()
    {
        return $this->hasOne(Usercompany::class, 'userid', 'userid');
    }
}
