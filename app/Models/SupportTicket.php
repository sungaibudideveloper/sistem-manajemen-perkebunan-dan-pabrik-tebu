<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $table = 'support_tickets';
    protected $primaryKey = 'ticket_id';
    
    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected $fillable = [
        'ticket_number',
        'category',
        'status',
        'priority',
        'fullname',
        'username',
        'companycode',
        'description',
        'resolution_notes',
        'resolved_by',
        'resolved_at'
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relationship dengan Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'companycode', 'companycode');
    }

    // Generate ticket number
    public static function generateTicketNumber()
    {
        $year = date('Y');
        $lastTicket = self::where('ticket_number', 'like', "TKT-{$year}-%")
                         ->orderBy('ticket_id', 'desc')
                         ->first();
        
        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket->ticket_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "TKT-{$year}-{$newNumber}";
    }

    // Scope untuk filter
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}