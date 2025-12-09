<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'useractivity';
    protected $primaryKey = null; // Composite key
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'userid',
        'companycode',
        'activitygroup',
        'grantedby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }
}