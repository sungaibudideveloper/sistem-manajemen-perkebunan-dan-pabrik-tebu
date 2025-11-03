<?php
// =====================================================
// FILE: app/Models/RkhLstWorker.php
// LOKASI: app/Models/RkhLstWorker.php (NEW FILE)
// =====================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RkhLstWorker extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'rkhlstworker';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     */
    protected $keyType = 'int';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'companycode',
        'rkhno',
        'activitycode',
        'jumlahlaki',
        'jumlahperempuan',
        'jumlahtenagakerja',
        'createdat',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'jumlahlaki' => 'integer',
        'jumlahperempuan' => 'integer',
        'jumlahtenagakerja' => 'integer',
        'createdat' => 'datetime',
    ];

    /**
     * Boot method - Auto-calculate total workers
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate jumlahtenagakerja before saving
        static::saving(function ($model) {
            $model->jumlahtenagakerja = $model->jumlahlaki + $model->jumlahperempuan;
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the RKH header that owns this worker assignment
     */
    public function rkhHeader()
    {
        return $this->belongsTo(RkhHdr::class, 'rkhno', 'rkhno')
                    ->where('companycode', $this->companycode);
    }

    /**
     * Get the activity details
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activitycode', 'activitycode');
    }

    /**
     * Get all plot details for this activity
     */
    public function plots()
    {
        return $this->hasMany(Rkhlst::class, 'activitycode', 'activitycode')
                    ->where('rkhno', $this->rkhno)
                    ->where('companycode', $this->companycode);
    }
}