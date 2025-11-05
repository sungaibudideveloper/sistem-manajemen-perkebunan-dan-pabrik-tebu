<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    protected $table = 'useractivity';
    
    // Composite primary key
    protected $primaryKey = ['userid', 'companycode'];
    public $incrementing = false;
    protected $keyType = 'array';
    
    // Timestamps
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
    
    // Override getKeyName untuk composite key

}