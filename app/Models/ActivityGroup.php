<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityGroup extends Model
{
    public $incrementing = false;
    protected $table = 'activitygroup';
    protected $primaryKey = ['activitygroup'];
    protected $fillable = [
        'activitygroup',
        'activityname'
    ];

}
