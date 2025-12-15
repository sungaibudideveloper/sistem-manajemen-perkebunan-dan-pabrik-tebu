<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class RkhLstWorker extends Model
{
    protected $table = 'rkhlstworker';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
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

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            $model->jumlahtenagakerja = $model->jumlahlaki + $model->jumlahperempuan;
        });
    }

    public function rkhHeader()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhhdrid', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }
}