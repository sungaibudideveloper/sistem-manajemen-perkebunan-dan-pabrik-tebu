<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class ActivityGroup extends Model
{
    protected $table = 'activitygroup';
    protected $primaryKey = 'activitygroup';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'activitygroup',
        'groupname'
    ];
}