<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\MasterData\Batch;
use App\Models\MasterData\Activity;

class Rkhlst extends Model
{
    protected $table = 'rkhlst';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'rkhno',
        'rkhhdrid',
        'rkhdate',
        'blok',
        'plot',
        'activitycode',
        'luasarea',
        'jumlahlaki',
        'jumlahperempuan',
        'jumlahtenagakerja',
        'jenistenagakerja',
        'usingmaterial',
        'herbisidagroupid',
        'usingvehicle',
        'operatorid',
        'usinghelper',
        'helperid',
        'batchno',
        'batchid',
        'kodestatus',
    ];

    protected $casts = [
        'rkhdate' => 'date',
        'luasarea' => 'float',
        'jumlahlaki' => 'integer',
        'jumlahperempuan' => 'integer',
        'jumlahtenagakerja' => 'integer',
        'jenistenagakerja' => 'integer',
        'usingmaterial' => 'boolean',
        'usingvehicle' => 'boolean',
        'herbisidagroupid' => 'integer',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }

    // Relationships (FK menggunakan surrogate ID)
    public function rkhHeader()
    {
        return $this->belongsTo(Rkhhdr::class, 'rkhhdrid', 'id');
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batchid', 'id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }
}