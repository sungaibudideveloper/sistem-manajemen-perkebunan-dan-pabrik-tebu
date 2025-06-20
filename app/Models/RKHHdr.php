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
        'activitygroup', // TAMBAHAN BARU
        'jumlahapproval',
        'approval1idjabatan',
        'approval1userid',
        'approval1date',
        'approval1flag', // Tambahan untuk flag approval
        'approval2idjabatan',
        'approval2userid',
        'approval2date',
        'approval2flag',
        'approvali3djabatan',
        'approval3userid',
        'approval3date',
        'approval3flag',
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

    // TAMBAHAN: Relasi ke ActivityGroup
    public function activityGroup()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }

    // TAMBAHAN: Relasi ke Approval berdasarkan activitygroup
    public function approvalSetting()
    {
        return $this->belongsTo(Approval::class, 'activitygroup', 'category')
                    ->where('companycode', $this->companycode);
    }

    // TAMBAHAN: Method untuk mendapatkan approval yang diperlukan
    public function getRequiredApprovals()
    {
        $approval = $this->approvalSetting;
        if (!$approval) return [];

        $required = [];
        
        if ($approval->idjabatanapproval1) {
            $required[] = [
                'level' => 1,
                'jabatan_id' => $approval->idjabatanapproval1,
                'status' => $this->approval1flag,
                'date' => $this->approval1date,
                'user_id' => $this->approval1userid
            ];
        }
        
        if ($approval->idjabatanapproval2) {
            $required[] = [
                'level' => 2,
                'jabatan_id' => $approval->idjabatanapproval2,
                'status' => $this->approval2flag,
                'date' => $this->approval2date,
                'user_id' => $this->approval2userid
            ];
        }
        
        if ($approval->idjabatanapproval3) {
            $required[] = [
                'level' => 3,
                'jabatan_id' => $approval->approvali3djabatan,
                'status' => $this->approval3flag,
                'date' => $this->approval3date,
                'user_id' => $this->approval3userid
            ];
        }

        return $required;
    }

    // Method untuk check apakah sudah fully approved
    public function isFullyApproved()
    {
        $required = $this->getRequiredApprovals();
        if (empty($required)) return true;

        foreach ($required as $approval) {
            if ($approval['status'] !== '1') {
                return false;
            }
        }
        
        return true;
    }
}