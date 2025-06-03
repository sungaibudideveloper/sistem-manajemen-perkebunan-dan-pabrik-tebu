<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Rkhhdr extends Model
{
    protected $table = 'rkhhdr';

    protected $primaryKey = 'rkhno';
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhdate',
        'manpower',
        'totalluas',
        'mandorid',
        'jumlahapproval',
        'approval1idjabatan',
        'approval1userid',
        'approval1date',
        'approval2idjabatan',
        'approval2userid',
        'approval2date',
        'approvali3djabatan',
        'approval3userid',
        'approval3date',
        'status',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'rkhdate'            => 'date',
        'manpower'           => 'integer',
        'totalluas'          => 'float',
        'jumlahapproval'     => 'integer',
        'approval1idjabatan' => 'integer',
        'approval1date'      => 'datetime',
        'approval2idjabatan' => 'integer',
        'approval2date'      => 'datetime',
        'approvali3djabatan' => 'integer',
        'approval3date'      => 'datetime',
        'createdat'          => 'datetime',
        'updatedat'          => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    

}
