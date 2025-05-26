<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masterlist extends Model
{
    protected $table = 'masterlist';

    public $timestamps = false;

    protected $primaryKey = ['companycode', 'plot', 'batchno'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'companycode',
        'blok',
        'plot',
        'batchno',
        'batchdate',
        'batcharea',
        'tanggalulangtahun',
        'kodevarietas',
        'kodestatus',
        'jaraktanam',
        'lastactivity',
        'tanggalpanenpc',
        'tanggalpanenrc1',
        'tanggalpanenrc2',
        'tanggalpanenrc3',
        'isactive',
    ];

    protected $casts = [
        'batchdate' => 'date',
        'tanggalulangtahun' => 'date',
        'tanggalpanenpc' => 'date',
        'tanggalpanenrc1' => 'date',
        'tanggalpanenrc2' => 'date',
        'tanggalpanenrc3' => 'date',
        'batcharea' => 'float',
        'jaraktanam' => 'integer',
        'isactive' => 'boolean',
    ];

    /**
     * Override default behavior for composite keys (manual handling required in queries).
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('companycode', $this->getAttribute('companycode'))
                     ->where('plot', $this->getAttribute('plot'))
                     ->where('batchno', $this->getAttribute('batchno'));
    }
}
