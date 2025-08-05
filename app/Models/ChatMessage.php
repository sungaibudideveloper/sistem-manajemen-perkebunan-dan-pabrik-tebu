<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    protected $fillable = [
        'user_id',
        'user_name',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get recent messages (last 50)
     */
    public static function getRecentMessages($limit = 50)
    {
        return self::orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get()
                   ->reverse()
                   ->values();
    }
}