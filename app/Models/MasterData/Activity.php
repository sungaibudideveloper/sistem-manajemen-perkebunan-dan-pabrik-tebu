<?php
// =====================================================
// FILE: app/Models/MasterData/Activity.php
// =====================================================
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activity';
    protected $primaryKey = 'activitycode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'activitycode',
        'activitygroup',
        'activityname',
        'tanggaltanam',
        'description',
        'jurnalno',
        'jenistenagakerja',
        'isblokactivity',
        'createdat',
        'inputby',
        'updatedat',
        'updatedby',
        'accno'
    ];

    protected $casts = [
        'tanggaltanam' => 'date',
        'jenistenagakerja' => 'integer',
        'isblokactivity' => 'boolean',
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    public function group()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }
}