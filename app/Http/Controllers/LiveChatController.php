<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class LiveChatController extends Controller
{
    /**
     * Get recent chat messages
     */
    public function getMessages()
    {
        $messages = ChatMessage::getRecentMessages();
        
        return response()->json([
            'success' => true,
            'messages' => $messages->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'user' => [
                        'id' => $msg->user_id,
                        'name' => $msg->user_name,
                    ],
                    'timestamp' => $msg->created_at->format('H:i'),
                    'isOwn' => $msg->user_id === auth()->user()->userid,
                ];
            })
        ]);
    }

    /**
     * Send a message
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $user = Auth::user();
        $message = $request->input('message');

        // Save to database
        $chatMessage = ChatMessage::create([
            'user_id' => $user->userid,
            'user_name' => $user->name,
            'message' => $message,
        ]);

        // Broadcast message
        broadcast(new MessageSent($message, [
            'id' => $user->userid,
            'name' => $user->name,
        ]))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
        ], 200);
    }
}