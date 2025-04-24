<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $table = 'approval';

    public $timestamps = false;

    protected $primaryKey = 'activitycode';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'companycode',
        'activitycode',
        'jumlahapproval',
        'idjabatanapproval1',
        'idjabatanapproval2',
        'idjabatanapproval3',
    ];

    protected $casts = [
        'jumlahapproval' => 'integer',
        'idjabatanapproval1' => 'integer',
        'idjabatanapproval2' => 'integer',
        'idjabatanapproval3' => 'integer',
    ];
}