<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lkhhdr extends Model
{
    protected $table = 'lkhhdr';
    protected $primaryKey = 'lkhno';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'lkhno',
        'rkhno',
        'companycode',
        'activitycode',
        'blok',
        'mandorid',
        'lkhdate',
        'jenistenagakerja',
        'totalworkers',
        'totalluasactual',
        'totalhasil',
        'totalsisa',
        'totalupahall',
        'status',
        'keterangan',
        'jumlahapproval',
        'approval1idjabatan',
        'approval1userid',
        'approval1flag',
        'approval1date',
        'approval2idjabatan',
        'approval2userid',
        'approval2flag',
        'approval2date',
        'approval3idjabatan',
        'approval3userid',
        'approval3flag',
        'approval3date',
        'issubmit',
        'submitby',
        'submitat',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
        'mobilecreatedat',
        'mobileupdatedat',
        'webreceivedat',
    ];

    protected $casts = [
        'lkhdate' => 'date',
        'jenistenagakerja' => 'integer',
        'totalworkers' => 'integer',
        'totalluasactual' => 'decimal:2',
        'totalhasil' => 'decimal:2',
        'totalsisa' => 'decimal:2',
        'totalupahall' => 'decimal:2',
        'totalovertimehours' => 'decimal:2',
        'jumlahapproval' => 'integer',
        'approval1idjabatan' => 'integer',
        'approval1date' => 'datetime',
        'approval2idjabatan' => 'integer',
        'approval2date' => 'datetime',
        'approval3idjabatan' => 'integer',
        'approval3date' => 'datetime',
        'issubmit' => 'boolean',
        'submitat' => 'datetime',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'mobilecreatedat' => 'datetime',
        'mobileupdatedat' => 'datetime',
        'webreceivedat' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Relations
    public function rkh()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhno', 'rkhno');
    }

    public function details()
    {
        return $this->hasMany(Lkhlst::class, 'lkhno', 'lkhno');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }

    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    // Helper methods untuk LKH
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'EMPTY' => 'gray',
            'DRAFT' => 'yellow',
            'COMPLETED' => 'blue',
            'SUBMITTED' => 'purple',
            'APPROVED' => 'green',
            default => 'gray'
        };
    }

    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'EMPTY' => 'Kosong',
            'DRAFT' => 'Draft',
            'COMPLETED' => 'Selesai',
            'SUBMITTED' => 'Disubmit',
            'APPROVED' => 'Disetujui',
            default => 'Unknown'
        };
    }

    // NEW: Helper methods untuk approval LKH
    public function getRequiredApprovals()
    {
        if (!$this->jumlahapproval || $this->jumlahapproval == 0) return [];

        $required = [];
        
        if ($this->approval1idjabatan) {
            $required[] = [
                'level' => 1,
                'jabatan_id' => $this->approval1idjabatan,
                'status' => $this->approval1flag,
                'date' => $this->approval1date,
                'user_id' => $this->approval1userid
            ];
        }
        
        if ($this->approval2idjabatan) {
            $required[] = [
                'level' => 2,
                'jabatan_id' => $this->approval2idjabatan,
                'status' => $this->approval2flag,
                'date' => $this->approval2date,
                'user_id' => $this->approval2userid
            ];
        }
        
        if ($this->approval3idjabatan) {
            $required[] = [
                'level' => 3,
                'jabatan_id' => $this->approval3idjabatan,
                'status' => $this->approval3flag,
                'date' => $this->approval3date,
                'user_id' => $this->approval3userid
            ];
        }

        return $required;
    }

    // NEW: Check apakah LKH sudah fully approved
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

    // NEW: Check apakah LKH bisa di-edit
    public function canBeEdited()
    {
        return !$this->issubmit && !$this->isFullyApproved();
    }

    // NEW: Check apakah LKH bisa di-lock
    public function canBeLocked()
    {
        return !$this->issubmit && ($this->status === 'COMPLETED' || $this->status === 'SUBMITTED');
    }

    // NEW: Get approval status text
    public function getApprovalStatusAttribute()
    {
        if (!$this->jumlahapproval || $this->jumlahapproval == 0) {
            return 'No Approval Required';
        }

        if ($this->isFullyApproved()) {
            return 'Approved';
        }

        // Check for declined
        if ($this->approval1flag === '0' || $this->approval2flag === '0' || $this->approval3flag === '0') {
            return 'Declined';
        }

        // Count completed approvals
        $completed = 0;
        if ($this->approval1flag === '1') $completed++;
        if ($this->approval2flag === '1') $completed++;
        if ($this->approval3flag === '1') $completed++;

        return "Waiting for Approve ({$completed} / {$this->jumlahapproval})";
    }

    // NEW: Get lock status info
    public function getLockStatusInfoAttribute()
    {
        if (!$this->issubmit) {
            return 'Unlocked';
        }

        $info = 'Locked';
        if ($this->submitby) {
            $info .= " by {$this->submitby}";
        }
        if ($this->submitat) {
            $info .= " at " . $this->submitat->format('d/m/Y H:i');
        }

        return $info;
    }

    // NEW: Get next approval level yang diperlukan
    public function getNextApprovalLevel()
    {
        if (!$this->jumlahapproval || $this->jumlahapproval == 0) {
            return null;
        }

        // Check level 1
        if ($this->approval1idjabatan && (!$this->approval1flag || $this->approval1flag === '0')) {
            return 1;
        }

        // Check level 2
        if ($this->approval2idjabatan && $this->approval1flag === '1' && 
            (!$this->approval2flag || $this->approval2flag === '0')) {
            return 2;
        }

        // Check level 3
        if ($this->approval3idjabatan && $this->approval1flag === '1' && 
            $this->approval2flag === '1' && (!$this->approval3flag || $this->approval3flag === '0')) {
            return 3;
        }

        return null; // Sudah fully approved atau declined
    }

    // NEW: Check apakah user bisa approve LKH ini
    public function canBeApprovedBy($userId, $jabatanId)
    {
        $nextLevel = $this->getNextApprovalLevel();
        if (!$nextLevel) return false;

        switch ($nextLevel) {
            case 1:
                return $this->approval1idjabatan == $jabatanId;
            case 2:
                return $this->approval2idjabatan == $jabatanId;
            case 3:
                return $this->approval3idjabatan == $jabatanId;
            default:
                return false;
        }
    }

    // Scope untuk filter
    public function scopeByCompany($query, $companycode)
    {
        return $query->where('companycode', $companycode);
    }

    public function scopeByMandor($query, $mandorid)
    {
        return $query->where('mandorid', $mandorid);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('lkhdate', $date);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRkh($query, $rkhno)
    {
        return $query->where('rkhno', $rkhno);
    }

    public function scopeLocked($query, $locked = true)
    {
        return $query->where('issubmit', $locked);
    }

    public function scopeUnlocked($query)
    {
        return $query->where('issubmit', 0);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('issubmit', 1)
                    ->where('status', '!=', 'APPROVED');
    }

    public function scopeFullyApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }

    // NEW: Scope untuk approval berdasarkan jabatan
    public function scopeAwaitingApprovalBy($query, $jabatanId)
    {
        return $query->where('issubmit', 1)
                    ->where(function($q) use ($jabatanId) {
                        $q->where(function($sub) use ($jabatanId) {
                            // Level 1 approval
                            $sub->where('approval1idjabatan', $jabatanId)
                                ->whereNull('approval1flag');
                        })->orWhere(function($sub) use ($jabatanId) {
                            // Level 2 approval
                            $sub->where('approval2idjabatan', $jabatanId)
                                ->where('approval1flag', '1')
                                ->whereNull('approval2flag');
                        })->orWhere(function($sub) use ($jabatanId) {
                            // Level 3 approval
                            $sub->where('approval3idjabatan', $jabatanId)
                                ->where('approval1flag', '1')
                                ->where('approval2flag', '1')
                                ->whereNull('approval3flag');
                        });
                    });
    }
}