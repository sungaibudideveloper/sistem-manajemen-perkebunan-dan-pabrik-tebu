<?php

namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $table = 'approval';

    public $timestamps = false;

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'companycode',
        'category',
        'activitygroup',
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
        'id' => 'integer',
        'jumlahapproval' => 'integer',
        'idjabatanapproval1' => 'integer',
        'idjabatanapproval2' => 'integer',
        'idjabatanapproval3' => 'integer',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

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