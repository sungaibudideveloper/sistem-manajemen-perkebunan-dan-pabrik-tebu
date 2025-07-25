<?php
// app\Models\RKHLst.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rkhlst extends Model
{
    protected $table = 'rkhlst';
    public $incrementing = false;    // disable auto-increment
    public $timestamps = false;      // custom createdat/updatedat

    protected $fillable = [
        'companycode',
        'rkhno',
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
    ];

    protected $casts = [
        'rkhdate'             => 'date',
        'luasarea'            => 'float',
        'jumlahlaki'          => 'integer',
        'jumlahperempuan'     => 'integer',
        'jumlahtenagakerja'   => 'integer',
        'jenistenagakerja'    => 'integer',
        'usingmaterial'       => 'boolean',
        'usingvehicle'        => 'boolean',
        'herbisidagroupid'    => 'integer',
    ];

    // Format dates as Y-m-d when serializing
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d');
    }
}
