<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $table = 'supporttickets';
    protected $primaryKey = 'ticket_id';
    public $timestamps = false;

    protected $fillable = [
        'ticket_number',
        'category',
        'status',
        'inprogress_by',
        'inprogress_at',
        'priority',
        'fullname',
        'username',
        'companycode',
        'description',
        'resolution_notes',
        'resolved_by',
        'resolved_at',
        'createdat',
        'updatedat'
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'inprogress_at' => 'datetime',
        'resolved_at' => 'datetime'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'userid');
    }
}