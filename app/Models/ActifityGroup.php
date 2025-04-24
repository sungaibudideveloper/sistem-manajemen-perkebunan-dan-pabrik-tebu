<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActifityGroup extends Model
{
    public $incrementing = false;
    protected $table = 'actifitygroup';
    protected $primaryKey = ['actifitygroup'];
    protected $fillable = [
        'actifitygroup',
        'actifityname'
    ];

}
