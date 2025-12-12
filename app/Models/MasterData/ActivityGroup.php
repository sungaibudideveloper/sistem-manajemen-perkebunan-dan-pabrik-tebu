<?php
// =====================================================
// FILE: app/Models/MasterData/ActivityGroup.php
// =====================================================
namespace App\Models\MasterData;

use Illuminate\Database\Eloquent\Model;

class ActivityGroup extends Model
{
    protected $table = 'activitygroup';
    protected $primaryKey = 'activitygroup';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'activitygroup',
        'groupname',
        'description',
        'createdat',
        'inputby'
    ];

    protected $casts = [
        'createdat' => 'datetime'
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class, 'activitygroup', 'activitygroup');
    }
}