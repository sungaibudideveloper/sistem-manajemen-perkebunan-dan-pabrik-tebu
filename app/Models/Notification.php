<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $fillable = ['id', 'kd_comp', 'title', 'body', 'user_input'];
}
