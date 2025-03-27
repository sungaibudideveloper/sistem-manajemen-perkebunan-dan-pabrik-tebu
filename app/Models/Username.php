<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Username extends Authenticatable
{
    use Notifiable;

    protected $table = 'username';
    protected $primaryKey = 'usernm';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['usernm', 'name', 'password', 'permissions', 'token_login'];

    public function userComp()
    {
        return $this->hasOne(Usercomp::class, 'usernm', 'usernm');
    }
}
