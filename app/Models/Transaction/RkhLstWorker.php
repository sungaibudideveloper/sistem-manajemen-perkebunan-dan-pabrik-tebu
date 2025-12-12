<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Activity;

class RkhLstWorker extends Model
{
    protected $table = 'rkhlstworker';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhhdrid',
        'activitycode',
        'jumlahlaki',
        'jumlahperempuan',
        'jumlahtenagakerja',
        'createdat',
    ];

    protected $casts = [
        'jumlahlaki' => 'integer',
        'jumlahperempuan' => 'integer',
        'jumlahtenagakerja' => 'integer',
        'createdat' => 'datetime',
    ];

    // Relationships (FK menggunakan surrogate ID)
    public function rkhHeader()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhhdrid', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }
}