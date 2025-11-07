<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Herbisidagroup extends Model
{
    protected $table = 'herbisidagroup';

    protected $primaryKey = 'herbisidagroupid';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'herbisidagroupid',
        'herbisidagroupname',
        'activitycode',
        'description',
    ];

}