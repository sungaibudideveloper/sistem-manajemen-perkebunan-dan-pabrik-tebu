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
        'inputby',
        'updateby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'jumlahapproval' => 'integer',
        'idjabatanapproval1' => 'integer',
        'idjabatanapproval2' => 'integer',
        'idjabatanapproval3' => 'integer',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public function jabatanApproval1()
    {
        return $this->belongsTo(Jabatan::class, 'idjabatanapproval1', 'idjabatan');
    }
    public function jabatanApproval2()
    {
        return $this->belongsTo(Jabatan::class, 'idjabatanapproval2', 'idjabatan');
    }
    public function jabatanApproval3()
    {
        return $this->belongsTo(Jabatan::class, 'idjabatanapproval3', 'idjabatan');
    }
}
