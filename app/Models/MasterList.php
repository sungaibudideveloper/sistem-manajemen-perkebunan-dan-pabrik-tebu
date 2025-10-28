<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Masterlist extends Model
{
    protected $table = 'masterlist';
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'companycode',
        'plot',
        'blok',
        'tanggalulangtahun',
        'activebatchno',
        'isactive',
    ];

    // Composite primary key handling
    protected function setKeysForSaveQuery($query)
    {
        $query->where('companycode', $this->getAttribute('companycode'))
              ->where('plot', $this->getAttribute('plot'));
        return $query;
    }

    // Relationship to active batch
    public function activeBatch()
    {
        return $this->belongsTo(Batch::class, 'activebatchno', 'batchno');
    }
}