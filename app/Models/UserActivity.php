<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'useractivity';

    // Composite primary key
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = true;
    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'userid',
        'companycode',
        'activitygroup',
        'grantedby',
        'createdat',
        'updatedat'
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userid', 'userid');
    }

    /**
     * Relasi ke Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    /**
     * Relasi ke ActivityGroup
     */
    public function activityGroup()
    {
        return $this->belongsTo(ActivityGroup::class, 'activitygroup', 'groupname');
    }

    /**
     * User yang memberikan akses
     */
    public function grantedByUser()
    {
        return $this->belongsTo(User::class, 'grantedby', 'userid');
    }
}
