<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'useractivity';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'userid',
        'companycode',
        'activitygroup',
        'isactive',
        'grantedby',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'isactive' => 'boolean',
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
    
    public function activityGroupModel()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'activitygroup');
    }
}