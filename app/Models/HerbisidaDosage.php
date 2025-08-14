<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HerbisidaDosage extends Model
{
    protected $table = 'herbisidadosage';

    public $timestamps = false;

    public $incrementing = false;        // kalau key-nya string/non-numeric
    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'herbisidagroupid',
        'itemcode',
        'dosageperha',
        'inputby',
        'updateby',
        'createdat',
        'updatedat',
    ];

    protected $casts = [
        'dosageperha' => 'decimal:2',
        'createdat'   => 'datetime',
        'updatedat'   => 'datetime',
    ];


    public function getFullHerbisidaGroupData($companycode)
    {
        return DB::select(
            "SELECT a.companycode, a.herbisidagroupid, c.herbisidagroupname, c.activitycode, a.itemcode, a.dosageperha, b.itemname, b.measure, c.description 
            FROM herbisidadosage a
            JOIN herbisida b ON (a.itemcode = b.itemcode)
            JOIN herbisidagroup c ON (a.herbisidagroupid = c.herbisidagroupid)
            WHERE a.companycode = ? AND b.companycode = ?",
            [$companycode,$companycode]
        );
    }
    
}