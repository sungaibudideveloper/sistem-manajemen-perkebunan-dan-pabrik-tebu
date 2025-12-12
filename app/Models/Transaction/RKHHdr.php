<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\ActivityGroup;
use App\Models\User;

class Rkhhdr extends Model
{
    protected $table = 'rkhhdr';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhdate',
        'manpower',
        'totalluas',
        'mandorid',
        'activitygroup',
        'keterangan',
        'jumlahapproval',
        'approval1idjabatan',
        'approval1userid',
        'approval1date',
        'approval1flag',
        'approval2idjabatan',
        'approval2userid',
        'approval2date',
        'approval2flag',
        'approval3idjabatan',
        'approval3userid',
        'approval3date',
        'approval3flag',
        'approvalstatus',
        'status',
        'inputby',
        'createdat',
        'updateby',
        'updatedat',
    ];

    protected $casts = [
        'rkhdate' => 'date',
        'manpower' => 'integer',
        'totalluas' => 'float',
        'jumlahapproval' => 'integer',
        'approval1idjabatan' => 'integer',
        'approval1date' => 'datetime',
        'approval2idjabatan' => 'integer',
        'approval2date' => 'datetime',
        'approval3idjabatan' => 'integer',
        'approval3date' => 'datetime',
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    // Relationships (FK menggunakan surrogate ID)
    public function rkhLst()
    {
        return $this->hasMany(Rkhlst::class, 'rkhhdrid', 'id');
    }

    public function rkhLstWorker()
    {
        return $this->hasMany(RkhLstWorker::class, 'rkhhdrid', 'id');
    }

    public function rkhLstKendaraan()
    {
        return $this->hasMany(RkhLstKendaraan::class, 'rkhhdrid', 'id');
    }

    public function lkhHeaders()
    {
        return $this->hasMany(Lkhhdr::class, 'rkhhdrid', 'id');
    }

    public function mandor()
    {
        return $this->belongsTo(User::class, 'mandorid', 'userid');
    }

    public function activityGroup()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }

    // Finder by business key
    public static function findByBusinessKey(string $companycode, string $rkhno): ?self
    {
        return static::where('companycode', $companycode)
            ->where('rkhno', $rkhno)
            ->first();
    }
}