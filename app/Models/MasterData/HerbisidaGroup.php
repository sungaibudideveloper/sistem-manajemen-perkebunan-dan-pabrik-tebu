<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class HerbisidaGroup extends Model
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